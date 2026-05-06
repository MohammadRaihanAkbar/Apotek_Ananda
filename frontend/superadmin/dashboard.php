<?php
/**
 * Dashboard Super Admin - Apotek Ananda Jadimulya
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();
requireSuperAdmin();

$pageTitle = 'Dashboard - Admin';
require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/controllers/dashboard_controller.php';
$data = getDashboardData();
$flash = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p>Selamat datang, <?= htmlspecialchars(getCurrentNamaLengkap()) ?>! Berikut ringkasan utama sistem.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="icon-box"><span class="material-icons-round">inventory</span></div>
        <div class="label">Jumlah Stok</div>
        <div class="value"><?= number_format($data['total_stok']) ?></div>
        <div class="sub-label">Total unit obat dari seluruh faktur</div>
        <a href="<?= BASE_URL ?>/frontend/superadmin/manajemen_stok.php" class="btn btn-primary btn-sm" style="margin-top:10px;align-self:flex-start;">Lihat Selengkapnya</a>
    </div>

    <div class="stat-card success">
        <div class="icon-box"><span class="material-icons-round">receipt_long</span></div>
        <div class="label">Jumlah Faktur</div>
        <div class="value"><?= number_format($data['total_faktur']) ?></div>
        <div class="sub-label">Total faktur stok yang tercatat</div>
        <a href="<?= BASE_URL ?>/frontend/superadmin/manajemen_stok.php" class="btn btn-success btn-sm" style="margin-top:10px;align-self:flex-start;">Lihat Selengkapnya</a>
    </div>

    <div class="stat-card danger">
        <div class="icon-box"><span class="material-icons-round">payments</span></div>
        <div class="label">Piutang Belum Lunas</div>
        <div class="value">Rp <?= number_format($data['piutang_belum_lunas_total'] ?? 0, 0, ',', '.') ?></div>
        <div class="sub-label"><?= number_format($data['piutang_belum_lunas_count'] ?? 0) ?> faktur belum lunas</div>
        <a href="<?= BASE_URL ?>/frontend/superadmin/piutang.php?status=belum_lunas" class="btn btn-danger btn-sm" style="margin-top:10px;align-self:flex-start;">Lihat Selengkapnya</a>
    </div>

    <div class="stat-card warning">
        <div class="icon-box"><span class="material-icons-round">notification_important</span></div>
        <div class="label">Obat Mendekati Expired</div>
        <div class="value"><?= number_format($data['expiring_6months_count'] ?? 0) ?></div>
        <div class="sub-label">Batch expired atau ≤ 6 bulan</div>
        <a href="<?= BASE_URL ?>/frontend/superadmin/laporan_expired.php" class="btn btn-warning btn-sm" style="margin-top:10px;align-self:flex-start;">Lihat Selengkapnya</a>
    </div>

    <div class="stat-card">
        <div class="icon-box" style="background:#f1f5f9;color:#64748b;"><span class="material-icons-round">history</span></div>
        <div class="label">Log Aktivitas</div>
        <div class="value"><?= number_format($data['total_log']) ?></div>
        <div class="sub-label">Total aktivitas tercatat</div>
        <a href="<?= BASE_URL ?>/frontend/superadmin/log_aktivitas.php" class="btn btn-secondary btn-sm" style="margin-top:10px;align-self:flex-start;">Lihat Selengkapnya</a>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
