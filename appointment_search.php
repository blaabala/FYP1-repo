<?php
// Enable error reporting for debugging, but suppress output to avoid breaking JSON
ini_set('display_errors', 0); // Suppress errors in production
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set headers to enforce JSON response
header('Content-Type: application/json; charset=UTF-8');
ob_start(); // Start output buffering to catch any unexpected output

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
include("database.php");

$student_id = $_SESSION['id'] ?? null;
if (!$student_id) {
    $response = ['error' => 'Unauthorized', 'debug' => 'No student_id in session'];
    echo json_encode($response);
    ob_end_flush();
    exit();
}

$search_term = isset($_POST['search']) ? trim($_POST['search']) : '';
$search_term = mysqli_real_escape_string($con, $search_term);
$response = [
    'debug' => [
        'search_term' => $search_term,
        'student_id' => $student_id
    ]
];

$sel_query = "SELECT appointments.*, 
    u1.username AS student_name,
    u1.email AS student_email,
    u2.username AS lecturer_name,
    u2.email AS lecturer_email 
FROM appointments 
JOIN users u1 ON appointments.student_id = u1.id 
JOIN lecturers l ON appointments.lecturer_id = l.id 
JOIN users u2 ON l.user_id = u2.id 
WHERE (appointments.student_id = ? OR appointments.lecturer_id = ?) 
AND appointments.title LIKE ? 
ORDER BY appointments.id DESC";

$stmt = $con->prepare($sel_query);
if (!$stmt) {
    $response['error'] = 'SQL Error: ' . mysqli_error($con);
    echo json_encode($response);
    ob_end_flush();
    exit();
}

$search_like = '%' . $search_term . '%';
$stmt->bind_param('iis', $student_id, $student_id, $search_like);
$stmt->execute();
$result = $stmt->get_result();

$response['debug']['num_rows'] = $result->num_rows;

if ($result->num_rows === 0) {
    $response['html'] = "<p class='text-center text-gray-500'>No appointments found for search term: \"$search_term\"</p>";
} else {
    $html = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $statusText = $row['status'] ?: 'Pending';
        $statusClass = '';
        switch ($statusText) {
            case 'Pending':
                $statusClass = 'status-pending';
                break;
            case 'Confirmed':
                $statusClass = 'status-confirmed';
                break;
            case 'Cancelled':
                $statusClass = 'status-cancelled';
                break;
            case 'Rejected':
                $statusClass = 'status-rejected';
                break;
            case 'Completed':
                $statusClass = 'status-completed';
                break;
        }

        $html .= '<div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">';
        $html .= '<div class="flex justify-between items-start">';
        $html .= '<div>';
        $html .= '<h3 class="text-lg font-semibold text-gray-800">' . htmlspecialchars($row['title']) . '</h3>';
        $html .= '<p class="text-sm text-gray-600"><span class="font-medium">Student:</span> ' . htmlspecialchars($row['student_name']) . '</p>';
        $html .= '<p class="text-sm text-gray-600"><span class="font-medium">Lecturer:</span> ' . htmlspecialchars($row['lecturer_name']) . '</p>';
        $html .= '<p class="text-sm text-gray-600"><span class="font-medium">From:</span> ' . htmlspecialchars($row['start_datetime']) . '</p>';
        $html .= '<p class="text-sm text-gray-600"><span class="font-medium">To:</span> ' . htmlspecialchars($row['end_datetime']) . '</p>';
        $html .= '<p class="text-sm text-gray-600"><span class="font-medium">Description:</span> ' . htmlspecialchars($row['description']) . '</p>';
        $html .= '<p class="text-sm text-gray-600"><span class="font-medium">Location:</span> ' . htmlspecialchars($row['location']) . '</p>';
        $html .= '<p class="text-sm ' . $statusClass . '"><span class="font-medium">Status:</span> ' . htmlspecialchars($statusText) . '</p>';
        $html .= '</div>';
        $html .= '<div>';
        if ($statusText !== 'Cancelled') {
            $html .= '<button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors" data-bs-toggle="modal" data-bs-target="#updateModal" data-id="' . $row['id'] . '">More...</button>';
        } else {
            $html .= '<button type="button" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg cursor-not-allowed" disabled>Cancelled</button>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $response['html'] = $html;
}

$stmt->close();

// Check for any unexpected output before sending JSON
$unexpected_output = ob_get_contents();
if (!empty($unexpected_output)) {
    $response['error'] = 'Unexpected output detected';
    $response['debug']['unexpected_output'] = $unexpected_output;
}

ob_end_clean(); // Clear the buffer and stop buffering
echo json_encode($response);
