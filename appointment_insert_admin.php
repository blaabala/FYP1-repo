<?php
session_start();
include("database.php");

// Function to validate 00/30-minute intervals
function validateTimeInterval($datetime)
{
    $date = new DateTime($datetime);
    $minutes = (int)$date->format('i');
    return $minutes === 0 || $minutes === 30;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate inputs
        $title = trim($_POST['title'] ?? '');
        $student_email = filter_var(trim($_POST['student_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $lecturer_email = filter_var(trim($_POST['lecturer_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $start_datetime = trim($_POST['start_datetime'] ?? '');
        $end_datetime = trim($_POST['end_datetime'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Check for empty or invalid fields
        $errors = [];
        if (empty($title)) $errors[] = "Title is required.";
        if (!$student_email) $errors[] = "Valid student email is required.";
        if (!$lecturer_email) $errors[] = "Valid lecturer email is required.";
        if (empty($start_datetime)) $errors[] = "Start date and time are required.";
        if (empty($end_datetime)) $errors[] = "End date and time are required.";
        if (empty($location)) $errors[] = "Location is required.";
        if (empty($description)) $errors[] = "Description is required.";

        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        // Validate time intervals
        if (!validateTimeInterval($start_datetime) || !validateTimeInterval($end_datetime)) {
            throw new Exception("Time must be on the hour (e.g., 10:00) or half-hour (e.g., 10:30).");
        }

        // Validate start_datetime is before end_datetime
        $start = new DateTime($start_datetime);
        $end = new DateTime($end_datetime);
        if ($start >= $end) {
            throw new Exception("Start time must be earlier than end time.");
        }

        // Fetch student and lecturer IDs
        $query = "SELECT s.id AS student_id FROM students s JOIN users u ON s.user_id = u.id WHERE u.email = ?";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error (student query): " . $con->error);
        }
        $stmt->bind_param("s", $student_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Student not found with email: $student_email");
        }
        $student = $result->fetch_assoc();
        $student_id = $student['student_id'];
        $stmt->close();

        $query = "SELECT l.id AS lecturer_id FROM lecturers l JOIN users u ON l.user_id = u.id WHERE u.email = ?";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error (lecturer query): " . $con->error);
        }
        $stmt->bind_param("s", $lecturer_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Lecturer not found with email: $lecturer_email");
        }
        $lecturer = $result->fetch_assoc();
        $lecturer_id = $lecturer['lecturer_id'];
        $stmt->close();

        // Insert appointment with status 'Confirmed'
        $query = "INSERT INTO appointments (student_id, lecturer_id, title, start_datetime, end_datetime, description, location, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Confirmed')";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error (appointment query): " . $con->error);
        }
        $stmt->bind_param("iisssss", $student_id, $lecturer_id, $title, $start_datetime, $end_datetime, $description, $location);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Appointment created successfully with status 'Confirmed'.";
        } else {
            throw new Exception("Error creating appointment: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    header("Location: appointment_view_admin.php");
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: appointment_view_admin.php");
    exit();
}
