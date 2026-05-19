<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();
requireSuperAdmin();

$pageTitle = 'Dashboard - Super Admin';

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../backend/controllers/dashboard_controller.php';

$data  = getDashboardData();
$flash = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';

/* =========================
   DATA CARD
========================= */

$cards = [
    [
        'class' => 'stok',
        'title' => 'JUMLAH STOK',
        'value' => number_format($data['total_stok'] ?? 0),
        'icon'  => 'inventory_2',
        'link'  => 'manajemen_stok.php'
    ],
    [
        'class' => 'faktur',
        'title' => 'JUMLAH FAKTUR',
        'value' => number_format($data['total_faktur'] ?? 0),
        'icon'  => 'receipt_long',
        'link'  => 'manajemen_stok.php'
    ],
    [
        'class' => 'piutang',
        'title' => 'PIUTANG BELUM LUNAS',
        'value' => 'Rp ' . number_format($data['piutang_belum_lunas_total'] ?? 0,0,',','.'),
        'icon'  => 'payments',
        'link'  => 'piutang.php?status=belum_lunas'
    ],
    [
        'class' => 'expired',
        'title' => 'OBAT EXPIRED',
        'value' => number_format($data['expiring_6months_count'] ?? 0),
        'icon'  => 'warning_amber',
        'link'  => 'laporan_expired.php'
    ],
    [
        'class' => 'log',
        'title' => 'LOG AKTIVITAS',
        'value' => number_format($data['total_log'] ?? 0),
        'icon'  => 'history',
        'link'  => 'log_aktivitas.php'
    ]
];
?>

<div class="bg-grid"></div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

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
            #edf4ff 0%,
            #f7fbff 40%,
            #ffffff 100%
        );
}

/* ======================
   BACKGROUND
====================== */

body::before{
    content:'';
    position:fixed;
    inset:-20%;
    z-index:-2;

    background:
        radial-gradient(circle at 20% 20%, rgba(59,130,246,.18), transparent 25%),
        radial-gradient(circle at 80% 30%, rgba(96,165,250,.15), transparent 25%),
        radial-gradient(circle at 50% 80%, rgba(125,211,252,.12), transparent 30%);

    filter:blur(70px);
}

.bg-grid{
    position:fixed;
    inset:0;
    z-index:-1;

    background-image:
        linear-gradient(rgba(255,255,255,.12) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.12) 1px, transparent 1px);

    background-size:38px 38px;
    mask-image:radial-gradient(circle at center, black 30%, transparent 80%);
}

/* ======================
   CONTAINER
====================== */

.dashboard-container{
    width:100%;
    padding:24px;
    display:flex;
    flex-direction:column;
    gap:24px;
}

/* ======================
   GLASS
====================== */

.glass{
    background:rgba(255,255,255,.55);
    border:1px solid rgba(255,255,255,.7);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

    box-shadow:
        0 10px 35px rgba(15,23,42,.08),
        inset 0 1px 0 rgba(255,255,255,.9);

    border-radius:26px;
}

/* ======================
   HEADER
====================== */

.page-header{
    padding:34px;
    position:relative;
    overflow:hidden;
}

.page-header::before{
    content:'';
    position:absolute;
    top:-80px;
    right:-80px;

    width:220px;
    height:220px;
    border-radius:50%;

    background:radial-gradient(circle, rgba(59,130,246,.25), transparent 70%);
}

.page-header h1{
    font-size:32px;
    font-weight:700;
    margin-bottom:10px;
    position:relative;
}

.page-header p{
    font-size:14px;
    color:#64748b;
    line-height:1.8;
    max-width:700px;
    position:relative;
}

/* ======================
   ALERT
====================== */

.alert{
    padding:16px 20px;
    font-size:14px;
    color:#1d4ed8;
}

/* ======================
   CARD GRID
====================== */

.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));
    gap:20px;
}

.stat-card{
    padding:22px;
    transition:.3s ease;
}

.stat-card:hover{
    transform:translateY(-5px);
    box-shadow:
        0 18px 40px rgba(15,23,42,.12);
}

/* ======================
   ICON
====================== */

.icon-box{
    width:58px;
    height:58px;
    border-radius:18px;

    display:flex;
    align-items:center;
    justify-content:center;

    margin-bottom:18px;

    box-shadow:
        0 10px 22px rgba(0,0,0,.12);
}

.icon-box .material-icons-round{
    color:#fff;
    font-size:28px;
}

