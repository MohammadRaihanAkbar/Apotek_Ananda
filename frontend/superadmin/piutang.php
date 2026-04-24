<?php
/**
 * Piutang - Super Admin - Apotek Ananda Jadimulya
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

$piutangList   = $model->getAll($filterStatus, $filterBulan, $search);
$summary       = $model->getSummary($filterBulan);
$months        = $model->getAvailableMonths();
$flash         = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>

    <div class="page-header">
        <h1>Manajemen Piutang</h1>
        <p>Pencatatan dan pemantauan piutang ke PBF</p>
    </div>
    
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    
    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">Rp <?= number_format($summary['total_semua'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label">💰 Total Piutang</div>
        </div>
        <div class="stat-card success">
            <div class="stat-value">Rp <?= number_format($summary['total_lunas'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label">✅ Total Lunas (<?= $summary['count_lunas'] ?? 0 ?>)</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-value">Rp <?= number_format($summary['total_belum_lunas'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label">❌ Belum Lunas (<?= $summary['count_belum_lunas'] ?? 0 ?>)</div>
        </div>
    </div>
    
    <!-- Filters -->
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
            <div style="flex:1; display:flex; min-width:200px;">
                <input type="text" name="search" class="form-control autocomplete-input" data-type="piutang" style="width:100%;" placeholder="🔍 Cari faktur/PBF..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="?" class="btn btn-secondary btn-sm">Reset</a>
        </form>
        <div style="display:flex;gap:8px;">
            <a href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_excel&bulan=<?= urlencode($filterBulan ?? '') ?>&status=<?= urlencode($filterStatus ?? '') ?>" class="btn btn-success btn-sm">📊 Excel</a>
            <a href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_pdf&bulan=<?= urlencode($filterBulan ?? '') ?>&status=<?= urlencode($filterStatus ?? '') ?>" class="btn btn-danger btn-sm" target="_blank">📄 PDF</a>
            <button class="btn btn-primary" onclick="openModal('modalTambahPiutang')">+ Tambah Piutang</button>
        </div>
    </div>
    
    <!-- Table -->
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>No</th><th>No. Faktur</th><th>PBF</th><th>Tgl Faktur</th><th>Jatuh Tempo</th><th>Jumlah Harga</th><th>Status</th><th>Tgl Lunas</th><th>Bukti</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($piutangList)): ?>
                        <tr><td colspan="10" class="text-center" style="color:#94a3b8;padding:30px;">Tidak ada data piutang</td></tr>
                    <?php else: ?>
                        <?php foreach ($piutangList as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($p['no_faktur']) ?></strong></td>
                            <td><?= htmlspecialchars($p['nama_pbf']) ?></td>
                            <td><?= $p['tanggal_faktur'] ?></td>
                            <td><?= $p['tanggal_jatuh_tempo'] ?></td>
                            <td class="text-right">Rp <?= number_format($p['jumlah_harga'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($p['status'] === 'lunas'): ?>
                                    <span class="badge badge-success">Lunas</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $p['tanggal_lunas'] ?? '-' ?></td>
                            <td>
                                <?php if ($p['bukti_pembayaran']): ?>
                                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($p['bukti_pembayaran']) ?>" target="_blank" class="btn btn-sm btn-secondary">📎 Lihat</a>
                                <?php else: ?>
                                    <span style="color:#94a3b8">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status'] === 'belum_lunas'): ?>
                                    <button class="btn btn-success btn-sm" onclick="lunasi(<?= $p['id_piutang'] ?>, '<?= htmlspecialchars($p['no_faktur']) ?>')">✅ Lunasi</button>
                                    <button class="btn btn-warning btn-sm" onclick='editPiutang(<?= json_encode($p) ?>)'>✏️</button>
                                <?php endif; ?>
                                <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=delete" style="display:inline" onsubmit="return confirm('Hapus piutang ini?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id_piutang" value="<?= $p['id_piutang'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Piutang -->
<div class="modal-overlay" id="modalTambahPiutang">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Piutang Baru</h3><button class="modal-close" onclick="closeModal('modalTambahPiutang')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=create" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="form-group"><label>No. Faktur *</label><input type="text" name="no_faktur" class="form-control" required></div>
            <div class="form-group"><label>Nama PBF *</label><input type="text" name="nama_pbf" class="form-control" required></div>
            <div class="form-group"><label>Tanggal Faktur *</label><input type="date" name="tanggal_faktur" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
            <div class="form-group"><label>Tanggal Jatuh Tempo *</label><input type="date" name="tanggal_jatuh_tempo" class="form-control" required></div>
            <div class="form-group"><label>Jumlah Harga *</label><input type="number" name="jumlah_harga" class="form-control" required min="1" step="1"></div>
            <div class="form-group"><label>Bukti Pembayaran (opsional)</label><input type="file" name="bukti_pembayaran" class="form-control" accept="image/*,.pdf"></div>
            <button type="submit" class="btn btn-primary">💾 Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Edit Piutang -->
<div class="modal-overlay" id="modalEditPiutang">
    <div class="modal">
        <div class="modal-header"><h3>Edit Piutang</h3><button class="modal-close" onclick="closeModal('modalEditPiutang')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=update">
            <?= csrfField() ?>
            <input type="hidden" name="id_piutang" id="piutEditId">
            <div class="form-group"><label>No. Faktur</label><input type="text" name="no_faktur" id="piutEditFaktur" class="form-control" required></div>
            <div class="form-group"><label>Nama PBF</label><input type="text" name="nama_pbf" id="piutEditPbf" class="form-control" required></div>
            <div class="form-group"><label>Tanggal Faktur</label><input type="date" name="tanggal_faktur" id="piutEditTglFaktur" class="form-control" required></div>
            <div class="form-group"><label>Jatuh Tempo</label><input type="date" name="tanggal_jatuh_tempo" id="piutEditJtTempo" class="form-control" required></div>
            <div class="form-group"><label>Jumlah Harga</label><input type="number" name="jumlah_harga" id="piutEditHarga" class="form-control" required min="1"></div>
            <button type="submit" class="btn btn-primary">💾 Update</button>
        </form>
    </div>
</div>

<!-- Modal Lunasi Piutang -->
<div class="modal-overlay" id="modalLunasi">
    <div class="modal">
        <div class="modal-header"><h3>Lunasi Piutang</h3><button class="modal-close" onclick="closeModal('modalLunasi')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=lunasi" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="id_piutang" id="lunasiId">
            <p style="margin-bottom:15px;">Melunasi piutang faktur: <strong id="lunasiFaktur"></strong></p>
            <div class="form-group">
                <label>Upload Bukti Pembayaran (WAJIB) *</label>
                <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*,.pdf" required>
                <small style="color:#64748b;">Format: JPG, PNG, WebP, PDF. Maks 5MB</small>
            </div>
            <button type="submit" class="btn btn-success">✅ Konfirmasi Pelunasan</button>
        </form>
    </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('active')}
function closeModal(id){document.getElementById(id).classList.remove('active')}
function editPiutang(d){document.getElementById('piutEditId').value=d.id_piutang;document.getElementById('piutEditFaktur').value=d.no_faktur;document.getElementById('piutEditPbf').value=d.nama_pbf;document.getElementById('piutEditTglFaktur').value=d.tanggal_faktur;document.getElementById('piutEditJtTempo').value=d.tanggal_jatuh_tempo;document.getElementById('piutEditHarga').value=d.jumlah_harga;openModal('modalEditPiutang')}
function lunasi(id,faktur){document.getElementById('lunasiId').value=id;document.getElementById('lunasiFaktur').textContent=faktur;openModal('modalLunasi')}
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
