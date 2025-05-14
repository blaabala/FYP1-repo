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

// Fetch operating hours from the database
$query = "SELECT start_time, end_time FROM operating_hours WHERE id = 1";
$hours_result = mysqli_query($con, $query);
$operating_hours = mysqli_fetch_assoc($hours_result);

if (!$operating_hours) {
    // Default to 8 AM to 5 PM if no record is found
    $slot_min_time = '08:00:00';
    $slot_max_time = '17:00:00';
} else {
    $slot_min_time = $operating_hours['start_time'];
    $slot_max_time = $operating_hours['end_time'];
}

$lecturer_id = isset($_GET['lecturer_id']) ? (int)$_GET['lecturer_id'] : 0;
if ($lecturer_id === 0) {
    die("Invalid lecturer ID.");
}

// Fetch lecturer details
$query = "SELECT username FROM lecturers WHERE id = ?";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
$lecturer = $result->fetch_assoc();
if (!$lecturer) {
    die("Lecturer not found.");
}

// Fetch availability (both one-time and recurring)
$availabilities = [];
$query = "SELECT * FROM lecturer_availability WHERE lecturer_id = ?";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_assoc()) {
    $availabilities[] = $row;
}

// Fetch blocked dates
$blocked_dates = [];
$query = "SELECT * FROM blocked_dates WHERE lecturer_id = ?";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_assoc()) {
    $blocked_dates[] = $row;
}

// Fetch booked appointments to exclude them
$booked_slots = [];
$query = "SELECT start_datetime, end_datetime FROM appointments WHERE lecturer_id = ? AND status IN ('Confirmed', 'Pending', 'testing123')";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
if (!$statement->execute()) {
    echo "<script>console.error('SQL Error:', " . json_encode($statement->error) . ");</script>";
}
$result = $statement->get_result();
while ($row = $result->fetch_assoc()) {
    $booked_slots[] = $row;
}

// Debug: Log booked slots
echo "<script>console.log('PHP booked_slots:', " . json_encode($booked_slots) . ");</script>";

// Fetch all appointments for the lecturer (for display purposes)
$query = "SELECT * FROM appointments WHERE lecturer_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start_datetime'],
        'end' => $row['end_datetime'],
        'color' => '#FF0000',
        'extendedProps' => [
            'location' => $row['location'],
            'description' => $row['description']
        ]
    ];
}

