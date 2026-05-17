<?php

session_start();
include("config/db.php");

$error = "";

if(isset($_COOKIE['remember_user']))
{
    $_SESSION['user_id'] = $_COOKIE['remember_user'];
    header("location: dashboard.php");
}

if(isset($_POST['login']))
{
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $sql->execute([$email]);

    if($sql->rowCount() > 0)
    {
        $user = $sql->fetch(PDO::FETCH_ASSOC);

        if(password_verify($password, $user['password_hash']))
        {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if(isset($_POST['remember']))
            {
                setcookie("remember_user", $user['id'], time() + (86400 * 30), "/");
            }

            header("location: dashboard.php");
        }
        else
        {
            $error = "Incorrect Password";
        }
    }
    else
    {
        $error = "Email Not Found";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">

<h2>Login Form</h2>

<form method="POST">

<input type="email" name="email" placeholder="Enter Email" required>

<input type="password" name="password" placeholder="Enter Password" required>

<label>
<input type="checkbox" name="remember">
Remember Me
</label>

<button type="submit" name="login">Login</button>

<p class="error"><?php echo $error; ?></p>

<p>
No account?
<a href="register.php">Register</a>
</p>

</form>

</div>

</body>
</html>
