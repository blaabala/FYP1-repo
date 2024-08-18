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
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>

<body>
	<header>
		<nav class="navbar">
			<div class="navdiv">
				<div class="image-container">
					<a href="home.php"><img src="assets/images/logo.png" alt="logo" class="nav-logo" style="width: 70px; height: auto;"></a>
					<a href="home.php" class="logo-text">Appointment Management System</a>

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
					<li><a href="home.php">Home</a></li>
					<li><a href="appointment_view.php">Create Appointments</a></li>
					<li><?php echo "<a href='edit_profile.php?id=$res_id'>Edit Profile</a>"; ?></li>
					<button><a href="logout.php" class="logout-btn">Logout</a></button>
				</ul>
			</div>
		</nav>
	</header>