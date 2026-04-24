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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--background); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* Sidebar Styling */
        .sidebar { width: 260px; background: var(--sidebar); border-right: 1px solid var(--border); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: all 0.3s; }
        .sidebar-header { padding: 30px 24px; display: flex; flex-direction: column; gap: 5px; }
        .logo-text { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 20px; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .logo-text span { color: #e11d48; }
        .logo-sub { font-size: 12px; color: #64748b; font-weight: 500; letter-spacing: 0.5px; }

        .sidebar-nav { flex: 1; padding: 0 12px; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; text-decoration: none; color: #475569; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .sidebar-nav a:hover { background: #f8fafc; color: var(--primary); }
        .sidebar-nav a.active { background: #eff6ff; color: var(--primary); }
        .sidebar-nav a .material-icons-round { font-size: 20px; }

        .sidebar-footer { padding: 24px; border-top: 1px solid var(--border); }
        .sidebar-user { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 12px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #64748b; }
        .user-info { flex: 1; min-width: 0; }
        .user-info .name { font-weight: 600; font-size: 13px; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info .role { font-size: 11px; color: #64748b; }

        /* Main Content Wrapper */
        .wrapper { flex: 1; margin-left: 260px; display: flex; flex-direction: column; width: calc(100% - 260px); }

        /* Top Bar Styling */
        .top-bar { height: 70px; background: #fff; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; position: sticky; top: 0; z-index: 90; }
        .search-box { background: #f1f5f9; border-radius: 12px; display: flex; align-items: center; padding: 8px 16px; width: 400px; gap: 10px; }
        .search-box input { border: none; background: transparent; flex: 1; font-size: 14px; outline: none; }
        .top-bar-right { display: flex; align-items: center; gap: 20px; }
        .user-profile-top { display: flex; align-items: center; gap: 12px; text-align: right; }
        .user-profile-top .user-name { font-weight: 600; font-size: 14px; color: #1e293b; }
        .user-profile-top .user-role { font-size: 12px; color: #64748b; }
        .user-avatar-top { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; }

        /* Content Area */
        .main-content { padding: 40px; max-width: 1400px; margin: 0 auto; width: 100%; }
        .page-header { margin-bottom: 30px; display: flex; flex-direction: column; gap: 4px; }
        .page-header h1 { font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 700; color: #1e293b; }
        .page-header p { color: #64748b; font-size: 14px; }

        /* Cards & Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .stat-card { background: #fff; padding: 24px; border-radius: 20px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 10px; }
        .stat-card .icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 4px; }
        .stat-card.primary .icon-box { background: #eff6ff; color: var(--primary); }
        .stat-card.warning .icon-box { background: #fffbeb; color: var(--warning); }
        .stat-card.danger .icon-box { background: #fef2f2; color: var(--danger); }
        .stat-card.success .icon-box { background: #ecfdf5; color: var(--success); }
        .stat-card .label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .value { font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 700; color: #1e293b; }
        .stat-card .sub-label { font-size: 11px; color: #94a3b8; }

        .card { background: #fff; border-radius: 20px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); padding: 24px; margin-bottom: 24px; }
        .card-title { font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* Table Design */
        .table-wrapper { width: 100%; overflow-x: auto; border-radius: 12px; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { background: #f8fafc; padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        td { padding: 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #fafafa; }
        
        .badge { padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #e0f2fe; color: #0369a1; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 12px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; font-family: 'Inter', sans-serif; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        .btn-outline { border: 1px solid var(--border); background: #fff; color: #475569; }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }

        /* Filter Bar */
        .filter-container { background: #fff; padding: 12px; border-radius: 16px; border: 1px solid var(--border); margin-bottom: 24px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .filter-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; font-weight: 500; }
        .form-select-minimal { border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-size: 13px; outline: none; background: #f8fafc; cursor: pointer; }

        /* Modals */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px); }
        .modal-overlay.active { display: flex; }
        .modal { background: #fff; border-radius: 24px; padding: 32px; width: 90%; max-width: 600px; box-shadow: var(--shadow-lg); animation: slideUp 0.3s ease-out; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .alert { padding: 16px; border-radius: 12px; margin-bottom: 24px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        /* Autocomplete Styles */
        .autocomplete-container { position: relative; width: 100%; flex: 1; display: flex; align-items: center; }
        .autocomplete-suggestions {
            position: absolute; top: 100%; left: 0; right: 0; background: #fff;
            border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-lg);
            z-index: 1000; max-height: 250px; overflow-y: auto; margin-top: 5px; display: none;
        }
        .autocomplete-item {
            padding: 10px 15px; font-size: 13px; color: #475569; cursor: pointer;
            border-bottom: 1px solid #f1f5f9; transition: background 0.2s;
        }
        .autocomplete-item:last-child { border-bottom: none; }
        .autocomplete-item:hover, .autocomplete-item.active { background: #f8fafc; color: var(--primary); font-weight: 500; }
        .autocomplete-item .material-icons-round { font-size: 16px; margin-right: 8px; vertical-align: middle; color: #94a3b8; }
        .search-input-wrapper { display: flex; align-items: center; width: 100%; }
        
    </style>
</head>
<body>
