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
	exit(); // Prevent further code execution
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
	<title>AMS</title>
	<meta charset="utf-8">
	<meta name="description" content="">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
		rel="stylesheet">
	<link rel="stylesheet" type="text/css"
		href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
	<link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
	<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
	<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<style>
		/* Ensure the body takes up the full viewport height */
		html,
		body {
			height: 100%;
			margin: 0;
			display: flex;
			flex-direction: column;
		}

		main {
			flex: 1;
			/* This makes the main content grow and push footer down */
		}
	</style>
</head>

<body class="bg-gray-100 font-merriweather">
	<header class="bg-gradient-to-r from-blue-600 to-indigo-800 text-white shadow-lg">
		<nav class="container mx-auto px-6 py-4">
			<div class="flex justify-between items-center">
				<div class="flex items-center space-x-4"><a href="home.php"><img src="assets/images/logo.png" alt="logo"
							class="w-16 h-auto transition-transform transform hover:scale-110"></a><a href="home.php"
						class="text-2xl font-bold tracking-wide hover:text-blue-200 transition-colors">Appointment
						Management System </a></div>
				<div>
					<ul class="flex space-x-6 items-center">
						<li><a href="home.php"
								class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Home </a>
						</li>
						<li><a href="lecturer_list.php"
								class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Create
								Appointments </a></li>
						<li>
						<li><a href="appointment_view.php"
								class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Appointments</a>
						</li>
						<li>
							<?php
							echo '<a href="edit_profile.php?id=' . htmlspecialchars($res_id) . '" class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Edit Profile</a>';
							?>
						</li>
						<li><a href="logout.php"
								class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors duration-300">Logout
							</a></li>
					</ul>
				</div>
			</div>
		</nav>
	</header>
	<main class="flex-grow container mx-auto p-4">