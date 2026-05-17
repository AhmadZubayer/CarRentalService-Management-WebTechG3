<?php
$host = "localhost";
$user = "root";
$password = "";
$dbName = "car_rental_db";


$conn = mysqli_connect($host, $user, $password);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql = "CREATE DATABASE IF NOT EXISTS $dbName";
if (mysqli_query($conn, $sql)) {
    mysqli_select_db($conn, $dbName);
} else {
    die("Error creating database: " . mysqli_error($conn));
}


$usersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    remember_token VARCHAR(255) DEFAULT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $usersTable)) {
    die("Error creating users table: " . mysqli_error($conn));
}


$carsTable = "CREATE TABLE IF NOT EXISTS cars (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    type ENUM('Private Car', 'Microbus', 'Pick-up', 'Other') NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    availability_status ENUM('Available', 'Not Available') DEFAULT 'Available',
    image_path VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $carsTable)) {
    die("Error creating cars table: " . mysqli_error($conn));
}


$ordersTable = "CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    car_id INT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $ordersTable)) {
    die("Error creating orders table: " . mysqli_error($conn));
}


$paymentsTable = "CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'bkash', 'nagad', 'bank_transfer', 'other') NOT NULL,
    transaction_id VARCHAR(100) DEFAULT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $paymentsTable)) {
    die("Error creating payments table: " . mysqli_error($conn));
}


$rentalsTable = "CREATE TABLE IF NOT EXISTS rentals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    car_id INT UNSIGNED NOT NULL,
    order_id INT UNSIGNED NOT NULL,
    rental_date DATE NOT NULL,
    return_date DATE NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $rentalsTable)) {
    die("Error creating rentals table: " . mysqli_error($conn));
}


$blogsTable = "CREATE TABLE IF NOT EXISTS blogs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $blogsTable)) {
    die("Error creating blogs table: " . mysqli_error($conn));
}
?>