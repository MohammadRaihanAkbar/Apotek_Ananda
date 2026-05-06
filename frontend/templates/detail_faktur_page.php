<?php
$roleFolder = isSuperAdmin() ? 'superadmin' : 'admin';
$backUrl = BASE_URL . '/frontend/' . $roleFolder . '/manajemen_stok.php';
$editUrl = BASE_URL . '/frontend/' . $roleFolder . '/tambah_faktur.php?id=' . (int)$faktur['id_faktur'];
?>

<div class="page-header">
    <h1>Detail Faktur</h1>
    <p>Informasi header faktur, daftar obat, dan batch/expired per qty.</p>
</div>

<div style="display:flex;gap:10px;margin-bottom:16px;">
    <a href="<?= $backUrl ?>" class="btn btn-secondary btn-sm">← Kembali</a>
    <a href="<?= $editUrl ?>" class="btn btn-warning btn-sm">✏️ Edit Faktur</a>
</div>

<div class="card">
    <h3 style="margin-bottom:16px;">Header Faktur</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
        <div><strong>No Faktur</strong><br><?= htmlspecialchars($faktur['no_faktur']) ?></div>
        <div><strong>PBF</strong><br><?= htmlspecialchars($faktur['nama_pbf']) ?></div>
        <div><strong>Tanggal Faktur</strong><br><?= htmlspecialchars($faktur['tanggal_faktur']) ?></div>
        <div><strong>Tanggal Masuk</strong><br><?= htmlspecialchars($faktur['tanggal_masuk']) ?></div>
        <div><strong>Jumlah Obat</strong><br><?= (int)$faktur['jumlah_item'] ?></div>
        <div><strong>Total Qty</strong><br><?= (int)$faktur['total_qty'] ?></div>
        <div><strong>Total Harga</strong><br>Rp <?= number_format($faktur['total_faktur'], 0, ',', '.') ?></div>
        <div><strong>Status</strong><br><?= ($faktur['status_pembayaran'] ?? '') === 'lunas' ? '<span class="badge badge-success">Lunas</span>' : '<span class="badge badge-danger">Belum Lunas</span>' ?></div>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom:16px;">Obat dalam Faktur</h3>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Obat</th>
                    <th>Satuan</th>
                    <th>Harga</th>
                    <th>Diskon (%)</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Batch & Expired</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($faktur['details'] ?? []) as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($item['nama_obat']) ?></strong></td>
                        <td><?= htmlspecialchars($item['satuan']) ?></td>
                        <td class="text-right">Rp <?= number_format($item['harga_beli'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($item['discount'], 2, ',', '.') ?>%</td>
                        <td class="text-right"><?= (int)$item['qty'] ?></td>
                        <td class="text-right"><strong>Rp <?= number_format($item['total'], 0, ',', '.') ?></strong></td>
                        <td>
                            <?php if (empty($item['batches'])): ?>
                                <span style="color:#94a3b8">-</span>
                            <?php else: ?>
                                <button class="btn btn-outline btn-sm" onclick="showBatchDetail('<?= htmlspecialchars($item['nama_obat'], ENT_QUOTES) ?>', <?= htmlspecialchars(json_encode($item['batches']), ENT_QUOTES) ?>)">
                                    📋 Detail (<?= count($item['batches']) ?>)
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Batch & Expired Detail -->
<div class="modal-overlay" id="modalBatchDetail">
    <div class="modal">
        <div class="modal-header">
            <h3 id="batchModalTitle">Batch & Expired</h3>
            <button class="modal-close" onclick="closeModal('modalBatchDetail')">&times;</button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Batch</th>
                        <th>Expired Date</th>
                    </tr>
                </thead>
                <tbody id="batchModalBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showBatchDetail(namaObat, batches) {
    document.getElementById('batchModalTitle').textContent = 'Batch & Expired — ' + namaObat;
    const tbody = document.getElementById('batchModalBody');
    tbody.innerHTML = '';
    batches.forEach(function(b, idx) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td>' + (idx + 1) + '</td><td><code>' + (b.no_batch || '-') + '</code></td><td>' + (b.expired_date || '-') + '</td>';
        tbody.appendChild(tr);
    });
    openModal('modalBatchDetail');
}
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
</script>
