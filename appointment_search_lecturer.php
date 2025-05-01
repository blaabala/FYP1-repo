<?php
// Enable error reporting for debugging, but suppress output to avoid breaking JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set headers to enforce JSON response
header('Content-Type: application/json; charset=UTF-8');
ob_start(); // Start output buffering to catch any unexpected output

include("database.php");

$lecturer_id = isset($_POST['lecturer_id']) ? (int)$_POST['lecturer_id'] : 0;
$search_term = isset($_POST['search']) ? trim($_POST['search']) : '';
$search_term = mysqli_real_escape_string($con, $search_term);
$response = [
    'debug' => [
        'search_term' => $search_term,
        'lecturer_id' => $lecturer_id
    ]
];

if (!$lecturer_id) {
    $response['error'] = 'Invalid lecturer ID';
    echo json_encode($response);
    ob_end_flush();
    exit();
}

// Fetch appointments with search filter
$sel_query = "SELECT appointments.*, 
              u1.username AS requester_name, 
              u1.email AS requester_email, 
              u2.username AS accepter_name, 
              u2.email AS accepter_email 
       FROM appointments 
       JOIN users u1 ON appointments.student_id = u1.id 
       JOIN lecturers l ON appointments.lecturer_id = l.id 
       JOIN users u2 ON l.user_id = u2.id 
       WHERE appointments.lecturer_id = ? 
       AND (u1.username LIKE ? OR appointments.title LIKE ? OR appointments.description LIKE ? OR appointments.location LIKE ? OR appointments.status LIKE ?)
       ORDER BY appointments.id DESC";
$stmt = $con->prepare($sel_query);
if (!$stmt) {
    $response['error'] = 'SQL Error: ' . mysqli_error($con);
    echo json_encode($response);
    ob_end_flush();
    exit();
}

$search_like = '%' . $search_term . '%';
$stmt->bind_param('isssss', $lecturer_id, $search_like, $search_like, $search_like, $search_like, $search_like);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

$response['debug']['num_rows'] = $result->num_rows;

if ($result->num_rows === 0) {
    $response['html'] = "<table class='product-table'><tbody><tr><td colspan='9' class='text-center'>No appointments found for search term: \"$search_term\"</td></tr></tbody></table>";
} else {
    $html = '<table class="product-table"><thead><tr><th>No.</th><th>Requester Name</th><th>Title</th><th>From</th><th>To</th><th>Description</th><th>Location</th><th>Status</th><th>Action</th></tr></thead><tbody>';
    $count = 1;
    foreach ($appointments as $row) {
        $statusText = $row['status'] ?: 'Confirmed';
        $statusClass = '';
        switch ($statusText) {
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
            default:
                $statusClass = '';
        }
        $html .= '<tr>';
        $html .= '<td>' . $count . '</td>';
        $html .= '<td>' . htmlspecialchars($row["requester_name"]) . '</td>';
        $html .= '<td>' . htmlspecialchars($row["title"]) . '</td>';
        $html .= '<td>' . htmlspecialchars($row["start_datetime"]) . '</td>';
        $html .= '<td>' . htmlspecialchars($row["end_datetime"]) . '</td>';
        $html .= '<td>' . htmlspecialchars($row["description"]) . '</td>';
        $html .= '<td>' . htmlspecialchars($row["location"]) . '</td>';
        $html .= '<td class="' . $statusClass . '">' . htmlspecialchars($statusText) . '</td>';
        $html .= '<td>';
        if ($statusText !== 'Cancelled') {
            $html .= '<button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#updateModal" data-id="' . $row['id'] . '">Update</button>';
        } else {
            $html .= '<button type="button" class="btn btn-outline-secondary" disabled>Cancelled</button>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        $count++;
    }
    $html .= '</tbody></table>';
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
