<?php
include("database.php");

// 1. Total Appointments for Percentage Calculations
$total_appointments_query = "SELECT COUNT(*) as total FROM appointments";
$total_appointments_result = mysqli_query($con, $total_appointments_query);
$total_appointments = mysqli_fetch_assoc($total_appointments_result)['total'];

// 2. Top Lecturers by Cancellations
$total_cancellations_query = "SELECT COUNT(*) as total FROM appointments WHERE status = 'Cancelled'";
$total_cancellations_result = mysqli_query($con, $total_cancellations_query);
$total_cancellations = mysqli_fetch_assoc($total_cancellations_result)['total'];

$top_cancellations_query = "
    SELECT 
        u.username AS lecturer_name, 
        COUNT(*) AS cancellation_count,
        IFNULL(ROUND((COUNT(*) / ? * 100), 2), 0) AS percentage
    FROM appointments a
    JOIN lecturers l ON a.lecturer_id = l.id
    JOIN users u ON l.user_id = u.id
    WHERE a.status = 'Cancelled'
    GROUP BY u.username
    ORDER BY cancellation_count DESC
    LIMIT 5";
$stmt = $con->prepare($top_cancellations_query);
$stmt->bind_param("i", $total_cancellations);
$stmt->execute();
$top_cancellations_result = $stmt->get_result();
$top_cancellations = [];
while ($row = $top_cancellations_result->fetch_assoc()) {
    $top_cancellations[] = $row;
}
$stmt->close();

// 3. Most Active Lecturers (Confirmed or Completed Appointments)
$most_active_query = "
    SELECT 
        u.username AS lecturer_name, 
        COUNT(*) AS appointment_count
    FROM appointments a
    JOIN lecturers l ON a.lecturer_id = l.id
    JOIN users u ON l.user_id = u.id
    WHERE a.status IN ('Confirmed', 'Completed')
    GROUP BY u.username
    ORDER BY appointment_count DESC
    LIMIT 5";
$most_active_result = mysqli_query($con, $most_active_query);
$most_active = [];
while ($row = mysqli_fetch_assoc($most_active_result)) {
    $most_active[] = $row;
}

// 4. Top Students by Appointment Requests
$top_students_requests_query = "
    SELECT 
        u.username AS student_name, 
        COUNT(*) AS request_count,
        IFNULL(ROUND((COUNT(*) / ? * 100), 2), 0) AS percentage
    FROM appointments a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    GROUP BY u.username
    ORDER BY request_count DESC
    LIMIT 5";
$stmt = $con->prepare($top_students_requests_query);
$stmt->bind_param("i", $total_appointments);
$stmt->execute();
$top_students_requests_result = $stmt->get_result();
$top_students_requests = [];
while ($row = $top_students_requests_result->fetch_assoc()) {
    $top_students_requests[] = $row;
}
$stmt->close();

// 5. Students with Most Cancellations
$total_student_cancellations_query = "SELECT COUNT(*) as total FROM appointments WHERE status = 'Cancelled'";
$total_student_cancellations_result = mysqli_query($con, $total_student_cancellations_query);
$total_student_cancellations = mysqli_fetch_assoc($total_student_cancellations_result)['total'];

$top_students_cancellations_query = "
    SELECT 
        u.username AS student_name, 
        COUNT(*) AS cancellation_count,
        IFNULL(ROUND((COUNT(*) / ? * 100), 2), 0) AS percentage
    FROM appointments a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE a.status = 'Cancelled'
    GROUP BY u.username
    ORDER BY cancellation_count DESC
    LIMIT 5";
$stmt = $con->prepare($top_students_cancellations_query);
$stmt->bind_param("i", $total_student_cancellations);
$stmt->execute();
$top_students_cancellations_result = $stmt->get_result();
$top_students_cancellations = [];
while ($row = $top_students_cancellations_result->fetch_assoc()) {
    $top_students_cancellations[] = $row;
}
$stmt->close();

// 6. Appointment Status Breakdown by Lecturer
$status_breakdown_query = "
    SELECT 
        u.username AS lecturer_name,
        SUM(CASE WHEN a.status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed,
        SUM(CASE WHEN a.status = 'Rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled,
        SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) AS completed,
        COUNT(*) AS total
    FROM appointments a
    JOIN lecturers l ON a.lecturer_id = l.id
    JOIN users u ON l.user_id = u.id
    GROUP BY u.username
    ORDER BY total DESC";
$status_breakdown_result = mysqli_query($con, $status_breakdown_query);
$status_breakdown = [];
while ($row = mysqli_fetch_assoc($status_breakdown_result)) {
    $status_breakdown[] = $row;
}

// Prepare the response
$response = [
    'top_cancellations' => $top_cancellations,
    'most_active' => $most_active,
    'top_students_requests' => $top_students_requests,
    'top_students_cancellations' => $top_students_cancellations,
    'status_breakdown' => $status_breakdown
];

// Output as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$con->close();
