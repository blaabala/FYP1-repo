<?php
include('database.php');
include('header_lecturer.php');

$current_user_id = $_SESSION['id'] ?? null;
if (!$current_user_id) {
    echo "<script>alert('Please login to continue.'); window.location.href = 'login.php';</script>";
    exit();
}

// Fetch lecturer_id
$lecturer_query = "SELECT id FROM lecturers WHERE user_id = ?";
$lecturer_stmt = $con->prepare($lecturer_query);
$lecturer_stmt->bind_param('i', $current_user_id);
$lecturer_stmt->execute();
$lecturer_result = $lecturer_stmt->get_result();
$lecturer = $lecturer_result->fetch_assoc();
$lecturer_stmt->close();

if (!$lecturer) {
    echo "<script>alert('Lecturer profile not found. Please contact the administrator.'); window.location.href = 'login.php';</script>";
    exit();
}
$lecturer_id = $lecturer['id'];

// Function to validate 00/30-minute intervals
function validateTimeInterval($datetime)
{
    $date = new DateTime($datetime);
    $minutes = (int)$date->format('i');
    return $minutes === 0 || $minutes === 30;
}

// Check for conflicts with lecturer availability and blocked dates
function checkConflicts($lecturer_id, $start_datetime, $end_datetime, $appointment_id, $con)
{
    // Check blocked dates
    $query = "SELECT * FROM blocked_dates WHERE lecturer_id = ? AND ((start_date <= ? AND end_date >= ?) OR (start_date >= ? AND start_date <= ?) OR (end_date >= ? AND end_date <= ?))";
    $stmt = $con->prepare($query);
    $stmt->bind_param("issssss", $lecturer_id, $end_datetime, $start_datetime, $start_datetime, $end_datetime, $start_datetime, $end_datetime);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return "Conflicts with blocked dates.";
    }
    $stmt->close();

    // Check existing appointments (exclude the current appointment being updated)
    $query = "SELECT * FROM appointments WHERE lecturer_id = ? AND id != ? AND status IN ('Confirmed', 'Completed') AND ((start_datetime <= ? AND end_datetime >= ?) OR (start_datetime >= ? AND start_datetime <= ?) OR (end_datetime >= ? AND end_datetime <= ?))";
    $stmt = $con->prepare($query);
    $stmt->bind_param("iissssss", $lecturer_id, $appointment_id, $end_datetime, $start_datetime, $start_datetime, $end_datetime, $start_datetime, $end_datetime);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return "Conflicts with existing appointments.";
    }
    $stmt->close();

    return false;
}

