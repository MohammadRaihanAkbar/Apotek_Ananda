<?php
/**
 * Dashboard Admin - Apotek Ananda Jadimulya
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();
if (isSuperAdmin()) { redirect(BASE_URL . '/frontend/superadmin/dashboard.php'); }

$pageTitle = 'Dashboard - Staff';
require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/controllers/dashboard_controller.php';
$data = getDashboardData();
$flash = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p>Selamat datang, <?= htmlspecialchars(getCurrentNamaLengkap()) ?>! Berikut ringkasan akses admin.</p>
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
        <a href="<?= BASE_URL ?>/frontend/admin/manajemen_stok.php" class="btn btn-primary btn-sm" style="margin-top:10px;align-self:flex-start;">Lihat Selengkapnya</a>
    </div>

    <div class="stat-card">
        <div class="icon-box" style="background:#f1f5f9;color:#64748b;"><span class="material-icons-round">history</span></div>
        <div class="label">Log Aktivitas</div>
        <div class="value"><?= number_format($data['total_log']) ?></div>
        <div class="sub-label">Total aktivitas semua user</div>
        <a href="<?= BASE_URL ?>/frontend/admin/log_aktivitas.php" class="btn btn-secondary btn-sm" style="margin-top:10px;align-self:flex-start;">Lihat Selengkapnya</a>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
