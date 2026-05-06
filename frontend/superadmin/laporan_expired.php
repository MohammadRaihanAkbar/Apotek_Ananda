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
    'status' => in_array(($_GET['status'] ?? ''), ['expired', 'segera_expired'], true) ? $_GET['status'] : null,
];

$expiredList = $model->getExpiredReport($filters);
$stats = $model->getSummaryStats($filters);
$pbfList = $pbfModel->getAll();
$flash = getFlashMessage();
$queryString = http_build_query(array_filter($filters, fn($v) => $v !== null && $v !== ''));

require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="page-header">
    <h1>Laporan Kadaluwarsa</h1>
    <p>Data otomatis dari batch obat yang sudah expired atau akan expired maksimal 6 bulan dari hari ini.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card danger">
        <div class="stat-value"><?= number_format($stats['expired_count'] ?? 0) ?></div>
        <div class="stat-label">Expired</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value"><?= number_format($stats['six_month_count'] ?? 0) ?></div>
        <div class="stat-label">Akan Expired ≤ 6 Bulan</div>
    </div>
    <div class="stat-card primary" style="background: linear-gradient(135deg, #1e40af, #3b82f6); color:#fff;">
        <div class="stat-value" style="color:#fff;">Rp <?= number_format($stats['potential_loss'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-label" style="color:rgba(255,255,255,.85);">Estimasi Nilai Stok</div>
    </div>
</div>

<div class="card">
    <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;align-items:end;">
        <div class="form-group" style="margin-bottom:0;">
            <label>PBF</label>
            <select name="pbf_id" class="form-control">
                <option value="">Semua PBF</option>
                <?php foreach ($pbfList as $pbf): ?>
                    <option value="<?= $pbf['id_pbf'] ?>" <?= (int)($filters['pbf_id'] ?? 0) === (int)$pbf['id_pbf'] ? 'selected' : '' ?>><?= htmlspecialchars($pbf['nama_pbf']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Dari Exp Date</label>
            <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($filters['date_start'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Sampai Exp Date</label>
            <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($filters['date_end'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">Semua</option>
                <option value="expired" <?= $filters['status'] === 'expired' ? 'selected' : '' ?>>Sudah Expired</option>
                <option value="segera_expired" <?= $filters['status'] === 'segera_expired' ? 'selected' : '' ?>>Akan Expired</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Nama Obat</label>
            <input type="text" name="nama_obat" class="form-control autocomplete-input" data-type="expired" placeholder="Cari obat..." value="<?= htmlspecialchars($filters['nama_obat'] ?? '') ?>" autocomplete="off">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="?" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="filter-bar" style="justify-content:flex-end;">
    <a href="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=export_pdf&<?= htmlspecialchars($queryString) ?>" class="btn btn-danger btn-sm" target="_blank">📄 PDF</a>
    <a href="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=export_excel&<?= htmlspecialchars($queryString) ?>" class="btn btn-success btn-sm">📊 Excel</a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Obat</th>
                    <th>No Batch</th>
                    <th>Exp Date</th>
                    <th>Sisa Hari</th>
                    <th>Qty</th>
                    <th>Satuan</th>
                    <th>PBF</th>
                    <th>No Faktur</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expiredList)): ?>
                    <tr><td colspan="10" class="text-center" style="padding:40px;color:#94a3b8;">Tidak ada batch expired/mendekati expired.</td></tr>
                <?php else: ?>
                    <?php foreach ($expiredList as $i => $item): ?>
                        <?php $sisa = (int)$item['sisa_hari']; ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($item['nama_obat']) ?></strong>
                            </td>
                            <td><code><?= htmlspecialchars($item['batch']) ?></code></td>
                            <td><?= htmlspecialchars($item['expired_date']) ?></td>
                            <td class="text-right"><?= $sisa ?></td>
                            <td class="text-right"><?= (int)$item['qty'] ?></td>
                            <td><?= htmlspecialchars($item['satuan']) ?></td>
                            <td><?= htmlspecialchars($item['nama_pbf']) ?></td>
                            <td><?= htmlspecialchars($item['no_faktur']) ?></td>
                            <td>
                                <?php if ($sisa < 0): ?>
                                    <span class="badge badge-danger">Sudah Expired</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">≤ 6 Bulan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
