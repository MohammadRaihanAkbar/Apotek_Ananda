<?php
/**
 * Piutang - Otomatis dari faktur.
 * Tampilan modern glassmorphism compact.
 */

require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

$pageTitle = 'Manajemen Piutang';

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../backend/models/piutang.php';

$model = new Piutang();

$search = isset($_GET['search'])
    ? sanitize($_GET['search'])
    : null;

$listBelumLunas = $model->getAllByStatus('belum_lunas', $search);
$listLunas      = $model->getAllByStatus('lunas', $search);
$summary        = $model->getSummary();
$flash          = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>

<!-- BACKGROUND -->
<div class="bg-grid"></div>
<div class="bg-bubble one"></div>
<div class="bg-bubble two"></div>
<div class="bg-bubble three"></div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

:root{
    --primary:#2563eb;
    --success:#10b981;
    --danger:#ef4444;
    --warning:#f59e0b;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

html,body{
    width:100%;
    overflow-x:hidden;
}

body{
    min-height:100vh;
    color:#0f172a;

    background:
        linear-gradient(
            135deg,
            #f8fbff 0%,
            #eef5ff 45%,
            #ffffff 100%
        );

    position:relative;
}

/* BACKGROUND HIDUP */
body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle, rgba(59,130,246,0.16) 0%, transparent 60%),
        radial-gradient(circle, rgba(96,165,250,0.12) 0%, transparent 60%);

    background-size:600px 600px;

    animation:moveGlow 18s linear infinite;

    filter:blur(45px);

    z-index:-5;
}

.bg-grid{
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);

    background-size:32px 32px;

    z-index:-4;
}

.bg-bubble{
    position:fixed;
    border-radius:50%;
    pointer-events:none;

    background:
        radial-gradient(circle at 30% 30%,
        rgba(255,255,255,0.95),
        rgba(255,255,255,0.08));

    box-shadow:
        inset 0 0 35px rgba(255,255,255,.95),
        0 0 45px rgba(59,130,246,.14);

    animation:floating 12s ease-in-out infinite;

    z-index:-2;
}

.bg-bubble.one{
    width:180px;
    height:180px;
    top:-60px;
    left:-60px;
}

.bg-bubble.two{
    width:220px;
    height:220px;
    bottom:-80px;
    right:-80px;
}

.bg-bubble.three{
    width:130px;
    height:130px;
    top:42%;
    right:16%;
}

@keyframes moveGlow{
    0%{
        transform:translate(0,0) rotate(0deg);
    }

    50%{
        transform:translate(30px,-20px) rotate(180deg);
    }

    100%{
        transform:translate(0,0) rotate(360deg);
    }
}

@keyframes floating{
    0%,100%{
        transform:translateY(0px);
    }

    50%{
        transform:translateY(-16px);
    }
}

/* WRAPPER */
.dashboard-wrapper{
    padding:14px;
    position:relative;
    z-index:10;
}

/* GLASS */
.glass-container{
    background:rgba(255,255,255,0.38);

    border:1px solid rgba(255,255,255,0.8);

    backdrop-filter:blur(18px);

    border-radius:22px;

    padding:14px;

    box-shadow:
        0 10px 30px rgba(15,23,42,0.08);
}

.glass-inner{
    background:rgba(255,255,255,0.28);

    border:1px solid rgba(255,255,255,0.7);

    border-radius:18px;

    padding:14px;
}

/* HEADER */
.page-header{
    margin-bottom:14px;
}

.page-header h1{
    font-size:22px;
    font-weight:700;
    margin-bottom:3px;
}

.page-header p{
    color:#64748b;
    font-size:11px;
}

/* ALERT */
.alert{
    padding:10px 12px;
    border-radius:12px;
    margin-bottom:12px;
    font-size:12px;

    background:rgba(255,255,255,.55);
    border:1px solid rgba(255,255,255,.8);
}

/* STATS */
.stats-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
    margin-bottom:14px;
}

.stat-card{
    border-radius:16px;
    padding:12px;
    color:#fff;
    min-height:78px;
    position:relative;
    overflow:hidden;
}

