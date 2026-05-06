<?php
/**
 * CAPTCHA Helper - Apotek Ananda Jadimulya
 * 
 * Math CAPTCHA sederhana (e.g. "3 + 5 = ?").
 * Tidak memerlukan GD Library.
 */

/**
 * Generate soal math CAPTCHA (dua angka 1-9, operator + atau -)
 * Simpan jawaban di session, return soal sebagai string.
 *
 * @return array ['question' => string, 'answer' => int]
 */
function generateMathCaptcha(): array {
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    
    // Gunakan penjumlahan atau pengurangan
    $ops = ['+', '-'];
    $op = $ops[random_int(0, 1)];
    
    if ($op === '-' && $a < $b) {
        // Pastikan hasil tidak negatif
        [$a, $b] = [$b, $a];
    }
    
    $answer = ($op === '+') ? ($a + $b) : ($a - $b);
    $question = "{$a} {$op} {$b} = ?";
    
    return ['question' => $question, 'answer' => $answer];
}

/**
 * Generate dan simpan CAPTCHA ke session, return soal.
 *
 * @return string Soal CAPTCHA (e.g. "3 + 5 = ?")
 */
function generateAndStoreCaptcha(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $captcha = generateMathCaptcha();
    $_SESSION['captcha_code'] = (string) $captcha['answer'];
    $_SESSION['captcha_time'] = time();
    
    return $captcha['question'];
}

/**
 * Simpan kode CAPTCHA ke session (legacy compatibility)
 *
 * @param string $code Kode CAPTCHA
 */
function storeCaptchaCode(string $code): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['captcha_code'] = strtoupper($code);
    $_SESSION['captcha_time'] = time();
}

/**
 * Validasi input CAPTCHA terhadap jawaban di session
 *
 * @param string $input Input user
 * @return bool True jika cocok
 */
function validateCaptcha(string $input): bool {
    if (!defined('CAPTCHA_ENABLED') || !CAPTCHA_ENABLED) {
        return true; // Skip jika CAPTCHA disabled
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($input) || !isset($_SESSION['captcha_code'])) {
        return false;
    }
    
    // CAPTCHA expired setelah 5 menit
    if (isset($_SESSION['captcha_time']) && (time() - $_SESSION['captcha_time']) > 300) {
        unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);
        return false;
    }
    
    $valid = trim($input) === $_SESSION['captcha_code'];
    
    // Hapus CAPTCHA setelah validasi (one-time use)
    unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);
    
    return $valid;
}

/**
 * API endpoint: return JSON with new captcha question.
 * Called via AJAX for refresh.
 */
function apiRefreshCaptcha(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $question = generateAndStoreCaptcha();
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo json_encode(['question' => $question]);
    exit;
}
