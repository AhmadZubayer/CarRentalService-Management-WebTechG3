<?php

function getAllCars($conn) {
    $sql = "SELECT * FROM cars ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    $data = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

function getCarById($conn, $id) {
    $sql = "SELECT * FROM cars WHERE id=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function getCarCurrentBooking($conn, $car_id) {
    $today = date('Y-m-d');
    $sql = "SELECT end_date FROM orders 
            WHERE car_id = ? AND status = 'confirmed' 
            AND ? BETWEEN start_date AND end_date 
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $car_id, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function getCarBookedDates($conn, $car_id) {
    $sql = "SELECT start_date, end_date FROM orders 
            WHERE car_id = ? AND status = 'confirmed' 
            AND end_date >= CURDATE()";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $car_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $dates = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $dates[] = $row;
        }
    }
    return $dates;
}

function hasActiveOrders($conn, $car_id) {
    $sql = "SELECT id FROM orders WHERE car_id=? AND status IN ('pending','confirmed') LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $car_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return ($result && mysqli_num_rows($result) > 0);
}

function insertCar($conn, $name, $model, $type, $price_per_day, $description, $image_path, $availability_status) {
    $sql = "INSERT INTO cars (name, model, type, price_per_day, description, image_path, availability_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssdsss", $name, $model, $type, $price_per_day, $description, $image_path, $availability_status);
    return mysqli_stmt_execute($stmt);
}

function updateCar($conn, $id, $name, $model, $type, $price_per_day, $description, $image_path, $availability_status) {
    if (!empty($image_path)) {
        $sql = "UPDATE cars SET name=?, model=?, type=?, price_per_day=?, description=?, availability_status=?, image_path=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssdsssi", $name, $model, $type, $price_per_day, $description, $availability_status, $image_path, $id);
    } else {
        $sql = "UPDATE cars SET name=?, model=?, type=?, price_per_day=?, description=?, availability_status=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssdssi", $name, $model, $type, $price_per_day, $description, $availability_status, $id);
    }
    return mysqli_stmt_execute($stmt);
}

function deleteCar($conn, $id) {
    $car = getCarById($conn, $id);
    $imagePath = null;
    if ($car) {
        $imagePath = $car['image_path'];
    }

    $sql = "DELETE FROM cars WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $result = mysqli_stmt_execute($stmt);

    if ($result && $imagePath && file_exists($imagePath)) {
        unlink($imagePath);
    }
    return $result;
}
?>
