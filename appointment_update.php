<?php

session_start(); // Make sure this is at the top of your script

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


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $current_user_id = $_SESSION['id'] ?? null;
        $appointment_id = $_POST['appointment_id'] ?? null;

        if (!$current_user_id || !$appointment_id) {
            error_log("Missing user ID or appointment ID");
            $_SESSION['error_message'] = "Invalid request data.";
            header("Location: appointment_view.php");
            exit();
        }

        // Fetch the appointment details
        $fetch_query = "SELECT * FROM appointments WHERE id = ?";
        $fetch_stmt = $con->prepare($fetch_query);
        $fetch_stmt->bind_param('i', $appointment_id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();
        $appointment = $result->fetch_assoc();

        if (!$appointment) {
            error_log("Appointment not found: " . $appointment_id);
            $_SESSION['error_message'] = "Appointment not found.";
            header("Location: appointment_view.php");
            exit();
        }

        // Check if the current user is the requester, accepter, or admin
        $is_requester = ($appointment['requester_id'] == $current_user_id);
        $is_accepter = ($appointment['accepter_id'] == $current_user_id);
        $is_admin = ($_SESSION['role_id'] == 3); // Assuming role_id 3 is admin

        if (!$is_requester && !$is_accepter && !$is_admin) {
            error_log("Unauthorized update attempt by user: " . $current_user_id);
            $_SESSION['error_message'] = "You don't have permission to update this appointment.";
            header("Location: appointment_view.php");
            exit();
        }

        // If accepter, only allow status update
        if ($is_accepter && !$is_admin) {
            $status = $_POST['status'] ?? null;
            if (!$status) {
                error_log("Missing status for accepter update");
                $_SESSION['error_message'] = "Status is required.";
                header("Location: appointment_view.php");
                exit();
            }
            $update_query = "UPDATE appointments SET status = ? WHERE id = ?";
            $update_stmt = $con->prepare($update_query);
            $update_stmt->bind_param('si', $status, $appointment_id);
        } else {
            // Full update for requester and admin
            // ... (rest of your code for full update)
        }

        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Appointment updated successfully.";
        } else {
            error_log("SQL Error: " . $update_stmt->error);
            $_SESSION['error_message'] = "Error updating appointment: " . $update_stmt->error;
        }

        header("Location: appointment_view.php");
        exit();
    }
    $con->close();
}



    // if (empty($title) || empty($requester_email) || empty($accepter_email) || empty($from_time) || empty($to_time) || empty($location) || empty($status)) {
    //     $_SESSION['error_message'] = "All fields are required.";
    //     header("Location: appointment_view.php");
    //     exit;
    // }

//     $query = "UPDATE appointments 
//               SET title = ?, from_time = ?, to_time = ?, location = ?, description = ?, status = ?
//               WHERE id = ?";

//     if ($stmt = $con->prepare($query)) {
//         $stmt->bind_param('ssssssi', $title, $from_time, $to_time, $location, $description, $status, $appointment_id);
//         if ($stmt->execute()) {
//             $_SESSION['success_message'] = "Appointment updated successfully.";
//             header("Location: appointment_view.php");
//             exit;
//         } else {
//             $_SESSION['error_message'] = "Error updating appointment. Please try again.";
//             header("Location: appointment_view.php");
//             exit;
//         }

//         $stmt->close();
//     } else {
//         $_SESSION['error_message'] = "Error preparing query. Please try again.";
//         header("Location: appointment_view.php");
//         exit;
//     }
// }
// $con->close();