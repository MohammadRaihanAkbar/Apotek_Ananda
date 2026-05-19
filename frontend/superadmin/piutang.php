<?php
/**
 * Piutang - Compact Glassmorphism Modern
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

<!-- BG -->
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

/* BG */
body::before{
    content:'';
    position:fixed;
    inset:-20%;

    background:
        radial-gradient(circle, rgba(59,130,246,.16) 0%, transparent 60%),
        radial-gradient(circle, rgba(96,165,250,.12) 0%, transparent 60%);

    background-size:700px 700px;

    animation:moveGlow 18s linear infinite;

    filter:blur(40px);

    z-index:-4;
}

.bg-grid{
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);

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
            rgba(255,255,255,.95),
            rgba(255,255,255,.08)
        );

    box-shadow:
        inset 0 0 25px rgba(255,255,255,.9),
        0 0 35px rgba(59,130,246,.10);

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
    width:280px;
    height:280px;
    bottom:-100px;
    right:-60px;
    animation-duration:18s;
}

@keyframes moveGlow{
    0%{
        transform:translate(0,0) rotate(0deg);
    }

    50%{
        transform:translate(40px,-30px) rotate(180deg);
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
        transform:translateY(-20px);
    }
}

/* WRAPPER */
.dashboard-wrapper{
    padding:18px;
    position:relative;
    z-index:10;
}

/* GLASS */
.glass-container{
    background:rgba(255,255,255,.40);

    border:1px solid rgba(255,255,255,.8);

    backdrop-filter:blur(24px);

    border-radius:26px;

    padding:18px;

    box-shadow:
        0 15px 45px rgba(15,23,42,.08),
        inset 0 1px 0 rgba(255,255,255,.8);
}

.glass-inner{
    background:rgba(255,255,255,.28);

    border:1px solid rgba(255,255,255,.7);

    border-radius:22px;

    padding:18px;
}

/* HEADER */
.page-header{
    margin-bottom:18px;
}

.page-header h1{
    font-size:26px;
    font-weight:700;
    margin-bottom:4px;
}

.page-header p{
    font-size:12px;
    color:#64748b;
}

/* ALERT */
.alert{
    padding:12px 14px;
    border-radius:14px;
    margin-bottom:16px;

    background:rgba(255,255,255,.7);

    border:1px solid rgba(255,255,255,.8);

    font-size:12px;
}

/* STATS */
.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:12px;

    margin-bottom:18px;
}

.stat-card{
    border-radius:18px;
    padding:16px;
    color:#fff;
    min-height:90px;
    position:relative;
    overflow:hidden;
}

.stat-card::before{
    content:'';

    position:absolute;

    width:100px;
    height:100px;

    border-radius:50%;

    top:-30px;
    right:-20px;

    background:rgba(255,255,255,.14);
}

.stat-card.primary{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
}