// Define the current time and buffer period (in hours)
$currentTime = new DateTime();
// For testing, you can uncomment the following line to simulate May 29, 2025, 12:00 PM
// $currentTime = new DateTime('2025-05-29 12:00:00', new DateTimeZone('Asia/Kuala_Lumpur'));
$bufferHours = 2; // Buffer period of 2 hours
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - <?php echo htmlspecialchars($lecturer['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
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

        .fc-highlight {
            background-color: rgba(0, 123, 255, 0.3) !important;
        }

        .fc-timegrid-slot {
            height: 40px !important;
        }

        .fc-timegrid-slot-label {
            vertical-align: middle !important;
        }

        .fc-event:hover {
            cursor: pointer;
        }

        .available-slot {
            background-color: rgb(49, 241, 103) !important;
            border: none !important;
            opacity: 0.5 !important;
            pointer-events: none !important;
        }

        .fully-booked-slot {
            background-color: #ff3b30 !important;
            border: none !important;
            opacity: 0.5 !important;
            pointer-events: none !important;
        }

        .disabled-day-slot,
        .disabled-time-slot {
            background-color: #d3d3d3 !important;
            border: none !important;
            opacity: 0.5 !important;
            pointer-events: none !important;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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

        <div class="container mx-auto p-4">
            <div id="current-datetime" class="font-semibold mt-2"></div>
            <h2 class="text-2xl font-bold mb-4">Book Appointment with
                <?php echo htmlspecialchars($lecturer['username']); ?>
            </h2>

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

            <!-- Modal for booking appointment -->
            <div id="booking-modal" class="modal">
                <div class="modal-content">
                    <span class="close">×</span>
                    <h3 class="text-lg font-bold mb-4">Book Appointment</h3>
                    <form id="booking-form" action="book_appointment.php" method="post">
                        <input type="hidden" name="lecturer_id" value="<?php echo $lecturer_id; ?>">
                        <input type="hidden" name="student_id"
                            value="<?php echo isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0; ?>">
                        <div class="mb-4">
                            <label class="block text-gray-700">Selected Date and Time:</label>
                            <input id="selected-datetime" name="start_datetime" type="hidden" value="">
                            <p id="selected-datetime-display" class="text-gray-800"></p>
                        </div>
                        <div class="mb-4">
                            <label for="title" class="block text-gray-700">Title:</label>
                            <input type="text" id="title" name="title" class="w-full p-2 border rounded" required>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700">Description:</label>
                            <textarea id="description" name="description" class="w-full p-2 border rounded" rows="3"
                                required></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="location" class="block text-gray-700">Location:</label>
                            <input type="text" id="location" name="location" class="w-full p-2 border rounded" required>
                        </div>
                        <button type="submit" name="book" id="confirm-button"
                            class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900 disabled:opacity-50">Confirm
                            Appointment</button>
                    </form>
                </div>
            </div>

            <div id="calendar" class="mb-4"></div>
        </div>

        <?php include("footer.php"); ?>

        <script>
            let calendar;

            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('calendar');
                if (!calendarEl) {
                    console.error('Calendar element not found.');
                    return;
                }

                const modal = document.getElementById('booking-modal');
                const closeModal = document.getElementsByClassName('close')[0];
                const bookingForm = document.getElementById('booking-form');
                const selectedDatetimeInput = document.getElementById('selected-datetime');
                const selectedDatetimeDisplay = document.getElementById('selected-datetime-display');
                const confirmButton = document.getElementById('confirm-button');
                const titleInput = document.getElementById('title');
                const descriptionInput = document.getElementById('description');
                const locationInput = document.getElementById('location');

                const operatingStartHour = <?php echo (int)explode(':', $slot_min_time)[0]; ?>;
                const operatingStartMinute = <?php echo (int)explode(':', $slot_min_time)[1]; ?>;
                const operatingEndHour = <?php echo (int)explode(':', $slot_max_time)[0]; ?>;
                const operatingEndMinute = <?php echo (int)explode(':', $slot_max_time)[1]; ?>;

                // Enable/disable confirm button based on form completion
                function updateConfirmButtonState() {
                    const isFormComplete = selectedDatetimeInput.value && titleInput.value && descriptionInput
                        .value && locationInput.value;
                    confirmButton.disabled = !isFormComplete;
                }

                titleInput.addEventListener('input', updateConfirmButtonState);
                descriptionInput.addEventListener('input', updateConfirmButtonState);
                locationInput.addEventListener('input', updateConfirmButtonState);

                // Modal close functionality
                closeModal.onclick = function() {
                    modal.style.display = 'none';
                    if (calendar) {
                        calendar.unselect();
                    }
                    bookingForm.reset();
                    selectedDatetimeInput.value = '';
                    selectedDatetimeDisplay.textContent = '';
                    confirmButton.disabled = true;
                };

                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                        if (calendar) {
                            calendar.unselect();
                        }
                        bookingForm.reset();
                        selectedDatetimeInput.value = '';
                        selectedDatetimeDisplay.textContent = '';
                        confirmButton.disabled = true;
                    }
                };

                // Helper function to parse dates in Asia/Kuala_Lumpur timezone
                function parseDateInKualaLumpur(dateStr) {
                    if (!dateStr) return null;
                    const isDateOnly = /^\d{4}-\d{2}-\d{2}$/.test(dateStr);
                    let adjustedDateStr;
                    if (isDateOnly) {
                        adjustedDateStr = `${dateStr}T00:00:00+08:00`;
                    } else if (dateStr.includes('+')) {
                        adjustedDateStr = dateStr;
                    } else {
                        adjustedDateStr = `${dateStr}+08:00`;
                    }
                    const date = new Date(adjustedDateStr);
                    if (isNaN(date.getTime())) {
                        console.error(`Invalid date string: ${dateStr}`);
                        return null;
                    }
                    return date;
                }

                // Function to check if a date is within a blocked date range
                function isDateBlocked(date, blockedDates) {
                    const dateStr = date.toISOString().split('T')[0]; // Get YYYY-MM-DD
                    return blockedDates.some(blocked => {
                        const blockStart = parseDateInKualaLumpur(blocked.start_date);
                        const blockEnd = parseDateInKualaLumpur(blocked.end_date);
                        const checkDate = parseDateInKualaLumpur(dateStr);
                        return checkDate >= blockStart && checkDate <= blockEnd;
                    });
                }

                // Current time and buffer period
                const currentTime = new Date('<?php echo $currentTime->format('c'); ?>');
                const bufferHours = <?php echo $bufferHours; ?>;
                const bufferMillis = bufferHours * 60 * 60 * 1000; // Convert hours to milliseconds
                const bufferThreshold = new Date(currentTime.getTime() + bufferMillis);
                console.log('Current Time:', currentTime);
                console.log('Buffer Threshold (cannot book before this):', bufferThreshold);

                // Blocked dates
                const blockedDates = <?php echo json_encode($blocked_dates); ?>;
                console.log('Blocked Dates:', blockedDates);

                // Events array (non-recurring slots)
                const nonRecurringEvents = [
                    <?php
                    $eventItems = [];
                    $debugLogs = [];

                    // First, add booked slots to the events array
                    foreach ($booked_slots as $slot) {
                        $eventItems[] = sprintf(
                            "{title: 'Booked', start: %s, end: %s, backgroundColor: '#ff3b30', borderColor: '#ff3b30', editable: false, selectable: false, eventOverlap: false, eventAllow: function() { return false; }}",
                            json_encode($slot['start_datetime']),
                            json_encode($slot['end_datetime'])
                        );
                    }

                    // Then, process non-recurring availability slots, skipping booked ones
                    foreach ($availabilities as $availability) {
                        if (!$availability['is_recurring']) {
                            $start = new DateTime($availability['start_datetime']);
                            $end = new DateTime($availability['end_datetime']);
                            $availabilitySlots = [];
                            while ($start < $end) {
                                $slotEnd = clone $start;
                                $slotEnd->modify('+30 minutes');
                                $availabilitySlots[] = [
                                    'start' => $start->format('Y-m-d\TH:i:s'),
                                    'end' => $slotEnd->format('Y-m-d\TH:i:s')
                                ];
                                $start->modify('+30 minutes');
                            }

                            $isFullyBooked = true;
                            foreach ($availabilitySlots as $slot) {
                                $slotStart = new DateTime($slot['start']);
                                $slotEnd = new DateTime($slot['end']);
                                $slotIsBooked = false;
                                foreach ($booked_slots as $booked) {
                                    $bookedStart = new DateTime($booked['start_datetime']);
                                    $bookedEnd = new DateTime($booked['end_datetime']);
                                    if ($slotStart < $bookedEnd && $slotEnd > $bookedStart) {
                                        $slotIsBooked = true;
                                        break;
                                    }
                                }
                                if (!$slotIsBooked) {
                                    $isFullyBooked = false;
                                    break;
                                }
                            }

                            foreach ($availabilitySlots as $slot) {
                                $slotStart = new DateTime($slot['start']);
                                $slotEnd = new DateTime($slot['end']);
                                $slotIsBooked = false;
                                foreach ($booked_slots as $booked) {
                                    $bookedStart = new DateTime($booked['start_datetime']);
                                    $bookedEnd = new DateTime($booked['end_datetime']);
                                    if ($slotStart < $bookedEnd && $slotEnd > $bookedStart) {
                                        $slotIsBooked = true;
                                        break;
                                    }
                                }

                                $isBlocked = false;
                                foreach ($blocked_dates as $blocked) {
                                    $blockStart = new DateTime($blocked['start_date']);
                                    $blockEnd = new DateTime($blocked['end_date']);
                                    $slotDate = (clone $slotStart)->setTime(0, 0, 0);
                                    if ($slotDate >= $blockStart && $slotDate <= $blockEnd) {
                                        $isBlocked = true;
                                        break;
                                    }
                                }

                                if ($isFullyBooked) {
                                    $eventItems[] = sprintf(
                                        "{title: '', start: %s, end: %s, backgroundColor: '#ff3b30', borderColor: '#ff3b30', classNames: ['fully-booked-slot'], editable: false, selectable: false, eventOverlap: false, eventAllow: function() { return false; }}",
                                        json_encode($slot['start']),
                                        json_encode($slot['end'])
                                    );
                                } elseif ($isBlocked) {
                                    $eventItems[] = sprintf(
                                        "{title: '', start: %s, end: %s, classNames: ['disabled-time-slot'], editable: false, selectable: false, eventOverlap: false, eventAllow: function() { return false; }}",
                                        json_encode($slot['start']),
                                        json_encode($slot['end'])
                                    );
                                } elseif (!$slotIsBooked) {
                                    // We'll check past/too-soon in JavaScript
                                    $eventItems[] = sprintf(
                                        "{title: '', start: %s, end: %s, classNames: ['available-slot'], editable: false, selectable: false, eventOverlap: false, eventAllow: function() { return false; }}",
                                        json_encode($slot['start']),
                                        json_encode($slot['end'])
                                    );
                                    $debugLogs[] = sprintf(
                                        "console.log('Non-recurring event - start: %s, end: %s');",
                                        $slot['start'],
                                        $slot['end']
                                    );
                                }
                            }
                        }
                    }

                    if (!empty($eventItems)) {
                        echo implode(",\n", $eventItems);
                    }
                    ?>
                ];

                // Apply time constraints to non-recurring events
                const events = nonRecurringEvents.map(event => {
                    const eventStart = parseDateInKualaLumpur(event.start);
                    const isPastOrTooSoon = eventStart < currentTime || eventStart.getTime() <
                        bufferThreshold.getTime();
                    if (isPastOrTooSoon && !event.classNames.includes('fully-booked-slot')) {
                        console.log('Disabling non-recurring event (past or too soon):', event.start, event
                            .end);
                        event.classNames = ['disabled-time-slot'];
                    }
                    return event;
                });

                console.log('Events array:', events);

                // Debug logs for non-recurring events
                <?php
                if (!empty($debugLogs)) {
                    echo implode("\n", $debugLogs);
                }
                ?>

                const recurringAvailabilities = <?php echo json_encode(array_values(array_filter($availabilities, function ($a) {
                                                    return $a['is_recurring'];
                                                }))); ?>;
                console.log('Recurring Availabilities:', recurringAvailabilities);

                try {
                    console.log('Initializing FullCalendar...');
                    calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'timeGridWeek',
                        initialDate: '2025-04-27',
                        slotDuration: '00:30:00',
                        slotMinTime: '<?php echo $slot_min_time; ?>',
                        slotMaxTime: '<?php echo $slot_max_time; ?>',
                        contentHeight: 'auto',
                        aspectRatio: 2,
                        events: events,
                        selectable: true,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'timeGridWeek,timeGridDay'
                        },
                        datesSet: function(dateInfo) {
                            try {
                                console.log('datesSet triggered with start:', dateInfo.startStr, 'end:',
                                    dateInfo.endStr);

                                // Remove existing dynamic events
                                calendar.getEvents().forEach(event => {
                                    if (event.classNames.includes('disabled-day-slot') || event
                                        .classNames.includes('recurring-availability-slot') ||
                                        event.classNames.includes('disabled-time-slot')) {
                                        event.remove();
                                    }
                                });

                                const startDate = parseDateInKualaLumpur(dateInfo.startStr);
                                const endDate = parseDateInKualaLumpur(dateInfo.endStr);
                                if (!startDate || !endDate) {
                                    console.error('Failed to parse startDate or endDate:', dateInfo
                                        .startStr, dateInfo.endStr);
                                    return;
                                }

                                console.log('Parsed startDate:', startDate, 'endDate:', endDate);

                                let currentDate = new Date(startDate);

                                // Disable weekends
                                while (currentDate < endDate) {
                                    const dayOfWeek = currentDate.getDay();
                                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                                        const dayStart = new Date(currentDate);
                                        dayStart.setHours(8, 0, 0, 0);
                                        const dayEnd = new Date(currentDate);
                                        dayEnd.setHours(18, 0, 0, 0);

                                        let slotStart = new Date(dayStart);
                                        while (slotStart < dayEnd) {
                                            const slotEnd = new Date(slotStart);
                                            slotEnd.setMinutes(slotStart.getMinutes() + 30);

                                            calendar.addEvent({
                                                title: '',
                                                start: slotStart,
                                                end: slotEnd,
                                                backgroundColor: '#d3d3d3',
                                                borderColor: '#d3d3d3',
                                                classNames: ['disabled-day-slot'],
                                                editable: false,
                                                selectable: false,
                                                eventOverlap: false,
                                                eventAllow: function() {
                                                    return false;
                                                }
                                            });

                                            slotStart.setMinutes(slotStart.getMinutes() + 30);
                                        }
                                    }
                                    currentDate.setDate(currentDate.getDate() + 1);
                                }

                                // Add recurring availability
                                currentDate.setTime(startDate.getTime());
                                recurringAvailabilities.forEach(availability => {
                                    console.log('Processing recurring availability:',
                                        availability);

                                    const dayOfWeek = parseInt(availability.day_of_week);
                                    const startTime = availability.start_time ? availability
                                        .start_time.split(':') : null;
                                    const endTime = availability.end_time ? availability
                                        .end_time.split(':') : null;
                                    const recurringStartDate = availability.start_date ?
                                        parseDateInKualaLumpur(availability.start_date) : null;
                                    const recurringEndDate = availability.end_date ?
                                        parseDateInKualaLumpur(availability.end_date) : null;

                                    console.log('dayOfWeek:', dayOfWeek, 'recurringStartDate:',
                                        recurringStartDate, 'recurringEndDate:',
                                        recurringEndDate);

                                    if (!startTime || !endTime) {
                                        console.error('Invalid time data for availability:',
                                            availability);
                                        return;
                                    }

                                    while (currentDate < endDate) {
                                        console.log('Checking date:', currentDate,
                                            'Day of week:', currentDate.getDay());
                                        if (currentDate.getDay() === dayOfWeek) {
                                            console.log('Matched dayOfWeek:', dayOfWeek);
                                            console.log('Comparing dates - currentDate:',
                                                currentDate, 'recurringStartDate:',
                                                recurringStartDate, 'recurringEndDate:',
                                                recurringEndDate);
                                            if (recurringStartDate && currentDate <
                                                recurringStartDate) {
                                                console.log('Skipping date before start_date:',
                                                    currentDate);
                                                currentDate.setDate(currentDate.getDate() + 1);
                                                continue;
                                            }
                                            if (recurringEndDate && currentDate >
                                                recurringEndDate) {
                                                console.log('Skipping date after end_date:',
                                                    currentDate);
                                                currentDate.setDate(currentDate.getDate() + 1);
                                                continue;
                                            }

                                            const slotStart = new Date(currentDate);
                                            slotStart.setHours(parseInt(startTime[0]), parseInt(
                                                startTime[1]), 0, 0);
                                            const slotEnd = new Date(currentDate);
                                            slotEnd.setHours(parseInt(endTime[0]), parseInt(
                                                endTime[1]), 0, 0);

                                            let currentSlot = new Date(slotStart);
                                            while (currentSlot < slotEnd) {
                                                const slotEndTime = new Date(currentSlot);
                                                slotEndTime.setMinutes(currentSlot
                                                    .getMinutes() + 30);

                                                // Check if the slot is in the past or within the buffer period
                                                const isPastOrTooSoon = currentSlot <
                                                    currentTime || currentSlot.getTime() <
                                                    bufferThreshold.getTime();

                                                // Check if the slot is within a blocked date range
                                                const isBlocked = isDateBlocked(currentSlot,
                                                    blockedDates);

                                                console.log('Adding recurring event:',
                                                    currentSlot, slotEndTime);

                                                if (isPastOrTooSoon || isBlocked) {
                                                    calendar.addEvent({
                                                        title: '',
                                                        start: currentSlot,
                                                        end: slotEndTime,
                                                        classNames: [
                                                            'disabled-time-slot',
                                                            'recurring-availability-slot'
                                                        ],
                                                        editable: false,
                                                        selectable: false,
                                                        eventOverlap: false,
                                                        eventAllow: function() {
                                                            return false;
                                                        }
                                                    });
                                                    console.log(
                                                        'Disabled recurring slot (past, too soon, or blocked):',
                                                        currentSlot, slotEndTime);
                                                } else {
                                                    calendar.addEvent({
                                                        title: '',
                                                        start: currentSlot,
                                                        end: slotEndTime,
                                                        classNames: ['available-slot',
                                                            'recurring-availability-slot'
                                                        ],
                                                        editable: false,
                                                        selectable: false,
                                                        eventOverlap: false,
                                                        eventAllow: function() {
                                                            return false;
                                                        }
                                                    });
                                                }

                                                currentSlot.setMinutes(currentSlot
                                                    .getMinutes() + 30);
                                            }
                                        }
                                        currentDate.setDate(currentDate.getDate() + 1);
                                    }
                                    currentDate.setTime(startDate
                                        .getTime()); // Reset for next iteration
                                });
                            } catch (error) {
                                console.error('Error in datesSet:', error);
                            }
                        },
                        select: function(info) {
                            try {
                                const selectedStart = parseDateInKualaLumpur(info.startStr);
                                const dayOfWeek = selectedStart.getDay();
                                if (dayOfWeek === 0 || dayOfWeek === 6) {
                                    alert('Appointments cannot be booked on Saturdays or Sundays.');
                                    calendar.unselect();
                                    return;
                                }

                                // Check if the selected slot is in the past or within the buffer period
                                if (selectedStart < currentTime) {
                                    alert('Cannot book a time slot in the past.');
                                    calendar.unselect();
                                    return;
                                }
                                if (selectedStart.getTime() < bufferThreshold.getTime()) {
                                    alert(
                                        `Cannot book a time slot within ${bufferHours} hours of the current time.`
                                    );
                                    calendar.unselect();
                                    return;
                                }

                                // Check if the selected slot is within operating hours
                                const selectedHour = selectedStart.getHours();
                                const selectedMinute = selectedStart.getMinutes();
                                let selectedEnd = new Date(selectedStart);
                                selectedEnd.setMinutes(selectedStart.getMinutes() + 30);
                                const selectedEndHour = selectedEnd.getHours();
                                const selectedEndMinute = selectedEnd.getMinutes();

                                const startMinutes = operatingStartHour * 60 + operatingStartMinute;
                                const endMinutes = operatingEndHour * 60 + operatingEndMinute;
                                const selectedStartMinutes = selectedHour * 60 + selectedMinute;
                                const selectedEndMinutes = selectedEndHour * 60 + selectedEndMinute;

                                if (selectedStartMinutes < startMinutes || selectedEndMinutes >
                                    endMinutes) {
                                    alert('Selected time slot is outside operating hours.');
                                    calendar.unselect();
                                    return;
                                }

                                // Check if the selected slot is within a blocked date range
                                if (isDateBlocked(selectedStart, blockedDates)) {
                                    alert(
                                        'This time slot is unavailable due to the lecturer is on leave.'
                                    );
                                    calendar.unselect();
                                    return;
                                }

                                // Round the start time to the nearest 30-minute slot
                                const roundedStart = new Date(selectedStart);
                                const minutes = roundedStart.getMinutes();
                                const roundedMinutes = minutes < 30 ? 0 : 30;
                                roundedStart.setMinutes(roundedMinutes, 0, 0);

                                // Calculate the end time based on the rounded start
                                selectedEnd = new Date(roundedStart);
                                selectedEnd.setMinutes(roundedStart.getMinutes() + 30);

                                const availabilities = <?php echo json_encode($availabilities); ?>;
                                const bookedSlots = <?php echo json_encode($booked_slots); ?>;
                                console.log('JavaScript bookedSlots:', bookedSlots);

                                let selectedAvailability = null;
                                let isAvailable = availabilities.some(a => {
                                    if (a.is_recurring) {
                                        const dayOfWeek = parseInt(a.day_of_week);
                                        const startTime = a.start_time ? a.start_time.split(
                                            ':') : null;
                                        const endTime = a.end_time ? a.end_time.split(':') :
                                            null;
                                        const recurringStartDate = a.start_date ?
                                            parseDateInKualaLumpur(a.start_date) : null;
                                        const recurringEndDate = a.end_date ?
                                            parseDateInKualaLumpur(a.end_date) : null;

                                        if (!startTime || !endTime) return false;
                                        if (roundedStart.getDay() !== dayOfWeek) return false;
                                        if (recurringStartDate && roundedStart <
                                            recurringStartDate) return false;
                                        if (recurringEndDate && roundedStart > recurringEndDate)
                                            return false;

                                        const slotStart = new Date(roundedStart);
                                        slotStart.setHours(parseInt(startTime[0]), parseInt(
                                            startTime[1]), 0, 0);
                                        const slotEnd = new Date(roundedStart);
                                        slotEnd.setHours(parseInt(endTime[0]), parseInt(endTime[
                                            1]), 0, 0);

                                        const isWithin = roundedStart >= slotStart &&
                                            selectedEnd <= slotEnd;
                                        if (isWithin) {
                                            selectedAvailability = {
                                                start: slotStart,
                                                end: slotEnd
                                            };
                                        }
                                        return isWithin;
                                    } else {
                                        const availStart = parseDateInKualaLumpur(a
                                            .start_datetime);
                                        const availEnd = parseDateInKualaLumpur(a.end_datetime);
                                        const isWithin = roundedStart >= availStart &&
                                            selectedEnd <= availEnd;
                                        if (isWithin) {
                                            selectedAvailability = {
                                                start: availStart,
                                                end: availEnd
                                            };
                                        }
                                        return isWithin;
                                    }
                                });

                                let isBooked = bookedSlots.some(b => {
                                    const bookedStart = parseDateInKualaLumpur(b
                                        .start_datetime);
                                    const bookedEnd = parseDateInKualaLumpur(b.end_datetime);
                                    return roundedStart < bookedEnd && selectedEnd >
                                        bookedStart;
                                });

                                if (isAvailable && !isBooked) {
                                    const formatter = new Intl.DateTimeFormat('en-MY', {
                                        hour: 'numeric',
                                        minute: '2-digit',
                                        hour12: true,
                                        timeZone: 'Asia/Kuala_Lumpur'
                                    });
                                    const formattedTime = formatter.format(roundedStart);
                                    selectedDatetimeInput.value = roundedStart.toISOString();
                                    selectedDatetimeDisplay.textContent = formattedTime;

                                    // Show the modal
                                    modal.style.display = 'block';
                                    updateConfirmButtonState();
                                } else {
                                    alert('This time slot is no longer available.');
                                    calendar.unselect();
                                }
                            } catch (error) {
                                console.error('Error during slot selection:', error);
                            }
                        }
                    });

                    calendar.render();
                    console.log('FullCalendar initialized and rendered.');
                } catch (error) {
                    console.error('Error initializing FullCalendar:', error);
                }
            });
        </script>
        <script src="script.js"></script>
</body>

</html>