<?php

include("config/db.php");

$nameError = "";
$emailError = "";
$passwordError = "";
$success = "";

if(isset($_POST['register']))
{
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];

    if(empty($name))
    {
        $nameError = "Name Required";
    }

    if(empty($email))
    {
        $emailError = "Email Required";
    }

    if(strlen($password) < 8)
    {
        $passwordError = "Password must be at least 8 characters";
    }

    if($nameError == "" && $emailError == "" && $passwordError == "")
    {
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);

        if($check->rowCount() > 0)
        {
            $emailError = "Email Already Exists";
        }
        else
        {
            $hashPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = $conn->prepare("INSERT INTO users(name,email,password_hash,role,address,phone) VALUES(?,?,?,?,?,?)");

            $sql->execute([$name,$email,$hashPassword,$role,$address,$phone]);

            $success = "Registration Successful";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">

<h2>Registration Form</h2>

<form method="POST" onsubmit="return validation()">

<input type="text" name="name" id="name" placeholder="Enter Name">
<span class="error"><?php echo $nameError; ?></span>

<input type="email" name="email" id="email" placeholder="Enter Email">
<span class="error"><?php echo $emailError; ?></span>

<input type="password" name="password" id="password" placeholder="Enter Password">
<span class="error"><?php echo $passwordError; ?></span>

<textarea name="address" placeholder="Enter Address"></textarea>

<input type="text" name="phone" placeholder="Enter Phone Number">

<select name="role">
    <option value="member">Member</option>
    <option value="admin">Admin</option>
</select>

<button type="submit" name="register">Register</button>

<p class="success"><?php echo $success; ?></p>

<p>
Already have account?
<a href="login.php">Login</a>
</p>

</form>

</div>

<script>
function validation()
{
    var name = document.getElementById("name").value;
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;

    if(name == "")
    {
        alert("Name Required");
        return false;
    }

    if(email == "")
    {
        alert("Email Required");
        return false;
    }

    if(password.length < 8)
    {
        alert("Password minimum 8 characters");
        return false;
    }
}
</script>

</body>
</html>
