<?php
$pageTitle = 'Dashboard';
include 'admin_header.php';
include '../../config/db-config.php';
include '../../model/UserModel.php';
include '../../model/OrderModel.php';

$totalCars    = getTotalCars($conn);
$totalMembers = getTotalMembers($conn);
$totalOrders  = getTotalOrders($conn);
$totalBlogs   = getTotalBlogs($conn);

$recentOrders = getAllOrders($conn);
$recentOrders = array_slice($recentOrders, 0, 5);

mysqli_close($conn);
?>


<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-value"><?= $totalCars ?></div>
        <div class="stat-label">Total Cars</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-value"><?= $totalMembers ?></div>
        <div class="stat-label">Members</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-value"><?= $totalOrders ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-value"><?= $totalBlogs ?></div>
        <div class="stat-label">Blog Posts</div>
    </div>
</div>


<div class="container-card">
    <div class="card-heading">
        Recent Rent Orders
        <a href="orders.php" class="view-all-link">View All &rarr;</a>
    </div>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Car</th>
                    <th>Rental Period</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                <tr>
                    <td colspan="6" class="loading-td">No orders yet.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['member_name']) ?></td>
                    <td>
                        <?= htmlspecialchars($order['car_name']) ?>
                        <br><span style="font-size:11px;color:#9ca3af"><?= htmlspecialchars($order['car_model']) ?></span>
                    </td>
                    <td><?= $order['start_date'] ?> to <?= $order['end_date'] ?></td>
                    <td><strong>Tk <?= number_format($order['total_cost'], 2) ?></strong></td>
                    <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                    <td><?= htmlspecialchars($order['payment_method'] ?? '-') ?></td>
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

<div id="toast-container"></div>
</body>
</html>
