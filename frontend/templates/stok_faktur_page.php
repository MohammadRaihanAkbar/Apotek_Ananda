<?php
$canDeleteFaktur = $canDeleteFaktur ?? false;
$roleFolder = isSuperAdmin() ? 'superadmin' : 'admin';
$tambahUrl = BASE_URL . '/frontend/' . $roleFolder . '/tambah_faktur.php';
?>

<div class="page-header">
    <h1>Manajemen Stok</h1>
    <p>Daftar stok dikelompokkan per faktur. Tambah dan edit faktur dilakukan di screen terpisah.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="tab-filters" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;align-items:center;">
    <a href="?<?= $search ? 'search='.urlencode($search) : '' ?>" class="tab-filter <?= !$filterPbf ? 'active' : '' ?>">Semua PBF</a>
    <?php foreach ($pbfList as $pbf): ?>
        <a href="?pbf=<?= $pbf['id_pbf'] ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="tab-filter <?= $filterPbf == $pbf['id_pbf'] ? 'active' : '' ?>"><?= htmlspecialchars($pbf['nama_pbf']) ?></a>
    <?php endforeach; ?>
</div>

<div class="filter-bar">
    <form method="GET" style="flex:1;display:flex;gap:10px;flex-wrap:wrap;">
        <?php if ($filterPbf): ?><input type="hidden" name="pbf" value="<?= $filterPbf ?>"><?php endif; ?>
        <div style="flex:1; display:flex; min-width:240px;">
            <input type="text" name="search" class="form-control autocomplete-input" data-type="stok_obat" placeholder="🔍 Cari no. faktur, PBF, atau nama obat..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <?php if ($search): ?><a href="?<?= $filterPbf ? 'pbf='.$filterPbf : '' ?>" class="btn btn-secondary btn-sm">Reset</a><?php endif; ?>
    </form>
    <a class="btn btn-primary" href="<?= $tambahUrl ?>">+ Tambah Faktur</a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Faktur</th>
                    <th>PBF</th>
                    <th>Tgl Faktur</th>
                    <th>Tgl Masuk Barang</th>
                    <th>Jumlah Obat</th>
                    <th>Total Qty</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stokList)): ?>
                    <tr><td colspan="10" class="text-center" style="color:#94a3b8;padding:30px;">Belum ada faktur stok</td></tr>
                <?php else: ?>
                    <?php foreach ($stokList as $i => $stok): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($stok['no_faktur']) ?></strong>
                            <?php if (!empty($stok['daftar_obat'])): ?>
                                <div style="font-size:11px;color:#94a3b8;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($stok['daftar_obat']) ?>"><?= htmlspecialchars($stok['daftar_obat']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($stok['nama_pbf']) ?></td>
                        <td><?= htmlspecialchars($stok['tanggal_faktur']) ?></td>
                        <td><?= htmlspecialchars($stok['tanggal_masuk']) ?></td>
                        <td class="text-right"><?= (int)$stok['jumlah_item'] ?></td>
                        <td class="text-right"><?= (int)$stok['total_qty'] ?></td>
                        <td class="text-right"><strong>Rp <?= number_format($stok['total_faktur'], 0, ',', '.') ?></strong></td>
                        <td>
                            <?php if (($stok['status_pembayaran'] ?? '') === 'lunas'): ?>
                                <span class="badge badge-success">Lunas</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Belum Lunas</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/frontend/<?= $roleFolder ?>/detail_faktur.php?id=<?= $stok['id_faktur'] ?>">Detail</a>
                                <a class="btn btn-warning btn-sm" href="<?= $tambahUrl ?>?id=<?= $stok['id_faktur'] ?>">✏️ Edit</a>
                                <?php if ($canDeleteFaktur): ?>
                                <form method="POST" action="<?= BASE_URL ?>/backend/controllers/stok_masuk_controller.php?action=delete_faktur" onsubmit="return confirm('Hapus faktur ini beserta semua item obat dan batch-nya?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id_faktur" value="<?= $stok['id_faktur'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
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
