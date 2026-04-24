<?php
/**
 * Controller: Admin - Apotek Ananda Jadimulya
 * CRUD akun admin. Akses: Super Admin SAJA.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/log_aktivitas.php';

initSecureSession();
requireSuperAdmin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':  handleCreate(); break;
    case 'update':  handleUpdate(); break;
    case 'delete':  handleDelete(); break;
    default:
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
        break;
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php'); return;
    }
    requireValidCSRF();
    
    $namaLengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $username    = sanitize($_POST['username'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    
    // Validasi
    $errors = [];
    if (empty($namaLengkap)) $errors[] = 'Nama lengkap harus diisi';
    if (empty($username)) $errors[] = 'Username harus diisi';
    if (strlen($username) < 4) $errors[] = 'Username minimal 4 karakter';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username hanya boleh huruf, angka, dan underscore';
    if (empty($password)) $errors[] = 'Password harus diisi';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
    if ($password !== $confirmPass) $errors[] = 'Konfirmasi password tidak cocok';
    
    if (!empty($errors)) {
        setFlashMessage('error', implode('. ', $errors));
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
        return;
    }
    
    $userModel = new User();
    
    if ($userModel->usernameExists($username)) {
        setFlashMessage('error', 'Username sudah digunakan.');
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
        return;
    }
    
    if ($userModel->createAdmin($namaLengkap, $username, $password)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Tambah Akun Admin', 
            "Menambahkan akun admin baru: {$namaLengkap} ({$username})");
        setFlashMessage('success', "Akun admin '{$namaLengkap}' berhasil ditambahkan.");
    } else {
        setFlashMessage('error', 'Gagal menambahkan akun admin.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php'); return;
    }
    requireValidCSRF();
    
    $id          = sanitizeInt($_POST['id_user'] ?? 0);
    $namaLengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $username    = sanitize($_POST['username'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    
    if ($id <= 0 || empty($namaLengkap) || empty($username)) {
        setFlashMessage('error', 'Data tidak lengkap.');
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
        return;
    }
    
    // Jika password diisi, validasi
    if (!empty($password)) {
        if (strlen($password) < 6) {
            setFlashMessage('error', 'Password minimal 6 karakter.');
            redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
            return;
        }
        if ($password !== $confirmPass) {
            setFlashMessage('error', 'Konfirmasi password tidak cocok.');
            redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
            return;
        }
    }
    
    $userModel = new User();
    
    if ($userModel->usernameExists($username, $id)) {
        setFlashMessage('error', 'Username sudah digunakan.');
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
        return;
    }
    
    $passwordToUpdate = !empty($password) ? $password : null;
    
    if ($userModel->updateAdmin($id, $namaLengkap, $username, $passwordToUpdate)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Edit Akun Admin', 
            "Mengubah akun admin ID {$id}: {$namaLengkap} ({$username})");
        setFlashMessage('success', 'Data admin berhasil diperbarui.');
    } else {
        setFlashMessage('error', 'Gagal memperbarui data admin.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php'); return;
    }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_user'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID admin tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
        return;
    }
    
    // Jangan bisa hapus diri sendiri
    if ($id === getCurrentUserId()) {
        setFlashMessage('error', 'Tidak dapat menghapus akun sendiri.');
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
        return;
    }
    
    $userModel = new User();
    $admin = $userModel->findById($id);
    
    if (!$admin) {
        setFlashMessage('error', 'Admin tidak ditemukan.');
        redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
        return;
    }
    
    if ($userModel->deleteAdmin($id)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Hapus Akun Admin', 
            "Menghapus akun admin: {$admin['nama_lengkap']} ({$admin['username']})");
        setFlashMessage('success', "Akun admin '{$admin['nama_lengkap']}' berhasil dihapus.");
    } else {
        setFlashMessage('error', 'Gagal menghapus akun admin.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/kelola_admin.php');
}
