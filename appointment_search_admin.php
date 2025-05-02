<?php
include("database.php");

$searchTerm = $_GET['term'] ?? '';

$output = '<table class="product-table" id="appointmentTable"><thead><tr><th>No.</th><th>Student Name</th><th>Lecturer Name</th><th>Title</th><th>From</th><th>To</th><th>Description</th><th>Location</th><th>Status</th><th>Action</th></tr></thead><tbody>';

$count = 1;
$sel_query = "SELECT appointments.*, 
              u1.username AS student_name, 
              u1.email AS student_email, 
              u2.username AS lecturer_name, 
              u2.email AS lecturer_email,
              DATE_FORMAT(appointments.start_datetime, '%Y-%m-%d %h:%i %p') AS formatted_start,
              DATE_FORMAT(appointments.end_datetime, '%Y-%m-%d %h:%i %p') AS formatted_end 
              FROM appointments 
              JOIN students s ON appointments.student_id = s.id 
              JOIN users u1 ON s.user_id = u1.id 
              JOIN lecturers l ON appointments.lecturer_id = l.id 
              JOIN users u2 ON l.user_id = u2.id 
              WHERE u1.username LIKE ? OR u2.username LIKE ? OR appointments.id LIKE ?
              ORDER BY appointments.id DESC";
$stmt = $con->prepare($sel_query);
$searchTerm = "%$searchTerm%";
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
if (!$stmt) {
    $output .= '<tr><td colspan="10" style="text-align: center;">Error preparing query: ' . htmlspecialchars($con->error) . '</td></tr>';
} else {
    if (!$stmt->execute()) {
        $output .= '<tr><td colspan="10" style="text-align: center;">Error executing query: ' . htmlspecialchars($stmt->error) . '</td></tr>';
    } else {
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $output .= '<tr><td colspan="10" style="text-align: center;">No appointments found.</td></tr>';
        } else {
            while ($row = $result->fetch_assoc()) {
                $output .= '<tr>';
                $output .= '<td>' . $count . '</td>';
                $output .= '<td>' . htmlspecialchars($row["student_name"]) . '</td>';
                $output .= '<td>' . htmlspecialchars($row["lecturer_name"]) . '</td>';
                $output .= '<td>' . htmlspecialchars($row["title"]) . '</td>';
                $output .= '<td>' . htmlspecialchars($row["formatted_start"]) . '</td>';
                $output .= '<td>' . htmlspecialchars($row["formatted_end"]) . '</td>';
                $output .= '<td>' . htmlspecialchars($row["description"]) . '</td>';
                $output .= '<td>' . htmlspecialchars($row["location"]) . '</td>';
                $statusClass = '';
                $statusText = $row['status'];
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
                    default:
                        $statusClass = '';
                }
                $output .= '<td class="' . $statusClass . '">' . htmlspecialchars($statusText) . '</td>';
                $output .= '<td>';
                if ($row['status'] !== 'Cancelled') {
                    $output .= '<button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#updateModal" data-id="' . $row['id'] . '">Update</button>';
                } else {
                    $output .= '<button type="button" class="btn btn-outline-secondary" disabled>Cancelled</button>';
                }
                $output .= '</td>';
                $output .= '</tr>';
                $count++;
            }
        }
        $stmt->close();
    }
}
$output .= '</tbody></table>';
echo $output;