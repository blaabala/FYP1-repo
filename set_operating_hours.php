<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
include("database.php");

$admin_id = $_SESSION['id'] ?? null;

if (!$admin_id) {
    echo "<script>
        alert('Please login to continue.');
        window.location.href = 'login.php';
    </script>";
    exit();
}
$email = $_SESSION['email'];

// Fetch admin data
$query = mysqli_query($con, "SELECT users.id, 
users.username, 
users.email, 
users.contact_number, 
users.role_id,
roles.role_name, 
admins.department
FROM users
INNER JOIN roles ON users.role_id = roles.id
INNER JOIN admins ON admins.user_id = users.id
WHERE users.email = '$email'");

$result = mysqli_fetch_assoc($query);
if (!$result) {
    echo "<script>
        alert('User not found. Please login again.');
        window.location.href = 'logout.php';
    </script>";
    exit();
}

$res_id = $result['id'];
$res_username = $result['username'];
$res_email = $result['email'];
$res_role = $result['role_id'];
$res_role_name = $result['role_name'];
$res_department = $result['department'];
$res_contact = $result['contact_number'];

// Fetch current operating hours
$query = "SELECT start_time, end_time FROM operating_hours WHERE id = 1";
$hours_result = mysqli_query($con, $query);
$operating_hours = mysqli_fetch_assoc($hours_result);
if (!$operating_hours) {
    // If no record exists, insert default values
    $query = "INSERT INTO operating_hours (id, start_time, end_time) VALUES (1, '08:00:00', '17:00:00')";
    mysqli_query($con, $query);
    $operating_hours = ['start_time' => '08:00:00', 'end_time' => '17:00:00'];
}

$start_time = $operating_hours['start_time'];
$end_time = $operating_hours['end_time'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_start_time = $_POST['start_time'] ?? '';
    $new_end_time = $_POST['end_time'] ?? '';

    // Validate time format (HH:MM)
    if (
        preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/", $new_start_time) &&
        preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/", $new_end_time)
    ) {

        // Convert to time objects for comparison
        $start = DateTime::createFromFormat('H:i', $new_start_time);
        $end = DateTime::createFromFormat('H:i', $new_end_time);

        if ($start < $end) {
            $query = "UPDATE operating_hours SET start_time = ?, end_time = ? WHERE id = 1";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $new_start_time, $new_end_time);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Operating hours updated successfully!";
                // Update the displayed values
                $start_time = $new_start_time;
                $end_time = $new_end_time;
            } else {
                $_SESSION['error_message'] = "Failed to update operating hours. Please try again.";
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "End time must be after start time.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid time format. Please use HH:MM (24-hour format).";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Set Operating Hours</title>
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
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .main-box {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .container-tight {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .navbar {
            background-color: #3D5A80;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navdiv {
            display: flex;
            align-items: center;
            width: 100%;
            justify-content: space-between;
        }

        .image-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .navdiv ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }

        .navdiv ul li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .navdiv ul li a:hover {
            color: #ecf0f1;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>

<body class="main-box top">
    <header>
        <nav class="navbar">
            <div class="navdiv">
                <div class="image-container">
                    <a href="home_admin.php"><img src="assets/images/logo.png" alt="logo" class="nav-logo"
                            style="width: 70px; height: auto;"></a>
                    <a href="home_admin.php" class="logo-text">Appointment Management System</a>
                </div>
                <ul>
                    <li><a href="home_admin.php">Home</a></li>
                    <li><a href="lecturer_view_admin.php">Lecturers</a></li>
                    <li><a href="student_view_admin.php">Students</a></li>
                    <li><a href="appointment_view_admin.php">Appointments</a></li>
                    <li><a href="edit_profile_admin.php?id=<?php echo $res_id; ?>">Edit Profile</a></li>
                    <li><button><a href="logout.php" class="logout-btn">Logout</a></button></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container-tight mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Set Operating Hours</h1>
            <p class="text-gray-600">Adjust the operating hours for the Appointment Management System.</p>
        </div>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <form method="post" action="">
                <div class="mb-4">
                    <label for="start_time" class="block text-gray-700 font-semibold mb-2">Start Time (HH:MM, 24-hour
                        format):</label>
                    <input type="text" name="start_time" id="start_time"
                        value="<?php echo htmlspecialchars($start_time); ?>" class="w-full p-2 border rounded"
                        placeholder="e.g., 08:00" required>
                </div>
                <div class="mb-4">
                    <label for="end_time" class="block text-gray-700 font-semibold mb-2">End Time (HH:MM, 24-hour
                        format):</label>
                    <input type="text" name="end_time" id="end_time" value="<?php echo htmlspecialchars($end_time); ?>"
                        class="w-full p-2 border rounded" placeholder="e.g., 17:00" required>
                </div>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Update
                    Operating Hours</button>
            </form>
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
                    <li><a href="lecturer_view_admin.php">Lecturers</a></li>
                    <li><a href="student_view_admin.php">Students</a></li>
                    <li><a href="appointment_view_admin.php">Appointments</a></li>
                    <li><?php echo "<a href='edit_profile_admin.php?id=$res_id'>Edit Profile</a>"; ?></li>
                </ul>
            </div>
            <div class="footer-bottom">
                <div class="text-center">
                    <p class="text-sm">Contact us: <a href="tel:+60123456789"
                            class="underline hover:text-blue-200 transition-colors duration-300">+60123456789</a> | <a
                            href="mailto:info@utarhospital.my"
                            class="underline hover:text-blue-200 transition-colors duration-300">info@ams.1utar.my</a>
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm">© <?php echo date('Y'); ?> LEE JUN KHANG. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
        let statusChart, trendChart;

        function updateDashboard() {
            fetch('dashboard_data.php')
                .then(response => response.json())
                .then(data => {
                    // Update Stats Cards
                    document.getElementById('total-appointments').textContent = data.total_appointments;
                    document.getElementById('total-lecturers').textContent = data.total_lecturers;
                    document.getElementById('total-students').textContent = data.total_students;

                    // Update Pie Chart: Appointment Status Distribution
                    if (statusChart) statusChart.destroy();
                    const statusCtx = document.getElementById('statusChart').getContext('2d');
                    statusChart = new Chart(statusCtx, {
                        type: 'pie',
                        data: {
                            labels: ['Confirmed', 'Rejected', 'Cancelled', 'Completed'],
                            datasets: [{
                                data: [
                                    data.status_data.Confirmed,
                                    data.status_data.Rejected,
                                    data.status_data.Cancelled,
                                    data.status_data.Completed,
                                ],
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(255, 206, 86, 0.6)',
                                    'rgba(75, 192, 192, 0.6)',
                                    'rgba(153, 102, 255, 0.6)'
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            }
                        }
                    });

                    // Update Line Chart: Daily Appointment Trends
                    if (trendChart) trendChart.destroy();
                    const trendCtx = document.getElementById('trendChart').getContext('2d');
                    trendChart = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: data.trend_labels,
                            datasets: [{
                                label: 'Appointments',
                                data: data.trend_values,
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Number of Appointments'
                                    },
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching dashboard data:', error));

            // Fetch Appointment Insights
            fetch('appointment_insights_admin.php')
                .then(response => response.json())
                .then(data => {
                    // Top Lecturers by Cancellations
                    const topCancellationsTable = document.getElementById('top-cancellations-table');
                    topCancellationsTable.innerHTML = '';
                    if (data.top_cancellations.length === 0) {
                        topCancellationsTable.innerHTML =
                            '<tr><td colspan="3" class="text-center text-gray-500">No cancellations found.</td></tr>';
                    } else {
                        data.top_cancellations.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>${row.lecturer_name}</td>
                            <td>${row.cancellation_count}</td>
                            <td>${row.percentage}%</td>
                        `;
                            topCancellationsTable.appendChild(tr);
                        });
                    }

                    // Most Active Lecturers
                    const mostActiveTable = document.getElementById('most-active-table');
                    mostActiveTable.innerHTML = '';
                    if (data.most_active.length === 0) {
                        mostActiveTable.innerHTML =
                            '<tr><td colspan="2" class="text-center text-gray-500">No active lecturers found.</td></tr>';
                    } else {
                        data.most_active.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>${row.lecturer_name}</td>
                            <td>${row.appointment_count}</td>
                        `;
                            mostActiveTable.appendChild(tr);
                        });
                    }

                    // Top Students by Appointment Requests
                    const topStudentsRequestsTable = document.getElementById('top-students-requests-table');
                    topStudentsRequestsTable.innerHTML = '';
                    if (data.top_students_requests.length === 0) {
                        topStudentsRequestsTable.innerHTML =
                            '<tr><td colspan="3" class="text-center text-gray-500">No appointment requests found.</td></tr>';
                    } else {
                        data.top_students_requests.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>${row.student_name}</td>
                            <td>${row.request_count}</td>
                            <td>${row.percentage}%</td>
                        `;
                            topStudentsRequestsTable.appendChild(tr);
                        });
                    }

                    // Students with Most Cancellations
                    const topStudentsCancellationsTable = document.getElementById('top-students-cancellations-table');
                    topStudentsCancellationsTable.innerHTML = '';
                    if (data.top_students_cancellations.length === 0) {
                        topStudentsCancellationsTable.innerHTML =
                            '<tr><td colspan="3" class="text-center text-gray-500">No cancellations found.</td></tr>';
                    } else {
                        data.top_students_cancellations.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>${row.student_name}</td>
                            <td>${row.cancellation_count}</td>
                            <td>${row.percentage}%</td>
                        `;
                            topStudentsCancellationsTable.appendChild(tr);
                        });
                    }

                    // Appointment Status Breakdown by Lecturer
                    const statusBreakdownTable = document.getElementById('status-breakdown-table');
                    statusBreakdownTable.innerHTML = '';
                    if (data.status_breakdown.length === 0) {
                        statusBreakdownTable.innerHTML =
                            '<tr><td colspan="6" class="text-center text-gray-500">No appointments found.</td></tr>';
                    } else {
                        data.status_breakdown.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>${row.lecturer_name}</td>
                            <td>${row.confirmed || 0}</td>
                            <td>${row.rejected || 0}</td>
                            <td>${row.cancelled || 0}</td>
                            <td>${row.completed || 0}</td>
                            <td>${row.total}</td>
                        `;
                            statusBreakdownTable.appendChild(tr);
                        });
                    }
                })
                .catch(error => console.error('Error fetching appointment insights:', error));
        }

        // Initial load
        updateDashboard();

        // Poll for updates every 30 seconds
        setInterval(updateDashboard, 30000);
    </script>
</body>

</html>