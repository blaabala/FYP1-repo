<?php
session_start();
include("database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['lecturer_id'];
    $username = strtoupper($_POST['username']);
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $faculty = $_POST['faculty'];
    $department = $_POST['department'];
    $designation = $_POST['designation'];
    $office_no = $_POST['office_no'];

    // Update users table
    $update_user_query = "UPDATE users SET username = ?, email = ?, contact_number = ? WHERE id = ? AND role_id = 1";
    $stmt = $con->prepare($update_user_query);
    $stmt->bind_param('sssi', $username, $email, $contact_number, $user_id);
    if ($stmt->execute()) {
        // Update lecturers table
        $update_lecturer_query = "UPDATE lecturers SET faculty = ?, department = ?, designation = ?, office_no = ? WHERE user_id = ?";
        $stmt2 = $con->prepare($update_lecturer_query);
        $stmt2->bind_param('ssssi', $faculty, $department, $designation, $office_no, $user_id);
        if ($stmt2->execute()) {
            $_SESSION['success_message'] = "Lecturer updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating lecturer.";
        }
        $stmt2->close();
    } else {
        $_SESSION['error_message'] = "Error updating user.";
    }
    $stmt->close();
}
header("Location: lecturer_view_admin.php");
exit();