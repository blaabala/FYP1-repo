<?php
include("header_lecturer.php");


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_availability'])) {
        $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;

        if ($is_recurring) {
            $day_of_week = (int)$_POST['day_of_week'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

            // Validate times
            if (strtotime($end_time) <= strtotime($start_time)) {
                die("End time must be after start time.");
            }

            $query = "INSERT INTO lecturer_availability (lecturer_id, is_recurring, day_of_week, start_time, end_time, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $statement = $con->prepare($query);
            $statement->bind_param("iiissss", $lecturer_id, $is_recurring, $day_of_week, $start_time, $end_time, $start_date, $end_date);
        } else {
            $start_datetime = $_POST['start_datetime'];
            $end_datetime = $_POST['end_datetime'];

            // Validate datetimes
            if (strtotime($end_datetime) <= strtotime($start_datetime)) {
                die("End datetime must be after start datetime.");
            }

            $query = "INSERT INTO lecturer_availability (lecturer_id, is_recurring, start_datetime, end_datetime) VALUES (?, ?, ?, ?)";
            $statement = $con->prepare($query);
            $statement->bind_param("iiss", $lecturer_id, $is_recurring, $start_datetime, $end_datetime);
        }

        if ($statement->execute()) {
            echo "<p class='text-green-500'>Availability set successfully.</p>";
        } else {
            echo "<p class='text-red-500'>Error setting availability.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Availability</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function toggleAvailabilityType() {
            const isRecurring = document.getElementById('is_recurring').checked;
            document.getElementById('one-time-fields').classList.toggle('hidden', isRecurring);
            document.getElementById('recurring-fields').classList.toggle('hidden', !isRecurring);
        }
    </script>
</head>

<body class="bg-gray-100 font-merriweather">
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Set Availability</h2>

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
                <select name="day_of_week" class="border rounded p-2 w-full">
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

    <?php
    include("footer.php");
    ?>
</body>

</html>