<?php

function getAllMembers($conn) {
    $sql = "SELECT id, name, email, phone, address, created_at FROM users WHERE role='member' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    $data = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

function deleteMember($conn, $id) {
    $sql = "DELETE FROM users WHERE id=? AND role='member'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

function getTotalMembers($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='member'");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getUserById($conn, $id) {
    $sql = "SELECT id, name, email, role, profile_picture, address, phone, password_hash FROM users WHERE id=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function updateProfile($conn, $id, $name, $email, $phone, $address) {
    $sql = "UPDATE users SET name=?, email=?, phone=?, address=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $phone, $address, $id);
    return mysqli_stmt_execute($stmt);
}

function updateProfilePicture($conn, $id, $imagePath) {
    $sql = "UPDATE users SET profile_picture=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $imagePath, $id);
    return mysqli_stmt_execute($stmt);
}

function updatePassword($conn, $id, $hashedPassword) {
    $sql = "UPDATE users SET password_hash=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $id);
    return mysqli_stmt_execute($stmt);
}
?>
