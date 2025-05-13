<?php
session_start();
include("database.php");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $title = $_POST['title'] ?? null;
    $requester_email = $_POST['requester_email'] ?? null;
    $accepter_email = $_POST['accepter_email'] ?? null;
    $from_time = $_POST['from_time'] ?? null;
    $to_time = $_POST['to_time'] ?? null;
    $location = $_POST['location'] ?? null;
    $description = $_POST['description'] ?? null;

    // Log the received data for debugging
    error_log("Received POST data: " . print_r($_POST, true));

    if (!$appointment_id) {
        $_SESSION['error_message'] = "Invalid appointment ID.";
        header("Location: appointment_view.php");
        exit();
    }

    // Prepare and execute the update query
    $stmt = $con->prepare("UPDATE appointments SET title = ?, status = ?, start_datetime = ?, end_datetime = ?, location = ?, description = ?, updated_at = NOW() WHERE id = ?");
    if (!$stmt) {
        $_SESSION['error_message'] = "SQL Error: " . $con->error;
        header("Location: appointment_view.php");
        exit();
    }

    $stmt->bind_param("ssssssi", $title, $status, $from_time, $to_time, $location, $description, $appointment_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Appointment updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update appointment: " . $stmt->error;
    }

    $stmt->close();
    header("Location: appointment_view.php");
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: appointment_view.php");
    exit();
}
