<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();

if (isSuperAdmin()) {
    redirect(
        BASE_URL .
        '/frontend/superadmin/tambah_faktur.php' .
        (!empty($_GET['id']) ? '?id=' . sanitizeInt($_GET['id']) : '')
    );
}

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
    redirect(BASE_URL . '/frontend/admin/manajemen_stok.php');
}

$pageTitle = $faktur ? 'Edit Faktur' : 'Tambah Faktur';

require_once __DIR__ . '/../templates/header.php';

$pbfList       = $pbfModel->getAll();
$namaObatList  = $stokModel->getNamaObatList();
$flash         = getFlashMessage();

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
        radial-gradient(circle at 20% 20%, rgba(59,130,246,.14), transparent 28%),
        radial-gradient(circle at 80% 30%, rgba(125,211,252,.14), transparent 28%),
        radial-gradient(circle at 50% 80%, rgba(96,165,250,.10), transparent 30%);

    filter:blur(65px);

    animation:bgMove 18s linear infinite;

    z-index:-5;
}

body::after{
    content:'';
    position:fixed;
    inset:0;

    background:rgba(255,255,255,.04);
    backdrop-filter:blur(8px);

    z-index:-4;
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

    background-image:
        linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);

    background-size:36px 36px;

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
            rgba(255,255,255,.9),
            rgba(255,255,255,.05)
        );

    filter:blur(10px);

    animation:floating 14s ease-in-out infinite;

    z-index:-2;
}

.bg-bubble.one{
    width:180px;
    height:180px;
    top:6%;
    left:-70px;
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
    padding:16px;
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
            rgba(255,255,255,.58),
            rgba(255,255,255,.26)
        );

    border:1px solid rgba(255,255,255,.7);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

    border-radius:24px;

    padding:16px;

    box-shadow:
        0 8px 24px rgba(15,23,42,.06),
        inset 0 1px 0 rgba(255,255,255,.8);
}

.glass-container::before{
    content:'';

    position:absolute;
    top:0;
    left:-120%;

    width:60%;
    height:100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.2),
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

    background:rgba(255,255,255,.30);

    border:1px solid rgba(255,255,255,.65);

    border-radius:18px;

    backdrop-filter:blur(12px);

    padding:18px;
}

/* =========================
   FORM STYLE
========================= */

.card,
.form-card{
    background:rgba(255,255,255,.44) !important;

    border:1px solid rgba(255,255,255,.68) !important;

    border-radius:18px !important;

    backdrop-filter:blur(10px);

    box-shadow:
        0 6px 16px rgba(15,23,42,.04);
}

.form-group label{
    font-size:12px;
    font-weight:600;
    color:#475569;
    margin-bottom:7px;
    display:block;
}

.form-control,
input,
select,
textarea{
    width:100%;

    border:none !important;

    background:rgba(255,255,255,.62) !important;

    border-radius:12px !important;

    padding:11px 14px !important;

    font-size:13px !important;

    color:#0f172a !important;

    outline:none !important;

    box-shadow:
        inset 0 0 0 1px rgba(203,213,225,.7);
}

.form-control:focus,
input:focus,
select:focus,
textarea:focus{
    box-shadow:
        inset 0 0 0 2px rgba(59,130,246,.35),
        0 0 0 4px rgba(59,130,246,.08) !important;
}

/* =========================
   BUTTON
========================= */

.btn{
    border:none !important;

    border-radius:12px !important;

    font-weight:600 !important;

    transition:.2s ease !important;

    padding:10px 16px !important;

    font-size:12px !important;

    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
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

    color:#fff !important;

    box-shadow:
        0 8px 20px rgba(37,99,235,.18);
}

.btn-success{
    background:
        linear-gradient(
            135deg,
            #34d399,
            #10b981
        ) !important;

    color:#fff !important;
}

.btn-danger{
    background:
        linear-gradient(
            135deg,
            #f87171,
            #ef4444
        ) !important;

    color:#fff !important;
}

.btn-secondary{
    background:
        linear-gradient(
            135deg,
            #cbd5e1,
            #94a3b8
        ) !important;

    color:#fff !important;
}

/* =========================
   TABLE
========================= */

.table-responsive{
    overflow-x:auto;
    border-radius:16px;
}

table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 8px;
    min-width:850px;
}

thead th{
    background:rgba(255,255,255,.72);

    padding:12px 10px;

    font-size:11px;
    font-weight:700;

    color:#334155;

    text-transform:uppercase;
    letter-spacing:.4px;
}

thead th:first-child{
    border-radius:14px 0 0 14px;
}

thead th:last-child{
    border-radius:0 14px 14px 0;
}

tbody td{
    background:rgba(255,255,255,.50);

    padding:12px 10px;

    font-size:12px;

    color:#475569;

    backdrop-filter:blur(10px);

    box-shadow:
        0 4px 12px rgba(15,23,42,.04);
}

tbody td:first-child{
    border-radius:14px 0 0 14px;
}

tbody td:last-child{
    border-radius:0 14px 14px 0;
}

/* =========================
   ALERT
========================= */

.alert{
    padding:12px 14px;
    border-radius:14px;
    margin-bottom:16px;

    font-size:12px;
    font-weight:600;

    backdrop-filter:blur(12px);
}

.alert-success{
    background:rgba(220,252,231,.7);
    color:#166534;
}

.alert-error{
    background:rgba(254,226,226,.7);
    color:#991b1b;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:992px){

    .form-wrapper{
        padding:12px;
    }

    .glass-container{
        padding:12px;
        border-radius:20px;
    }

    .glass-inner{
        padding:14px;
    }
}

@media(max-width:768px){

    .form-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
    }

    .glass-inner{
        padding:12px;
    }

    .btn{
        width:100%;
    }

    table{
        min-width:760px;
    }
}

@media(max-width:576px){

    .bg-bubble{
        display:none;
    }

    .glass-container{
        border-radius:16px;
        padding:8px;
    }

    .glass-inner{
        border-radius:14px;
        padding:10px;
    }

    table{
        min-width:700px;
    }

    thead th,
    tbody td{
        font-size:10px;
        padding:9px 7px;
    }
}
</style>

<div class="form-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php require_once __DIR__ . '/../templates/faktur_form_page.php'; ?>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
