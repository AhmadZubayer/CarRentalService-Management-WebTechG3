<?php
// process_payment.php  (entry point)
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/controllers/OrderController.php';

$controller = new OrderController();
$controller->processPayment();
?>
