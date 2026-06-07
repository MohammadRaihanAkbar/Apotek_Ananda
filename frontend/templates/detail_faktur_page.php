<?php
$roleFolder = isSuperAdmin() ? 'superadmin' : 'admin';
$backUrl = BASE_URL . '/frontend/' . $roleFolder . '/manajemen_stok.php';
$editUrl = BASE_URL . '/frontend/' . $roleFolder . '/tambah_faktur.php?id=' . (int)$faktur['id_faktur'];
?>


<style>
.detail-obat-table { min-width: 940px; table-layout: fixed; }
.detail-obat-table th,
.detail-obat-table td { vertical-align: middle; }
.detail-obat-table td:nth-child(2) { word-break: break-word; }
</style>

<div class="page-header">
    <h1>Detail Faktur</h1>
    <p>Informasi header faktur, daftar obat, dan batch/expired per qty.</p>
</div>

<div style="display:flex;gap:10px;margin-bottom:16px;">
    <a href="<?= $backUrl ?>" class="btn btn-secondary btn-sm"><span class="material-icons-round">arrow_back</span>Kembali</a>
    <a href="<?= $editUrl ?>" class="btn btn-warning btn-sm"><span class="material-icons-round">edit</span>Edit Faktur</a>
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
        <table class="detail-obat-table">
            <colgroup>
                <col style="width:6%">
                <col style="width:18%">
                <col style="width:14%">
                <col style="width:10%">
                <col style="width:12%">
                <col style="width:10%">
                <col style="width:6%">
                <col style="width:12%">
                <col style="width:12%">
            </colgroup>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Obat</th>
                    <th>Merk Dagang</th>
                    <th>Satuan</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Diskon (%)</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Subtotal</th>
                    <th>Batch & Expired</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($faktur['details'] ?? []) as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($item['nama_obat']) ?></strong></td>
                        <td><?= htmlspecialchars($item['merk_dagang'] ?? '-') ?: '-' ?></td>
                        <td><?= htmlspecialchars($item['satuan']) ?></td>
                        <td class="text-right">Rp <?= number_format($item['harga_beli'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($item['discount'], 2, ',', '.') ?>%</td>
                        <td class="text-right"><?= (int)$item['qty'] ?></td>
                        <td class="text-right"><strong>Rp <?= number_format($item['total'], 0, ',', '.') ?></strong></td>
                        <td>
                            <?php if (empty($item['batches'])): ?>
                                <span style="color:#94a3b8">-</span>
                            <?php else: ?>
                                <?php
                                    $namaObatJson = htmlspecialchars(
                                        json_encode($item['nama_obat'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    $batchJson = htmlspecialchars(
                                        json_encode($item['batches'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                ?>
                                <button class="btn btn-outline btn-sm" onclick='showBatchDetail(<?= $namaObatJson ?>, <?= $batchJson ?>)'>
                                    <span class="material-icons-round">list_alt</span>Detail (<?= count($item['batches']) ?>)
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
function appendCell(row, value, useCode = false) {
    const td = document.createElement('td');

    if (useCode) {
        const code = document.createElement('code');
        code.textContent = value || '-';
        td.appendChild(code);
    } else {
        td.textContent = value || '-';
    }

    row.appendChild(td);
}

function showBatchDetail(namaObat, batches) {
    document.getElementById('batchModalTitle').textContent = 'Batch & Expired — ' + (namaObat || '-');
    const tbody = document.getElementById('batchModalBody');
    tbody.replaceChildren();

    if (!Array.isArray(batches)) {
        batches = [];
    }

    batches.forEach(function(b, idx) {
        const tr = document.createElement('tr');
        appendCell(tr, String(idx + 1));
        appendCell(tr, b && b.no_batch ? String(b.no_batch) : '-', true);
        appendCell(tr, b && b.expired_date ? String(b.expired_date) : '-');
        tbody.appendChild(tr);
    });
    openModal('modalBatchDetail');
}
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
</script>
