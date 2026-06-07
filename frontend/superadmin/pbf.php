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

<!-- Background -->
<div class="bg-grid"></div>
<div class="bg-bubble one"></div>
<div class="bg-bubble two"></div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    min-height:100vh;
    overflow-x:hidden;
    color:#0f172a;

    background:
        linear-gradient(
            135deg,
            #f8fbff 0%,
            #eef5ff 40%,
            #ffffff 100%
        );

    position:relative;
}

/* BACKGROUND */
body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 60%),
        radial-gradient(circle, rgba(96,165,250,0.13) 0%, transparent 60%);

    background-size:700px 700px;
    animation:moveGlow 18s linear infinite;

    filter:blur(40px);
    z-index:-4;
}

.bg-grid{
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);

    background-size:40px 40px;

    mask-image:
        radial-gradient(circle at center, black 40%, transparent 85%);

    z-index:-3;
}

.bg-bubble{
    position:fixed;
    border-radius:50%;
    pointer-events:none;

    background:
        radial-gradient(
            circle at 30% 30%,
            rgba(255,255,255,0.9),
            rgba(255,255,255,0.08)
        );

    box-shadow:
        inset 0 0 25px rgba(255,255,255,0.9),
        0 0 35px rgba(59,130,246,0.10);

    animation:floating 12s ease-in-out infinite;
    z-index:-2;
}

.bg-bubble.one{
    width:220px;
    height:220px;
    top:5%;
    left:-80px;
}

.bg-bubble.two{
    width:300px;
    height:300px;
    bottom:-120px;
    right:-80px;
    animation-duration:18s;
}

@keyframes moveGlow{
    0%{ transform:translate(0,0) rotate(0deg); }
    50%{ transform:translate(40px,-30px) rotate(180deg); }
    100%{ transform:translate(0,0) rotate(360deg); }
}

@keyframes floating{
    0%,100%{
        transform:translateY(0px);
    }

    50%{
        transform:translateY(-20px);
    }
}

/* WRAPPER */
.dashboard-wrapper{
    padding:35px;
    position:relative;
    z-index:10;
}

/* GLASS */
.glass-container{
    background:rgba(255,255,255,0.42);

    border:1px solid rgba(255,255,255,0.8);

    backdrop-filter:blur(24px);
    -webkit-backdrop-filter:blur(24px);

    border-radius:30px;

    padding:30px;

    box-shadow:
        0 15px 45px rgba(15,23,42,0.08),
        inset 0 1px 0 rgba(255,255,255,0.8);
}

.glass-inner{
    background:rgba(255,255,255,0.30);

    border:1px solid rgba(255,255,255,0.7);

    border-radius:26px;

    padding:28px;
}

/* HEADER */
.page-header{
    margin-bottom:28px;
}

.page-header h1{
    font-size:34px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:8px;
}

.page-header p{
    color:#64748b;
    font-size:14px;
}

/* ALERT */
.alert{
    padding:14px 18px;
    border-radius:16px;
    margin-bottom:20px;
    border:none;
    background:rgba(255,255,255,0.7);
    backdrop-filter:blur(14px);
}

/* FILTER */
.filter-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:25px;
    flex-wrap:wrap;
}

.search-box{
    display:flex;
    align-items:center;
    gap:10px;

    background:rgba(255,255,255,0.55);

    border:1px solid rgba(255,255,255,0.9);

    border-radius:18px;

    padding:8px;
}

.form-control{
    border:none !important;
    background:transparent !important;
    padding:12px 14px !important;
    min-width:300px;
    outline:none !important;
    box-shadow:none !important;
}

/* BUTTON */
.btn{
    border:none !important;
    border-radius:14px !important;

    padding:11px 18px !important;

    font-size:13px !important;
    font-weight:600 !important;

    transition:0.25s ease;
}

.btn:hover{
    transform:translateY(-2px);
}

.btn-primary{
    color:#fff !important;

    background:linear-gradient(
        135deg,
        #3b82f6,
        #2563eb
    ) !important;

    box-shadow:
        0 10px 25px rgba(37,99,235,0.18);
}

.btn-secondary{
    background:#e2e8f0 !important;
    color:#334155 !important;
}

.btn-warning{
    background:#facc15 !important;
    color:#111827 !important;
}

.btn-danger{
    background:#ef4444 !important;
    color:#fff !important;
}

/* TABLE */
.table-wrapper{
    overflow:auto;
    border-radius:24px;
}

table{
    width:100%;
    border-collapse:collapse;
    overflow:hidden;
}

thead th{
    background:rgba(219,234,254,0.75);

    padding:18px 16px;

    text-align:left;

    font-size:13px;
    font-weight:700;
    color:#334155;
}

tbody td{
    padding:18px 16px;

    background:rgba(255,255,255,0.45);

    border-bottom:1px solid rgba(226,232,240,0.8);

    font-size:14px;
    color:#334155;
}

tbody tr{
    transition:0.25s ease;
}

tbody tr:hover{
    transform:scale(1.005);

    background:rgba(255,255,255,0.7);
}

.badge-count{
    width:34px;
    height:34px;

    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    background:#dbeafe;

    color:#2563eb;
    font-weight:700;
}

