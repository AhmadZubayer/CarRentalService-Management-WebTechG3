<?php
session_start();
include '../../config/db-config.php';
include '../../config/security.php';


if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    list($userId, $token) = explode(':', $_COOKIE['remember_me'], 2);
    $userId = (int)$userId;
    
    $sql = "SELECT * FROM users WHERE id=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if ($user['remember_token'] && password_verify($token, $user['remember_token'])) {
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['role']            = $user['role'];
            $_SESSION['name']            = $user['name'];
            $_SESSION['email']           = $user['email'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            $_SESSION['start_time']      = time();
        }
    }
}


if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../member/home.php");
    }
    exit();
}

$error = "";
$success = isset($_GET['success']) ? "Registration successful! Please sign in." : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "CSRF token validation failed.";
    } else {
        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $error = "Both fields are required.";
        } else {
            $sql = "SELECT * FROM users WHERE email=? LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id']         = $user['id'];
                    $_SESSION['role']            = $user['role'];
                    $_SESSION['name']            = $user['name'];
                    $_SESSION['email']           = $user['email'];
                    $_SESSION['profile_picture'] = $user['profile_picture'];
                    $_SESSION['start_time']      = time();

                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $tokenHash = password_hash($token, PASSWORD_DEFAULT);
                        $updateToken = "UPDATE users SET remember_token=? WHERE id=?";
                        $stmtUpdate = mysqli_prepare($conn, $updateToken);
                        mysqli_stmt_bind_param($stmtUpdate, "si", $tokenHash, $user['id']);
                        mysqli_stmt_execute($stmtUpdate);
                        setcookie('remember_me', $user['id'] . ':' . $token, time() + (86400 * 30), "/");
                    }

                    if ($user['role'] === 'admin') {
                        header("Location: ../admin/dashboard.php");
                    } else {
                        header("Location: ../member/home.php");
                    }
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - CarRental</title>
    <link rel="stylesheet" href="../admin/admin-ui.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }

        .auth-wrap {
            width: 100%;
            max-width: 420px;
            padding: 16px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .auth-header .brand-bar {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #1f2937;
            padding: 10px 20px;
            margin-bottom: 16px;
        }
        .auth-header .brand-bar span {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .auth-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .auth-header p {
            font-size: 13px;
            color: #6b7280;
        }

        .alert-bar {
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-error {
            background: #fef2f2;
            border-left: 3px solid #ef4444;
            color: #991b1b;
        }
        .alert-success {
            background: #f0fdf4;
            border-left: 3px solid #22c55e;
            color: #166534;
        }

        .form-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
        .form-field label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #374151;
        }
        .form-field input, .form-field select {
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            outline: none;
            width: 100%;
            transition: border-color 0.15s;
        }
        .form-field input:focus, .form-field select:focus { border-color: #3949ab; }
        .field-err { font-size: 11.5px; color: #ef4444; }

        .btn-1 { width: 100%; padding: 11px; font-size: 13px; margin-top: 4px; }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        .checkbox-row input { width: auto; }
        .checkbox-row label {
            font-size: 12px;
            color: #4b5563;
            text-transform: none;
            letter-spacing: normal;
            font-weight: normal;
        }

        .toggle-link {
            text-align: center;
            margin-top: 16px;
            font-size: 12.5px;
            color: #6b7280;
        }
        .toggle-link a {
            color: #3949ab;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .toggle-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="container-card">
        <div class="auth-header">
            <div class="brand-bar">
                <img src="../../public/logo.png" alt="Logo" style="height: 24px; width: auto; object-fit: contain;">
                <span>CarRental</span>
            </div>
            <h1>Sign In</h1>
            <p>Enter your credentials to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-bar alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert-bar alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="sign-in.php" id="loginForm" novalidate>
            <?= get_csrf_input() ?>
            <div class="form-field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter Email Here"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                <span class="field-err" id="emailErr"></span>
            </div>
            <div class="form-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password">
                <span class="field-err" id="passErr"></span>
            </div>
            <div class="checkbox-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember Me</label>
            </div>
            <button type="submit" class="btn-1" id="loginBtn">Sign In</button>
        </form>
        <div class="toggle-link">
            Don't have an account? <a href="sign-up.php">Register here</a>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    var valid = true;
    document.getElementById('emailErr').textContent = '';
    document.getElementById('passErr').textContent  = '';

    if (!document.getElementById('email').value.trim()) {
        document.getElementById('emailErr').textContent = 'Email is required.';
        valid = false;
    }
    if (!document.getElementById('password').value.trim()) {
        document.getElementById('passErr').textContent = 'Password is required.';
        valid = false;
    }
    if (!valid) e.preventDefault();
});
</script>
</body>
</html>
