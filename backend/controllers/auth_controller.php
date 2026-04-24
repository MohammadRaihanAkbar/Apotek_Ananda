<?php
/**
 * Controller: Auth (Login & Logout) - Apotek Ananda Jadimulya
 * 
 * Menangani proses autentikasi dengan keamanan:
 * - CAPTCHA pada login
 * - CSRF protection
 * - Rate limiting (brute-force protection)
 * - Session hijacking prevention
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../helpers/captcha_helper.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/log_aktivitas.php';

initSecureSession();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        redirect(BASE_URL . '/frontend/auth/login.php');
        break;
}

/**
 * Proses login
 */
function handleLogin(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/auth/login.php');
        return;
    }
    
    // 1. Validasi CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Sesi tidak valid. Silakan muat ulang halaman.');
        redirect(BASE_URL . '/frontend/auth/login.php');
        return;
    }
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaInput = trim($_POST['captcha'] ?? '');
    $ip = getClientIP();
    
    // 2. Validasi input kosong
    if (empty($username) || empty($password)) {
        setFlashMessage('error', 'Username dan password harus diisi.');
        redirect(BASE_URL . '/frontend/auth/login.php');
        return;
    }
    
    $userModel = new User();
    
    // 3. Cek rate limiting
    if ($userModel->isLockedOut($ip, $username)) {
        $lockoutMinutes = (defined('LOGIN_LOCKOUT_TIME') ? LOGIN_LOCKOUT_TIME : 900) / 60;
        setFlashMessage('error', "Terlalu banyak percobaan login. Coba lagi dalam {$lockoutMinutes} menit.");
        redirect(BASE_URL . '/frontend/auth/login.php');
        return;
    }
    
    // 4. Validasi CAPTCHA
    if (defined('CAPTCHA_ENABLED') && CAPTCHA_ENABLED) {
        if (!validateCaptcha($captchaInput)) {
            setFlashMessage('error', 'Kode CAPTCHA salah. Silakan coba lagi.');
            redirect(BASE_URL . '/frontend/auth/login.php');
            return;
        }
    }
    
    // 5. Cari user di database
    $user = $userModel->findByUsername($username);
    
    if (!$user || !$userModel->verifyPassword($password, $user['password'])) {
        // Catat percobaan gagal
        $userModel->recordLoginAttempt($ip, $username);
        setFlashMessage('error', 'Username atau password salah.');
        redirect(BASE_URL . '/frontend/auth/login.php');
        return;
    }
    
    // 6. Login berhasil!
    $userModel->clearLoginAttempts($ip, $username);
    setUserSession($user);
    
    // 7. Catat log aktivitas
    $logModel = new LogAktivitas();
    $logModel->catat($user['id_user'], 'Login', "User {$user['nama_lengkap']} login ke sistem.");
    
    // 8. Redirect berdasarkan role
    if ($user['role'] === 'super_admin') {
        redirect(BASE_URL . '/frontend/superadmin/dashboard.php');
    } else {
        redirect(BASE_URL . '/frontend/admin/dashboard.php');
    }
}

/**
 * Proses logout
 */
function handleLogout(): void {
    // Catat log sebelum destroy session
    if (isLoggedIn()) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Logout', "User " . getCurrentNamaLengkap() . " logout dari sistem.");
    }
    
    destroySession();
    setcookie(session_name(), '', time() - 3600, '/');
    
    // Mulai session baru untuk flash message
    session_start();
    setFlashMessage('success', 'Anda berhasil logout.');
    redirect(BASE_URL . '/frontend/auth/login.php');
}
