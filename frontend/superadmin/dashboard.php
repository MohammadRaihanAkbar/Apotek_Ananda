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
?>

<!-- BACKGROUND -->
<div class="bg-grid"></div>
<div class="bg-glow glow-1"></div>
<div class="bg-glow glow-2"></div>
<div class="bg-glow glow-3"></div>

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
    position:relative;

    background:
        linear-gradient(
            135deg,
            #edf4ff 0%,
            #f7fbff 35%,
            #ffffff 100%
        );
}

/* =========================
   BACKGROUND HIDUP
========================= */

body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle at 20% 20%, rgba(59,130,246,0.22), transparent 25%),
        radial-gradient(circle at 80% 30%, rgba(96,165,250,0.20), transparent 25%),
        radial-gradient(circle at 50% 80%, rgba(125,211,252,0.18), transparent 30%);

    filter:blur(70px);

    animation:bgMove 20s linear infinite;

    z-index:-5;
}

body::after{
    content:'';
    position:fixed;
    inset:0;

    background:
        rgba(255,255,255,0.05);

    backdrop-filter:blur(10px);

    z-index:-4;
}

@keyframes bgMove{

    0%{
        transform:translate(0,0) rotate(0deg);
    }

    50%{
        transform:translate(50px,-40px) rotate(180deg);
    }

    100%{
        transform:translate(0,0) rotate(360deg);
    }
}

.bg-grid{
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,0.08) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.08) 1px, transparent 1px);

    background-size:40px 40px;

    mask-image:
        radial-gradient(circle at center, black 35%, transparent 85%);

    z-index:-3;
}

.bg-glow{
    position:fixed;
    border-radius:50%;
    filter:blur(10px);

    background:
        radial-gradient(circle at 30% 30%,
        rgba(255,255,255,0.95),
        rgba(255,255,255,0.08));

    box-shadow:
        inset 0 0 35px rgba(255,255,255,0.9),
        0 0 45px rgba(59,130,246,0.20);

    animation:floating 15s ease-in-out infinite;

    z-index:-2;
}

.glow-1{
    width:260px;
    height:260px;
    top:5%;
    left:-70px;
}

.glow-2{
    width:340px;
    height:340px;
    bottom:-120px;
    right:-80px;
    animation-duration:18s;
}

.glow-3{
    width:160px;
    height:160px;
    top:45%;
    right:18%;
    animation-duration:12s;
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
   CONTAINER
========================= */

.dashboard-container{
    padding:28px;
    display:flex;
    flex-direction:column;
    gap:24px;
    position:relative;
    z-index:10;
}

/* =========================
   GLASS EFFECT
========================= */

.glass{
    position:relative;
    overflow:hidden;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,0.55),
            rgba(255,255,255,0.28)
        );

    border:
        1px solid rgba(255,255,255,0.75);

    backdrop-filter:blur(24px);
    -webkit-backdrop-filter:blur(24px);

    box-shadow:
        0 10px 35px rgba(15,23,42,0.08),
        inset 0 1px 0 rgba(255,255,255,0.9),
        inset 0 -1px 0 rgba(255,255,255,0.4);

    transition:0.35s ease;
}

.glass::before{
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
            rgba(255,255,255,0.35),
            transparent
        );

    transform:skewX(-25deg);

    animation:shine 7s linear infinite;
}

@keyframes shine{
    0%{left:-120%;}
    100%{left:150%;}
}

/* =========================
   HEADER
========================= */

.page-header{
    padding:34px;
    border-radius:30px;
}

.page-header h1{
    font-size:32px;
    font-weight:700;
    margin-bottom:8px;
}

.page-header p{
    font-size:14px;
    color:#64748b;
    line-height:1.7;
}

/* =========================
   ALERT
========================= */

.alert{
    padding:16px 20px;
    border-radius:18px;
    color:#1e3a8a;
    font-size:13px;
}

/* =========================
   STATS
========================= */

.stats-grid{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:20px;
}

.stat-card{
    padding:22px;
    border-radius:28px;
    position:relative;

    transition:0.35s ease;
}

.stat-card:hover{
    transform:
        translateY(-8px)
        scale(1.02);

    box-shadow:
        0 20px 40px rgba(37,99,235,0.16),
        inset 0 1px 0 rgba(255,255,255,0.9);
}

.stat-card::after{
    content:'';
    position:absolute;
    inset:0;

    background:
        radial-gradient(circle at top right,
        rgba(59,130,246,0.12),
        transparent 40%);

    pointer-events:none;
}

/* ICON */

