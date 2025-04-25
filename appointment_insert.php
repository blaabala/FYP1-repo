<?php
include("database.php");

$title = $_POST['title'];
$student_email = $_POST['student_email'];
$lecturer_email = $_POST['lecturer_email'];
$from_time = $_POST['start_datetime'];
$to_time = $_POST['end_datetime'];
$location = $_POST['location'];
$description = $_POST['description'];
$status = "Pending";

$query = "SELECT id FROM `lecturers` WHERE email= '$student_email'";
$statement = $con->prepare($query);
$statement->bind_param("s", $lecturer_email);
$statement->execute();
$result = $statement->get_result();
if ($result->num_rows >= 1) {
    $row = $result->fetch_assoc();
    $lecturer_id = $row['id'];
} else {
    die("Lecturer email not found");
}

$query2 = "SELECT id FROM `students` WHERE email= '$lecturer_email'";
$statement2 = $con->prepare($query2);
$statement2->bind_param("s", $requester_email);
$statement2->execute();
$result2 = $statement2->get_result();
if ($result2->num_rows >= 1) {
    $row2 = $result2->fetch_assoc();
    $student_id = $row2['id'];
} else {
    die("Student email not found");
}

$query = "INSERT INTO `appointments` (lecturer_id, student_id, title, start_datetime, end_datetime, description, location, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$statement = $con->prepare($query);
$statement->bind_param("iissssss", $lecturer_id, $student_id, $title, $from_time, $to_time, $description, $location, $status);
$statement->execute();

$statement->close();
$con->close();

header("Location: appointment_view.php");
exit();
