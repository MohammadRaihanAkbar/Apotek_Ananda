<?php
/**
 * Laporan Kadaluwarsa - otomatis dari obat_batch.
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

$pageTitle = 'Laporan Kadaluwarsa';
require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/models/obat_expired.php';
require_once __DIR__ . '/../../backend/models/pbf.php';

$model = new ObatExpired();
$pbfModel = new PBF();

$filters = [
    'pbf_id' => isset($_GET['pbf_id']) ? sanitizeInt($_GET['pbf_id']) : null,
    'nama_obat' => isset($_GET['nama_obat']) ? sanitize($_GET['nama_obat']) : null,
    'date_start' => $_GET['date_start'] ?? null,
    'date_end' => $_GET['date_end'] ?? null,
    'status' => in_array(($_GET['status'] ?? ''), ['expired', 'segera_expired'], true)
        ? $_GET['status']
        : null,
];

$expiredList = $model->getExpiredReport($filters);
$stats = $model->getSummaryStats($filters);
$pbfList = $pbfModel->getAll();
$flash = getFlashMessage();

$queryString = http_build_query(
    array_filter(
        $filters,
        fn($v) => $v !== null && $v !== ''
    )
);

require_once __DIR__ . '/../templates/sidebar.php';
?>

<!-- BACKGROUND -->
<div class="bg-grid"></div>
<div class="bg-bubble one"></div>
<div class="bg-bubble two"></div>
<div class="bg-bubble three"></div>

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
            #f8fbff 0%,
            #eef5ff 40%,
            #ffffff 100%
        );

    position:relative;
}

/* BACKGROUND HIDUP */
body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle, rgba(59,130,246,0.16) 0%, transparent 60%),
        radial-gradient(circle, rgba(96,165,250,0.13) 0%, transparent 60%);

    background-size:700px 700px;

    animation:moveGlow 18s linear infinite;

    filter:blur(40px);

    z-index:-4;
}

.bg-grid{
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);

    background-size:40px 40px;

    mask-image:
        radial-gradient(circle at center, black 40%, transparent 85%);

    z-index:-3;
}

.bg-bubble{
    position:fixed;
    border-radius:50%;
    pointer-events:none;

    background:
        radial-gradient(
            circle at 30% 30%,
            rgba(255,255,255,0.95),
            rgba(255,255,255,0.08)
        );

    box-shadow:
        inset 0 0 25px rgba(255,255,255,0.9),
        0 0 35px rgba(59,130,246,0.10);

    animation:floating 12s ease-in-out infinite;

    z-index:-2;
}

.bg-bubble.one{
    width:220px;
    height:220px;
    top:5%;
    left:-80px;
}

.bg-bubble.two{
    width:280px;
    height:280px;
    bottom:-100px;
    right:-60px;
    animation-duration:18s;
}

.bg-bubble.three{
    width:140px;
    height:140px;
    top:50%;
    right:15%;
    animation-duration:14s;
}

@keyframes moveGlow{
    0%{
        transform:translate(0,0) rotate(0deg);
    }

    50%{
        transform:translate(40px,-30px) rotate(180deg);
    }

    100%{
        transform:translate(0,0) rotate(360deg);
    }
}

@keyframes floating{
    0%,100%{
        transform:translateY(0px);
    }

    50%{
        transform:translateY(-20px);
    }
}

/* WRAPPER */
.dashboard-wrapper{
    padding:22px;
    position:relative;
    z-index:10;
}

/* GLASS */
.glass-container{
    background:rgba(255,255,255,0.40);

    border:1px solid rgba(255,255,255,0.8);

    backdrop-filter:blur(24px);
    -webkit-backdrop-filter:blur(24px);

    border-radius:28px;

    padding:22px;

    box-shadow:
        0 15px 45px rgba(15,23,42,0.08),
        inset 0 1px 0 rgba(255,255,255,0.8);
}

.glass-inner{
    background:rgba(255,255,255,0.26);

    border:1px solid rgba(255,255,255,0.7);

    border-radius:24px;

    padding:22px;
}

/* HEADER */
.page-header{
    margin-bottom:20px;
}

.page-header h1{
    font-size:28px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:6px;
}

.page-header p{
    color:#64748b;
    font-size:13px;
}

/* ALERT */
.alert{
    padding:12px 16px;
    border-radius:14px;
    margin-bottom:18px;

    background:rgba(255,255,255,0.7);

    backdrop-filter:blur(14px);

    border:none;
}

/* STATS */
.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:14px;

    margin-bottom:20px;
}

.stat-card{
    position:relative;
    overflow:hidden;

    border-radius:20px;

    padding:18px;

    min-height:100px;

    color:#fff;

    box-shadow:
        0 10px 30px rgba(15,23,42,.08);

    border:1px solid rgba(255,255,255,.35);
}

