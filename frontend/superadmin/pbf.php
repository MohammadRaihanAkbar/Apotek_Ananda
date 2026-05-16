<?php
/**
 * Data PBF - Master data PBF khusus Super Admin.
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

$pageTitle = 'Manajemen PBF';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../backend/models/pbf.php';

$model = new PBF();
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$pbfList = $model->getAll($search);
$flash = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>
<div class="dashboard-wrapper">
  <div class="glass-container">
    <div class="glass-inner">
<div class="page-header">
    <h1>Manajemen PBF</h1>
    <p>Master data PBF dikelola oleh Super Admin dan dipakai sebagai dropdown di Manajemen Stok.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="filter-bar">
    <form method="GET" style="display:flex;gap:8px;flex:1;min-width:260px;">
        <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama PBF, kontak, telepon, alamat..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <?php if ($search !== ''): ?><a href="?" class="btn btn-secondary btn-sm">Reset</a><?php endif; ?>
    </form>
    <button class="btn btn-primary" onclick="openModal('modalTambahPBF')">+ Tambah PBF</button>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>No</th><th>Nama PBF</th><th>Kontak</th><th>Alamat</th><th>Jumlah Faktur</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($pbfList)): ?>
                    <tr><td colspan="6" class="text-center" style="color:#94a3b8;padding:30px;">Tidak ada data PBF yang cocok</td></tr>
                <?php else: ?>
                    <?php foreach ($pbfList as $i => $pbf): $stokCount = $model->countStokByPBF((int)$pbf['id_pbf']); ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($pbf['nama_pbf']) ?></strong>
                            <div style="font-size:11px;color:#94a3b8;">Dibuat: <?= htmlspecialchars($pbf['created_by'] ?? '-') ?></div>
                        </td>
                        <td>
                            <?= htmlspecialchars($pbf['kontak_person'] ?? '-') ?><br>
                            <span style="font-size:12px;color:#64748b;"><?= htmlspecialchars($pbf['no_telepon'] ?? '-') ?></span>
                        </td>
                        <td style="max-width:260px;"><?= htmlspecialchars($pbf['alamat'] ?? '-') ?></td>
                        <td class="text-right"><?= $stokCount ?></td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <button class="btn btn-secondary btn-sm" onclick='detailPBF(<?= json_encode($pbf) ?>)'>Detail</button>
                                <button class="btn btn-warning btn-sm" onclick='editPBF(<?= json_encode($pbf) ?>)'>✏️ Edit</button>
                                <form method="POST" action="<?= BASE_URL ?>/backend/controllers/pbf_controller.php?action=delete" onsubmit="return confirm('Hapus PBF ini?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id_pbf" value="<?= $pbf['id_pbf'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" <?= $stokCount > 0 ? 'disabled title="PBF sudah dipakai di faktur"' : '' ?>>🗑️ Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalTambahPBF">
    <div class="modal">
        <div class="modal-header"><h3>Tambah PBF Baru</h3><button class="modal-close" onclick="closeModal('modalTambahPBF')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/pbf_controller.php?action=create">
            <?= csrfField() ?>
            <div class="form-group"><label>Nama PBF *</label><input type="text" name="nama_pbf" class="form-control" required placeholder="Contoh: Kimia Farma"></div>
            <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control" rows="3"></textarea></div>
            <div class="form-group"><label>No. Telepon</label><input type="text" name="no_telepon" class="form-control"></div>
            <div class="form-group"><label>Kontak Person</label><input type="text" name="kontak_person" class="form-control"></div>
            <div class="form-group"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="3"></textarea></div>
            <button type="submit" class="btn btn-primary">💾 Simpan PBF</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalEditPBF">
    <div class="modal">
        <div class="modal-header"><h3>Edit PBF</h3><button class="modal-close" onclick="closeModal('modalEditPBF')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/pbf_controller.php?action=update">
            <?= csrfField() ?>
            <input type="hidden" name="id_pbf" id="editIdPbf">
            <div class="form-group"><label>Nama PBF *</label><input type="text" name="nama_pbf" id="editNamaPbf" class="form-control" required></div>
            <div class="form-group"><label>Alamat</label><textarea name="alamat" id="editAlamat" class="form-control" rows="3"></textarea></div>
            <div class="form-group"><label>No. Telepon</label><input type="text" name="no_telepon" id="editTelepon" class="form-control"></div>
            <div class="form-group"><label>Kontak Person</label><input type="text" name="kontak_person" id="editKontak" class="form-control"></div>
            <div class="form-group"><label>Keterangan</label><textarea name="keterangan" id="editKeterangan" class="form-control" rows="3"></textarea></div>
            <button type="submit" class="btn btn-primary">💾 Update PBF</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalDetailPBF">
    <div class="modal">
        <div class="modal-header"><h3>Detail PBF</h3><button class="modal-close" onclick="closeModal('modalDetailPBF')">&times;</button></div>
        <div id="detailPBFContent"></div>
    </div>
</div>

<script>
function val(v){ return v || '-'; }
function editPBF(data){
    document.getElementById('editIdPbf').value = data.id_pbf;
    document.getElementById('editNamaPbf').value = data.nama_pbf || '';
    document.getElementById('editAlamat').value = data.alamat || '';
    document.getElementById('editTelepon').value = data.no_telepon || '';
    document.getElementById('editKontak').value = data.kontak_person || '';
    document.getElementById('editKeterangan').value = data.keterangan || '';
    openModal('modalEditPBF');
}
function detailPBF(data){
    document.getElementById('detailPBFContent').innerHTML = `
        <div style="display:grid;gap:12px;">
            <div><strong>Nama PBF</strong><br>${val(data.nama_pbf)}</div>
            <div><strong>Alamat</strong><br>${val(data.alamat)}</div>
            <div><strong>No. Telepon</strong><br>${val(data.no_telepon)}</div>
            <div><strong>Kontak Person</strong><br>${val(data.kontak_person)}</div>
            <div><strong>Keterangan</strong><br>${val(data.keterangan)}</div>
        </div>`;
    openModal('modalDetailPBF');
}
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
</script>
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

/* Card & Table ikut glass */
.card{
    background:rgba(255,255,255,0.65) !important;
    backdrop-filter:blur(18px);
    border-radius:22px !important;
    border:1px solid rgba(255,255,255,0.8) !important;
}

table thead th{
    background:rgba(219,234,254,0.85) !important;
    border:none !important;
}

table tbody td{
    background:rgba(255,255,255,0.65) !important;
}

/* Modal glass */
.modal-content, .modal{
    background:rgba(255,255,255,0.75) !important;
    backdrop-filter:blur(30px);
    border-radius:26px !important;
}

/* Header hitam */
.page-header h1,
.page-header p{
    color:#000 !important;
}
</style>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
