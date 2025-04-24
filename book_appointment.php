<?php
include("header.php");

// Get form data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['book'])) {
    die("Invalid request.");
}

$lecturer_id = (int)$_POST['lecturer_id'];
$start_datetime = $_POST['start_datetime'];

// Validate input
if (empty($lecturer_id) || empty($student_id) || empty($start_datetime)) {
    die("Missing required fields.");
}

// Calculate end datetime (30-minute slot)
$start = new DateTime($start_datetime);
$end = clone $start;
$end->modify('+30 minutes');
$end_datetime = $end->format('Y-m-d\TH:i:s');

// Validate lecturer and student exist
$query = "SELECT id FROM lecturers WHERE id = ?";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
if ($result->num_rows === 0) {
    die("Invalid lecturer ID.");
}

$query = "SELECT id FROM students WHERE id = ?";
$statement = $con->prepare($query);
$statement->bind_param("i", $student_id);
$statement->execute();
$result = $statement->get_result();
if ($result->num_rows === 0) {
    die("Invalid student ID.");
}

// Check if the selected slot is on a weekend
$selected_day = $start->format('w'); // 0 = Sunday, 6 = Saturday
if ($selected_day == 0 || $selected_day == 6) {
    die("Appointments cannot be booked on Saturdays or Sundays.");
}

$now = new DateTime();
if ($start < $now) {
    die("You cannot book an appointment in the past.");
}

// Fetch lecturer availability (both one-time and recurring)
$availabilities = [];
$query = "SELECT * FROM lecturer_availability WHERE lecturer_id = ?";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_assoc()) {
    $availabilities[] = $row;
}

// Check if the selected slot is within availability
$is_available = false;
foreach ($availabilities as $a) {
    // echo "<pre>";
    // echo "Checking availability entry: ";
    // print_r($a);
    // echo "Start: " . $start->format('Y-m-d H:i:s') . "\n";
    // echo "End: " . $end->format('Y-m-d H:i:s') . "\n";
    // echo "</pre>";
    if ($a['is_recurring']) {
        $day_of_week = (int)$a['day_of_week'];
        $start_time = $a['start_time'];
        $end_time = $a['end_time'];
        $recurring_start_date = $a['start_date'] ? new DateTime($a['start_date']) : null;
        $recurring_end_date = $a['end_date'] ? new DateTime($a['end_date']) : null;

        if ($start->format('w') !== (string)$day_of_week) continue;
        if ($recurring_start_date && $start < $recurring_start_date) continue;
        if ($recurring_end_date && $start > $recurring_end_date) continue;

        $slot_start = clone $start;
        $slot_start->setTime(...explode(':', $start_time));
        $slot_end = clone $start;
        $slot_end->setTime(...explode(':', $end_time));

        if ($start >= $slot_start && $end <= $slot_end) {
            $is_available = true;
            break;
        }
    } else {
        $avail_start = new DateTime($a['start_datetime']);
        $avail_end = new DateTime($a['end_datetime']);
        if ($start >= $avail_start && $end <= $avail_end) {
            $is_available = true;
            break;
        }
    }
}

if (!$is_available) {
    die("The selected time slot is not available.");
}

// Check for existing bookings
$query = "SELECT id FROM appointments WHERE lecturer_id = ? AND ((start_datetime < ? AND end_datetime > ?) OR (start_datetime < ? AND end_datetime > ?))";
$statement = $con->prepare($query);
$statement->bind_param("issss", $lecturer_id, $end_datetime, $start_datetime, $start_datetime, $end_datetime);
$statement->execute();
$result = $statement->get_result();
if ($result->num_rows > 0) {
    die("The selected time slot is already booked.");
}

// Insert the appointment
$query = "INSERT INTO appointments (lecturer_id, student_id, start_datetime, end_datetime, status) VALUES (?, ?, ?, ?, 'Pending')";
$statement = $con->prepare($query);
$statement->bind_param("iiss", $lecturer_id, $student_id, $start_datetime, $end_datetime);
if ($statement->execute()) {
    $message = "Appointment booked successfully.";
    header("Location: calendar.php?lecturer_id=$lecturer_id&message=" . urlencode($message));
    exit;
} else {
    die("Error booking appointment.");
}
