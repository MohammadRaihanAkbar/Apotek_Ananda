<?php
/**
 * Logout Handler - Apotek Ananda Jadimulya
 *
 * Logout sekarang wajib lewat POST + CSRF dari sidebar.
 * File ini hanya menjadi fallback kalau URL /logout.php dibuka langsung.
 */

require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/helpers/session_helper.php';

initSecureSession();

setFlashMessage('error', 'Silakan gunakan tombol Logout dari menu aplikasi.');

if (isLoggedIn()) {
    $role = getCurrentRole();
    $target = $role === 'super_admin'
        ? BASE_URL . '/frontend/superadmin/dashboard.php'
        : BASE_URL . '/frontend/admin/dashboard.php';
    redirect($target);
}

redirect(BASE_URL . '/frontend/auth/login.php');
