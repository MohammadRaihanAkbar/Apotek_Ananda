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
                                <button class="btn btn-warning btn-sm" onclick='editAdmin(<?= json_encode($admin) ?>)'>✏️ Edit</button>
                                <form method="POST" action="<?= BASE_URL ?>/backend/controllers/admin_controller.php?action=delete" style="display:inline" onsubmit="return confirm('Hapus akun staff ini? Semua data terkait akan ikut terhapus.')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id_user" value="<?= $admin['id_user'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
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
            <button type="submit" class="btn btn-primary">💾 Simpan Staff</button>
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
            <button type="submit" class="btn btn-primary">💾 Update Staff</button>
        </form>
    </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('active')}
function closeModal(id){document.getElementById(id).classList.remove('active')}
function editAdmin(d){document.getElementById('adminEditId').value=d.id_user;document.getElementById('adminEditNama').value=d.nama_lengkap;document.getElementById('adminEditUsername').value=d.username;openModal('modalEditAdmin')}
document.querySelectorAll('.modal-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)closeModal(el.id)})});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
