<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/csrf_helper.php';

// Inisialisasi session otomatis
initSecureSession();

/**
 * Session Helper - Apotek Ananda Jadimulya
 * 
 * Mengelola session management, autentikasi, dan otorisasi.
 * Menerapkan keamanan session: regenerate ID, httponly cookies, secure flags.
 */

/**
 * Inisialisasi session dengan konfigurasi keamanan
 */
function initSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Konfigurasi cookie session yang aman
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Set session lifetime
        $lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 3600;
        ini_set('session.gc_maxlifetime', $lifetime);
        
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly'  => true,
            'samesite'  => 'Strict'
        ]);
        
        session_start();
        
        // Regenerasi session ID untuk mencegah fixation attack
        if (!isset($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
        }
        
        // Cek session timeout
        if (isset($_SESSION['_last_activity'])) {
            $elapsed = time() - $_SESSION['_last_activity'];
            if ($elapsed > $lifetime) {
                destroySession();
                return;
            }
        }
        $_SESSION['_last_activity'] = time();
    }
}

/**
 * Set data user ke session setelah login berhasil
 */
function setUserSession(array $user): void {
    session_regenerate_id(true); // Regenerasi ID setelah login
    $_SESSION['user_id']    = $user['id_user'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['logged_in']  = true;
    $_SESSION['login_time'] = time();
    $_SESSION['_last_activity'] = time();
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $_SESSION['ip_address'] = getClientIP();
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn(): bool {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Cek apakah user adalah Super Admin
 */
function isSuperAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

/**
 * Cek apakah user adalah Admin
 */
function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Dapatkan ID user yang sedang login
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Dapatkan username yang sedang login
 */
function getCurrentUsername(): ?string {
    return $_SESSION['username'] ?? null;
}

/**
 * Dapatkan nama lengkap yang sedang login
 */
function getCurrentNamaLengkap(): ?string {
    return $_SESSION['nama_lengkap'] ?? null;
}

/**
 * Dapatkan role yang sedang login
 */
function getCurrentRole(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Guard: Paksa user harus login, redirect jika belum
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Silakan login terlebih dahulu.');
        redirect(BASE_URL . '/frontend/auth/login.php');
        exit;
    }
    
    // Validasi session hijacking: cek user agent & IP
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        destroySession();
        redirect(BASE_URL . '/frontend/auth/login.php');
        exit;
    }
}

/**
 * Guard: Paksa user harus Super Admin
 */
function requireSuperAdmin(): void {
    requireLogin();
    if (!isSuperAdmin()) {
        setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini.');
        $redirectUrl = BASE_URL . '/frontend/admin/dashboard.php';
        redirect($redirectUrl);
        exit;
    }
}

/**
 * Guard: Hanya Super Admin ATAU Admin yang boleh akses
 */
function requireAdminOrSuperAdmin(): void {
    requireLogin();
    if (!isSuperAdmin() && !isAdmin()) {
        setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini.');
        redirect(BASE_URL . '/frontend/auth/login.php');
        exit;
    }
}

/**
 * Destroy session secara aman
 */
function destroySession(): void {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Set flash message (pesan sekali tampil)
 */
function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Ambil flash message dan hapus dari session
 */
function getFlashMessage(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirect ke URL tertentu
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Dapatkan IP address client
 */
function getClientIP(): string {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Sanitize input string (prevent XSS)
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize integer input
 */
function sanitizeInt($input): int {
    return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitize decimal/float input
 */
function sanitizeDecimal($input): float {
    return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Validate date format (Y-m-d)
 */
function isValidDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Format angka ke format Rupiah
 */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format tanggal ke format Indonesia
 */
function formatTanggal(string $date): string {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $d = new DateTime($date);
    return $d->format('d') . ' ' . $bulan[(int)$d->format('m')] . ' ' . $d->format('Y');
}
