<?php
/**
 * Dashboard Admin - Apotek Ananda Jadimulya
 * Admin hanya melihat: Jumlah Stok + Log Aktivitas
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();
if (isSuperAdmin()) { redirect(BASE_URL . '/frontend/superadmin/dashboard.php'); }

$pageTitle = 'Dashboard - Admin';
require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/controllers/dashboard_controller.php';
$data = getDashboardData();
$flash = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>

    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Selamat datang, <?= htmlspecialchars(getCurrentNamaLengkap()) ?>!</p>
    </div>
    
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="icon-box"><span class="material-icons-round">inventory</span></div>
            <div class="label">Total Unit Stok Obat</div>
            <div class="value"><?= number_format($data['total_stok']) ?></div>
            <div class="sub-label">Keseluruhan stok di gudang</div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-title">📋 Log Aktivitas Terbaru</div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Waktu</th><th>User</th><th>Role</th><th>Aksi</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($data['recent_logs'])): ?>
                        <tr><td colspan="5" class="text-center" style="color:#94a3b8;">Belum ada aktivitas</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['recent_logs'] as $log): ?>
                        <tr>
                            <td style="white-space:nowrap"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                            <td><?= htmlspecialchars($log['nama_lengkap']) ?></td>
                            <td><span class="badge <?= $log['role'] === 'super_admin' ? 'badge-warning' : 'badge-success' ?>"><?= $log['role'] === 'super_admin' ? 'Super Admin' : 'Admin' ?></span></td>
                            <td><?= htmlspecialchars($log['aksi']) ?></td>
                            <td><?= htmlspecialchars($log['keterangan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>

