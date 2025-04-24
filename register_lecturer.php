<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
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

    $department = stripslashes($_POST['department']);
    $department = mysqli_real_escape_string($con, $department);

    $designation = stripslashes($_POST['designation']);
    $designation = mysqli_real_escape_string($con, $designation);

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

        //getting lecturer's user id and stores into $user_id
        if ($result) {
            $user_id = $statement->insert_id;

            $query = "INSERT INTO `lecturers` (username, user_id, faculty, designation, department)
            VALUES (?, ?, ?, ?, ?)";
            $statement = $con->prepare($query);
            $statement->bind_param("sisss", $username, $user_id, $faculty, $designation, $department);
            $result = $statement->execute();
        }

        if ($result) {
            header("Location: register_lecturer.php?success=1");
            exit();
        } else {
            header("Location: register_lecturer.php?error=1");
            exit();
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
    <!-- <link rel="stylesheet" href="css/style.css"> -->
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
    <!-- <div>
        <h1>Appointment Management System</h1>
    </div> -->
    <div class="flex flex-col items-center">
        <img src="assets/images/logo - Copy.png" alt="Logo" class="w-28 h-28 mb-4 object-contain">

        <!-- ✅ Form Box -->
        <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md">
            <h2 class="text-xl font-bold mb-6 text-center">Lecturer Register Page</h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4 text-center">
                    Registration successful! <a href="login.php" class="text-blue-600 underline">Click here to login</a>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4 text-center">
                    Registration failed. Something went wrong. Please try again or contact support.
                </div>
            <?php endif; ?>

            <form id="form" action="" method="POST">
                <div>
                    <input required type="text" id="username" name="username" placeholder="Full Name"
                        class="mb-3 w-full p-2 border rounded">
                    <?php if (isset($errors['username'])) {
                        echo "<p style='color:red;'>" . $errors['username'] . "</p>";
                    } ?>
                </div>
                <div>
                    <input required type="email" id="email" name="email" placeholder="Email Address"
                        class="mb-3 w-full p-2 border rounded">
                    <?php if (isset($errors['email'])) {
                        echo "<p style='color:red;'>" . $errors['email'] . "</p>";
                    } ?>
                </div>
                <div>
                    <input required type="password" id="password" name="password" placeholder="Password"
                        class="mb-3 w-full p-2 border rounded" autocomplete="off">
                </div>

                <div>
                    <select required id="userrole" name="userrole" class="mb-3 w-full p-2 border rounded">
                        <option value="" disabled selected>User Role</option>
                        <option value="1">Lecturer</option>
                    </select>
                </div>
                <div>
                    <select required id="faculty" name="faculty" class="mb-3 w-full p-2 border rounded">
                        <option value="" disabled selected>Faculty</option>
                        <option disabled value="MK-FMHS">M. Kandiah Faculty of Medicine and Health Sciences</option>
                        <option disabled value="LKC-FES">Lee Kong Chian Faculty of Engineering and Science</option>
                        <option disabled value="FEGT">Faculty of Engineering and Green Technology</option>
                        <option value="FICT">Faculty of Information and Communication Technology</option>
                        <option disabled value="FSc">Faculty of Science</option>
                        <option disabled value="FAM">Faculty of Accountancy and Management (Sungai Long Campus)</option>
                        <option disabled value="FBF">Faculty of Business and Finance (Kampar Campus)</option>
                        <option disabled value="FAS">Faculty of Arts and Social Science (Kampar Campus)</option>
                        <option disabled value="FCI">Faculty of Creative Industries</option>
                        <option disabled value="Postgraduate">Institute of Postgraduate Studies & Research</option>
                        <option disabled value="ICS">Institute of Chinese Studies</option>
                        <option disabled value="IMLD">Institute of Management and Leadership Development</option>
                        <option disabled value="CFS-KPR">Centre for Foundation Studies (Kampar Campus)</option>
                        <option disabled value="CFS-SGLONG">Centre for Foundation Studies (Sungai Long Campus)</option>
                        <option disabled value="CEE">Centre for Extension Education</option>
                        <option disabled value="CCCD">Centre for Corporate and Community Development</option>
                    </select>
                </div>

                <div>
                    <select required id="department" name="department" class="mb-3 w-full p-2 border rounded">
                        <option value="" disabled selected>Department</option>
                        <option value="DCCT">Department of Computer and Communication Technology</option>
                        <option value="DCS">Department of Computer Science</option>
                        <option value="DDET">Department of Digital Economy Technology</option>
                        <option value="DIS">Department of Information Systems</option>
                    </select>
                </div>

                <div>
                    <select required id="designation" name="designation" class="mb-3 w-full p-2 border rounded">
                        <option value="" disabled selected>Designation</option>
                        <option value="Lecturer">Lecturer</option>
                        <option value="Senior Lecturer">Senior Lecturer</option>
                    </select>
                </div>

                <input required type="text" id="phoneno" name="phoneno" class="mb-3 w-full p-2 border rounded"
                    placeholder="Contact Number (i.e.: 60123456789)">


                <button type="submit" required name="register" value="Register"
                    class="w-full bg-blue-800 text-white py-2 rounded hover:bg-blue-900">Register</button>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600">
                Existing user? <a href="login.php" class="text-blue-600 hover:underline">Click here to Login</a><br>
                <a href="register.php" class="text-blue-600 hover:underline">Continue as Student?</a>
            </p>

        </div>
    </div>
</body>

</html>