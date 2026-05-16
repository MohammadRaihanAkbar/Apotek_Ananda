<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

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
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

*{ font-family:'Poppins',sans-serif; }

body{
    min-height:100vh;
    background: linear-gradient(135deg,#cfe9ff 0%,#ffffff 45%,#dbeafe 100%);
    color:#0f172a;
}

/* WRAPPER */
.dashboard-wrapper{ padding:40px; }

/* GLASS UTAMA */
.glass-container{
    background: rgba(255,255,255,0.60);
    backdrop-filter: blur(26px);
    border:1px solid rgba(255,255,255,0.9);
    border-radius:28px;
    padding:28px;
    box-shadow:0 15px 45px rgba(15,23,42,0.12);
}

/* GLASS DALAM */
.glass-inner{
    background: rgba(255,255,255,0.55);
    backdrop-filter: blur(22px);
    border:1px solid rgba(255,255,255,0.85);
    border-radius:24px;
    padding:24px;
    box-shadow:0 10px 35px rgba(15,23,42,0.10);
}

/* ALERT */
.alert{
    border:none !important;
    border-radius:18px !important;
    background:rgba(255,255,255,0.75) !important;
    backdrop-filter:blur(18px);
    color:#0f172a !important;
    box-shadow:0 8px 25px rgba(15,23,42,0.08);
}

/* TABLE */
.table{
    border-radius:20px;
    overflow:hidden;
    background:transparent !important;
}

.table thead th{
    background:rgba(219,234,254,0.85) !important;
    color:#0f172a !important;
    border:none !important;
    padding:16px !important;
    font-weight:600;
}

.table tbody td{
    background:rgba(255,255,255,0.65) !important;
    border-color:rgba(15,23,42,0.06) !important;
    padding:14px !important;
}

.table tbody tr:hover{
    background:rgba(219,234,254,0.35) !important;
    transform:scale(1.01);
    transition:0.2s ease;
}

/* BUTTON */
.btn, button{
    border:none !important;
    border-radius:16px !important;
    font-weight:600 !important;
    transition:0.25s ease !important;
    box-shadow:0 8px 25px rgba(15,23,42,0.12);
}

.btn-primary{ background:linear-gradient(135deg,#60a5fa,#3b82f6) !important; }
.btn-success{ background:linear-gradient(135deg,#93c5fd,#60a5fa) !important; }
.btn-danger{ background:linear-gradient(135deg,#93c5fd,#3b82f6) !important; }
.btn-warning{
    background:linear-gradient(135deg,#bfdbfe,#60a5fa) !important;
    color:#0f172a !important;
}

/* INPUT */
input, select, textarea{
    border:none !important;
    border-radius:18px !important;
    padding:12px 16px !important;
    background:rgba(255,255,255,0.7) !important;
    backdrop-filter:blur(12px);
    color:#0f172a !important;
    box-shadow:0 6px 20px rgba(15,23,42,0.08);
}

input:focus, select:focus, textarea:focus{
    outline:none !important;
    box-shadow:0 0 0 4px rgba(96,165,250,0.25) !important;
}

/* MODAL */
.modal-content{
    border:none !important;
    border-radius:28px !important;
    background:rgba(255,255,255,0.75) !important;
    backdrop-filter:blur(30px);
}
</style>

<div class="dashboard-wrapper">

    <?php if ($flash): ?>
        <div class="alert alert-info mb-4">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="glass-container">
        <div class="glass-inner">
            <?php require_once __DIR__ . '/../templates/stok_faktur_page.php'; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
