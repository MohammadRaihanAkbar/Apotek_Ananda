<?php
/**
 * Controller: Faktur/Stok - CRUD faktur dengan obat dan batch per qty.
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
    case 'create_faktur': handleCreateFaktur(); break;
    case 'update_faktur': handleUpdateFaktur(); break;
    case 'delete_faktur': handleDeleteFaktur(); break;
    case 'detail':        handleDetail(); break;
    case 'update_batch':  handleUpdateBatch(); break;
    case 'create':        handleCreateFaktur(); break;
    case 'update':        handleUpdateFaktur(); break;
    case 'delete':        handleDeleteFaktur(); break;
    default: redirect(getRedirectUrl()); break;
}

function getRedirectUrl(): string {
    $role = getCurrentRole();
    return BASE_URL . '/frontend/' . ($role === 'super_admin' ? 'superadmin' : 'admin') . '/manajemen_stok.php';
}

function getFakturFormUrl(?int $id = null): string {
    $role = getCurrentRole();
    $base = BASE_URL . '/frontend/' . ($role === 'super_admin' ? 'superadmin' : 'admin') . '/tambah_faktur.php';
    return $id ? $base . '?id=' . $id : $base;
}

function getValidSatuan(): array {
    return ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];
}

function collectHeader(): array {
    $status = $_POST['status_bayar'] ?? $_POST['status_pembayaran'] ?? 'belum_lunas';
    if (!in_array($status, ['belum_lunas', 'lunas'], true)) {
        $status = 'belum_lunas';
    }
    return [
        'id_pbf'              => sanitizeInt($_POST['id_pbf'] ?? 0),
        'no_faktur'           => sanitize($_POST['no_faktur'] ?? ''),
        'tanggal_faktur'      => $_POST['tanggal_faktur'] ?? '',
        'tanggal_masuk'       => $_POST['tanggal_masuk'] ?? '',
        // Jatuh tempo tidak ditampilkan di Manajemen Stok. Nilai ini sengaja nullable.
        'tanggal_jatuh_tempo' => null,
        'status_bayar'        => $status,
        'id_user'             => getCurrentUserId(),
    ];
}

function parseBatchJson(string $json): array {
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) return [];
    $batches = [];
    foreach ($decoded as $row) {
        $noBatch = sanitize($row['no_batch'] ?? '');
        $expired = $row['expired_date'] ?? '';
        if ($noBatch !== '' && isValidDate($expired)) {
            $batches[] = ['no_batch' => $noBatch, 'expired_date' => $expired];
        }
    }
    return $batches;
}

function collectItems(): array {
    $names = $_POST['nama_obat'] ?? [];
    $satuan = $_POST['satuan'] ?? [];
    $harga = $_POST['harga_beli'] ?? [];
    $disc = $_POST['discount'] ?? [];
    $qty = $_POST['qty'] ?? ($_POST['jumlah_masuk'] ?? []);
    $batchData = $_POST['batch_data'] ?? [];

    $items = [];
    $validSatuan = getValidSatuan();
    $totalRows = is_array($names) ? count($names) : 0;
    for ($i = 0; $i < $totalRows; $i++) {
        $nama = sanitize($names[$i] ?? '');
        if ($nama === '') continue;
        $row = [
            'nama_obat'   => $nama,
            'satuan'      => $satuan[$i] ?? '',
            'harga_beli'  => sanitizeDecimal($harga[$i] ?? 0),
            'discount'    => sanitizeDecimal($disc[$i] ?? 0),
            'qty'         => sanitizeInt($qty[$i] ?? 0),
            'batches'     => parseBatchJson($batchData[$i] ?? ''),
        ];
        if (!in_array($row['satuan'], $validSatuan, true)) $row['satuan'] = '';
        $items[] = $row;
    }
    return $items;
}

function validateFaktur(array $header, array $items): array {
    $errors = [];
    if ($header['id_pbf'] <= 0) $errors[] = 'PBF harus dipilih';
    if ($header['no_faktur'] === '') $errors[] = 'No. faktur harus diisi';
    if (!isValidDate($header['tanggal_faktur'])) $errors[] = 'Tanggal faktur tidak valid';
    if (!isValidDate($header['tanggal_masuk'])) $errors[] = 'Tanggal masuk tidak valid';
    if (empty($items)) $errors[] = 'Minimal harus ada satu item obat';

    foreach ($items as $idx => $item) {
        $row = $idx + 1;
        if ($item['nama_obat'] === '') $errors[] = "Nama obat baris {$row} wajib diisi";
        if ($item['satuan'] === '') $errors[] = "Satuan baris {$row} tidak valid";
        if ($item['harga_beli'] < 0) $errors[] = "Harga beli baris {$row} tidak valid";
        if ($item['discount'] < 0) $errors[] = "Diskon baris {$row} tidak valid";
        if ($item['discount'] > 100) $errors[] = "Diskon baris {$row} tidak boleh lebih dari 100%";
        if ($item['qty'] <= 0) $errors[] = "Qty baris {$row} harus lebih dari 0";
        if (count($item['batches']) !== (int)$item['qty']) {
            $errors[] = "Batch/expired baris {$row} harus diisi sesuai qty ({$item['qty']} data)";
        }
    }
    return $errors;
}

function handleCreateFaktur(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();

    $header = collectHeader();
    $items = collectItems();
    $errors = validateFaktur($header, $items);
    if ($errors) {
        setFlashMessage('error', implode(', ', array_slice($errors, 0, 5)));
        redirect(getFakturFormUrl());
        return;
    }

    $stokModel = new StokMasuk();
    $id = $stokModel->createFakturWithDetails($header, $items);
    if ($id) {
        $pbf = (new PBF())->findById($header['id_pbf']);
        $pbfNama = $pbf ? $pbf['nama_pbf'] : 'Unknown';
        (new LogAktivitas())->catat(getCurrentUserId(), 'Tambah Faktur', "Menambahkan faktur {$header['no_faktur']} dari PBF {$pbfNama} dengan " . count($items) . " item obat");
        setFlashMessage('success', "Faktur '{$header['no_faktur']}' berhasil ditambahkan.");
        redirect(getRedirectUrl());
    } else {
        setFlashMessage('error', 'Gagal menambahkan faktur. Pastikan no. faktur belum pernah dipakai.');
        redirect(getFakturFormUrl());
    }
}

function handleUpdateFaktur(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();

    $idFaktur = sanitizeInt($_POST['id_faktur'] ?? 0);
    if ($idFaktur <= 0) {
        setFlashMessage('error', 'ID faktur tidak valid.');
        redirect(getRedirectUrl());
        return;
    }

    $header = collectHeader();
    $items = collectItems();
    $errors = validateFaktur($header, $items);
    if ($errors) {
        setFlashMessage('error', implode(', ', array_slice($errors, 0, 5)));
        redirect(getFakturFormUrl($idFaktur));
        return;
    }

    $stokModel = new StokMasuk();
    if ($stokModel->updateFakturWithDetails($idFaktur, $header, $items)) {
        (new LogAktivitas())->catat(getCurrentUserId(), 'Edit Faktur', "Mengubah faktur {$header['no_faktur']} dengan " . count($items) . " item obat");
        setFlashMessage('success', 'Faktur berhasil diperbarui.');
        redirect(getRedirectUrl());
    } else {
        setFlashMessage('error', 'Gagal memperbarui faktur. Pastikan no. faktur belum dipakai.');
        redirect(getFakturFormUrl($idFaktur));
    }
}

function handleDeleteFaktur(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    // Revisi: Admin dan Super Admin sama-sama boleh hapus faktur.
    requireLogin();
    requireValidCSRF();

    $idFaktur = sanitizeInt($_POST['id_faktur'] ?? 0);
    if ($idFaktur <= 0) {
        setFlashMessage('error', 'ID faktur tidak valid.');
        redirect(getRedirectUrl());
        return;
    }

    $stokModel = new StokMasuk();
    $faktur = $stokModel->findFakturById($idFaktur);
    if (!$faktur) {
        setFlashMessage('error', 'Faktur tidak ditemukan.');
        redirect(getRedirectUrl());
        return;
    }

    if ($stokModel->deleteFaktur($idFaktur)) {
        (new LogAktivitas())->catat(getCurrentUserId(), 'Hapus Faktur', "Menghapus faktur {$faktur['no_faktur']} dari PBF {$faktur['nama_pbf']}");
        setFlashMessage('success', "Faktur '{$faktur['no_faktur']}' berhasil dihapus.");
    } else {
        setFlashMessage('error', 'Gagal menghapus faktur.');
    }
    redirect(getRedirectUrl());
}

function handleDetail(): void {
    $id = sanitizeInt($_GET['id'] ?? 0);
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID tidak valid']);
        exit;
    }
    $data = (new StokMasuk())->findFakturWithDetails($id);
    header('Content-Type: application/json');
    echo json_encode($data ?: ['error' => 'Data tidak ditemukan']);
    exit;
}

function handleUpdateBatch(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php'); return; }
    requireSuperAdmin();
    requireValidCSRF();

    $id = sanitizeInt($_POST['id_batch'] ?? $_POST['id_masuk'] ?? 0);
    $batch = sanitize($_POST['batch'] ?? '');
    if ($id <= 0 || $batch === '') {
        setFlashMessage('error', 'ID batch atau nomor batch tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        return;
    }

    if ((new StokMasuk())->updateBatch($id, $batch)) {
        (new LogAktivitas())->catat(getCurrentUserId(), 'Update Batch Obat', "Memperbarui No. Batch ID {$id} menjadi '{$batch}'");
        setFlashMessage('success', 'No. Batch berhasil diperbarui.');
    } else {
        setFlashMessage('error', 'Gagal memperbarui No. Batch.');
    }
    redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
}
