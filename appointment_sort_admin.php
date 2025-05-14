<?php
include("database.php");

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$valid_sort_columns = ['start_datetime', 'end_datetime', 'id'];
$sort_by = in_array($sort_by, $valid_sort_columns) ? $sort_by : 'id';
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

$sel_query = "SELECT appointments.*, 
              u1.username AS student_name, 
              u1.email AS student_email, 
              u2.username AS lecturer_name, 
              u2.email AS lecturer_email,
              DATE_FORMAT(appointments.start_datetime, '%Y-%m-%d %h:%i %p') AS formatted_start,
              DATE_FORMAT(appointments.end_datetime, '%Y-%m-%d %h:%i %p') AS formatted_end 
              FROM appointments 
              JOIN students s ON appointments.student_id = s.user_id 
              JOIN users u1 ON s.user_id = u1.id 
              JOIN lecturers l ON appointments.lecturer_id = l.id 
              JOIN users u2 ON l.user_id = u2.id 
              ORDER BY appointments.$sort_by $sort_order";

$stmt = $con->prepare($sel_query);
$stmt->execute();
$result = $stmt->get_result();

$count = 1;
if ($result->num_rows === 0) {
    echo "<tr><td colspan='10' style='text-align: center;'>No appointments found.</td></tr>";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>$count</td>";
        echo "<td>" . htmlspecialchars($row["student_name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["lecturer_name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["formatted_start"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["formatted_end"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["location"]) . "</td>";

        $statusClass = '';
        $statusText = $row['status'];
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
        echo "<td class='$statusClass'>" . htmlspecialchars($statusText) . "</td>";

        echo "<td>";
        if ($row['status'] !== 'Cancelled') {
            echo "<button type='button' class='btn btn-outline-warning' data-bs-toggle='modal' data-bs-target='#updateModal' data-id='{$row['id']}'>Update</button>";
        } else {
            echo "<button type='button' class='btn btn-outline-secondary' disabled>Cancelled</button>";
        }
        echo "</td>";
        echo "</tr>";
        $count++;
    }
}
$stmt->close();
