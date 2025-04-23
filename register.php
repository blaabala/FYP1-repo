<?php

include('database.php');
$errors = array();

if (isset($_POST['register'])) {
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

    if (!preg_match("/^[a-zA-Z\s]+$/", $username)) { //alphabetic char and spaces only
        $errors['username'] = "Invalid full name. Please enter a valid name.";
    }

    $emailCheckQuery = "SELECT email FROM users WHERE email = ?";
    $statement = $con->prepare($emailCheckQuery);
    $statement->bind_param("s", $email);
    $statement->execute();
    $emailCheckResult = $statement->get_result();
    if ($emailCheckResult->num_rows > 0) {
        $errors['email'] = "Email is already registered. Please use a different email address.";
    }

    if (empty($errors)) {
        $query = "INSERT INTO `users` (username, email, password, reg_date, role_id, faculty, contact_number)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $statement = $con->prepare($query);
        $statement->bind_param("ssssssi", $username, $email, $hashed_password, $reg_date, $userrole, $faculty, $phoneno);
        $result = $statement->execute();
        if ($result) {
            echo "<div class='message'>
                <h3>Registration Successfully!</h3>
                <h3>Click <a href='login.php'>HERE</a> to login</h3>
              </div><br>";
        } else {
            echo "Registration failed.";
        }
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
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>

<body style="background-color: #98C1D9;">
    <div>
        <h1>Appointment Management System</h1>
    </div>
    <div class="container">
        <div class="box form-box">
            <header class="roboto-black-italic">Student Register Page</header>
            <form id="form" action="" method="post">
                <div class="field input">
                    <input required type="text" id="username" name="username" placeholder="Full Name">
                    <?php if (isset($errors['username'])) {
                        echo "<p style='color:red;'>" . $errors['username'] . "</p>";
                    } ?>
                </div>
                <div class="field input">
                    <input required type="email" id="email" name="email" placeholder="Email Address">
                    <?php if (isset($errors['email'])) {
                        echo "<p style='color:red;'>" . $errors['email'] . "</p>";
                    } ?>
                </div>
                <div class="field input">
                    <input required type="password" id="password" name="password" placeholder="Password"
                        autocomplete="off">
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
                    <input required type="text" id="phoneno" name="phoneno"
                        placeholder="Contact Number (i.e.: 60123456789)">
                </div>
                <div class="field">
                    <input required type="submit" name="register" value="Register" class="btn btn-lg btn-primary">
                </div>
                <div class="input">
                    Existing user? <a href="login.php">Click here to Login Page</a></br>
                    <a href="register_lecturer.php">Continue as Lecturer?</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
</body>

</html>