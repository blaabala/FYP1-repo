<?php
include('database.php');

$lecturer_id = isset($_GET['lecturer_id']) ? (int)$_GET['lecturer_id'] : 0;
if ($lecturer_id === 0) {
    die("Invalid lecturer ID.");
}

// Fetch lecturer details
$query = "SELECT username FROM users WHERE id = ? AND role_id = 1";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
$lecturer = $result->fetch_assoc();
if (!$lecturer) {
    die("Lecturer not found.");
}

// // Fetch availability
// $availabilities = [];
// $query = "SELECT start_datetime, end_datetime FROM lecturer_availability WHERE lecturer_id = ?";
// $statement = $con->prepare($query);
// $statement->bind_param("i", $lecturer_id);
// $statement->execute();
// $result = $statement->get_result();
// while ($row = $result->fetch_assoc()) {
//     $availabilities[] = $row;
// }

// // Fetch booked appointments to exclude them
// $booked_slots = [];
// $query = "SELECT start_datetime, end_datetime FROM appointments WHERE lecturer_id = ? AND status != 'cancelled'";
// $statement = $con->prepare($query);
// $statement->bind_param("i", $lecturer_id);
// $statement->execute();
// $result = $statement->get_result();
// while ($row = $result->fetch_assoc()) {
//     $booked_slots[] = $row;
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - <?php echo htmlspecialchars($lecturer['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <header class="bg-blue-900 text-white p-4 flex justify-between items-center">
        <div class="flex items-center">
            <img src="assets/images/logo.png" alt="UTAR Logo" class="w-12 h-12 mr-2">
            <h1 class="text-xl font-bold">UTAR Hospital</h1>
        </div>
        <div>
            <a href="login.php" class="text-white hover:underline">Sign In</a>
        </div>
    </header>

    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Book Appointment with <?php echo htmlspecialchars($lecturer['username']); ?>
        </h2>
        <div id="calendar" class="mb-4"></div>

        <!-- Form to book appointment -->
        <form id="booking-form" action="book_appointment.php" method="post" class="hidden bg-white p-4 rounded shadow">
            <input type="hidden" name="lecturer_id" value="<?php echo $lecturer_id; ?>">
            <input type="hidden" name="student_id" value="1"> <!-- Replace with actual student ID from session -->
            <div class="mb-4">
                <label class="block text-gray-700">Selected Date and Time:</label>
                <input id="selected-datetime" name="start_datetime" class="border p-2 rounded w-full" readonly>
            </div>
            <button type="submit" name="book" class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900">Confirm
                Appointment</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const bookingForm = document.getElementById('booking-form');
            const selectedDatetimeInput = document.getElementById('selected-datetime');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                slotDuration: '00:30:00', // 30-minute slots
                slotMinTime: '08:00:00',
                slotMaxTime: '18:00:00',
                events: [
                    <?php foreach ($availabilities as $availability): ?> {
                            title: 'Available',
                            start: '<?php echo $availability['start_datetime']; ?>',
                            end: '<?php echo $availability['end_datetime']; ?>',
                            backgroundColor: '#34c759',
                            borderColor: '#34c759'
                        },
                    <?php endforeach; ?>
                    <?php foreach ($booked_slots as $slot): ?> {
                            title: 'Booked',
                            start: '<?php echo $slot['start_datetime']; ?>',
                            end: '<?php echo $slot['end_datetime']; ?>',
                            backgroundColor: '#ff3b30',
                            borderColor: '#ff3b30',
                            editable: false
                        },
                    <?php endforeach; ?>
                ],
                selectable: true,
                select: function(info) {
                    // Ensure the selected slot is within an available period and not booked
                    const selectedStart = new Date(info.startStr);
                    const selectedEnd = new Date(info.endStr);
                    let isAvailable = false;

                    <?php foreach ($availabilities as $availability): ?>
                        const availStart = new Date('<?php echo $availability['start_datetime']; ?>');
                        const availEnd = new Date('<?php echo $availability['end_datetime']; ?>');
                        if (selectedStart >= availStart && selectedEnd <= availEnd) {
                            isAvailable = true;
                        }
                    <?php endforeach; ?>

                    let isBooked = false;
                    <?php foreach ($booked_slots as $slot): ?>
                        const bookedStart = new Date('<?php echo $slot['start_datetime']; ?>');
                        const bookedEnd = new Date('<?php echo $slot['end_datetime']; ?>');
                        if (selectedStart < bookedEnd && selectedEnd > bookedStart) {
                            isBooked = true;
                        }
                    <?php endforeach; ?>

                    if (isAvailable && !isBooked) {
                        selectedDatetimeInput.value = info.startStr;
                        bookingForm.classList.remove('hidden');
                    } else {
                        alert('This time slot is not available.');
                        calendar.unselect();
                    }
                }
            });
            calendar.render();
        });
    </script>
</body>

</html>