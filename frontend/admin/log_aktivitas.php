<?php
/**
 * Log Aktivitas - Admin - Apotek Ananda Jadimulya
 */

require_once __DIR__ . '/../../backend/helpers/session_helper.php';

requireLogin();

if (isSuperAdmin()) {
    redirect(BASE_URL . '/frontend/superadmin/log_aktivitas.php');
}

$pageTitle = 'Log Aktivitas';

require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/controllers/log_controller.php';

$logs    = getLogData();
$actions = getLogActions();
$flash   = getFlashMessage();

$filterRole = $_GET['role'] ?? '';
$filterDate = $_GET['date'] ?? '';
$filterAksi = $_GET['aksi'] ?? '';

require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="bg-grid"></div>
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

html,
body{
    overflow-x:hidden;
}

body{
    min-height:100vh;
    color:#0f172a;
    position:relative;

    background:
        linear-gradient(
            135deg,
            #f5f9ff 0%,
            #edf4ff 45%,
            #ffffff 100%
        );
}

/* =========================
   BACKGROUND
========================= */

body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle at 20% 20%, rgba(59,130,246,.14), transparent 28%),
        radial-gradient(circle at 80% 30%, rgba(125,211,252,.14), transparent 28%),
        radial-gradient(circle at 50% 80%, rgba(96,165,250,.10), transparent 30%);

    filter:blur(65px);

    animation:bgMove 18s linear infinite;

    z-index:-5;
}

body::after{
    content:'';
    position:fixed;
    inset:0;

    background:rgba(255,255,255,.04);
    backdrop-filter:blur(8px);

    z-index:-4;
}

@keyframes bgMove{

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

.bg-grid{
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);

    background-size:36px 36px;

    mask-image:
        radial-gradient(circle at center, black 35%, transparent 85%);

    z-index:-3;
}

.bg-bubble{
    position:fixed;
    border-radius:50%;

    background:
        radial-gradient(
            circle at 30% 30%,
            rgba(255,255,255,.9),
            rgba(255,255,255,.05)
        );

    filter:blur(10px);

    animation:floating 14s ease-in-out infinite;

    z-index:-2;
}

.bg-bubble.one{
    width:180px;
    height:180px;
    top:6%;
    left:-70px;
}

.bg-bubble.two{
    width:240px;
    height:240px;
    right:-90px;
    bottom:-90px;
    animation-duration:18s;
}

@keyframes floating{

    0%,100%{
        transform:translateY(0) translateX(0);
    }

    50%{
        transform:translateY(-18px) translateX(12px);
    }
}

/* =========================
   WRAPPER
========================= */

.dashboard-wrapper{
    padding:16px;
    position:relative;
    z-index:10;
}

/* =========================
   GLASS
========================= */

.glass-container{
    position:relative;
    overflow:hidden;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.58),
            rgba(255,255,255,.26)
        );

    border:1px solid rgba(255,255,255,.7);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

    border-radius:24px;

    padding:16px;

    box-shadow:
        0 8px 24px rgba(15,23,42,.06),
        inset 0 1px 0 rgba(255,255,255,.8);
}

.glass-inner{
    position:relative;

    background:rgba(255,255,255,.30);

    border:1px solid rgba(255,255,255,.65);

    border-radius:18px;

    backdrop-filter:blur(12px);

    padding:18px;
}

/* =========================
   HEADER
========================= */

.page-header{
    margin-bottom:20px;
}

.page-header h1{
    font-size:28px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:5px;
}

.page-header p{
    color:#64748b;
    font-size:13px;
}

/* =========================
   ALERT
========================= */

.alert{
    padding:12px 14px;
    border-radius:14px;
    margin-bottom:18px;

    font-size:12px;
    font-weight:600;

    backdrop-filter:blur(12px);
}

.alert-success{
    background:rgba(220,252,231,.7);
    color:#166534;
}

