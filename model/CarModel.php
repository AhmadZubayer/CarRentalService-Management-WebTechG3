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
    $sql = "SELECT * FROM cars WHERE id=$id LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function getCarCurrentBooking($conn, $car_id) {
    $today = date('Y-m-d');
    $sql = "SELECT end_date FROM orders 
            WHERE car_id = $car_id AND status = 'confirmed' 
            AND '$today' BETWEEN start_date AND end_date 
            LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function getCarBookedDates($conn, $car_id) {
    $sql = "SELECT start_date, end_date FROM orders 
            WHERE car_id = $car_id AND status = 'confirmed' 
            AND end_date >= CURDATE()";
    $result = mysqli_query($conn, $sql);
    $dates = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $dates[] = $row;
        }
    }
    return $dates;
}

function hasActiveOrders($conn, $car_id) {
    $sql = "SELECT id FROM orders WHERE car_id=$car_id AND status IN ('pending','confirmed') LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return ($result && mysqli_num_rows($result) > 0);
}

function insertCar($conn, $name, $model, $type, $price_per_day, $description, $image_path, $availability_status) {
    $sql = "INSERT INTO cars (name, model, type, price_per_day, description, image_path, availability_status)
            VALUES ('$name', '$model', '$type', '$price_per_day', '$description', '$image_path', '$availability_status')";
    return mysqli_query($conn, $sql);
}

function updateCar($conn, $id, $name, $model, $type, $price_per_day, $description, $image_path, $availability_status) {
    $imageClause = "";
    if (!empty($image_path)) {
        $imageClause = ", image_path='$image_path'";
    }
    $sql = "UPDATE cars SET
                name='$name',
                model='$model',
                type='$type',
                price_per_day='$price_per_day',
                description='$description',
                availability_status='$availability_status'
                $imageClause
            WHERE id=$id";
    return mysqli_query($conn, $sql);
}

function deleteCar($conn, $id) {
    
    $car = getCarById($conn, $id);
    $imagePath = null;
    if ($car) {
        $imagePath = $car['image_path'];
    }

    $sql = "DELETE FROM cars WHERE id=$id";
    $result = mysqli_query($conn, $sql);

    
    if ($result && $imagePath && file_exists($imagePath)) {
        unlink($imagePath);
    }
    return $result;
}
?>
