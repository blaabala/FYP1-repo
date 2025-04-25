<?php
include('header.php');
?>
<!-- <link
    href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
    rel="stylesheet"> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

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
                        <!-- Button trigger modal -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <p>Create</p>
                            </button> -->
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="product-table">
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 1;
                                // Selecting all product records from the database
                                $sel_query = "SELECT appointments.*, 
                                                u1.username AS student_name, 
                                                u1.email AS student_email, 
                                                u2.username AS lecturer_name, 
                                                u2.email AS lecturer_email 
                                                FROM appointments 
                                                JOIN users u1 ON appointments.student_id = u1.id 
                                                JOIN users u2 ON appointments.lecturer_id = u2.id 
                                                WHERE appointments.student_id = ? OR appointments.lecturer_id = ?
                                                ORDER BY appointments.id DESC;";
                                $stmt = $con->prepare($sel_query);
                                $stmt->bind_param('ii', $res_id, $res_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo $row["student_name"]; ?></td>
                                        <td><?php echo $row["lecturer_name"]; ?></td>
                                        <td><?php echo $row["title"]; ?></td>
                                        <td><?php echo $row["start_datetime"]; ?></td>
                                        <td><?php echo $row["end_datetime"]; ?></td>
                                        <td><?php echo $row["description"]; ?></td>
                                        <td><?php echo $row["location"]; ?></td>

                                        <!-- status text color changes -->
                                        <?php
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
                                            default:
                                                $statusClass = '';
                                        }
                                        echo "<td class='$statusClass'>{$statusText}</td>";
                                        ?>
                                        <!-- Conditionally render the Update button -->
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
                                ?>
                            </tbody>
                        </table>

                    </div>
                </div>
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
            <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true"
                data-bs-backdrop='static'>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="updateModalLabel">Update Appointment Request</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button><br>
                        </div>
                        <div id="viewOnlyMessage" style="display: none;" class="alert alert-info">
                            You are viewing this appointment in read-only mode.
                        </div>
                        <div id="accepterMessage" style="display: none;" class="alert alert-warning">
                            As the accepter, you can only update the status of this appointment.
                        </div>
                        <form action="appointment_update.php" method="post">
                            <div class="modal-body">
                                <div class="form-group row">
                                    <label for="update_status"
                                        class="col-sm-2 col-form-label col-form-label-lg">Status</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" id="update_status" name="status">
                                            <option value="Pending" disabled default>Pending</option>
                                            <option value="Confirmed">Confirmed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <label for="update_title" class="col-sm-2 col-form-label col-form-label-lg"></label>
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
    document.addEventListener('DOMContentLoaded', function() {
        // const updateButtons = document.querySelectorAll('button[data-bs-target="#updateModal"]');

        // updateButtons.forEach(button => {
        //     button.addEventListener('click', function() {
        //         const appointmentId = this.getAttribute('data-id');
        //         fetch('appointment_fetch.php?id=' + appointmentId)
        //             .then(response => response.json())
        //             .then(data => {
        //                 document.getElementById('update_status').value = data.status;
        //                 document.getElementById('update_title').value = data.title;
        //                 document.getElementById('update_requester_email').value = data.requester_email;
        //                 document.getElementById('update_accepter_email').value = data.accepter_email;
        //                 document.getElementById('update_from_time').value = data.from_time;
        //                 document.getElementById('update_to_time').value = data.to_time;
        //                 document.getElementById('update_location').value = data.location;
        //                 document.getElementById('update_description').value = data.description;
        //                 document.getElementById('update_appointment_id').value = data.id;
        //             });
        //     });
        // });
        const updateButtons = document.querySelectorAll('button[data-bs-target="#updateModal"]');

        updateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const appointmentId = this.getAttribute('data-id');
                fetch('appointment_fetch.php?id=' + appointmentId)
                    .then(response => response.json())
                    .then(data => {
                        // Populate fields as before
                        document.getElementById('update_status').value = data.status;
                        document.getElementById('update_title').value = data.title;
                        document.getElementById('update_requester_email').value = data
                            .requester_email;
                        document.getElementById('update_accepter_email').value = data
                            .accepter_email;
                        document.getElementById('update_from_time').value = data.from_time;
                        document.getElementById('update_to_time').value = data.to_time;
                        document.getElementById('update_location').value = data.location;
                        document.getElementById('update_description').value = data.description;
                        document.getElementById('update_appointment_id').value = data.id;

                        const isAccepter = (data.current_user_id == data.accepter_id);
                        const isRequester = (data.current_user_id == data.requester_id);

                        if (isAccepter && !isRequester) {
                            // Disable all input fields except status
                            document.querySelectorAll(
                                '#updateModal input:not(#update_status), #updateModal textarea'
                            ).forEach(el => {
                                el.disabled = true;
                            });
                            document.getElementById('update_status').disabled = false;
                            // Show the update button
                            document.querySelector('#updateModal .btn-primary').style.display =
                                'block';
                            // Show accepter message
                            document.getElementById('accepterMessage').style.display = 'block';
                            document.getElementById('viewOnlyMessage').style.display = 'none';
                        } else if (!isRequester && data.current_user_role !==
                            3) { // Not requester and not admin
                            // Disable all input fields
                            document.querySelectorAll(
                                '#updateModal input, #updateModal select, #updateModal textarea'
                            ).forEach(el => {
                                el.disabled = true;
                            });
                            // Hide the update button
                            document.querySelector('#updateModal .btn-primary').style.display =
                                'none';
                            // Show view-only message
                            document.getElementById('viewOnlyMessage').style.display = 'block';
                            document.getElementById('accepterMessage').style.display = 'none';
                        } else {
                            // Enable all input fields for requester and admin
                            document.querySelectorAll(
                                '#updateModal input, #updateModal select, #updateModal textarea'
                            ).forEach(el => {
                                el.disabled = false;
                            });
                            // Show the update button
                            document.querySelector('#updateModal .btn-primary').style.display =
                                'block';
                            // Hide messages
                            document.getElementById('viewOnlyMessage').style.display = 'none';
                            document.getElementById('accepterMessage').style.display = 'none';
                        }
                    });
            });
        });
    });

    function showConfirmationModal() {
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmModalCenter'));
        confirmationModal.show();
    }

    function submitForm() {
        // Hide the confirmation modal
        const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('confirmModalCenter'));
        confirmationModal.hide();

        // Submit the form
        document.querySelector('#updateModal form').submit();
    }
</script>
<script src="assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>
</body>

</html>