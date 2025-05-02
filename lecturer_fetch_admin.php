<?php
include("database.php");

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $query = "SELECT users.id, users.username, users.email, users.contact_number, lecturers.faculty, lecturers.department, lecturers.designation, lecturers.office_no
              FROM users
              JOIN lecturers ON users.id = lecturers.user_id
              WHERE users.id = ? AND users.role_id = 1";
    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $lecturer = $result->fetch_assoc();
        echo json_encode($lecturer);
    } else {
        echo json_encode(['error' => 'Lecturer not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'No lecturer ID provided']);
}