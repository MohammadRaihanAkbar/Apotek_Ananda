<?php
/**
 * Controller: Expired - Apotek Ananda Jadimulya
 * CRUD laporan obat expired (manual) + laporan gabungan otomatis.
 * Akses: Super Admin SAJA.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../models/obat_expired.php';
require_once __DIR__ . '/../helpers/export_helper.php';

initSecureSession();
requireSuperAdmin(); // Hanya Super Admin

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':       handleCreate(); break;
    case 'update':       handleUpdate(); break;
    case 'delete':       handleDelete(); break;
    case 'export_excel': handleExportExcel(); break;
    case 'export_pdf':   handleExportPDF(); break;
    default:
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        break;
}

function handleExportExcel(): void {
    $search = $_GET['search'] ?? null;
    $model = new ObatExpired();
    $data = $model->getCombinedExpiredReport($search);
    
    $headers = ['No', 'Sumber', 'Nama Obat', 'Qty', 'Satuan', 'Batch', 'Exp Date', 'Harga Beli', 'PBF'];
    $rows = [];
    $no = 1;
    foreach ($data as $row) {
        $rows[] = [
            $no++,
            ucfirst($row['sumber']),
            $row['nama_obat'],
            $row['qty'],
            $row['satuan'],
            $row['batch'] ?: '-',
            $row['expired_date'],
            number_format($row['harga_beli'], 0, ',', '.'),
            $row['nama_pbf'] ?: '-'
        ];
    }
    
    exportToExcel('Laporan_Expired_' . date('Ymd'), 'Laporan Obat Expired Apotek Ananda', $headers, $rows);
}

function handleExportPDF(): void {
    $search = $_GET['search'] ?? null;
    $model = new ObatExpired();
    $data = $model->getCombinedExpiredReport($search);
    
    $headers = ['No', 'Sumber', 'Nama Obat', 'Qty', 'Satuan', 'Batch', 'Exp Date', 'Harga Beli', 'PBF'];
    $rows = [];
    $no = 1;
    foreach ($data as $row) {
        $rows[] = [
            $no++,
            ucfirst($row['sumber']),
            $row['nama_obat'],
            $row['qty'],
            $row['satuan'],
            $row['batch'] ?: '-',
            $row['expired_date'],
            'Rp ' . number_format($row['harga_beli'], 0, ',', '.'),
            $row['nama_pbf'] ?: '-'
        ];
    }
    
    exportToPDFView('Laporan Obat Expired', $headers, $rows);
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    requireValidCSRF();
    
    $validSatuan = ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];
    
    $data = [
        'nama_obat'    => sanitize($_POST['nama_obat'] ?? ''),
        'qty'          => sanitizeInt($_POST['qty'] ?? 0),
        'satuan'       => $_POST['satuan'] ?? '',
        'batch'        => sanitize($_POST['batch'] ?? ''),
        'expired_date' => $_POST['expired_date'] ?? '',
        'harga_beli'   => sanitizeDecimal($_POST['harga_beli'] ?? 0),
        'nama_pbf'     => sanitize($_POST['nama_pbf'] ?? ''),
    ];
    
    // Validasi
    if (empty($data['nama_obat']) || $data['qty'] <= 0 || !in_array($data['satuan'], $validSatuan) || !isValidDate($data['expired_date'])) {
        setFlashMessage('error', 'Data tidak lengkap atau tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    
    $model = new ObatExpired();
    $id = $model->create($data, getCurrentUserId());
    
    if ($id) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Input Expired Manual', 
            "Menambahkan data expired manual: {$data['nama_obat']} ({$data['qty']} {$data['satuan']})");
        setFlashMessage('success', "Data obat expired '{$data['nama_obat']}' berhasil ditambahkan.");
    } else {
        setFlashMessage('error', 'Gagal menambahkan data obat expired.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_expired'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    
    $validSatuan = ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];
    
    $data = [
        'nama_obat'    => sanitize($_POST['nama_obat'] ?? ''),
        'qty'          => sanitizeInt($_POST['qty'] ?? 0),
        'satuan'       => $_POST['satuan'] ?? '',
        'batch'        => sanitize($_POST['batch'] ?? ''),
        'expired_date' => $_POST['expired_date'] ?? '',
        'harga_beli'   => sanitizeDecimal($_POST['harga_beli'] ?? 0),
        'nama_pbf'     => sanitize($_POST['nama_pbf'] ?? ''),
    ];
    
    if (empty($data['nama_obat']) || !in_array($data['satuan'], $validSatuan)) {
        setFlashMessage('error', 'Data tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    
    $model = new ObatExpired();
    if ($model->update($id, $data)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Edit Expired Manual', 
            "Mengubah data expired ID {$id}: {$data['nama_obat']}");
        setFlashMessage('success', 'Data obat expired berhasil diperbarui.');
    } else {
        setFlashMessage('error', 'Gagal memperbarui data.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_expired'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    
    $model = new ObatExpired();
    $expired = $model->findById($id);
    
    if (!$expired) {
        setFlashMessage('error', 'Data tidak ditemukan.');
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }
    
    if ($model->delete($id)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Hapus Expired Manual', 
            "Menghapus data expired: {$expired['nama_obat']}");
        setFlashMessage('success', "Data expired '{$expired['nama_obat']}' berhasil dihapus.");
    } else {
        setFlashMessage('error', 'Gagal menghapus data.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
}
