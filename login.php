<?php
session_start();
include('database.php');
$errors = array();

if (isset($_POST['login'])) {
    $email = stripslashes($_POST['email']);
    $email = mysqli_real_escape_string($con, $email);

    $password = stripslashes($_POST['password']);
    $password = mysqli_real_escape_string($con, $password);

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($con, $query) or die(mysqli_error($con));
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

        if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
            header("Location: home.php");
            exit();
        } else {
            header("Location: home_admin.php");
            exit();
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        <link rel="icon" type="image/x-icon" href="images/favicon.ico">
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    </head>

    <body style="background-color: #98C1D9;">
        <div>
            <h1>Appointment Management System</h1>
        </div>
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

                    <p><label for="remember_me">Remember Me&emsp;</label><input type="checkbox" name="remember_me" id="remember_me"></p>
                    <div class="field">
                        <input required type="submit" name="login" value="Login" class="btn btn-lg btn-primary">
                    </div>
                    <div class="input">
                        New user? <a href="register.php">Click here to Register Page</a>
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    </body>

    </html>