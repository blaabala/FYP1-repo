<?php
session_start();
include("database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = strtoupper($_POST['username']);
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $faculty = $_POST['faculty'];
    $password = password_hash('default123', PASSWORD_DEFAULT); // Default password

    // Insert into users table
    $insert_user_query = "INSERT INTO users (username, email, password, contact_number, role_id) VALUES (?, ?, ?, ?, 2)";
    $stmt = $con->prepare($insert_user_query);
    $stmt->bind_param('ssssi', $username, $email, $password, $contact_number, $role_id);
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Insert into students table
        $insert_student_query = "INSERT INTO students (user_id, username, faculty) VALUES (?, ?, ?)";
        $stmt2 = $con->prepare($insert_student_query);
        $stmt2->bind_param('iss', $user_id, $username, $faculty);
        if ($stmt2->execute()) {
            $_SESSION['success_message'] = "Student created successfully.";
        } else {
            $_SESSION['error_message'] = "Error creating student.";
        }
        $stmt2->close();
    } else {
        $_SESSION['error_message'] = "Error creating user.";
    }
    $stmt->close();
}
header("Location: student_view_admin.php");
exit();