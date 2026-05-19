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

$flash = getFlashMessage();

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

$canDeleteFaktur = true;

require_once __DIR__ . '/../templates/sidebar.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

body{
    background:
        linear-gradient(
            135deg,
            #f8fbff 0%,
            #eef5ff 45%,
            #ffffff 100%
        ) !important;

    min-height:100vh;
    overflow-x:hidden;
    position:relative;
}

/* GLOW */
body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle, rgba(59,130,246,.16) 0%, transparent 60%),
        radial-gradient(circle, rgba(96,165,250,.13) 0%, transparent 60%);

    background-size:700px 700px;

    animation:moveGlow 18s linear infinite;

    filter:blur(45px);

    z-index:-5;
}

/* GRID */
body::after{
    content:'';
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);

    background-size:34px 34px;

    mask-image:
        radial-gradient(circle at center, black 40%, transparent 85%);

    z-index:-4;
}

/* FLOATING BUBBLE */
.bg-bubble{
    position:fixed;
    border-radius:50%;
    pointer-events:none;

    background:
        radial-gradient(
            circle at 30% 30%,
            rgba(255,255,255,.95),
            rgba(255,255,255,.08)
        );

    box-shadow:
        inset 0 0 30px rgba(255,255,255,.95),
        0 0 45px rgba(59,130,246,.15);

    animation:floating 10s ease-in-out infinite;

    z-index:-2;
}

.bg-bubble.one{
    width:180px;
    height:180px;
    top:-60px;
    left:-60px;
}

.bg-bubble.two{
    width:220px;
    height:220px;
    bottom:-90px;
    right:-80px;
    animation-duration:15s;
}

/* WRAPPER */
.dashboard-wrapper{
    padding:14px;
    position:relative;
    z-index:5;
}

/* GLASS */
.glass-container{
    background:rgba(255,255,255,.40);

    border:1px solid rgba(255,255,255,.85);

    backdrop-filter:blur(22px);
    -webkit-backdrop-filter:blur(22px);

    border-radius:22px;

    padding:14px;

    box-shadow:
        0 10px 35px rgba(15,23,42,.08),
        inset 0 1px 0 rgba(255,255,255,.75);
}

.glass-inner{
    background:rgba(255,255,255,.28);

    border:1px solid rgba(255,255,255,.70);

    border-radius:18px;

    padding:14px;
}

/* ANIMATION */
@keyframes floating{

    0%,100%{
        transform:translateY(0px);
    }

    50%{
        transform:translateY(-18px);
    }
}

@keyframes moveGlow{

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

/* MOBILE */
@media(max-width:768px){

    .dashboard-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
        border-radius:18px;
    }

    .glass-inner{
        padding:10px;
        border-radius:14px;
    }
}
</style>

<div class="dashboard-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <?php require_once __DIR__ . '/../templates/stok_faktur_page.php'; ?>

        </div>

    </div>

</div>

<!-- BUBBLE -->
<div class="bg-bubble one"></div>
<div class="bg-bubble two"></div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
