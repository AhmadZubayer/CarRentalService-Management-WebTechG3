<?php

include_once 'model/UserModel.php';

function handleUserRequest($conn) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

    if ($action === 'getMembers') {
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(array("status" => "error", "message" => "Unauthorized"));
            return;
        }
        $data = getAllMembers($conn);
        header('Content-Type: application/json');
        echo json_encode($data);
        return;
    }

    if ($action === 'deleteMember') {
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(array("status" => "error", "message" => "Unauthorized"));
            return;
        }
        $id = (int) trim($_POST['id']);
        if ($id <= 0) {
            echo json_encode(array("status" => "error", "message" => "Invalid member ID."));
            return;
        }
        deleteMember($conn, $id);
        echo json_encode(array("status" => "success", "message" => "Member deleted successfully."));
        return;
    }

    if ($action === 'getProfile') {
        $user = getUserById($conn, $userId);
        if ($user) {
            unset($user['password_hash']);
            echo json_encode(array("status" => "success", "data" => $user));
        } else {
            echo json_encode(array("status" => "error", "message" => "User not found."));
        }
        return;
    }

    if ($action === 'updateProfile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        $currentPass = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        $newPass = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

        $currentUser = getUserById($conn, $userId);
        if ($currentUser['email'] !== $email) {
            $checkSql = "SELECT id FROM users WHERE email=? LIMIT 1";
            $stmtCheck = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($stmtCheck, "s", $email);
            mysqli_stmt_execute($stmtCheck);
            $checkResult = mysqli_stmt_get_result($stmtCheck);
            if (mysqli_num_rows($checkResult) > 0) {
                echo json_encode(array("status" => "error", "message" => "Email already in use by another account."));
                return;
            }
        }

        if (!empty($newPass)) {
            if (empty($currentPass)) {
                echo json_encode(array("status" => "error", "message" => "Current password is required to set a new password."));
                return;
            }
            if (strlen($newPass) < 8) {
                echo json_encode(array("status" => "error", "message" => "New password must be at least 8 characters."));
                return;
            }
            if (!password_verify($currentPass, $currentUser['password_hash'])) {
                echo json_encode(array("status" => "error", "message" => "Incorrect current password."));
                return;
            }
        }

        if (updateProfile($conn, $userId, $name, $email, $phone, $address)) {
            $_SESSION['name'] = $name;

            if (!empty($newPass)) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                updatePassword($conn, $userId, $newHash);
            }

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed = ['image/jpeg', 'image/png'];
                if (in_array($_FILES['profile_picture']['type'], $allowed) && $_FILES['profile_picture']['size'] <= 2 * 1024 * 1024) {
                    $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    $fileName = 'profile_' . $userId . '_' . time() . '.' . $ext;
                    $uploadDir = 'public/uploads/users/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $uploadPath = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                        updateProfilePicture($conn, $userId, $uploadPath);
                        $_SESSION['profile_picture'] = $uploadPath;
                    }
                }
            }

            echo json_encode(array("status" => "success", "message" => "Profile updated successfully."));
        } else {
            echo json_encode(array("status" => "error", "message" => "Database error."));
        }
        return;
    }

    if ($action === 'updatePassword') {
        $currentPass = trim($_POST['current_password']);
        $newPass = trim($_POST['new_password']);

        if (strlen($newPass) < 8) {
            echo json_encode(array("status" => "error", "message" => "New password must be at least 8 characters."));
            return;
        }

        $sql = "SELECT password_hash FROM users WHERE id=? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($currentPass, $user['password_hash'])) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                if (updatePassword($conn, $userId, $newHash)) {
                    echo json_encode(array("status" => "success", "message" => "Password updated successfully."));
                } else {
                    echo json_encode(array("status" => "error", "message" => "Database error."));
                }
            } else {
                echo json_encode(array("status" => "error", "message" => "Incorrect current password."));
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "User not found."));
        }
        return;
    }

    echo json_encode(array("status" => "error", "message" => "Unknown action."));
}
?>
