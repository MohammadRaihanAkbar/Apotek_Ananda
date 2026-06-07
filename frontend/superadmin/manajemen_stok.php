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

$flash = getFlashMessage();

$validSatuan = [
    'Tube','FLS','Strip','Sach','Box',
    'Kaleng','Pcs','Tablet','Kapsul',
    'Ampul','Supp','Ovula','Pack'
];

$canDeleteFaktur = true;

require_once __DIR__ . '/../templates/sidebar.php';
?>

<!-- BACKGROUND -->
<div class="bg-grid"></div>
<div class="bg-bubble one"></div>
<div class="bg-bubble two"></div>
<div class="bg-bubble three"></div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

*{
    font-family:'Poppins',sans-serif;
}

body{
    min-height:100vh;
    overflow-x:hidden;
    color:#0f172a;

    background:
        linear-gradient(
            135deg,
            #f8fbff 0%,
            #eef5ff 35%,
            #ffffff 100%
        );

    position:relative;
}

/* =========================
   BACKGROUND HIDUP
========================= */

body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle, rgba(59,130,246,0.22) 0%, transparent 55%),
        radial-gradient(circle, rgba(125,211,252,0.18) 0%, transparent 60%),
        radial-gradient(circle, rgba(96,165,250,0.16) 0%, transparent 55%);

    background-size:
        700px 700px,
        600px 600px,
        800px 800px;

    background-position:
        0% 0%,
        100% 100%,
        50% 50%;

    animation:moveGlow 18s linear infinite;

    filter:blur(40px);

    z-index:-4;
}

body::after{
    content:'';
    position:fixed;
    inset:0;

    backdrop-filter:blur(10px);

    background:
        rgba(255,255,255,0.05);

    z-index:-3;
}

.bg-grid{
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);

    background-size:40px 40px;

    mask-image:
        radial-gradient(circle at center, black 35%, transparent 85%);

    z-index:-2;
}

.bg-bubble{
    position:fixed;
    border-radius:50%;
    pointer-events:none;

    background:
        radial-gradient(
            circle at 30% 30%,
            rgba(255,255,255,0.95),
            rgba(255,255,255,0.08)
        );

    box-shadow:
        inset 0 0 20px rgba(255,255,255,0.9),
        0 0 40px rgba(59,130,246,0.15);

    animation:
        floating 14s ease-in-out infinite;

    z-index:-1;
}

.bg-bubble.one{
    width:220px;
    height:220px;
    top:8%;
    left:-60px;
}

.bg-bubble.two{
    width:300px;
    height:300px;
    bottom:-100px;
    right:-80px;
    animation-duration:20s;
}

.bg-bubble.three{
    width:140px;
    height:140px;
    top:45%;
    right:18%;
    animation-duration:12s;
}

@keyframes moveGlow{

    0%{
        transform:translate(0,0) rotate(0deg);
    }

    25%{
        transform:translate(60px,-40px) rotate(90deg);
    }

    50%{
        transform:translate(-30px,50px) rotate(180deg);
    }

    75%{
        transform:translate(40px,30px) rotate(270deg);
    }

    100%{
        transform:translate(0,0) rotate(360deg);
    }
}

@keyframes floating{

    0%,100%{
        transform:
            translateY(0)
            translateX(0);
    }

    50%{
        transform:
            translateY(-25px)
            translateX(15px);
    }
}

/* =========================
   WRAPPER
========================= */

.dashboard-wrapper{
    padding:35px;
    position:relative;
    z-index:10;
}

/* =========================
   GLASS
========================= */

.glass-container{
    background:rgba(255,255,255,0.42);

    border:1px solid rgba(255,255,255,0.75);

    backdrop-filter:blur(24px);
    -webkit-backdrop-filter:blur(24px);

    border-radius:32px;

    padding:30px;

    box-shadow:
        0 10px 35px rgba(15,23,42,0.07),
        inset 0 1px 0 rgba(255,255,255,0.9),
        inset 0 -1px 0 rgba(255,255,255,0.35);

    position:relative;
    overflow:hidden;
}

.glass-container::before{
    content:'';
    position:absolute;
    top:0;
    left:-120%;
    width:70%;
    height:100%;

    background:linear-gradient(
        90deg,
        transparent,
        rgba(255,255,255,0.30),
        transparent
    );

    transform:skewX(-25deg);

    animation:shine 8s linear infinite;
}

