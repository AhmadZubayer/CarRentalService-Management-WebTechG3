<?php

session_start();
include("config/db.php");

if(!isset($_SESSION['user_id']))
{
    header("location: login.php");
}

$type = "";

if(isset($_GET['type']))
{
    $type = $_GET['type'];

    $cars = $conn->prepare("SELECT * FROM cars WHERE type = ?");
    $cars->execute([$type]);
}
else
{
    $cars = $conn->prepare("SELECT * FROM cars");
    $cars->execute();
}

$category = $conn->prepare("SELECT DISTINCT type FROM cars");
$category->execute();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse Cars</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="container" style="width:90%;">

<h2>Browse Cars by Category</h2>

<?php
while($cat = $category->fetch(PDO::FETCH_ASSOC))
{
    ?>
    <a href="category.php?type=<?php echo $cat['type']; ?>">
        <button><?php echo $cat['type']; ?></button>
    </a>
    <?php
}
?>

<hr>

<?php
while($car = $cars->fetch(PDO::FETCH_ASSOC))
{
    ?>

    <div class="card">

        <img src="uploads/<?php echo $car['image_path']; ?>">

        <h3><?php echo $car['name']; ?></h3>

        <p>Model : <?php echo $car['model']; ?></p>

        <p>Type : <?php echo $car['type']; ?></p>

        <p>Price Per Day : <?php echo $car['price_per_day']; ?></p>

    </div>

    <?php
}
?>

</div>

</body>
</html>
