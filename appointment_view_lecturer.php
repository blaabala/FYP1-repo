<?php
session_start();
include('database.php');
include('header_lecturer.php');

// Fetch the lecturer's ID from the lecturers table
$current_user_id = $_SESSION['id'] ?? null;

if (!$current_user_id) {
    echo "<script>
        alert('Please login to continue.');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Fetch the lecturer_id from the lecturers table based on user_id
$lecturer_query = "SELECT id FROM lecturers WHERE user_id = ?";
$lecturer_stmt = $con->prepare($lecturer_query);
$lecturer_stmt->bind_param('i', $current_user_id);
$lecturer_stmt->execute();
$lecturer_result = $lecturer_stmt->get_result();
$lecturer = $lecturer_result->fetch_assoc();
$lecturer_stmt->close();

if (!$lecturer) {
    echo "<script>
        alert('Lecturer profile not found. Please contact the administrator.');
        window.location.href = 'login.php';
    </script>";
    exit();
}

$lecturer_id = $lecturer['id'];

// Handle POST request for updating an appointment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_id = $_POST['appointment_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $requester_email = $_POST['requester_email'] ?? '';
    $accepter_email = $_POST['accepter_email'] ?? '';
    $from_time = $_POST['from_time'] ?? '';
    $to_time = $_POST['to_time'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!$current_user_id || !$appointment_id) {
        error_log("Missing user ID or appointment ID");
        $_SESSION['error_message'] = "Invalid request data.";
        header("Location: appointment_view_lecturer.php");
        exit();
    }

    // Fetch the appointment details to determine permissions
    $fetch_query = "SELECT * FROM appointments WHERE id = ?";
    $fetch_stmt = $con->prepare($fetch_query);
    $fetch_stmt->bind_param('i', $appointment_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    $appointment = $result->fetch_assoc();
    $fetch_stmt->close();

    if (!$appointment) {
        error_log("Appointment not found: " . $appointment_id);
        $_SESSION['error_message'] = "Appointment not found.";
        header("Location: appointment_view_lecturer.php");
        exit();
    }

    // Determine user permissions
    $is_accepter = ($appointment['lecturer_id'] == $lecturer_id);
    $is_admin = ($_SESSION['role_id'] == 3); // Assuming role_id 3 is admin

    // Check if the user has permission to update
    if (!$is_accepter && !$is_admin) {
        error_log("Unauthorized update attempt by user: " . $current_user_id);
        $_SESSION['error_message'] = "You don't have permission to update this appointment.";
        header("Location: appointment_view_lecturer.php");
        exit();
    }

    // If accepter (but not admin), only allow status update
    if ($is_accepter && !$is_admin) {
        if (empty($status)) {
            error_log("Missing status for accepter update");
            $_SESSION['error_message'] = "Status is required.";
            header("Location: appointment_view_lecturer.php");
            exit();
        }
        $update_query = "UPDATE appointments SET status = ? WHERE id = ?";
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param('si', $status, $appointment_id);
    } else {
        // Full update for admin
        if (empty($title) || empty($requester_email) || empty($accepter_email) || empty($from_time) || empty($to_time) || empty($location) || empty($status)) {
            $_SESSION['error_message'] = "All fields are required.";
            header("Location: appointment_view_lecturer.php");
            exit();
        }

        // Fetch student_id and lecturer_id based on emails
        $student_query = "SELECT id FROM users WHERE email = ?";
        $student_stmt = $con->prepare($student_query);
        $student_stmt->bind_param('s', $requester_email);
        $student_stmt->execute();
        $student_result = $student_stmt->get_result();
        $student = $student_result->fetch_assoc();
        $student_stmt->close();

        $lecturer_query = "SELECT l.id FROM lecturers l JOIN users u ON l.user_id = u.id WHERE u.email = ?";
        $lecturer_stmt = $con->prepare($lecturer_query);
        $lecturer_stmt->bind_param('s', $accepter_email);
        $lecturer_stmt->execute();
        $lecturer_result = $lecturer_stmt->get_result();
        $lecturer = $lecturer_result->fetch_assoc();
        $lecturer_stmt->close();

        if (!$student || !$lecturer) {
            $_SESSION['error_message'] = "Invalid requester or accepter email.";
            header("Location: appointment_view_lecturer.php");
            exit();
        }

        $student_id = $student['id'];
        $lecturer_id = $lecturer['id'];

        $update_query = "UPDATE appointments 
                         SET title = ?, student_id = ?, lecturer_id = ?, start_datetime = ?, end_datetime = ?, location = ?, description = ?, status = ?
                         WHERE id = ?";
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param('siisssssi', $title, $student_id, $lecturer_id, $from_time, $to_time, $location, $description, $status, $appointment_id);
    }

    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Appointment updated successfully.";
    } else {
        error_log("SQL Error: " . $update_stmt->error);
        $_SESSION['error_message'] = "Error updating appointment: " . $update_stmt->error;
    }

    $update_stmt->close();
    header("Location: appointment_view_lecturer.php");
    exit();
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<style>
.status-confirmed {
    color: #27ae60;
    font-weight: bold;
}

.status-cancelled {
    color: #c0392b;
    font-weight: bold;
}

.status-rejected {
    color: #e74c3c;
    font-weight: bold;
}

.status-completed {
    color: #3498db;
    font-weight: bold;
}

.product-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.product-table th,
.product-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.product-table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.product-table tr:hover {
    background-color: #f1f1f1;
}
</style>

<main>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
                    unset($_SESSION['success_message']);
                }

                if (isset($_SESSION['error_message'])) {
                    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
                    unset($_SESSION['error_message']);
                }
                ?>
                <div class="card">
                    <div class="card-header">
                        <h2 style="text-align: center;">View Appointment Records</h2>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <!-- Create button can be added if needed -->
                        </div>
                    </div>

                    <div class="card-body">
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
                                <?php
                                $count = 1;
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
                                      ORDER BY appointments.id DESC";
                                $stmt = $con->prepare($sel_query);
                                $stmt->bind_param('i', $lecturer_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows === 0) {
                                    echo "<tr><td colspan='9' class='text-center'>No appointments found.</td></tr>";
                                } else {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $statusText = $row['status'] ?: 'Confirmed'; // Default to Confirmed as per schema
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
                                ?>
                                <tr>
                                    <td><?php echo $count; ?></td>
                                    <td><?php echo htmlspecialchars($row["requester_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["start_datetime"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["end_datetime"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["description"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["location"]); ?></td>
                                    <td class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusText); ?>
                                    </td>
                                    <td>
                                        <?php if ($statusText !== 'Cancelled'): ?>
                                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                            data-bs-target="#updateModal"
                                            data-id="<?php echo $row['id']; ?>">Update</button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-outline-secondary"
                                            disabled>Cancelled</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                        $count++;
                                    }
                                }
                                $stmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Update Appointment Modal -->
            <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true"
                data-bs-backdrop='static'>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="updateModalLabel">Update Appointment Request</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div id="viewOnlyMessage" style="display: none;" class="alert alert-info">
                            You are viewing this appointment in read-only mode.
                        </div>
                        <div id="accepterMessage" style="display: none;" class="alert alert-warning">
                            As the accepter, you can only update the status of this appointment.
                        </div>
                        <form action="appointment_view_lecturer.php" method="post">
                            <div class="modal-body">
                                <div class="form-group row">
                                    <label for="update_status"
                                        class="col-sm-2 col-form-label col-form-label-lg">Status</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" id="update_status" name="status">
                                            <option value=""></option>
                                            <option value="Confirmed">Confirmed</option>
                                            <option value="Rejected">Rejected</option>
                                            <option value="Cancelled">Cancelled</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="appointment_id" id="update_appointment_id" value="">
                                <div class="form-group row">
                                    <label for="update_title"
                                        class="col-sm-2 col-form-label col-form-label-lg">Title</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control form-control-lg" id="update_title"
                                            name="title" placeholder="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="form-group col-md-6">
                                        <label for="update_requester_email">Requester email</label>
                                        <input type="email" class="form-control" id="update_requester_email"
                                            name="requester_email" value="">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="update_accepter_email">Accepter email</label>
                                        <input type="email" class="form-control" id="update_accepter_email"
                                            name="accepter_email" value="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="form-group mb-4">
                                        <label for="update_from_time">From</label>
                                        <input type="datetime-local" class="form-control" id="update_from_time"
                                            name="from_time">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="update_to_time">To</label>
                                        <input type="datetime-local" class="form-control" id="update_to_time"
                                            name="to_time">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="form-group col-md-6">
                                        <label for="update_location">Location</label>
                                        <input type="text" class="form-control" id="update_location" name="location">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="update_description">Description</label>
                                    <textarea class="form-control" id="update_description" name="description" rows="3"
                                        placeholder="Description"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="button"
                                    onclick="showConfirmationModal()">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmModalCenter" tabindex="-1" role="dialog"
                aria-labelledby="confirmModalCenterTitle" aria-hidden="true" data-bs-backdrop='static'>
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmModalLongTitle">Please Confirm</h5>
                        </div>
                        <div class="modal-body">
                            Are you sure that you want to submit changes?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" onclick="submitForm()">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include("footer_lecturer.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateButtons = document.querySelectorAll('button[data-bs-target="#updateModal"]');

    updateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-id');

            fetch('appointment_fetch.php?id=' + appointmentId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response
                            .statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Server error:', data.error);
                        alert('Error fetching appointment data: ' + data.error);
                        return;
                    }

                    // Ensure the date format is compatible with datetime-local (YYYY-MM-DDThh:mm)
                    const formatDateTime = (dateTime) => {
                        const date = new Date(dateTime);
                        return date.toISOString().slice(0,
                        16); // Cuts off seconds and timezone
                    };

                    document.getElementById('update_status').value = data.status ||
                        'Confirmed';
                    document.getElementById('update_title').value = data.title || '';
                    document.getElementById('update_requester_email').value = data
                        .requester_email || '';
                    document.getElementById('update_accepter_email').value = data
                        .accepter_email || '';
                    document.getElementById('update_from_time').value = data.from_time ?
                        formatDateTime(data.from_time) : '';
                    document.getElementById('update_to_time').value = data.to_time ?
                        formatDateTime(data.to_time) : '';
                    document.getElementById('update_location').value = data.location || '';
                    document.getElementById('update_description').value = data
                        .description || '';
                    document.getElementById('update_appointment_id').value = data.id || '';

                    const isAccepter = (data.current_user_id == data.accepter_id);
                    const isAdmin = (data.current_user_role == 3);

                    if (isAccepter && !isAdmin) {
                        document.querySelectorAll(
                                '#updateModal input:not(#update_status), #updateModal textarea'
                                )
                            .forEach(el => {
                                el.disabled = true;
                            });
                        document.getElementById('update_status').disabled = false;
                        document.querySelector('#updateModal .btn-primary').style.display =
                            'block';
                        document.getElementById('accepterMessage').style.display = 'block';
                        document.getElementById('viewOnlyMessage').style.display = 'none';
                    } else if (!isAdmin) {
                        document.querySelectorAll(
                                '#updateModal input, #updateModal select, #updateModal textarea'
                                )
                            .forEach(el => {
                                el.disabled = true;
                            });
                        document.querySelector('#updateModal .btn-primary').style.display =
                            'none';
                        document.getElementById('viewOnlyMessage').style.display = 'block';
                        document.getElementById('accepterMessage').style.display = 'none';
                    } else {
                        document.querySelectorAll(
                                '#updateModal input, #updateModal select, #updateModal textarea'
                                )
                            .forEach(el => {
                                el.disabled = false;
                            });
                        document.querySelector('#updateModal .btn-primary').style.display =
                            'block';
                        document.getElementById('viewOnlyMessage').style.display = 'none';
                        document.getElementById('accepterMessage').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching appointment data:', error);
                    alert(
                        'Failed to fetch appointment data. Check the console for details.');
                });
        });
    });
});

function showConfirmationModal() {
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmModalCenter'));
    confirmationModal.show();
}

function submitForm() {
    const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('confirmModalCenter'));
    confirmationModal.hide();
    document.querySelector('#updateModal form').submit();
}
</script>
<script src="assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>
</body>

</html>
<?php
$con->close();
?>