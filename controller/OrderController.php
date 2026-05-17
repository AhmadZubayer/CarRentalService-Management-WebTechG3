<?php

include_once 'model/OrderModel.php';

function handleOrderRequest($conn) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'getOrders') {
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';
        $date   = isset($_POST['date'])   ? trim($_POST['date'])   : '';

        $allowed_status = array('', 'pending', 'confirmed', 'cancelled');
        if (!in_array($status, $allowed_status)) {
            $status = '';
        }

        $data = getAllOrders($conn, $status, $date);
        header('Content-Type: application/json');
        echo json_encode($data);
        return;
    }

    if ($action === 'getUserOrders') {
        $user_id = $_SESSION['user_id'];
        $data = getUserOrders($conn, $user_id);
        header('Content-Type: application/json');
        echo json_encode($data);
        return;
    }

    if ($action === 'updateStatus') {
        $id     = (int) trim($_POST['id']);
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';

        if ($id <= 0) {
            echo json_encode(array("status" => "error", "message" => "Invalid order ID."));
            return;
        }
        $result = updateOrderStatus($conn, $id, $status);
        if ($result) {
            echo json_encode(array("status" => "success", "message" => "Order status updated."));
        } else {
            echo json_encode(array("status" => "error", "message" => "Invalid status or database error."));
        }
        return;
    }

    if ($action === 'calculateCost') {
        $car_id = (int)$_POST['car_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $car = getCarById($conn, $car_id);
        if (!$car) {
            echo json_encode(array("status" => "error", "message" => "Car not found"));
            return;
        }

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $diff = $start->diff($end);
        $days = $diff->days + 1;

        if ($days <= 0) {
            echo json_encode(array("status" => "error", "message" => "Invalid dates"));
            return;
        }

        $total_cost = $days * $car['price_per_day'];
        echo json_encode(array("status" => "success", "total_cost" => $total_cost, "days" => $days));
        return;
    }

    if ($action === 'createOrder') {
        $user_id = $_SESSION['user_id'];
        $car_id = (int)$_POST['car_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $total_cost = (float)$_POST['total_cost'];

        $order_id = createOrder($conn, $user_id, $car_id, $start_date, $end_date, $total_cost);
        if ($order_id) {
            echo json_encode(array("status" => "success", "order_id" => $order_id));
        } else {
            echo json_encode(array("status" => "error", "message" => "Failed to create order"));
        }
        return;
    }

    if ($action === 'cancelOrder') {
        $order_id = (int)$_POST['order_id'];
        $order = getOrderById($conn, $order_id);
        if ($order && $order['user_id'] == $_SESSION['user_id']) {
            if (cancelOrder($conn, $order_id)) {
                echo json_encode(array("status" => "success", "message" => "Order cancelled"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Failed to cancel order"));
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "Unauthorized"));
        }
        return;
    }

    if ($action === 'confirmPayment') {
        $order_id = (int)$_POST['order_id'];
        $method = $_POST['payment_method'];
        $amount = (float)$_POST['amount'];
        $transaction_id = 'TXN-' . strtoupper(uniqid());

        $order = getOrderById($conn, $order_id);
        if ($order && $order['user_id'] == $_SESSION['user_id']) {
            if (confirmPayment($conn, $order_id, $amount, $method, $transaction_id)) {
                echo json_encode(array("status" => "success", "message" => "Payment confirmed"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Payment failed"));
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "Unauthorized"));
        }
        return;
    }

    echo json_encode(array("status" => "error", "message" => "Unknown action."));
}
?>
