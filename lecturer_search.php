<?php
// Enable error reporting for debugging, but suppress output to avoid breaking JSON
ini_set('display_errors', 0);
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

// Fetch lecturers with search filter
$sel_query = "SELECT id, username, user_id, faculty, department, designation 
              FROM lecturers 
              WHERE username LIKE ? OR faculty LIKE ? OR department LIKE ? OR designation LIKE ? 
              ORDER BY username ASC";
$stmt = $con->prepare($sel_query);
if (!$stmt) {
    $response['error'] = 'SQL Error: ' . mysqli_error($con);
    echo json_encode($response);
    ob_end_flush();
    exit();
}

$search_like = '%' . $search_term . '%';
$stmt->bind_param('ssss', $search_like, $search_like, $search_like, $search_like);
$stmt->execute();
$result = $stmt->get_result();

$lecturers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch availability for this lecturer
        $lecturer_id = $row['id'];
        $avail_query = "SELECT * FROM lecturer_availability WHERE lecturer_id = ?";
        $avail_stmt = $con->prepare($avail_query);
        $avail_stmt->bind_param("i", $lecturer_id);
        $avail_stmt->execute();
        $avail_result = $avail_stmt->get_result();

        $availability_strings = [];
        while ($avail_row = $avail_result->fetch_assoc()) {
            if ($avail_row['is_recurring']) {
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $day_of_week = $days[$avail_row['day_of_week']];
                $start_time = date("h:i A", strtotime($avail_row['start_time']));
                $end_time = date("h:i A", strtotime($avail_row['end_time']));
                $recurring_period = '';
                if ($avail_row['start_date'] && $avail_row['end_date']) {
                    $start_date = date("d M Y", strtotime($avail_row['start_date']));
                    $end_date = date("d M Y", strtotime($avail_row['end_date']));
                    $recurring_period = " (from $start_date to $end_date)";
                }
                $availability_strings[] = "Every $day_of_week, $start_time - $end_time$recurring_period";
            } else {
                $start_datetime = date("d M Y, h:i A", strtotime($avail_row['start_datetime']));
                $end_datetime = date("h:i A", strtotime($avail_row['end_datetime']));
                $availability_strings[] = "$start_datetime - $end_datetime";
            }
        }
        $avail_stmt->close();

        // Add availability to the lecturer's data
        $row['availability'] = $availability_strings;
        $lecturers[] = $row;
    }
}

$response['debug']['num_rows'] = $result->num_rows;

if ($result->num_rows === 0) {
    $response['html'] = "<p class='text-center text-gray-500'>No lecturers found for search term: \"$search_term\"</p>";
} else {
    $html = '';
    foreach ($lecturers as $lecturer) {
        $html .= '<div class="lecturer-card bg-white p-4 mb-2 rounded shadow flex justify-between items-center cursor-pointer"';
        $html .= ' data-id="' . htmlspecialchars($lecturer['id']) . '"';
        $html .= ' data-availability="' . htmlspecialchars(json_encode($lecturer['availability'])) . '">';
        $html .= '<div>';
        $html .= '<h3 class="text-lg font-semibold">' . htmlspecialchars($lecturer['username']) . '</h3>';
        $html .= '<p class="text-gray-600">' . htmlspecialchars($lecturer['faculty']) . '</p>';
        $html .= '<p class="text-gray-500">' . htmlspecialchars($lecturer['department']) . '</p>';
        $html .= '<p class="text-gray-500">' . htmlspecialchars($lecturer['designation']) . '</p>';
        $html .= '</div>';
        $html .= '<div class="flex space-x-4">';
        $html .= '<button class="text-gray-500 hover:text-blue-600 p-2 rounded-full hover:bg-blue-50 transition-colors duration-300" title="View Details">';
        $html .= '<i class="fas fa-info-circle"></i>';
        $html .= '</button>';
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
