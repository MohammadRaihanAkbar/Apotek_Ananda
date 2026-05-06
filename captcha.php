<?php
/**
 * CAPTCHA Endpoint - Apotek Ananda Jadimulya
 * Mengembalikan soal math CAPTCHA baru (JSON).
 */
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/helpers/captcha_helper.php';

// Jika request refresh (AJAX), return JSON
if (isset($_GET['refresh'])) {
    apiRefreshCaptcha();
    exit;
}

// Legacy fallback: generate dan return JSON
apiRefreshCaptcha();
