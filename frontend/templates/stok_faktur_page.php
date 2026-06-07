<?php
$canDeleteFaktur = $canDeleteFaktur ?? false;
$roleFolder = isSuperAdmin() ? 'superadmin' : 'admin';
$tambahUrl = BASE_URL . '/frontend/' . $roleFolder . '/tambah_faktur.php';

$filterPbf = $filterPbf ?? null;
$search = $search ?? null;
$tanggalFakturDari = $tanggalFakturDari ?? null;
$tanggalFakturSampai = $tanggalFakturSampai ?? null;
$tanggalMasukDari = $tanggalMasukDari ?? null;
$tanggalMasukSampai = $tanggalMasukSampai ?? null;
$hargaMin = $hargaMin ?? null;
$hargaMax = $hargaMax ?? null;

$hasFilter = $filterPbf || $search || $tanggalFakturDari || $tanggalFakturSampai || $tanggalMasukDari || $tanggalMasukSampai || $hargaMin || $hargaMax;
?>

<div class="page-header">
    <h1>Manajemen Stok</h1>
    <p>Daftar stok dikelompokkan per faktur. Tambah dan edit faktur dilakukan di screen terpisah.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<style>
    .stok-filter-box {
        display: block;
        padding: 16px;
    }

    .stok-filter-form {
        width: 100%;
    }

    .stok-filter-top {
        display: grid;
        grid-template-columns: 170px 145px 145px 145px 145px 120px 120px;
        gap: 10px;
        align-items: end;
        margin-bottom: 12px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .stok-filter-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .stok-search-action {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
    }

    .stok-search-input {
        width: 100%;
        max-width: 760px;
        min-width: 420px;
    }

    .stok-filter-button-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
    }

    .stok-add-button {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    @media (max-width: 1200px) {
        .stok-filter-top {
            grid-template-columns: repeat(3, minmax(145px, 1fr));
        }

        .stok-search-input {
            min-width: 320px;
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .stok-filter-top {
            grid-template-columns: 1fr;
        }

        .stok-filter-bottom {
            flex-direction: column;
            align-items: stretch;
        }

        .stok-search-action {
            flex-direction: column;
            align-items: stretch;
        }

        .stok-filter-button-group {
            width: 100%;
        }

        .stok-filter-button-group .btn {
            flex: 1;
            justify-content: center;
        }

        .stok-search-input {
            min-width: 0;
            max-width: 100%;
        }

        .stok-add-button {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="filter-bar stok-filter-box">
    <form method="GET" class="stok-filter-form">

        <div class="stok-filter-top">
            <div class="form-group" style="margin-bottom:0;">
                <label>PBF</label>
                <select name="pbf" class="form-control">
                    <option value="">Semua PBF</option>
                    <?php foreach ($pbfList as $pbf): ?>
                        <option 
                            value="<?= (int)$pbf['id_pbf'] ?>" 
                            <?= (int)($filterPbf ?? 0) === (int)$pbf['id_pbf'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($pbf['nama_pbf']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Tgl Faktur Dari</label>
                <input 
                    type="date" 
                    name="tanggal_faktur_dari" 
                    class="form-control" 
                    value="<?= htmlspecialchars($tanggalFakturDari ?? '') ?>"
                >
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Tgl Faktur Sampai</label>
                <input 
                    type="date" 
                    name="tanggal_faktur_sampai" 
                    class="form-control" 
                    value="<?= htmlspecialchars($tanggalFakturSampai ?? '') ?>"
                >
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Tgl Masuk Dari</label>
                <input 
                    type="date" 
                    name="tanggal_masuk_dari" 
                    class="form-control" 
                    value="<?= htmlspecialchars($tanggalMasukDari ?? '') ?>"
                >
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Tgl Masuk Sampai</label>
                <input 
                    type="date" 
                    name="tanggal_masuk_sampai" 
                    class="form-control" 
                    value="<?= htmlspecialchars($tanggalMasukSampai ?? '') ?>"
                >
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Harga Min</label>
                <input 
                    type="number" 
                    name="harga_min" 
                    class="form-control" 
                    min="0" 
                    step="100" 
                    placeholder="Min" 
                    value="<?= htmlspecialchars($hargaMin ?? '') ?>"
                >
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Harga Max</label>
                <input 
                    type="number" 
                    name="harga_max" 
                    class="form-control" 
                    min="0" 
                    step="100" 
                    placeholder="Max" 
                    value="<?= htmlspecialchars($hargaMax ?? '') ?>"
                >
            </div>
        </div>

        <div class="stok-filter-bottom">
            <div class="stok-search-action">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control autocomplete-input stok-search-input" 
                    data-type="stok_obat" 
                    placeholder="Cari no. faktur, PBF, atau nama obat..." 
                    value="<?= htmlspecialchars($search ?? '') ?>" 
                    autocomplete="off"
                >

                <div class="stok-filter-button-group">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="material-icons-round">search</span>
                        Cari
                    </button>

                    <a href="?" class="btn btn-secondary btn-sm">
                        <span class="material-icons-round">refresh</span>
                        Reset
                    </a>
                </div>
            </div>

            <a class="btn btn-primary stok-add-button" href="<?= $tambahUrl ?>">
                <span class="material-icons-round">add</span>
                Tambah Faktur
            </a>
        </div>

    </form>
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
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($stokList)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="color:#94a3b8;padding:30px;">
                            Belum ada faktur stok<?= $hasFilter ? ' sesuai filter' : '' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($stokList as $i => $stok): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>

                            <td>
                                <strong><?= htmlspecialchars($stok['no_faktur']) ?></strong>
                            </td>

                            <td><?= htmlspecialchars($stok['nama_pbf']) ?></td>

                            <td><?= htmlspecialchars($stok['tanggal_faktur']) ?></td>

                            <td><?= htmlspecialchars($stok['tanggal_masuk']) ?></td>

                            <td class="text-right"><?= (int)$stok['jumlah_item'] ?></td>

                            <td class="text-right"><?= (int)$stok['total_qty'] ?></td>

                            <td class="text-right">
                                <strong>Rp <?= number_format($stok['total_faktur'], 0, ',', '.') ?></strong>
                            </td>

                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <a 
                                        class="btn btn-secondary btn-sm" 
                                        href="<?= BASE_URL ?>/frontend/<?= $roleFolder ?>/detail_faktur.php?id=<?= (int)$stok['id_faktur'] ?>"
                                    >
                                        <span class="material-icons-round">visibility</span>
                                        Detail
                                    </a>

                                    <a 
                                        class="btn btn-warning btn-sm" 
                                        href="<?= $tambahUrl ?>?id=<?= (int)$stok['id_faktur'] ?>"
                                    >
                                        <span class="material-icons-round">edit</span>
                                        Edit
                                    </a>

                                    <?php if ($canDeleteFaktur): ?>
                                        <form 
                                            method="POST" 
                                            action="<?= BASE_URL ?>/backend/controllers/stok_masuk_controller.php?action=delete_faktur" 
                                            onsubmit="return confirm('Hapus faktur ini beserta semua item obat dan batch-nya?')"
                                        >
                                            <?= csrfField() ?>

                                            <input 
                                                type="hidden" 
                                                name="id_faktur" 
                                                value="<?= (int)$stok['id_faktur'] ?>"
                                            >

                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <span class="material-icons-round">delete</span>
                                                Hapus
                                            </button>
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