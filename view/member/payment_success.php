<?php
$currentPage = 'orders';
$pageTitle = 'Payment Success';
include 'public_header.php';
include_once '../../config/db-config.php';
include_once '../../model/OrderModel.php';

if (!$isLoggedIn) {
    header("Location: ../registration/sign-in.php");
    exit();
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order = getOrderById($conn, $orderId);

if (!$order || $order['user_id'] != $_SESSION['user_id'] || $order['status'] !== 'confirmed') {
    echo "<div class='container-card' style='text-align:center; padding: 40px;'>Order not found or not confirmed. <a href='home.php'>Go Back</a></div>";
    exit();
}
?>

<div class="container-card" style="max-width: 600px; margin: 40px auto; padding: 40px; text-align: center;">
    <div style="width: 80px; height: 80px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
        <svg style="width: 40px; height: 40px; color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>
    
    <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 8px;">Payment Successful!</h2>
    <p style="color: #6b7280; margin-bottom: 30px;">Your rental order 

    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; text-align: left; margin-bottom: 30px;">
        <h4 style="font-size: 12px; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;">Order Summary</h4>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span style="color: #6b7280;">Car</span>
            <span style="font-weight: 600; color: #374151;"><?= htmlspecialchars($order['car_name']) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span style="color: #6b7280;">Rental Period</span>
            <span style="font-weight: 600; color: #374151;"><?= date('d M Y', strtotime($order['start_date'])) ?> - <?= date('d M Y', strtotime($order['end_date'])) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span style="color: #6b7280;">Payment Method</span>
            <span style="font-weight: 600; color: #374151; text-transform: uppercase;"><?= str_replace('_', ' ', $order['payment_method']) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #d1d5db;">
            <span style="font-weight: 700; color: #111827;">Total Paid</span>
            <span style="font-weight: 800; color: #3949AB; font-size: 18px;">Tk <?= number_format($order['total_cost'], 2) ?></span>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 12px;">
        <a href="order_history.php" class="btn-1" style="text-decoration: none;">View My Orders</a>
        <a href="home.php" class="btn-2" style="text-decoration: none;">Return to Home</a>
    </div>
</div>

        </main>
    </div>
</div>
</body>
</html>
