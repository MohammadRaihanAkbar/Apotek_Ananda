<?php
/**
 * Dashboard Admin - Apotek Ananda Jadimulya
 * Premium Glassmorphism UI
 */

require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();

if (isSuperAdmin()) {
    redirect(BASE_URL . '/frontend/superadmin/dashboard.php');
}

$pageTitle = 'Dashboard - Staff';

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../backend/controllers/dashboard_controller.php';

$data  = getDashboardData();
$flash = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

:root{
    --primary:#2563eb;
    --primary2:#60a5fa;
    --success:#10b981;
    --danger:#ef4444;
    --warning:#f59e0b;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
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
            #eef5ff 45%,
            #ffffff 100%
        );

    position:relative;
}

/* BACKGROUND GLOW */
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

/* BUBBLE */
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

/* WRAPPER */
.dashboard-wrapper{
    padding:16px;
    position:relative;
    z-index:5;
}

/* GLASS */
.glass-container{
    background:rgba(255,255,255,.40);

    border:1px solid rgba(255,255,255,.85);

    backdrop-filter:blur(22px);
    -webkit-backdrop-filter:blur(22px);

    border-radius:24px;

    padding:18px;

    box-shadow:
        0 10px 35px rgba(15,23,42,.08),
        inset 0 1px 0 rgba(255,255,255,.75);
}

.glass-inner{
    background:rgba(255,255,255,.28);

    border:1px solid rgba(255,255,255,.70);

    border-radius:20px;

    padding:20px;
}

/* HEADER */
.page-header{
    margin-bottom:20px;
}

.page-header h1{
    font-size:28px;
    font-weight:700;
    color:#111827;
    margin-bottom:4px;
}

.page-header p{
    color:#64748b;
    font-size:13px;
}

/* ALERT */
.alert{
    padding:14px 16px;
    border-radius:14px;
    margin-bottom:18px;
    font-size:13px;

    background:rgba(255,255,255,.60);

    border:1px solid rgba(255,255,255,.75);

    backdrop-filter:blur(10px);
}

/* GRID */
.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:18px;
}

/* CARD */
.stat-card{
    position:relative;

    background:rgba(255,255,255,.38);

    border:1px solid rgba(255,255,255,.85);

    backdrop-filter:blur(18px);

    border-radius:24px;

    padding:24px;

    overflow:hidden;

    box-shadow:
        0 10px 30px rgba(15,23,42,.06);

    transition:.25s ease;
}

.stat-card:hover{
    transform:translateY(-5px);
    box-shadow:
        0 18px 40px rgba(15,23,42,.10);
}

.stat-card.primary{
    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,.95),
            rgba(96,165,250,.92)
        );

    color:#fff;
}

.stat-card.primary .label,
.stat-card.primary .sub-label{
    color:rgba(255,255,255,.85);
}

.icon-box{
    width:64px;
    height:64px;

    border-radius:18px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:rgba(255,255,255,.18);

    color:#fff;

    margin-bottom:18px;

    backdrop-filter:blur(12px);
}

.icon-box .material-icons-round{
    font-size:32px;
}

.label{
    font-size:13px;
    font-weight:600;
    color:#64748b;
    margin-bottom:6px;
}

.value{
    font-size:38px;
    font-weight:700;
    line-height:1;
    margin-bottom:10px;
}

.sub-label{
    font-size:12px;
    color:#94a3b8;
    line-height:1.5;
}

/* BUTTON */
.btn{
    border:none !important;

    border-radius:12px !important;

    padding:10px 14px !important;

    font-size:12px !important;
    font-weight:600 !important;

    cursor:pointer;

    transition:.2s ease;

    text-decoration:none;

    display:inline-flex;
    align-items:center;
    justify-content:center;

    gap:6px;
}

.btn:hover{
    transform:translateY(-1px);
}

.btn-sm{
    padding:9px 14px !important;
}

.btn-primary{
    color:#fff !important;

    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #2563eb
        ) !important;

    box-shadow:
        0 8px 20px rgba(37,99,235,.18);
}

.btn-secondary{
    background:rgba(255,255,255,.70) !important;
    color:#334155 !important;
}

/* MOBILE */
@media(max-width:768px){

    .dashboard-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
        border-radius:20px;
    }

    .glass-inner{
        padding:14px;
        border-radius:16px;
    }

    .page-header h1{
        font-size:22px;
    }

    .page-header p{
        font-size:12px;
    }

    .stats-grid{
        grid-template-columns:1fr;
    }

    .stat-card{
        padding:18px;
    }

    .value{
        font-size:30px;
    }

    .icon-box{
        width:56px;
        height:56px;
    }

    .icon-box .material-icons-round{
        font-size:28px;
    }

    .btn{
        width:100%;
    }
}
</style>

<div class="dashboard-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <div class="page-header">
                <h1>Dashboard Staff</h1>
                <p>
                    Selamat datang,
                    <?= htmlspecialchars(getCurrentNamaLengkap()) ?> 👋
                    Berikut ringkasan akses admin.
                </p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">

                <!-- STOK -->
                <div class="stat-card primary">

                    <div class="icon-box">
                        <span class="material-icons-round">
                            inventory_2
                        </span>
                    </div>

                    <div class="label">
                        Jumlah Stok
                    </div>

                    <div class="value">
                        <?= number_format($data['total_stok']) ?>
                    </div>

                    <div class="sub-label">
                        Total unit obat dari seluruh faktur pembelian.
                    </div>

                    <a
                        href="<?= BASE_URL ?>/frontend/admin/manajemen_stok.php"
                        class="btn btn-primary btn-sm"
                        style="margin-top:18px;"
                    >
                        <span class="material-icons-round">east</span>
                        Lihat Selengkapnya
                    </a>

                </div>

                <!-- LOG -->
                <div class="stat-card">

                    <div
                        class="icon-box"
                        style="
                            background:#f1f5f9;
                            color:#64748b;
                        "
                    >
                        <span class="material-icons-round">
                            history
                        </span>
                    </div>

                    <div class="label">
                        Log Aktivitas
                    </div>

                    <div class="value">
                        <?= number_format($data['total_log']) ?>
                    </div>

                    <div class="sub-label">
                        Total aktivitas semua user yang tercatat dalam sistem.
                    </div>

                    <a
                        href="<?= BASE_URL ?>/frontend/admin/log_aktivitas.php"
                        class="btn btn-secondary btn-sm"
                        style="margin-top:18px;"
                    >
                        <span class="material-icons-round">visibility</span>
                        Lihat Selengkapnya
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- BACKGROUND -->
<div class="bg-bubble one"></div>
<div class="bg-bubble two"></div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
