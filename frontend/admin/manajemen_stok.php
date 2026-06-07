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

$tanggalFakturDari = isset($_GET['tanggal_faktur_dari']) ? sanitize($_GET['tanggal_faktur_dari']) : null;
$tanggalFakturSampai = isset($_GET['tanggal_faktur_sampai']) ? sanitize($_GET['tanggal_faktur_sampai']) : null;
$tanggalMasukDari = isset($_GET['tanggal_masuk_dari']) ? sanitize($_GET['tanggal_masuk_dari']) : null;
$tanggalMasukSampai = isset($_GET['tanggal_masuk_sampai']) ? sanitize($_GET['tanggal_masuk_sampai']) : null;
$hargaMin = (isset($_GET['harga_min']) && $_GET['harga_min'] !== '') ? sanitizeDecimal($_GET['harga_min']) : null;
$hargaMax = (isset($_GET['harga_max']) && $_GET['harga_max'] !== '') ? sanitizeDecimal($_GET['harga_max']) : null;

if ($tanggalFakturDari && !isValidDate($tanggalFakturDari)) $tanggalFakturDari = null;
if ($tanggalFakturSampai && !isValidDate($tanggalFakturSampai)) $tanggalFakturSampai = null;
if ($tanggalMasukDari && !isValidDate($tanggalMasukDari)) $tanggalMasukDari = null;
if ($tanggalMasukSampai && !isValidDate($tanggalMasukSampai)) $tanggalMasukSampai = null;
if ($hargaMin !== null && $hargaMin < 0) $hargaMin = null;
if ($hargaMax !== null && $hargaMax < 0) $hargaMax = null;

$stokList  = $stokModel->getAll($filterPbf, $search, $tanggalFakturDari, $tanggalFakturSampai, $tanggalMasukDari, $tanggalMasukSampai, $hargaMin, $hargaMax);
$pbfList   = $pbfModel->getAll();
$namaObatList = $stokModel->getNamaObatList();
$flash     = getFlashMessage();
$validSatuan = ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];
$canDeleteFaktur = true;

require_once __DIR__ . '/../templates/sidebar.php';
require_once __DIR__ . '/../templates/stok_faktur_page.php';
require_once __DIR__ . '/../templates/footer.php';
