<?php
session_start();
include("database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = strtoupper($_POST['username']);
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $faculty = $_POST['faculty'];
    $department = $_POST['department'];
    $designation = $_POST['designation'];
    $office_no = $_POST['office_no'];
    $password = password_hash('default123', PASSWORD_DEFAULT); // Default password

    // Insert into users table
    $insert_user_query = "INSERT INTO users (username, email, password, contact_number, role_id) VALUES (?, ?, ?, ?, 1)";
    $stmt = $con->prepare($insert_user_query);
    $stmt->bind_param('ssss', $username, $email, $password, $contact_number);
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Insert into lecturers table
        $insert_lecturer_query = "INSERT INTO lecturers (user_id, username, faculty, department, designation, office_no) VALUES (?, ?, ?, ?, ?)";
        $stmt2 = $con->prepare($insert_lecturer_query);
        $stmt2->bind_param('isssss', $username, $user_id, $faculty, $department, $designation, $office_no);
        if ($stmt2->execute()) {
            $_SESSION['success_message'] = "Lecturer created successfully.";
        } else {
            $_SESSION['error_message'] = "Error creating lecturer.";
        }
        $stmt2->close();
    } else {
        $_SESSION['error_message'] = "Error creating user.";
    }
    $stmt->close();
}
header("Location: lecturer_view_admin.php");
exit();