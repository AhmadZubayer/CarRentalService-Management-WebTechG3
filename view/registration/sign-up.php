<?php
session_start();
include '../../config/db-config.php';
include '../../config/security.php';


if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../member/home.php");
    }
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "CSRF token validation failed.";
    } else {
        $name     = trim($_POST['reg_name']);
        $email    = trim($_POST['reg_email']);
        $password = trim($_POST['reg_password']);
        $confirmPassword = trim($_POST['reg_confirm_password']);
        $phone    = trim($_POST['reg_phone']);
        $address  = trim($_POST['reg_address']);
        $role     = trim($_POST['reg_role']);

        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = "Name, Email, and Password are required.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            $checkSql = "SELECT id FROM users WHERE email=? LIMIT 1";
            $stmtCheck = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($stmtCheck, "s", $email);
            mysqli_stmt_execute($stmtCheck);
            $checkResult = mysqli_stmt_get_result($stmtCheck);

            if (mysqli_num_rows($checkResult) > 0) {
                $error = "Email is already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $roleSafe = ($role === 'admin') ? 'admin' : 'member';

                $insertSql = "INSERT INTO users (name, email, password_hash, role, phone, address) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                $stmtInsert = mysqli_prepare($conn, $insertSql);
                mysqli_stmt_bind_param($stmtInsert, "ssssss", $name, $email, $hash, $roleSafe, $phone, $address);
                
                if (mysqli_stmt_execute($stmtInsert)) {
                    header("Location: sign-in.php?success=1");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
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
    <title>Sign Up - CarRental</title>
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
            <h1>Create Account</h1>
            <p>Join as a Member or Admin</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-bar alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="sign-up.php" id="registerForm" novalidate>
            <?= get_csrf_input() ?>
            <div class="form-field">
                <label for="reg_name">Full Name *</label>
                <input type="text" id="reg_name" name="reg_name" placeholder="Enter Full Name Here"
                       value="<?= isset($_POST['reg_name']) ? htmlspecialchars($_POST['reg_name']) : '' ?>">
                <span class="field-err" id="regNameErr"></span>
            </div>
            <div class="form-field">
                <label for="reg_email">Email Address *</label>
                <input type="email" id="reg_email" name="reg_email" placeholder="Enter Email Here"
                       value="<?= isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : '' ?>">
                <span class="field-err" id="regEmailErr"></span>
            </div>
            <div class="form-field">
                <label for="reg_password">Password (Min 8 chars) *</label>
                <input type="password" id="reg_password" name="reg_password" placeholder="Password">
                <span class="field-err" id="regPassErr"></span>
            </div>
            <div class="form-field">
                <label for="reg_confirm_password">Confirm Password *</label>
                <input type="password" id="reg_confirm_password" name="reg_confirm_password" placeholder="Confirm Password">
                <span class="field-err" id="regConfirmPassErr"></span>
            </div>
            <div style="display:flex; gap:10px;">
                <div class="form-field" style="flex:1;">
                    <label for="reg_phone">Phone</label>
                    <input type="text" id="reg_phone" name="reg_phone" placeholder="Phone Number"
                           value="<?= isset($_POST['reg_phone']) ? htmlspecialchars($_POST['reg_phone']) : '' ?>">
                </div>
                <div class="form-field" style="flex:1;">
                    <label for="reg_role">Role *</label>
                    <select id="reg_role" name="reg_role">
                        <option value="member" <?= isset($_POST['reg_role']) && $_POST['reg_role']==='member' ? 'selected' : '' ?>>Member</option>
                        <option value="admin" <?= isset($_POST['reg_role']) && $_POST['reg_role']==='admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
            </div>
            <div class="form-field">
                <label for="reg_address">Address</label>
                <input type="text" id="reg_address" name="reg_address" placeholder="Full Address"
                       value="<?= isset($_POST['reg_address']) ? htmlspecialchars($_POST['reg_address']) : '' ?>">
            </div>

            <button type="submit" class="btn-1" id="regBtn">Create Account</button>
        </form>
        <div class="toggle-link">
            Already have an account? <a href="sign-in.php">Sign In</a>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    var valid = true;
    document.getElementById('regNameErr').textContent = '';
    document.getElementById('regEmailErr').textContent = '';
    document.getElementById('regPassErr').textContent = '';
    document.getElementById('regConfirmPassErr').textContent = '';

    if (!document.getElementById('reg_name').value.trim()) {
        document.getElementById('regNameErr').textContent = 'Name is required.';
        valid = false;
    }
    if (!document.getElementById('reg_email').value.trim()) {
        document.getElementById('regEmailErr').textContent = 'Email is required.';
        valid = false;
    }
    var pwd = document.getElementById('reg_password').value.trim();
    var cpwd = document.getElementById('reg_confirm_password').value.trim();

    if (!pwd) {
        document.getElementById('regPassErr').textContent = 'Password is required.';
        valid = false;
    } else if (pwd.length < 8) {
        document.getElementById('regPassErr').textContent = 'Password must be at least 8 characters.';
        valid = false;
    }

    if (!cpwd) {
        document.getElementById('regConfirmPassErr').textContent = 'Please confirm your password.';
        valid = false;
    } else if (pwd !== cpwd) {
        document.getElementById('regConfirmPassErr').textContent = 'Passwords do not match.';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>
