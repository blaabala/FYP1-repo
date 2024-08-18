<?php

include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $appointment_id = $_POST['appointment_id'];
    $title = $_POST['title'];
    $requester_email = $_POST['requester_email'];
    $accepter_email = $_POST['accepter_email'];
    $from_time = $_POST['from_time'];
    $to_time = $_POST['to_time'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    if (empty($title) || empty($requester_email) || empty($accepter_email) || empty($from_time) || empty($to_time) || empty($location) || empty($status)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: appointment_view.php");
        exit;
    }

    $query = "UPDATE appointments 
              SET title = ?, from_time = ?, to_time = ?, location = ?, description = ?, status = ?
              WHERE id = ?";

    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param('ssssssi', $title, $from_time, $to_time, $location, $description, $status, $appointment_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Appointment updated successfully.";
            header("Location: appointment_view.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Error updating appointment. Please try again.";
            header("Location: appointment_view.php");
            exit;
        }

        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing query. Please try again.";
        header("Location: appointment_view.php");
        exit;
    }
}
$con->close();
