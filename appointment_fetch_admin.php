<?php
header('Content-Type: application/json');
include("database.php");
$id = $_GET['id'] ?? null;
if ($id) {
    $query = "SELECT appointments.*, u1.email AS student_email, u2.email AS lecturer_email,
              DATE_FORMAT(appointments.start_datetime, '%Y-%m-%dT%H:%i') AS formatted_start,
              DATE_FORMAT(appointments.end_datetime, '%Y-%m-%dT%H:%i') AS formatted_end
              FROM appointments
              JOIN students s ON appointments.student_id = s.id
              JOIN users u1 ON s.user_id = u1.id
              JOIN lecturers l ON appointments.lecturer_id = l.id
              JOIN users u2 ON l.user_id = u2.id
              WHERE appointments.id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Appointment not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid ID']);
}