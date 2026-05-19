<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

require_once __DIR__ . '/../../backend/models/stok_masuk.php';

$id = isset($_GET['id']) ? sanitizeInt($_GET['id']) : 0;

$faktur = $id > 0
    ? (new StokMasuk())->findFakturWithDetails($id)
    : null;

if (!$faktur) {
    setFlashMessage('error', 'Faktur tidak ditemukan.');
    redirect(BASE_URL . '/frontend/superadmin/manajemen_stok.php');
}

$pageTitle = 'Detail Faktur';

require_once __DIR__ . '/../templates/header.php';
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

.detail-wrapper{
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

    padding:16px;
}

/* =========================
   GLOBAL DETAIL
========================= */

h1,h2,h3,h4,h5{
    color:#0f172a;
}

p{
    color:#64748b;
}

/* =========================
   CARD SECTION
========================= */

.card,
.detail-card,
.summary-card{
    background:rgba(255,255,255,.44) !important;

    border:1px solid rgba(255,255,255,.68) !important;

    border-radius:18px !important;

    backdrop-filter:blur(10px);

    box-shadow:
        0 6px 16px rgba(15,23,42,.04);

    overflow:hidden;
}

.card-header,
.detail-header{
    background:transparent !important;
    border-bottom:1px solid rgba(148,163,184,.10) !important;
    padding:14px 16px !important;
}

.card-body{
    padding:16px !important;
}

/* =========================
   INFO GRID
========================= */

.info-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
    gap:12px;
}

.info-item{
    background:rgba(255,255,255,.42);
    border:1px solid rgba(255,255,255,.65);
    border-radius:16px;
    padding:14px;
}

.info-label{
    font-size:11px;
    font-weight:600;
    color:#64748b;
    margin-bottom:6px;
}

.info-value{
    font-size:15px;
    font-weight:700;
    color:#0f172a;
    line-height:1.4;
}

/* =========================
   TABLE
========================= */

.table-responsive{
    width:100%;
    overflow-x:auto;
    overflow-y:hidden;
    border-radius:18px;
    -webkit-overflow-scrolling:touch;
    padding-bottom:2px;
}

.table{
    width:100%;
    min-width:880px;

    border-collapse:separate !important;
    border-spacing:0 8px !important;

    background:transparent !important;
}

.table thead th{
    background:rgba(255,255,255,.7) !important;

    border:none !important;

    padding:12px 10px !important;

    color:#0f172a !important;

    font-size:11px;
    font-weight:700;
    letter-spacing:.3px;
    text-transform:uppercase;

    white-space:nowrap;
    vertical-align:middle;

    backdrop-filter:blur(10px);
}

.table thead th:first-child{
    border-radius:14px 0 0 14px;
}

.table thead th:last-child{
    border-radius:0 14px 14px 0;
}

.table tbody tr{
    transition:.2s ease;
}

.table tbody tr:hover{
    transform:translateY(-1px);
}

.table tbody td{
    background:rgba(255,255,255,.5) !important;

    border:none !important;

    padding:12px 10px !important;

    vertical-align:middle;

    white-space:nowrap;

    color:#475569 !important;
    font-size:12px;

    backdrop-filter:blur(10px);

    box-shadow:
        0 4px 12px rgba(15,23,42,.04);
}

.table tbody td:first-child{
    border-radius:14px 0 0 14px;
}

.table tbody td:last-child{
    border-radius:0 14px 14px 0;
}

.table td strong{
    color:#0f172a;
    font-weight:700;
}

/* =========================
   RAPIIIN KOLOM
========================= */

.table td:nth-child(1),
.table td:nth-child(4),
.table td:nth-child(5),
.table td:nth-child(6),
.table td:nth-child(7){
    text-align:center;
    font-variant-numeric:tabular-nums;
}

.table td:nth-child(2){
    font-weight:600;
    color:#0f172a !important;
}

.table td:nth-child(4),
.table td:nth-child(7){
    font-weight:700;
    color:#1e293b !important;
}

.table td:nth-child(8){
    min-width:120px;
}

/* =========================
   BADGE
========================= */

.badge{
    border-radius:999px !important;
    padding:5px 10px !important;
    font-size:10px !important;
    font-weight:700 !important;
}

/* =========================
   BUTTON
========================= */

.btn{
    border:none !important;

    border-radius:10px !important;

    font-weight:600 !important;

    transition:.2s ease !important;

    box-shadow:
        0 4px 10px rgba(59,130,246,.12);

    padding:7px 12px !important;

    font-size:12px !important;
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

.btn-warning{
    background:
        linear-gradient(
            135deg,
            #fbbf24,
            #f59e0b
        ) !important;

    color:#fff !important;
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
   RESPONSIVE
========================= */

@media(max-width:992px){

    .detail-wrapper{
        padding:12px;
    }

    .glass-container{
        padding:12px;
        border-radius:20px;
    }

    .glass-inner{
        padding:12px;
        border-radius:16px;
    }

    .table{
        min-width:820px;
    }
}

@media(max-width:768px){

    .detail-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
    }

    .glass-inner{
        padding:10px;
    }

    .table{
        min-width:760px;
    }

    .table thead th{
        font-size:10px;
        padding:10px 8px !important;
    }

    .table tbody td{
        font-size:11px;
        padding:10px 8px !important;
    }

    .btn{
        width:100%;
        justify-content:center;
    }
}

@media(max-width:576px){

    .glass-container{
        padding:8px;
        border-radius:16px;
    }

    .glass-inner{
        padding:10px;
        border-radius:14px;
    }

    .bg-bubble{
        display:none;
    }

    .table{
        min-width:700px;
    }

    .table thead th,
    .table tbody td{
        padding:9px 7px !important;
        font-size:10px;
    }

    .table td:nth-child(8){
        min-width:110px;
    }

    .card-header,
    .card-body{
        padding:12px !important;
    }
}
</style>

<div class="detail-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <?php require_once __DIR__ . '/../templates/detail_faktur_page.php'; ?>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
