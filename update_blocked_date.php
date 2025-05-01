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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    try {
        $blocked_id = (int)$_POST['blocked_id'];
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $reason = !empty($_POST['reason']) ? trim($_POST['reason']) : null;

        // Validate fields
        if (!$start_date || !$end_date) {
            $_SESSION['error_message'] = "Please fill in all required fields for blocked dates.";
            header("Location: set_availability.php");
            exit();
        }

        // Validate dates
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        if ($start_date_obj > $end_date_obj) {
            $_SESSION['error_message'] = "End date must be on or after start date.";
            header("Location: set_availability.php");
            exit();
        }

        // Check for conflicts with existing appointments
        $query = "SELECT id, title, start_datetime, end_datetime 
                  FROM appointments 
                  WHERE lecturer_id = ? 
                  AND status IN ('Confirmed', 'Completed') 
                  AND (
                      (start_datetime <= ? AND end_datetime >= ?) 
                      OR (start_datetime >= ? AND start_datetime <= ?) 
                      OR (end_datetime >= ? AND end_datetime <= ?)
                  )";
        $stmt = $con->prepare($query);
        $stmt->bind_param("issssss", $lecturer_id, $end_date, $start_date, $start_date, $end_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $conflicts = [];
            while ($appointment = $result->fetch_assoc()) {
                $start = date("d M Y, h:i A", strtotime($appointment['start_datetime']));
                $end = date("d M Y, h:i A", strtotime($appointment['end_datetime']));
                $conflicts[] = "Appointment ID {$appointment['id']} ({$appointment['title']}) on {$start} to {$end}";
            }
            $_SESSION['error_message'] = "Cannot update blocked date: Conflicts with existing appointments:\n" . implode("\n", $conflicts);
            header("Location: set_availability.php");
            exit();
        }

        // Update the blocked date entry
        $query = "UPDATE blocked_dates SET start_date = ?, end_date = ?, reason = ? WHERE id = ? AND lecturer_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("sssii", $start_date, $end_date, $reason, $blocked_id, $lecturer_id);
        $stmt->execute();

        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Blocked date updated successfully.";
        } else {
            $_SESSION['error_message'] = "No blocked date was updated. It may not exist or you lack permission to update it.";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating blocked date: " . $e->getMessage();
    }
    header("Location: set_availability.php");
    exit();
} else {
    header("Location: set_availability.php");
    exit();
}