<?php
/**
 * Piutang - Otomatis dari faktur.
 * Tampilan modern glassmorphism compact.
 */

require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

$pageTitle = 'Manajemen Piutang';

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../backend/models/piutang.php';
require_once __DIR__ . '/../../backend/models/pbf.php';

$model = new Piutang();
$pbfModel = new PBF();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
$filterPbf = isset($_GET['pbf']) ? sanitizeInt($_GET['pbf']) : null;
$filterStatus = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$filterBulanParam = isset($_GET['bulan']) ? sanitize($_GET['bulan']) : null;
$filterBulanAngka = isset($_GET['bulan_angka']) ? sanitize($_GET['bulan_angka']) : '';
$filterTahun = isset($_GET['tahun']) ? sanitize($_GET['tahun']) : '';
$filterBulan = null;

$filterTempo = isset($_GET['tempo']) ? sanitize($_GET['tempo']) : '';

if ($filterBulanAngka !== '' && !preg_match('/^(0[1-9]|1[0-2])$/', $filterBulanAngka)) {
    $filterBulanAngka = '';
}

if ($filterTahun !== '' && !preg_match('/^\d{4}$/', $filterTahun)) {
    $filterTahun = '';
}

if ($filterBulanAngka !== '' && $filterTahun !== '') {
    $filterBulan = $filterTahun . '-' . $filterBulanAngka;
} elseif ($filterBulanParam !== null && preg_match('/^\d{4}-\d{2}$/', $filterBulanParam)) {
    $filterBulan = $filterBulanParam;

    if ($filterTahun === '') {
        $filterTahun = substr($filterBulanParam, 0, 4);
    }

    if ($filterBulanAngka === '') {
        $filterBulanAngka = substr($filterBulanParam, 5, 2);
    }
}

if (!in_array($filterStatus, ['', 'belum_lunas', 'lunas'], true)) {
    $filterStatus = '';
}

if (!in_array($filterTempo, ['', 'no_due_date', 'overdue', 'today', 'due_soon', 'safe'], true)) {
    $filterTempo = '';
}

$filters = [
    'pbf' => $filterPbf,
    'bulan' => $filterBulan,
    'tempo' => $filterTempo,
];

$listBelumLunas = $filterStatus === 'lunas' ? [] : $model->getAllByStatus('belum_lunas', $search, $filters);
$listLunas = $filterStatus === 'belum_lunas' ? [] : $model->getAllByStatus('lunas', $search, $filters);
$summary = $model->getSummary($filterStatus !== '' ? $filterStatus : null, $search, $filters);
$pbfList = $pbfModel->getAll();
$flash = getFlashMessage();

$exportParams = [];
if ($search !== null && $search !== '') $exportParams['search'] = $search;
if ($filterStatus !== '') $exportParams['status'] = $filterStatus;
if ($filterPbf !== null && $filterPbf > 0) $exportParams['pbf'] = $filterPbf;
if ($filterBulanAngka !== '') $exportParams['bulan_angka'] = $filterBulanAngka;
if ($filterTahun !== '') $exportParams['tahun'] = $filterTahun;
if ($filterBulan !== null && $filterBulan !== '') $exportParams['bulan'] = $filterBulan;
if ($filterTempo !== '') $exportParams['tempo'] = $filterTempo;

$exportQuery = http_build_query($exportParams);
$exportSuffix = $exportQuery ? '&' . $exportQuery : '';

function formatTanggalPiutang(?string $date): string {
    if (!$date) return '-';

    try {
        $bulan = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];
        $d = new DateTime($date);
        return $d->format('d') . ' ' . $bulan[(int)$d->format('m')] . ' ' . $d->format('Y');
    } catch (Exception $e) {
        return $date;
    }
}

