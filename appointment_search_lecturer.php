<?php
include('database.php');

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$lecturer_id = isset($_POST['lecturer_id']) ? (int)$_POST['lecturer_id'] : 0;
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';

if ($lecturer_id <= 0) {
    echo json_encode(['error' => 'Invalid lecturer ID.']);
    exit();
}

$where_clause = "WHERE appointments.lecturer_id = ? AND (u1.username LIKE ? OR appointments.title LIKE ? OR appointments.description LIKE ? OR appointments.location LIKE ?)";
$params = [$lecturer_id, "%$search%", "%$search%", "%$search%", "%$search%"];
$types = 'issss';

if (!empty($from_date) && !empty($to_date)) {
    $from_date = date('Y-m-d 00:00:00', strtotime($from_date));
    $to_date = date('Y-m-d 23:59:59', strtotime($to_date));
    $where_clause .= " AND appointments.start_datetime BETWEEN ? AND ?";
    $params[] = $from_date;
    $params[] = $to_date;
    $types .= 'ss';
} elseif (!empty($from_date)) {
    $from_date = date('Y-m-d 00:00:00', strtotime($from_date));
    $where_clause .= " AND appointments.start_datetime >= ?";
    $params[] = $from_date;
    $types .= 's';
} elseif (!empty($to_date)) {
    $to_date = date('Y-m-d 23:59:59', strtotime($to_date));
    $where_clause .= " AND appointments.start_datetime <= ?";
    $params[] = $to_date;
    $types .= 's';
}

$query = "SELECT appointments.*, 
          u1.username AS requester_name, 
          u1.email AS requester_email, 
          u2.username AS accepter_name, 
          u2.email AS accepter_email,
          DATE_FORMAT(appointments.start_datetime, '%Y-%m-%d %h:%i %p') AS formatted_start,
          DATE_FORMAT(appointments.end_datetime, '%Y-%m-%d %h:%i %p') AS formatted_end
          FROM appointments 
          JOIN users u1 ON appointments.student_id = u1.id 
          JOIN lecturers l ON appointments.lecturer_id = l.id 
          JOIN users u2 ON l.user_id = u2.id 
          $where_clause 
          ORDER BY appointments.id DESC";

$stmt = $con->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare statement: ' . $con->error]);
    exit();
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ob_start();
?>
<table class="product-table">
    <thead>
        <tr>
            <th>No.</th>
            <th>Requester Name</th>
            <th>Title</th>
            <th>From</th>
            <th>To</th>
            <th>Description</th>
            <th>Location</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($appointments)): ?>
            <tr>
                <td colspan="9" class="text-center">No appointments found.</td>
            </tr>
        <?php else: ?>
            <?php $count = 1; ?>
            <?php foreach ($appointments as $row): ?>
                <?php
                $statusText = $row['status'] ?: 'Confirmed';
                $statusClass = match ($statusText) {
                    'Confirmed' => 'status-confirmed',
                    'Cancelled' => 'status-cancelled',
                    'Rejected' => 'status-rejected',
                    'Completed' => 'status-completed',
                    default => ''
                };
                ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo htmlspecialchars($row["requester_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td><?php echo htmlspecialchars($row["formatted_start"]); ?></td>
                    <td><?php echo htmlspecialchars($row["formatted_end"]); ?></td>
                    <td><?php echo htmlspecialchars($row["description"]); ?></td>
                    <td><?php echo htmlspecialchars($row["location"]); ?></td>
                    <td class="<?php echo $statusClass; ?>">
                        <?php echo htmlspecialchars($statusText); ?>
                    </td>
                    <td>
                        <?php if ($statusText !== 'Cancelled'): ?>
                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                data-bs-target="#updateModal" data-id="<?php echo $row['id']; ?>">Update</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-secondary" disabled>Cancelled</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php $count++; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php
$html = ob_get_clean();
echo json_encode(['html' => $html]);
$con->close();
?>