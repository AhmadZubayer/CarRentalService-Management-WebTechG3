<?php
$currentPage = 'home';
$pageTitle = 'Payment Section';
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

<h2 class="section-title">Payment for Order 

<div class="container-card" style="max-width: 600px; margin: 0 auto; padding: 30px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <p style="font-size: 14px; color: #6b7280; margin-bottom: 5px;">Amount to Pay</p>
        <h2 style="font-size: 32px; font-weight: 800; color: #3949AB;">Tk <?= number_format($order['total_cost'], 2) ?></h2>
    </div>

    <form id="paymentForm">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <input type="hidden" name="amount" value="<?= $order['total_cost'] ?>">

        <h4 style="font-size: 12px; text-transform: uppercase; color: #374151; font-weight: 700; margin-bottom: 15px; border-bottom: 1px solid #f3f4f6; padding-bottom: 8px;">Select Payment Method</h4>
        
        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 30px;">
            <label style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; transition: all 0.2s;" class="payment-option">
                <input type="radio" name="payment_method" value="credit_card" required>
                <span style="font-weight: 600; font-size: 14px; color: #374151;">Credit / Debit Card</span>
            </label>
            <label style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; transition: all 0.2s;" class="payment-option">
                <input type="radio" name="payment_method" value="bkash">
                <span style="font-weight: 600; font-size: 14px; color: #374151;">bKash</span>
            </label>
            <label style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; transition: all 0.2s;" class="payment-option">
                <input type="radio" name="payment_method" value="nagad">
                <span style="font-weight: 600; font-size: 14px; color: #374151;">Nagad</span>
            </label>
            <label style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; transition: all 0.2s;" class="payment-option">
                <input type="radio" name="payment_method" value="bank_transfer">
                <span style="font-weight: 600; font-size: 14px; color: #374151;">Bank Transfer</span>
            </label>
            <label style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; transition: all 0.2s;" class="payment-option">
                <input type="radio" name="payment_method" value="other">
                <span style="font-weight: 600; font-size: 14px; color: #374151;">Cash on Delivery</span>
            </label>
        </div>

        <button type="submit" id="confirmPaymentBtn" class="btn-1" style="width: 100%; padding: 15px; font-size: 15px;">Confirm Payment</button>
    </form>
</div>

<style>
    .payment-option:has(input:checked) {
        border-color: #3949ab;
        background: #f3f4f6;
    }
    .payment-option input {
        accent-color: #3949ab;
        width: 18px;
        height: 18px;
    }
</style>

<script>
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('confirmPaymentBtn');
        btn.disabled = true;
        btn.textContent = "Processing Payment...";

        var formData = new FormData(this);
        formData.append('module', 'order');
        formData.append('action', 'confirmPayment');
        formData.append('csrf_token', window.csrfToken);

        var params = new URLSearchParams(formData).toString();

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../ajax_handler.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var data = JSON.parse(xhr.responseText);
                if (data.status === 'success') {
                    window.location.href = 'payment_success.php?order_id=<?= $order['id'] ?>';
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.textContent = "Confirm Payment";
                }
            }
        };
        xhr.send(params);
    });
</script>

        </main>
    </div>
</div>
</body>
</html>
