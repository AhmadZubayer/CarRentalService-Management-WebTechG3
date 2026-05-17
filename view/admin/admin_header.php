<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$timeout = 3600;

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../registration/sign-in.php");
    exit();
}

if (time() - $_SESSION['start_time'] > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../registration/sign-in.php");
    exit();
}

$_SESSION['start_time'] = time();

$adminName    = htmlspecialchars($_SESSION['name']);
$adminInitial = strtoupper(substr($_SESSION['name'], 0, 1));
$adminPhoto   = $_SESSION['profile_picture'] ?? null;
$currentPage  = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - CarRental Admin' : 'CarRental Admin' ?></title>
    <meta name="description" content="CarRental Admin Panel - manage cars, members and orders.">

    <link rel="stylesheet" href="admin-ui.css">
    <style>

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: 'Inter', sans-serif; }


        .app-shell {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }


        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            height: 56px;
            position: sticky; top: 0;
            z-index: 200;
            flex-shrink: 0;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .navbar-brand .brand-mark {
            width: 32px; height: 32px;
            background: #1f2937;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .navbar-brand span {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .navbar-admin {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #374151;
        }
        .navbar-avatar {
            width: 28px; height: 28px;
            background: #3949ab;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #ffffff;
        }


        .body-row {
            display: flex;
            flex: 1;
        }


        .drawer {
            flex-shrink: 0;
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            border-right: 1px solid #e5e7eb;
        }


        .page-wrapper {
            flex: 1;
            padding: 28px 32px;
            min-width: 0;
        }


        .page-heading {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            letter-spacing: -0.01em;
        }


        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 22px 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .stat-card .stat-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
        }
        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            line-height: 1;
        }
        .stat-card .stat-icon {
            font-size: 20px;
            margin-bottom: 4px;
        }


        .card-heading {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }


        .tbl-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        thead th {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 8px 14px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            white-space: nowrap;
            background: #f9fafb;
        }
        tbody tr { border-bottom: 1px solid #e5e7eb; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f9fafb; }
        tbody td { padding: 11px 14px; color: #374151; }
        .loading-td { text-align: center; color: #6b7280; }


        .car-thumb { width: 52px; height: 38px; object-fit: cover; border: 1px solid #e5e7eb; }


        .badge {
            display: inline-block;
            padding: 2px 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
            letter-spacing: 0.02em;
        }
        .badge-pending    { background: none; color: #f59e0b; }
        .badge-confirmed  { background: none; color: #10b981; }
        .badge-cancelled  { background: none; color: #ef4444; }
        .badge-available  { background: none; color: #10b981; }
        .badge-unavailable{ background: none; color: #ef4444; }


        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 20px;
        }
        .form-grid .span-2 { grid-column: 1 / -1; }
        .form-field { display: flex; flex-direction: column; gap: 5px; }
        .form-field label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .form-field input,
        .form-field select,
        .form-field textarea {
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            padding: 9px 11px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            outline: none;
            transition: border-color 0.15s;
            width: 100%;
        }
        .form-field input:focus,
        .form-field select:focus,
        .form-field textarea:focus { border-color: #3949ab; }
        .form-field textarea { resize: vertical; min-height: 80px; }
        .field-err { font-size: 11.5px; color: #ef4444; }



        .btn-row { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
        .btn-1 { padding: 9px 20px; font-size: 12px; }


        .btn-cancel {
            background: #e5e7eb;
            color: #374151;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            padding: 9px 20px;
            cursor: pointer;
        }
        .btn-cancel:hover { background: #d1d5db; }


        .btn-danger {
            background: #ef4444;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            padding: 9px 20px;
            cursor: pointer;
        }
        .btn-danger:hover { background: #dc2626; }


        .tbl-btn {
            background: none;
            border: none;
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .tbl-btn-edit   { color: #3949ab; }
        .tbl-btn-edit:hover   { text-decoration: underline; }
        .tbl-btn-delete { color: #ef4444; }
        .tbl-btn-delete:hover { text-decoration: underline; }


        .filter-bar {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filter-bar .form-field { min-width: 160px; }


        .modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #ffffff;
            padding: 32px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.18);
            text-align: center;
        }
        .modal-box h3 { font-size: 16px; font-weight: 700; color: #111827; }
        .modal-box p  { font-size: 13.5px; color: #6b7280; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }


        .status-sel {
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 500;
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            cursor: pointer;
        }


        .toast-container {
            position: fixed; bottom: 24px; right: 24px;
            z-index: 9999; display: flex; flex-direction: column; gap: 8px;
        }
        .toast {
            background: #ffffff;
            color: #374151;
            padding: 10px 18px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            min-width: 220px;
            animation: slideUp 0.22s ease;
        }
        .toast.success { border-left: 3px solid #10b981; }
        .toast.error   { border-left: 3px solid #ef4444; }
        @keyframes slideUp {
            from { opacity:0; transform: translateY(12px); }
            to   { opacity:1; transform: translateY(0); }
        }


        .view-all-link {
            font-size: 12px;
            font-weight: 600;
            color: #3949ab;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .view-all-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="app-shell">


    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <img src="../../public/logo.png" alt="Logo" style="height: 32px; width: auto; max-width: 100px; object-fit: contain;">
            <span style="color: #ffffff;">ADMIN</span>
        </a>
        <div class="navbar-admin">
            <a href="profile.php" style="text-decoration:none; color:#ffffff; display:flex; align-items:center; gap:8px;">
                <img id="navAvatar" src="../../public/logo.png" alt="Avatar" class="navbar-avatar" style="object-fit: cover; display: none;">
                <div id="navInitial" class="navbar-avatar"><?= $adminInitial ?></div>
                <?= $adminName ?>
            </a>
        </div>
    </nav>

    <script>
    (function() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../ajax_handler.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.status === 'success' && res.data.profile_picture) {
                        var navImg = document.getElementById('navAvatar');
                        var navInit = document.getElementById('navInitial');
                        if (navImg && navInit) {
                            navImg.src = '../../' + res.data.profile_picture;
                            navImg.style.display = 'flex';
                            navInit.style.display = 'none';
                        }
                    }
                } catch(e) {}
            }
        };
        xhr.send('module=user&action=getProfile');
    })();
    </script>


    <div class="body-row">


        <aside class="drawer">
            <div class="drawer-section-label">Main Menu</div>
            <a href="dashboard.php" class="drawer-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                Dashboard
            </a>
            <a href="add_car.php" class="drawer-item <?= $currentPage === 'add_car' ? 'active' : '' ?>">
                Add Car
            </a>
            <a href="manage_cars.php" class="drawer-item <?= $currentPage === 'manage_cars' ? 'active' : '' ?>">
                Manage All Cars
            </a>
            <a href="members.php" class="drawer-item <?= $currentPage === 'members' ? 'active' : '' ?>">
                Members
            </a>
            <a href="orders.php" class="drawer-item <?= $currentPage === 'orders' ? 'active' : '' ?>">
                Rent Orders
            </a>

            <div class="drawer-section-label" style="margin-top:auto"></div>
            <a href="../registration/logout.php" class="drawer-item" style="color:#dc2626; margin-top: 16px;">
                Logout
            </a>
        </aside>


        <main class="page-wrapper">
            <div class="page-heading"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?></div>
