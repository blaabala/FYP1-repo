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
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="navdiv">
                <div class="image-container">
                    <a href="home_admin.php"><img src="assets/images/logo.png" alt="logo" class="nav-logo" style="width: 70px; height: auto;"></a>
                    <a href="home_admin.php" class="logo-text">Appointment Management System</a>

                    <?php
                    session_start();
                    include("database.php");
                    $email = $_SESSION['email'];
                    $query = mysqli_query($con, "SELECT users.*, roles.role_name 
											FROM users 
											JOIN roles ON users.role_id = roles.id 
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
        <div class="main-box top">
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
                        <h2 style="text-align: center;">View User Lists</h2>
                        <!-- Button trigger modal -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                                <p>Create</p>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Full Name</th>
                                    <th>Email Address</th>
                                    <th>Registration Date Time</th>
                                    <th>Role Name</th>
                                    <th>Faculty</th>
                                    <th>contact_number</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 1;
                                $sel_query = "SELECT users.*, roles.role_name AS role_name
                                                FROM users 
                                                JOIN roles ON users.role_id = roles.id
                                                ORDER BY users.id DESC";
                                $stmt = $con->prepare($sel_query);
                                // $stmt->bind_param('ii', $res_id, $res_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo $row["username"]; ?></td>
                                        <td><?php echo $row["email"]; ?></td>
                                        <td><?php echo $row["reg_date"]; ?></td>
                                        <td><?php echo $row["role_name"]; ?></td>
                                        <td><?php echo $row["faculty"]; ?></td>
                                        <td><?php echo $row["contact_number"]; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModalCenter" data-id="<?php echo $row['id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php
                                    $count++;
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Confirmation Modal -->
                        <div class="modal fade" id="confirmModalCenter" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true" data-bs-backdrop='static'>
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmModalLongTitle">Please Confirm</h5>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure that you want to remove this user?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger" onclick="deleteUser()">Confirm</button>
                                    </div>
                                </div>
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
                <p>&copy; 2024 LEE JUN KHANG. All rights reserved. </p>
            </div>
        </div>
    </footer>
    <script>
        function deleteUser() {

        }
    </script>
    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>