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

// Fetch availability
$availabilities = [];
$query = "SELECT start_datetime, end_datetime FROM lecturer_availability WHERE lecturer_id = ?";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_assoc()) {
    $availabilities[] = $row;
}

// Fetch booked appointments to exclude them
$booked_slots = [];
$query = "SELECT start_datetime, end_datetime FROM appointments WHERE lecturer_id = ? AND status NOT IN ('Confirmed', 'Pending')";
$statement = $con->prepare($query);
$statement->bind_param("i", $lecturer_id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_assoc()) {
    $booked_slots[] = $row;
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
        .fc-bg-event {
            opacity: 0.5 !important;
        }

        .fc-highlight {
            background-color: rgba(0, 123, 255, 0.3) !important;
        }
    </style>
</head>

<body class="bg-gray-100 font-merriweather">
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Book Appointment with <?php echo htmlspecialchars($lecturer['username']); ?>
        </h2>

        <!-- Form to book appointment -->
        <form id="booking-form" action="book_appointment.php" method="post" class="hidden bg-white p-4 rounded shadow">
            <input type="hidden" name="lecturer_id" value="<?php echo $lecturer_id; ?>">
            <input type="hidden" name="student_id" value="1"> <!-- Replace with actual student ID from session -->
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
                            borderColor: '#34c759',
                            rendering: 'background'
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
                    // Round the selected start time to the nearest 30-minute slot
                    const selectedStart = new Date(info.startStr);
                    const minutes = selectedStart.getMinutes();
                    const roundedMinutes = minutes < 30 ? 0 : 30;
                    selectedStart.setMinutes(roundedMinutes, 0, 0); // Set seconds and milliseconds to 0

                    // Set the end time to 30 minutes after the start
                    const selectedEnd = new Date(selectedStart);
                    selectedEnd.setMinutes(selectedStart.getMinutes() + 30);

                    const availabilities = <?php echo json_encode($availabilities); ?>;
                    const bookedSlots = <?php echo json_encode($booked_slots); ?>;

                    // Find the availability period that contains the selected slot
                    let selectedAvailability = null;
                    let isAvailable = availabilities.some(a => {
                        const availStart = new Date(a.start_datetime);
                        const availEnd = new Date(a.end_datetime);
                        const isWithin = selectedStart >= availStart && selectedEnd <= availEnd;
                        if (isWithin) {
                            selectedAvailability = {
                                start: availStart,
                                end: availEnd
                            };
                        }
                        return isWithin;
                    });

                    let isBooked = bookedSlots.some(b => {
                        const bookedStart = new Date(b.start_datetime);
                        const bookedEnd = new Date(b.end_datetime);
                        return selectedStart < bookedEnd && selectedEnd > bookedStart;
                    });

                    if (isAvailable && !isBooked) {
                        // Generate 30-minute slots within the availability period
                        const timeSlots = [];
                        const currentSlot = new Date(selectedAvailability.start);
                        while (currentSlot < selectedAvailability.end) {
                            timeSlots.push(new Date(currentSlot));
                            currentSlot.setMinutes(currentSlot.getMinutes() + 30);
                        }

                        // Format each slot in Malaysia time and create buttons
                        const timeSlotsContainer = document.getElementById('time-slots');
                        timeSlotsContainer.innerHTML = ''; // Clear previous buttons
                        const confirmButton = document.getElementById('confirm-button');
                        confirmButton.disabled =
                            true; // Disable confirm button until a slot is selected

                        const formatter = new Intl.DateTimeFormat('en-MY', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true,
                            timeZone: 'Asia/Kuala_Lumpur'
                        });

                        timeSlots.forEach(slot => {
                            const formattedTime = formatter.format(slot);
                            const button = document.createElement('button');
                            button.type = 'button'; // Prevent form submission
                            button.className =
                                'bg-gray-200 text-gray-800 py-1 px-3 rounded hover:bg-gray-300 focus:bg-blue-500 focus:text-white';
                            button.textContent = formattedTime;
                            button.dataset.isoTime = slot
                                .toISOString(); // Store ISO time for submission

                            // Check if this slot is booked
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
                                    // Remove focus style from other buttons
                                    timeSlotsContainer.querySelectorAll('button')
                                        .forEach(btn => {
                                            btn.className =
                                                'bg-gray-200 text-gray-800 py-1 px-3 rounded hover:bg-gray-300 focus:bg-blue-500 focus:text-white';
                                        });
                                    // Apply focus style to the clicked button
                                    button.className =
                                        'bg-blue-500 text-white py-1 px-3 rounded';
                                    // Update the hidden input with the selected slot's ISO time
                                    selectedDatetimeInput.value = button.dataset
                                        .isoTime;
                                    // Enable the confirm button
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