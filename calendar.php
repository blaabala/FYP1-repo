<?php
include("header.php");

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

// Fetch booked appointments to exclude them
$booked_slots = [];
$query = "SELECT start_datetime, end_datetime FROM appointments WHERE lecturer_id = ? AND status IN ('Confirmed', 'Pending')";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_assoc()) {
    $booked_slots[] = $row;
}

$lecturer_id = $_GET['lecturer_id'];
$query = "SELECT * FROM appointments WHERE lecturer_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'id' => $row['id'],
        'name' => 'Booked',
        'startDate' => $row['start_datetime'],
        'endDate' => $row['end_datetime'],
        'color' => '#FF0000'
    ];
}
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
    <style>
        .fc-highlight {
            background-color: rgba(0, 123, 255, 0.3) !important;
        }

        .fc-timegrid-slot {
            height: 40px !important;
        }

        .fc-timegrid-slot-label {
            vertical-align: middle !important;
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

        .disabled-day-slot {
            background-color: #d3d3d3 !important;
            border: none !important;
            opacity: 0.5 !important;
            pointer-events: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 font-merriweather">
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Book Appointment with <?php echo htmlspecialchars($lecturer['username']); ?>
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

        <!-- Form to book appointment -->
        <form id="booking-form" action="book_appointment.php" method="post" class="hidden bg-white p-4 rounded shadow">
            <input type="hidden" name="lecturer_id" value="<?php echo $lecturer_id; ?>">
            <input type="hidden" name="student_id"
                value="<?php echo isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0; ?>">
            <div class="mb-4">
                <label class="block text-gray-700">Selected Date and Time:</label>
                <input id="selected-datetime" name="start_datetime" type="hidden" value="">
                <div id="time-slots" class="flex flex-wrap gap-2 mt-2"></div>
            </div>
            <button type="submit" name="book" id="confirm-button"
                class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900 disabled:opacity-50" disabled>Confirm
                Appointment</button>
        </form>
        <div id="calendar" class="mb-4"></div>
    </div>

    <?php
    include("footer.php");
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const bookingForm = document.getElementById('booking-form');
            const selectedDatetimeInput = document.getElementById('selected-datetime');

            const events = [
                <?php foreach ($availabilities as $availability): ?>
                    <?php if (!$availability['is_recurring']): ?>
                        <?php
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

                            if ($isFullyBooked) {
                        ?> {
                                    title: '',
                                    start: '<?php echo $slot['start']; ?>',
                                    end: '<?php echo $slot['end']; ?>',
                                    backgroundColor: '#ff3b30',
                                    borderColor: '#ff3b30',
                                    classNames: ['fully-booked-slot'],
                                    editable: false,
                                    selectable: false,
                                    eventOverlap: false,
                                    eventAllow: function() {
                                        return false;
                                    }
                                },
                            <?php
                            } elseif (!$slotIsBooked) {
                            ?> {
                                    title: '',
                                    start: '<?php echo $slot['start']; ?>',
                                    end: '<?php echo $slot['end']; ?>',
                                    backgroundColor: '#d4f4dd',
                                    borderColor: '#d4f4dd',
                                    classNames: ['available-slot'],
                                    editable: false,
                                    selectable: false,
                                    eventOverlap: false,
                                    eventAllow: function() {
                                        return false;
                                    }
                                },
                        <?php
                            }
                        }
                        ?>
                    <?php endif; ?>
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
            ];

            const recurringAvailabilities =
                <?php echo json_encode(array_filter($availabilities, function ($a) {
                    return $a['is_recurring'];
                })); ?>;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                slotDuration: '00:30:00',
                slotMinTime: '08:00:00',
                slotMaxTime: '18:00:00',
                contentHeight: 'auto',
                aspectRatio: 2,
                events: events,
                selectable: true,
                datesSet: function(dateInfo) {
                    calendar.getEvents().forEach(event => {
                        if (event.classNames.includes('disabled-day-slot') || event.classNames
                            .includes('recurring-availability-slot')) {
                            event.remove();
                        }
                    });

                    const startDate = new Date(dateInfo.startStr);
                    const endDate = new Date(dateInfo.endStr);
                    const currentDate = new Date(startDate);

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

                    currentDate.setTime(startDate.getTime());
                    recurringAvailabilities.forEach(availability => {
                        const dayOfWeek = parseInt(availability.day_of_week);
                        const startTime = availability.start_time.split(':');
                        const endTime = availability.end_time.split(':');
                        const recurringStartDate = availability.start_date ? new Date(
                            availability.start_date) : null;
                        const recurringEndDate = availability.end_date ? new Date(availability
                            .end_date) : null;

                        while (currentDate < endDate) {
                            if (currentDate.getDay() === dayOfWeek) {
                                if ((recurringStartDate && currentDate < recurringStartDate) ||
                                    (recurringEndDate && currentDate > recurringEndDate)) {
                                    currentDate.setDate(currentDate.getDate() + 1);
                                    continue;
                                }

                                const slotStart = new Date(currentDate);
                                slotStart.setHours(startTime[0], startTime[1], 0, 0);
                                const slotEnd = new Date(currentDate);
                                slotEnd.setHours(endTime[0], endTime[1], 0, 0);

                                let currentSlot = new Date(slotStart);
                                while (currentSlot < slotEnd) {
                                    const slotEndTime = new Date(currentSlot);
                                    slotEndTime.setMinutes(currentSlot.getMinutes() + 30);

                                    calendar.addEvent({
                                        title: '',
                                        start: currentSlot,
                                        end: slotEndTime,
                                        backgroundColor: '#d4f4dd',
                                        borderColor: '#d4f4dd',
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

                                    currentSlot.setMinutes(currentSlot.getMinutes() + 30);
                                }
                            }
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                        currentDate.setTime(startDate.getTime());
                    });
                },
                select: function(info) {
                    const selectedStart = new Date(info.startStr);
                    const dayOfWeek = selectedStart.getDay();
                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                        alert('Appointments cannot be booked on Saturdays or Sundays.');
                        calendar.unselect();
                        return;
                    }

                    const minutes = selectedStart.getMinutes();
                    const roundedMinutes = minutes < 30 ? 0 : 30;
                    selectedStart.setMinutes(roundedMinutes, 0, 0);

                    const selectedEnd = new Date(selectedStart);
                    selectedEnd.setMinutes(selectedStart.getMinutes() + 30);

                    const availabilities = <?php echo json_encode($availabilities); ?>;
                    const bookedSlots = <?php echo json_encode($booked_slots); ?>;

                    let selectedAvailability = null;
                    let isAvailable = availabilities.some(a => {
                        if (a.is_recurring) {
                            const dayOfWeek = parseInt(a.day_of_week);
                            const startTime = a.start_time.split(':');
                            const endTime = a.end_time.split(':');
                            const recurringStartDate = a.start_date ? new Date(a.start_date) :
                                null;
                            const recurringEndDate = a.end_date ? new Date(a.end_date) : null;

                            if (selectedStart.getDay() !== dayOfWeek) return false;
                            if (recurringStartDate && selectedStart < recurringStartDate)
                                return false;
                            if (recurringEndDate && selectedStart > recurringEndDate)
                                return false;

                            const slotStart = new Date(selectedStart);
                            slotStart.setHours(startTime[0], startTime[1], 0, 0);
                            const slotEnd = new Date(selectedStart);
                            slotEnd.setHours(endTime[0], endTime[1], 0, 0);

                            const isWithin = selectedStart >= slotStart && selectedEnd <=
                                slotEnd;
                            if (isWithin) {
                                selectedAvailability = {
                                    start: slotStart,
                                    end: slotEnd
                                };
                            }
                            return isWithin;
                        } else {
                            const availStart = new Date(a.start_datetime);
                            const availEnd = new Date(a.end_datetime);
                            const isWithin = selectedStart >= availStart && selectedEnd <=
                                availEnd;
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
                        const bookedStart = new Date(b.start_datetime);
                        const bookedEnd = new Date(b.end_datetime);
                        return selectedStart < bookedEnd && selectedEnd > bookedStart;
                    });

                    if (isAvailable && !isBooked) {
                        const timeSlots = [];
                        const currentSlot = new Date(selectedAvailability.start);
                        while (currentSlot < selectedAvailability.end) {
                            timeSlots.push(new Date(currentSlot));
                            currentSlot.setMinutes(currentSlot.getMinutes() + 30);
                        }

                        const timeSlotsContainer = document.getElementById('time-slots');
                        timeSlotsContainer.innerHTML = '';
                        const confirmButton = document.getElementById('confirm-button');
                        confirmButton.disabled = true;

                        const formatter = new Intl.DateTimeFormat('en-MY', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true,
                            timeZone: 'Asia/Kuala_Lumpur'
                        });

                        timeSlots.forEach(slot => {
                            const formattedTime = formatter.format(slot);
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className =
                                'bg-gray-200 text-gray-800 py-1 px-3 rounded hover:bg-gray-300 focus:bg-blue-500 focus:text-white';
                            button.textContent = formattedTime;
                            button.dataset.isoTime = slot.toISOString();

                            const slotEnd = new Date(slot);
                            slotEnd.setMinutes(slotEnd.getMinutes() + 30);
                            const isSlotBooked = bookedSlots.some(b => {
                                const bookedStart = new Date(b.start_datetime);
                                const bookedEnd = new Date(b.end_datetime);
                                return slot < bookedEnd && slotEnd > bookedStart;
                            });

                            if (isSlotBooked) {
                                button.disabled = true;
                                button.className =
                                    'bg-red-200 text-gray-500 py-1 px-3 rounded cursor-not-allowed';
                                button.textContent += ' (Booked)';
                            } else {
                                button.addEventListener('click', () => {
                                    timeSlotsContainer.querySelectorAll('button')
                                        .forEach(btn => {
                                            btn.className =
                                                'bg-gray-200 text-gray-800 py-1 px-3 rounded hover:bg-gray-300 focus:bg-blue-500 focus:text-white';
                                        });
                                    button.className =
                                        'bg-blue-500 text-white py-1 px-3 rounded';
                                    selectedDatetimeInput.value = button.dataset
                                        .isoTime;
                                    confirmButton.disabled = false;
                                });
                            }

                            timeSlotsContainer.appendChild(button);
                        });

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