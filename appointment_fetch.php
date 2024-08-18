<?php
include('database.php');

if (isset($_GET['id'])) {
    $appointment_id = $_GET['id'];
    $query = "SELECT appointments.*, 
                     u1.email AS requester_email, 
                     u2.email AS accepter_email 
              FROM appointments 
              JOIN users u1 ON appointments.requester_id = u1.id 
              JOIN users u2 ON appointments.accepter_id = u2.id 
              WHERE appointments.id = ?";

    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        echo json_encode($appointment);
    } else {
        echo json_encode(['error' => 'Appointment not found']);
    }

    $stmt->close();
    $con->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}
