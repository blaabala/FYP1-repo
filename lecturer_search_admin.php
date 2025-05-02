<?php
include("database.php");

$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';
$searchTerm = "%$searchTerm%";

$query = "SELECT users.id, users.username, users.email, users.contact_number, lecturers.faculty, lecturers.department, lecturers.designation, lecturers.office_no
          FROM users
          JOIN lecturers ON users.id = lecturers.user_id
          WHERE users.role_id = 1 AND (users.username LIKE ? OR users.id LIKE ?)
          ORDER BY users.id DESC";
$stmt = $con->prepare($query);
$stmt->bind_param('ss', $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$count = 1;
if ($result->num_rows === 0) {
    echo "<tbody><tr><td colspan='7' style='text-align: center;'>No lecturers found.</td></tr></tbody>";
} else {
    echo "<thead>
            <tr>
                <th>No.</th>
                <th>Lecturer Name</th>
                <th>Faculty</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Office No.</th>
                <th>Actions</th>
            </tr>
          </thead>
          <tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>$count</td>
                <td>" . htmlspecialchars($row['username']) . "</td>
                <td>" . htmlspecialchars($row['faculty'] ?? 'N/A') . "</td>
                <td>" . htmlspecialchars($row['department'] ?? 'N/A') . "</td>
                <td>" . htmlspecialchars($row['designation'] ?? 'N/A') . "</td>
                <td>" . htmlspecialchars($row['office_no'] ?? 'N/A') . "</td>
                <td>
                    <button type='button' class='btn btn-outline-warning' data-bs-toggle='modal'
                        data-bs-target='#updateModal'
                        data-id='" . $row['id'] . "'>Update</button>
                    <a href='lecturer_view_admin.php?delete_id=" . $row['id'] . "'
                        class='btn btn-outline-danger'
                        onclick=\"return confirm('Are you sure you want to delete this lecturer?')\">Delete</a>
                </td>
              </tr>";
        $count++;
    }
    echo "</tbody>";
}
$stmt->close();