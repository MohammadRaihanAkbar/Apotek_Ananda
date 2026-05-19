<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

require_once __DIR__ . '/../../backend/models/stok_masuk.php';
require_once __DIR__ . '/../../backend/models/pbf.php';

$stokModel = new StokMasuk();
$pbfModel  = new PBF();

$id = isset($_GET['id']) ? sanitizeInt($_GET['id']) : 0;

$faktur = $id > 0
    ? $stokModel->findFakturWithDetails($id)
    : null;

if ($id > 0 && !$faktur) {
    setFlashMessage('error', 'Faktur tidak ditemukan.');
    redirect(BASE_URL . '/frontend/superadmin/manajemen_stok.php');
}

$pageTitle = $faktur
    ? 'Edit Faktur'
    : 'Tambah Faktur';

require_once __DIR__ . '/../templates/header.php';

$pbfList      = $pbfModel->getAll();
$namaObatList = $stokModel->getNamaObatList();
$flash        = getFlashMessage();

$validSatuan = [
    'Tube',
    'FLS',
    'Strip',
    'Sach',
    'Box',
    'Kaleng',
    'Pcs',
    'Tablet',
    'Kapsul',
    'Ampul',
    'Supp',
    'Ovula',
    'Pack'
];

require_once __DIR__ . '/../templates/sidebar.php';
?>

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
    background:linear-gradient(
        135deg,
        #f7fbff 0%,
        #eef5ff 45%,
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
    z-index:-5;

    background:
        radial-gradient(circle at 20% 20%, rgba(59,130,246,.15), transparent 28%),
        radial-gradient(circle at 80% 30%, rgba(125,211,252,.15), transparent 28%),
        radial-gradient(circle at 50% 80%, rgba(96,165,250,.12), transparent 30%);

    filter:blur(70px);

    animation:bgMove 18s linear infinite;
}

body::after{
    content:'';
    position:fixed;
    inset:0;
    z-index:-4;

    background:rgba(255,255,255,.04);

    backdrop-filter:blur(8px);
}

@keyframes bgMove{

    0%{
        transform:translate(0,0) rotate(0deg);
    }

    50%{
        transform:translate(35px,-25px) rotate(180deg);
    }

    100%{
        transform:translate(0,0) rotate(360deg);
    }
}

.bg-grid{
    position:fixed;
    inset:0;
    z-index:-3;

    background-image:
        linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);

    background-size:34px 34px;

    mask-image:
        radial-gradient(circle at center, black 35%, transparent 85%);
}

.bg-bubble{
    position:fixed;
    border-radius:50%;
    z-index:-2;

    background:
        radial-gradient(
            circle at 30% 30%,
            rgba(255,255,255,.95),
            rgba(255,255,255,.08)
        );

    filter:blur(10px);

    animation:floating 14s ease-in-out infinite;
}

.bg-bubble.one{
    width:180px;
    height:180px;
    top:5%;
    left:-60px;
}

.bg-bubble.two{
    width:240px;
    height:240px;
    right:-90px;
    bottom:-90px;
    animation-duration:18s;
}

@keyframes floating{

    0%,100%{
        transform:translateY(0) translateX(0);
    }

    50%{
        transform:translateY(-18px) translateX(12px);
    }
}

/* =========================
   WRAPPER
========================= */

.form-wrapper{
    position:relative;
    z-index:10;

    padding:14px;
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
            rgba(255,255,255,.60),
            rgba(255,255,255,.28)
        );

    border:1px solid rgba(255,255,255,.72);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

    border-radius:22px;

    padding:14px;

    box-shadow:
        0 8px 25px rgba(15,23,42,.06),
        inset 0 1px 0 rgba(255,255,255,.85);
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
            rgba(255,255,255,.22),
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

    background:rgba(255,255,255,.34);

    border:1px solid rgba(255,255,255,.65);

    border-radius:18px;

    backdrop-filter:blur(14px);

    padding:16px;
}

/* =========================
   ALERT
========================= */

.alert{
    border:none !important;

    border-radius:14px !important;

    background:rgba(255,255,255,.60) !important;

    backdrop-filter:blur(14px);

    color:#0f172a !important;

    padding:12px 14px !important;

    font-size:13px;

    box-shadow:
        0 6px 18px rgba(15,23,42,.05);
}

/* =========================
   FORM
========================= */

form{
    width:100%;
}

label{
    display:block;

    margin-bottom:6px;

    font-size:12px;
    font-weight:600;

    color:#334155;
}

