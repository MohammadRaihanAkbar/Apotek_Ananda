<?php
/**
 * Log Aktivitas - Admin - Apotek Ananda Jadimulya
 * Desain premium dengan filter card sesuai screenshot.
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();
if (isSuperAdmin()) { redirect(BASE_URL . '/frontend/superadmin/log_aktivitas.php'); }

$pageTitle = 'Log Aktivitas';
require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/controllers/log_controller.php';
$logs = getLogData();
$actions = getLogActions();
$flash = getFlashMessage();

// Active filters
$filterRole = $_GET['role'] ?? '';
$filterDate = $_GET['date'] ?? '';
$filterAksi = $_GET['aksi'] ?? '';

require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="page-header">
    <h1>Log Aktivitas</h1>
    <p>Pantau jejak aktivitas semua pengguna dalam sistem.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Log Filter Cards -->
<form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 150px; gap: 20px; margin-bottom: 30px;">
    <div class="card" style="margin-bottom:0; padding: 15px 20px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:8px;">Filter Peran</label>
        <select name="role" class="form-control" style="border:none; padding:0; height:auto; font-weight:600; color:#1e293b;">
            <option value="">Semua Role</option>
            <option value="super_admin" <?= $filterRole === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
            <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </div>

    <div class="card" style="margin-bottom:0; padding: 15px 20px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:8px;">Rentang Waktu</label>
        <input type="date" name="date" class="form-control" value="<?= $filterDate ?>" style="border:none; padding:0; height:auto; font-weight:600; color:#1e293b;">
    </div>
    
    <div class="card" style="margin-bottom:0; padding: 15px 20px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:8px;">Kategori Aksi</label>
        <select name="aksi" class="form-control" style="border:none; padding:0; height:auto; font-weight:600; color:#1e293b;">
            <option value="">Semua Aksi</option>
            <?php foreach ($actions as $action): ?>
                <option value="<?= $action ?>" <?= $filterAksi === $action ? 'selected' : '' ?>><?= $action ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary" style="height: 100%; border-radius: 20px; justify-content: center;">
        Terapkan Filter
    </button>
</form>

<!-- Log Table Card -->
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>WAKTU</th>
                    <th>USER</th>
                    <th>ROLE</th>
                    <th>AKSI</th>
                    <th>KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center" style="padding:40px; color:#94a3b8;">Tidak ada catatan aktivitas ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $i => $log): ?>
                    <tr>
                        <td style="font-weight:600; color:#94a3b8;"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                        <td>
                            <div style="font-weight:700; color:#1e293b;"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                            <div style="font-size:11px; color:#94a3b8;"><?= date('d F Y', strtotime($log['created_at'])) ?></div>
                        </td>
                        <td>
                            <div style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($log['nama_lengkap']) ?></div>
                        </td>
                        <td>
                            <span class="badge <?= $log['role'] === 'super_admin' ? 'badge-info' : 'badge-success' ?>" style="font-size:10px;">
                                <?= $log['role'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px; font-weight:600; color: var(--success);">
                                <span class="material-icons-round" style="font-size:18px;">
                                    <?php 
                                    if (strpos($log['aksi'], 'Tambah') !== false) echo 'add_circle';
                                    elseif (strpos($log['aksi'], 'Edit') !== false) echo 'edit';
                                    elseif (strpos($log['aksi'], 'Hapus') !== false) echo 'delete_forever';
                                    elseif (strpos($log['aksi'], 'Login') !== false) echo 'login';
                                    elseif (strpos($log['aksi'], 'Logout') !== false) echo 'logout';
                                    else echo 'history';
                                    ?>
                                </span>
                                <?= htmlspecialchars($log['aksi']) ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px; color:#64748b; max-width:400px;"><?= htmlspecialchars($log['keterangan']) ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
