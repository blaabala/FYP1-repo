<?php
include("header_lecturer.php");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug session variables
var_dump($_SESSION);

// Check if the user is a lecturer
if (!isset($res_role) || $res_role != 1) {
    die("Access denied. You must be a lecturer to set availability.");
}

// Get lecturer ID from session
$lecturer_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
if ($lecturer_id === 0) {
    die("Invalid lecturer ID. Please log in again.");
}

// Debug form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Form submitted<br>";
    var_dump($_POST);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_availability'])) {
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;

    try {
        if ($is_recurring) {
            $day_of_week = isset($_POST['day_of_week']) ? (int)$_POST['day_of_week'] : null;
            $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
            $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : null;
            $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

            // Validate required fields
            if (is_null($day_of_week) || is_null($start_time) || is_null($end_time)) {
                throw new Exception("All recurring availability fields are required.");
            }

            // Validate times
            if (strtotime($end_time) <= strtotime($start_time)) {
                throw new Exception("End time must be after start time.");
            }

            // Validate start and end dates if provided
            if ($start_date && $end_date && strtotime($end_date) <= strtotime($start_date)) {
                throw new Exception("End date must be after start date.");
            }

            // Add seconds to time for database compatibility
            $start_time = $start_time . ":00";
            $end_time = $end_time . ":00";

            $query = "INSERT INTO lecturer_availability (lecturer_id, is_recurring, day_of_week, start_time, end_time, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $statement = $con->prepare($query);
            if (!$statement) {
                throw new Exception("Database prepare failed: " . $con->error);
            }
            $statement->bind_param("iiissss", $lecturer_id, $is_recurring, $day_of_week, $start_time, $end_time, $start_date, $end_date);
        } else {
            $start_datetime = isset($_POST['start_datetime']) ? $_POST['start_datetime'] : null;
            $end_datetime = isset($_POST['end_datetime']) ? $_POST['end_datetime'] : null;

            // Validate required fields
            if (is_null($start_datetime) || is_null($end_datetime)) {
                throw new Exception("All one-time availability fields are required.");
            }

            // Validate datetimes
            if (strtotime($end_datetime) <= strtotime($start_datetime)) {
                throw new Exception("End datetime must be after start datetime.");
            }

            // Convert datetime format for database (from YYYY-MM-DDTHH:MM to YYYY-MM-DD HH:MM:SS)
            $start_datetime = str_replace("T", " ", $start_datetime) . ":00";
            $end_datetime = str_replace("T", " ", $end_datetime) . ":00";

            $query = "INSERT INTO lecturer_availability (lecturer_id, is_recurring, start_datetime, end_datetime) VALUES (?, ?, ?, ?)";
            $statement = $con->prepare($query);
            if (!$statement) {
                throw new Exception("Database prepare failed: " . $con->error);
            }
            $statement->bind_param("iiss", $lecturer_id, $is_recurring, $start_datetime, $end_datetime);
        }

        if ($statement->execute()) {
            $message = "Availability set successfully.";
            // Redirect to calendar.php
            header("Location: calendar.php?lecturer_id=$lecturer_id&message=" . urlencode($message));
            exit;
        } else {
            throw new Exception("Error setting availability: " . $statement->error);
        }
        $statement->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!-- Remove <html>, <head>, and <body> since they are already in header_lecturer.php -->
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Set Availability</h2>

    <?php if (isset($message)): ?>
        <p class="text-green-500 mb-4"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="text-red-500 mb-4"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="set_availability.php" method="post" class="bg-white p-4 rounded shadow">
        <div class="mb-4">
            <label class="block text-gray-700">
                <input type="checkbox" id="is_recurring" name="is_recurring" onchange="toggleAvailabilityType()">
                Recurring Availability (e.g., every Monday)
            </label>
        </div>

        <!-- One-time availability fields -->
        <div id="one-time-fields" class="mb-4">
            <label class="block text-gray-700">Start Date and Time:</label>
            <input type="datetime-local" name="start_datetime" class="border rounded p-2 w-full" required>
            <label class="block text-gray-700 mt-2">End Date and Time:</label>
            <input type="datetime-local" name="end_datetime" class="border rounded p-2 w-full" required>
        </div>

        <!-- Recurring availability fields -->
        <div id="recurring-fields" class="mb-4 hidden">
            <label class="block text-gray-700">Day of the Week:</label>
            <select name="day_of_week" class="border rounded p-2 w-full" required>
                <option value="1">Monday</option>
                <option value="2">Tuesday</option>
                <option value="3">Wednesday</option>
                <option value="4">Thursday</option>
                <option value="5">Friday</option>
                <option value="6">Saturday</option>
                <option value="0">Sunday</option>
            </select>

            <label class="block text-gray-700 mt-2">Start Time:</label>
            <input type="time" name="start_time" class="border rounded p-2 w-full" required>

            <label class="block text-gray-700 mt-2">End Time:</label>
            <input type="time" name="end_time" class="border rounded p-2 w-full" required>

            <label class="block text-gray-700 mt-2">Start Date (Optional):</label>
            <input type="date" name="start_date" class="border rounded p-2 w-full">

            <label class="block text-gray-700 mt-2">End Date (Optional):</label>
            <input type="date" name="end_date" class="border rounded p-2 w-full">
        </div>

        <button type="submit" name="set_availability"
            class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900">Set Availability</button>
    </form>
</div>

<script>
    function toggleAvailabilityType() {
        const isRecurring = document.getElementById('is_recurring').checked;
        document.getElementById('one-time-fields').classList.toggle('hidden', isRecurring);
        document.getElementById('recurring-fields').classList.toggle('hidden', !isRecurring);
    }
</script>

<?php
include("footer.php");
?>