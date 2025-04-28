<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
include("database.php");

$user_id = $_SESSION['id'] ?? null;
$email = $_SESSION['email'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;

if (!$user_id || !$email) {
    echo "<script>
        alert('Please login to continue.');
        window.location.href = 'login_lecturer.php';
    </script>";
    exit();
}

// Fetch user details with a prepared statement
$query = "SELECT users.*, roles.role_name 
          FROM users 
          JOIN roles ON users.role_id = roles.id 
          WHERE users.email = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        alert('User not found. Please login again.');
        window.location.href = 'login_lecturer.php';
    </script>";
    exit();
}

$user = $result->fetch_assoc();
$res_id = $user['id'];
$res_username = $user['username'];
$res_email = $user['email'];
$res_role = $user['role_id'];
$res_role_name = $user['role_name'];
$res_faculty = $user['faculty'];
$res_contact = $user['contact_number'];

// Verify that the user is a lecturer (case-insensitive comparison)
if (strtolower($res_role_name) !== 'lecturer') {
    echo "<script>
        alert('You must be a lecturer to set availability.');
        window.location.href = 'home.php';
    </script>";
    exit();
}

// Fetch the lecturer's ID from the lecturers table
$query = "SELECT id FROM lecturers WHERE user_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        alert('Lecturer record not found. Please contact the administrator.');
        window.location.href = 'home_lecturer.php';
    </script>";
    exit();
}

$lecturer = $result->fetch_assoc();
$lecturer_id = $lecturer['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_availability'])) {
    try {
        $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
        $start_datetime = $end_datetime = null;
        $day_of_week = $start_time = $end_time = $start_date = $end_date = null;

        if ($is_recurring) {
            $day_of_week = isset($_POST['day_of_week']) ? (int)$_POST['day_of_week'] : -1;
            $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
            $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : null;
            $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

            // Validate recurring fields
            if ($day_of_week < 0 || $day_of_week > 6 || !$start_time || !$end_time) {
                $_SESSION['error_message'] = "Please fill in all required fields for recurring availability.";
                header("Location: set_availability.php");
                exit;
            }

            // Validate time format
            if (!preg_match("/^[0-2][0-9]:[0-5][0-9]$/", $start_time) || !preg_match("/^[0-2][0-9]:[0-5][0-9]$/", $end_time)) {
                $_SESSION['error_message'] = "Invalid time format. Please use HH:MM (24-hour format).";
                header("Location: set_availability.php");
                exit;
            }

            // Ensure end_time is after start_time
            $start_time_obj = DateTime::createFromFormat('H:i', $start_time);
            $end_time_obj = DateTime::createFromFormat('H:i', $end_time);
            if ($start_time_obj >= $end_time_obj) {
                $_SESSION['error_message'] = "End time must be after start time.";
                header("Location: set_availability.php");
                exit;
            }

            // Validate dates if provided
            if ($start_date && $end_date) {
                $start_date_obj = new DateTime($start_date);
                $end_date_obj = new DateTime($end_date);
                if ($start_date_obj >= $end_date_obj) {
                    $_SESSION['error_message'] = "End date must be after start date.";
                    header("Location: set_availability.php");
                    exit;
                }
            }
        } else {
            $start_datetime = isset($_POST['start_datetime']) ? $_POST['start_datetime'] : null;
            $end_datetime = isset($_POST['end_datetime']) ? $_POST['end_datetime'] : null;

            // Validate non-recurring fields
            if (!$start_datetime || !$end_datetime) {
                $_SESSION['error_message'] = "Please fill in all required fields for non-recurring availability.";
                header("Location: set_availability.php");
                exit;
            }

            // Validate datetime
            $start_dt_obj = new DateTime($start_datetime);
            $end_dt_obj = new DateTime($end_datetime);
            if ($start_dt_obj >= $end_dt_obj) {
                $_SESSION['error_message'] = "End datetime must be after start datetime.";
                header("Location: set_availability.php");
                exit;
            }
        }

        // Insert into lecturer_availability table
        $query = "INSERT INTO lecturer_availability (lecturer_id, start_datetime, end_datetime, is_recurring, day_of_week, start_time, end_time, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bind_param("isssissss", $lecturer_id, $start_datetime, $end_datetime, $is_recurring, $day_of_week, $start_time, $end_time, $start_date, $end_date);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Availability set successfully.";
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error setting availability: " . $e->getMessage();
    }
    header("Location: set_availability.php");
    exit;
}

