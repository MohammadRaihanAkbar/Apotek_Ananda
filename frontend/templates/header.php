<?php
// Template: Header - Apotek Ananda Jadimulya
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Apotek Ananda' ?> - Sistem Manajemen Stok</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --background: #f1f5f9;
            --surface: #ffffff;
            --sidebar: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --sidebar-width: 240px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--background); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* Sidebar Styling — Compact */
        .sidebar { width: var(--sidebar-width); background: var(--sidebar); border-right: 1px solid var(--border); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: transform 0.3s ease; }
        .sidebar-header { padding: 18px 20px; display: flex; flex-direction: column; gap: 4px; }
        .logo-text { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 19px; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .logo-text span { color: #e11d48; }
        .logo-sub { font-size: 11px; color: #64748b; font-weight: 500; letter-spacing: 0.5px; }

        .sidebar-nav { flex: 1; padding: 0 10px; display: flex; flex-direction: column; gap: 3px; }
        .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 9px 14px; border-radius: 10px; text-decoration: none; color: #475569; font-size: 13px; font-weight: 500; transition: all 0.2s; }
        .sidebar-nav a:hover { background: #f8fafc; color: var(--primary); }
        .sidebar-nav a.active { background: #eff6ff; color: var(--primary); }
        .sidebar-nav a .material-icons-round { font-size: 20px; }

        .sidebar-footer { padding: 18px; border-top: 1px solid var(--border); }
        .sidebar-user { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .user-avatar { width: 36px; height: 36px; border-radius: 10px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #64748b; }
        .user-info { flex: 1; min-width: 0; }
        .user-info .name { font-weight: 600; font-size: 12px; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info .role { font-size: 11px; color: #64748b; }

        /* Main Content Wrapper */
        .wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; width: calc(100% - var(--sidebar-width)); }

        /* Top Bar Styling — Compact */
        .top-bar { height: 54px; background: #fff; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; position: sticky; top: 0; z-index: 90; }
        .search-box { background: #f1f5f9; border-radius: 10px; display: flex; align-items: center; padding: 6px 14px; width: 360px; gap: 8px; }
        .search-box input { border: none; background: transparent; flex: 1; font-size: 13px; outline: none; }
        .top-bar-right { display: flex; align-items: center; gap: 16px; }
        .user-profile-top { display: flex; align-items: center; gap: 10px; text-align: right; }
        .user-profile-top .user-name { font-weight: 600; font-size: 13px; color: #1e293b; }
        .user-profile-top .user-role { font-size: 11px; color: #64748b; }
        .user-avatar-top { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; }

        /* Hamburger button — hidden on desktop */
        .hamburger-btn { display: none; background: none; border: none; cursor: pointer; padding: 4px; color: #475569; border-radius: 8px; transition: background 0.2s; }
        .hamburger-btn:hover { background: #f1f5f9; }
        .hamburger-btn .material-icons-round { font-size: 24px; }

        /* Sidebar overlay — hidden on desktop */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.5); z-index: 99; backdrop-filter: blur(2px); }

        /* Content Area — Compact */
        .main-content { padding: 24px; max-width: 1400px; margin: 0 auto; width: 100%; }
        .page-header { margin-bottom: 18px; display: flex; flex-direction: column; gap: 3px; }
        .page-header h1 { font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 700; color: #1e293b; }
        .page-header p { color: #64748b; font-size: 13px; }

        /* Cards & Stats — Compact */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; padding: 16px; border-radius: 14px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 8px; }
        .stat-card .icon-box { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 2px; }
        .stat-card.primary .icon-box { background: #eff6ff; color: var(--primary); }
        .stat-card.warning .icon-box { background: #fffbeb; color: var(--warning); }
        .stat-card.danger .icon-box { background: #fef2f2; color: var(--danger); }
        .stat-card.success .icon-box { background: #ecfdf5; color: var(--success); }
        .stat-card .label { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .value { font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 700; color: #1e293b; }
        .stat-card .sub-label { font-size: 11px; color: #94a3b8; }

        .card { background: #fff; border-radius: 14px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); padding: 18px; margin-bottom: 18px; }
        .card-title { font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

        /* Table Design — Compact */
        .table-wrapper { width: 100%; overflow-x: auto; border-radius: 10px; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { background: #f8fafc; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 1px solid var(--border); }
        td { padding: 10px 12px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #fafafa; }
        
        .badge { padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #e0f2fe; color: #0369a1; }

        /* Buttons — Compact */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; font-family: 'Inter', sans-serif; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        .btn-outline { border: 1px solid var(--border); background: #fff; color: #475569; }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
        .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

        /* Filter Bar */
        .filter-container { background: #fff; padding: 10px; border-radius: 14px; border: 1px solid var(--border); margin-bottom: 18px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .filter-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b; font-weight: 500; }
        .form-select-minimal { border: 1px solid var(--border); border-radius: 7px; padding: 5px 10px; font-size: 12px; outline: none; background: #f8fafc; cursor: pointer; }

        /* Modals — Compact */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px); }
        .modal-overlay.active { display: flex; }
        .modal { background: #fff; border-radius: 16px; padding: 24px; width: 90%; max-width: 600px; box-shadow: var(--shadow-lg); animation: slideUp 0.3s ease-out; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .alert { padding: 12px; border-radius: 10px; margin-bottom: 18px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }


        /* Form & Utility */
        .filter-bar { background: #fff; padding: 12px; border-radius: 14px; border: 1px solid var(--border); margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .tab-filter { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border); background: #fff; color: #475569; text-decoration: none; font-size: 11px; font-weight: 600; }
        .tab-filter.active, .tab-filter:hover { background: #eff6ff; color: var(--primary); border-color: #bfdbfe; }
        .form-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
        .form-group label { font-size: 12px; font-weight: 600; color: #475569; }
        .form-control { width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 8px 10px; font-size: 13px; font-family: 'Inter', sans-serif; outline: none; background: #fff; color: #1e293b; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); }
        .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .modal-header h3 { font-family: 'Outfit', sans-serif; font-size: 18px; color: #1e293b; }
        .modal-close { border: none; background: #f1f5f9; width: 30px; height: 30px; border-radius: 8px; cursor: pointer; font-size: 20px; line-height: 1; color: #64748b; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .btn-secondary { background: #64748b; color: #fff; }
        .btn-success { background: var(--success); color: #fff; }
        .btn-warning { background: var(--warning); color: #fff; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        /* Autocomplete Styles */
        .autocomplete-container { position: relative; width: 100%; flex: 1; display: flex; align-items: center; }
        .autocomplete-suggestions {
            position: absolute; top: 100%; left: 0; right: 0; background: #fff;
            border: 1px solid var(--border); border-radius: 10px; box-shadow: var(--shadow-lg);
            z-index: 1000; max-height: 220px; overflow-y: auto; margin-top: 4px; display: none;
        }
        .autocomplete-item {
            padding: 8px 12px; font-size: 12px; color: #475569; cursor: pointer;
            border-bottom: 1px solid #f1f5f9; transition: background 0.2s;
        }
        .autocomplete-item:last-child { border-bottom: none; }
        .autocomplete-item:hover, .autocomplete-item.active { background: #f8fafc; color: var(--primary); font-weight: 500; }
        .autocomplete-item .material-icons-round { font-size: 14px; margin-right: 6px; vertical-align: middle; color: #94a3b8; }
        .search-input-wrapper { display: flex; align-items: center; width: 100%; }

        /* ===== RESPONSIVE — Tablet (≤ 1024px) ===== */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 200;
                box-shadow: var(--shadow-lg);
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.active { display: block; }
            .wrapper { margin-left: 0; width: 100%; }
            .main-content { padding: 18px; }
            .hamburger-btn { display: flex; align-items: center; justify-content: center; }
            .top-bar { padding: 0 16px; }
        }

        /* ===== RESPONSIVE — Mobile (≤ 768px) ===== */
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; gap: 12px; }
            table { font-size: 12px; }
            td, th { padding: 8px 6px; }
            .filter-bar { flex-direction: column; align-items: stretch; gap: 8px; }
            .filter-bar form { flex-direction: column; }
            .modal { width: 95%; padding: 18px; border-radius: 14px; }
            .page-header h1 { font-size: 20px; }
            .page-header { margin-bottom: 14px; }
            .card { padding: 14px; margin-bottom: 14px; }
            .stat-card { padding: 14px; }
            .user-profile-top .info { display: none; }
            .top-bar { height: 48px; }
        }

        /* ===== RESPONSIVE — Small Mobile (≤ 480px) ===== */
        @media (max-width: 480px) {
            .main-content { padding: 12px; }
            .btn { padding: 6px 10px; font-size: 11px; }
            .btn-sm { padding: 4px 8px; font-size: 10px; }
        }
        
    </style>
</head>
<body>
