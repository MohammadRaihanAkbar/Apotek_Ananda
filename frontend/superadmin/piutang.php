<?php
/**
 * Piutang - Otomatis dari faktur.
 * Tampilan 2 tabel: Belum Lunas & Lunas.
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

$pageTitle = 'Manajemen Piutang';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../backend/models/piutang.php';

$model = new Piutang();
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;

$listBelumLunas = $model->getAllByStatus('belum_lunas', $search);
$listLunas      = $model->getAllByStatus('lunas', $search);
$summary        = $model->getSummary();
$flash          = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>
<div class="dashboard-wrapper">
  <div class="glass-container">
    <div class="glass-inner">
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
    <form method="GET" style="display:flex;gap:8px;flex:1;">
        <div style="flex:1; display:flex; min-width:220px;">
            <input type="text" name="search" class="form-control autocomplete-input" data-type="piutang" placeholder="🔍 Cari no faktur / PBF / obat..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <a href="?" class="btn btn-secondary btn-sm">Reset</a>
    </form>
    <div style="display:flex;gap:8px;">
        <a href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_excel&search=<?= urlencode($search ?? '') ?>" class="btn btn-success btn-sm">📊 Excel</a>
        <a href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_pdf&search=<?= urlencode($search ?? '') ?>" class="btn btn-danger btn-sm" target="_blank">📄 PDF</a>
    </div>
</div>

<div class="card" style="background:#f8fafc;border-style:dashed;">
    <strong>Catatan:</strong> Tambah/edit data faktur dilakukan dari menu <strong>Manajemen Stok</strong>. Halaman ini hanya mengambil data faktur dan mengelola status pembayaran.
</div>

<!-- Tabel Belum Lunas -->
<div class="card" style="border-left:4px solid var(--danger);">
    <div class="card-title" style="color:var(--danger);">
        <span class="material-icons-round">warning</span>
        Belum Lunas (<?= count($listBelumLunas) ?>)
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>No</th><th>No. Faktur</th><th>PBF</th><th>Tgl Faktur</th><th>Jatuh Tempo</th><th>Item</th><th>Total Faktur</th><th>Bukti</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($listBelumLunas)): ?>
                    <tr><td colspan="9" class="text-center" style="color:#94a3b8;padding:30px;">Tidak ada piutang belum lunas 🎉</td></tr>
                <?php else: ?>
                    <?php foreach ($listBelumLunas as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($p['no_faktur']) ?></strong></td>
                        <td><?= htmlspecialchars($p['nama_pbf']) ?></td>
                        <td><?= htmlspecialchars($p['tanggal_faktur']) ?></td>
                        <td><?= htmlspecialchars($p['tanggal_jatuh_tempo'] ?? '-') ?></td>
                        <td class="text-right"><?= (int)$p['jumlah_item'] ?></td>
                        <td class="text-right">Rp <?= number_format($p['jumlah_harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($p['bukti_pembayaran']): ?>
                                <a href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= $p['id_faktur'] ?>" class="btn btn-sm btn-secondary">📎 Lihat</a>
                            <?php else: ?>
                                <span style="color:#94a3b8">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="lunasi(<?= $p['id_faktur'] ?>, '<?= htmlspecialchars($p['no_faktur']) ?>')">✅ Lunas</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tabel Lunas -->
<div class="card" style="border-left:4px solid var(--success);">
    <div class="card-title" style="color:var(--success);">
        <span class="material-icons-round">check_circle</span>
        Lunas (<?= count($listLunas) ?>)
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>No</th><th>No. Faktur</th><th>PBF</th><th>Tgl Faktur</th><th>Total Faktur</th><th>Tgl Lunas</th><th>Bukti</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($listLunas)): ?>
                    <tr><td colspan="8" class="text-center" style="color:#94a3b8;padding:30px;">Tidak ada data lunas</td></tr>
                <?php else: ?>
                    <?php foreach ($listLunas as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($p['no_faktur']) ?></strong></td>
                        <td><?= htmlspecialchars($p['nama_pbf']) ?></td>
                        <td><?= htmlspecialchars($p['tanggal_faktur']) ?></td>
                        <td class="text-right">Rp <?= number_format($p['jumlah_harga'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($p['tanggal_lunas'] ?? '-') ?></td>
                        <td>
                            <?php if ($p['bukti_pembayaran']): ?>
                                <a href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= $p['id_faktur'] ?>" class="btn btn-sm btn-secondary">📎 Lihat</a>
                            <?php else: ?>
                                <span style="color:#94a3b8">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=belum_lunas" onsubmit="return confirm('Ubah faktur ini menjadi belum lunas?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="id_faktur" value="<?= $p['id_faktur'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">↩ Belum</button>
                            </form>
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
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
*{font-family:'Poppins',sans-serif}

/* Background biru putih halus */
body{
    min-height:100vh;
    background: linear-gradient(135deg,#e0f2fe 0%,#ffffff 45%,#dbeafe 100%);
    color:#0f172a;
}

/* Wrapper glass */
.dashboard-wrapper{ padding:40px; }

.glass-container{
    background: rgba(255,255,255,0.60);
    backdrop-filter: blur(26px);
    -webkit-backdrop-filter: blur(26px);
    border:1px solid rgba(255,255,255,0.9);
    border-radius:28px;
    padding:28px;
    box-shadow:0 15px 45px rgba(15,23,42,0.12);
}

.glass-inner{
    background: rgba(255,255,255,0.55);
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
    border:1px solid rgba(255,255,255,0.85);
    border-radius:24px;
    padding:24px;
    box-shadow:0 10px 35px rgba(15,23,42,0.10);
}

/* Semua card jadi glass */
.card, .stat-card{
    background: rgba(255,255,255,0.65) !important;
    backdrop-filter: blur(22px);
    border:1px solid rgba(255,255,255,0.8);
    border-radius:18px;
    box-shadow:0 15px 45px rgba(37,99,235,.18);
}

/* Header gradasi biru */
.page-header h1{
    background: linear-gradient(90deg,#0ea5e9,#2563eb);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

/* Table feel */
.table-wrapper thead{
    background: linear-gradient(90deg,#dbeafe,#eff6ff);
}
.table-wrapper tbody tr:hover{
    background:rgba(59,130,246,.08);
    transition:.2s;
}

/* Form */
.form-control{
    border-radius:10px;
    border:1px solid #cbd5e1;
}
.form-control:focus{
    border-color:#3b82f6;
    box-shadow:0 0 0 3px rgba(59,130,246,.2);
}

/* Button gradient */
.btn-primary{
    background: linear-gradient(90deg,#0ea5e9,#2563eb);
    border:none;
}
.btn-secondary{
    background:#e2e8f0;
    border:none;
}
.page-header h1,
.page-header p{
    color:#000 !important;
    -webkit-text-fill-color:#000 !important;
    background:none !important;
}
</style>
 </div>
  </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
