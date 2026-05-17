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
    
    $sql = "DELETE FROM users WHERE id=$id AND role='member'";
    return mysqli_query($conn, $sql);
}

function getTotalMembers($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='member'");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getUserById($conn, $id) {
    $sql = "SELECT id, name, email, role, profile_picture, address, phone FROM users WHERE id=$id LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function updateProfile($conn, $id, $name, $email, $phone, $address) {
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $phone = mysqli_real_escape_string($conn, $phone);
    $address = mysqli_real_escape_string($conn, $address);

    $sql = "UPDATE users SET name='$name', email='$email', phone='$phone', address='$address' WHERE id=$id";
    return mysqli_query($conn, $sql);
}

function updateProfilePicture($conn, $id, $imagePath) {
    $imagePath = mysqli_real_escape_string($conn, $imagePath);
    $sql = "UPDATE users SET profile_picture='$imagePath' WHERE id=$id";
    return mysqli_query($conn, $sql);
}

function updatePassword($conn, $id, $hashedPassword) {
    $hashedPassword = mysqli_real_escape_string($conn, $hashedPassword);
    $sql = "UPDATE users SET password_hash='$hashedPassword' WHERE id=$id";
    return mysqli_query($conn, $sql);
}
?>
