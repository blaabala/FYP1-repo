<?php
// Start session and include database connection
session_start();
include("database.php");

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $username = strtoupper(trim($_POST['username'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $contact_number = preg_replace('/\D/', '', trim($_POST['contact_number'] ?? '')); // Remove non-digits
    $faculty = trim($_POST['faculty'] ?? '');

    // Input validation
    $errors = [];
    if (empty($username)) $errors[] = "Username is required.";
    if (!$email) $errors[] = "Valid email is required.";
    if (empty($contact_number) || strlen($contact_number) < 10) $errors[] = "Valid contact number is required (at least 10 digits).";
    if (empty($faculty)) $errors[] = "Faculty is required.";

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: student_view_admin.php");
        exit();
    }

    $password = password_hash('User1234', PASSWORD_DEFAULT);

    try {
        // Start transaction for atomicity
        $con->begin_transaction();

        // Insert into users table
        $insert_user_query = "INSERT INTO users (username, email, password, contact_number, role_id) VALUES (?, ?, ?, ?, 2)";
        $stmt = $con->prepare($insert_user_query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }
        $stmt->bind_param('sssi', $username, $email, $password, $contact_number); // Removed $role_id
        if (!$stmt->execute()) {
            throw new Exception("User insertion failed: " . $stmt->error);
        }
        $user_id = $stmt->insert_id;

        // Insert into students table
        $insert_student_query = "INSERT INTO students (user_id, username, faculty) VALUES (?, ?, ?)";
        $stmt2 = $con->prepare($insert_student_query);
        if (!$stmt2) {
            throw new Exception("Prepare failed: " . $con->error);
        }
        $stmt2->bind_param('iss', $user_id, $username, $faculty);
        if (!$stmt2->execute()) {
            throw new Exception("Student insertion failed: " . $stmt2->error);
        }

        // Commit transaction
        $con->commit();
        $_SESSION['success_message'] = "Student created successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        $_SESSION['error_message'] = "Error creating student: " . $e->getMessage();
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($stmt2)) $stmt2->close();
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

header("Location: student_view_admin.php");
exit();