.glass-inner{
    background:rgba(255,255,255,0.32);

    border:1px solid rgba(255,255,255,0.65);

    backdrop-filter:blur(18px);

    border-radius:26px;

    padding:24px;

    position:relative;
}

@keyframes shine{
    0%{ left:-120%; }
    100%{ left:150%; }
}

/* =========================
   ALERT
========================= */

.alert{
    border:none !important;

    border-radius:18px !important;

    background:rgba(255,255,255,0.58) !important;

    backdrop-filter:blur(20px);

    color:#0f172a !important;

    box-shadow:
        0 10px 25px rgba(15,23,42,0.08);

    padding:16px 18px !important;
}

/* =========================
   TABLE
========================= */

.table{
    border-collapse:separate !important;
    border-spacing:0 12px !important;
    background:transparent !important;
}

.table thead th{
    background:rgba(255,255,255,0.65) !important;

    border:none !important;

    color:#0f172a !important;

    font-weight:700;

    padding:18px !important;

    backdrop-filter:blur(12px);
}

.table thead th:first-child{
    border-radius:18px 0 0 18px;
}

.table thead th:last-child{
    border-radius:0 18px 18px 0;
}

.table tbody tr{
    transition:0.25s ease;
}

.table tbody tr:hover{
    transform:
        translateY(-4px)
        scale(1.005);
}

.table tbody td{
    background:rgba(255,255,255,0.45) !important;

    border:none !important;

    padding:18px 15px !important;

    vertical-align:middle;

    backdrop-filter:blur(12px);

    box-shadow:
        0 8px 20px rgba(15,23,42,0.05);
}

.table tbody td:first-child{
    border-radius:18px 0 0 18px;
}

.table tbody td:last-child{
    border-radius:0 18px 18px 0;
}

/* =========================
   BUTTON
========================= */

.btn,
button{
    border:none !important;

    border-radius:16px !important;

    font-weight:600 !important;

    transition:0.25s ease !important;

    position:relative;
    overflow:hidden;

    box-shadow:
        0 8px 25px rgba(37,99,235,0.18);
}

.btn::before,
button::before{
    content:'';

    position:absolute;
    top:0;
    left:-100%;

    width:100%;
    height:100%;

    background:
        linear-gradient(
            120deg,
            transparent,
            rgba(255,255,255,0.35),
            transparent
        );

    transition:0.6s;
}

.btn:hover::before,
button:hover::before{
    left:120%;
}

.btn:hover,
button:hover{
    transform:
        translateY(-2px)
        scale(1.02);
}

.btn-primary{
    background:
        linear-gradient(
            135deg,
            #60a5fa,
            #2563eb
        ) !important;
}

.btn-success{
    background:
        linear-gradient(
            135deg,
            #7dd3fc,
            #3b82f6
        ) !important;
}

.btn-danger{
    background:
        linear-gradient(
            135deg,
            #93c5fd,
            #2563eb
        ) !important;
}

.btn-warning{
    background:
        linear-gradient(
            135deg,
            #dbeafe,
            #60a5fa
        ) !important;

    color:#0f172a !important;
}

/* =========================
   INPUT
========================= */

input,
select,
textarea{
    border:none !important;

    border-radius:18px !important;

    padding:13px 16px !important;

    background:rgba(255,255,255,0.60) !important;

    backdrop-filter:blur(14px);

    color:#0f172a !important;

    box-shadow:
        0 6px 20px rgba(15,23,42,0.06);

    transition:0.25s ease;
}

input:focus,
select:focus,
textarea:focus{
    outline:none !important;

    transform:translateY(-1px);

    box-shadow:
        0 0 0 4px rgba(96,165,250,0.22),
        0 12px 24px rgba(59,130,246,0.12) !important;
}

/* =========================
   MODAL
========================= */

.modal-content{
    border:none !important;

    border-radius:28px !important;

    background:rgba(255,255,255,0.72) !important;

    backdrop-filter:blur(30px);
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:992px){

    .dashboard-wrapper{
        padding:18px;
    }

    .glass-container{
        padding:18px;
    }

    .glass-inner{
        padding:18px;
    }

    .table{
        font-size:13px;
    }
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
