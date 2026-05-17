<?php

function getAllOrders($conn, $status = '', $date = '') {
    $where = "WHERE 1=1";
    if (!empty($status)) {
        $where .= " AND o.status='$status'";
    }
    if (!empty($date)) {
        $where .= " AND DATE(o.order_date)='$date'";
    }

    $sql = "SELECT o.id, u.name AS member_name, u.email AS member_email,
                   c.name AS car_name, c.model AS car_model, c.type AS car_type,
                   o.start_date, o.end_date, o.total_cost,
                   o.status, o.payment_method, o.order_date
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN cars  c ON o.car_id  = c.id
            $where
            ORDER BY o.order_date DESC";

    $result = mysqli_query($conn, $sql);
    $data = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

function getUserOrders($conn, $user_id) {
    $sql = "SELECT o.id, c.name AS car_name, c.model AS car_model, c.type AS car_type,
                   o.start_date, o.end_date, o.total_cost,
                   o.status, o.payment_method, o.order_date
            FROM orders o
            JOIN cars  c ON o.car_id  = c.id
            WHERE o.user_id = $user_id
            ORDER BY o.order_date DESC";

    $result = mysqli_query($conn, $sql);
    $data = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

function updateOrderStatus($conn, $id, $status) {
    $allowed = array('pending', 'confirmed', 'cancelled');
    if (!in_array($status, $allowed)) {
        return false;
    }
    $sql = "UPDATE orders SET status='$status' WHERE id=$id";
    return mysqli_query($conn, $sql);
}

function createOrder($conn, $user_id, $car_id, $start_date, $end_date, $total_cost) {
    $sql = "INSERT INTO orders (user_id, car_id, start_date, end_date, total_cost, status) 
            VALUES ($user_id, $car_id, '$start_date', '$end_date', $total_cost, 'pending')";
    if (mysqli_query($conn, $sql)) {
        return mysqli_insert_id($conn);
    }
    return false;
}

function getOrderById($conn, $id) {
    $sql = "SELECT o.*, c.name as car_name, c.model as car_model, c.type as car_type, c.price_per_day, c.image_path 
            FROM orders o 
            JOIN cars c ON o.car_id = c.id 
            WHERE o.id = $id";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

function cancelOrder($conn, $id) {
    $sql = "UPDATE orders SET status='cancelled' WHERE id = $id";
    return mysqli_query($conn, $sql);
}

function confirmPayment($conn, $order_id, $amount, $method, $transaction_id) {
    $sql = "INSERT INTO payments (order_id, amount, payment_method, transaction_id) 
            VALUES ($order_id, $amount, '$method', '$transaction_id')";
    if (mysqli_query($conn, $sql)) {
        
        $updateOrderSql = "UPDATE orders SET status='confirmed', payment_method='$method' WHERE id=$order_id";
        return mysqli_query($conn, $updateOrderSql);
    }
    return false;
}

function getTotalOrders($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getTotalBlogs($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM blogs");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getTotalCars($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM cars");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
?>
