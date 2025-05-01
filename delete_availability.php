<?php
session_start();
include("database.php");

$lecturer_user_id = $_SESSION['id'] ?? null;

if (!$lecturer_user_id) {
    $_SESSION['error_message'] = "Please login to continue.";
    header("Location: login_lecturer.php");
    exit();
}

// Fetch the lecturer's ID from the lecturers table
$query = "SELECT id FROM lecturers WHERE user_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $lecturer_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Lecturer record not found. Please contact the administrator.";
    header("Location: set_availability.php");
    exit();
}

$lecturer = $result->fetch_assoc();
$lecturer_id = $lecturer['id'];

if (isset($_GET['id'])) {
    $avail_id = (int)$_GET['id'];

    // Delete the availability entry
    $query = "DELETE FROM lecturer_availability WHERE id = ? AND lecturer_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $avail_id, $lecturer_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success_message'] = "Availability deleted successfully.";
    } else {
        $_SESSION['error_message'] = "No availability was deleted. It may not exist or you lack permission to delete it.";
    }
} else {
    $_SESSION['error_message'] = "Invalid availability ID.";
}

header("Location: set_availability.php");
exit();