<?php
/**
 * Kelola Staff - Admin - Apotek Ananda Jadimulya
 */
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();

$pageTitle = 'Kelola Staff';
require_once __DIR__ . '/../templates/header.php';

require_once __DIR__ . '/../../backend/models/user.php';
$userModel = new User();
$adminList = $userModel->getAllAdmins();
$flash = getFlashMessage();

require_once __DIR__ . '/../templates/sidebar.php';
?>
<div class="dashboard-wrapper">
  <div class="glass-container">
    <div class="glass-inner">
    <div class="page-header">
        <h1>Kelola Staff</h1>
        <p>Tambah, edit, dan hapus akun staff</p>
    </div>
    
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    
    <div style="margin-bottom:15px;">
        <button class="btn btn-primary" onclick="openModal('modalTambahAdmin')">+ Tambah Staff</button>
    </div>
    
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>No</th><th>Nama Lengkap</th><th>Username</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($adminList)): ?>
                        <tr><td colspan="6" class="text-center" style="color:#94a3b8;padding:30px;">Belum ada akun staff</td></tr>
                    <?php else: ?>
                        <?php foreach ($adminList as $i => $admin): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($admin['nama_lengkap']) ?></strong></td>
                            <td><?= htmlspecialchars($admin['username']) ?></td>
                            <td><span class="badge badge-success">Staff</span></td>
                            <td><?= date('d/m/Y', strtotime($admin['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick='editAdmin(<?= json_encode($admin) ?>)'><span class="material-icons-round">edit</span>Edit</button>
                                <form method="POST" action="<?= BASE_URL ?>/backend/controllers/admin_controller.php?action=delete" style="display:inline" onsubmit="return confirm('Hapus akun staff ini? Semua data terkait akan ikut terhapus.')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id_user" value="<?= $admin['id_user'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><span class="material-icons-round">delete</span>Hapus</button>
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

<!-- Modal Tambah Staff -->
<div class="modal-overlay" id="modalTambahAdmin">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Staff Baru</h3><button class="modal-close" onclick="closeModal('modalTambahAdmin')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/admin_controller.php?action=create">
            <?= csrfField() ?>
            <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama_lengkap" class="form-control" required></div>
            <div class="form-group"><label>Username * (min 4 karakter, huruf/angka/_)</label><input type="text" name="username" class="form-control" required minlength="4" pattern="[a-zA-Z0-9_]+"></div>
            <div class="form-group"><label>Password * (min 6 karakter)</label><input type="password" name="password" class="form-control" required minlength="6"></div>
            <div class="form-group"><label>Konfirmasi Password *</label><input type="password" name="confirm_password" class="form-control" required minlength="6"></div>
            <button type="submit" class="btn btn-primary"><span class="material-icons-round">save</span>Simpan Staff</button>
        </form>
    </div>
</div>

<!-- Modal Edit Staff -->
<div class="modal-overlay" id="modalEditAdmin">
    <div class="modal">
        <div class="modal-header"><h3>Edit Staff</h3><button class="modal-close" onclick="closeModal('modalEditAdmin')">&times;</button></div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/admin_controller.php?action=update">
            <?= csrfField() ?>
            <input type="hidden" name="id_user" id="adminEditId">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" id="adminEditNama" class="form-control" required></div>
            <div class="form-group"><label>Username</label><input type="text" name="username" id="adminEditUsername" class="form-control" required minlength="4"></div>
            <div class="form-group"><label>Password Baru (kosongkan jika tidak diubah)</label><input type="password" name="password" class="form-control" minlength="6"></div>
            <div class="form-group"><label>Konfirmasi Password Baru</label><input type="password" name="confirm_password" class="form-control" minlength="6"></div>
            <button type="submit" class="btn btn-primary"><span class="material-icons-round">save</span>Update Staff</button>
        </form>
    </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('active')}
function closeModal(id){document.getElementById(id).classList.remove('active')}
function editAdmin(d){document.getElementById('adminEditId').value=d.id_user;document.getElementById('adminEditNama').value=d.nama_lengkap;document.getElementById('adminEditUsername').value=d.username;openModal('modalEditAdmin')}
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
</script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

:root{
    --primary:#2563eb;
    --primary2:#60a5fa;
    --danger:#ef4444;
    --warning:#f59e0b;
    --success:#10b981;
}

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
        radial-gradient(circle, rgba(59,130,246,.16) 0%, transparent 60%),
        radial-gradient(circle, rgba(96,165,250,.13) 0%, transparent 60%);

    background-size:700px 700px;

    animation:moveGlow 18s linear infinite;

    filter:blur(45px);

    z-index:-5;
}

body::after{
    content:'';
    position:fixed;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);

    background-size:32px 32px;

    mask-image:
        radial-gradient(circle at center, black 35%, transparent 85%);

    z-index:-4;
}

@keyframes moveGlow{

    0%{
        transform:translate(0,0) rotate(0deg);
    }

    50%{
        transform:translate(35px,-25px) rotate(180deg);
    }

    100%{
        transform:translate(0,0) rotate(360deg);
    }
}