.alert-error{
    background:rgba(254,226,226,.7);
    color:#991b1b;
}

/* =========================
   FILTER
========================= */

.filter-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:22px;
}

.filter-card{
    background:rgba(255,255,255,.45);

    border:1px solid rgba(255,255,255,.7);

    border-radius:18px;

    backdrop-filter:blur(12px);

    padding:16px;
}

.filter-card label{
    display:block;
    font-size:11px;
    font-weight:700;
    color:#64748b;
    margin-bottom:8px;
}

.form-control{
    width:100%;

    border:none;

    background:rgba(255,255,255,.65);

    border-radius:12px;

    padding:11px 12px;

    font-size:12px;
    font-weight:600;

    color:#0f172a;

    outline:none;

    box-shadow:
        inset 0 0 0 1px rgba(203,213,225,.7);
}

.form-control:focus{
    box-shadow:
        inset 0 0 0 2px rgba(59,130,246,.35),
        0 0 0 4px rgba(59,130,246,.08);
}

/* =========================
   BUTTON
========================= */

.btn{
    border:none !important;

    border-radius:14px !important;

    font-weight:600 !important;

    transition:.2s ease !important;

    padding:12px 16px !important;

    font-size:12px !important;

    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;

    cursor:pointer;
}

.btn:hover{
    transform:translateY(-1px);
}

.btn-primary{
    background:
        linear-gradient(
            135deg,
            #60a5fa,
            #2563eb
        ) !important;

    color:#fff !important;

    box-shadow:
        0 8px 20px rgba(37,99,235,.18);
}

/* =========================
   CARD
========================= */

.card{
    background:rgba(255,255,255,.44);

    border:1px solid rgba(255,255,255,.68);

    border-radius:20px;

    backdrop-filter:blur(12px);

    overflow:hidden;

    box-shadow:
        0 6px 18px rgba(15,23,42,.05);
}

/* =========================
   TABLE
========================= */

.table-wrapper{
    overflow-x:auto;
}

table{
    width:100%;
    min-width:950px;

    border-collapse:separate;
    border-spacing:0 8px;

    padding:12px;
}

thead th{
    background:rgba(255,255,255,.75);

    padding:12px 10px;

    font-size:11px;
    font-weight:700;

    color:#334155;

    text-transform:uppercase;
    letter-spacing:.4px;
}

thead th:first-child{
    border-radius:14px 0 0 14px;
}

thead th:last-child{
    border-radius:0 14px 14px 0;
}

tbody td{
    background:rgba(255,255,255,.55);

    padding:14px 10px;

    font-size:12px;

    color:#475569;

    backdrop-filter:blur(10px);

    box-shadow:
        0 4px 12px rgba(15,23,42,.04);
}

tbody td:first-child{
    border-radius:14px 0 0 14px;
}

tbody td:last-child{
    border-radius:0 14px 14px 0;
}

tbody tr{
    transition:.2s ease;
}

tbody tr:hover{
    transform:translateY(-1px);
}

/* =========================
   BADGE
========================= */

.badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;

    padding:6px 10px;

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

/* =========================
   RESPONSIVE
========================= */

@media(max-width:1100px){

    .filter-grid{
        grid-template-columns:repeat(2,1fr);
    }
}

@media(max-width:768px){

    .dashboard-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
        border-radius:18px;
    }

    .glass-inner{
        padding:12px;
    }

    .page-header h1{
        font-size:22px;
    }

    .filter-grid{
        grid-template-columns:1fr;
        gap:12px;
    }

    .filter-card{
        padding:14px;
    }

    .btn{
        width:100%;
        min-height:48px;
    }

    table{
        min-width:780px;
    }
}

