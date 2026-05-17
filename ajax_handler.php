<?php




session_start();

include 'config/db-config.php';
include 'config/security.php';
include 'controller/CarController.php';
include 'controller/UserController.php';
include 'controller/OrderController.php';


$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action === 'get_car_details') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id > 0) {
        $car = getCarById($conn, $id);
        if ($car) {
            echo json_encode(array("success" => true, "car" => $car));
        } else {
            echo json_encode(array("success" => false, "message" => "Car not found."));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Invalid ID."));
    }
    exit();
}


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

$module = isset($_POST['module']) ? $_POST['module'] : '';

if ($module === 'car') {
    handleCarRequest($conn);
} elseif ($module === 'user') {
    handleUserRequest($conn);
} elseif ($module === 'order') {
    handleOrderRequest($conn);
} else {
    echo json_encode(array("status" => "error", "message" => "Unknown module."));
}

mysqli_close($conn);
?>
