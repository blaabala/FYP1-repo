<?php
include("database.php");

// Fetch data from POST request
$title = $_POST['title'];
$requester_email = $_POST['requester_email'];
$accepter_email = $_POST['accepter_email'];
$from_time = $_POST['from_time'];
$to_time = $_POST['to_time'];
$location = $_POST['location'];
$description = $_POST['description'];
$status = "Pending";

// Prepare and execute query to get accepter ID
$query = "SELECT id FROM `users` WHERE email=?";
$statement = $con->prepare($query);
$statement->bind_param("s", $accepter_email);
$statement->execute();
$result = $statement->get_result();
if ($result->num_rows >= 1) {
    $row = $result->fetch_assoc();
    $accepter_id = $row['id'];
} else {
    die("Accepter email not found");
}

// Prepare and execute query to get requester ID
$query2 = "SELECT id FROM `users` WHERE email=?";
$statement2 = $con->prepare($query2);
$statement2->bind_param("s", $requester_email);
$statement2->execute();
$result2 = $statement2->get_result();
if ($result2->num_rows >= 1) {
    $row2 = $result2->fetch_assoc();
    $requester_id = $row2['id'];
} else {
    die("Requester email not found");
}

// Insert appointment
$query = "INSERT INTO `appointments` (accepter_id, requester_id, title, from_time, to_time, description, location, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$statement = $con->prepare($query);
$statement->bind_param("iissssss", $accepter_id, $requester_id, $title, $from_time, $to_time, $description, $location, $status);
$statement->execute();

$statement->close();
$con->close();

header("Location: appointment_view.php");
exit();
