<?php
include("database.php");

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $query = "SELECT users.id, users.username, users.email, users.contact_number, students.faculty
              FROM users
              JOIN students ON users.id = students.user_id
              WHERE users.id = ? AND users.role_id = 2";
    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo json_encode($student);
    } else {
        echo json_encode(['error' => 'Student not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'No student ID provided']);
}