<?php
/**
 * Controller: Stok Masuk - Apotek Ananda Jadimulya
 * CRUD stok obat masuk secara global dengan filter per PBF.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../models/stok_masuk.php';
require_once __DIR__ . '/../models/pbf.php';
require_once __DIR__ . '/../models/log_aktivitas.php';

initSecureSession();
requireLogin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':       handleCreate(); break;
    case 'update':       handleUpdate(); break;
    case 'update_batch': handleUpdateBatch(); break;
    case 'delete':       handleDelete(); break;
    case 'detail':       handleDetail(); break;
    default:
        redirect(getRedirectUrl());
        break;
}

function getRedirectUrl(): string {
    $role = getCurrentRole();
    return BASE_URL . '/frontend/' . ($role === 'super_admin' ? 'superadmin' : 'admin') . '/manajemen_stok.php';
}

function getValidSatuan(): array {
    return ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();
    
    // Validasi input
    $data = [
        'id_pbf'        => sanitizeInt($_POST['id_pbf'] ?? 0),
        'no_faktur'     => sanitize($_POST['no_faktur'] ?? ''),
        'tanggal_masuk' => $_POST['tanggal_masuk'] ?? '',
        'nama_obat'     => sanitize($_POST['nama_obat'] ?? ''),
        'satuan'        => $_POST['satuan'] ?? '',
        'batch'         => sanitize($_POST['batch'] ?? ''),
        'expired_date'  => $_POST['expired_date'] ?? '',
        'harga_beli'    => sanitizeDecimal($_POST['harga_beli'] ?? 0),
        'discount'      => sanitizeDecimal($_POST['discount'] ?? 0),
        'jumlah_masuk'  => sanitizeInt($_POST['jumlah_masuk'] ?? 0),
    ];
    
    // Validasi required fields
    $errors = [];
    if ($data['id_pbf'] <= 0) $errors[] = 'Asal PBF harus dipilih';
    if (empty($data['no_faktur'])) $errors[] = 'No. Faktur harus diisi';
    if (!isValidDate($data['tanggal_masuk'])) $errors[] = 'Tanggal masuk tidak valid';
    if (empty($data['nama_obat'])) $errors[] = 'Nama obat harus diisi';
    if (!in_array($data['satuan'], getValidSatuan())) $errors[] = 'Satuan tidak valid';
    if (!isValidDate($data['expired_date'])) $errors[] = 'Expired date tidak valid';
    if ($data['harga_beli'] < 0) $errors[] = 'Harga beli tidak valid';
    if ($data['discount'] < 0 || $data['discount'] > 100) $errors[] = 'Discount harus antara 0-100%';
    if ($data['jumlah_masuk'] <= 0) $errors[] = 'Jumlah masuk harus lebih dari 0';
    
    if (!empty($errors)) {
        setFlashMessage('error', implode(', ', $errors));
        redirect(getRedirectUrl());
        return;
    }
    
    $stokModel = new StokMasuk();
    $id = $stokModel->create($data);
    
    if ($id) {
        $pbfModel = new PBF();
        $pbf = $pbfModel->findById($data['id_pbf']);
        $pbfNama = $pbf ? $pbf['nama_pbf'] : 'Unknown';
        
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Tambah Stok Obat', 
            "Menambahkan stok {$data['nama_obat']} ({$data['jumlah_masuk']} {$data['satuan']}) dari PBF {$pbfNama}, Faktur {$data['no_faktur']}");
        
        setFlashMessage('success', "Stok obat '{$data['nama_obat']}' berhasil ditambahkan.");
    } else {
        setFlashMessage('error', 'Gagal menambahkan stok obat.');
    }
    
    redirect(getRedirectUrl());
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_masuk'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID stok tidak valid.');
        redirect(getRedirectUrl());
        return;
    }
    
    $data = [
        'id_pbf'        => sanitizeInt($_POST['id_pbf'] ?? 0),
        'no_faktur'     => sanitize($_POST['no_faktur'] ?? ''),
        'tanggal_masuk' => $_POST['tanggal_masuk'] ?? '',
        'nama_obat'     => sanitize($_POST['nama_obat'] ?? ''),
        'satuan'        => $_POST['satuan'] ?? '',
        'batch'         => sanitize($_POST['batch'] ?? ''),
        'expired_date'  => $_POST['expired_date'] ?? '',
        'harga_beli'    => sanitizeDecimal($_POST['harga_beli'] ?? 0),
        'discount'      => sanitizeDecimal($_POST['discount'] ?? 0),
        'jumlah_masuk'  => sanitizeInt($_POST['jumlah_masuk'] ?? 0),
    ];
    
    // Validasi
    if ($data['id_pbf'] <= 0 || empty($data['nama_obat']) || !in_array($data['satuan'], getValidSatuan())) {
        setFlashMessage('error', 'Data tidak lengkap atau tidak valid.');
        redirect(getRedirectUrl());
        return;
    }
    
    $stokModel = new StokMasuk();
    
    if ($stokModel->update($id, $data)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Edit Stok Obat', 
            "Mengubah stok obat ID {$id}: {$data['nama_obat']} ({$data['jumlah_masuk']} {$data['satuan']})");
        setFlashMessage('success', 'Data stok obat berhasil diperbarui.');
    } else {
        setFlashMessage('error', 'Gagal memperbarui data stok.');
    }
    
    redirect(getRedirectUrl());
}

/**
 * Update hanya no batch (digunakan dari laporan expired otomatis)
 */
function handleUpdateBatch(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php'); return; }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_masuk'] ?? 0);
    $batch = sanitize($_POST['batch'] ?? '');
    
    if ($id <= 0) {
        setFlashMessage('error', 'ID tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    
    $stokModel = new StokMasuk();
    if ($stokModel->updateBatch($id, $batch)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Update Batch Obat', "Memperbarui No. Batch untuk stok obat ID {$id} menjadi '{$batch}'");
        setFlashMessage('success', 'No. Batch berhasil diperbarui.');
    } else {
        setFlashMessage('error', 'Gagal memperbarui No. Batch.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireSuperAdmin(); // Hanya Super Admin boleh hapus
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_masuk'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID stok tidak valid.');
        redirect(getRedirectUrl());
        return;
    }
    
    $stokModel = new StokMasuk();
    $stok = $stokModel->findById($id);
    
    if (!$stok) {
        setFlashMessage('error', 'Data stok tidak ditemukan.');
        redirect(getRedirectUrl());
        return;
    }
    
    if ($stokModel->delete($id)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Hapus Stok Obat', 
            "Menghapus stok: {$stok['nama_obat']} ({$stok['jumlah_masuk']} {$stok['satuan']}) dari PBF {$stok['nama_pbf']}");
        setFlashMessage('success', "Stok obat '{$stok['nama_obat']}' berhasil dihapus.");
    } else {
        setFlashMessage('error', 'Gagal menghapus stok obat.');
    }
    
    redirect(getRedirectUrl());
}

function handleDetail(): void {
    $id = sanitizeInt($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['error' => 'ID tidak valid']);
        exit;
    }
    
    $stokModel = new StokMasuk();
    $data = $stokModel->findById($id);
    
    header('Content-Type: application/json');
    echo json_encode($data ?: ['error' => 'Data tidak ditemukan']);
    exit;
}