/* ACTION */
.action-group{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

/* MODAL */
.modal-overlay{
    position:fixed;
    inset:0;

    background:rgba(15,23,42,0.35);

    display:none;
    align-items:center;
    justify-content:center;

    padding:20px;

    z-index:999;
}

.modal-overlay.active{
    display:flex;
}

.modal{
    width:100%;
    max-width:520px;

    background:#ffffff;

    border-radius:24px;

    padding:24px;

    animation:modalShow .25s ease;
}

@keyframes modalShow{
    from{
        opacity:0;
        transform:translateY(20px) scale(.96);
    }

    to{
        opacity:1;
        transform:translateY(0) scale(1);
    }
}

.modal-header{
    display:flex;
    align-items:center;
    justify-content:space-between;

    margin-bottom:20px;
}

.modal-header h3{
    font-size:22px;
    font-weight:700;
}

.modal-close{
    border:none;
    background:#f1f5f9;
    width:38px;
    height:38px;
    border-radius:12px;
    cursor:pointer;
    font-size:22px;
}

.form-group{
    margin-bottom:16px;
}

.form-group label{
    display:block;
    margin-bottom:8px;

    font-size:13px;
    font-weight:600;
    color:#334155;
}

.form-group input,
.form-group textarea{
    width:100%;

    border:none;

    background:#f8fafc;

    border-radius:16px;

    padding:14px 16px;

    font-size:14px;
}

.form-group input:focus,
.form-group textarea:focus{
    outline:none;
    box-shadow:0 0 0 4px rgba(59,130,246,0.15);
}

.detail-grid{
    display:grid;
    gap:16px;
}

.detail-item{
    background:#f8fafc;
    border-radius:16px;
    padding:16px;
}

.detail-item strong{
    display:block;
    margin-bottom:5px;
    color:#0f172a;
}

@media(max-width:768px){

    .dashboard-wrapper{
        padding:18px;
    }

    .form-control{
        min-width:100%;
    }

    .filter-bar{
        flex-direction:column;
        align-items:stretch;
    }

    .search-box{
        width:100%;
    }

    .page-header h1{
        font-size:26px;
    }

    table{
        min-width:900px;
    }
}
</style>

<div class="dashboard-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <div class="page-header">
                <h1>Manajemen PBF</h1>
                <p>
                    Kelola seluruh data Pedagang Besar Farmasi dengan tampilan modern.
                </p>
            </div>

            <?php if ($flash): ?>
                <div class="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="filter-bar">

                <form method="GET" class="search-box">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari nama PBF, telepon, kontak..."
                        value="<?= htmlspecialchars($search) ?>"
                    >

                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>

                    <?php if ($search !== ''): ?>
                        <a href="?" class="btn btn-secondary">
                            Reset
                        </a>
                    <?php endif; ?>

                </form>

                <button
                    class="btn btn-primary"
                    onclick="openModal('modalTambahPBF')"
                >
                    + Tambah PBF
                </button>

            </div>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama PBF</th>
                            <th>Kontak</th>
                            <th>Alamat</th>
                            <th>Jumlah Faktur</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($pbfList)): ?>

                            <tr>
                                <td colspan="6" style="text-align:center;padding:30px;">
                                    Tidak ada data PBF
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($pbfList as $i => $pbf): 
                                $stokCount = $model->countStokByPBF((int)$pbf['id_pbf']);
                                $pbfJson = htmlspecialchars(
                                    json_encode($pbf, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>

                            <tr>

                                <td><?= $i + 1 ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($pbf['nama_pbf']) ?>
                                    </strong>

                                    <div style="font-size:12px;color:#94a3b8;margin-top:4px;">
                                        Dibuat:
                                        <?= htmlspecialchars($pbf['created_by'] ?? '-') ?>
                                    </div>
                                </td>

                                <td>
                                    <?= htmlspecialchars($pbf['kontak_person'] ?? '-') ?>

                                    <div style="font-size:12px;color:#94a3b8;margin-top:4px;">
                                        <?= htmlspecialchars($pbf['no_telepon'] ?? '-') ?>
                                    </div>
                                </td>

                                <td style="max-width:240px;">
                                    <?= htmlspecialchars($pbf['alamat'] ?? '-') ?>
                                </td>

                                <td>
                                    <div class="badge-count">
                                        <?= $stokCount ?>
                                    </div>
                                </td>

                                <td>

                                    <div class="action-group">

                                        <button
                                            class="btn btn-primary"
                                            onclick='detailPBF(<?= $pbfJson ?>)'
                                        >
                                            Detail
                                        </button>

                                        <button
                                            class="btn btn-warning"
                                            onclick='editPBF(<?= $pbfJson ?>)'
                                        >
                                            Edit
                                        </button>

                                        <form
                                            method="POST"
                                            action="<?= BASE_URL ?>/backend/controllers/pbf_controller.php?action=delete"
                                            onsubmit="return confirm('Hapus PBF ini?')"
                                        >
                                            <?= csrfField() ?>

                                            <input
                                                type="hidden"
                                                name="id_pbf"
                                                value="<?= $pbf['id_pbf'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-danger"
                                                <?= $stokCount > 0 ? 'disabled' : '' ?>
                                            >
                                                Hapus
                                            </button>

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

    </div>

</div>

<!-- MODAL TAMBAH -->
<div class="modal-overlay" id="modalTambahPBF">

    <div class="modal">

        <div class="modal-header">
            <h3>Tambah PBF</h3>

            <button
                class="modal-close"
                onclick="closeModal('modalTambahPBF')"
            >
                &times;
            </button>
        </div>

        <form
            method="POST"
            action="<?= BASE_URL ?>/backend/controllers/pbf_controller.php?action=create"
        >

            <?= csrfField() ?>

            <div class="form-group">
                <label>Nama PBF</label>

                <input
                    type="text"
                    name="nama_pbf"
                    required
                >
            </div>

            <div class="form-group">
                <label>Alamat</label>

                <textarea
                    name="alamat"
                    rows="3"
                ></textarea>
            </div>

            <div class="form-group">
                <label>No Telepon</label>

                <input
                    type="text"
                    name="no_telepon"
                >
            </div>

            <div class="form-group">
                <label>Kontak Person</label>

                <input
                    type="text"
                    name="kontak_person"
                >
            </div>

            <div class="form-group">
                <label>Keterangan</label>

                <textarea
                    name="keterangan"
                    rows="3"
                ></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan PBF
            </button>

        </form>

    </div>

</div>

<!-- MODAL EDIT -->
<div class="modal-overlay" id="modalEditPBF">

    <div class="modal">

        <div class="modal-header">

            <h3>Edit PBF</h3>

            <button
                class="modal-close"
                onclick="closeModal('modalEditPBF')"
            >
                &times;
            </button>

        </div>

        <form
            method="POST"
            action="<?= BASE_URL ?>/backend/controllers/pbf_controller.php?action=update"
        >

            <?= csrfField() ?>

            <input
                type="hidden"
                name="id_pbf"
                id="editIdPbf"
            >

            <div class="form-group">
                <label>Nama PBF</label>

                <input
                    type="text"
                    name="nama_pbf"
                    id="editNamaPbf"
                    required
                >
            </div>

            <div class="form-group">
                <label>Alamat</label>

                <textarea
                    name="alamat"
                    id="editAlamat"
                    rows="3"
                ></textarea>
            </div>

            <div class="form-group">
                <label>No Telepon</label>

                <input
                    type="text"
                    name="no_telepon"
                    id="editTelepon"
                >
            </div>

            <div class="form-group">
                <label>Kontak Person</label>

                <input
                    type="text"
                    name="kontak_person"
                    id="editKontak"
                >
            </div>

            <div class="form-group">
                <label>Keterangan</label>

                <textarea
                    name="keterangan"
                    id="editKeterangan"
                    rows="3"
                ></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Update PBF
            </button>

        </form>

    </div>

</div>

<!-- MODAL DETAIL -->
<div class="modal-overlay" id="modalDetailPBF">

    <div class="modal">

        <div class="modal-header">

            <h3>Detail PBF</h3>

            <button
                class="modal-close"
                onclick="closeModal('modalDetailPBF')"
            >
                &times;
            </button>

        </div>

        <div id="detailPBFContent"></div>

    </div>

</div>

<script>
function openModal(id){
    document.getElementById(id).classList.add('active');
}

function closeModal(id){
    document.getElementById(id).classList.remove('active');
}

function val(v){
    return (v === null || v === undefined || String(v).trim() === '') ? '-' : String(v);
}

function editPBF(data){

    document.getElementById('editIdPbf').value = data.id_pbf;
    document.getElementById('editNamaPbf').value = data.nama_pbf || '';
    document.getElementById('editAlamat').value = data.alamat || '';
    document.getElementById('editTelepon').value = data.no_telepon || '';
    document.getElementById('editKontak').value = data.kontak_person || '';
    document.getElementById('editKeterangan').value = data.keterangan || '';

    openModal('modalEditPBF');
}

function createDetailItem(label, value){
    const item = document.createElement('div');
    item.className = 'detail-item';

    const title = document.createElement('strong');
    title.textContent = label;

    const body = document.createElement('div');
    body.textContent = val(value);

    item.appendChild(title);
    item.appendChild(body);
    return item;
}

function detailPBF(data){
    const container = document.getElementById('detailPBFContent');
    container.replaceChildren();

    const grid = document.createElement('div');
    grid.className = 'detail-grid';

    grid.appendChild(createDetailItem('Nama PBF', data.nama_pbf));
    grid.appendChild(createDetailItem('Alamat', data.alamat));
    grid.appendChild(createDetailItem('No Telepon', data.no_telepon));
    grid.appendChild(createDetailItem('Kontak Person', data.kontak_person));
    grid.appendChild(createDetailItem('Keterangan', data.keterangan));

    container.appendChild(grid);
    openModal('modalDetailPBF');
}

document.querySelectorAll('.modal-overlay').forEach(function(el){

    el.addEventListener('click',function(e){

        if(e.target === el){
            el.classList.remove('active');
        }

    });

});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
