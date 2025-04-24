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
            $expiration_time = time() + 60 * 60 * 2;
            setcookie($cookie_name, $cookie_value, $expiration_time, "/");
        }

        if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
            header("Location: home.php");
            exit();
        } else if ($_SESSION['role_id'] == 3) {
            header("Location: home_admin.php");
            exit();
        }
    } else {
        header("Location: login.php?error=1");
        exit();
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

        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link href="css/tailwind.min.css" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
            rel="stylesheet">
    </head>

    <body class="bg-blue-200 min-h-screen flex items-center justify-center">

        <div class="flex flex-col items-center">
            <img src="assets/images/logo - Copy.png" alt="Logo" class="w-28 h-28 mb-4 object-contain">

            <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md">
                <header class="text-xl font-bold mb-6 text-center">Login Page</header>

                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4 text-center">
                        Wrong Username/Password! Please try again.
                    </div>
                <?php endif; ?>

                <form id="form" action="" method="post">
                    <div>
                        <input required type="email" id="email" name="email" placeholder="Email Address"
                            class="mb-3 w-full p-2 border rounded">
                    </div>
                    <div>
                        <input required type="password" id="password" name="password" placeholder="Password"
                            autocomplete="off" class="mb-3 w-full p-2 border rounded">
                    </div>

                    <p><label for="remember_me">Remember Me&emsp;</label><input type="checkbox" name="remember_me"
                            id="remember_me"></p>
                    <div>
                        <button required type="submit" name="login" value="Login"
                            class="w-full bg-blue-800 text-white py-2 rounded hover:bg-blue-900">Login</button>
                    </div>

                </form>
                <p class="mt-4 text-center text-sm text-gray-600">
                    New user? <a href="register.php" class="text-blue-600 hover:underline">Click here to Register Page</a>
                </p>
            </div>
        </div>
    <?php } ?>
    </div>

    </body>

    </html>