.stat-card.total{
    background:linear-gradient(135deg,#3b82f6,#2563eb);
}

.stat-card.success{
    background:linear-gradient(135deg,#10b981,#059669);
}

.stat-card.danger{
    background:linear-gradient(135deg,#ef4444,#dc2626);
}

.stat-value{
    font-size:16px;
    font-weight:700;
}

.stat-label{
    margin-top:5px;
    font-size:11px;
}

/* FILTER */
.filter-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:12px;
}

.search-box{
    flex:1;
    display:flex;
    gap:6px;
    min-width:240px;
}

.form-control{
    width:100%;
    border:none;
    border-radius:10px;
    padding:9px 12px;
    font-size:12px;

    background:rgba(255,255,255,.65);

    border:1px solid rgba(255,255,255,.8);

    outline:none;
}

.form-control:focus{
    box-shadow:0 0 0 3px rgba(59,130,246,.14);
}

/* BUTTON */
.btn{
    border:none !important;

    border-radius:10px !important;

    padding:8px 12px !important;

    font-size:11px !important;
    font-weight:600 !important;

    cursor:pointer;

    text-decoration:none;

    display:inline-flex;
    align-items:center;
    justify-content:center;

    white-space:nowrap;

    transition:.2s;
}

.btn:hover{
    transform:translateY(-1px);
}

.btn-primary{
    background:linear-gradient(135deg,#3b82f6,#2563eb)!important;
    color:#fff!important;
}

.btn-success{
    background:linear-gradient(135deg,#10b981,#059669)!important;
    color:#fff!important;
}

.btn-danger{
    background:linear-gradient(135deg,#ef4444,#dc2626)!important;
    color:#fff!important;
}

.btn-warning{
    background:linear-gradient(135deg,#f59e0b,#d97706)!important;
    color:#fff!important;
}

.btn-secondary{
    background:#e2e8f0!important;
    color:#334155!important;
}

/* CARD */
.card{
    background:rgba(255,255,255,0.34);

    border:1px solid rgba(255,255,255,.8);

    backdrop-filter:blur(16px);

    border-radius:18px;

    padding:12px;

    margin-bottom:12px;
}

.card-title{
    display:flex;
    align-items:center;
    gap:8px;

    margin-bottom:10px;

    font-size:14px;
    font-weight:700;
}

/* TABLE */
.table-wrapper{
    overflow:auto;
    border-radius:14px;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:860px;
}

thead th{
    background:rgba(219,234,254,0.75);

    padding:10px;

    font-size:11px;
    font-weight:700;

    color:#334155;
}

tbody td{
    padding:10px;

    background:rgba(255,255,255,0.45);

    border-bottom:1px solid rgba(226,232,240,0.6);

    font-size:11px;
}

tbody tr:hover{
    background:rgba(255,255,255,.65);
}

.text-center{
    text-align:center;
}

.text-right{
    text-align:right;
}

/* MODAL */
.modal-overlay{
    position:fixed;
    inset:0;

    background:rgba(15,23,42,.45);

    display:none;
    align-items:center;
    justify-content:center;

    padding:16px;

    z-index:999;
}

.modal-overlay.active{
    display:flex;
}

.modal{
    width:100%;
    max-width:400px;

    background:rgba(255,255,255,.95);

    border-radius:18px;

    padding:18px;
}

.modal-header{
    display:flex;
    align-items:center;
    justify-content:space-between;

    margin-bottom:14px;
}

.modal-header h3{
    font-size:18px;
}

.modal-close{
    border:none;

    width:32px;
    height:32px;

    border-radius:10px;

    background:#f1f5f9;

    cursor:pointer;

    font-size:18px;
}

.form-group{
    margin-bottom:14px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-size:12px;
    font-weight:600;
}

/* MOBILE */
@media(max-width:768px){

    .stats-grid{
        grid-template-columns:1fr;
    }

    .filter-bar{
        flex-direction:column;
        align-items:stretch;
    }

    .search-box{
        width:100%;
    }

    .dashboard-wrapper{
        padding:10px;
    }
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

    margin-bottom:18px;
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

/* MOBILE */
@media(max-width:768px){

    .dashboard-wrapper{
        padding:14px;
    }

    .glass-container{
        padding:16px;
    }

    .glass-inner{
        padding:16px;
    }

    .page-header h1{
        font-size:24px;
    }

    .filter-bar{
        flex-direction:column;
        align-items:stretch;
    }

    .search-box{
        width:100%;
    }

    .stats-grid{
        grid-template-columns:1fr;
    }
}
</style>

<div class="dashboard-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <div class="page-header">
                <h1>Manajemen Piutang</h1>

                <p>
                    Piutang otomatis dari faktur pembelian dan monitoring pembayaran.
                </p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- STATS -->
            <div class="stats-grid">

                <div class="stat-card total">
                    <div class="stat-value">
                        Rp <?= number_format($summary['total_semua'] ?? 0,0,',','.') ?>
                    </div>

                    <div class="stat-label">
                        💰 Total Faktur
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-value">
                        Rp <?= number_format($summary['total_lunas'] ?? 0,0,',','.') ?>
                    </div>

                    <div class="stat-label">
                        ✅ Lunas (<?= $summary['count_lunas'] ?? 0 ?>)
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-value">
                        Rp <?= number_format($summary['total_belum_lunas'] ?? 0,0,',','.') ?>
                    </div>

                    <div class="stat-label">
                        ❌ Belum Lunas (<?= $summary['count_belum_lunas'] ?? 0 ?>)
                    </div>
                </div>

            </div>
            <!-- FILTER -->
<div class="filter-bar">

    <form method="GET" class="search-box">

        <input
            type="text"
            name="search"
            class="form-control autocomplete-input"
            data-type="piutang"
            placeholder="🔍 Cari no faktur / PBF / obat..."
            value="<?= htmlspecialchars($search ?? '') ?>"
            autocomplete="off"
        >

        <button type="submit" class="btn btn-primary btn-sm">
            Cari
        </button>

        <a href="?" class="btn btn-secondary btn-sm">
            Reset
        </a>

    </form>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">

        <a
            href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_excel&search=<?= urlencode($search ?? '') ?>"
            class="btn btn-success btn-sm"
        >
            📊 Excel
        </a>

        <a
            href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_pdf&search=<?= urlencode($search ?? '') ?>"
            class="btn btn-danger btn-sm"
            target="_blank"
        >
            📄 PDF
        </a>

    </div>

</div>

<!-- INFO -->
<div class="card" style="border-left:4px solid #3b82f6;">

    <strong>Catatan:</strong>

    Tambah/edit data faktur dilakukan dari menu
    <strong>Manajemen Stok</strong>.
    Halaman ini hanya mengambil data faktur dan mengelola status pembayaran.

</div>

<!-- BELUM LUNAS -->
<div class="card" style="border-left:4px solid var(--danger);">

    <div class="card-title" style="color:var(--danger);">

        <span class="material-icons-round">
            warning
        </span>

        Belum Lunas
        (<?= count($listBelumLunas) ?>)

    </div>

    <div class="table-wrapper">

        <table>

            <thead>

                <tr>
                    <th>No</th>
                    <th>No Faktur</th>
                    <th>PBF</th>
                    <th>Tanggal</th>
                    <th>Jatuh Tempo</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Bukti</th>
                    <th>Aksi</th>
                </tr>

            </thead>

            <tbody>

                <?php if (empty($listBelumLunas)): ?>

                    <tr>

                        <td colspan="9" class="text-center" style="padding:35px;color:#94a3b8;">
                            Tidak ada piutang belum lunas 🎉
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($listBelumLunas as $i => $p): ?>

                        <tr>

                            <td>
                                <?= $i + 1 ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($p['no_faktur']) ?>
                                </strong>

                            </td>

                            <td>
                                <?= htmlspecialchars($p['nama_pbf']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($p['tanggal_faktur']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($p['tanggal_jatuh_tempo'] ?? '-') ?>
                            </td>

                            <td class="text-right">
                                <?= (int)$p['jumlah_item'] ?>
                            </td>

                            <td class="text-right">

                                Rp
                                <?= number_format($p['jumlah_harga'],0,',','.') ?>

                            </td>

                            <td>

                                <?php if ($p['bukti_pembayaran']): ?>

                                    <a
                                        href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= $p['id_faktur'] ?>"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        📎 Lihat
                                    </a>

                                <?php else: ?>

                                    <span style="color:#94a3b8;">-</span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <button
                                    class="btn btn-success btn-sm"
                                    onclick="lunasi(<?= $p['id_faktur'] ?>,'<?= htmlspecialchars($p['no_faktur']) ?>')"
                                >
                                    ✅ Lunas
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- LUNAS -->
<div class="card" style="border-left:4px solid var(--success);">

    <div class="card-title" style="color:var(--success);">

        <span class="material-icons-round">
            check_circle
        </span>

        Lunas
        (<?= count($listLunas) ?>)

    </div>

    <div class="table-wrapper">

        <table>

            <thead>

                <tr>
                    <th>No</th>
                    <th>No Faktur</th>
                    <th>PBF</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Tanggal Lunas</th>
                    <th>Bukti</th>
                    <th>Aksi</th>
                </tr>

            </thead>

            <tbody>

                <?php if (empty($listLunas)): ?>

                    <tr>

                        <td colspan="8" class="text-center" style="padding:35px;color:#94a3b8;">
                            Tidak ada data lunas
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($listLunas as $i => $p): ?>

                        <tr>

                            <td>
                                <?= $i + 1 ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($p['no_faktur']) ?>
                                </strong>

                            </td>

                            <td>
                                <?= htmlspecialchars($p['nama_pbf']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($p['tanggal_faktur']) ?>
                            </td>

                            <td class="text-right">

                                Rp
                                <?= number_format($p['jumlah_harga'],0,',','.') ?>

                            </td>

                            <td>
                                <?= htmlspecialchars($p['tanggal_lunas'] ?? '-') ?>
                            </td>

                            <td>

                                <?php if ($p['bukti_pembayaran']): ?>

                                    <a
                                        href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= $p['id_faktur'] ?>"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        📎 Lihat
                                    </a>

                                <?php else: ?>

                                    <span style="color:#94a3b8;">-</span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <form
                                    method="POST"
                                    action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=belum_lunas"
                                    onsubmit="return confirm('Ubah faktur ini menjadi belum lunas?')"
                                >

                                    <?= csrfField() ?>

                                    <input
                                        type="hidden"
                                        name="id_faktur"
                                        value="<?= $p['id_faktur'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-warning btn-sm"
                                    >
                                        ↩ Belum
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalLunasi">

    <div class="modal">

        <div class="modal-header">

            <h3>Tandai Faktur Lunas</h3>

            <button
                class="modal-close"
                onclick="closeModal('modalLunasi')"
            >
                &times;
            </button>

        </div>

        <form
            method="POST"
            action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=lunasi"
            enctype="multipart/form-data"
        >

            <?= csrfField() ?>

            <input
                type="hidden"
                name="id_faktur"
                id="lunasiId"
            >

            <p style="margin-bottom:16px;">

                Faktur:
                <strong id="lunasiFaktur"></strong>

            </p>

            <div class="form-group">

                <label>
                    Bukti Pembayaran (opsional)
                </label>

                <input
                    type="file"
                    name="bukti_pembayaran"
                    class="form-control"
                    accept="image/*,.pdf"
                >

                <small style="color:#64748b;display:block;margin-top:8px;">
                    Format JPG, PNG, WEBP, PDF maksimal 5MB.
                </small>

            </div>

            <button
                type="submit"
                class="btn btn-success"
            >
                ✅ Simpan Status Lunas
            </button>

        </form>

    </div>

</div>

</div>
</div>
</div>

<script>
function openModal(id){
    document.getElementById(id).classList.add('active');
}

function closeModal(id){
    document.getElementById(id).classList.remove('active');
}

function lunasi(id,faktur){

    document.getElementById('lunasiId').value = id;

    document.getElementById('lunasiFaktur').textContent = faktur;

    openModal('modalLunasi');
}

document.querySelectorAll('.modal-overlay').forEach(function(el){

    el.addEventListener('click',function(e){

        if(e.target === el){
            closeModal(el.id);
        }

    });

});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
