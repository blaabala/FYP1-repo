<!DOCTYPE html>
<html lang="en">

<head>
    <title>AMS</title>
    <meta charset="utf-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="navdiv">
                <div class="image-container">
                    <a href="home_admin.php"><img src="assets/images/logo.png" alt="logo" class="nav-logo"
                            style="width: 70px; height: auto;"></a>
                    <a href="home_admin.php" class="logo-text">Appointment Management System</a>

                    <?php
                    session_start();
                    include("database.php");

                    $email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
                    if (!$email) {
                        $_SESSION['error_message'] = "Please log in to continue.";
                        header("Location: login.php");
                        exit();
                    }

                    $query = "SELECT users.*, roles.role_name 
                              FROM users 
                              JOIN roles ON users.role_id = roles.id 
                              WHERE users.email = ?";
                    $stmt = $con->prepare($query);
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 0) {
                        $_SESSION['error_message'] = "User not found. Please log in again.";
                        header("Location: login.php");
                        exit();
                    }

                    $user = $result->fetch_assoc();
                    $res_id = $user['id'];
                    $res_username = $user['username'];
                    $res_email = $user['email'];
                    $res_role_name = $user['role_name'];
                    $res_faculty = isset($user['faculty']) ? $user['faculty'] : null;
                    $res_contact = $user['contact_number'];

                    if (strtolower($res_role_name) !== 'admin') {
                        $_SESSION['error_message'] = "You must be an admin to view this page.";
                        header("Location: home.php");
                        exit();
                    }
                    ?>

                </div>
                <ul>
                    <li><a href="home_admin.php">Home</a></li>
                    <li><a href="appointment_view_admin.php">View Appointments</a></li>
                    <li><a href="user_view_admin.php">User Lists</a></li>
                    <li><?php echo "<a href='edit_profile_admin.php?id=$res_id'>Edit Profile</a>"; ?></li>
                    <button><a href="logout.php" class="logout-btn">Logout</a></button>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    if (isset($_SESSION['success_message'])) {
                        echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
                        unset($_SESSION['success_message']);
                    }

                    if (isset($_SESSION['error_message'])) {
                        echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error_message']) . "</div>";
                        unset($_SESSION['error_message']);
                    }
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 style="text-align: center;">View Appointment Records</h2>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <p>Create</p>
                                </button>
                            </div>
                            <div class="input-group mb-3" style="max-width: 300px; margin-top: 10px;">
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Search by name or ID">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table class="product-table" id="appointmentTable">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Student Name</th>
                                        <th>Lecturer Name</th>
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
                                                  ORDER BY appointments.id DESC";
                                    $stmt = $con->prepare($sel_query);
                                    if (!$stmt) {
                                        echo "<tr><td colspan='10' style='text-align: center;'>Error preparing query: " . htmlspecialchars($con->error) . "</td></tr>";
                                    } else {
                                        if (!$stmt->execute()) {
                                            echo "<tr><td colspan='10' style='text-align: center;'>Error executing query: " . htmlspecialchars($stmt->error) . "</td></tr>";
                                        } else {
                                            $result = $stmt->get_result();
                                            if ($result->num_rows === 0) {
                                                echo "<tr><td colspan='10' style='text-align: center;'>No appointments found.</td></tr>";
                                            } else {
                                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo htmlspecialchars($row["student_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["lecturer_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["title"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["formatted_start"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["formatted_end"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["description"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["location"]); ?></td>
                                        <?php
                                                        $statusClass = '';
                                                        $statusText = $row['status'];
                                                        switch ($statusText) {
                                                            // case 'Pending':
                                                            //     $statusClass = 'status-pending';
                                                            // break;
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
                                                        ?>
                                        <td>
                                            <?php if ($row['status'] !== 'Cancelled'): ?>
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
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Create New Appointment Modal -->
                <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel"
                    aria-hidden="true" data-bs-backdrop='static'>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="createModalLabel">Create New Appointment Request</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="appointment_insert_admin.php" method="post">
                                    <div class="form-group row">
                                        <label for="title"
                                            class="col-sm-2 col-form-label col-form-label-lg">Title</label>
                                        <div class="col-sm-10">
                                            <input required type="text" class="form-control form-control-lg" id="title"
                                                name="title" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-md-6">
                                            <label for="student_email">Student email</label>
                                            <input required type="email" class="form-control" id="student_email"
                                                name="student_email">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="lecturer_email">Lecturer email</label>
                                            <input required type="email" class="form-control" id="lecturer_email"
                                                name="lecturer_email">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group mb-4">
                                            <label for="start_datetime">From</label>
                                            <input required type="datetime-local" class="form-control"
                                                id="start_datetime" name="start_datetime" step="1800">
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="end_datetime">To</label>
                                            <input required type="datetime-local" class="form-control" id="end_datetime"
                                                name="end_datetime" step="1800">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-md-6">
                                            <label for="location">Location</label>
                                            <input required type="text" class="form-control" id="location"
                                                name="location">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea required class="form-control" id="description" name="description"
                                            rows="3" placeholder="Description"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Create</button>
                                    </div>
                                </form>
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
                            <form action="appointment_update_admin.php" method="post">
                                <div class="modal-body">
                                    <div class="form-group row">
                                        <label for="update_status"
                                            class="col-sm-2 col-form-label col-form-label-lg">Status</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" id="update_status" name="status">
                                                <!-- <option value="Pending">Pending</option> -->
                                                <option value="Confirmed">Confirmed</option>
                                                <option value="Cancelled">Cancelled</option>
                                                <option value="Rejected">Rejected</option>
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
                                                name="requester_email" readonly>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="update_accepter_email">Accepter email</label>
                                            <input type="email" class="form-control" id="update_accepter_email"
                                                name="accepter_email" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group mb-4">
                                            <label for="update_from_time">From</label>
                                            <input type="datetime-local" class="form-control" id="update_from_time"
                                                name="from_time" step="1800">
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="update_to_time">To</label>
                                            <input type="datetime-local" class="form-control" id="update_to_time"
                                                name="to_time" step="1800">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-md-6">
                                            <label for="update_location">Location</label>
                                            <input type="text" class="form-control" id="update_location"
                                                name="location">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="update_description">Description</label>
                                        <textarea class="form-control" id="update_description" name="description"
                                            rows="3" placeholder="Description"></textarea>
                                    </div>
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

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-icons">
                <a href=""><i class="fa-brands fa-facebook"></i></a>
                <a href=""><i class="fa-brands fa-instagram"></i></a>
                <a href=""><i class="fa-brands fa-google-plus"></i></a>
                <a href=""><i class="fa-brands fa-youtube"></i></a>
            </div>
            <div class="footer-nav">
                <ul>
                    <li><a href="home_admin.php">Home</a></li>
                    <li><a href="appointment_view_admin.php">View Appointments</a></li>
                    <li><a href="user_view_admin.php">User Lists</a></li>
                    <li><a href="edit_profile_admin.php">Edit Profile</a></li>
                </ul>
            </div>
            <div class="footer-bottom">
                <p>© 2024 LEE JUN KHANG. All rights reserved. </p>
            </div>
        </div>
    </footer>
    <script src="assets/js/script.js"></script>
    <script>
    // Ensure updateDateTime only runs if the element exists
    if (typeof updateDateTime === 'function' && document.getElementById('someElementId')) {
        updateDateTime();
    }

    // Validate datetime inputs to enforce 00/30-minute increments
    function validateDatetime(inputId) {
        const input = document.getElementById(inputId);
        const value = input.value;
        if (value) {
            const date = new Date(value);
            const minutes = date.getMinutes();
            if (minutes !== 0 && minutes !== 30) {
                alert('Time must be on the hour (e.g., 10:00) or half-hour (e.g., 10:30).');
                input.value = '';
            }
        }
    }

    // Attach validation to form submissions
    document.querySelector('#createModal form').addEventListener('submit', function(event) {
        validateDatetime('start_datetime');
        validateDatetime('end_datetime');
        if (!document.getElementById('start_datetime').value || !document.getElementById('end_datetime')
            .value) {
            event.preventDefault();
        }
    });

    document.querySelector('#updateModal form').addEventListener('submit', function(event) {
        validateDatetime('update_from_time');
        validateDatetime('update_to_time');
        if (!document.getElementById('update_from_time').value || !document.getElementById('update_to_time')
            .value) {
            event.preventDefault();
        }
    });

    function showConfirmationModal() {
        new bootstrap.Modal(document.getElementById('confirmModalCenter')).show();
    }

    function submitForm() {
        document.querySelector('#updateModal form').submit();
    }

    // Populate update modal with data
    var updateModal = document.getElementById('updateModal');
    updateModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var appointmentId = button.getAttribute('data-id');
        var form = updateModal.querySelector('form');
        form.querySelector('#update_appointment_id').value = appointmentId;

        fetch(`appointment_fetch_admin.php?id=${appointmentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                form.querySelector('#update_title').value = data.title || '';
                form.querySelector('#update_requester_email').value = data.student_email || '';
                form.querySelector('#update_accepter_email').value = data.lecturer_email || '';
                form.querySelector('#update_from_time').value = data.formatted_start || '';
                form.querySelector('#update_to_time').value = data.formatted_end || '';
                form.querySelector('#update_location').value = data.location || '';
                form.querySelector('#update_description').value = data.description || '';
                form.querySelector('#update_status').value = data.status || 'Pending';
            })
            .catch(error => console.error('Error fetching appointment data:', error));
    });

    // AJAX search functionality
    document.getElementById('searchButton').addEventListener('click', function() {
        var searchTerm = document.getElementById('searchInput').value;
        fetch(`appointment_search_admin.php?term=${encodeURIComponent(searchTerm)}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('appointmentTable').innerHTML = html;
            })
            .catch(error => console.error('Error searching appointments:', error));
    });

    document.getElementById('searchInput').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            document.getElementById('searchButton').click();
        }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>