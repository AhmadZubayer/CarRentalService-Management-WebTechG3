<?php
$currentPage = 'home';
$pageTitle = 'Rent a Car';
include 'public_header.php';
include_once '../../config/db-config.php';
include_once '../../controller/CarController.php';

if (!$isLoggedIn || $_SESSION['role'] !== 'member') {
    header("Location: ../registration/sign-in.php");
    exit();
}

$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$car = getCarById($conn, $carId);

if (!$car) {
    echo "<div class='container-card' style='text-align:center; padding: 40px;'>Car not found. <a href='home.php'>Go Back</a></div>";
    exit();
}


$bookedDates = getCarBookedDates($conn, $carId);
?>

<style>
    .form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-field label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: 0.05em;
    }
    .form-field input {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
        color: #111827;
        width: 100%;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-field input:focus {
        border-color: #3949ab;
        box-shadow: 0 0 0 2px rgba(57, 73, 171, 0.1);
    }
</style>

<h2 class="section-title">Rent: <?= htmlspecialchars($car['name']) ?></h2>

<div class="container-card" style="max-width: 900px; margin: 0 auto;">
    <div class="car-detail-grid">
        <div class="car-detail-img-wrap">
            <img src="../../<?= htmlspecialchars($car['image_path'] ? $car['image_path'] : 'public/logo.png') ?>" class="car-detail-img" alt="<?= htmlspecialchars($car['name']) ?>">
        </div>
        <div class="car-detail-info">
            <div class="car-type"><?= htmlspecialchars($car['type']) ?></div>
            <h2><?= htmlspecialchars($car['name']) ?></h2>
            <p>Model: <?= htmlspecialchars($car['model']) ?></p>
            <p style="font-weight: 700; font-size: 18px; margin: 10px 0;">Tk <?= number_format($car['price_per_day'], 2) ?> / day</p>
            
            <?php if (!empty($bookedDates)): ?>
                <div style="margin-bottom: 25px; padding: 5px 0;">
                    <h4 style="font-size: 11px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.05em;">Currently Booked Dates:</h4>
                    <ul style="font-size: 13px; color: #374151; list-style: none; padding: 0; line-height: 1.6;">
                        <?php foreach ($bookedDates as $range): ?>
                            <li><span style="color: #dc2626; margin-right: 5px;">•</span> <?= date('d M', strtotime($range['start_date'])) ?> to <?= date('d M Y', strtotime($range['end_date'])) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="orderForm" style="margin-top: 20px; border-top: 1px solid #f3f4f6; padding-top: 20px;">
                <input type="hidden" name="car_id" id="car_id" value="<?= $car['id'] ?>">
                <input type="hidden" name="price_per_day" id="price_per_day" value="<?= $car['price_per_day'] ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: flex-start;">
                    <div class="form-field">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-field">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" required min="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div id="cost_display" style="margin: 20px 0; padding: 15px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; display: none;">
                    <p>Total Days: <span id="display_days" style="font-weight: 600;">0</span></p>
                    <p style="font-size: 18px; font-weight: 700; color: #111827; margin-top: 5px;">Total Cost: Tk <span id="display_total">0.00</span></p>
                    <input type="hidden" name="total_cost" id="total_cost" value="0">
                </div>

                <p id="error_msg" style="color: #ef4444; font-size: 13px; margin-bottom: 10px; display: none;"></p>

                <button type="submit" class="btn-1" id="placeOrderBtn" style="width: 100%; margin-top: 10px;" disabled>Place Order</button>
            </form>
        </div>
    </div>
</div>

<script>
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const costDisplay = document.getElementById('cost_display');
    const displayDays = document.getElementById('display_days');
    const displayTotal = document.getElementById('display_total');
    const totalCostInput = document.getElementById('total_cost');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const errorMsg = document.getElementById('error_msg');
    const carId = document.getElementById('car_id').value;
    const bookedDates = <?= json_encode($bookedDates) ?>;

    function checkRangeOverlap(startStr, endStr) {
        const checkStart = new Date(startStr);
        const checkEnd = new Date(endStr);
        checkStart.setHours(0,0,0,0);
        checkEnd.setHours(0,0,0,0);

        for (let range of bookedDates) {
            const start = new Date(range.start_date);
            const end = new Date(range.end_date);
            start.setHours(0,0,0,0);
            end.setHours(0,0,0,0);

            
            if (checkStart <= end && checkEnd >= start) {
                return true;
            }
        }
        return false;
    }

    function calculateCost() {
        const start = startDateInput.value;
        const end = endDateInput.value;

        if (start && end) {
            if (new Date(start) > new Date(end)) {
                errorMsg.textContent = "Start date cannot be after end date.";
                errorMsg.style.display = "block";
                costDisplay.style.display = "none";
                placeOrderBtn.disabled = true;
                return;
            }

            if (checkRangeOverlap(start, end)) {
                errorMsg.textContent = "Selected dates overlap with an existing booking.";
                errorMsg.style.display = "block";
                costDisplay.style.display = "none";
                placeOrderBtn.disabled = true;
                return;
            }
            
            errorMsg.style.display = "none";

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../../ajax_handler.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        displayDays.textContent = data.days;
                        displayTotal.textContent = parseFloat(data.total_cost).toLocaleString(undefined, {minimumFractionDigits: 2});
                        totalCostInput.value = data.total_cost;
                        costDisplay.style.display = "block";
                        placeOrderBtn.disabled = false;
                    } else {
                        errorMsg.textContent = data.message;
                        errorMsg.style.display = "block";
                        costDisplay.style.display = "none";
                        placeOrderBtn.disabled = true;
                    }
                }
            };
            xhr.send("module=order&action=calculateCost&car_id=" + carId + "&start_date=" + start + "&end_date=" + end + "&csrf_token=" + encodeURIComponent(window.csrfToken));
        }
    }

    startDateInput.addEventListener('change', calculateCost);
    endDateInput.addEventListener('change', calculateCost);

    document.getElementById('orderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        placeOrderBtn.disabled = true;
        placeOrderBtn.textContent = "Processing...";

        var formData = new FormData(this);
        formData.append('module', 'order');
        formData.append('action', 'createOrder');
        formData.append('csrf_token', window.csrfToken);

        var params = new URLSearchParams(formData).toString();

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../ajax_handler.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var data = JSON.parse(xhr.responseText);
                if (data.status === 'success') {
                    window.location.href = 'invoice.php?order_id=' + data.order_id;
                } else {
                    errorMsg.textContent = data.message;
                    errorMsg.style.display = "block";
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.textContent = "Place Order";
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
