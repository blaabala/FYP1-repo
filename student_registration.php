<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Page</title>
    </head>

    <body>
        <?php
        require('database.php');
        if (isset($_REQUEST['username'])) {
            $username = stripslashes($_POST['username']);
            $username = mysqli_real_escape_string($con, $username);
            
            $email = stripslashes($_POST['email']);
            $email = mysqli_real_escape_string($con, $email);
            
            $password = stripslashes($_POST['password']);
            $password = mysqli_real_escape_string($con, $password);
            
            $studentid = stripslashes($_POST['studentid']);
            $studentid = mysqli_real_escape_string($con, $studentid);
            
            $studentname = stripslashes($_POST['studentname']);
            $studentname = mysqli_real_escape_string($con, $studentname);
            
            $phone = stripslashes($_POST['phone']);
            $phone = mysqli_real_escape_string($con, $phone);
            
            $reg_date = date("Y-m-d H:i:s");
            $usertype = "student";
    
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
            // Insert the user into the database
            $query = "INSERT INTO `students` (studentid, studentname, utarmail, phone, user_type, password, reg_date) 
                      VALUES ('$username', '$hashed_password', '$email', '$phone', '$reg_date', 'user')";
            
            $result = mysqli_query($con, $query);
    
            if ($result) {
                echo "<div class='form'>
                      <h3>You are registered successfully.</h3>
                      <br/>Click here to <a href='login.php'>Login</a></div>";
            } else {
                echo "<div class='form'>
                      <h3>There was an error during registration.</h3></div>";
            }
        } else {
        ?>

        <div class="form">
        <h1>Student Registration Page</h1>
        <form name="registration" action="" method="post">
            <input type="text" name="studentid" placeholder="Student ID" required /><br>
            <input type="text" name="studentname" placeholder="Name" required /><br>
            <input type="email" name="utarmail" placeholder="UTAR Mail" required /><br>
            <input type="tel" name="phone" placeholder="Phone No." required /><br>
            <input type="password" name="password" placeholder="Password" required /><br>
            <input type="submit" name="submit" value="Register" />
        </form>
        </div>
        <?php } ?>
    </body>
</html>