.stok .icon-box{
    background:linear-gradient(135deg,#60a5fa,#2563eb);
}

.faktur .icon-box{
    background:linear-gradient(135deg,#a78bfa,#7c3aed);
}

.piutang .icon-box{
    background:linear-gradient(135deg,#34d399,#059669);
}

.expired .icon-box{
    background:linear-gradient(135deg,#f87171,#dc2626);
}

.log .icon-box{
    background:linear-gradient(135deg,#fbbf24,#d97706);
}

/* ======================
   TEXT
====================== */

.label{
    font-size:12px;
    font-weight:700;
    letter-spacing:.8px;
    color:#64748b;
}

.value{
    margin:10px 0;
    font-size:30px;
    font-weight:700;
    line-height:1.2;

    word-break:break-word;
}

.sub-label{
    font-size:12px;
    color:#94a3b8;
    margin-bottom:18px;
}

/* ======================
   BUTTON
====================== */

.btn{
    width:100%;
    padding:12px 14px;

    display:flex;
    justify-content:center;
    align-items:center;

    border-radius:14px;

    text-decoration:none;
    color:#fff;

    font-size:13px;
    font-weight:600;

    transition:.25s ease;
}

.btn:hover{
    transform:translateY(-2px);
}

.stok .btn{
    background:linear-gradient(135deg,#60a5fa,#2563eb);
}

.faktur .btn{
    background:linear-gradient(135deg,#a78bfa,#7c3aed);
}

.piutang .btn{
    background:linear-gradient(135deg,#34d399,#059669);
}

.expired .btn{
    background:linear-gradient(135deg,#f87171,#dc2626);
}

.log .btn{
    background:linear-gradient(135deg,#fbbf24,#d97706);
}

/* ======================
   BOTTOM GRID
====================== */

.bottom-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

.summary-box,
.calendar-box{
    padding:24px;
}

.section-title{
    font-size:18px;
    font-weight:700;
    margin-bottom:20px;
}

/* ======================
   SUMMARY
====================== */

.summary-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
}

.summary-card{
    padding:20px;
    border-radius:20px;

    background:rgba(255,255,255,.45);
    border:1px solid rgba(255,255,255,.7);
}

.summary-label{
    font-size:12px;
    color:#64748b;
    margin-bottom:8px;
}

.summary-value{
    font-size:28px;
    font-weight:700;
    margin-bottom:8px;
}

.summary-desc{
    font-size:12px;
    color:#94a3b8;
    line-height:1.6;
}

/* ======================
   CALENDAR
====================== */

.calendar-box{
    text-align:center;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.calendar-date{
    font-size:72px;
    font-weight:700;
    line-height:1;

    background:linear-gradient(135deg,#2563eb,#60a5fa);

    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.calendar-day{
    margin-top:12px;
    font-size:18px;
    font-weight:600;
}

.calendar-month{
    margin-top:4px;
    color:#64748b;
    font-size:13px;
}

/* ======================
   RESPONSIVE TABLET
====================== */

@media(max-width:992px){

    .dashboard-container{
        padding:18px;
    }

    .bottom-grid{
        grid-template-columns:1fr;
    }

    .summary-grid{
        grid-template-columns:1fr;
    }

    .page-header{
        padding:28px;
    }

    .page-header h1{
        font-size:28px;
    }
}

/* ======================
   RESPONSIVE MOBILE
====================== */

@media(max-width:600px){

    .dashboard-container{
        padding:14px;
        gap:18px;
    }

    .page-header{
        padding:22px 18px;
        border-radius:22px;
    }

    .page-header h1{
        font-size:22px;
        line-height:1.4;
    }

    .page-header p{
        font-size:13px;
        line-height:1.7;
    }

    .glass{
        border-radius:22px;
    }

    .stat-card{
        padding:18px;
    }

    .icon-box{
        width:52px;
        height:52px;
        border-radius:16px;
    }

    .icon-box .material-icons-round{
        font-size:24px;
    }

    .value{
        font-size:24px;
    }

    .btn{
        font-size:12px;
        padding:10px 12px;
    }

    .summary-box,
    .calendar-box{
        padding:18px;
    }

    .summary-value{
        font-size:24px;
    }

    .calendar-date{
        font-size:56px;
    }

    .calendar-day{
        font-size:16px;
    }

    .section-title{
        font-size:16px;
    }

    .alert{
        font-size:13px;
        padding:14px 16px;
    }
}
</style>

<div class="dashboard-container">

    <!-- HEADER -->
    <div class="page-header glass">

        <h1>
            Selamat Datang,
            <?= htmlspecialchars(getCurrentNamaLengkap()) ?>
        </h1>

        <p>
            Pantau dan kelola seluruh data stok obat, transaksi, piutang,
            serta aktivitas pengguna Apotek Ananda dari satu dashboard.
        </p>

    </div>

    <!-- FLASH -->
    <?php if ($flash): ?>
        <div class="alert glass">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">

        <?php foreach($cards as $card): ?>

            <div class="stat-card glass <?= $card['class'] ?>">

                <div class="icon-box">
                    <span class="material-icons-round">
                        <?= $card['icon'] ?>
                    </span>
                </div>

                <div class="label">
                    <?= $card['title'] ?>
                </div>

                <div class="value">
                    <?= $card['value'] ?>
                </div>

                <div class="sub-label">
                    Data terbaru sistem
                </div>

                <a
                    href="<?= BASE_URL ?>/frontend/superadmin/<?= $card['link'] ?>"
                    class="btn"
                >
                    Lihat Detail
                </a>

            </div>

        <?php endforeach; ?>

    </div>

    <!-- BOTTOM -->
    <div class="bottom-grid">

        <div class="summary-box glass">

            <div class="section-title">
                Ringkasan Hari Ini
            </div>

            <div class="summary-grid">

                <div class="summary-card">

                    <div class="summary-label">
                        Total Faktur
                    </div>

                    <div class="summary-value">
                        <?= number_format($data['total_faktur'] ?? 0) ?>
                    </div>

                    <div class="summary-desc">
                        Faktur tercatat dalam sistem
                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-label">
                        Total Aktivitas
                    </div>

                    <div class="summary-value">
                        <?= number_format($data['total_log'] ?? 0) ?>
                    </div>

                    <div class="summary-desc">
                        Aktivitas admin & user
                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-label">
                        Obat Mendekati Expired
                    </div>

                    <div class="summary-value">
                        <?= number_format($data['expiring_6months_count'] ?? 0) ?>
                    </div>

                    <div class="summary-desc">
                        Perlu monitoring stok
                    </div>

                </div>

            </div>

        </div>

        <div class="calendar-box glass">

            <div class="section-title">
                Kalender
            </div>

            <div class="calendar-date">
                <?= date('d') ?>
            </div>

            <div class="calendar-day">
                <?= date('l') ?>
            </div>

            <div class="calendar-month">
                <?= date('F Y') ?>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
