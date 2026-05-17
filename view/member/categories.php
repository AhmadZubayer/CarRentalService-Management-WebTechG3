<?php
$currentPage = 'categories';
$pageTitle = 'Categories';
include 'public_header.php';
include_once '../../config/db-config.php';
include_once '../../model/CarModel.php';

$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : '';


$catSql = "SELECT DISTINCT type FROM cars";
$catResult = mysqli_query($conn, $catSql);
$categories = [];
if ($catResult) {
    while ($row = mysqli_fetch_assoc($catResult)) {
        $categories[] = $row['type'];
    }
}


$carSql = "SELECT * FROM cars WHERE 1=1";
$params = [];
$types = "";

if ($typeFilter) {
    $carSql .= " AND type=?";
    $params[] = $typeFilter;
    $types .= "s";
}
$carSql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $carSql);
if ($typeFilter) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$carResult = mysqli_stmt_get_result($stmt);
$cars = [];
if ($carResult) {
    while ($row = mysqli_fetch_assoc($carResult)) {
        $booking = getCarCurrentBooking($conn, $row['id']);
        if ($booking) {
            $row['is_booked'] = true;
            $row['available_after'] = $booking['end_date'];
        } else {
            $row['is_booked'] = false;
        }
        $cars[] = $row;
    }
}
mysqli_close($conn);
?>

<style>
    .category-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }
    .category-chip {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .category-chip:hover, .category-chip.active {
        background: #3949ab;
        color: white;
        border-color: #3949ab;
    }

    .car-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }
    .car-card {
        padding: 0;
        margin-bottom: 0;
        overflow: hidden;
        transition: transform 0.2s;
        display: flex;
        flex-direction: column;
    }
    .car-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .car-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: #f3f4f6;
    }
    .car-info {
        padding: 16px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .car-type {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 600;
        color: #3949ab;
        margin-bottom: 6px;
    }
    .car-name {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }
    .car-model {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 15px;
    }
    .car-price {
        margin-top: auto;
        font-size: 18px;
        font-weight: 700;
        color: #3949ab;
    }
    .car-price span {
        font-size: 13px;
        color: #6b7280;
        font-weight: normal;
    }
    .booking-status {
        margin-top: 10px;
        font-size: 12px;
        font-weight: 700;
        color: #ef4444;
    }
    .car-action {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .car-action .btn-1 {
        width: 100%;
        text-align: center;
        text-decoration: none;
        display: inline-block;
    }
</style>

<h2 class="section-title">
    <?= $typeFilter ? htmlspecialchars($typeFilter) . ' Cars' : 'All Available Cars' ?>
</h2>

<div class="category-bar">
    <a href="categories.php" class="category-chip <?= empty($typeFilter) ? 'active' : '' ?>">All Cars</a>
    <?php foreach ($categories as $cat): ?>
        <a href="categories.php?type=<?= urlencode($cat) ?>" class="category-chip <?= $typeFilter === $cat ? 'active' : '' ?>"><?= htmlspecialchars($cat) ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($cars)): ?>
    <div class="container-card" style="text-align:center; padding: 40px; color: #6b7280;">
        No cars found in this category.
    </div>
<?php else: ?>
    <div class="car-grid">
        <?php foreach ($cars as $car): ?>
            <div class="container-card car-card">
                <?php if ($car['image_path']): ?>
                    <img src="../../<?= htmlspecialchars($car['image_path']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" class="car-image">
                <?php else: ?>
                    <div class="car-image" style="display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:14px;">No Image</div>
                <?php endif; ?>
                
                <div class="car-info">
                    <div class="car-type"><?= htmlspecialchars($car['type']) ?></div>
                    <div class="car-name"><?= htmlspecialchars($car['name']) ?></div>
                    <div class="car-model">Model: <?= htmlspecialchars($car['model']) ?></div>
                    <div class="car-price">Tk <?= number_format($car['price_per_day'], 2) ?> <span>/ day</span></div>
                    
                    <?php if ($car['is_booked']): ?>
                        <div class="booking-status">
                            NOT AVAILABLE<br>
                            <span style="font-weight:500; color:#6b7280;">Available after: <?= date('d M Y', strtotime($car['available_after'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="car-action">
                    <?php if ($isLoggedIn && $_SESSION['role'] === 'member'): ?>
                        <a href="rent_car.php?id=<?= $car['id'] ?>" class="btn-1">Rent Now</a>
                    <?php elseif ($isLoggedIn && $_SESSION['role'] === 'admin'): ?>
                        <a href="../admin/manage_cars.php" class="btn-1" style="background:#4b5563;">Manage Car</a>
                    <?php else: ?>
                        <a href="../registration/sign-in.php" class="btn-1" onclick="alert('Please login as a member to rent a car');">Login to Rent</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

        </main>
    </div>
</div>
</body>
</html>
