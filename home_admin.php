<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
include("database.php");

$admin_id = $_SESSION['id'] ?? null;

if (!$admin_id) {
    echo "<script>
        alert('Please login to continue.');
        window.location.href = 'login.php';
    </script>";
    exit(); // Prevent further code execution
}
$email = $_SESSION['email'];

$query = mysqli_query($con, "SELECT users.id, 
users.username, 
users.email, 
users.contact_number, 
users.role_id,
roles.role_name, 
admins.department

FROM users
INNER JOIN roles ON users.role_id = roles.id
INNER JOIN admins ON admins.user_id = users.id
WHERE users.email = '$email'");

while ($result = mysqli_fetch_assoc($query)) {
    $res_id = $result['id'];
    $res_username = $result['username'];
    $res_email = $result['email'];
    $res_role = $result['role_id'];
    $res_role_name = $result['role_name'];
    $res_department = $result['department'];
    $res_contact = $result['contact_number'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>AMS</title>
    <meta charset="utf-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    /* Ensure the layout takes up the full viewport height */
    .main-box {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Reduce padding for the container holding the buttons, but leave a small gap */
    .container-tight {
        padding-top: 0.75rem;
        /* 12px, reduced from 1rem, leaves a small gap */
        padding-bottom: 0.75rem;
        /* 12px, reduced from 1rem */
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    </style>
</head>

<body class="main-box top">
    <header>
        <nav class="navbar">
            <div class="navdiv">
                <div class="image-container">
                    <a href="home_admin.php"><img src="assets/images/logo.png" alt="logo" class="nav-logo"
                            style="width: 70px; height: auto;"></a>
                    <a href="home_admin.php" class="logo-text">Appointment Management System</a>

                </div>
                <ul>
                    <li>
                        <a href="home_admin.php">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="lecturer_view_admin.php">
                            Lecturers
                        </a>
                    </li>
                    <li>
                        <a href="student_view_admin.php">
                            Students
                        </a>
                    </li>
                    <li>
                        <a href="appointment_view_admin.php">
                            Appointments
                        </a>
                    </li>
                    <li>
                        <?php echo "<a href='edit_profile_admin.php?id=$res_id'>Edit Profile</a>" ?>
                    </li>
                    <button><a href="logout.php" class="logout-btn">Logout</a></button>
                </ul>
            </div>
        </nav>
    </header>

    <div class="top">
        <div class="box">
            <p>Welcome, <strong><?php echo $res_username . ' (' . $res_role_name . ')'; ?></strong></p>
        </div>
        <div class="box">
            <p>Email Address: <strong><?php echo $res_email ?></strong></p>
        </div>
        <div class="box">
            <p>Current Date & Time:<br><strong id="datetime"></strong></p>
        </div>
    </div>
    <div class="container-tight mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Clickable Lecturer Box -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 text-center">
                <a href="lecturer_view_admin.php"
                    class="block h-full text-blue-700 text-3xl font-bold hover:text-blue-900">
                    <i class="fa-solid fa-chalkboard-teacher"></i> Lecturers
                </a>
            </div>

            <!-- Clickable Students Box -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 text-center">
                <a href="student_view_admin.php"
                    class="block h-full text-blue-700 text-3xl font-bold hover:text-blue-900">
                    <i class="fa-solid fa-users"></i> Students
                </a>
            </div>

            <!-- Clickable Appointments Box -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 text-center">
                <a href="appointment_view_admin.php"
                    class="block h-full text-blue-700 text-3xl font-bold hover:text-blue-900">
                    <i class="fa-solid fa-calendar-check"></i> Appointments
                </a>
            </div>
        </div>
    </div>
    </main>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-icons">
                <a href=""><i class="fa-brands fa-facebook"></i></a>
                <a href=""><i class="fa-brands fa-instagram"></i></a>
                <a href=""><i class="fa-brands fa-google-plus"></i></a>
                <a href=""><i class="fa-brands fa-youtube"></i></a>
            </div>
            <div class="footer-nav">
                <ul>
                    <li>
                        <a href="home_admin.php">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="lecturer_view_admin.php">
                            Lecturers
                        </a>
                    </li>
                    <li>
                        <a href="student_view_admin.php">
                            Students
                        </a>
                    </li>
                    <li>
                        <a href="appointment_view_admin.php">
                            Appointments
                        </a>
                    </li>
                    <li>
                        <?php echo "<a href='edit_profile_admin.php?id=$res_id'>Edit Profile</a>" ?>
                    </li>
                </ul>
            </div>
            <div class=" footer-bottom">
                <p>&copy; 2024 LEE JUN KHANG. All rights reserved. </p>
            </div>
        </div>
    </footer>
    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>