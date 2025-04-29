<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
include("database.php");

$student_id = $_SESSION['id'] ?? null;

if (!$student_id) {
    echo "<script>
        alert('Please login to continue.');
        window.location.href = 'login.php';
    </script>";
    exit();
}
$email = $_SESSION['email'];

$query = mysqli_query($con, "SELECT users.id, 
users.username, 
users.email, 
users.contact_number, 
users.role_id,
roles.role_name, 
students.faculty
FROM users
INNER JOIN roles ON users.role_id = roles.id
INNER JOIN students ON students.user_id = users.id
WHERE users.email = '$email'");

while ($result = mysqli_fetch_assoc($query)) {
    $res_id = $result['id'];
    $res_username = $result['username'];
    $res_email = $result['email'];
    $res_role = $result['role_id'];
    $res_role_name = $result['role_name'];
    $res_faculty = $result['faculty'];
    $res_contact = $result['contact_number'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointment Records</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- Comment out missing style.css to avoid 404 error -->
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        header nav a:not(.bg-red-500),
        footer ul a {
            text-decoration: none !important;
        }

        .status-pending {
            color: #f39c12;
            font-weight: bold;
        }

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

<body class="bg-gray-100 font-merriweather">
    <header class="bg-gradient-to-r from-blue-600 to-indigo-800 text-white shadow-lg">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="home.php"><img src="assets/images/logo.png" alt="logo"
                            class="w-16 h-auto transition-transform transform hover:scale-110"></a>
                    <a href="home.php"
                        class="text-2xl font-bold tracking-wide hover:text-blue-200 transition-colors">Appointment
                        Management System</a>
                </div>
                <div>
                    <ul class="flex space-x-6 items-center">
                        <li><a href="home.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Home</a>
                        </li>
                        <li><a href="lecturer_list.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Create
                                Appointments</a></li>
                        <li><a href="appointment_view.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Appointments</a>
                        </li>
                        <li><a href="edit_profile.php?id=<?php echo htmlspecialchars($res_id); ?>"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Edit
                                Profile</a></li>
                        <li><a href="logout.php"
                                class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors duration-300">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 py-8">
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-center mb-4">Appointments</h2>
            <div class="flex justify-between items-center mb-4">
                <div class="relative w-1/4">
                    <input type="text" id="search-title" placeholder="Search by title"
                        class="border p-2 rounded w-full">
                    <button id="clear-search"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        style="display: none;">×</button>
                </div>
            </div>
            <div class="flex justify-end">
                <a href="lecturer_list.php"
                    class="bg-blue-500 text-white no-underline px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Create Appointment
                </a>
            </div>
        </div>

        <div class="space-y-4" id="appointment-list">
            <?php
            $sel_query = "SELECT appointments.*, 
                u1.username AS student_name,
                u1.email AS student_email,
                u2.username AS lecturer_name,
                u2.email AS lecturer_email 
            FROM appointments 
            JOIN users u1 ON appointments.student_id = u1.id 
            JOIN lecturers l ON appointments.lecturer_id = l.id 
            JOIN users u2 ON l.user_id = u2.id 
            WHERE appointments.student_id = ? OR appointments.lecturer_id = ? 
            ORDER BY appointments.id DESC";

            $stmt = $con->prepare($sel_query);
            if (!$stmt) {
                echo "<p class='text-red-500'>SQL Error: " . mysqli_error($con) . "</p>";
                exit();
            }

            $stmt->bind_param('ii', $res_id, $res_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                echo "<p class='text-center text-gray-500'>No appointments found.</p>";
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    $statusText = $row['status'] ?: 'Pending';
                    $statusClass = '';
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
            ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row['title']); ?>
                                </h3>
                                <p class="text-sm text-gray-600"><span class="font-medium">Student:</span>
                                    <?php echo htmlspecialchars($row['student_name']); ?></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Lecturer:</span>
                                    <?php echo htmlspecialchars($row['lecturer_name']); ?></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">From:</span>
                                    <?php echo htmlspecialchars($row['start_datetime']); ?></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">To:</span>
                                    <?php echo htmlspecialchars($row['end_datetime']); ?></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Description:</span>
                                    <?php echo htmlspecialchars($row['description']); ?></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Location:</span>
                                    <?php echo htmlspecialchars($row['location']); ?></p>
                                <p class="text-sm <?php echo $statusClass; ?>"><span class="font-medium">Status:</span>
                                    <?php echo htmlspecialchars($statusText); ?></p>
                            </div>
                            <div>
                                <?php if ($statusText !== 'Cancelled'): ?>
                                    <button type="button"
                                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                        data-bs-toggle="modal" data-bs-target="#updateModal"
                                        data-id="<?php echo $row['id']; ?>">More...</button>
                                <?php else: ?>
                                    <button type="button" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg cursor-not-allowed"
                                        disabled>Cancelled</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            $stmt->close();
            ?>
        </div>

        <!-- Create New Appointment Modal -->
        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true"
            data-bs-backdrop='static'>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="createModalLabel">Create New Appointment Request</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="appointment_insert.php" method="post">
                            <div class="form-group row">
                                <label for="title" class="col-sm-2 col-form-label col-form-label-lg">Title</label>
                                <div class="col-sm-10">
                                    <input required type="text" class="form-control form-control-lg" id="title"
                                        name="title" placeholder="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="form-group col-md-6">
                                    <label for="requester_email">Your email</label>
                                    <input required type="email" class="form-control" id="requester_email"
                                        name="requester_email">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="accepter_email">Guest email</label>
                                    <input required type="email" class="form-control" id="accepter_email"
                                        name="accepter_email">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="form-group mb-4">
                                    <label for="from_time">From</label>
                                    <input required type="datetime-local" class="form-control" id="from_time"
                                        name="from_time">
                                </div>
                                <div class="form-group mb-4">
                                    <label for="to_time">To</label>
                                    <input required type="datetime-local" class="form-control" id="to_time"
                                        name="to_time">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="form-group col-md-6">
                                    <label for="location">Location</label>
                                    <input required type="text" class="form-control" id="location" name="location">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea required class="form-control" id="description" name="description" rows="3"
                                    placeholder="Description"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </form>
                    </div>
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
                    <div id="viewOnlyMessage" style="display: none;" class="alert alert-info">You are viewing this
                        appointment in read-only mode.</div>
                    <div id="accepterMessage" style="display: none;" class="alert alert-warning">As the accepter, you
                        can only update the status of this appointment.</div>
                    <form action="appointment_update.php" method="post">
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="update_status"
                                    class="col-sm-2 col-form-label col-form-label-lg">Status</label>
                                <div class="col-sm-10">
                                    <select class="form-control" id="update_status" name="status">
                                        <option value=""></option>
                                        <option value="Pending">Pending</option>
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
    </main>

    <?php include("footer.php"); ?>

    <script>
        $(document).ready(function() {
            console.log('Document ready. Checking for #appointment-list...');
            const appointmentListCheck = $('#appointment-list');
            console.log('Initial #appointment-list check:', appointmentListCheck.length ? 'Found' : 'Not found');

            const updateButtons = document.querySelectorAll('button[data-bs-target="#updateModal"]');

            function attachUpdateButtonListeners() {
                console.log('Attaching update button listeners...');
                document.querySelectorAll('button[data-bs-target="#updateModal"]').forEach(button => {
                    button.removeEventListener('click', handleUpdateButtonClick);
                    button.addEventListener('click', handleUpdateButtonClick);
                });
            }

            function handleUpdateButtonClick() {
                const appointmentId = this.getAttribute('data-id');
                console.log('Fetching appointment data for ID:', appointmentId);
                fetch('appointment_fetch.php?id=' + appointmentId)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Server error:', data.error);
                            alert('Error fetching appointment data: ' + data.error);
                            return;
                        }

                        const formatDateTime = (dateTime) => {
                            const date = new Date(dateTime);
                            return date.toISOString().slice(0, 16);
                        };

                        document.getElementById('update_status').value = data.status || 'Pending';
                        document.getElementById('update_title').value = data.title || '';
                        document.getElementById('update_requester_email').value = data.requester_email || '';
                        document.getElementById('update_accepter_email').value = data.accepter_email || '';
                        document.getElementById('update_from_time').value = data.from_time ? formatDateTime(data
                            .from_time) : '';
                        document.getElementById('update_to_time').value = data.to_time ? formatDateTime(data
                            .to_time) : '';
                        document.getElementById('update_location').value = data.location || '';
                        document.getElementById('update_description').value = data.description || '';
                        document.getElementById('update_appointment_id').value = data.id || '';

                        const isAccepter = (data.current_user_id == data.accepter_id);
                        const isRequester = (data.current_user_id == data.requester_id);

                        if (isAccepter && !isRequester) {
                            document.querySelectorAll(
                                    '#updateModal input:not(#update_status), #updateModal textarea')
                                .forEach(el => {
                                    el.disabled = true;
                                });
                            document.getElementById('update_status').disabled = false;
                            document.querySelector('#updateModal .btn-primary').style.display = 'block';
                            document.getElementById('accepterMessage').style.display = 'block';
                            document.getElementById('viewOnlyMessage').style.display = 'none';
                        } else if (!isRequester && data.current_user_role !== 3) {
                            document.querySelectorAll(
                                    '#updateModal input, #updateModal select, #updateModal textarea')
                                .forEach(el => {
                                    el.disabled = true;
                                });
                            document.querySelector('#updateModal .btn-primary').style.display = 'none';
                            document.getElementById('viewOnlyMessage').style.display = 'block';
                            document.getElementById('accepterMessage').style.display = 'none';
                        } else {
                            document.querySelectorAll(
                                    '#updateModal input, #updateModal select, #updateModal textarea')
                                .forEach(el => {
                                    el.disabled = false;
                                });
                            document.querySelector('#updateModal .btn-primary').style.display = 'block';
                            document.getElementById('viewOnlyMessage').style.display = 'none';
                            document.getElementById('accepterMessage').style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching appointment data:', error);
                        alert('Failed to fetch appointment data. Check the console for details.');
                    });
            }

            function showConfirmationModal() {
                const confirmationModal = new bootstrap.Modal(document.getElementById('confirmModalCenter'));
                confirmationModal.show();
            }

            function submitForm() {
                const confirmationModal = bootstrap.Modal.getInstance(document.getElementById(
                    'confirmModalCenter'));
                confirmationModal.hide();
                document.querySelector('#updateModal form').submit();
            }

            // Simplified search without debounce to rule out timing issues
            $('#search-title').on('input', function() {
                var searchTerm = $(this).val().trim();
                console.log('Search term:', searchTerm);

                const appointmentList = $('#appointment-list');
                console.log('Before AJAX - #appointment-list exists:', appointmentList.length ? 'Yes' :
                    'No');
                if (!appointmentList.length) {
                    console.error('appointment-list element not found before AJAX');
                    alert('Error: Appointment list container not found. Please refresh the page.');
                    return;
                }

                appointmentList.prepend('<div class="loading">Loading...</div>');
                $('.loading').show();

                $.ajax({
                    url: 'appointment_search.php',
                    type: 'POST',
                    data: {
                        search: searchTerm
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('.loading').remove();
                        console.log('AJAX response:', response);

                        const appointmentListAfter = $('#appointment-list');
                        console.log('After AJAX - #appointment-list exists:',
                            appointmentListAfter.length ? 'Yes' : 'No');
                        if (!appointmentListAfter.length) {
                            console.error('appointment-list element not found after AJAX');
                            alert(
                                'Error: Appointment list container not found after search. Please refresh the page.'
                            );
                            return;
                        }

                        if (response.error) {
                            alert('Error: ' + response.error + '\nDebug: ' + JSON.stringify(
                                response.debug));
                            return;
                        }
                        appointmentListAfter.html(response.html);
                        attachUpdateButtonListeners();
                    },
                    error: function(xhr, status, error) {
                        $('.loading').remove();
                        console.error('AJAX Error:', status, error);
                        console.log('Raw response:', xhr.responseText);
                        alert(
                            'Failed to fetch search results. Raw response logged to console.'
                        );
                    }
                });
            });

            $('#search-title').on('input', function() {
                $('#clear-search').toggle(!!$(this).val());
            });

            $('#clear-search').on('click', function() {
                $('#search-title').val('').trigger('input');
            });

            // Initial attachment of event listeners
            attachUpdateButtonListeners();
        });
    </script>
    <!-- Ensure script.js is commented out to avoid conflicts -->
    <!-- <script src="assets/js/script.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>