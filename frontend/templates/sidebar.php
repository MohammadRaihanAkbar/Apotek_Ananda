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
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-text">APOTEK <span>ANANDA</span></div>
        <div class="logo-sub">Group Jadimulya</div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/frontend/<?= $roleFolder ?>/dashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <span class="material-icons-round">dashboard</span>
            Dashboard
        </a>
        
        <a href="<?= BASE_URL ?>/frontend/<?= $roleFolder ?>/manajemen_stok.php" class="<?= $currentPage === 'manajemen_stok' ? 'active' : '' ?>">
            <span class="material-icons-round">inventory_2</span>
            Manajemen Stok
        </a>
        
        <?php if (isSuperAdmin()): ?>
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
                <div class="role"><?= $role === 'super_admin' ? 'Super Admin' : 'Admin' ?></div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center; color: var(--danger);">
            <span class="material-icons-round">logout</span>
            Logout
        </a>
    </div>
</aside>

<div class="wrapper">
    <header class="top-bar">
        <div style="flex:1;"></div>
        
        <div class="top-bar-right">
            <div class="user-profile-top">
                <div class="info">
                    <div class="user-name"><?= htmlspecialchars($fullName) ?></div>
                    <div class="user-role"><?= $role === 'super_admin' ? 'Super Admin' : 'Admin' ?></div>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=eff6ff&color=2563eb&bold=true" class="user-avatar-top" alt="Avatar">
            </div>
        </div>
    </header>
    <main class="main-content">
