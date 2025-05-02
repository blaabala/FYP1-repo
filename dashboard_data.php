<?php
header('Content-Type: application/json');
include("database.php");

// Fetch stats
$total_appointments = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM appointments"))['count'];
$total_lecturers = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM lecturers"))['count'];
$total_students = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM students"))['count'];

// Fetch appointment status data for pie chart
$status_query = "SELECT status, COUNT(*) as count FROM appointments GROUP BY status";
$status_result = mysqli_query($con, $status_query);
$status_data = ['Confirmed' => 0, 'Rejected' => 0, 'Cancelled' => 0, 'Completed' => 0, 'Pending' => 0];
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_data[$row['status']] = $row['count'];
}

// Fetch daily appointment trends for line chart (last 7 days)
$trend_query = "SELECT DATE(start_datetime) as date, COUNT(*) as count 
                FROM appointments 
                WHERE start_datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                GROUP BY DATE(start_datetime) 
                ORDER BY DATE(start_datetime)";
$trend_result = mysqli_query($con, $trend_query);
$trend_labels = [];
$trend_data = [];
$today = new DateTime();
for ($i = 6; $i >= 0; $i--) {
    $date = (clone $today)->modify("-$i days")->format('Y-m-d');
    $trend_labels[] = (clone $today)->modify("-$i days")->format('M d');
    $trend_data[$date] = 0;
}
while ($row = mysqli_fetch_assoc($trend_result)) {
    $trend_data[$row['date']] = $row['count'];
}
$trend_values = array_values($trend_data);

echo json_encode([
    'total_appointments' => $total_appointments,
    'total_lecturers' => $total_lecturers,
    'total_students' => $total_students,
    'status_data' => $status_data,
    'trend_labels' => $trend_labels,
    'trend_values' => $trend_values
]);