function reminderTempo(?string $tanggalJatuhTempo, string $status): array {
    if ($status === 'lunas') {
        return ['tanggal' => formatTanggalPiutang($tanggalJatuhTempo), 'label' => 'Selesai', 'class' => 'tempo-success'];
    }

    if (!$tanggalJatuhTempo) {
        return ['tanggal' => '-', 'label' => 'Belum diatur', 'class' => 'tempo-muted'];
    }

    try {
        $today = new DateTime(date('Y-m-d'));
        $due = new DateTime($tanggalJatuhTempo);
        $diff = (int)$today->diff($due)->format('%r%a');

        if ($diff < 0) {
            return ['tanggal' => formatTanggalPiutang($tanggalJatuhTempo), 'label' => 'Lewat ' . abs($diff) . ' hari', 'class' => 'tempo-danger'];
        }

        if ($diff === 0) {
            return ['tanggal' => formatTanggalPiutang($tanggalJatuhTempo), 'label' => 'Hari ini', 'class' => 'tempo-danger'];
        }

        if ($diff <= 30) {
            return ['tanggal' => formatTanggalPiutang($tanggalJatuhTempo), 'label' => 'Kurang ' . $diff . ' hari', 'class' => 'tempo-warning'];
        }

        return ['tanggal' => formatTanggalPiutang($tanggalJatuhTempo), 'label' => 'Masih aman', 'class' => 'tempo-success'];
    } catch (Exception $e) {
        return ['tanggal' => $tanggalJatuhTempo, 'label' => 'Tidak valid', 'class' => 'tempo-muted'];
    }
}

require_once __DIR__ . '/../templates/sidebar.php';
?>

<!-- BACKGROUND -->
<div class="bg-grid"></div>
<div class="bg-bubble one"></div>
<div class="bg-bubble two"></div>
<div class="bg-bubble three"></div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

