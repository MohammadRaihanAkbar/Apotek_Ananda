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

function formatLogTime(?string $datetime): string {
    if (!$datetime) return '-';
    $timestamp = strtotime($datetime);
    return $timestamp ? date('H:i:s', $timestamp) : '-';
}

function formatLogDate(?string $datetime): string {
    if (!$datetime) return '-';
    $timestamp = strtotime($datetime);
    return $timestamp ? date('d F Y', $timestamp) : '-';
}


require_once __DIR__ . '/../templates/sidebar.php';
?>
<div class="dashboard-wrapper">
  <div class="glass-container">
    <div class="glass-inner">
<div class="page-header">
    <h1>Log Aktivitas</h1>
    <p>Pantau semua jejak aktivitas pengguna dalam sistem secara real-time.</p>
    <div style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:7px 10px;border-radius:999px;background:rgba(219,234,254,.75);color:#1d4ed8;font-size:12px;font-weight:700;">
        <span class="material-icons-round" style="font-size:16px;">schedule</span>
        Jam Sistem: <span id="liveClock"><?= date('H:i:s') ?> WIB</span>
    </div>
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
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>" style="border:none; padding:0; height:auto; font-weight:600; color:#1e293b;">
    </div>
    
    <div class="card" style="margin-bottom:0; padding: 15px 20px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:8px;">Kategori Aksi</label>
        <select name="aksi" class="form-control" style="border:none; padding:0; height:auto; font-weight:600; color:#1e293b;">
            <option value="">Semua Aksi</option>
            <?php foreach ($actions as $action): ?>
                <option value="<?= htmlspecialchars($action) ?>" <?= $filterAksi === $action ? 'selected' : '' ?>><?= htmlspecialchars($action) ?></option>
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
                            <div style="font-weight:700; color:#1e293b;"><?= formatLogTime($log['created_at']) ?></div>
                            <div style="font-size:11px; color:#94a3b8;"><?= formatLogDate($log['created_at']) ?></div>
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
<!-- BACKGROUND -->
<div class="bg-bubble one"></div>
<div class="bg-bubble two"></div>

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

/* GLOW BACKGROUND */
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

/* HEADER */
.page-header{
    margin-bottom:14px;
}

.page-header h1{
    font-size:22px;
    font-weight:700;
    color:#111827;
    margin-bottom:2px;
}

.page-header p{
    color:#64748b;
    font-size:11px;
}

/* ALERT */
.alert{
    padding:10px 12px;
    border-radius:12px;
    margin-bottom:12px;
    font-size:12px;

    background:rgba(255,255,255,.60);

    border:1px solid rgba(255,255,255,.75);

    backdrop-filter:blur(10px);
}

/* FILTER */
.filter-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:10px;
    margin-bottom:14px;
}

.filter-card{
    background:rgba(255,255,255,.40);

    border:1px solid rgba(255,255,255,.85);

    backdrop-filter:blur(18px);

    border-radius:16px;

    padding:12px;
}

.filter-card label{
    display:block;
    font-size:10px;
    font-weight:700;
    color:#64748b;
    margin-bottom:8px;
}

/* FORM */
.form-control{
    width:100%;

    border:none;

    background:rgba(255,255,255,.55);

    border:1px solid rgba(255,255,255,.90);

    border-radius:10px;

    padding:9px 10px;

    font-size:11px;

    outline:none;

    transition:.2s;
}

.form-control:focus{
    border-color:#60a5fa;

    box-shadow:
        0 0 0 3px rgba(59,130,246,.12);
}

/* BUTTON */
.btn{
    border:none !important;

    border-radius:10px !important;

    padding:8px 12px !important;

    font-size:11px !important;
    font-weight:600 !important;

    cursor:pointer;

    transition:.2s ease;

    text-decoration:none;

    display:inline-flex;
    align-items:center;
    justify-content:center;

    gap:4px;
}

.btn:hover{
    transform:translateY(-1px);
}

.btn-sm{
    padding:7px 10px !important;
    font-size:10px !important;
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

.btn-outline{
    background:rgba(255,255,255,.60) !important;
    color:#334155 !important;
}

/* CARD */
.card{
    background:rgba(255,255,255,.38);

    border:1px solid rgba(255,255,255,.85);

    backdrop-filter:blur(18px);

    border-radius:18px;

    padding:12px;

    box-shadow:
        0 10px 30px rgba(15,23,42,.06);
}

/* TABLE */
.table-wrapper{
    overflow:auto;
    border-radius:14px;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:850px;
}

thead th{
    background:rgba(219,234,254,.75);

    padding:10px 10px;

    text-align:left;

    font-size:10px;
    font-weight:700;

    color:#334155;
}

tbody td{
    padding:10px 10px;

    background:rgba(255,255,255,.45);

    border-bottom:1px solid rgba(226,232,240,.7);

    font-size:11px;
    color:#334155;
}

tbody tr{
    transition:.2s ease;
}

tbody tr:hover{
    background:rgba(255,255,255,.70);
}

/* BADGE */
.badge{
    padding:5px 10px;
    border-radius:999px;
    font-size:10px;
    font-weight:700;
}

.badge-success{
    background:#dcfce7;
    color:#15803d;
}

.badge-info{
    background:#dbeafe;
    color:#1d4ed8;
}

/* FOOTER */
.log-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-top:12px;

    color:#94a3b8;
    font-size:11px;
}

/* MOBILE */
@media(max-width:768px){

    .dashboard-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
    }

    .glass-inner{
        padding:10px;
    }

    .page-header h1{
        font-size:18px;
    }

    .filter-grid{
        grid-template-columns:1fr;
    }

    table{
        min-width:750px;
    }

    .log-footer{
        flex-direction:column;
        gap:8px;
        align-items:flex-start;
    }
}
</style>
    </div>
  </div>
</div>

<script>
(function(){
    const clock = document.getElementById('liveClock');
    if (!clock) return;
    function pad(n){ return String(n).padStart(2, '0'); }
    function updateClock(){
        const parts = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).formatToParts(new Date());
        const data = {};
        parts.forEach(p => data[p.type] = p.value);
        clock.textContent = `${data.hour}:${data.minute}:${data.second} WIB`;
    }
    updateClock();
    setInterval(updateClock, 1000);
})();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
