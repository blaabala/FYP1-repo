<?php
header('Content-Type: application/json'); // Ensure JSON response
include("database.php");

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Appointment ID not provided.']);
    exit();
}

$appointment_id = (int)$_GET['id'];
$query = "SELECT appointments.*, 
          u1.email AS student_email, 
          u2.email AS lecturer_email,
          DATE_FORMAT(appointments.start_datetime, '%Y-%m-%dT%H:%i') AS formatted_start,
          DATE_FORMAT(appointments.end_datetime, '%Y-%m-%dT%H:%i') AS formatted_end 
          FROM appointments 
          JOIN students s ON appointments.student_id = s.id 
          JOIN users u1 ON s.user_id = u1.id 
          JOIN lecturers l ON appointments.lecturer_id = l.id 
          JOIN users u2 ON l.user_id = u2.id 
          WHERE appointments.id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Appointment not found.']);
    exit();
}

$appointment = $result->fetch_assoc();
echo json_encode($appointment);
$stmt->close();