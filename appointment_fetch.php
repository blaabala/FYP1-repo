<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
include("database.php");

// Ensure the user is logged in
$student_id = $_SESSION['id'] ?? null;
if (!$student_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get the appointment ID from the query string
$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($appointment_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid appointment ID']);
    exit();
}

// Fetch the appointment details
$query = "SELECT appointments.*, 
    u1.username AS student_name, 
    u1.email AS student_email, 
    u1.id AS student_user_id,
    u2.username AS lecturer_name, 
    u2.email AS lecturer_email,
    u2.id AS lecturer_user_id
FROM appointments 
JOIN users u1 ON appointments.student_id = u1.id 
JOIN lecturers l ON appointments.lecturer_id = l.id 
JOIN users u2 ON l.user_id = u2.id 
WHERE appointments.id = ?";

$stmt = $con->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . mysqli_error($con)]);
    exit();
}

$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found']);
    exit();
}

$appointment = $result->fetch_assoc();
$stmt->close();

// Prepare the response in the format expected by the JavaScript
$response = [
    'id' => $appointment['id'],
    'title' => $appointment['title'],
    'requester_email' => $appointment['student_email'],
    'accepter_email' => $appointment['lecturer_email'],
    'from_time' => $appointment['start_datetime'],
    'to_time' => $appointment['end_datetime'],
    'location' => $appointment['location'],
    'description' => $appointment['description'],
    'status' => $appointment['status'] ?: 'Pending',
    'requester_id' => $appointment['student_id'],
    'accepter_id' => $appointment['lecturer_id'],
    'current_user_id' => $student_id,
    'current_user_role' => isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0
];

// Set the content type and return the JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();