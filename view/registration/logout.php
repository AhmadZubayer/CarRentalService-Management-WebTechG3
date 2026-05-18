<?php
include 'config/db-config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// if (isset($_SESSION['user_id'])) {
//     $userId = $_SESSION['user_id'];
//     $sql = "UPDATE users SET remember_token=NULL WHERE id=$userId";
//     $stmt = mysqli_prepare($conn, $sql);
//     mysqli_stmt_bind_param($stmt, "i", $userId);
//     mysqli_stmt_execute($stmt);
//     mysqli_close($conn);
// }

session_unset();
session_destroy();

if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

header("Location: sign-in.php");
exit();
?>
