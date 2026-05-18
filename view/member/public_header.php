<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../config/security.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? htmlspecialchars($_SESSION['name']) : '';
$userInitial = $isLoggedIn ? strtoupper(substr($userName, 0, 1)) : '';
$userPhoto = $isLoggedIn ? ($_SESSION['profile_picture'] ?? null) : null;
$userRole = $isLoggedIn ? $_SESSION['role'] : 'guest';
$currentPage = isset($currentPage) ? $currentPage : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - CarRental' : 'CarRental'; ?></title>
    <script>
        window.csrfToken = "<?= generate_csrf_token() ?>";
    </script>
    <link rel="stylesheet" href="../public/home-ui.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        .drawer { display: none !important; }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-left: 40px;
            flex-grow: 1;
        }
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .navbar-avatar {
            width: 28px; height: 28px;
            background: #1f2937;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #ffffff;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <a href="home.php" class="navbar-brand">
        <img src="../../public/logo.png" alt="Logo" style="height: 32px; width: auto; max-width: 100px; object-fit: contain;">
    </a>

    <div class="navbar-links">
        <a href="home.php" class="btn-2 <?php echo ($currentPage === 'home') ? 'active' : ''; ?>">Home</a>
        <a href="categories.php" class="btn-2 <?php echo ($currentPage === 'categories') ? 'active' : ''; ?>">Categories</a>
        <?php if ($isLoggedIn) { ?>
            <a href="order_history.php" class="btn-2 <?php echo ($currentPage === 'orders') ? 'active' : ''; ?>">Order History</a>
        <?php } ?>
        <a href="blog.php" class="btn-2 <?php echo ($currentPage === 'blog') ? 'active' : ''; ?>">Blog</a>
        <?php if ($isLoggedIn && $userRole === 'admin') { ?>
            <a href="../admin/dashboard.php" class="btn-2">Admin Panel</a>
        <?php } ?>
    </div>

    <div class="navbar-user">
        <?php if ($isLoggedIn) { ?>
            <a href="profile.php" style="text-decoration:none; color:#ffffff; display:flex; align-items:center; gap:8px; font-size:13px; font-weight:500;">
                <img id="navAvatar" src="../../public/logo.png" alt="Avatar" class="navbar-avatar" style="object-fit: cover; display: none;">
                <div id="navInitial" class="navbar-avatar"><?php echo $userInitial; ?></div>
                <?php echo $userName; ?>
            </a>
            <a href="../registration/logout.php" class="btn-2" style="color:#ef4444; opacity: 1;">Logout</a>
        <?php } else { ?>
            <a href="../registration/sign-in.php" class="btn-2">Login</a>
            <a href="../registration/sign-up.php" class="btn-1" style="font-size: 12px; padding: 6px 15px;">Sign Up</a>
        <?php } ?>
    </div>
</nav>

<script>
<?php if ($isLoggedIn) { ?>
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
    xhr.send('module=user&action=getProfile&csrf_token=' + encodeURIComponent(window.csrfToken));
})();
<?php } ?>
</script>

    <div class="body-row">
        <main class="page-wrapper">
