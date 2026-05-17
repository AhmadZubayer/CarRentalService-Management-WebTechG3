<?php
// payment.php  (entry point)
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/controllers/OrderController.php';

$order_id = (int)($_GET['order_id'] ?? 0);
if (!$order_id) {
    header('Location: /index.php');
    exit();
}

$controller = new OrderController();
$controller->showPayment($order_id);
?>
