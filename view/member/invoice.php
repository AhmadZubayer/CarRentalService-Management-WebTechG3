<?php
$currentPage = 'home';
$pageTitle = 'Order Invoice';
include 'public_header.php';
include_once '../../config/db-config.php';
include_once '../../model/OrderModel.php';

if (!$isLoggedIn) {
    header("Location: ../registration/sign-in.php");
    exit();
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order = getOrderById($conn, $orderId);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    echo "<div class='container-card' style='text-align:center; padding: 40px;'>Order not found. <a href='home.php'>Go Back</a></div>";
    exit();
}

if ($order['status'] !== 'pending') {
    echo "<div class='container-card' style='text-align:center; padding: 40px;'>This order is already " . $order['status'] . ". <a href='order_history.php'>View History</a></div>";
    exit();
}
?>

<h2 class="section-title">Order Invoice 

<div class="container-card" style="max-width: 700px; margin: 0 auto; padding: 30px;">
    <div style="border-bottom: 2px solid #f3f4f6; padding-bottom: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 style="font-size: 24px; color: #111827;">Invoice</h2>
            <p style="color: #6b7280; font-size: 13px;">Date: <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></p>
        </div>
        <div style="text-align: right;">
            <div class="badge badge-pending">PENDING PAYMENT</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <div>
            <h4 style="font-size: 11px; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em; margin-bottom: 8px;">Customer Details</h4>
            <p style="font-weight: 600; color: #374151;"><?= htmlspecialchars($_SESSION['name']) ?></p>
            <p style="font-size: 13px; color: #6b7280;"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></p>
        </div>
        <div>
            <h4 style="font-size: 11px; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em; margin-bottom: 8px;">Car Details</h4>
            <p style="font-weight: 600; color: #374151;"><?= htmlspecialchars($order['car_name']) ?></p>
            <p style="font-size: 13px; color: #6b7280;"><?= htmlspecialchars($order['car_type']) ?> - <?= htmlspecialchars($order['car_model']) ?></p>
        </div>
    </div>

    <div class="tbl-wrap" style="margin-bottom: 30px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <th style="padding: 12px; text-align: left; font-size: 12px; color: #6b7280;">Description</th>
                    <th style="padding: 12px; text-align: center; font-size: 12px; color: #6b7280;">Dates</th>
                    <th style="padding: 12px; text-align: right; font-size: 12px; color: #6b7280;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 15px 12px;">
                        <span style="font-weight: 500;">Car Rental Service</span><br>
                        <span style="font-size: 12px; color: #6b7280;">Daily Rate: Tk <?= number_format($order['price_per_day'], 2) ?></span>
                    </td>
                    <td style="padding: 15px 12px; text-align: center; font-size: 13px;">
                        <?= date('d M Y', strtotime($order['start_date'])) ?> to<br>
                        <?= date('d M Y', strtotime($order['end_date'])) ?>
                    </td>
                    <td style="padding: 15px 12px; text-align: right; font-weight: 600;">
                        Tk <?= number_format($order['total_cost'], 2) ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="padding: 20px 12px; text-align: right; font-weight: 700; font-size: 16px;">Total Amount:</td>
                    <td style="padding: 20px 12px; text-align: right; font-weight: 800; font-size: 20px; color: #3949AB;">Tk <?= number_format($order['total_cost'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="display: flex; gap: 15px; margin-top: 20px; align-items: center;">
        <button id="finalizeBtn" class="btn-1" style="padding: 10px 30px; font-size: 13px;">Pay</button>
        <button id="cancelBtn" style="background: none; border: none; color: #dc2626; font-size: 12px; font-weight: 700; text-transform: uppercase; cursor: pointer; padding: 10px 0; letter-spacing: 0.05em;">Cancel Order</button>
    </div>
</div>

<script>
    const orderId = <?= $order['id'] ?>;

    document.getElementById('finalizeBtn').addEventListener('click', function() {
        window.location.href = 'payment.php?order_id=' + orderId;
    });

    document.getElementById('cancelBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to cancel this order?')) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../../ajax_handler.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        alert('Order cancelled successfully.');
                        window.location.href = 'home.php';
                    } else {
                        alert('Error: ' + data.message);
                    }
                }
            };
            xhr.send("module=order&action=cancelOrder&order_id=" + orderId);
        }
    });
</script>

        </main>
    </div>
</div>
</body>
</html>
