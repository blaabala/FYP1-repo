<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login Page</title>
</head>

<body>
    <?php
    require('database.php');
    if (isset($_POST['user_id'])) {
        $user_id = stripslashes($_REQUEST['user_id']);
        $user_id = mysqli_real_escape_string($con,$user_id);
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($con,$password);
        $query = "SELECT *
        FROM `users`
        WHERE id='$user_id'
        AND password='".password_hash($password, PASSWORD_DEFAULT)."'";
        $result = mysqli_query($con,$query) or die(mysqli_error($con));
        $rows = mysqli_num_rows($result);
        if($rows==1){
            $_SESSION['user_id'] = $user_id;
            header("Location: index.php");
            exit();
        }else {
        echo "<div class='form'>
        <h3>Student ID/password is incorrect.</h3>
        <br/>Click here to <a href='student_login.php'>Login</a></div>";
        }
    }else {
        ?>
        <div class="form">
            <h1>User Login Page</h1>
            <form action="" method="post" name="login">
            <input type="text" name="user_id" placeholder="User ID" required /><br>
            <input type="password" name="password" placeholder="Password" required /><br>
            <input name="submit" type="submit" value="Login" />
            </form>
            <p>Not registered yet? <a href='registration.php'>Register Here</a></p>
        </div>
    <?php } ?>
</body>
</html>