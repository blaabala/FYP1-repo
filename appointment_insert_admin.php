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
        $title = $_POST['title'] ?? '';
        $student_email = $_POST['student_email'] ?? '';
        $lecturer_email = $_POST['lecturer_email'] ?? '';
        $start_datetime = $_POST['start_datetime'] ?? '';
        $end_datetime = $_POST['end_datetime'] ?? '';
        $location = $_POST['location'] ?? '';
        $description = $_POST['description'] ?? '';

        if (!$title || !$student_email || !$lecturer_email || !$start_datetime || !$end_datetime || !$location || !$description) {
            throw new Exception("All fields are required.");
        }

        // Validate time intervals
        if (!validateTimeInterval($start_datetime) || !validateTimeInterval($end_datetime)) {
            throw new Exception("Time must be on the hour (e.g., 10:00) or half-hour (e.g., 10:30).");
        }

        // Fetch student and lecturer IDs
        $query = "SELECT s.id AS student_id FROM students s JOIN users u ON s.user_id = u.id WHERE u.email = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $student_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Student not found.");
        }
        $student = $result->fetch_assoc();
        $student_id = $student['student_id'];

        $query = "SELECT l.id AS lecturer_id FROM lecturers l JOIN users u ON l.user_id = u.id WHERE u.email = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $lecturer_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Lecturer not found.");
        }
        $lecturer = $result->fetch_assoc();
        $lecturer_id = $lecturer['lecturer_id'];

        // Insert appointment
        $query = "INSERT INTO appointments (student_id, lecturer_id, title, start_datetime, end_datetime, description, location, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $con->prepare($query);
        $stmt->bind_param("iisssss", $student_id, $lecturer_id, $title, $start_datetime, $end_datetime, $description, $location);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Appointment created successfully.";
        } else {
            throw new Exception("Error creating appointment: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    header("Location: appointment_view_admin.php");
    exit();
}