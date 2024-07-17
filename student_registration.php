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
        if (isset($_POST['student_name'])) {
            $student_name = stripslashes($_POST['student_name']);
            $student_name = mysqli_real_escape_string($con, $student_name);
            
            $utar_mail = stripslashes($_POST['utar_mail']);
            $utar_mail = mysqli_real_escape_string($con, $utar_mail);
            
            $password = stripslashes($_POST['password']);
            $password = mysqli_real_escape_string($con, $password);
            
            $student_id = stripslashes($_POST['student_id']);
            $student_id = mysqli_real_escape_string($con, $student_id);
            
            $student_name = stripslashes($_POST['student_name']);
            $student_name = mysqli_real_escape_string($con, $student_name);
            
            $programme_id = stripslashes($_POST['programme_id']);
            $programme_id = mysqli_real_escape_string($con, $programme_id);
            
            $phone = stripslashes($_POST['phone']);
            $phone = mysqli_real_escape_string($con, $phone);
            
            $reg_date = date("Y-m-d H:i:s");
            $user_type = "student";

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Register info into users and students table
            $users_query = "INSERT INTO `users` (user_type, password, reg_date) 
                    VALUES ('$user_type', '$hashed_password', '$reg_date')";
            
            $result = mysqli_query($con, $users_query);
            
            if ($result) {
                $students_query = "INSERT INTO `students` (id, student_name, email, programme_id, phone) 
                VALUES ('$student_id', '$student_name', '$utar_mail', '$programme_id', '$phone')";
        
                $result2 = mysqli_query($con, $students_query);
    
                if ($result2) {
                    echo "<div class='form'>
                          <h3>You are registered successfully.</h3>
                          <br/>Click here to <a href='login.php'>Login</a></div>";
                } else {
                    echo "<div class='form'>
                          <h3>There was an error during registration in the students table.</h3></div>";
                }
            } else {
                echo "<div class='form'>
                      <h3>There was an error during registration in the users table.</h3></div>";
            }
        } else {
    ?>

    <div class="form">
    <h1>Student Registration Page</h1>
    <form name="registration" action="" method="post">
        <input type="text" name="student_id" placeholder="Student ID" required /><br>
        <input type="text" name="student_name" placeholder="Name" required /><br>
        <input type="text" name="programme_id" placeholder="Programme (e.g.: IA)" required /><br>
        <input type="email" name="utar_mail" placeholder="UTAR Mail" required /><br>
        <input type="tel" name="phone" placeholder="Phone No." required /><br>
        <input type="password" name="password" placeholder="Password" required /><br>
        <input type="submit" name="submit" value="Register" />
    </form>
    </div>
    <?php }?>
</body>
</html>

