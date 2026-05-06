<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

require_once __DIR__ . '/../../backend/models/stok_masuk.php';
$id = isset($_GET['id']) ? sanitizeInt($_GET['id']) : 0;
$faktur = $id > 0 ? (new StokMasuk())->findFakturWithDetails($id) : null;
if (!$faktur) {
    setFlashMessage('error', 'Faktur tidak ditemukan.');
    redirect(BASE_URL . '/frontend/superadmin/manajemen_stok.php');
}

$pageTitle = 'Detail Faktur';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/sidebar.php';
require_once __DIR__ . '/../templates/detail_faktur_page.php';
require_once __DIR__ . '/../templates/footer.php';
