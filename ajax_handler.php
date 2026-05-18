<?php




session_start();

include 'config/db-config.php';
include 'config/security.php';
include 'controller/CarController.php';
include 'controller/UserController.php';
include 'controller/OrderController.php';
include 'controller/BlogsController.php';


$action = isset($_POST['action']) ? $_POST['action'] : '';
$module = isset($_POST['module']) ? $_POST['module'] : '';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(array("status" => "error", "message" => "Unauthorized"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verify_csrf_token($csrfToken)) {
        http_response_code(403);
        echo json_encode(array("status" => "error", "message" => "CSRF token validation failed."));
        exit();
    }
}

if ($module === 'car') {
    handleCarRequest($conn);
} elseif ($module === 'user') {
    handleUserRequest($conn);
} elseif ($module === 'order') {
    handleOrderRequest($conn);
} elseif ($module === 'blog') {
    handleBlogRequest($conn);
} else {
    echo json_encode(array("status" => "error", "message" => "Unknown module."));
}

mysqli_close($conn);
?>
