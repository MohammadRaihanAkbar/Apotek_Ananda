<?php
/**
 * Piutang - Otomatis dari faktur.
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

$pageTitle = 'Manajemen Piutang';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../backend/models/piutang.php';

$model = new Piutang();
$filterStatus = $_GET['status'] ?? null;
$filterBulan  = $_GET['bulan'] ?? null;
$search       = isset($_GET['search']) ? sanitize($_GET['search']) : null;

$piutangList = $model->getAll($filterStatus, $filterBulan, $search);
$summary     = $model->getSummary($filterBulan);
$months      = $model->getAvailableMonths();
$flash       = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="page-header">
    <h1>Manajemen Piutang</h1>
    <p>Piutang otomatis muncul dari faktur. Di sini hanya mengubah status lunas / belum lunas.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">Rp <?= number_format($summary['total_semua'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-label">💰 Total Faktur</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value">Rp <?= number_format($summary['total_lunas'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-label">✅ Lunas (<?= $summary['count_lunas'] ?? 0 ?>)</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value">Rp <?= number_format($summary['total_belum_lunas'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-label">❌ Belum Lunas (<?= $summary['count_belum_lunas'] ?? 0 ?>)</div>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;flex:1;">
        <select name="bulan" class="form-control" style="width:auto;min-width:150px;">
            <option value="">Semua Bulan</option>
            <?php foreach ($months as $m): ?>
                <option value="<?= $m ?>" <?= $filterBulan === $m ? 'selected' : '' ?>><?= $m ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-control" style="width:auto;min-width:140px;">
            <option value="">Semua Status</option>
            <option value="lunas" <?= $filterStatus === 'lunas' ? 'selected' : '' ?>>Lunas</option>
            <option value="belum_lunas" <?= $filterStatus === 'belum_lunas' ? 'selected' : '' ?>>Belum Lunas</option>
        </select>
        <div style="flex:1; display:flex; min-width:220px;">
            <input type="text" name="search" class="form-control autocomplete-input" data-type="piutang" placeholder="🔍 Cari faktur/PBF..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="?" class="btn btn-secondary btn-sm">Reset</a>
    </form>
    <div style="display:flex;gap:8px;">
        <a href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_excel&bulan=<?= urlencode($filterBulan ?? '') ?>&status=<?= urlencode($filterStatus ?? '') ?>&search=<?= urlencode($search ?? '') ?>" class="btn btn-success btn-sm">📊 Excel</a>
        <a href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_pdf&bulan=<?= urlencode($filterBulan ?? '') ?>&status=<?= urlencode($filterStatus ?? '') ?>&search=<?= urlencode($search ?? '') ?>" class="btn btn-danger btn-sm" target="_blank">📄 PDF</a>
    </div>
</div>

<div class="card" style="background:#f8fafc;border-style:dashed;">
    <strong>Catatan:</strong> Tambah/edit data faktur dilakukan dari menu <strong>Manajemen Stok</strong>. Halaman ini hanya mengambil data faktur dan mengelola status pembayaran.
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>No</th><th>No. Faktur</th><th>PBF</th><th>Tgl Faktur</th><th>Jatuh Tempo</th><th>Item</th><th>Total Faktur</th><th>Status</th><th>Tgl Lunas</th><th>Bukti</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($piutangList)): ?>
                    <tr><td colspan="11" class="text-center" style="color:#94a3b8;padding:30px;">Tidak ada data faktur/piutang</td></tr>
                <?php else: ?>
                    <?php foreach ($piutangList as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($p['no_faktur']) ?></strong></td>
                        <td><?= htmlspecialchars($p['nama_pbf']) ?></td>
                        <td><?= htmlspecialchars($p['tanggal_faktur']) ?></td>
                        <td><?= htmlspecialchars($p['tanggal_jatuh_tempo'] ?? '-') ?></td>
                        <td class="text-right"><?= (int)$p['jumlah_item'] ?></td>
                        <td class="text-right">Rp <?= number_format($p['jumlah_harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($p['status'] === 'lunas'): ?>
                                <span class="badge badge-success">Lunas</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Belum Lunas</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['tanggal_lunas'] ?? '-') ?></td>
                        <td>
                            <?php if ($p['bukti_pembayaran']): ?>
                                <a href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= $p['id_faktur'] ?>" class="btn btn-sm btn-secondary">📎 Lihat</a>
                            <?php else: ?>
                                <span style="color:#94a3b8">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <?php if ($p['status'] === 'belum_lunas'): ?>
                                    <button class="btn btn-success btn-sm" onclick="lunasi(<?= $p['id_faktur'] ?>, '<?= htmlspecialchars($p['no_faktur']) ?>')">✅ Lunas</button>
                                <?php else: ?>
                                    <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=belum_lunas" onsubmit="return confirm('Ubah faktur ini menjadi belum lunas?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id_faktur" value="<?= $p['id_faktur'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">↩ Belum</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalLunasi">
    <div class="modal">
        <div class="modal-header"><h3>Tandai Faktur Lunas</h3><button class="modal-close" onclick="closeModal('modalLunasi')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=lunasi" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="id_faktur" id="lunasiId">
            <p style="margin-bottom:15px;">Faktur: <strong id="lunasiFaktur"></strong></p>
            <div class="form-group">
                <label>Bukti Pembayaran (opsional)</label>
                <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*,.pdf">
                <small style="color:#64748b;">Format: JPG, PNG, WebP, PDF. Maks 5MB.</small>
            </div>
            <button type="submit" class="btn btn-success">✅ Simpan Status Lunas</button>
        </form>
    </div>
</div>

<script>
function lunasi(id,faktur){
    document.getElementById('lunasiId').value = id;
    document.getElementById('lunasiFaktur').textContent = faktur;
    openModal('modalLunasi');
}
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
