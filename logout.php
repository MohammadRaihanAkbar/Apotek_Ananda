<?php
/**
 * Logout Handler - Apotek Ananda Jadimulya
 */

require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/helpers/session_helper.php';
require_once __DIR__ . '/backend/models/log_aktivitas.php';

initSecureSession();

// Catat log sebelum destroy
if (isLoggedIn()) {
    $logModel = new LogAktivitas();
    $logModel->catat(getCurrentUserId(), 'Logout', 'User ' . getCurrentNamaLengkap() . ' logout dari sistem.');
}

destroySession();

// Mulai session baru untuk flash message
session_start();
setFlashMessage('success', 'Anda berhasil logout.');
header('Location: ' . BASE_URL . '/frontend/auth/login.php');
exit;
