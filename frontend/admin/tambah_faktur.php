<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();
if (isSuperAdmin()) { redirect(BASE_URL . '/frontend/superadmin/tambah_faktur.php' . (!empty($_GET['id']) ? '?id=' . sanitizeInt($_GET['id']) : '')); }

require_once __DIR__ . '/../../backend/models/stok_masuk.php';
require_once __DIR__ . '/../../backend/models/pbf.php';

$stokModel = new StokMasuk();
$pbfModel = new PBF();
$id = isset($_GET['id']) ? sanitizeInt($_GET['id']) : 0;
$faktur = $id > 0 ? $stokModel->findFakturWithDetails($id) : null;
if ($id > 0 && !$faktur) {
    setFlashMessage('error', 'Faktur tidak ditemukan.');
    redirect(BASE_URL . '/frontend/admin/manajemen_stok.php');
}

$pageTitle = $faktur ? 'Edit Faktur' : 'Tambah Faktur';
require_once __DIR__ . '/../templates/header.php';
$pbfList = $pbfModel->getAll();
$namaObatList = $stokModel->getNamaObatList();
$flash = getFlashMessage();
$validSatuan = ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];
require_once __DIR__ . '/../templates/sidebar.php';
require_once __DIR__ . '/../templates/faktur_form_page.php';
require_once __DIR__ . '/../templates/footer.php';