:root{
    --primary:#2563eb;
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

html,body{
    width:100%;
    overflow-x:hidden;
}

body{
    min-height:100vh;
    color:#0f172a;
    background:linear-gradient(135deg,#f8fbff 0%,#eef5ff 45%,#ffffff 100%);
    position:relative;
}

body::before{
    content:'';
    position:fixed;
    inset:-20%;
    background:
        radial-gradient(circle, rgba(59,130,246,0.16) 0%, transparent 60%),
        radial-gradient(circle, rgba(96,165,250,0.12) 0%, transparent 60%);
    background-size:600px 600px;
    animation:moveGlow 18s linear infinite;
    filter:blur(45px);
    z-index:-5;
}

.bg-grid{
    position:fixed;
    inset:0;
    background-image:
        linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size:32px 32px;
    z-index:-4;
}

.bg-bubble{
    position:fixed;
    border-radius:50%;
    pointer-events:none;
    background:radial-gradient(circle at 30% 30%,rgba(255,255,255,0.95),rgba(255,255,255,0.08));
    box-shadow:inset 0 0 35px rgba(255,255,255,.95),0 0 45px rgba(59,130,246,.14);
    animation:floating 12s ease-in-out infinite;
    z-index:-2;
}

.bg-bubble.one{width:180px;height:180px;top:-60px;left:-60px;}
.bg-bubble.two{width:220px;height:220px;bottom:-80px;right:-80px;}
.bg-bubble.three{width:130px;height:130px;top:42%;right:16%;}

@keyframes moveGlow{
    0%{transform:translate(0,0) rotate(0deg);}
    50%{transform:translate(30px,-20px) rotate(180deg);}
    100%{transform:translate(0,0) rotate(360deg);}
}

@keyframes floating{
    0%,100%{transform:translateY(0px);}
    50%{transform:translateY(-16px);}
}

.dashboard-wrapper{padding:14px;position:relative;z-index:10;}

.glass-container{
    background:rgba(255,255,255,0.38);
    border:1px solid rgba(255,255,255,0.8);
    backdrop-filter:blur(18px);
    border-radius:22px;
    padding:14px;
    box-shadow:0 10px 30px rgba(15,23,42,0.08);
}

.glass-inner{
    background:rgba(255,255,255,0.28);
    border:1px solid rgba(255,255,255,0.7);
    border-radius:18px;
    padding:14px;
}

.page-header{margin-bottom:14px;}
.page-header h1{font-size:22px;font-weight:700;margin-bottom:3px;}
.page-header p{color:#64748b;font-size:11px;}

.alert{
    padding:10px 12px;
    border-radius:12px;
    margin-bottom:12px;
    font-size:12px;
    background:rgba(255,255,255,.55);
    border:1px solid rgba(255,255,255,.8);
}

.stats-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
    margin-bottom:14px;
}

.stat-card{
    border-radius:16px;
    padding:12px;
    color:#fff;
    min-height:78px;
    position:relative;
    overflow:hidden;
}

.stat-card.total{background:linear-gradient(135deg,#3b82f6,#2563eb);}
.stat-card.success{background:linear-gradient(135deg,#10b981,#059669);}
.stat-card.danger{background:linear-gradient(135deg,#ef4444,#dc2626);}
.stat-value{font-size:16px;font-weight:700;}
.stat-label{margin-top:5px;font-size:11px;display:flex;align-items:center;gap:5px;}
.stat-label .material-icons-round{font-size:18px;}

.filter-card{
    margin-bottom:12px;
    padding:12px;
    background:rgba(255,255,255,0.34);
    border:1px solid rgba(255,255,255,.8);
    backdrop-filter:blur(16px);
    border-radius:18px;
}

.filter-form{width:100%;}
.filter-top{
    display:grid;
    grid-template-columns:repeat(5,minmax(145px,1fr));
    gap:10px;
    margin-bottom:10px;
}
.filter-bottom{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
}
.search-box{
    flex:1;
    display:flex;
    gap:6px;
    min-width:240px;
    align-items:center;
}
.search-box input{max-width:680px;}
.export-box{display:flex;gap:8px;flex-wrap:wrap;}

.form-control{
    width:100%;
    border:none;
    border-radius:10px;
    padding:9px 12px;
    font-size:12px;
    background:rgba(255,255,255,.65);
    border:1px solid rgba(255,255,255,.8);
    outline:none;
}

.form-control:focus{box-shadow:0 0 0 3px rgba(59,130,246,.14);}

.btn{
    border:none !important;
    border-radius:10px !important;
    padding:8px 12px !important;
    font-size:11px !important;
    font-weight:600 !important;
    cursor:pointer;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:4px;
    white-space:nowrap;
    transition:.2s;
}

.btn:hover{transform:translateY(-1px);}
.btn-primary{background:linear-gradient(135deg,#3b82f6,#2563eb)!important;color:#fff!important;}
.btn-success{background:linear-gradient(135deg,#10b981,#059669)!important;color:#fff!important;}
.btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626)!important;color:#fff!important;}
.btn-warning{background:linear-gradient(135deg,#f59e0b,#d97706)!important;color:#fff!important;}
.btn-secondary{background:#e2e8f0!important;color:#334155!important;}
.btn .material-icons-round{font-size:15px;}

.card{
    background:rgba(255,255,255,0.34);
    border:1px solid rgba(255,255,255,.8);
    backdrop-filter:blur(16px);
    border-radius:18px;
    padding:12px;
    margin-bottom:12px;
}

.card-title{
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:10px;
    font-size:14px;
    font-weight:700;
}

.table-wrapper{overflow:auto;border-radius:14px;}
table{width:100%;border-collapse:collapse;min-width:860px;}
thead th{background:rgba(219,234,254,0.75);padding:10px;font-size:11px;font-weight:700;color:#334155;}
tbody td{padding:10px;background:rgba(255,255,255,0.45);border-bottom:1px solid rgba(226,232,240,0.6);font-size:11px;}
tbody tr:hover{background:rgba(255,255,255,.65);}
.text-center{text-align:center;}
.text-right{text-align:right;}

.due-cell{display:flex;flex-direction:column;gap:5px;}
.tempo-badge{display:inline-flex;width:max-content;padding:3px 8px;border-radius:999px;font-size:10px;font-weight:700;line-height:1;}
.tempo-success{background:#dcfce7;color:#166534;}
.tempo-warning{background:#fef3c7;color:#92400e;}
.tempo-danger{background:#fee2e2;color:#991b1b;}
.tempo-muted{background:#e2e8f0;color:#475569;}

.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.45);
    display:none;
    align-items:center;
    justify-content:center;
    padding:16px;
    z-index:999;
}
.modal-overlay.active{display:flex;}
.modal{width:100%;max-width:400px;background:rgba(255,255,255,.95);border-radius:18px;padding:18px;}
.modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.modal-header h3{font-size:22px;font-weight:700;}
.modal-close{border:none;background:#f1f5f9;width:38px;height:38px;border-radius:12px;cursor:pointer;font-size:22px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;margin-bottom:6px;font-size:12px;font-weight:600;}

@media(max-width:900px){
    .stats-grid{grid-template-columns:1fr;}
    .filter-top{grid-template-columns:1fr;}
    .filter-bottom{flex-direction:column;align-items:stretch;}
    .search-box{width:100%;flex-direction:column;align-items:stretch;}
    .search-box input{max-width:100%;}
    .export-box{width:100%;}
    .export-box .btn{flex:1;}
}

@media(max-width:768px){
    .dashboard-wrapper{padding:14px;}
    .glass-container{padding:16px;}
    .glass-inner{padding:16px;}
    .page-header h1{font-size:24px;}
}
</style>

<div class="dashboard-wrapper">
    <div class="glass-container">
        <div class="glass-inner">
            <div class="page-header">
                <h1>Manajemen Piutang</h1>
                <p>Piutang otomatis dari faktur pembelian dan monitoring pembayaran.</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-value">Rp <?= number_format($summary['total_semua'] ?? 0,0,',','.') ?></div>
                    <div class="stat-label"><span class="material-icons-round">payments</span>Total Faktur</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-value">Rp <?= number_format($summary['total_lunas'] ?? 0,0,',','.') ?></div>
                    <div class="stat-label"><span class="material-icons-round">check_circle</span>Lunas (<?= $summary['count_lunas'] ?? 0 ?>)</div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-value">Rp <?= number_format($summary['total_belum_lunas'] ?? 0,0,',','.') ?></div>
                    <div class="stat-label"><span class="material-icons-round">cancel</span>Belum Lunas (<?= $summary['count_belum_lunas'] ?? 0 ?>)</div>
                </div>
            </div>

            <div class="filter-card">
                <form method="GET" class="filter-form">
                    <div class="filter-top">
                        <div class="form-group" style="margin-bottom:0;">
                            <label>PBF</label>
                            <select name="pbf" class="form-control">
                                <option value="">Semua PBF</option>
                                <?php foreach ($pbfList as $pbf): ?>
                                    <option value="<?= (int)$pbf['id_pbf'] ?>" <?= (int)($filterPbf ?? 0) === (int)$pbf['id_pbf'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pbf['nama_pbf']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua Status</option>
                                <option value="belum_lunas" <?= $filterStatus === 'belum_lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                                <option value="lunas" <?= $filterStatus === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label>Bulan Faktur</label>
                            <select name="bulan_angka" class="form-control">
                                <option value="" <?= $filterBulanAngka === '' ? 'selected' : '' ?>>Semua Bulan</option>
                                <option value="01" <?= $filterBulanAngka === '01' ? 'selected' : '' ?>>Januari</option>
                                <option value="02" <?= $filterBulanAngka === '02' ? 'selected' : '' ?>>Februari</option>
                                <option value="03" <?= $filterBulanAngka === '03' ? 'selected' : '' ?>>Maret</option>
                                <option value="04" <?= $filterBulanAngka === '04' ? 'selected' : '' ?>>April</option>
                                <option value="05" <?= $filterBulanAngka === '05' ? 'selected' : '' ?>>Mei</option>
                                <option value="06" <?= $filterBulanAngka === '06' ? 'selected' : '' ?>>Juni</option>
                                <option value="07" <?= $filterBulanAngka === '07' ? 'selected' : '' ?>>Juli</option>
                                <option value="08" <?= $filterBulanAngka === '08' ? 'selected' : '' ?>>Agustus</option>
                                <option value="09" <?= $filterBulanAngka === '09' ? 'selected' : '' ?>>September</option>
                                <option value="10" <?= $filterBulanAngka === '10' ? 'selected' : '' ?>>Oktober</option>
                                <option value="11" <?= $filterBulanAngka === '11' ? 'selected' : '' ?>>November</option>
                                <option value="12" <?= $filterBulanAngka === '12' ? 'selected' : '' ?>>Desember</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label>Tahun Faktur</label>
                            <input
                                type="number"
                                name="tahun"
                                class="form-control"
                                min="2000"
                                max="9999"
                                step="1"
                                placeholder="Contoh: <?= date('Y') ?>"
                                value="<?= htmlspecialchars($filterTahun ?? '') ?>"
                            >
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label>Tempo</label>
                            <select name="tempo" class="form-control">
                                <option value="" <?= $filterTempo === '' ? 'selected' : '' ?>>Semua Tempo</option>
                                <option value="no_due_date" <?= $filterTempo === 'no_due_date' ? 'selected' : '' ?>>Belum Diatur</option>
                                <option value="overdue" <?= $filterTempo === 'overdue' ? 'selected' : '' ?>>Lewat Tempo</option>
                                <option value="today" <?= $filterTempo === 'today' ? 'selected' : '' ?>>Jatuh Tempo Hari Ini</option>
                                <option value="due_soon" <?= $filterTempo === 'due_soon' ? 'selected' : '' ?>>Mendekati Tempo</option>
                                <option value="safe" <?= $filterTempo === 'safe' ? 'selected' : '' ?>>Masih Aman</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-bottom">
                        <div class="search-box">
                            <input
                                type="text"
                                name="search"
                                class="form-control autocomplete-input"
                                data-type="piutang"
                                placeholder="Cari no faktur / PBF / obat..."
                                value="<?= htmlspecialchars($search ?? '') ?>"
                                autocomplete="off"
                            >

                            <button type="submit" class="btn btn-primary btn-sm">
                                <span class="material-icons-round">search</span>Cari
                            </button>

                            <a href="?" class="btn btn-secondary btn-sm">
                                <span class="material-icons-round">refresh</span>Reset
                            </a>
                        </div>

                        <div class="export-box">
                            <a href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_excel<?= htmlspecialchars($exportSuffix) ?>" class="btn btn-success btn-sm">
                                <span class="material-icons-round">table_view</span>Excel
                            </a>

                            <a href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_pdf<?= htmlspecialchars($exportSuffix) ?>" class="btn btn-danger btn-sm" target="_blank">
                                <span class="material-icons-round">picture_as_pdf</span>PDF
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card" style="border-left:4px solid #3b82f6;">
                <strong>Catatan:</strong>
                Tambah/edit data faktur dilakukan dari menu
                <strong>Manajemen Stok</strong>.
                Halaman ini hanya mengambil data faktur dan mengelola status pembayaran.
            </div>

            <?php if ($filterStatus !== 'lunas'): ?>
            <div class="card" style="border-left:4px solid var(--danger);">
                <div class="card-title" style="color:var(--danger);">
                    <span class="material-icons-round">warning</span>
                    Belum Lunas (<?= count($listBelumLunas) ?>)
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Faktur</th>
                                <th>PBF</th>
                                <th>Tanggal</th>
                                <th>Jatuh Tempo</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($listBelumLunas)): ?>
                                <tr>
                                    <td colspan="9" class="text-center" style="padding:35px;color:#94a3b8;">
                                        Tidak ada piutang belum lunas sesuai filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($listBelumLunas as $i => $p): ?>
                                    <?php $tempoInfo = reminderTempo($p['tanggal_jatuh_tempo'] ?? null, $p['status'] ?? 'belum_lunas'); ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($p['no_faktur']) ?></strong></td>
                                        <td><?= htmlspecialchars($p['nama_pbf']) ?></td>
                                        <td><?= htmlspecialchars(formatTanggalPiutang($p['tanggal_faktur'])) ?></td>
                                        <td>
                                            <div class="due-cell">
                                                <span><?= htmlspecialchars($tempoInfo['tanggal']) ?></span>
                                                <span class="tempo-badge <?= htmlspecialchars($tempoInfo['class']) ?>">
                                                    <?= htmlspecialchars($tempoInfo['label']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-right"><?= (int)$p['jumlah_item'] ?></td>
                                        <td class="text-right">Rp <?= number_format($p['jumlah_harga'],0,',','.') ?></td>
                                        <td>
                                            <?php if ($p['bukti_pembayaran']): ?>
                                                <a href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= (int)$p['id_faktur'] ?>" class="btn btn-secondary btn-sm">
                                                    <span class="material-icons-round">attach_file</span>Lihat
                                                </a>
                                            <?php else: ?>
                                                <span style="color:#94a3b8;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-success btn-sm" onclick="lunasi(<?= (int)$p['id_faktur'] ?>,'<?= htmlspecialchars($p['no_faktur'], ENT_QUOTES) ?>')">
                                                <span class="material-icons-round">check_circle</span>Lunas
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($filterStatus !== 'belum_lunas'): ?>
            <div class="card" style="border-left:4px solid var(--success);">
                <div class="card-title" style="color:var(--success);">
                    <span class="material-icons-round">check_circle</span>
                    Lunas (<?= count($listLunas) ?>)
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Faktur</th>
                                <th>PBF</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Tanggal Lunas</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($listLunas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center" style="padding:35px;color:#94a3b8;">
                                        Tidak ada data lunas sesuai filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($listLunas as $i => $p): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($p['no_faktur']) ?></strong></td>
                                        <td><?= htmlspecialchars($p['nama_pbf']) ?></td>
                                        <td><?= htmlspecialchars(formatTanggalPiutang($p['tanggal_faktur'])) ?></td>
                                        <td class="text-right">Rp <?= number_format($p['jumlah_harga'],0,',','.') ?></td>
                                        <td><?= htmlspecialchars(formatTanggalPiutang($p['tanggal_lunas'] ?? null)) ?></td>
                                        <td>
                                            <?php if ($p['bukti_pembayaran']): ?>
                                                <a href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= (int)$p['id_faktur'] ?>" class="btn btn-secondary btn-sm">
                                                    <span class="material-icons-round">attach_file</span>Lihat
                                                </a>
                                            <?php else: ?>
                                                <span style="color:#94a3b8;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=belum_lunas" onsubmit="return confirm('Ubah faktur ini menjadi belum lunas?')">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id_faktur" value="<?= (int)$p['id_faktur'] ?>">
                                                <button type="submit" class="btn btn-warning btn-sm">
                                                    <span class="material-icons-round">undo</span>Belum
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div class="modal-overlay" id="modalLunasi">
                <div class="modal">
                    <div class="modal-header">
                        <h3>Tandai Faktur Lunas</h3>
                        <button class="modal-close" onclick="closeModal('modalLunasi')">&times;</button>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=lunasi" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <input type="hidden" name="id_faktur" id="lunasiId">

                        <p style="margin-bottom:16px;">Faktur: <strong id="lunasiFaktur"></strong></p>

                        <div class="form-group">
                            <label>Bukti Pembayaran (opsional)</label>
                            <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*,.pdf">
                            <small style="color:#64748b;display:block;margin-top:8px;">
                                Format JPG, PNG, WEBP, PDF maksimal 5MB.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <span class="material-icons-round">save</span>Simpan Status Lunas
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(id){
    document.getElementById(id).classList.add('active');
}

function closeModal(id){
    document.getElementById(id).classList.remove('active');
}

function lunasi(id,faktur){
    document.getElementById('lunasiId').value = id;
    document.getElementById('lunasiFaktur').textContent = faktur;
    openModal('modalLunasi');
}

document.querySelectorAll('.modal-overlay').forEach(function(el){
    el.addEventListener('click',function(e){
        if(e.target === el){
            closeModal(el.id);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>