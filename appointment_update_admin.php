<?php
session_start();
include("database.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['appointment_id'] ?? '';
        $status = $_POST['status'] ?? '';
        $title = $_POST['title'] ?? '';
        $requester_email = $_POST['requester_email'] ?? '';
        $accepter_email = $_POST['accepter_email'] ?? '';
        $from_time = $_POST['from_time'] ?? '';
        $to_time = $_POST['to_time'] ?? '';
        $location = $_POST['location'] ?? '';
        $description = $_POST['description'] ?? '';

        if (!$id || !$status || !$title || !$from_time || !$to_time || !$location || !$description) {
            throw new Exception("All fields are required.");
        }

        // Fetch student and lecturer IDs (for validation, though emails are readonly)
        $query = "SELECT s.id AS student_id, l.id AS lecturer_id
                  FROM appointments
                  JOIN students s ON appointments.student_id = s.id
                  JOIN users u1 ON s.user_id = u1.id
                  JOIN lecturers l ON appointments.lecturer_id = l.id
                  JOIN users u2 ON l.user_id = u2.id
                  WHERE appointments.id = ? AND u1.email = ? AND u2.email = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("iss", $id, $requester_email, $accepter_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Invalid appointment or email mismatch.");
        }

        // Update appointment
        $query = "UPDATE appointments SET status = ?, title = ?, start_datetime = ?, end_datetime = ?, location = ?, description = ? WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ssssssi", $status, $title, $from_time, $to_time, $location, $description, $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Appointment updated successfully.";
        } else {
            throw new Exception("Error updating appointment: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    header("Location: appointment_view_admin.php");
    exit();
}