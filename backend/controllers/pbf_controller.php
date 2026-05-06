<?php
/**
 * Controller: PBF - CRUD master PBF khusus Super Admin.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../models/pbf.php';
require_once __DIR__ . '/../models/log_aktivitas.php';

initSecureSession();
requireSuperAdmin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create': handleCreate(); break;
    case 'update': handleUpdate(); break;
    case 'delete': handleDelete(); break;
    case 'list':   handleList(); break;
    default: redirect(getRedirectUrl()); break;
}

function getRedirectUrl(): string {
    return BASE_URL . '/frontend/superadmin/pbf.php';
}

function collectPbfData(): array {
    return [
        'nama_pbf'      => sanitize($_POST['nama_pbf'] ?? ''),
        'alamat'        => sanitize($_POST['alamat'] ?? ''),
        'no_telepon'    => sanitize($_POST['no_telepon'] ?? ''),
        'kontak_person' => sanitize($_POST['kontak_person'] ?? ''),
        'keterangan'    => sanitize($_POST['keterangan'] ?? ''),
    ];
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();

    $data = collectPbfData();
    if ($data['nama_pbf'] === '') {
        setFlashMessage('error', 'Nama PBF harus diisi.');
        redirect(getRedirectUrl());
        return;
    }

    $pbfModel = new PBF();
    if ($pbfModel->nameExists($data['nama_pbf'])) {
        setFlashMessage('error', 'Nama PBF sudah ada.');
        redirect(getRedirectUrl());
        return;
    }

    $id = $pbfModel->create($data, getCurrentUserId());
    if ($id) {
        (new LogAktivitas())->catat(getCurrentUserId(), 'Tambah PBF', "Menambahkan PBF baru: {$data['nama_pbf']}");
        setFlashMessage('success', "PBF '{$data['nama_pbf']}' berhasil ditambahkan.");
    } else {
        setFlashMessage('error', 'Gagal menambahkan PBF.');
    }
    redirect(getRedirectUrl());
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();

    $id = sanitizeInt($_POST['id_pbf'] ?? 0);
    $data = collectPbfData();

    if ($id <= 0 || $data['nama_pbf'] === '') {
        setFlashMessage('error', 'Data PBF tidak lengkap.');
        redirect(getRedirectUrl());
        return;
    }

    $pbfModel = new PBF();
    if ($pbfModel->nameExists($data['nama_pbf'], $id)) {
        setFlashMessage('error', 'Nama PBF sudah ada.');
        redirect(getRedirectUrl());
        return;
    }

    if ($pbfModel->update($id, $data)) {
        (new LogAktivitas())->catat(getCurrentUserId(), 'Edit PBF', "Mengubah data PBF: {$data['nama_pbf']}");
        setFlashMessage('success', 'Data PBF berhasil diperbarui.');
    } else {
        setFlashMessage('error', 'Gagal memperbarui PBF.');
    }
    redirect(getRedirectUrl());
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();

    $id = sanitizeInt($_POST['id_pbf'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID PBF tidak valid.');
        redirect(getRedirectUrl());
        return;
    }

    $pbfModel = new PBF();
    $pbf = $pbfModel->findById($id);
    if (!$pbf) {
        setFlashMessage('error', 'PBF tidak ditemukan.');
        redirect(getRedirectUrl());
        return;
    }

    $stokCount = $pbfModel->countStokByPBF($id);
    if ($stokCount > 0) {
        setFlashMessage('error', "PBF '{$pbf['nama_pbf']}' tidak bisa dihapus karena sudah dipakai di {$stokCount} faktur.");
        redirect(getRedirectUrl());
        return;
    }

    if ($pbfModel->delete($id)) {
        (new LogAktivitas())->catat(getCurrentUserId(), 'Hapus PBF', "Menghapus PBF: {$pbf['nama_pbf']}");
        setFlashMessage('success', "PBF '{$pbf['nama_pbf']}' berhasil dihapus.");
    } else {
        setFlashMessage('error', 'Gagal menghapus PBF.');
    }
    redirect(getRedirectUrl());
}

function handleList(): void {
    header('Content-Type: application/json');
    echo json_encode(['data' => (new PBF())->getAll()]);
    exit;
}