.icon-box{
    width:62px;
    height:62px;

    border-radius:20px;

    display:flex;
    align-items:center;
    justify-content:center;

    margin-bottom:18px;

    background:
        linear-gradient(
            135deg,
            #60a5fa,
            #2563eb
        );

    box-shadow:
        0 14px 30px rgba(37,99,235,0.28);
}

.icon-box .material-icons-round{
    color:#fff;
    font-size:28px;
}

/* TEXT */

.label{
    font-size:11px;
    font-weight:700;
    letter-spacing:1.2px;
    color:#64748b;
}

.value{
    font-size:30px;
    font-weight:700;
    margin:10px 0;
    color:#0f172a;
}

.sub-label{
    font-size:12px;
    color:#94a3b8;
    margin-bottom:18px;
}

/* BUTTON */

.btn{
    width:100%;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:11px 14px;

    border-radius:14px;

    text-decoration:none;
    font-size:13px;
    font-weight:600;

    color:#fff;

    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #2563eb
        );

    box-shadow:
        0 10px 22px rgba(37,99,235,0.24);

    transition:0.3s ease;
}

.btn:hover{
    transform:translateY(-2px);

    box-shadow:
        0 14px 26px rgba(37,99,235,0.34);
}

/* =========================
   BOTTOM
========================= */

.bottom-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:22px;
}

/* SUMMARY */

.summary-box{
    padding:24px;
    border-radius:28px;
}

.section-title{
    font-size:18px;
    font-weight:700;
    margin-bottom:20px;
}

.summary-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
}

.summary-card{
    padding:20px;
    border-radius:22px;

    background:
        rgba(255,255,255,0.35);

    border:
        1px solid rgba(255,255,255,0.7);

    transition:0.3s ease;
}

.summary-card:hover{
    transform:translateY(-6px);
}

.summary-label{
    font-size:12px;
    color:#64748b;
    margin-bottom:8px;
}

.summary-value{
    font-size:28px;
    font-weight:700;
    margin-bottom:6px;
}

.summary-desc{
    font-size:11px;
    color:#94a3b8;
}

/* =========================
   CALENDAR
========================= */

.calendar-box{
    padding:28px;
    border-radius:28px;
    text-align:center;
}

.calendar-date{
    font-size:84px;
    font-weight:700;
    line-height:1;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #60a5fa
        );

    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;

    margin-top:10px;
}

.calendar-day{
    margin-top:14px;
    font-size:17px;
    font-weight:600;
}

.calendar-month{
    margin-top:4px;
    font-size:13px;
    color:#64748b;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:1400px){

    .stats-grid{
        grid-template-columns:repeat(3,1fr);
    }
}

@media(max-width:992px){

    .stats-grid{
        grid-template-columns:repeat(2,1fr);
    }

    .bottom-grid{
        grid-template-columns:1fr;
    }

    .summary-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:600px){

    .stats-grid{
        grid-template-columns:1fr;
    }

    .dashboard-container{
        padding:16px;
    }

    .page-header h1{
        font-size:24px;
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
            Kelola seluruh sistem Apotek Ananda melalui dashboard modern dengan tampilan glassmorphism premium.
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

        <?php
        $cards = [
            [
                'title'=>'JUMLAH STOK',
                'value'=>$data['total_stok'],
                'icon'=>'inventory_2',
                'link'=>'manajemen_stok.php'
            ],
            [
                'title'=>'JUMLAH FAKTUR',
                'value'=>$data['total_faktur'],
                'icon'=>'receipt_long',
                'link'=>'manajemen_stok.php'
            ],
            [
                'title'=>'PIUTANG BELUM LUNAS',
                'value'=>'Rp '.number_format($data['piutang_belum_lunas_total'] ?? 0,0,',','.'),
                'icon'=>'payments',
                'link'=>'piutang.php?status=belum_lunas'
            ],
            [
                'title'=>'OBAT EXPIRED',
                'value'=>number_format($data['expiring_6months_count'] ?? 0),
                'icon'=>'warning_amber',
                'link'=>'laporan_expired.php'
            ],
            [
                'title'=>'LOG AKTIVITAS',
                'value'=>number_format($data['total_log']),
                'icon'=>'history',
                'link'=>'log_aktivitas.php'
            ]
        ];

        foreach($cards as $card):
        ?>

        <div class="stat-card glass">

            <div class="icon-box">
                <span class="material-icons-round">
                    <?= $card['icon'] ?>
                </span>
            </div>

            <div class="label">
                <?= $card['title'] ?>
            </div>

            <div class="value">
                <?= is_numeric($card['value'])
                    ? number_format($card['value'])
                    : $card['value']
                ?>
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

        <!-- SUMMARY -->
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

        <!-- CALENDAR -->
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