.stat-card.success{
    background:linear-gradient(135deg,#10b981,#059669);
}

.stat-card.danger{
    background:linear-gradient(135deg,#ef4444,#dc2626);
}

.stat-value{
    font-size:18px;
    font-weight:700;
    position:relative;
    z-index:2;
}

.stat-label{
    margin-top:5px;
    font-size:11px;
    opacity:.92;
    position:relative;
    z-index:2;
}

/* GLASS CARD */
.glass-card{
    background:rgba(255,255,255,.35);

    border:1px solid rgba(255,255,255,.75);

    backdrop-filter:blur(20px);

    border-radius:20px;

    padding:16px;

    margin-bottom:16px;
}

/* FILTER */
.filter-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.search-box{
    display:flex;
    gap:8px;
    flex:1;
    min-width:240px;
}

.form-control{
    width:100%;
    height:40px;

    border:none;

    background:rgba(255,255,255,.55);

    border:1px solid rgba(255,255,255,.9);

    border-radius:12px;

    padding:0 12px;

    outline:none;

    font-size:12px;
}

.form-control:focus{
    background:#fff;

    box-shadow:
        0 0 0 4px rgba(59,130,246,.12);
}

/* BUTTON */
.btn{
    border:none !important;

    border-radius:12px !important;

    padding:9px 14px !important;

    font-size:11px !important;

    font-weight:600 !important;

    text-decoration:none !important;

    transition:.25s ease;

    display:inline-flex;
    align-items:center;
    justify-content:center;

    white-space:nowrap;
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
}

.btn-success{
    background:#10b981 !important;
    color:#fff !important;
}

.btn-danger{
    background:#ef4444 !important;
    color:#fff !important;
}

.btn-warning{
    background:#f59e0b !important;
    color:#fff !important;
}

.btn-secondary{
    background:#e2e8f0 !important;
    color:#334155 !important;
}

/* TITLE */
.section-title{
    display:flex;
    align-items:center;
    justify-content:space-between;

    margin-bottom:12px;
}

.section-title h3{
    font-size:15px;
    font-weight:700;
}

/* TABLE */
.table-wrapper{
    overflow:auto;
    border-radius:18px;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
}

thead th{
    background:rgba(219,234,254,.72);

    padding:12px;

    text-align:left;

    font-size:11px;
    font-weight:700;

    color:#334155;

    white-space:nowrap;
}

tbody td{
    padding:12px;

    background:rgba(255,255,255,.40);

    border-bottom:1px solid rgba(226,232,240,.8);

    font-size:12px;

    color:#334155;

    white-space:nowrap;
}

tbody tr{
    transition:.25s ease;
}

tbody tr:hover{
    background:rgba(255,255,255,.7);
}

.text-center{
    text-align:center;
}

.text-right{
    text-align:right;
}

/* BADGE */
.badge{
    padding:6px 10px;

    border-radius:999px;

    font-size:10px;
    font-weight:700;
}

.badge-danger{
    background:#fee2e2;
    color:#dc2626;
}

.badge-success{
    background:#dcfce7;
    color:#16a34a;
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
    max-width:420px;

    background:#fff;

    border-radius:20px;

    padding:20px;
}

.modal-header{
    display:flex;
    align-items:center;
    justify-content:space-between;

    margin-bottom:16px;
}

.modal-header h3{
    font-size:18px;
    font-weight:700;
}

.modal-close{
    border:none;

    width:34px;
    height:34px;

    border-radius:10px;

    background:#f1f5f9;

    cursor:pointer;

    font-size:20px;
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

    .dashboard-wrapper{
        padding:12px;
    }

    .glass-container,
    .glass-inner{
        padding:14px;
    }

    .page-header h1{
        font-size:22px;
    }

    .filter-bar{
        flex-direction:column;
        align-items:stretch;
    }

    .search-box{
        width:100%;
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
                <h1>Manajemen Piutang</h1>

                <p>
                    Monitoring pembayaran faktur pembelian.
                </p>
            </div>

            <?php if ($flash): ?>
                <div class="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- STATS -->
            <div class="stats-grid">

                <div class="stat-card primary">
                    <div class="stat-value">
                        Rp <?= number_format($summary['total_semua'] ?? 0,0,',','.') ?>
                    </div>

                    <div class="stat-label">
                        Total Faktur
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-value">
                        Rp <?= number_format($summary['total_lunas'] ?? 0,0,',','.') ?>
                    </div>

                    <div class="stat-label">
                        Lunas (<?= $summary['count_lunas'] ?? 0 ?>)
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-value">
                        Rp <?= number_format($summary['total_belum_lunas'] ?? 0,0,',','.') ?>
                    </div>

                    <div class="stat-label">
                        Belum Lunas (<?= $summary['count_belum_lunas'] ?? 0 ?>)
                    </div>
                </div>

            </div>

            <!-- FILTER -->
            <div class="glass-card">

                <div class="filter-bar">

                    <form method="GET" class="search-box">

                        <input
                            type="text"
                            name="search"
                            class="form-control autocomplete-input"
                            data-type="piutang"
                            placeholder="Cari no faktur / PBF / obat..."
                            value="<?= htmlspecialchars($search ?? '') ?>"
                            autocomplete="off"
                        >

                        <button type="submit" class="btn btn-primary">
                            Cari
                        </button>

                        <a href="?" class="btn btn-secondary">
                            Reset
                        </a>

                    </form>

                    <div style="display:flex;gap:8px;flex-wrap:wrap;">

                        <a
                            href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_excel&search=<?= urlencode($search ?? '') ?>"
                            class="btn btn-success"
                        >
                            Excel
                        </a>

                        <a
                            href="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=export_pdf&search=<?= urlencode($search ?? '') ?>"
                            class="btn btn-danger"
                            target="_blank"
                        >
                            PDF
                        </a>

                    </div>

                </div>

            </div>

            <!-- BELUM LUNAS -->
            <div class="glass-card">

                <div class="section-title">

                    <h3 style="color:#dc2626;">
                        Belum Lunas (<?= count($listBelumLunas) ?>)
                    </h3>

                </div>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>
                                <th>No</th>
                                <th>No Faktur</th>
                                <th>PBF</th>
                                <th>Tanggal</th>
                                <th>Tempo</th>
                                <th>Total</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php if (empty($listBelumLunas)): ?>

                            <tr>
                                <td colspan="9" class="text-center" style="padding:30px;color:#94a3b8;">
                                    Tidak ada data
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($listBelumLunas as $i => $p): ?>

                                <tr>

                                    <td><?= $i + 1 ?></td>

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
                                        Rp <?= number_format($p['jumlah_harga'],0,',','.') ?>
                                    </td>

                                    <td>

                                        <?php if ($p['bukti_pembayaran']): ?>

                                            <a
                                                href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= $p['id_faktur'] ?>"
                                                class="btn btn-secondary"
                                            >
                                                Lihat
                                            </a>

                                        <?php else: ?>

                                            -

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <span class="badge badge-danger">
                                            Belum
                                        </span>

                                    </td>

                                    <td>

                                        <button
                                            class="btn btn-success"
                                            onclick="lunasi(<?= $p['id_faktur'] ?>,'<?= htmlspecialchars($p['no_faktur']) ?>')"
                                        >
                                            Lunas
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
            <div class="glass-card">

                <div class="section-title">

                    <h3 style="color:#16a34a;">
                        Lunas (<?= count($listLunas) ?>)
                    </h3>

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
                                <th>Tgl Lunas</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php if (empty($listLunas)): ?>

                            <tr>
                                <td colspan="9" class="text-center" style="padding:30px;color:#94a3b8;">
                                    Tidak ada data
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($listLunas as $i => $p): ?>

                                <tr>

                                    <td><?= $i + 1 ?></td>

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
                                        Rp <?= number_format($p['jumlah_harga'],0,',','.') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($p['tanggal_lunas'] ?? '-') ?>
                                    </td>

                                    <td>

                                        <?php if ($p['bukti_pembayaran']): ?>

                                            <a
                                                href="<?= BASE_URL ?>/frontend/superadmin/lihat_bukti.php?id=<?= $p['id_faktur'] ?>"
                                                class="btn btn-secondary"
                                            >
                                                Lihat
                                            </a>

                                        <?php else: ?>

                                            -

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <span class="badge badge-success">
                                            Lunas
                                        </span>

                                    </td>

                                    <td>

                                        <form
                                            method="POST"
                                            action="<?= BASE_URL ?>/backend/controllers/piutang_controller.php?action=belum_lunas"
                                            onsubmit="return confirm('Ubah menjadi belum lunas?')"
                                        >

                                            <?= csrfField() ?>

                                            <input
                                                type="hidden"
                                                name="id_faktur"
                                                value="<?= $p['id_faktur'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-warning"
                                            >
                                                Undo
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

        </div>

    </div>

</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalLunasi">

    <div class="modal">

        <div class="modal-header">

            <h3>Tandai Lunas</h3>

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

            <p style="margin-bottom:16px;font-size:13px;">

                Faktur:
                <strong id="lunasiFaktur"></strong>

            </p>

            <div class="form-group">

                <label>
                    Bukti Pembayaran
                </label>

                <input
                    type="file"
                    name="bukti_pembayaran"
                    class="form-control"
                    accept="image/*,.pdf"
                >

            </div>

            <button
                type="submit"
                class="btn btn-success"
            >
                Simpan
            </button>

        </form>

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
