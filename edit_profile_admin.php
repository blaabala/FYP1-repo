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
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = strtoupper($_POST['username']);
            $email = $_POST['email'];
            $current_password_input = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            $faculty = $_POST['faculty'];
            $phoneno = $_POST['phoneno'];

            $current_password_query = "SELECT password FROM users WHERE id = ?";
            $stmt = $con->prepare($current_password_query);
            $stmt->bind_param('i', $res_id);
            $stmt->execute();
            $stmt->bind_result($current_password_hash);
            $stmt->fetch();
            $stmt->close();

            // if new passwords match, validate the current password and update
            if (!empty($current_password_input)) {
                if (password_verify($current_password_input, $current_password_hash)) {
                    if (!empty($new_password) && $new_password === $confirm_password) {
                        $password = password_hash($new_password, PASSWORD_DEFAULT);
                    } else {
                        $_SESSION['error_message'] = "New passwords do not match!";
                        header("Location: edit_profile_admin.php?id=$res_id");
                        exit();
                    }
                } else {
                    $_SESSION['error_message'] = "Current password is incorrect!";
                    header("Location: edit_profile_admin.php?id=$res_id");
                    exit();
                }
            } else {
                $password = $current_password_hash;
            }

            $update_query = "UPDATE users SET username = ?, email = ?, password = ?, faculty = ?, contact_number = ? WHERE id = ?";
            if ($stmt = $con->prepare($update_query)) {
                $stmt->bind_param('sssssi', $username, $email, $password, $faculty, $phoneno, $res_id);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Profile updated successfully.";
                } else {
                    $_SESSION['error_message'] = "Error updating profile.";
                }
                $stmt->close();
            } else {
                $_SESSION['error_message'] = "Error preparing query.";
            }
            header("Location: edit_profile_admin.php?id=$res_id");
            exit();
        }

        if (isset($_SESSION['success_message'])) {
            echo "<script>alert('Profile updated successfully!');</script>";
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo "<script>alert('Failed to update profile, please try again!');</script>";
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="container">
            <div class="box form-box">
                <header class="roboto-black-italic">Edit User Profile</header>
                <form action="" method="post">
                    <div class="field input">
                        <label for="username">User Name</label>
                        <input required type="text" id="username" name="username" value="<?php echo $res_username; ?>" placeholder="Enter your full name">
                    </div>
                    <div class="field input">
                        <label for="email">User Email</label>
                        <input required type="text" id="email" name="email" value="<?php echo $res_email; ?>" placeholder="Enter your email address">
                    </div>
                    <div class="field input">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter your current password">
                    </div>
                    <div class="field input">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter your new password">
                    </div>
                    <div class="field input">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
                    </div>

                    <div class="field input">
                        <label for="faculty">Faculty</label>
                        <select id="faculty" name="faculty">
                            <option value="MK-FMHS" <?php if ($res_faculty == 'MK-FMHS') echo 'selected'; ?>>M. Kandiah Faculty of Medicine and Health Sciences</option>
                            <option value="LKC-FES" <?php if ($res_faculty == 'LKC-FES') echo 'selected'; ?>>Lee Kong Chian Faculty of Engineering and Science</option>
                            <option value="FEGT" <?php if ($res_faculty == 'FEGT') echo 'selected'; ?>>Faculty of Engineering and Green Technology</option>
                            <option value="FICT" <?php if ($res_faculty == 'FICT') echo 'selected'; ?>>Faculty of Information and Communication Technology</option>
                            <option value="FSc" <?php if ($res_faculty == 'FSc') echo 'selected'; ?>>Faculty of Science</option>
                            <option value="FAM" <?php if ($res_faculty == 'FAM') echo 'selected'; ?>>Faculty of Accountancy and Management (Sungai Long Campus)</option>
                            <option value="FBF" <?php if ($res_faculty == 'FBF') echo 'selected'; ?>>Faculty of Business and Finance (Kampar Campus)</option>
                            <option value="FAS" <?php if ($res_faculty == 'FAS') echo 'selected'; ?>>Faculty of Arts and Social Science (Kampar Campus)</option>
                            <option value="FCI" <?php if ($res_faculty == 'FCI') echo 'selected'; ?>>Faculty of Creative Industries</option>
                            <option value="Postgraduate" <?php if ($res_faculty == 'Postgraduate') echo 'selected'; ?>>Institute of Postgraduate Studies & Research</option>
                            <option value="ICS" <?php if ($res_faculty == 'ICS') echo 'selected'; ?>>Institute of Chinese Studies</option>
                            <option value="IMLD" <?php if ($res_faculty == 'IMLD') echo 'selected'; ?>>Institute of Management and Leadership Development</option>
                            <option value="CFS-KPR" <?php if ($res_faculty == 'CFS-KPR') echo 'selected'; ?>>Centre for Foundation Studies (Kampar Campus)</option>
                            <option value="CFS-SGLONG" <?php if ($res_faculty == 'CFS-SGLONG') echo 'selected'; ?>>Centre for Foundation Studies (Sungai Long Campus)</option>
                            <option value="CEE" <?php if ($res_faculty == 'CEE') echo 'selected'; ?>>Centre for Extension Education</option>
                            <option value="CCCD" <?php if ($res_faculty == 'CCCD') echo 'selected'; ?>>Centre for Corporate and Community Development</option>
                        </select>
                    </div>
                    <div class="field input">
                        <label for="phoneno">Contact Number</label>
                        <input required type="text" id="phoneno" name="phoneno" value="<?php echo $res_contact; ?>" placeholder="i.e.: +60123456789">
                    </div>
                    <div class="field">
                        <input required type="submit" name="submit" value="Submit" class="btn">
                    </div>
                </form>
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
    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>