<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("location: login.php");
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="container">

<h2>Welcome <?php echo $_SESSION['name']; ?></h2>

<h3>Role : <?php echo $_SESSION['role']; ?></h3>

<p>Task 1 Dashboard Page</p>

</div>

</body>
</html>
