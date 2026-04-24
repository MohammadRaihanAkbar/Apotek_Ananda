<?php
/**
 * Laporan Kadaluwarsa - Super Admin - Apotek Ananda Jadimulya
 * Desain premium sesuai screenshot.
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

$pageTitle = 'Laporan Kadaluwarsa';
require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/models/obat_expired.php';
$model = new ObatExpired();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
$expiredList = $model->getCombinedExpiredReport($search);
$stats = $model->getSummaryStats();
$flash = getFlashMessage();
$validSatuan = ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];

require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="page-header">
    <h1>Laporan Kadaluwarsa</h1>
    <p>Pantau obat yang sudah expired atau mendekati masa kadaluwarsa.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Filter Bar Premium -->
<div class="filter-container">
    <div class="filter-item">
        <span class="material-icons-round" style="font-size:18px;">filter_list</span>
        <span>Filter Laporan</span>
    </div>
    <select class="form-select-minimal"><option>Bulan</option></select>
    <select class="form-select-minimal"><option>Tahun</option></select>
    <select class="form-select-minimal"><option>Tanggal</option></select>
    <div style="flex:1"></div>
    <form method="GET" style="display:flex; gap:10px;">
        <div style="width:250px; display:flex;">
            <input type="text" name="search" class="form-control autocomplete-input" data-type="expired" placeholder="🔍 Cari nama obat..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off" style="font-size:12px; height:36px; border-radius:8px;">
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="height:36px;">Cari</button>
        <?php if ($search): ?><a href="?" class="btn btn-secondary btn-sm" style="height:36px; display:flex; align-items:center;">Reset</a><?php endif; ?>
    </form>
</div>

<!-- Stats Cards Premium -->
<div class="stats-grid">
    <div class="stat-card danger">
        <div class="icon-box"><span class="material-icons-round">warning</span></div>
        <div class="label">EXPIRED ITEM</div>
        <div class="value"><?= number_format($stats['expired_count']) ?></div>
        <div class="sub-label">Need immediate disposal</div>
    </div>
    
    <div class="stat-card warning">
        <div class="icon-box"><span class="material-icons-round">hourglass_bottom</span></div>
        <div class="label">NEARING (30 DAYS)</div>
        <div class="value"><?= number_format($stats['nearing_count']) ?></div>
        <div class="sub-label">Prioritize first-out usage</div>
    </div>
    
    <div class="stat-card primary" style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff;">
        <div class="icon-box" style="background: rgba(255,255,255,0.2); color:#fff;"><span class="material-icons-round">payments</span></div>
        <div class="label" style="color: rgba(255,255,255,0.8);">POTENSIAL LOSS</div>
        <div class="value" style="color:#fff;">Rp <?= number_format($stats['potential_loss'] / 1000000, 1, ',', '.') ?>jt</div>
        <div class="sub-label" style="color: rgba(255,255,255,0.7);">Estimated value of stock</div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>NAMA OBAT</th>
                    <th>QTY</th>
                    <th>SATUAN</th>
                    <th>BATCH</th>
                    <th>EXPIRED</th>
                    <th>HARGA/PCS</th>
                    <th>NAMA PBF</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expiredList)): ?>
                    <tr><td colspan="9" class="text-center" style="padding:40px; color:#94a3b8;">Tidak ada data kadaluwarsa ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($expiredList as $i => $item): ?>
                    <tr>
                        <td style="font-weight:600; color:#94a3b8;"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                        <td>
                            <div style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($item['nama_obat']) ?></div>
                            <div style="font-size:10px; color:#94a3b8; text-transform:uppercase;"><?= $item['sumber'] === 'manual' ? 'Manual Input' : 'Auto Detect' ?></div>
                        </td>
                        <td style="font-weight:500;"><?= $item['qty'] ?></td>
                        <td><?= htmlspecialchars($item['satuan']) ?></td>
                        <td style="font-family:monospace;"><?= htmlspecialchars($item['batch'] ?? '-') ?></td>
                        <td>
                            <?php 
                            $expDate = new DateTime($item['expired_date']);
                            $today = new DateTime();
                            $color = ($expDate <= $today) ? 'var(--danger)' : 'var(--warning)';
                            ?>
                            <span style="color: <?= $color ?>; font-weight:600; font-size:11px;">
                                <?= strtoupper(date('d M Y', strtotime($item['expired_date']))) ?>
                            </span>
                        </td>
                        <td>Rp <?= number_format($item['harga_beli'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($item['nama_pbf'] ?? '-') ?></td>
                        <td>
                            <div style="display:flex; gap:5px;">
                                <?php if ($item['sumber'] === 'manual'): ?>
                                    <button class="btn btn-outline btn-sm" onclick='editExpired(<?= json_encode($item) ?>)' title="Edit"><span class="material-icons-round" style="font-size:16px;">edit</span></button>
                                    <form method="POST" action="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=delete" onsubmit="return confirm('Hapus data ini?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id_expired" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger);" title="Hapus"><span class="material-icons-round" style="font-size:16px;">delete</span></button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-outline btn-sm" onclick='editBatchOtomatis(<?= json_encode($item) ?>)' title="Edit Batch"><span class="material-icons-round" style="font-size:16px;">edit</span></button>
                                    <span class="badge badge-info" style="margin-left:5px;">Auto</span>
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

<!-- Export Report Box -->
<div class="card" style="background: #f8fafc; border-style: dashed;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div style="font-weight:700; color:#1e293b; margin-bottom:4px;">EXPORT REPORT</div>
            <div style="font-size:12px; color:#64748b; max-width:400px;">Buat laporan lengkap dalam format PDF atau Excel untuk inventaris apotek dan dokumentasi audit.</div>
        </div>
        <div style="display:flex; gap:12px;">
            <a href="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=export_pdf&search=<?= urlencode($search ?? '') ?>" class="btn btn-outline" target="_blank">
                <span class="material-icons-round">download</span> Download PDF
            </a>
            <a href="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=export_excel&search=<?= urlencode($search ?? '') ?>" class="btn btn-outline">
                <span class="material-icons-round">table_view</span> Export Excel
            </a>
        </div>
    </div>
</div>

<!-- Action Floating Button (Optional) -->
<button class="btn btn-primary" style="position:fixed; bottom:40px; right:40px; border-radius:50px; padding:15px 25px; box-shadow:var(--shadow-lg);" onclick="openModal('modalTambahExpired')">
    <span class="material-icons-round">add</span> Tambah Data Manual
</button>

<!-- Modals same as before but styled -->
<div class="modal-overlay" id="modalTambahExpired">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Data Kadaluwarsa Manual</h3>
            <button class="modal-close" onclick="closeModal('modalTambahExpired')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=create">
            <?= csrfField() ?>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Nama Obat *</label>
                    <input type="text" name="nama_obat" class="form-control" required placeholder="Contoh: Amoxicillin 500mg">
                </div>
                <div class="form-group">
                    <label>Qty *</label>
                    <input type="number" name="qty" class="form-control" required min="1">
                </div>
                <div class="form-group">
                    <label>Satuan *</label>
                    <select name="satuan" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($validSatuan as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>No. Batch</label>
                    <input type="text" name="batch" class="form-control" placeholder="B22024X">
                </div>
                <div class="form-group">
                    <label>Expired Date *</label>
                    <input type="date" name="expired_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga Beli / Pcs *</label>
                    <input type="number" name="harga_beli" class="form-control" required min="0">
                </div>
                <div class="form-group">
                    <label>Nama PBF</label>
                    <input type="text" name="nama_pbf" class="form-control" placeholder="Kimia Farma">
                </div>
            </div>
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalTambahExpired')">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalEditExpired">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Data Kadaluwarsa</h3>
            <button class="modal-close" onclick="closeModal('modalEditExpired')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/expired_controller.php?action=update">
            <?= csrfField() ?>
            <input type="hidden" name="id_expired" id="expEditId">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Nama Obat</label>
                    <input type="text" name="nama_obat" id="expEditNama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Qty</label>
                    <input type="number" name="qty" id="expEditQty" class="form-control" required min="1">
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <select name="satuan" class="form-control" required id="expEditSatuan">
                        <?php foreach ($validSatuan as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>No. Batch</label>
                    <input type="text" name="batch" id="expEditBatch" class="form-control">
                </div>
                <div class="form-group">
                    <label>Expired Date</label>
                    <input type="date" name="expired_date" id="expEditDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga Beli</label>
                    <input type="number" name="harga_beli" id="expEditHarga" class="form-control" required min="0">
                </div>
                <div class="form-group">
                    <label>Nama PBF</label>
                    <input type="text" name="nama_pbf" id="expEditPbf" class="form-control">
                </div>
            </div>
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditExpired')">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Batch Otomatis -->
<div class="modal-overlay" id="modalEditBatchOtomatis">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header">
            <h3>Update No. Batch</h3>
            <button class="modal-close" onclick="closeModal('modalEditBatchOtomatis')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/stok_masuk_controller.php?action=update_batch">
            <?= csrfField() ?>
            <input type="hidden" name="id_masuk" id="batchEditId">
            <div class="form-group">
                <label>Nama Obat</label>
                <input type="text" id="batchEditNama" class="form-control" disabled style="background:#f1f5f9;">
            </div>
            <div class="form-group">
                <label>No. Batch Baru</label>
                <input type="text" name="batch" id="batchEditValue" class="form-control" required placeholder="Contoh: B2401">
            </div>
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditBatchOtomatis')">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Batch</button>
            </div>
        </form>
    </div>
</div>

<script>
function editExpired(d) {
    document.getElementById('expEditId').value = d.id;
    document.getElementById('expEditNama').value = d.nama_obat;
    document.getElementById('expEditQty').value = d.qty;
    document.getElementById('expEditSatuan').value = d.satuan;
    document.getElementById('expEditBatch').value = d.batch || '';
    document.getElementById('expEditDate').value = d.expired_date;
    document.getElementById('expEditHarga').value = d.harga_beli;
    document.getElementById('expEditPbf').value = d.nama_pbf || '';
    openModal('modalEditExpired');
}

function editBatchOtomatis(d) {
    document.getElementById('batchEditId').value = d.id_masuk;
    document.getElementById('batchEditNama').value = d.nama_obat;
    document.getElementById('batchEditValue').value = d.batch || '';
    openModal('modalEditBatchOtomatis');
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