.stat-card::before{
    content:'';

    position:absolute;

    width:120px;
    height:120px;

    border-radius:50%;

    top:-40px;
    right:-30px;

    background:rgba(255,255,255,.15);
}

.stat-card.danger{
    background:linear-gradient(135deg,#ef4444,#dc2626);
}

.stat-card.warning{
    background:linear-gradient(135deg,#f59e0b,#d97706);
}

.stat-card.primary{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
}

.stat-value{
    font-size:24px;
    font-weight:700;
    position:relative;
    z-index:2;
}

.stat-label{
    margin-top:6px;
    font-size:12px;
    opacity:.92;
    position:relative;
    z-index:2;
}

/* CARD GLASS */
.glass-card{
    background:rgba(255,255,255,0.36);

    border:1px solid rgba(255,255,255,0.75);

    backdrop-filter:blur(20px);

    border-radius:22px;

    padding:18px;

    margin-bottom:18px;

    box-shadow:
        0 10px 30px rgba(15,23,42,.05);
}

/* FILTER */
.filter-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
    gap:12px;
    align-items:end;
}

.form-group{
    display:flex;
    flex-direction:column;
    gap:6px;
}

.form-group label{
    font-size:12px;
    font-weight:600;
    color:#334155;
}

.form-control{
    height:42px;

    border:none;

    background:rgba(255,255,255,.55);

    border:1px solid rgba(255,255,255,.9);

    border-radius:14px;

    padding:0 12px;

    outline:none;

    font-size:13px;

    transition:.2s ease;
}

.form-control:focus{
    background:#fff;

    box-shadow:
        0 0 0 4px rgba(59,130,246,.12);
}

/* BUTTON */
.btn{
    border:none !important;

    border-radius:14px !important;

    padding:10px 16px !important;

    font-size:12px !important;

    font-weight:600 !important;

    text-decoration:none !important;

    transition:.25s ease;

    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.btn:hover{
    transform:translateY(-2px);
}

.btn-primary{
    color:#fff !important;

    background:linear-gradient(
        135deg,
        #3b82f6,
        #2563eb
    ) !important;

    box-shadow:
        0 10px 25px rgba(37,99,235,0.18);
}

.btn-secondary{
    background:#e2e8f0 !important;
    color:#334155 !important;
}

.btn-danger{
    background:#ef4444 !important;
    color:#fff !important;
}

.btn-success{
    background:#10b981 !important;
    color:#fff !important;
}

/* ACTION */
.action-bar{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    flex-wrap:wrap;
}

/* TABLE */
.table-wrapper{
    overflow:auto;
    border-radius:22px;
}

table{
    width:100%;
    border-collapse:collapse;
}

thead th{
    background:rgba(219,234,254,0.72);

    padding:14px 12px;

    text-align:left;

    font-size:12px;
    font-weight:700;

    color:#334155;

    white-space:nowrap;
}

tbody td{
    padding:13px 12px;

    background:rgba(255,255,255,0.40);

    border-bottom:1px solid rgba(226,232,240,0.8);

    font-size:13px;

    color:#334155;

    white-space:nowrap;
}

tbody tr{
    transition:0.25s ease;
}

tbody tr:hover{
    transform:scale(1.003);

    background:rgba(255,255,255,0.7);
}

.text-center{
    text-align:center;
}

.text-right{
    text-align:right;
}

/* BATCH */
.batch-code{
    background:#dbeafe;

    color:#2563eb;

    padding:5px 10px;

    border-radius:999px;

    font-size:11px;

    font-weight:700;
}

/* BADGE */
.badge{
    padding:7px 12px;

    border-radius:999px;

    font-size:11px;

    font-weight:700;

    display:inline-block;
}

.badge-danger{
    background:#fee2e2;
    color:#dc2626;
}

.badge-warning{
    background:#fef3c7;
    color:#d97706;
}

.empty-state{
    padding:35px;
    color:#94a3b8;
}

@media(max-width:768px){

    .dashboard-wrapper{
        padding:14px;
    }

    .glass-container,
    .glass-inner{
        padding:14px;
    }

    .page-header h1{
        font-size:24px;
    }

    table{
        min-width:1000px;
    }

    .action-bar{
        flex-direction:column;
    }

    .action-bar .btn{
        width:100%;
    }
}
</style>

<div class="dashboard-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <div class="page-header">
                <h1>Laporan Kadaluwarsa</h1>

                <p>
                    Monitoring batch obat expired dan mendekati expired maksimal 6 bulan.
                </p>
            </div>

            <?php if ($flash): ?>
                <div class="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- STATS -->
            <div class="stats-grid">

                <div class="stat-card danger">
                    <div class="stat-value">
                        <?= number_format($stats['expired_count'] ?? 0) ?>
                    </div>

                    <div class="stat-label">
                        Obat Expired
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-value">
                        <?= number_format($stats['six_month_count'] ?? 0) ?>
                    </div>

                    <div class="stat-label">
                        Akan Expired
                    </div>
                </div>

                <div class="stat-card primary">
                    <div class="stat-value">
                        Rp <?= number_format($stats['potential_loss'] ?? 0,0,',','.') ?>
                    </div>

                    <div class="stat-label">
                        Estimasi Kerugian
                    </div>
                </div>

            </div>

            <!-- FILTER -->
            <div class="glass-card">

                <form method="GET">

                    <div class="filter-grid">

                        <div class="form-group">
                            <label>PBF</label>

                            <select name="pbf_id" class="form-control">

                                <option value="">
                                    Semua PBF
                                </option>

                                <?php foreach ($pbfList as $pbf): ?>

                                    <option
                                        value="<?= $pbf['id_pbf'] ?>"
                                        <?= (int)($filters['pbf_id'] ?? 0) === (int)$pbf['id_pbf']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= htmlspecialchars($pbf['nama_pbf']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="form-group">
                            <label>Dari Exp</label>

                            <input
                                type="date"
                                name="date_start"
                                class="form-control"
                                value="<?= htmlspecialchars($filters['date_start'] ?? '') ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label>Sampai Exp</label>

                            <input
                                type="date"
                                name="date_end"
                                class="form-control"
                                value="<?= htmlspecialchars($filters['date_end'] ?? '') ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label>Status</label>

                            <select name="status" class="form-control">

                                <option value="">
                                    Semua
                                </option>

                                <option
                                    value="expired"
                                    <?= $filters['status'] === 'expired'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Sudah Expired
                                </option>

                                <option
                                    value="segera_expired"
                                    <?= $filters['status'] === 'segera_expired'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Akan Expired
                                </option>

                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nama Obat</label>

                            <input
                                type="text"
                                name="nama_obat"
                                class="form-control autocomplete-input"
                                data-type="expired"
                                placeholder="Cari obat..."
                                value="<?= htmlspecialchars($filters['nama_obat'] ?? '') ?>"
                                autocomplete="off"
                            >
                        </div>

                        <div style="display:flex;gap:10px;">

                            <button type="submit" class="btn btn-primary">
                                Cari
                            </button>

                            <a href="?" class="btn btn-secondary">
                                Reset
                            </a>

                        </div>

                    </div>

                </form>

            </div>

            <!-- ACTION -->
            <div class="action-bar" style="margin-bottom:18px;">

                <a
                    href="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=export_pdf&<?= htmlspecialchars($queryString) ?>"
                    class="btn btn-danger"
                    target="_blank"
                >
                    <span class="material-icons-round">picture_as_pdf</span>PDF
                </a>

                <a
                    href="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=export_excel&<?= htmlspecialchars($queryString) ?>"
                    class="btn btn-success"
                >
                    <span class="material-icons-round">table_view</span>Excel
                </a>

            </div>

            <!-- TABLE -->
            <div class="glass-card">

                <div class="table-wrapper">

                    <table>

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Obat</th>
                                <th>Merk Dagang</th>
                                <th>No Batch</th>
                                <th>Expired</th>
                                <th>Sisa</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>PBF</th>
                                <th>Faktur</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (empty($expiredList)): ?>

                                <tr>
                                    <td colspan="10" class="text-center empty-state">
                                        Tidak ada data expired.
                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($expiredList as $i => $item): ?>

                                    <?php $sisa = (int)$item['sisa_hari']; ?>

                                    <tr>

                                        <td><?= $i + 1 ?></td>

                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($item['nama_obat']) ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($item['merk_dagang'] ?? '-') ?: '-' ?>
                                        </td>

                                        <td>
                                            <span class="batch-code">
                                                <?= htmlspecialchars($item['batch']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($item['expired_date']) ?>
                                        </td>

                                        <td class="text-right">
                                            <?= $sisa ?>
                                        </td>

                                        <td class="text-right">
                                            <?= (int)$item['qty'] ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($item['satuan']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($item['nama_pbf']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($item['no_faktur']) ?>
                                        </td>

                                        <td>

                                            <?php if ($sisa < 0): ?>

                                                <span class="badge badge-danger">
                                                    Expired
                                                </span>

                                            <?php else: ?>

                                                <span class="badge badge-warning">
                                                    ≤ 6 Bulan
                                                </span>

                                            <?php endif; ?>

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