/* BUBBLE */
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
        inset 0 0 30px rgba(255,255,255,.95),
        0 0 45px rgba(59,130,246,.15);

    animation:floating 10s ease-in-out infinite;

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
    bottom:-90px;
    right:-80px;
    animation-duration:15s;
}

@keyframes floating{

    0%,100%{
        transform:translateY(0px);
    }

    50%{
        transform:translateY(-18px);
    }
}

/* WRAPPER */
.dashboard-wrapper{
    padding:14px;
    position:relative;
    z-index:5;
}

/* GLASS */
.glass-container{
    background:rgba(255,255,255,.40);

    border:1px solid rgba(255,255,255,.85);

    backdrop-filter:blur(22px);
    -webkit-backdrop-filter:blur(22px);

    border-radius:22px;

    padding:14px;

    box-shadow:
        0 10px 35px rgba(15,23,42,.08),
        inset 0 1px 0 rgba(255,255,255,.75);
}

.glass-inner{
    background:rgba(255,255,255,.28);

    border:1px solid rgba(255,255,255,.70);

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
    color:#111827;
    margin-bottom:2px;
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

    background:rgba(255,255,255,.60);

    border:1px solid rgba(255,255,255,.75);

    backdrop-filter:blur(10px);
}

/* BUTTON */
.btn{
    border:none !important;

    border-radius:10px !important;

    padding:8px 12px !important;

    font-size:11px !important;
    font-weight:600 !important;

    cursor:pointer;

    transition:.2s ease;

    text-decoration:none;

    display:inline-flex;
    align-items:center;
    justify-content:center;

    gap:4px;
}

.btn:hover{
    transform:translateY(-1px);
}

.btn-sm{
    padding:7px 10px !important;
    font-size:10px !important;
}

.btn-primary{
    color:#fff !important;

    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #2563eb
        ) !important;

    box-shadow:
        0 8px 20px rgba(37,99,235,.18);
}

.btn-warning{
    background:
        linear-gradient(
            135deg,
            #f59e0b,
            #d97706
        ) !important;

    color:#fff !important;
}

.btn-danger{
    background:
        linear-gradient(
            135deg,
            #ef4444,
            #dc2626
        ) !important;

    color:#fff !important;
}

/* CARD */
.card{
    background:rgba(255,255,255,.38);

    border:1px solid rgba(255,255,255,.85);

    backdrop-filter:blur(18px);

    border-radius:18px;

    padding:12px;

    box-shadow:
        0 10px 30px rgba(15,23,42,.06);
}

/* TABLE */
.table-wrapper{
    overflow:auto;
    border-radius:14px;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:700px;
}

thead th{
    background:rgba(219,234,254,.75);

    padding:10px 10px;

    text-align:left;

    font-size:11px;
    font-weight:700;

    color:#334155;
}

tbody td{
    padding:10px 10px;

    background:rgba(255,255,255,.45);

    border-bottom:1px solid rgba(226,232,240,.7);

    font-size:11px;
    color:#334155;
}

tbody tr{
    transition:.2s ease;
}

tbody tr:hover{
    background:rgba(255,255,255,.70);
}

/* BADGE */
.badge{
    padding:5px 10px;
    border-radius:999px;
    font-size:10px;
    font-weight:700;
}

.badge-success{
    background:#dcfce7;
    color:#15803d;
}

/* MODAL */
.modal-overlay{
    position:fixed;
    inset:0;

    background:rgba(15,23,42,.45);

    display:none;
    align-items:center;
    justify-content:center;

    padding:14px;

    z-index:999;
}

.modal-overlay.active{
    display:flex;
}

.modal{
    width:100%;
    max-width:400px;

    background:rgba(255,255,255,.92);

    border-radius:20px;

    padding:16px;

    backdrop-filter:blur(20px);

    animation:modalShow .2s ease;
}

@keyframes modalShow{

    from{
        opacity:0;
        transform:translateY(15px) scale(.95);
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

    margin-bottom:14px;
}

.modal-header h3{
    font-size:17px;
    font-weight:700;
}

.modal-close{
    border:none;
    background:#f1f5f9;

    width:30px;
    height:30px;

    border-radius:10px;

    cursor:pointer;

    font-size:18px;
}

/* FORM */
.form-group{
    margin-bottom:12px;
}

.form-group label{
    display:block;
    margin-bottom:6px;

    font-size:11px;
    font-weight:600;
    color:#334155;
}

.form-control{
    width:100%;

    border:none;

    background:rgba(255,255,255,.60);

    border:1px solid rgba(255,255,255,.90);

    border-radius:10px;

    padding:10px 12px;

    font-size:11px;

    outline:none;

    backdrop-filter:blur(10px);

    transition:.2s;
}

.form-control:focus{
    border-color:#60a5fa;

    box-shadow:
        0 0 0 3px rgba(59,130,246,.12);
}

/* MOBILE */
@media(max-width:768px){

    .dashboard-wrapper{
        padding:10px;
    }

    .glass-container{
        padding:10px;
    }

    .glass-inner{
        padding:10px;
    }

    .page-header h1{
        font-size:18px;
    }

    table{
        min-width:650px;
    }
}
</style>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
