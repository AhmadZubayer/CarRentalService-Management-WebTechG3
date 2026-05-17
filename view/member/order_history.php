<?php
$currentPage = 'orders';
$pageTitle = 'My Order History';
include 'public_header.php';
include_once '../../config/db-config.php';

$userId = $_SESSION['user_id'];


$sql = "SELECT o.id, c.name AS car_name, c.model AS car_model, c.type AS car_type, 
               o.start_date, o.end_date, o.total_cost, o.status, o.order_date
        FROM orders o
        JOIN cars c ON o.car_id = c.id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}
mysqli_close($conn);
?>

<h2 class="section-title">My Rental Orders</h2>

<div class="container-card">
    <div class="card-heading">Rent History</div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order 
                    <th>Car Details</th>
                    <th>Rental Period</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px; color: #9ca3af;">
                            You haven't placed any orders yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($order['car_name']) ?></strong><br>
                                <span style="font-size:12px; color:#6b7280;"><?= htmlspecialchars($order['car_type']) ?> - <?= htmlspecialchars($order['car_model']) ?></span>
                            </td>
                            <td>
                                <?= date('d M Y', strtotime($order['start_date'])) ?> - <?= date('d M Y', strtotime($order['end_date'])) ?>
                            </td>
                            <td style="font-weight:600;">Tk <?= number_format($order['total_cost'], 2) ?></td>
                            <td>
                                <span class="badge badge-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                            </td>
                            <td style="color:#6b7280; font-size:12.5px;">
                                <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

        </main>
    </div>
</div>
</body>
</html>
