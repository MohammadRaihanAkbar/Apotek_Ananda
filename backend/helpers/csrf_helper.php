<?php
/**
 * CSRF Helper - Apotek Ananda Jadimulya
 * 
 * Cross-Site Request Forgery protection.
 * Generate dan validasi token CSRF pada setiap form submission.
 */

/**
 * Generate CSRF token baru dan simpan ke session
 * 
 * @return string Token CSRF
 */
function generateCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    
    return $token;
}

/**
 * Dapatkan CSRF token yang sedang aktif, atau buat baru
 * 
 * @return string Token CSRF
 */
function getCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Buat token baru jika belum ada atau sudah expired (30 menit)
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) 
        || (time() - $_SESSION['csrf_token_time']) > 1800) {
        return generateCSRFToken();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Render hidden input field untuk CSRF token
 * 
 * @return string HTML hidden input
 */
function csrfField(): string {
    $token = getCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validasi CSRF token dari form submission
 * 
 * @param string|null $token Token dari POST request
 * @return bool True jika valid
 */
function validateCSRFToken(?string $token = null): bool {
    if (!defined('CSRF_ENABLED') || !CSRF_ENABLED) {
        return true; // Skip jika CSRF disabled
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Gunakan hash_equals untuk mencegah timing attack
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    
    // Regenerate token setelah validasi (one-time use)
    if ($valid) {
        generateCSRFToken();
    }
    
    return $valid;
}

/**
 * Validasi CSRF dan redirect jika gagal
 */
function requireValidCSRF(): void {
    if (!validateCSRFToken()) {
        if (function_exists('setFlashMessage')) {
            setFlashMessage('error', 'Sesi tidak valid. Silakan coba lagi.');
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? (defined('BASE_URL') ? BASE_URL : '/');
        header("Location: $referer");
        exit;
    }
}
