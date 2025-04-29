<?php
include('database.php');

// Fetch lecturers (initial load)
$query = "SELECT id, username, user_id, faculty, department, designation FROM lecturers";
$result = $con->query($query);

$lecturers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch availability for this lecturer
        $lecturer_id = $row['id'];
        $avail_query = "SELECT * FROM lecturer_availability WHERE lecturer_id = ?";
        $stmt = $con->prepare($avail_query);
        $stmt->bind_param("i", $lecturer_id);
        $stmt->execute();
        $avail_result = $stmt->get_result();

        $availability_strings = [];
        while ($avail_row = $avail_result->fetch_assoc()) {
            if ($avail_row['is_recurring']) {
                // Recurring availability: Show day of week and time range
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $day_of_week = $days[$avail_row['day_of_week']];
                $start_time = date("h:i A", strtotime($avail_row['start_time']));
                $end_time = date("h:i A", strtotime($avail_row['end_time']));
                $recurring_period = '';
                if ($avail_row['start_date'] && $avail_row['end_date']) {
                    $start_date = date("d M Y", strtotime($avail_row['start_date']));
                    $end_date = date("d M Y", strtotime($avail_row['end_date']));
                    $recurring_period = " (from $start_date to $end_date)";
                }
                $availability_strings[] = "Every $day_of_week, $start_time - $end_time$recurring_period";
            } else {
                // Non-recurring availability: Show full date and time range
                $start_datetime = date("d M Y, h:i A", strtotime($avail_row['start_datetime']));
                $end_datetime = date("h:i A", strtotime($avail_row['end_datetime']));
                $availability_strings[] = "$start_datetime - $end_datetime";
            }
        }

        // Add availability to the lecturer's data
        $row['availability'] = $availability_strings;
        $lecturers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <style>
        .modal {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .modal-hidden {
            opacity: 0;
            transform: scale(0.95);
            pointer-events: none;
        }

        .modal-visible {
            opacity: 1;
            transform: scale(1);
            pointer-events: auto;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        .loading {
            text-align: center;
            padding: 20px;
            display: none;
        }

        #clear-search {
            cursor: pointer;
            font-size: 1.2rem;
        }
    </style>
</head>

<body class="bg-gray-100 font-merriweather">
    <?php
    include("header.php");
    $student_id = $_SESSION['id'] ?? null;

    if (!$student_id) {
        die("Student not logged in.");
    }
    ?>

    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-4">
            <div class="relative w-1/4">
                <input type="text" id="search-filter" placeholder="Filter" class="border p-2 rounded w-full">
                <button id="clear-search"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    style="display: none;">×</button>
            </div>
            <select class="border p-2 rounded">
                <option>All Faculties</option>
                <option>FICT</option>
            </select>
        </div>

        <div id="lecturer-list">
            <?php if (empty($lecturers)): ?>
                <p class="text-center text-gray-500">No lecturers found.</p>
            <?php else: ?>
                <?php foreach ($lecturers as $lecturer): ?>
                    <div class="lecturer-card bg-white p-4 mb-2 rounded shadow flex justify-between items-center cursor-pointer"
                        data-id="<?php echo $lecturer['id']; ?>"
                        data-availability="<?php echo htmlspecialchars(json_encode($lecturer['availability'])); ?>">
                        <div>
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($lecturer['username']); ?></h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($lecturer['faculty']); ?></p>
                            <p class="text-gray-500"><?php echo htmlspecialchars($lecturer['department']); ?></p>
                            <p class="text-gray-500"><?php echo htmlspecialchars($lecturer['designation']); ?></p>
                        </div>
                        <div class="flex space-x-4">
                            <button
                                class="text-gray-500 hover:text-blue-600 p-2 rounded-full hover:bg-blue-50 transition-colors duration-300"
                                title="View Details">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="lecturer-modal"
        class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 modal-hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modal-title" class="text-xl font-semibold"></h3>
                <button id="close-modal" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <p id="modal-faculty" class="text-gray-500 mb-4"></p>
            <p id="modal-department" class="text-gray-500 mb-4"></p>
            <p id="modal-designation" class="text-gray-500 mb-4"></p>
            <div id="modal-availability" class="text-gray-700 mb-4"></div>
            <a id="make-appointment-btn" href="#"
                class="block text-center bg-blue-800 text-white py-2 rounded hover:bg-blue-900">Make Appointment</a>
        </div>
    </div>

    <?php
    include("footer.php");
    ?>

    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
        $(document).ready(function() {
            // Function to attach event listeners to lecturer cards
            function attachLecturerCardListeners() {
                const lecturerCards = document.querySelectorAll('.lecturer-card');
                const modal = document.getElementById('lecturer-modal');
                const closeModalBtn = document.getElementById('close-modal');
                const modalTitle = document.getElementById('modal-title');
                const modalFaculty = document.getElementById('modal-faculty');
                const modalDepartment = document.getElementById('modal-department');
                const modalDesignation = document.getElementById('modal-designation');
                const modalAvailability = document.getElementById('modal-availability');
                const makeAppointmentBtn = document.getElementById('make-appointment-btn');

                lecturerCards.forEach(card => {
                    card.removeEventListener('click',
                        handleCardClick); // Remove existing listeners to prevent duplicates
                    card.addEventListener('click', handleCardClick);
                });

                function handleCardClick() {
                    const lecturerId = this.getAttribute('data-id');
                    const lecturerName = this.querySelector('h3').textContent;
                    const faculty = this.querySelector('p:nth-child(2)').textContent;
                    const department = this.querySelector('p:nth-child(3)').textContent;
                    const designation = this.querySelector('p:nth-child(4)').textContent;
                    const availability = JSON.parse(this.getAttribute('data-availability'));

                    // Set modal content
                    modalTitle.textContent = lecturerName;
                    modalFaculty.textContent = `Faculty: ${faculty}`;
                    modalDepartment.textContent = `Department: ${department}`;
                    modalDesignation.textContent = `Designation: ${designation}`;

                    // Format availability
                    if (availability.length > 0) {
                        modalAvailability.innerHTML = '<strong>Available:</strong><br>' + availability.join('<br>');
                    } else {
                        modalAvailability.innerHTML = '<strong>Available:</strong> No availability set.';
                    }

                    makeAppointmentBtn.setAttribute('href', `calendar.php?lecturer_id=${lecturerId}`);

                    // Show the modal
                    modal.classList.remove('modal-hidden');
                    modal.classList.add('modal-visible');
                }

                closeModalBtn.addEventListener('click', () => {
                    modal.classList.remove('modal-visible');
                    modal.classList.add('modal-hidden');
                });

                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('modal-visible');
                        modal.classList.add('modal-hidden');
                    }
                });
            }

            // Initial attachment of event listeners
            attachLecturerCardListeners();

            // Search functionality
            $('#search-filter').on('input', function() {
                var searchTerm = $(this).val().trim();
                console.log('Search term:', searchTerm);

                const lecturerList = $('#lecturer-list');
                console.log('Before AJAX - #lecturer-list exists:', lecturerList.length ? 'Yes' : 'No');
                if (!lecturerList.length) {
                    console.error('lecturer-list element not found before AJAX');
                    alert('Error: Lecturer list container not found. Please refresh the page.');
                    return;
                }

                lecturerList.prepend('<div class="loading">Loading...</div>');
                $('.loading').show();

                $.ajax({
                    url: 'lecturer_search.php',
                    type: 'POST',
                    data: {
                        search: searchTerm
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('.loading').remove();
                        console.log('AJAX response:', response);

                        const lecturerListAfter = $('#lecturer-list');
                        console.log('After AJAX - #lecturer-list exists:', lecturerListAfter
                            .length ? 'Yes' : 'No');
                        if (!lecturerListAfter.length) {
                            console.error('lecturer-list element not found after AJAX');
                            alert(
                                'Error: Lecturer list container not found after search. Please refresh the page.'
                            );
                            return;
                        }

                        if (response.error) {
                            alert('Error: ' + response.error + '\nDebug: ' + JSON.stringify(
                                response.debug));
                            return;
                        }
                        lecturerListAfter.html(response.html);
                        attachLecturerCardListeners(); // Re-attach event listeners to new cards
                    },
                    error: function(xhr, status, error) {
                        $('.loading').remove();
                        console.error('AJAX Error:', status, error);
                        console.log('Raw response:', xhr.responseText);
                        alert(
                            'Failed to fetch search results. Raw response logged to console.'
                        );
                    }
                });
            });

            // Clear search functionality
            $('#search-filter').on('input', function() {
                $('#clear-search').toggle(!!$(this).val());
            });

            $('#clear-search').on('click', function() {
                $('#search-filter').val('').trigger('input');
            });
        });
    </script>
</body>

</html>