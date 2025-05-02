<?php
header('Content-Type: application/json'); // Ensure JSON response
include("database.php");

$range = $_GET['range'] ?? 'monthly';
$today = date('Y-m-d');
$startDate = $endDate = '';

switch ($range) {
    case 'daily':
        $startDate = $endDate = $today;
        break;
    case 'weekly':
        $startDate = date('Y-m-d', strtotime('-7 days', strtotime($today)));
        $endDate = $today;
        break;
    case 'monthly':
    default:
        $startDate = date('Y-m-01', strtotime($today));
        $endDate = date('Y-m-t', strtotime($today));
        break;
}

$query = "SELECT status, COUNT(*) as count
          FROM appointments
          WHERE DATE(start_datetime) BETWEEN ? AND ?
          GROUP BY status";
$stmt = $con->prepare($query);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

$data = [
    'confirmed' => 0,
    'rejected' => 0,
    'cancelled' => 0,
    'completed' => 0
];

while ($row = $result->fetch_assoc()) {
    $data[strtolower($row['status'])] = $row['count'];
}

echo json_encode($data);
$stmt->close();