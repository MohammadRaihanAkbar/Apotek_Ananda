<?php
/**
 * Template: Sidebar - Apotek Ananda Jadimulya
 * Navigasi premium dengan Material Icons.
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$role = getCurrentRole();
$roleFolder = $role === 'super_admin' ? 'superadmin' : 'admin';
$fullName = getCurrentNamaLengkap() ?? 'User';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>/frontend/<?= $roleFolder ?>/dashboard.php" class="sidebar-brand" aria-label="Apotek Ananda Group">
            <img src="<?= BASE_URL ?>/frontend/assets/images/logo_ananda_group.jpg" alt="Apotek Ananda Group" class="sidebar-logo">
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/frontend/<?= $roleFolder ?>/dashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <span class="material-icons-round">dashboard</span>
            Dashboard
        </a>
        
        <a href="<?= BASE_URL ?>/frontend/<?= $roleFolder ?>/manajemen_stok.php" class="<?= in_array($currentPage, ['manajemen_stok','tambah_faktur','detail_faktur']) ? 'active' : '' ?>">
            <span class="material-icons-round">inventory_2</span>
            Manajemen Stok
        </a>
        
        <?php if (isSuperAdmin()): ?>
        <a href="<?= BASE_URL ?>/frontend/superadmin/pbf.php" class="<?= $currentPage === 'pbf' ? 'active' : '' ?>">
            <span class="material-icons-round">local_shipping</span>
            Manajemen PBF
        </a>
        

        <a href="<?= BASE_URL ?>/frontend/superadmin/laporan_expired.php" class="<?= $currentPage === 'laporan_expired' ? 'active' : '' ?>">
            <span class="material-icons-round">event_busy</span>
            Laporan Kadaluwarsa
        </a>
        
        <a href="<?= BASE_URL ?>/frontend/superadmin/piutang.php" class="<?= $currentPage === 'piutang' ? 'active' : '' ?>">
            <span class="material-icons-round">payments</span>
            Piutang
        </a>
        
        <a href="<?= BASE_URL ?>/frontend/superadmin/kelola_admin.php" class="<?= $currentPage === 'kelola_admin' ? 'active' : '' ?>">
            <span class="material-icons-round">admin_panel_settings</span>
            Kelola Akun
        </a>
        <?php endif; ?>
        
        <a href="<?= BASE_URL ?>/frontend/<?= $roleFolder ?>/log_aktivitas.php" class="<?= $currentPage === 'log_aktivitas' ? 'active' : '' ?>">
            <span class="material-icons-round">history_edu</span>
            Log Aktivitas
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar">
                <span class="material-icons-round">person</span>
            </div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($fullName) ?></div>
                <div class="role"><?= $role === 'super_admin' ? 'Admin' : 'Staff' ?></div>
            </div>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/auth_controller.php?action=logout" style="width:100%;">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center; color: var(--danger);">
                <span class="material-icons-round">logout</span>
                Logout
            </button>
        </form>
    </div>
</aside>

<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="wrapper">
    <header class="top-bar">
        <!-- Hamburger menu for mobile -->
        <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Toggle menu">
            <span class="material-icons-round">menu</span>
        </button>
        <div style="flex:1;"></div>
        
        <div class="top-bar-right">
            <div class="user-profile-top">
                <div class="info">
                    <div class="user-name"><?= htmlspecialchars($fullName) ?></div>
                    <div class="user-role"><?= $role === 'super_admin' ? 'Admin' : 'Staff' ?></div>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=eff6ff&color=2563eb&bold=true" class="user-avatar-top" alt="Avatar">
            </div>
        </div>
    </header>
    <main class="main-content">
