<?php
// include('database.php');

// if (isset($_GET['id'])) {
//     $appointment_id = $_GET['id'];
//     $query = "SELECT appointments.*, 
//                      u1.email AS requester_email, 
//                      u2.email AS accepter_email 
//               FROM appointments 
//               JOIN users u1 ON appointments.requester_id = u1.id 
//               JOIN users u2 ON appointments.accepter_id = u2.id 
//               WHERE appointments.id = ?";

//     $stmt = $con->prepare($query);
//     $stmt->bind_param('i', $appointment_id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     if ($result->num_rows > 0) {
//         $appointment = $result->fetch_assoc();
//         echo json_encode($appointment);
//     } else {
//         echo json_encode(['error' => 'Appointment not found']);
//     }

//     $stmt->close();
//     $con->close();
// } else {
//     echo json_encode(['error' => 'Invalid request']);
// }
session_start(); // Make sure to start the session
include('database.php');

if (isset($_GET['id'])) {
    $appointment_id = $_GET['id'];
    $current_user_id = $_SESSION['id']; // Assuming you store user ID in session

    $query = "SELECT appointments.*, 
                     u1.email AS requester_email, 
                     u2.email AS accepter_email,
                     u3.role_id AS current_user_role
              FROM appointments 
              JOIN users u1 ON appointments.requester_id = u1.id 
              JOIN users u2 ON appointments.accepter_id = u2.id 
              JOIN users u3 ON u3.id = ?
              WHERE appointments.id = ?";

    $stmt = $con->prepare($query);
    $stmt->bind_param('ii', $current_user_id, $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        $appointment['current_user_id'] = $current_user_id;
        echo json_encode($appointment);
    } else {
        echo json_encode(['error' => 'Appointment not found']);
    }

    $stmt->close();
    $con->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}
