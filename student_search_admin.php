<?php
include("database.php");

$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';
$searchTerm = "%$searchTerm%";

$query = "SELECT users.id, users.username, users.contact_number, students.faculty
          FROM users
          JOIN students ON users.id = students.user_id
          WHERE users.role_id = 2 AND users.username LIKE ?
          ORDER BY users.id DESC";
$stmt = $con->prepare($query);
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$count = 1;
if ($result->num_rows === 0) {
    echo "<tbody><tr><td colspan='5' style='text-align: center;'>No students found.</td></tr></tbody>";
} else {
    echo "<thead>
            <tr>
                <th>No.</th>
                <th>Student Name</th>
                <th>Faculty</th>
                <th>Contact Number</th>
                <th>Actions</th>
            </tr>
          </thead>
          <tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>$count</td>
                <td>" . htmlspecialchars($row['username']) . "</td>
                <td>" . htmlspecialchars($row['faculty'] ?? 'N/A') . "</td>
                <td>" . htmlspecialchars($row['contact_number'] ?? 'N/A') . "</td>
                <td>
                    <button type='button' class='btn btn-outline-warning' data-bs-toggle='modal'
                        data-bs-target='#updateModal'
                        data-id='" . $row['id'] . "'>Update</button>
                    <a href='student_view_admin.php?delete_id=" . $row['id'] . "'
                        class='btn btn-outline-danger'
                        onclick=\"return confirm('Are you sure you want to delete this student?')\">Delete</a>
                </td>
              </tr>";
        $count++;
    }
    echo "</tbody>";
}
$stmt->close();