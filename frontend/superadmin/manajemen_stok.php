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

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

html,
body{
    overflow-x:hidden;
}

body{
    min-height:100vh;
    color:#0f172a;
    position:relative;

    background:
        linear-gradient(
            135deg,
            #f5f9ff 0%,
            #edf4ff 45%,
            #ffffff 100%
        );
}

/* =========================
   BACKGROUND
========================= */

body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle at 20% 20%, rgba(59,130,246,.18), transparent 28%),
        radial-gradient(circle at 80% 30%, rgba(125,211,252,.18), transparent 28%),
        radial-gradient(circle at 50% 80%, rgba(96,165,250,.14), transparent 30%);

    filter:blur(70px);

    animation:bgMove 18s linear infinite;

    z-index:-5;
}

body::after{
    content:'';
    position:fixed;
    inset:0;

    background:rgba(255,255,255,.05);
    backdrop-filter:blur(10px);

    z-index:-4;
}

@keyframes bgMove{

    0%{
        transform:translate(0,0) rotate(0deg);
    }

    50%{
        transform:translate(40px,-30px) rotate(180deg);
    }

    100%{
        transform:translate(0,0) rotate(360deg);
    }
}

.bg-grid{
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px);

    background-size:40px 40px;

    mask-image:
        radial-gradient(circle at center, black 35%, transparent 85%);

    z-index:-3;
}

.bg-bubble{
    position:fixed;
    border-radius:50%;

    background:
        radial-gradient(
            circle at 30% 30%,
            rgba(255,255,255,.95),
            rgba(255,255,255,.08)
        );

    filter:blur(10px);

    animation:floating 14s ease-in-out infinite;

    z-index:-2;
}

.bg-bubble.one{
    width:240px;
    height:240px;
    top:5%;
    left:-80px;
}

.bg-bubble.two{
    width:320px;
    height:320px;
    right:-120px;
    bottom:-120px;
    animation-duration:18s;
}

@keyframes floating{

    0%,100%{
        transform:translateY(0) translateX(0);
    }

    50%{
        transform:translateY(-25px) translateX(15px);
    }
}

/* =========================
   WRAPPER
========================= */

.dashboard-wrapper{
    padding:24px;
    position:relative;
    z-index:10;
}

/* =========================
   GLASS
========================= */

.glass-container{
    position:relative;
    overflow:hidden;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.62),
            rgba(255,255,255,.30)
        );

    border:1px solid rgba(255,255,255,.75);

    backdrop-filter:blur(24px);
    -webkit-backdrop-filter:blur(24px);

    border-radius:30px;

    padding:24px;

    box-shadow:
        0 10px 35px rgba(15,23,42,.08),
        inset 0 1px 0 rgba(255,255,255,.9);
}

.glass-container::before{
    content:'';

    position:absolute;
    top:0;
    left:-120%;

    width:70%;
    height:100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.28),
            transparent
        );

    transform:skewX(-25deg);

    animation:shine 8s linear infinite;
}

@keyframes shine{

    0%{
        left:-120%;
    }

    100%{
        left:150%;
    }
}

.glass-inner{
    position:relative;

    background:rgba(255,255,255,.36);

    border:1px solid rgba(255,255,255,.70);

    border-radius:24px;

    backdrop-filter:blur(18px);

    padding:22px;
}

/* =========================
   ALERT
========================= */

.alert{
    border:none !important;

    border-radius:18px !important;

    background:rgba(255,255,255,.60) !important;

    backdrop-filter:blur(18px);

    color:#0f172a !important;

    box-shadow:
        0 10px 25px rgba(15,23,42,.08);

    padding:15px 18px !important;
}

/* =========================
   TABLE WRAP
========================= */

.table-responsive{
    border-radius:22px;
    overflow:auto;
    width:100%;
}

/* =========================
   TABLE
========================= */

.table{
    width:100%;
    min-width:1100px;

    border-collapse:separate !important;
    border-spacing:0 10px !important;

    background:transparent !important;
}

.table thead th{
    background:rgba(255,255,255,.72) !important;

    border:none !important;

    padding:16px !important;

    font-size:13px;
    font-weight:700;
    color:#0f172a;

    white-space:nowrap;
}

.table thead th:first-child{
    border-radius:16px 0 0 16px;
}

.table thead th:last-child{
    border-radius:0 16px 16px 0;
}

.table tbody tr{
    transition:.25s ease;
}

.table tbody tr:hover{
    transform:translateY(-3px);
}

.table tbody td{
    background:rgba(255,255,255,.50) !important;

    border:none !important;

    padding:16px 14px !important;

    vertical-align:middle;

    color:#334155;

    backdrop-filter:blur(12px);

    box-shadow:
        0 8px 20px rgba(15,23,42,.05);
}

.table tbody td:first-child{
    border-radius:16px 0 0 16px;
}

.table tbody td:last-child{
    border-radius:0 16px 16px 0;
}

/* =========================
   BUTTON
========================= */

.btn,
button{
    border:none !important;

    border-radius:14px !important;

    font-weight:600 !important;

    transition:.25s ease !important;

    box-shadow:
        0 8px 18px rgba(59,130,246,.15);
}

.btn:hover,
button:hover{
    transform:translateY(-2px);
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
            #38bdf8,
            #2563eb
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

    border-radius:16px !important;

    padding:12px 14px !important;

    background:rgba(255,255,255,.65) !important;

    backdrop-filter:blur(14px);

    color:#0f172a !important;

    box-shadow:
        0 6px 18px rgba(15,23,42,.05);

    transition:.25s ease;
}

input:focus,
select:focus,
textarea:focus{
    outline:none !important;

    box-shadow:
        0 0 0 4px rgba(96,165,250,.18),
        0 12px 24px rgba(59,130,246,.10) !important;
}

/* =========================
   MODAL
========================= */

.modal-content{
    border:none !important;

    border-radius:24px !important;

    background:rgba(255,255,255,.78) !important;

    backdrop-filter:blur(26px);
}

/* =========================
   MOBILE
========================= */

@media(max-width:992px){

    .dashboard-wrapper{
        padding:16px;
    }

    .glass-container{
        padding:16px;
        border-radius:24px;
    }

    .glass-inner{
        padding:16px;
        border-radius:20px;
    }

    .table{
        min-width:900px;
    }
}

@media(max-width:768px){

    .dashboard-wrapper{
        padding:12px;
    }

    .glass-container{
        padding:12px;
        border-radius:20px;
    }

    .glass-inner{
        padding:12px;
        border-radius:18px;
    }

    .table{
        min-width:780px;
        font-size:12px;
    }

    .table thead th{
        padding:12px !important;
        font-size:12px;
    }

    .table tbody td{
        padding:12px 10px !important;
        font-size:12px;
    }

    .btn,
    button{
        font-size:12px !important;
        padding:9px 12px !important;
    }

    input,
    select,
    textarea{
        font-size:13px !important;
        padding:10px 12px !important;
    }

    .modal-dialog{
        margin:10px;
    }

    .modal-content{
        border-radius:18px !important;
    }
}

@media(max-width:576px){

    .dashboard-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
        border-radius:18px;
    }

    .glass-inner{
        padding:10px;
        border-radius:16px;
    }

    .table{
        min-width:720px;
    }

    .alert{
        font-size:12px !important;
        padding:12px 14px !important;
    }

    .bg-bubble{
        display:none;
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
