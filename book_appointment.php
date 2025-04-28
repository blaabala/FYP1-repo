<?php
session_start();
include("database.php");

// Set the time zone for PHP
date_default_timezone_set('Asia/Kuala_Lumpur');

// Check if the user is logged in (student)
$student_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
if ($student_id === 0) {
    die("Please log in as a student to book an appointment.");
}

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['book'])) {
    die("Invalid request.");
}

$lecturer_id = isset($_POST['lecturer_id']) ? (int)$_POST['lecturer_id'] : 0;
$start_datetime = isset($_POST['start_datetime']) ? $_POST['start_datetime'] : null;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';

if ($lecturer_id === 0 || !$start_datetime || !$title || !$description || !$location) {
    $_SESSION['error_message'] = "All fields are required.";
    header("Location: calendar.php?lecturer_id=$lecturer_id");
    exit;
}

// Convert the start_datetime from UTC to Asia/Kuala_Lumpur
$start = new DateTime($start_datetime, new DateTimeZone('UTC'));
$start->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
$start_datetime_adjusted = $start->format('Y-m-d H:i:s');

// Calculate end_datetime (30 minutes after start)
$end = clone $start;
$end->modify('+30 minutes');
$end_datetime_adjusted = $end->format('Y-m-d H:i:s');

// Validate the time slot against lecturer availability
$query = "SELECT * FROM lecturer_availability WHERE lecturer_id = ?";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
$availabilities = [];
while ($row = $result->fetch_assoc()) {
    $availabilities[] = $row;
}

$is_available = false;
foreach ($availabilities as $a) {
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
    $_SESSION['error_message'] = "The selected time slot is not available.";
    header("Location: calendar.php?lecturer_id=$lecturer_id");
    exit;
}

// Check for existing bookings
$query = "SELECT id FROM appointments WHERE lecturer_id = ? AND ((start_datetime < ? AND end_datetime > ?) OR (start_datetime < ? AND end_datetime > ?))";
$statement = $con->prepare($query);
$statement->bind_param("issss", $lecturer_id, $end_datetime_adjusted, $start_datetime_adjusted, $start_datetime_adjusted, $end_datetime_adjusted);
$statement->execute();
$result = $statement->get_result();
if ($result->num_rows > 0) {
    $_SESSION['error_message'] = "The selected time slot is already booked.";
    header("Location: calendar.php?lecturer_id=$lecturer_id");
    exit;
}

// Insert the appointment
$status = 'Confirmed';
$query = "INSERT INTO appointments (student_id, lecturer_id, title, start_datetime, end_datetime, description, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$statement = $con->prepare($query);
$statement->bind_param("iissssss", $student_id, $lecturer_id, $title, $start_datetime_adjusted, $end_datetime_adjusted, $description, $location, $status);
if ($statement->execute()) {
    $_SESSION['success_message'] = "Appointment booked successfully.";
} else {
    $_SESSION['error_message'] = "Error booking appointment.";
}
$statement->close();

header("Location: calendar.php?lecturer_id=$lecturer_id");
exit;