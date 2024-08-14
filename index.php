<?php
session_start();

include('database.php');
if(isset($_POST['login'])) {
    $email = stripslashes($_POST['email']);
    $email = mysqli_real_escape_string($con, $email);
    
    $password = stripslashes($_POST['password']);
    $password = mysqli_real_escape_string($con, $password);

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($con,$query) or die(mysqli_error($con));
    $rows = mysqli_fetch_assoc($result);

    if (is_array($rows) && password_verify($password, $rows['password'])) {
        $_SESSION['id'] = $rows['id'];
        $_SESSION['username'] = $rows['username'];
        $_SESSION['email'] = $rows['email'];
        $_SESSION['role_id'] = $rows['role_id'];

        echo $_SESSION['id'];
        echo $_SESSION['username'];
        echo $_SESSION['email'];
        echo $_SESSION['role_id'];

        if (isset($_POST['remember_me'])) {
            $cookie_name = "email";
            $cookie_value = $email;
            $expiration_time = time() + 60 * 60 * 24 * 30;
            setcookie($cookie_name, $cookie_value, $expiration_time, "/");
        }

        if ($_SESSION['role_id'] == 1){
            header("Location: home_lecturer.php");
        } elseif ($_SESSION['role_id'] == 2) {
            header("Location: home.php");
        } else {
            header("Location: home_admin.php");
        }
    } else {
        echo "<div class= 'message'>
                <p>Wrong Username/Password!</p>
              </div><br>";
        echo "<a href='index.php'><button class='btn'>Go Back</button></a>";
    }
} else {

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header class="roboto-black-italic">Login Page</header>
            <form action="" method="post">
                <div class="field input">
                    <input required type="email" id="email" name="email" placeholder="Email Address">
                </div>
                <div class="field input">
                    <input required type="password" id="password" name="password" placeholder="Password" autocomplete="off">
                </div>

                    <p><label for="remember_me">Remember Me</label><input type="checkbox" name="remember_me" id="remember_me"></p>
                <div class="field">
                    <input required type="submit" name="login" value="Login" class="btn">
                </div>
                <div class="input">
                    New user? Click here to <a href="register.php">Register Page</a>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>
</body>
</html>