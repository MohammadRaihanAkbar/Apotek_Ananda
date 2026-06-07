<?php
$roleFolder = isSuperAdmin() ? 'superadmin' : 'admin';
$isEdit = !empty($faktur);
$actionUrl = BASE_URL . '/backend/controllers/stok_masuk_controller.php?action=' . ($isEdit ? 'update_faktur' : 'create_faktur');
$backUrl = BASE_URL . '/frontend/' . $roleFolder . '/manajemen_stok.php';
$initialItems = [];
if ($isEdit) {
    foreach (($faktur['details'] ?? []) as $d) {
        $initialItems[] = [
            'nama_obat' => $d['nama_obat'] ?? '',
            'merk_dagang' => $d['merk_dagang'] ?? '',
            'satuan' => $d['satuan'] ?? '',
            'harga_beli' => $d['harga_beli'] ?? 0,
            'discount' => $d['discount'] ?? 0,
            'qty' => $d['qty'] ?? $d['jumlah_masuk'] ?? 0,
            'batches' => array_map(function($b) { return ['no_batch' => $b['no_batch'], 'expired_date' => $b['expired_date']]; }, $d['batches'] ?? []),
        ];
    }
}
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Edit Faktur' : 'Tambah Faktur' ?></h1>
    <p>Isi header faktur sekali, lalu masukkan banyak obat. Batch dan expired diinput lewat popup sesuai qty setiap obat.</p>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div style="margin-bottom:16px;">
    <a href="<?= $backUrl ?>" class="btn btn-secondary btn-sm"><span class="material-icons-round">arrow_back</span>Kembali ke Manajemen Stok</a>
</div>

<form id="fakturForm" method="POST" action="<?= $actionUrl ?>">
    <?= csrfField() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id_faktur" value="<?= (int)$faktur['id_faktur'] ?>"><?php endif; ?>

    <div class="card">
        <h3 style="margin-bottom:18px;">Data Faktur</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;">
            <div class="form-group">
                <label>PBF *</label>
                <select name="id_pbf" class="form-control" required <?= empty($pbfList) ? 'disabled' : '' ?>>
                    <option value="">-- Pilih PBF --</option>
                    <?php foreach ($pbfList as $pbf): ?>
                        <option value="<?= $pbf['id_pbf'] ?>" <?= $isEdit && (int)$faktur['id_pbf'] === (int)$pbf['id_pbf'] ? 'selected' : '' ?>><?= htmlspecialchars($pbf['nama_pbf']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($pbfList)): ?><small style="color:var(--danger);">Belum ada PBF. Minta Super Admin menambahkan PBF lebih dulu.</small><?php endif; ?>
            </div>
            <div class="form-group">
                <label>No. Faktur *</label>
                <input type="text" name="no_faktur" class="form-control" required value="<?= htmlspecialchars($faktur['no_faktur'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Tanggal Faktur *</label>
                <input type="date" name="tanggal_faktur" class="form-control" required value="<?= htmlspecialchars($faktur['tanggal_faktur'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="form-group">
                <label>Tanggal Masuk Barang *</label>
                <input type="date" name="tanggal_masuk" class="form-control" required value="<?= htmlspecialchars($faktur['tanggal_masuk'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="form-group">
                <label>Tanggal Jatuh Tempo *</label>
                <input type="date" name="tanggal_jatuh_tempo" class="form-control" required value="<?= htmlspecialchars($faktur['tanggal_jatuh_tempo'] ?? date('Y-m-d')) ?>">
                <small style="color:#64748b;">Tanggal ini hanya ditampilkan di menu Piutang.</small>
            </div>
        </div>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:12px;">
            <div>
                <h3>Daftar Obat</h3>
                <p style="margin:4px 0 0;color:#64748b;font-size:13px;">Klik <strong>Isi Batch</strong> setelah mengisi qty. Jumlah form batch otomatis mengikuti qty.</p>
            </div>
            <button type="button" class="btn btn-success btn-sm" onclick="addItemRow()"><span class="material-icons-round">add</span>Tambah Obat</button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama Obat *</th>
                        <th>Merk Dagang</th>
                        <th>Satuan *</th>
                        <th>Harga Beli *</th>
                        <th>Diskon (%)</th>
                        <th>Qty</th>
                        <th>Batch & Exp</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemRows"></tbody>
            </table>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:16px;font-weight:700;font-size:16px;">Total Faktur: <span id="grandTotal" style="margin-left:8px;">Rp 0</span></div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-bottom:30px;">
        <a href="<?= $backUrl ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" <?= empty($pbfList) ? 'disabled' : '' ?>><span class="material-icons-round">save</span><?= $isEdit ? 'Update Faktur' : 'Simpan Faktur' ?></button>
    </div>