// Handle POST request for updating an appointment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $appointment_id = $_POST['appointment_id'] ?? null;
        $status = $_POST['status'] ?? '';

        if (!$current_user_id || !$appointment_id || !$status) {
            throw new Exception("Invalid request data. All fields are required.");
        }

        // Fetch appointment details
        $fetch_query = "SELECT * FROM appointments WHERE id = ?";
        $fetch_stmt = $con->prepare($fetch_query);
        $fetch_stmt->bind_param('i', $appointment_id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();
        $appointment = $result->fetch_assoc();
        $fetch_stmt->close();

        if (!$appointment) {
            throw new Exception("Appointment not found.");
        }

        // Determine permissions
        $is_accepter = ($appointment['lecturer_id'] == $lecturer_id);
        $is_admin = ($_SESSION['role_id'] == 3);

        if (!$is_accepter && !$is_admin) {
            throw new Exception("You don't have permission to update this appointment.");
        }

        if ($is_accepter && !$is_admin) {
            // Lecturers can only update status
            $update_query = "UPDATE appointments SET status = ? WHERE id = ?";
            $update_stmt = $con->prepare($update_query);
            $update_stmt->bind_param('si', $status, $appointment_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Error updating appointment status: " . $update_stmt->error);
            }
            $_SESSION['success_message'] = "Appointment status updated successfully.";
            $update_stmt->close();
        } else {
            // Admins can update all fields
            $title = trim($_POST['title'] ?? '');
            $requester_email = filter_var(trim($_POST['requester_email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $accepter_email = filter_var(trim($_POST['accepter_email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $from_time = $_POST['from_time'] ?? '';
            $to_time = $_POST['to_time'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (!$title || !$requester_email || !$accepter_email || !$from_time || !$to_time || !$location) {
                throw new Exception("All fields are required.");
            }

            // Validate time intervals
            if (!validateTimeInterval($from_time) || !validateTimeInterval($to_time)) {
                throw new Exception("Times must be on the hour (e.g., 10:00) or half-hour (e.g., 10:30).");
            }

            // Validate start < end
            $start_dt = new DateTime($from_time);
            $end_dt = new DateTime($to_time);
            if ($start_dt >= $end_dt) {
                throw new Exception("End time must be after start time.");
            }

            // Check for conflicts
            $conflict = checkConflicts($lecturer_id, $from_time, $to_time, $appointment_id, $con);
            if ($conflict) {
                throw new Exception($conflict);
            }

            // Fetch student_id and lecturer_id
            $student_query = "SELECT s.id FROM students s JOIN users u ON s.user_id = u.id WHERE u.email = ?";
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
                throw new Exception("Invalid requester or accepter email.");
            }

            $student_id = $student['id'];
            $lecturer_id = $lecturer['id'];

            $update_query = "UPDATE appointments SET title = ?, student_id = ?, lecturer_id = ?, start_datetime = ?, end_datetime = ?, location = ?, description = ?, status = ? WHERE id = ?";
            $update_stmt = $con->prepare($update_query);
            $update_stmt->bind_param('siisssssi', $title, $student_id, $lecturer_id, $from_time, $to_time, $location, $description, $status, $appointment_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Error updating appointment: " . $update_stmt->error);
            }
            $_SESSION['success_message'] = "Appointment updated successfully.";
            $update_stmt->close();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    header("Location: appointment_view_lecturer.php");
    exit();
}

// Fetch appointments for display
$sel_query = "SELECT appointments.*, u1.username AS requester_name, u1.email AS requester_email, u2.username AS accepter_name, u2.email AS accepter_email 
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
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointment Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
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

        .loading {
            text-align: center;
            padding: 20px;
            display: none;
        }

        #clear-search {
            cursor: pointer;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <main>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 style="text-align: center;">View Appointment Records</h2>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <div class="relative">
                                    <input type="text" id="search-filter" placeholder="Search appointments..."
                                        class="border p-2 rounded">
                                    <button id="clear-search"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                        style="display: none;">×</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="appointment-list">
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
                                                    <td><?php echo htmlspecialchars($row["start_datetime"]); ?></td>
                                                    <td><?php echo htmlspecialchars($row["end_datetime"]); ?></td>
                                                    <td><?php echo htmlspecialchars($row["description"]); ?></td>
                                                    <td><?php echo htmlspecialchars($row["location"]); ?></td>
                                                    <td class="<?php echo $statusClass; ?>">
                                                        <?php echo htmlspecialchars($statusText); ?></td>
                                                    <td>
                                                        <?php if ($statusText !== 'Cancelled'): ?>
                                                            <button type="button" class="btn btn-outline-warning"
                                                                data-bs-toggle="modal" data-bs-target="#updateModal"
                                                                data-id="<?php echo $row['id']; ?>">Update</button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                disabled>Cancelled</button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php $count++; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Appointment Modal -->
                <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel"
                    aria-hidden="true" data-bs-backdrop='static'>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="updateModalLabel">Update Appointment Request</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="appointment_view_lecturer.php" method="post" id="updateForm">
                                <div class="modal-body">
                                    <input type="hidden" name="appointment_id" id="update_appointment_id" value="">
                                    <?php if ($_SESSION['role_id'] != 3): ?>
                                        <!-- Lecturers can only update status -->
                                        <div class="form-group row">
                                            <label for="update_status"
                                                class="col-sm-2 col-form-label col-form-label-lg">Status</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" id="update_status" name="status" required>
                                                    <option value="Confirmed">Confirmed</option>
                                                    <option value="Rejected">Rejected</option>
                                                    <option value="Cancelled">Cancelled</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Admins can update all fields -->
                                        <div class="form-group row">
                                            <label for="update_status"
                                                class="col-sm-2 col-form-label col-form-label-lg">Status</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" id="update_status" name="status" required>
                                                    <option value="Confirmed">Confirmed</option>
                                                    <option value="Rejected">Rejected</option>
                                                    <option value="Cancelled">Cancelled</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="update_title"
                                                class="col-sm-2 col-form-label col-form-label-lg">Title</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control form-control-lg" id="update_title"
                                                    name="title" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="form-group col-md-6">
                                                <label for="update_requester_email">Requester email</label>
                                                <input type="email" class="form-control" id="update_requester_email"
                                                    name="requester_email" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="update_accepter_email">Accepter email</label>
                                                <input type="email" class="form-control" id="update_accepter_email"
                                                    name="accepter_email" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="form-group mb-4">
                                                <label for="update_from_time">From</label>
                                                <input type="datetime-local" class="form-control" id="update_from_time"
                                                    name="from_time" required>
                                            </div>
                                            <div class="form-group mb-4">
                                                <label for="update_to_time">To</label>
                                                <input type="datetime-local" class="form-control" id="update_to_time"
                                                    name="to_time" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="form-group col-md-6">
                                                <label for="update_location">Location</label>
                                                <input type="text" class="form-control" id="update_location" name="location"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="update_description">Description</label>
                                            <textarea class="form-control" id="update_description" name="description"
                                                rows="3"></textarea>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
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

    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
        $(document).ready(function() {
            $('#search-filter').on('input', function() {
                var searchTerm = $(this).val().trim();
                const appointmentList = $('#appointment-list');
                appointmentList.prepend('<div class="loading">Loading...</div>');
                $('.loading').show();

                $.ajax({
                    url: 'appointment_search_lecturer.php',
                    type: 'POST',
                    data: {
                        search: searchTerm,
                        lecturer_id: <?php echo $lecturer_id; ?>
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('.loading').remove();
                        if (response.error) {
                            alert('Error: ' + response.error);
                            return;
                        }
                        appointmentList.html(response.html);
                        attachUpdateButtonListeners();
                    },
                    error: function(xhr, status, error) {
                        $('.loading').remove();
                        alert('Failed to fetch search results.');
                    }
                });
            });

            $('#search-filter').on('input', function() {
                $('#clear-search').toggle(!!$(this).val());
            });

            $('#clear-search').on('click', function() {
                $('#search-filter').val('').trigger('input');
            });

            function attachUpdateButtonListeners() {
                document.querySelectorAll('button[data-bs-target="#updateModal"]').forEach(button => {
                    button.removeEventListener('click', handleUpdateButtonClick);
                    button.addEventListener('click', handleUpdateButtonClick);
                });

                function handleUpdateButtonClick() {
                    const appointmentId = this.getAttribute('data-id');
                    fetch('appointment_fetch.php?id=' + appointmentId)
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok: ' + response
                                .statusText);
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                alert('Error fetching appointment data: ' + data.error);
                                return;
                            }

                            const formatDateTime = (dateTime) => {
                                if (!dateTime) return '';
                                return new Date(dateTime).toISOString().slice(0, 16);
                            };

                            document.getElementById('update_status').value = data.status || 'Confirmed';
                            document.getElementById('update_appointment_id').value = data.id || '';
                            <?php if ($_SESSION['role_id'] == 3): ?>
                                document.getElementById('update_title').value = data.title || '';
                                document.getElementById('update_requester_email').value = data.requester_email ||
                                    '';
                                document.getElementById('update_accepter_email').value = data.accepter_email || '';
                                document.getElementById('update_from_time').value = formatDateTime(data
                                    .start_datetime);
                                document.getElementById('update_to_time').value = formatDateTime(data.end_datetime);
                                document.getElementById('update_location').value = data.location || '';
                                document.getElementById('update_description').value = data.description || '';
                            <?php endif; ?>
                        })
                        .catch(error => {
                            console.error('Error fetching appointment data:', error);
                            alert('Failed to fetch appointment data.');
                        });
                }
            }

            attachUpdateButtonListeners();
        });

        function showConfirmationModal() {
            const confirmationModal = new bootstrap.Modal(document.getElementById('confirmModalCenter'));
            confirmationModal.show();
        }

        function submitForm() {
            const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('confirmModalCenter'));
            confirmationModal.hide();
            document.getElementById('updateForm').submit();
        }
    </script>
</body>

</html>

<?php $con->close(); ?>