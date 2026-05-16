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

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

body{
    min-height:100vh;
    background: linear-gradient(120deg,#eaf4ff 0%,#ffffff 55%,#e0f2fe 100%);
    color:#0f172a;
}

/* Container lebih compact */
.dashboard-container{
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:18px;
}

/* Glass elegan ringan */
.page-header,
.stat-card,
.alert{
    background: rgba(255,255,255,0.72);
    backdrop-filter: blur(16px);
    border:1px solid rgba(255,255,255,0.9);
    border-radius:16px;
    box-shadow:0 10px 30px rgba(37,99,235,0.12);
}

/* Header */
.page-header{ padding:18px 22px; }

.page-header h1{
    font-size:22px;
    font-weight:600;
    margin-bottom:4px;
}

.page-header p{
    font-size:13px;
    color:#475569;
}

/* Alert */
.alert{ padding:10px 14px;font-size:13px; }

/* Grid responsif */
.stats-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(210px,1fr));
    gap:14px;
}

/* Card padat */
.stat-card{
    padding:16px;
    transition:.25s;
}

.stat-card:hover{
    transform:translateY(-4px);
    box-shadow:0 18px 35px rgba(37,99,235,0.18);
}

/* Icon */
.icon-box{
    width:42px;height:42px;border-radius:10px;
    background:linear-gradient(135deg,#60a5fa,#2563eb);
    display:flex;align-items:center;justify-content:center;
    margin-bottom:10px;
}

.icon-box .material-icons-round{
    font-size:20px;color:#fff;
}

/* Text */
.label{font-size:11px;color:#64748b;}
.value{font-size:20px;font-weight:600;margin:6px 0;}
.sub-label{font-size:10px;color:#94a3b8;margin-bottom:10px;}

/* Button */
.btn{
    display:inline-block;
    padding:6px 12px;
    background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:#fff;text-decoration:none;
    border-radius:8px;
    font-size:12px;font-weight:500;
    box-shadow:0 6px 16px rgba(37,99,235,0.25);
    transition:.25s;
}
.btn:hover{ transform:translateY(-2px); }
</style>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Selamat Datang, <?= htmlspecialchars(getCurrentNamaLengkap()) ?></h1>
        <p>Kelola seluruh sistem Apotek Ananda melalui dashboard Super Admin.</p>
    </div>

    <?php if ($flash): ?>
        <div class="alert"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <?php
        $cards = [
            ['title'=>'JUMLAH STOK','value'=>$data['total_stok'],'icon'=>'inventory_2','link'=>'manajemen_stok.php'],
            ['title'=>'JUMLAH FAKTUR','value'=>$data['total_faktur'],'icon'=>'receipt_long','link'=>'manajemen_stok.php'],
            ['title'=>'PIUTANG BELUM LUNAS','value'=>'Rp '.number_format($data['piutang_belum_lunas_total'] ?? 0,0,',','.'),'icon'=>'payments','link'=>'piutang.php?status=belum_lunas'],
            ['title'=>'OBAT MENDEKATI EXPIRED','value'=>number_format($data['expiring_6months_count'] ?? 0),'icon'=>'warning_amber','link'=>'laporan_expired.php'],
            ['title'=>'LOG AKTIVITAS','value'=>number_format($data['total_log']),'icon'=>'history','link'=>'log_aktivitas.php']
        ];
        foreach($cards as $card): ?>
        <div class="stat-card">
            <div class="icon-box">
                <span class="material-icons-round"><?= $card['icon'] ?></span>
            </div>
            <div class="label"><?= $card['title'] ?></div>
            <div class="value"><?= is_numeric($card['value']) ? number_format($card['value']) : $card['value'] ?></div>
            <div class="sub-label"><?= $card['title'] ?> terbaru</div>
            <a href="<?= BASE_URL ?>/frontend/superadmin/<?= $card['link'] ?>" class="btn">Lihat</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
