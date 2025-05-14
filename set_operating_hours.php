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

$result = mysqli_fetch_assoc($query);
if (!$result) {
    echo "<script>
        alert('User not found. Please login again.');
        window.location.href = 'logout.php';
    </script>";
    exit();
}

$res_id = $result['id'];
$res_username = $result['username'];
$res_email = $result['email'];
$res_role = $result['role_id'];
$res_role_name = $result['role_name'];
$res_department = $result['department'];
$res_contact = $result['contact_number'];

// Fetch current operating hours
$query = "SELECT start_time, end_time FROM operating_hours WHERE id = 1";
$hours_result = mysqli_query($con, $query);
$operating_hours = mysqli_fetch_assoc($hours_result);
if (!$operating_hours) {
    // If no record exists, insert default values
    $query = "INSERT INTO operating_hours (id, start_time, end_time) VALUES (1, '08:00:00', '17:00:00')";
    mysqli_query($con, $query);
    $operating_hours = ['start_time' => '08:00:00', 'end_time' => '17:00:00'];
}

$start_time = $operating_hours['start_time'];
$end_time = $operating_hours['end_time'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_start_time = trim($_POST['start_time'] ?? '');
    $new_end_time = trim($_POST['end_time'] ?? '');

    // Validate and normalize time format to HH:MM:SS
    $time_pattern = '/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])(?::([0-5][0-9]))?$/';
    if (
        preg_match($time_pattern, $new_start_time, $start_matches) &&
        preg_match($time_pattern, $new_end_time, $end_matches)
    ) {
        // Normalize to HH:MM:SS format
        $normalized_start_time = sprintf(
            '%02d:%02d:00',
            $start_matches[1], // Hours
            $start_matches[2]  // Minutes
        );
        $normalized_end_time = sprintf(
            '%02d:%02d:00',
            $end_matches[1],   // Hours
            $end_matches[2]    // Minutes
        );

        // Convert to DateTime objects for comparison
        $start = DateTime::createFromFormat('H:i:s', $normalized_start_time);
        $end = DateTime::createFromFormat('H:i:s', $normalized_end_time);

        if ($start && $end && $start < $end) {
            $query = "UPDATE operating_hours SET start_time = ?, end_time = ? WHERE id = 1";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $normalized_start_time, $normalized_end_time);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Operating hours updated successfully!";
                // Update the displayed values
                $start_time = $normalized_start_time;
                $end_time = $normalized_end_time;
            } else {
                $_SESSION['error_message'] = "Failed to update operating hours. Please try again. Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "End time must be after start time.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid time format. Please use HH:MM or HH:MM:SS (24-hour format).";
    }

    // Debug: Log the input values
    error_log("Submitted start_time: $new_start_time, end_time: $new_end_time");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Set Operating Hours</title>
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
                    <li><a href="set_operating_hours.php">Set Operating Hours</a></li>
                    <li><a href="edit_profile_admin.php?id=<?php echo $res_id; ?>">Edit Profile</a></li>
                    <li><button><a href="logout.php" class="logout-btn">Logout</a></button></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container-tight mx-auto">
        <div id="current-datetime" class="font-semibold mt-2"></div>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Set Operating Hours</h1>
            <p class="text-gray-600">Adjust the operating hours for the Appointment Management System.</p>
        </div>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <form method="post" action="">
                <div class="mb-4">
                    <label for="start_time" class="block text-gray-700 font-semibold mb-2">Start Time (HH:MM or
                        HH:MM:SS, 24-hour format):</label>
                    <input type="text" name="start_time" id="start_time"
                        value="<?php echo htmlspecialchars($start_time); ?>" class="w-full p-2 border rounded"
                        placeholder="e.g., 08:00 or 08:00:00" required>
                </div>
                <div class="mb-4">
                    <label for="end_time" class="block text-gray-700 font-semibold mb-2">End Time (HH:MM or HH:MM:SS,
                        24-hour format):</label>
                    <input type="text" name="end_time" id="end_time" value="<?php echo htmlspecialchars($end_time); ?>"
                        class="w-full p-2 border rounded" placeholder="e.g., 17:00 or 17:00:00" required>
                </div>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Update
                    Operating Hours</button>
            </form>
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
                    <li><a href="set_operating_hours.php">Set Operating Hours</a></li>
                    <li><?php echo "<a href='edit_profile_admin.php?id=$res_id'>Edit Profile</a>"; ?></li>
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