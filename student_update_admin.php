<?php
session_start();
include("database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['student_id'];
    $username = strtoupper($_POST['username']);
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $faculty = $_POST['faculty'];

    // Update users table
    $update_user_query = "UPDATE users SET username = ?, email = ?, contact_number = ? WHERE id = ? AND role_id = 2";
    $stmt = $con->prepare($update_user_query);
    $stmt->bind_param('sssi', $username, $email, $contact_number, $user_id);
    if ($stmt->execute()) {
        // Update students table
        $update_student_query = "UPDATE students SET username = ?, faculty = ? WHERE user_id = ?";
        $stmt2 = $con->prepare($update_student_query);
        $stmt2->bind_param('ssi', $username, $faculty, $user_id);
        if ($stmt2->execute()) {
            $_SESSION['success_message'] = "Student updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating student.";
        }
        $stmt2->close();
    } else {
        $_SESSION['error_message'] = "Error updating user.";
    }
    $stmt->close();
}
header("Location: student_view_admin.php");
exit();