<?php
/**
 * Controller: PBF - Apotek Ananda Jadimulya
 * CRUD PBF (Pedagang Besar Farmasi). Terintegrasi di halaman Manajemen Stok.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../models/pbf.php';
require_once __DIR__ . '/../models/log_aktivitas.php';

initSecureSession();
requireLogin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        handleCreate();
        break;
    case 'update':
        handleUpdate();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'list':
        handleList();
        break;
    default:
        echo json_encode(['error' => 'Aksi tidak valid']);
        break;
}

function getRedirectUrl(): string {
    $role = getCurrentRole();
    return BASE_URL . '/frontend/' . ($role === 'super_admin' ? 'superadmin' : 'admin') . '/manajemen_stok.php';
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();
    
    $namaPbf = sanitize($_POST['nama_pbf'] ?? '');
    
    if (empty($namaPbf)) {
        setFlashMessage('error', 'Nama PBF harus diisi.');
        redirect(getRedirectUrl());
        return;
    }
    
    $pbfModel = new PBF();
    
    if ($pbfModel->nameExists($namaPbf)) {
        setFlashMessage('error', 'Nama PBF sudah ada.');
        redirect(getRedirectUrl());
        return;
    }
    
    $id = $pbfModel->create($namaPbf, getCurrentUserId());
    
    if ($id) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Tambah PBF', "Menambahkan PBF baru: {$namaPbf}");
        setFlashMessage('success', "PBF '{$namaPbf}' berhasil ditambahkan.");
    } else {
        setFlashMessage('error', 'Gagal menambahkan PBF.');
    }
    
    redirect(getRedirectUrl());
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_pbf'] ?? 0);
    $namaPbf = sanitize($_POST['nama_pbf'] ?? '');
    
    if ($id <= 0 || empty($namaPbf)) {
        setFlashMessage('error', 'Data tidak lengkap.');
        redirect(getRedirectUrl());
        return;
    }
    
    $pbfModel = new PBF();
    
    if ($pbfModel->nameExists($namaPbf, $id)) {
        setFlashMessage('error', 'Nama PBF sudah ada.');
        redirect(getRedirectUrl());
        return;
    }
    
    if ($pbfModel->update($id, $namaPbf)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Edit PBF', "Mengubah data PBF ID {$id} menjadi: {$namaPbf}");
        setFlashMessage('success', 'Data PBF berhasil diperbarui.');
    } else {
        setFlashMessage('error', 'Gagal memperbarui PBF.');
    }
    
    redirect(getRedirectUrl());
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(getRedirectUrl()); return; }
    requireSuperAdmin(); // Hanya Super Admin
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
    
    if ($pbfModel->delete($id)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Hapus PBF', "Menghapus PBF: {$pbf['nama_pbf']} (beserta {$stokCount} data obat)");
        setFlashMessage('success', "PBF '{$pbf['nama_pbf']}' berhasil dihapus.");
    } else {
        setFlashMessage('error', 'Gagal menghapus PBF.');
    }
    
    redirect(getRedirectUrl());
}

function handleList(): void {
    header('Content-Type: application/json');
    $pbfModel = new PBF();
    echo json_encode(['data' => $pbfModel->getAll()]);
    exit;
}
