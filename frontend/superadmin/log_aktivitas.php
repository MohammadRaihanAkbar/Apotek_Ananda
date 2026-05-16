<?php
/**
 * Log Aktivitas - Super Admin - Apotek Ananda Jadimulya
 * Desain premium dengan filter card sesuai screenshot.
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

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
<div class="dashboard-wrapper">
  <div class="glass-container">
    <div class="glass-inner">
<div class="page-header">
    <h1>Log Aktivitas</h1>
    <p>Pantau semua jejak aktivitas pengguna dalam sistem secara real-time.</p>
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
            <option value="super_admin" <?= $filterRole === 'super_admin' ? 'selected' : '' ?>>Admin</option>
            <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Staff</option>
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
                                <?= $log['role'] === 'super_admin' ? 'Admin' : 'Staff' ?>
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

<div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; color:#94a3b8; font-size:13px;">
    <div>Showing <?= count($logs) ?> results</div>
    <div style="display:flex; gap:5px;">
        <button class="btn btn-outline btn-sm" disabled><span class="material-icons-round">chevron_left</span></button>
        <button class="btn btn-primary btn-sm">1</button>
        <button class="btn btn-outline btn-sm" disabled><span class="material-icons-round">chevron_right</span></button>
    </div>
</div>
<style>
body{
    min-height:100vh;
    background: linear-gradient(135deg,#cfe9ff 0%,#ffffff 45%,#dbeafe 100%);
    color:#0f172a;
    font-family:'Poppins',sans-serif;
}

.dashboard-wrapper{ padding:40px; }

.glass-container{
    background: rgba(255,255,255,0.60);
    backdrop-filter: blur(26px);
    border:1px solid rgba(255,255,255,0.9);
    border-radius:28px;
    padding:28px;
    box-shadow:0 15px 45px rgba(15,23,42,0.12);
}

.glass-inner{
    background: rgba(255,255,255,0.55);
    backdrop-filter: blur(22px);
    border:1px solid rgba(255,255,255,0.85);
    border-radius:24px;
    padding:24px;
    box-shadow:0 10px 35px rgba(15,23,42,0.10);
}

/* Card & filter */
.card{
    background:rgba(255,255,255,0.65) !important;
    backdrop-filter:blur(18px);
    border-radius:22px !important;
    border:1px solid rgba(255,255,255,0.8) !important;
}

/* Table glass */
table thead th{
    background:rgba(219,234,254,0.85) !important;
    border:none !important;
}

table tbody td{
    background:rgba(255,255,255,0.65) !important;
}

/* Header hitam */
.page-header h1,
.page-header p{
    color:#000 !important;
}

/* Select & input filter biar menyatu */
.form-control{
    background:transparent !important;
    border:none !important;
    box-shadow:none !important;
}
</style>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