input,
select,
textarea{
    width:100%;

    border:none !important;

    border-radius:12px !important;

    padding:10px 12px !important;

    font-size:13px !important;

    background:rgba(255,255,255,.65) !important;

    backdrop-filter:blur(12px);

    color:#0f172a !important;

    box-shadow:
        0 4px 14px rgba(15,23,42,.05);

    transition:.25s ease;
}

input:focus,
select:focus,
textarea:focus{
    outline:none !important;

    box-shadow:
        0 0 0 3px rgba(96,165,250,.20),
        0 8px 18px rgba(59,130,246,.10) !important;
}

textarea{
    min-height:90px;
    resize:vertical;
}

/* =========================
   CARD
========================= */

.card,
.form-card,
.section-card{
    background:rgba(255,255,255,.45) !important;

    border:1px solid rgba(255,255,255,.68) !important;

    border-radius:18px !important;

    backdrop-filter:blur(12px);

    overflow:hidden;

    box-shadow:
        0 6px 18px rgba(15,23,42,.04);

    margin-bottom:16px;
}

.card-header{
    background:transparent !important;

    border-bottom:1px solid rgba(148,163,184,.10) !important;

    padding:14px 16px !important;
}

.card-header h5,
.card-header h4,
.card-header h3{
    margin:0;
    font-size:15px;
    font-weight:700;
}

.card-body{
    padding:16px !important;
}

/* =========================
   TABLE
========================= */

.table-responsive{
    width:100%;
    overflow-x:auto;
    overflow-y:hidden;

    border-radius:16px;

    -webkit-overflow-scrolling:touch;
}

.table{
    width:100%;
    min-width:850px;

    border-collapse:separate !important;
    border-spacing:0 8px !important;

    background:transparent !important;
}

.table thead th{
    background:rgba(255,255,255,.72) !important;

    border:none !important;

    padding:12px 10px !important;

    color:#0f172a !important;

    font-size:11px;
    font-weight:700;

    text-transform:uppercase;

    white-space:nowrap;
}

.table thead th:first-child{
    border-radius:12px 0 0 12px;
}

.table thead th:last-child{
    border-radius:0 12px 12px 0;
}

.table tbody td{
    background:rgba(255,255,255,.54) !important;

    border:none !important;

    padding:10px !important;

    vertical-align:middle;

    white-space:nowrap;

    font-size:12px;

    color:#475569 !important;

    box-shadow:
        0 5px 16px rgba(15,23,42,.04);
}

.table tbody td:first-child{
    border-radius:12px 0 0 12px;
}

.table tbody td:last-child{
    border-radius:0 12px 12px 0;
}

/* =========================
   BUTTON
========================= */

.btn{
    border:none !important;

    border-radius:10px !important;

    font-size:12px !important;
    font-weight:600 !important;

    padding:8px 14px !important;

    transition:.25s ease !important;

    box-shadow:
        0 5px 14px rgba(59,130,246,.12);
}

.btn:hover{
    transform:translateY(-1px);
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
            #22c55e,
            #16a34a
        ) !important;
}

.btn-danger{
    background:
        linear-gradient(
            135deg,
            #ef4444,
            #dc2626
        ) !important;
}

.btn-secondary{
    background:
        linear-gradient(
            135deg,
            #94a3b8,
            #64748b
        ) !important;

    color:#fff !important;
}

/* =========================
   TITLE
========================= */

.page-title{
    margin-bottom:16px;
}

.page-title h1{
    font-size:22px;
    font-weight:700;
    color:#0f172a;
}

.page-subtitle{
    margin-top:4px;

    color:#64748b;
    font-size:12px;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:768px){

    .form-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
        border-radius:18px;
    }

    .glass-inner{
        padding:12px;
        border-radius:14px;
    }

    .table{
        min-width:760px;
    }

    .btn{
        width:100%;
    }
}

@media(max-width:576px){

    .bg-bubble{
        display:none;
    }

    .page-title h1{
        font-size:18px;
    }

    .page-subtitle{
        font-size:11px;
    }

    input,
    select,
    textarea{
        font-size:12px !important;
    }

    .table{
        min-width:700px;
    }
}
</style>

<div class="form-wrapper">

    <?php if ($flash): ?>
        <div class="alert alert-info mb-3">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="glass-container">

        <div class="glass-inner">

            <?php require_once __DIR__ . '/../templates/faktur_form_page.php'; ?>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