@media(max-width:576px){

    .bg-bubble{
        display:none;
    }

    .glass-container{
        border-radius:16px;
        padding:8px;
    }

    .glass-inner{
        border-radius:14px;
        padding:10px;
    }

    .filter-card{
        padding:12px;
    }

    .form-control{
        min-height:44px;
        font-size:12px;
    }

    table{
        min-width:720px;
    }

    thead th,
    tbody td{
        font-size:10px;
        padding:10px 8px;
    }
}
</style>

<div class="dashboard-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <div class="page-header">
                <h1>Log Aktivitas</h1>
                <p>Pantau jejak aktivitas semua pengguna dalam sistem.</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- FILTER -->
            <form method="GET" class="filter-grid">

                <div class="filter-card">

                    <label>Filter Peran</label>

                    <select name="role" class="form-control">

                        <option value="">Semua Role</option>

                        <option
                            value="super_admin"
                            <?= $filterRole === 'super_admin' ? 'selected' : '' ?>
                        >
                            Admin
                        </option>

                        <option
                            value="admin"
                            <?= $filterRole === 'admin' ? 'selected' : '' ?>
                        >
                            Staff
                        </option>

                    </select>

                </div>

                <div class="filter-card">

                    <label>Rentang Waktu</label>

                    <input
                        type="date"
                        name="date"
                        class="form-control"
                        value="<?= $filterDate ?>"
                    >

                </div>

                <div class="filter-card">

                    <label>Kategori Aksi</label>

                    <select name="aksi" class="form-control">

                        <option value="">Semua Aksi</option>

                        <?php foreach ($actions as $action): ?>

                            <option
                                value="<?= $action ?>"
                                <?= $filterAksi === $action ? 'selected' : '' ?>
                            >
                                <?= $action ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="filter-card" style="display:flex;align-items:flex-end;">

                    <button type="submit" class="btn btn-primary">
                        Terapkan Filter
                    </button>

                </div>

            </form>

            <!-- TABLE -->
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

                            <tr>
                                <td colspan="6" style="text-align:center;padding:40px;color:#94a3b8;">
                                    Tidak ada catatan aktivitas ditemukan.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($logs as $i => $log): ?>

                            <tr>

                                <td style="font-weight:700;color:#94a3b8;">
                                    <?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?>
                                </td>

                                <td>

                                    <div style="font-weight:700;color:#1e293b;">
                                        <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                    </div>

                                    <div style="font-size:11px;color:#94a3b8;">
                                        <?= date('d F Y', strtotime($log['created_at'])) ?>
                                    </div>

                                </td>

                                <td>

                                    <div style="font-weight:600;color:#0f172a;">
                                        <?= htmlspecialchars($log['nama_lengkap']) ?>
                                    </div>

                                </td>

                                <td>

                                    <span class="badge <?= $log['role'] === 'super_admin' ? 'badge-info' : 'badge-success' ?>">

                                        <?= $log['role'] === 'super_admin' ? 'Admin' : 'Staff' ?>

                                    </span>

                                </td>

                                <td>

                                    <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:#2563eb;">

                                        <span class="material-icons-round" style="font-size:18px;">

                                            <?php
                                            if (strpos($log['aksi'], 'Tambah') !== false) {
                                                echo 'add_circle';
                                            } elseif (strpos($log['aksi'], 'Edit') !== false) {
                                                echo 'edit';
                                            } elseif (strpos($log['aksi'], 'Hapus') !== false) {
                                                echo 'delete_forever';
                                            } elseif (strpos($log['aksi'], 'Login') !== false) {
                                                echo 'login';
                                            } elseif (strpos($log['aksi'], 'Logout') !== false) {
                                                echo 'logout';
                                            } else {
                                                echo 'history';
                                            }
                                            ?>

                                        </span>

                                        <?= htmlspecialchars($log['aksi']) ?>

                                    </div>

                                </td>

                                <td>

                                    <div style="max-width:420px;color:#64748b;line-height:1.6;">
                                        <?= htmlspecialchars($log['keterangan']) ?>
                                    </div>

                                </td>

                            </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
