<?php

include('database.php');
if(isset($_POST['register'])) {
    $username = stripslashes($_POST['username']);
    $username = mysqli_real_escape_string($con, strtoupper($username));
    
    $email = stripslashes($_POST['email']);
    $email = mysqli_real_escape_string($con, $email);
    
    $password = stripslashes($_POST['password']);
    $password = mysqli_real_escape_string($con, $password);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $reg_date = date("Y-m-d H:i:s");

    $userrole = stripslashes($_POST['userrole']);
    $userrole = mysqli_real_escape_string($con, $userrole);

    $faculty = stripslashes($_POST['faculty']);
    $faculty = mysqli_real_escape_string($con, $faculty);

    $phoneno = stripslashes($_POST['phoneno']);
    $phoneno = mysqli_real_escape_string($con, $phoneno);

    $verify_email = mysqli_query($con, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($verify_email) != 0) {
        echo "<div class = 'message'>
                <p>Not an unique email address!</p>
              </div><br>";
    } else {
        $query = "INSERT INTO users (username, email, password, reg_date, role_id, faculty, contact_number) 
                VALUES ('$username', '$email', '$hashed_password', '$reg_date', '$userrole', '$faculty', '$phoneno')";
        $result = mysqli_query($con, $query) or die(mysqli_error($con));
        echo "<div class='message'>
                <p>Registration Successfully!</p>
                <p>Click <a href='index.php'>HERE</a> to login</p>
              </div><br>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Register Page</title>
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
            <header class="roboto-black-italic">Student Register Page</header>
            <form action="" method="post">
                <div class="field input">
                    <input required type="text" id="username" name="username" placeholder="Full Name">
                </div>
                <div class="field input">
                    <input required type="email" id="email" name="email" placeholder="Email Address">
                </div>
                <div class="field input">
                    <input required type="text" id="password" name="password" placeholder="Password" autocomplete="off">
                </div>
                <div class="field">
                    <select id="userrole" name="userrole">
                        <option value="" disabled selected>User Role</option>
                        <option value="2">Student</option>
                      </select>
                </div>
                <div class="field">
                    <select id="faculty" name="faculty">
                        <option value="" disabled selected>Faculty</option>
                        <option value="MK-FMHS">M. Kandiah Faculty of Medicine and Health Sciences</option>
                        <option value="LKC-FES">Lee Kong Chian Faculty of Engineering and Science</option>
                        <option value="FEGT">Faculty of Engineering and Green Technology</option>
                        <option value="FICT">Faculty of Information and Communication Technology</option>
                        <option value="FSc">Faculty of Science</option>
                        <option value="FAM">Faculty of Accountancy and Management (Sungai Long Campus)</option>
                        <option value="FBF">Faculty of Business and Finance (Kampar Campus)</option>
                        <option value="FAS">Faculty of Arts and Social Science (Kampar Campus)</option>
                        <option value="FCI">Faculty of Creative Industries</option>
                        <option value="Postgraduate">Institute of Postgraduate Studies & Research</option>
                        <option value="ICS">Institute of Chinese Studies</option>
                        <option value="IMLD">Institute of Management and Leadership Development</option>
                        <option value="CFS-KPR">Centre for Foundation Studies (Kampar Campus)</option>
                        <option value="CFS-SGLONG">Centre for Foundation Studies (Sungai Long Campus)</option>
                        <option value="CEE">Centre for Extension Education</option>
                        <option value="CCCD">Centre for Corporate and Community Development</option>
                    </select>
                </div>
                <div class="field input">
                    <input required type="text" id="phoneno" name="phoneno" placeholder="Contact Number (i.e.: +60123456789)">
                </div>
                <div class="field">
                    <input required type="submit" name="register" value="Register" class="btn">
                </div>
                <div class="input">
                    Existing user? <a href="index.php">Click here to Login Page</a></br>
                    <a href="register_lecturer.php">Continue as Lecturer?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>