// Fetch existing availability for display
$query = "SELECT * FROM lecturer_availability WHERE lecturer_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
$availabilities = [];
while ($row = $result->fetch_assoc()) {
    $availabilities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Set Availability</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
    html,
    body {
        height: 100%;
        margin: 0;
    }
    </style>
    <script>
    function toggleAvailabilityFields() {
        const isRecurring = document.getElementById('is_recurring').checked;
        document.getElementById('non-recurring-fields').classList.toggle('hidden', isRecurring);
        document.getElementById('recurring-fields').classList.toggle('hidden', !isRecurring);
    }
    </script>
</head>

<body class="bg-gray-100 font-merriweather">
    <header class="bg-gradient-to-r from-blue-600 to-indigo-800 text-white shadow-lg">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="home_lecturer.php">
                        <img src="assets/images/logo.png" alt="logo"
                            class="w-16 h-auto transition-transform transform hover:scale-110">
                    </a>
                    <a href="home_lecturer.php"
                        class="text-2xl font-bold tracking-wide hover:text-blue-200 transition-colors">
                        Appointment Management System
                    </a>
                </div>
                <div>
                    <ul class="flex space-x-6 items-center">
                        <li>
                            <a href="home_lecturer.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Home</a>
                        </li>
                        <li>
                            <a href="appointment_view_lecturer.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Appointments</a>
                        </li>
                        <li>
                            <a href="set_availability.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Set
                                Availability</a>
                        </li>
                        <li>
                            <a href="edit_profile.php?id=<?php echo htmlspecialchars($res_id); ?>"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Edit
                                Profile</a>
                        </li>
                        <li>
                            <a href="logout.php"
                                class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors duration-300">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Set Availability</h2>

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

        <!-- Form to set availability -->
        <form action="set_availability.php" method="post" class="bg-white p-4 rounded shadow">
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="is_recurring" name="is_recurring" onchange="toggleAvailabilityFields()"
                        class="form-checkbox">
                    <span class="ml-2">Recurring Availability</span>
                </label>
            </div>

            <!-- Non-recurring fields -->
            <div id="non-recurring-fields" class="">
                <div class="mb-4">
                    <label for="start_datetime" class="block text-gray-700">Start Date and Time:</label>
                    <input type="datetime-local" id="start_datetime" name="start_datetime"
                        class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label for="end_datetime" class="block text-gray-700">End Date and Time:</label>
                    <input type="datetime-local" id="end_datetime" name="end_datetime"
                        class="w-full p-2 border rounded">
                </div>
            </div>

            <!-- Recurring fields -->
            <div id="recurring-fields" class="hidden">
                <div class="mb-4">
                    <label for="day_of_week" class="block text-gray-700">Day of the Week:</label>
                    <select id="day_of_week" name="day_of_week" class="w-full p-2 border rounded">
                        <option value="0">Sunday</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="start_time" class="block text-gray-700">Start Time (HH:MM, 24-hour format):</label>
                    <input type="time" id="start_time" name="start_time" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label for="end_time" class="block text-gray-700">End Time (HH:MM, 24-hour format):</label>
                    <input type="time" id="end_time" name="end_time" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="start_date" class="block text-gray-700">Recurring Start Date (optional):</label>
                    <input type="date" id="start_date" name="start_date" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="end_date" class="block text-gray-700">Recurring End Date (optional):</label>
                    <input type="date" id="end_date" name="end_date" class="w-full p-2 border rounded">
                </div>
            </div>

            <button type="submit" name="set_availability"
                class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900">Set Availability</button>
        </form>

        <!-- Display existing availability -->
        <h3 class="text-xl font-semibold mt-6 mb-2">Existing Availability</h3>
        <?php if (count($availabilities) > 0): ?>
        <ul class="bg-white p-4 rounded shadow">
            <?php foreach ($availabilities as $avail): ?>
            <li class="mb-2">
                <?php
                        if ($avail['is_recurring']) {
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            $day_of_week = $days[$avail['day_of_week']];
                            $start_time = date("h:i A", strtotime($avail['start_time']));
                            $end_time = date("h:i A", strtotime($avail['end_time']));
                            $recurring_period = '';
                            if ($avail['start_date'] && $avail['end_date']) {
                                $start_date = date("d M Y", strtotime($avail['start_date']));
                                $end_date = date("d M Y", strtotime($avail['end_date']));
                                $recurring_period = " (from $start_date to $end_date)";
                            }
                            echo "Every $day_of_week, $start_time - $end_time$recurring_period";
                        } else {
                            $start_datetime = date("d M Y, h:i A", strtotime($avail['start_datetime']));
                            $end_datetime = date("d M Y, h:i A", strtotime($avail['end_datetime']));
                            echo "$start_datetime - $end_datetime";
                        }
                        ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-gray-700">No availability set.</p>
        <?php endif; ?>
    </div>

    <?php include("footer.php"); ?>
</body>

</html>