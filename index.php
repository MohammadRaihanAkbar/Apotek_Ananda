<?php
/**
 * Entry Point - Apotek Ananda Jadimulya
 * Redirect ke halaman login atau dashboard sesuai status login.
 */

require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/helpers/session_helper.php';

initSecureSession();

if (isLoggedIn()) {
    if (isSuperAdmin()) {
        header('Location: ' . BASE_URL . '/frontend/superadmin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/frontend/admin/dashboard.php');
    }
} else {
    header('Location: ' . BASE_URL . '/frontend/auth/login.php');
}
exit;
