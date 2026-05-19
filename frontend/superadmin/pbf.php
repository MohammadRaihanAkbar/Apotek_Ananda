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

$search = isset($_GET['search'])
    ? sanitize($_GET['search'])
    : '';

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
    width:180px;
    height:180px;
    top:5%;
    left:-70px;
}

.bg-bubble.two{
    width:240px;
    height:240px;
    bottom:-100px;
    right:-70px;
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
    padding:18px;
    position:relative;
    z-index:10;
}

/* GLASS */
.glass-container{
    background:rgba(255,255,255,0.35);

    border:1px solid rgba(255,255,255,0.7);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

    border-radius:22px;

    padding:18px;

    box-shadow:
        0 10px 30px rgba(15,23,42,0.06),
        inset 0 1px 0 rgba(255,255,255,0.7);
}

.glass-inner{
    background:rgba(255,255,255,0.25);

    border:1px solid rgba(255,255,255,0.55);

    border-radius:18px;

    padding:18px;
}

/* HEADER */
.page-header{
    margin-bottom:16px;
}

.page-header h1{
    font-size:24px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:4px;
}

.page-header p{
    color:#64748b;
    font-size:12px;
}

/* ALERT */
.alert{
    padding:10px 14px;
    border-radius:12px;
    margin-bottom:16px;

    background:rgba(255,255,255,0.5);
    backdrop-filter:blur(10px);

    font-size:13px;
}

/* FILTER */
.filter-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-bottom:16px;
    flex-wrap:wrap;
}

.search-box{
    display:flex;
    align-items:center;
    gap:8px;

    background:rgba(255,255,255,0.45);

    border:1px solid rgba(255,255,255,0.7);

    border-radius:14px;

    padding:6px;
}

.form-control{
    border:none !important;
    background:transparent !important;

    padding:8px 10px !important;

    min-width:220px;

    outline:none !important;
    box-shadow:none !important;

    font-size:13px !important;
}

/* BUTTON */
.btn{
    border:none !important;
    border-radius:10px !important;

    padding:8px 12px !important;

    font-size:12px !important;
    font-weight:600 !important;

    transition:.2s ease;
}

.btn:hover{
    transform:translateY(-1px);
}

.btn-primary{
    color:#fff !important;

    background:linear-gradient(
        135deg,
        #3b82f6,
        #2563eb
    ) !important;
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
    border-radius:16px;
}

table{
    width:100%;
    border-collapse:collapse;
    overflow:hidden;
}

thead th{
    background:rgba(219,234,254,0.7);

    padding:12px;

    text-align:left;

    font-size:12px;
    font-weight:700;
    color:#334155;
}

tbody td{
    padding:12px;

    background:rgba(255,255,255,0.35);

    border-bottom:1px solid rgba(226,232,240,0.7);

    font-size:12px;
    color:#334155;
}

tbody tr{
    transition:.2s ease;
}

tbody tr:hover{
    background:rgba(255,255,255,0.55);
}

.badge-count{
    width:28px;
    height:28px;

    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    background:#dbeafe;

    color:#2563eb;
    font-size:12px;
    font-weight:700;
}

/* ACTION */
.action-group{
    display:flex;
    gap:6px;
    flex-wrap:wrap;
}

/* MOBILE */
@media(max-width:768px){

    .dashboard-wrapper{
        padding:12px;
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
        flex-wrap:wrap;
    }

    .page-header h1{
        font-size:22px;
    }

    table{
        min-width:720px;
    }
}
</style>

<div class="dashboard-wrapper">

    <div class="glass-container">

        <div class="glass-inner">

            <div class="page-header">
                <h1>Manajemen PBF</h1>
                <p>
                    Kelola seluruh data Pedagang Besar Farmasi.
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
                        placeholder="Cari PBF..."
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
                    + Tambah
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
                            <th>Faktur</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($pbfList)): ?>

                        <tr>
                            <td colspan="6" style="text-align:center;padding:20px;">
                                Tidak ada data
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($pbfList as $i => $pbf):

                            $stokCount = $model->countStokByPBF(
                                (int)$pbf['id_pbf']
                            );
                        ?>

                        <tr>

                            <td><?= $i + 1 ?></td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($pbf['nama_pbf']) ?>
                                </strong>

                                <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                    <?= htmlspecialchars($pbf['created_by'] ?? '-') ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars($pbf['kontak_person'] ?? '-') ?>

                                <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                    <?= htmlspecialchars($pbf['no_telepon'] ?? '-') ?>
                                </div>
                            </td>

                            <td style="max-width:200px;">
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
                                        onclick='detailPBF(<?= json_encode($pbf) ?>)'
                                    >
                                        Detail
                                    </button>

                                    <button
                                        class="btn btn-warning"
                                        onclick='editPBF(<?= json_encode($pbf) ?>)'
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
