<?php

include_once __DIR__ . '/../model/CarModel.php';

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function handleCarRequest($conn) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    
    if ($action === 'get') {
        $data = getAllCars($conn);
        header('Content-Type: application/json');
        echo json_encode($data);
        return;
    }

    
    if ($action === 'getOne') {
        $id = (int) $_POST['id'];
        $car = getCarById($conn, $id);
        header('Content-Type: application/json');
        echo json_encode($car);
        return;
    }

    
    if ($action === 'delete') {
        $id = (int) test_input($_POST['id']);
        if (hasActiveOrders($conn, $id)) {
            echo json_encode(array("status" => "error", "message" => "Cannot delete: car has pending or confirmed orders."));
            return;
        }
        deleteCar($conn, $id);
        echo json_encode(array("status" => "success", "message" => "Car deleted successfully."));
        return;
    }

    
    if ($action === 'add' || $action === 'update') {

        
        if (empty($_POST['name'])) {
            echo json_encode(array("status" => "error", "message" => "Car name is required."));
            return;
        } else {
            $name = test_input($_POST['name']);
        }

        
        if (empty($_POST['model'])) {
            echo json_encode(array("status" => "error", "message" => "Car model is required."));
            return;
        } else {
            $model = test_input($_POST['model']);
        }

        
        $allowed_types = array('Private Car','Sports Car', 'Microbus', 'Pick-up', 'Other');
        if (empty($_POST['type']) || !in_array($_POST['type'], $allowed_types)) {
            echo json_encode(array("status" => "error", "message" => "Please select a valid car type."));
            return;
        } else {
            $type = test_input($_POST['type']);
        }

        
        if (empty($_POST['price_per_day']) || !is_numeric($_POST['price_per_day']) || (float)$_POST['price_per_day'] <= 0) {
            echo json_encode(array("status" => "error", "message" => "Price per day must be a positive number."));
            return;
        } else {
            $price_per_day = (float) $_POST['price_per_day'];
        }

        
        $description = isset($_POST['description']) ? test_input($_POST['description']) : '';

        
        $allowed_status = array('Available', 'Not Available');
        if (empty($_POST['availability_status']) || !in_array($_POST['availability_status'], $allowed_status)) {
            echo json_encode(array("status" => "error", "message" => "Please select availability status."));
            return;
        } else {
            $availability_status = test_input($_POST['availability_status']);
        }

        
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_mime = array('image/jpeg', 'image/png');
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed_mime)) {
                echo json_encode(array("status" => "error", "message" => "Image must be JPEG or PNG."));
                return;
            }
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                echo json_encode(array("status" => "error", "message" => "Image must be 2MB or smaller."));
                return;
            }

            $upload_dir = __DIR__ . '/../public/uploads/cars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $ext      = ($mime === 'image/png') ? 'png' : 'jpg';
            $filename = uniqid('car_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename);
            $image_path = 'public/uploads/cars/' . $filename;
        }

        if ($action === 'add') {
            if (empty($image_path)) {
                echo json_encode(array("status" => "error", "message" => "Car image is required."));
                return;
            }
            $result = insertCar($conn, $name, $model, $type, $price_per_day, $description, $image_path, $availability_status);
            if ($result) {
                echo json_encode(array("status" => "success", "message" => "Car added successfully."));
            } else {
                echo json_encode(array("status" => "error", "message" => "Database error: " . mysqli_error($conn)));
            }

        } elseif ($action === 'update') {
            $id = (int) test_input($_POST['id']);
            $result = updateCar($conn, $id, $name, $model, $type, $price_per_day, $description, $image_path, $availability_status);
            if ($result) {
                echo json_encode(array("status" => "success", "message" => "Car updated successfully."));
            } else {
                echo json_encode(array("status" => "error", "message" => "Database error: " . mysqli_error($conn)));
            }
        }
        return;
    }

    echo json_encode(array("status" => "error", "message" => "Unknown action."));
}
?>
