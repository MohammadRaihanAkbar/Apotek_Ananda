<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();

$pageTitle = 'Manajemen Stok';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../backend/models/stok_masuk.php';
require_once __DIR__ . '/../../backend/models/pbf.php';

$stokModel = new StokMasuk();
$pbfModel  = new PBF();
$filterPbf = isset($_GET['pbf']) ? sanitizeInt($_GET['pbf']) : null;
$search    = isset($_GET['search']) ? sanitize($_GET['search']) : null;
$stokList  = $stokModel->getAll($filterPbf, $search);
$pbfList   = $pbfModel->getAll();
$namaObatList = $stokModel->getNamaObatList();
$flash     = getFlashMessage();
$validSatuan = ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];
$canDeleteFaktur = true;

require_once __DIR__ . '/../templates/sidebar.php';
require_once __DIR__ . '/../templates/stok_faktur_page.php';
require_once __DIR__ . '/../templates/footer.php';
