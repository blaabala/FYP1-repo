<?php
session_start();
include("database.php");

$email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
if (!$email) {
    $_SESSION['error_message'] = "Please log in to continue.";
    header("Location: login_admin.php");
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
    header("Location: login_admin.php");
    exit();
}

$user = $result->fetch_assoc();
$res_id = $user['id'];
$res_username = $user['username'];
$res_email = $user['email'];
$res_role_name = $user['role_name'];

if (strtolower($res_role_name) !== 'admin') {
    $_SESSION['error_message'] = "You must be an admin to view this page.";
    header("Location: home.php");
    exit();
}

// Fetch all students
$students_query = "SELECT users.id, users.username, users.contact_number, students.faculty
                   FROM users
                   JOIN students ON users.id = students.user_id
                   WHERE users.role_id = 2
                   ORDER BY users.id DESC";
$stmt = $con->prepare($students_query);
$stmt->execute();
$students_result = $stmt->get_result();
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Handle Delete Student
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];

    // Delete from students table
    $delete_student_query = "DELETE FROM students WHERE user_id = ? AND EXISTS (SELECT 1 FROM users WHERE id = ? AND role_id = 2)";
    $stmt = $con->prepare($delete_student_query);
    $stmt->bind_param('ii', $user_id, $user_id);
    if ($stmt->execute()) {
        // Delete from users table
        $delete_user_query = "DELETE FROM users WHERE id = ? AND role_id = 2";
        $stmt2 = $con->prepare($delete_user_query);
        $stmt2->bind_param('i', $user_id);
        if ($stmt2->execute()) {
            $_SESSION['success_message'] = "Student deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting user.";
        }
        $stmt2->close();
    } else {
        $_SESSION['error_message'] = "Error deleting student.";
    }
    $stmt->close();
    header("Location: student_view_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>AMS - Student List</title>
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: #98C1D9;
        }

        .main-box {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .product-table th,
        .product-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .product-table th {
            background-color: #f4f4f4;
            font-weight: 600;
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
                    <li><a href="set_operating_hours.php">Set Operating Hours</a></li>
                    <li><a href="edit_profile_admin.php?id=<?php echo $res_id; ?>">Edit Profile</a></li>
                    <li><button><a href="logout.php" class="logout-btn">Logout</a></button></li>
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
                            <h2 style="text-align: center;">Student List</h2>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <p>Create</p>
                                </button>
                            </div>
                            <div class="input-group mb-3" style="max-width: 300px; margin-top: 10px;">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search by name">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table class="product-table" id="studentTable">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Student Name</th>
                                        <th>Faculty</th>
                                        <th>Contact Number</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    if ($students_result->num_rows === 0) {
                                        echo "<tr><td colspan='5' style='text-align: center;'>No students found.</td></tr>";
                                    } else {
                                        foreach ($students as $student) {
                                    ?>
                                            <tr>
                                                <td><?php echo $count; ?></td>
                                                <td><?php echo htmlspecialchars($student["username"]); ?></td>
                                                <td><?php echo htmlspecialchars($student["faculty"] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($student["contact_number"] ?? 'N/A'); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                                        data-bs-target="#updateModal"
                                                        data-id="<?php echo $student['id']; ?>">Update</button>
                                                    <a href="student_view_admin.php?delete_id=<?php echo $student['id']; ?>"
                                                        class="btn btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                                </td>
                                            </tr>
                                    <?php
                                            $count++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Create New Student Modal -->
                <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel"
                    aria-hidden="true" data-bs-backdrop='static'>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="createModalLabel">Create New Student</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="student_insert_admin.php" method="post">
                                    <div class="form-group row">
                                        <label for="username"
                                            class="col-sm-3 col-form-label col-form-label-lg">Username</label>
                                        <div class="col-sm-9">
                                            <input required type="text" class="form-control form-control-lg"
                                                id="username" name="username" placeholder="Enter student's full name">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-md-6">
                                            <label for="email">Email</label>
                                            <input required type="email" class="form-control" id="email" name="email"
                                                placeholder="Enter student's email">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="contact_number">Contact Number</label>
                                            <input required type="text" class="form-control" id="contact_number"
                                                name="contact_number" placeholder="i.e.: 60123456789">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-md-12">
                                            <label for="faculty">Faculty</label>
                                            <input required type="text" class="form-control" id="faculty" name="faculty"
                                                placeholder="Enter faculty">
                                        </div>
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

                <!-- Update Student Modal -->
                <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel"
                    aria-hidden="true" data-bs-backdrop='static'>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="updateModalLabel">Update Student</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="student_update_admin.php" method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="student_id" id="update_student_id" value="">
                                    <div class="form-group row">
                                        <label for="update_username"
                                            class="col-sm-3 col-form-label col-form-label-lg">Username</label>
                                        <div class="col-sm-9">
                                            <input required type="text" class="form-control form-control-lg"
                                                id="update_username" name="username"
                                                placeholder="Enter student's full name">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-md-6">
                                            <label for="update_email">Email</label>
                                            <input required type="email" class="form-control" id="update_email"
                                                name="email">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="update_contact_number">Contact Number</label>
                                            <input required type="text" class="form-control" id="update_contact_number"
                                                name="contact_number">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-md-12">
                                            <label for="update_faculty">Faculty</label>
                                            <input required type="text" class="form-control" id="update_faculty"
                                                name="faculty">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
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
                    <li><a href="lecturer_view_admin.php">Lecturers</a></li>
                    <li><a href="student_view_admin.php">Students</a></li>
                    <li><a href="appointment_view_admin.php">Appointments</a></li>
                    <li><a href="set_operating_hours.php">Set Operating Hours</a></li>
                    <li><a href="edit_profile_admin.php?id=<?php echo $res_id; ?>">Edit Profile</a></li>
                </ul>
            </div>
            <div class="footer-bottom">
                <p>© 2024 LEE JUN KHANG. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script>
        // Populate update modal with data
        var updateModal = document.getElementById('updateModal');
        updateModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var studentId = button.getAttribute('data-id');
            var form = updateModal.querySelector('form');
            form.querySelector('#update_student_id').value = studentId;

            fetch(`student_fetch_admin.php?id=${studentId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    form.querySelector('#update_username').value = data.username || '';
                    form.querySelector('#update_email').value = data.email || '';
                    form.querySelector('#update_contact_number').value = data.contact_number || '';
                    form.querySelector('#update_faculty').value = data.faculty || '';
                })
                .catch(error => console.error('Error fetching student data:', error));
        });

        // AJAX search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            var searchTerm = document.getElementById('searchInput').value;
            fetch(`student_search_admin.php?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('studentTable').innerHTML = html;
                })
                .catch(error => console.error('Error searching students:', error));
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