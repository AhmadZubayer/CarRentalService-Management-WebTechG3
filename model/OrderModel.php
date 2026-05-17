<?php

function getAllOrders($conn, $status = '', $date = '') {
    $where = "WHERE 1=1";
    $params = array();
    $types = "";

    if (!empty($status)) {
        $where .= " AND o.status=?";
        $params[] = $status;
        $types .= "s";
    }
    if (!empty($date)) {
        $where .= " AND DATE(o.order_date)=?";
        $params[] = $date;
        $types .= "s";
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

    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
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
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
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
    $sql = "UPDATE orders SET status=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    return mysqli_stmt_execute($stmt);
}

function createOrder($conn, $user_id, $car_id, $start_date, $end_date, $total_cost) {
    $sql = "INSERT INTO orders (user_id, car_id, start_date, end_date, total_cost, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iissd", $user_id, $car_id, $start_date, $end_date, $total_cost);
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($conn);
    }
    return false;
}

function getOrderById($conn, $id) {
    $sql = "SELECT o.*, c.name as car_name, c.model as car_model, c.type as car_type, c.price_per_day, c.image_path 
            FROM orders o 
            JOIN cars c ON o.car_id = c.id 
            WHERE o.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function cancelOrder($conn, $id) {
    $sql = "UPDATE orders SET status='cancelled' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

function confirmPayment($conn, $order_id, $amount, $method, $transaction_id) {
    $sql = "INSERT INTO payments (order_id, amount, payment_method, transaction_id) 
            VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "idss", $order_id, $amount, $method, $transaction_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $updateOrderSql = "UPDATE orders SET status='confirmed', payment_method=? WHERE id=?";
        $updateStmt = mysqli_prepare($conn, $updateOrderSql);
        mysqli_stmt_bind_param($updateStmt, "si", $method, $order_id);
        return mysqli_stmt_execute($updateStmt);
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