</form>

<div class="modal-overlay" id="modalBatch">
    <div class="modal" style="max-width:720px;max-height:88vh;overflow:auto;">
        <div class="modal-header">
            <h3 id="batchTitle">Input Batch</h3>
            <button class="modal-close" onclick="closeModal('modalBatch')">&times;</button>
        </div>
        <div id="batchFields"></div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalBatch')">Batal</button>
            <button type="button" class="btn btn-primary" onclick="saveBatchModal()">Simpan Batch</button>
        </div>
    </div>
</div>

<datalist id="obatOptions">
    <?php foreach ($namaObatList as $namaObat): ?>
        <option value="<?= htmlspecialchars($namaObat) ?>"></option>
    <?php endforeach; ?>
</datalist>

<script>
const satuanOptions = <?= json_encode($validSatuan) ?>;
const initialItems = <?= json_encode($initialItems, JSON_UNESCAPED_UNICODE) ?>;
let activeRow = null;

function escapeHtml(v){return String(v ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
function rupiah(v){return 'Rp ' + (Number(v)||0).toLocaleString('id-ID');}
function satuanSelect(value){return `<option value="">--</option>` + satuanOptions.map(s => `<option value="${escapeHtml(s)}" ${s===value?'selected':''}>${escapeHtml(s)}</option>`).join('');}
function normalizeBatches(batches, qty){
    const arr = Array.isArray(batches) ? batches.slice(0, qty) : [];
    while (arr.length < qty) arr.push({no_batch:'', expired_date:''});
    return arr;
}
function getRowBatches(tr){
    try { return JSON.parse(tr.querySelector('input[name="batch_data[]"]').value || '[]') || []; }
    catch(e) { return []; }
}
function setRowBatches(tr, batches){
    const qty = parseInt(tr.querySelector('input[name="qty[]"]').value) || 0;
    const normalized = normalizeBatches(batches, qty);
    tr.querySelector('input[name="batch_data[]"]').value = JSON.stringify(normalized);
    const filled = normalized.filter(b => b.no_batch && b.expired_date).length;
    tr.querySelector('.batch-count').textContent = `${filled}/${qty}`;
}
function itemRow(item = {}){
    const batches = JSON.stringify(normalizeBatches(item.batches || [], parseInt(item.qty)||0)).replace(/'/g, '&#39;');
    return `<tr>
        <td><input list="obatOptions" type="text" name="nama_obat[]" class="form-control" required value="${escapeHtml(item.nama_obat || '')}" placeholder="Nama generik/obat"></td>
        <td><input type="text" name="merk_dagang[]" class="form-control" value="${escapeHtml(item.merk_dagang || '')}" placeholder="Opsional"></td>
        <td><select name="satuan[]" class="form-control" required>${satuanSelect(item.satuan || '')}</select></td>
        <td><input type="number" name="harga_beli[]" class="form-control" min="0" step="1" required value="${escapeHtml(item.harga_beli || '')}" oninput="calcRow(this)"></td>
        <td><input type="number" name="discount[]" class="form-control" min="0" max="100" step="0.01" value="${escapeHtml(item.discount ?? 0)}" oninput="calcRow(this)" placeholder="0-100"></td>
        <td><input type="number" name="qty[]" class="form-control" min="1" step="1" required value="${escapeHtml(item.qty || '')}" oninput="qtyChanged(this)"></td>
        <td>
            <input type="hidden" name="batch_data[]" value='${batches}'>
            <button type="button" class="btn btn-secondary btn-sm" onclick="openBatchForRow(this)">Isi Batch & Exp <span class="batch-count">0/0</span></button>
        </td>
        <td><input type="text" class="form-control row-total" readonly value="Rp 0" style="background:#f1f5f9;font-weight:600;min-width:110px;"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeItemRow(this)">×</button></td>
    </tr>`;
}
function addItemRow(item = {}){
    document.getElementById('itemRows').insertAdjacentHTML('beforeend', itemRow(item));
    const tr = document.getElementById('itemRows').lastElementChild;
    setRowBatches(tr, item.batches || []);
    calcRow(tr.querySelector('input[name="harga_beli[]"]'));
}
function removeItemRow(btn){
    const tbody = document.getElementById('itemRows');
    if (tbody.children.length <= 1) return alert('Minimal harus ada satu obat dalam faktur.');
    btn.closest('tr').remove();
    updateGrandTotal();
}
function qtyChanged(input){
    const tr = input.closest('tr');
    // Qty hanya menyesuaikan jumlah data batch yang harus diisi.
    // Popup batch tidak muncul otomatis; user klik tombol "Isi Batch & Exp" secara manual.
    setRowBatches(tr, getRowBatches(tr));
    calcRow(input);
}
function calcRow(el){
    const tr = el.closest('tr');
    const harga = parseFloat(tr.querySelector('input[name="harga_beli[]"]').value) || 0;
    const disc = parseFloat(tr.querySelector('input[name="discount[]"]').value) || 0;
    const qty = parseInt(tr.querySelector('input[name="qty[]"]').value) || 0;
    const total = (harga * qty) * Math.max(1 - (disc / 100), 0);
    tr.querySelector('.row-total').value = rupiah(total);
    updateGrandTotal();
}
function updateGrandTotal(){
    let total = 0;
    document.querySelectorAll('#itemRows tr').forEach(tr => {
        const harga = parseFloat(tr.querySelector('input[name="harga_beli[]"]').value) || 0;
        const disc = parseFloat(tr.querySelector('input[name="discount[]"]').value) || 0;
        const qty = parseInt(tr.querySelector('input[name="qty[]"]').value) || 0;
        total += (harga * qty) * Math.max(1 - (disc / 100), 0);
    });
    document.getElementById('grandTotal').textContent = rupiah(total);
}
function openBatchForRow(btn){
    const tr = btn.closest('tr');
    const qty = parseInt(tr.querySelector('input[name="qty[]"]').value) || 0;
    const nama = tr.querySelector('input[name="nama_obat[]"]').value || 'Obat';
    if (qty <= 0) return alert('Isi qty terlebih dahulu.');
    activeRow = tr;
    const batches = normalizeBatches(getRowBatches(tr), qty);
    document.getElementById('batchTitle').textContent = `${nama} - ${qty} data batch`;
    document.getElementById('batchFields').innerHTML = batches.map((b, idx) => `
        <div class="card" style="margin-bottom:10px;padding:14px;background:#f8fafc;">
            <strong>Item ${idx + 1} dari ${qty}</strong>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px;">
                <div class="form-group" style="margin-bottom:0;"><label>No Batch *</label><input type="text" class="form-control batch-no" value="${escapeHtml(b.no_batch || '')}" required></div>
                <div class="form-group" style="margin-bottom:0;"><label>Exp Date *</label><input type="date" class="form-control batch-exp" value="${escapeHtml(b.expired_date || '')}" required></div>
            </div>
        </div>`).join('');
    openModal('modalBatch');
}
function saveBatchModal(){
    if (!activeRow) return;
    const fields = Array.from(document.querySelectorAll('#batchFields .card'));
    const batches = [];
    for (const box of fields) {
        const no = box.querySelector('.batch-no').value.trim();
        const exp = box.querySelector('.batch-exp').value;
        if (!no || !exp) return alert('Semua No Batch dan Exp Date wajib diisi.');
        batches.push({no_batch:no, expired_date:exp});
    }
    setRowBatches(activeRow, batches);
    closeModal('modalBatch');
}
document.getElementById('fakturForm').addEventListener('submit', function(e){
    let ok = true;
    document.querySelectorAll('#itemRows tr').forEach((tr, i) => {
        const qty = parseInt(tr.querySelector('input[name="qty[]"]').value) || 0;
        const batches = getRowBatches(tr).filter(b => b.no_batch && b.expired_date);
        if (qty <= 0 || batches.length !== qty) {
            ok = false;
            alert(`Batch dan Exp Date baris ${i + 1} harus diisi sesuai qty.`);
        }
    });
    if (!ok) e.preventDefault();
});
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
if (initialItems.length) initialItems.forEach(item => addItemRow(item)); else addItemRow();
</script>
