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
    exit();
}
$email = $_SESSION['email'];

// Fetch admin data
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

// Check if the query returned any rows
$result = mysqli_fetch_assoc($query);
if (!$result) {
    echo "<script>
        alert('User not found. Please login again.');
        window.location.href = 'logout.php';
    </script>";
    exit();
}

// Assign variables after confirming a result exists
$res_id = $result['id'];
$res_username = $result['username'];
$res_email = $result['email'];
$res_role = $result['role_id'];
$res_role_name = $result['role_name'];
$res_department = $result['department'];
$res_contact = $result['contact_number'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = strtoupper($_POST['username']);
    $email = $_POST['email'];
    $current_password_input = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $phoneno = $_POST['phoneno'];

    $current_password_query = "SELECT password FROM users WHERE id = ?";
    $stmt = $con->prepare($current_password_query);
    $stmt->bind_param('i', $res_id);
    $stmt->execute();
    $stmt->bind_result($current_password_hash);
    $stmt->fetch();
    $stmt->close();

    // If new passwords match, validate the current password and update
    if (!empty($current_password_input)) {
        if (password_verify($current_password_input, $current_password_hash)) {
            if (!empty($new_password) && $new_password === $confirm_password) {
                $password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $_SESSION['error_message'] = "New passwords do not match!";
                header("Location: edit_profile_admin.php?id=$res_id");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Current password is incorrect!";
            header("Location: edit_profile_admin.php?id=$res_id");
            exit();
        }
    } else {
        $password = $current_password_hash;
    }

    $update_query = "UPDATE users SET username = ?, email = ?, password = ?, contact_number = ? WHERE id = ?";
    if ($stmt = $con->prepare($update_query)) {
        $stmt->bind_param('ssssi', $username, $email, $password, $phoneno, $res_id);
        if ($stmt->execute()) {
            $_SESSION['email'] = $email; // Update session email
            $_SESSION['success_message'] = "Profile updated successfully.";
            header("Location: home_admin.php"); // Redirect to home page
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating profile.";
            header("Location: edit_profile_admin.php?id=$res_id");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing query.";
        header("Location: edit_profile_admin.php?id=$res_id");
        exit();
    }
}

// Display success or error messages
if (isset($_SESSION['success_message'])) {
    echo "<script>alert('Profile updated successfully!');</script>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<script>alert('Failed to update profile, please try again!');</script>";
    unset($_SESSION['error_message']);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    body {
        background: #98C1D9;
    }

    .main-box {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .container-tight {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    .form-box {
        max-width: 500px;
        margin: 0 auto;
        background: white;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .roboto-black-italic {
        font-family: 'Merriweather', serif;
        font-weight: 900;
        font-style: italic;
        font-size: 1.5rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .field {
        margin-bottom: 1.25rem;
    }

    .field label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }

    .field input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 0.5rem;
        font-size: 1rem;
    }

    .btn {
        background-color: #1d4ed8;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.3s;
    }

    .btn:hover {
        background-color: #1e40af;
    }

    /* Header fixes */
    .navbar {
        background-color: #3D5A80;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .navdiv {
        display: flex;
        align-items: center;
        width: 100%;
        justify-content: space-between;
    }

    .image-container {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .logo-text {
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
        text-decoration: none;
    }

    .navdiv ul {
        list-style: none;
        display: flex;
        gap: 1.5rem;
        margin: 0;
        padding: 0;
    }

    .navdiv ul li a {
        color: white;
        text-decoration: none;
        font-size: 1rem;
        transition: color 0.3s;
    }

    .navdiv ul li a:hover {
        color: #ecf0f1;
    }

    .logout-btn {
        background-color: #e74c3c;
        color: white;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .logout-btn:hover {
        background-color: #c0392b;
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
                    <li><a href="home_admin.php">Home</a></li>
                    <li><a href="lecturer_view_admin.php">Lecturers</a></li>
                    <li><a href="student_view_admin.php">Students</a></li>
                    <li><a href="appointment_view_admin.php">Appointments</a></li>
                    <li><a href="edit_profile_admin.php?id=<?php echo $res_id; ?>">Edit Profile</a></li>
                    <li><button><a href="logout.php" class="logout-btn">Logout</a></button></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container-tight mx-auto">
        <div class="container-tight mx-auto">
            <div class="form-box">
                <header class="roboto-black-italic">Edit User Profile</header>
                <form action="" method="post">
                    <div class="field input">
                        <label for="username">User Name</label>
                        <input required type="text" id="username" name="username"
                            value="<?php echo htmlspecialchars($res_username); ?>" placeholder="Enter your full name">
                    </div>
                    <div class="field input">
                        <label for="email">User Email</label>
                        <input required type="text" id="email" name="email"
                            value="<?php echo htmlspecialchars($res_email); ?>" placeholder="Enter your email address">
                    </div>
                    <div class="field input">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password"
                            placeholder="Enter your current password">
                    </div>
                    <div class="field input">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password"
                            placeholder="Enter your new password">
                    </div>
                    <div class="field input">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirm your new password">
                    </div>
                    <div class="field input">
                        <label for="phoneno">Contact Number</label>
                        <input required type="text" id="phoneno" name="phoneno"
                            value="<?php echo htmlspecialchars($res_contact); ?>" placeholder="i.e.: +60123456789">
                    </div>
                    <div class="field">
                        <input type="submit" name="submit" value="Submit" class="btn">
                    </div>
                </form>
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
                    <li><a href="home_admin.php">Home</a></li>
                    <li><a href="lecturer_view_admin.php">Lecturers</a></li>
                    <li><a href="student_view_admin.php">Students</a></li>
                    <li><a href="appointment_view_admin.php">Appointments</a></li>
                    <li><a href="edit_profile_admin.php?id=<?php echo $res_id; ?>">Edit Profile</a></li>
                </ul>
            </div>
            <div class="footer-bottom">
                <div class="text-center">
                    <p class="text-sm">Contact us: <a href="tel:+60123456789"
                            class="underline hover:text-blue-200 transition-colors duration-300">+60123456789</a> | <a
                            href="mailto:info@utarhospital.my"
                            class="underline hover:text-blue-200 transition-colors duration-300">info@ams.1utar.my</a>
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm">© <?php echo date('Y'); ?> LEE JUN KHANG. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>