<?php
/**
 * Manajemen Stok - Admin - Apotek Ananda Jadimulya
 * Sama seperti Super Admin tapi tanpa tombol Hapus obat.
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireLogin();
if (isSuperAdmin()) { redirect(BASE_URL . '/frontend/superadmin/manajemen_stok.php'); }

$pageTitle = 'Manajemen Stok';
require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/models/stok_masuk.php';
require_once __DIR__ . '/../../backend/models/pbf.php';

$stokModel = new StokMasuk();
$pbfModel  = new PBF();

$filterPbf = isset($_GET['pbf']) ? sanitizeInt($_GET['pbf']) : null;
$search    = isset($_GET['search']) ? sanitize($_GET['search']) : null;

$stokList = $stokModel->getAll($filterPbf, $search);
$pbfList  = $pbfModel->getAll();
$flash    = getFlashMessage();

$validSatuan = ['Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack'];

require_once __DIR__ . '/../templates/sidebar.php';
?>

    <div class="page-header">
        <h1>Manajemen Stok</h1>
        <p>Data obat masuk dari seluruh PBF</p>
    </div>
    
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    
    <div class="tab-filters">
        <button class="btn btn-success btn-sm" onclick="openModal('modalTambahPBF')">+ Tambah PBF</button>
        <a href="?<?= $search ? 'search='.urlencode($search) : '' ?>" class="tab-filter <?= !$filterPbf ? 'active' : '' ?>">Semua</a>
        <?php foreach ($pbfList as $pbf): ?>
            <a href="?pbf=<?= $pbf['id_pbf'] ?><?= $search ? '&search='.urlencode($search) : '' ?>" 
               class="tab-filter <?= $filterPbf == $pbf['id_pbf'] ? 'active' : '' ?>">
                <?= htmlspecialchars($pbf['nama_pbf']) ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <div class="filter-bar">
        <form method="GET" style="flex:1;display:flex;gap:10px;">
            <?php if ($filterPbf): ?><input type="hidden" name="pbf" value="<?= $filterPbf ?>"><?php endif; ?>
            <div style="flex:1; display:flex;">
                <input type="text" name="search" class="form-control autocomplete-input" data-type="stok_obat" placeholder="🔍 Cari obat..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <?php if ($search): ?><a href="?<?= $filterPbf ? 'pbf='.$filterPbf : '' ?>" class="btn btn-secondary btn-sm">Reset</a><?php endif; ?>
        </form>
        <button class="btn btn-primary" onclick="openModal('modalTambahObat')">+ Tambah Obat</button>
    </div>
    
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th><th>Nama Obat</th><th>PBF</th><th>No. Faktur</th><th>Tgl Masuk</th>
                        <th>Satuan</th><th>Exp Date</th><th>Harga Beli</th><th>Disc (%)</th><th>Jml</th><th>Total</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stokList)): ?>
                        <tr><td colspan="12" class="text-center" style="color:#94a3b8;padding:30px;">Belum ada data</td></tr>
                    <?php else: ?>
                        <?php foreach ($stokList as $i => $stok): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($stok['nama_obat']) ?></strong></td>
                            <td><?= htmlspecialchars($stok['nama_pbf']) ?></td>
                            <td><?= htmlspecialchars($stok['no_faktur']) ?></td>
                            <td><?= $stok['tanggal_masuk'] ?></td>
                            <td><?= htmlspecialchars($stok['satuan']) ?></td>
                            <td><?= $stok['expired_date'] ?></td>
                            <td class="text-right"><?= number_format($stok['harga_beli'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($stok['discount'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= $stok['jumlah_masuk'] ?></td>
                            <td class="text-right"><strong><?= number_format($stok['total'], 0, ',', '.') ?></strong></td>
                            <td>
                                <!-- Admin: hanya Edit, TIDAK ADA Hapus -->
                                <button class="btn btn-warning btn-sm" onclick='editStok(<?= json_encode($stok) ?>)'>✏️</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah PBF -->
<div class="modal-overlay" id="modalTambahPBF">
    <div class="modal">
        <div class="modal-header"><h3>Tambah PBF Baru</h3><button class="modal-close" onclick="closeModal('modalTambahPBF')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/pbf_controller.php?action=create">
            <?= csrfField() ?>
            <div class="form-group"><label>Nama PBF</label><input type="text" name="nama_pbf" class="form-control" required></div>
            <button type="submit" class="btn btn-primary">💾 Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Tambah Obat -->
<div class="modal-overlay" id="modalTambahObat">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Obat</h3><button class="modal-close" onclick="closeModal('modalTambahObat')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/stok_masuk_controller.php?action=create">
            <?= csrfField() ?>
            <div class="form-group"><label>Asal PBF *</label><select name="id_pbf" class="form-control" required><option value="">-- Pilih --</option><?php foreach ($pbfList as $p): ?><option value="<?= $p['id_pbf'] ?>"><?= htmlspecialchars($p['nama_pbf']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>No. Faktur *</label><input type="text" name="no_faktur" class="form-control" required></div>
            <div class="form-group"><label>Tanggal Masuk *</label><input type="date" name="tanggal_masuk" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
            <div class="form-group"><label>Nama Obat *</label><input type="text" name="nama_obat" class="form-control" required></div>
            <div class="form-group"><label>Satuan *</label><select name="satuan" class="form-control" required><option value="">--</option><?php foreach ($validSatuan as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Expired Date *</label><input type="date" name="expired_date" class="form-control" required></div>
            <div class="form-group"><label>Harga Beli *</label><input type="number" name="harga_beli" class="form-control" required min="0" step="1" id="addHargaBeli" oninput="calcTotalAdd()"></div>
            <div class="form-group"><label>Discount (%)</label><input type="number" name="discount" class="form-control" value="0" min="0" max="100" step="0.01" id="addDiscount" oninput="calcTotalAdd()"></div>
            <div class="form-group"><label>Jumlah Masuk *</label><input type="number" name="jumlah_masuk" class="form-control" required min="1" step="1" id="addJumlah" oninput="calcTotalAdd()"></div>
            <div class="form-group"><label>Total</label><input type="text" id="addTotal" class="form-control" readonly style="background:#f1f5f9;font-weight:600"></div>
            <button type="submit" class="btn btn-primary">💾 Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Edit Obat -->
<div class="modal-overlay" id="modalEditObat">
    <div class="modal">
        <div class="modal-header"><h3>Edit Obat</h3><button class="modal-close" onclick="closeModal('modalEditObat')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/stok_masuk_controller.php?action=update">
            <?= csrfField() ?>
            <input type="hidden" name="id_masuk" id="editIdMasuk">
            <div class="form-group"><label>PBF</label><select name="id_pbf" class="form-control" required id="editPbf"><?php foreach ($pbfList as $p): ?><option value="<?= $p['id_pbf'] ?>"><?= htmlspecialchars($p['nama_pbf']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>No. Faktur</label><input type="text" name="no_faktur" id="editFaktur" class="form-control" required></div>
            <div class="form-group"><label>Tanggal Masuk</label><input type="date" name="tanggal_masuk" id="editTanggal" class="form-control" required></div>
            <div class="form-group"><label>Nama Obat</label><input type="text" name="nama_obat" id="editNamaObat" class="form-control" required></div>
            <div class="form-group"><label>Satuan</label><select name="satuan" class="form-control" required id="editSatuan"><?php foreach ($validSatuan as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Expired Date</label><input type="date" name="expired_date" id="editExpired" class="form-control" required></div>
            <div class="form-group"><label>Harga Beli</label><input type="number" name="harga_beli" id="editHargaBeli" class="form-control" required min="0" step="1" oninput="calcTotalEdit()"></div>
            <div class="form-group"><label>Discount (%)</label><input type="number" name="discount" id="editDiscount" class="form-control" value="0" min="0" max="100" step="0.01" oninput="calcTotalEdit()"></div>
            <div class="form-group"><label>Jumlah</label><input type="number" name="jumlah_masuk" id="editJumlah" class="form-control" required min="1" step="1" oninput="calcTotalEdit()"></div>
            <div class="form-group"><label>Total</label><input type="text" id="editTotal" class="form-control" readonly style="background:#f1f5f9;font-weight:600"></div>
            <button type="submit" class="btn btn-primary">💾 Update</button>
        </form>
    </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('active')}
function closeModal(id){document.getElementById(id).classList.remove('active')}
function calcTotalAdd(){var h=parseFloat(document.getElementById('addHargaBeli').value)||0;var d=parseFloat(document.getElementById('addDiscount').value)||0;var j=parseInt(document.getElementById('addJumlah').value)||0;document.getElementById('addTotal').value='Rp '+((h * (1 - d/100)) * j).toLocaleString('id-ID')}
function calcTotalEdit(){var h=parseFloat(document.getElementById('editHargaBeli').value)||0;var d=parseFloat(document.getElementById('editDiscount').value)||0;var j=parseInt(document.getElementById('editJumlah').value)||0;document.getElementById('editTotal').value='Rp '+((h * (1 - d/100)) * j).toLocaleString('id-ID')}
function editStok(data){document.getElementById('editIdMasuk').value=data.id_masuk;document.getElementById('editPbf').value=data.id_pbf;document.getElementById('editFaktur').value=data.no_faktur;document.getElementById('editTanggal').value=data.tanggal_masuk;document.getElementById('editNamaObat').value=data.nama_obat;document.getElementById('editSatuan').value=data.satuan;document.getElementById('editExpired').value=data.expired_date;document.getElementById('editHargaBeli').value=data.harga_beli;document.getElementById('editDiscount').value=data.discount;document.getElementById('editJumlah').value=data.jumlah_masuk;calcTotalEdit();openModal('modalEditObat')}
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
