<?php

session_start();
include("config/db.php");

if(!isset($_SESSION['user_id']))
{
    header("location: login.php");
}

$userId = $_SESSION['user_id'];

$sql = $conn->prepare("SELECT * FROM users WHERE id = ?");
$sql->execute([$userId]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

$success = "";
$error = "";

if(isset($_POST['update']))
{
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $imageName = $user['profile_picture'];

    if(!empty($_FILES['image']['name']))
    {
        $fileName = time() . $_FILES['image']['name'];
        $tmpName = $_FILES['image']['tmp_name'];

        move_uploaded_file($tmpName, "uploads/" . $fileName);

        $imageName = $fileName;
    }

    $update = $conn->prepare("UPDATE users SET name=?, email=?, address=?, phone=?, profile_picture=? WHERE id=?");

    $update->execute([$name,$email,$address,$phone,$imageName,$userId]);

    $_SESSION['name'] = $name;

    $success = "Profile Updated Successfully";
}

if(isset($_POST['changePassword']))
{
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    if(password_verify($currentPassword, $user['password_hash']))
    {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $passUpdate = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");

        $passUpdate->execute([$newHash,$userId]);

        $success = "Password Changed Successfully";
    }
    else
    {
        $error = "Current Password Incorrect";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="container">

<h2>Profile Page</h2>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="name" value="<?php echo $user['name']; ?>">

<input type="email" name="email" value="<?php echo $user['email']; ?>">

<textarea name="address"><?php echo $user['address']; ?></textarea>

<input type="text" name="phone" value="<?php echo $user['phone']; ?>">

<input type="file" name="image">

<?php
if($user['profile_picture'] != "")
{
    ?>
    <img src="uploads/<?php echo $user['profile_picture']; ?>" width="100">
    <?php
}
?>

<button type="submit" name="update">Update Profile</button>

</form>

<hr>

<h3>Change Password</h3>

<form method="POST">

<input type="password" name="current_password" placeholder="Current Password">

<input type="password" name="new_password" placeholder="New Password">

<button type="submit" name="changePassword">Change Password</button>

</form>

<p class="success"><?php echo $success; ?></p>
<p class="error"><?php echo $error; ?></p>

</div>

</body>
</html>
