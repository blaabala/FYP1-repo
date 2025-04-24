<?php
include('database.php');

// Fetch lecturers
$query = "SELECT id, username, faculty, department, designation FROM lecturers";
$result = $con->query($query);

$lecturers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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

    /* Ensure the body takes up the full viewport height */
    html,
    body {
        height: 100%;
        margin: 0;
    }
    </style>
</head>

<body class="bg-gray-100 font-merriweather">
    <?php
    include("header.php");
    ?>


    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-4">
            <input type="text" placeholder="Filter" class="border p-2 rounded w-1/4">
            <select class="border p-2 rounded">
                <option>All Faculties</option>
                <option>FICT</option>
            </select>
        </div>

        <div id="lecturer-list">
            <?php foreach ($lecturers as $lecturer): ?>
            <div class="lecturer-card bg-white p-4 mb-2 rounded shadow flex justify-between items-center cursor-pointer"
                data-id="<?php echo $lecturer['id']; ?>">
                <div>
                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($lecturer['username']); ?></h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($lecturer['faculty']); ?></p>
                    <p class="text-gray-500"><?php echo htmlspecialchars($lecturer['department']); ?></p>
                    <p class="text-gray-500"><?php echo htmlspecialchars($lecturer['designation']); ?></p>
                </div>
                <div class="flex space-x-4">
                    <!-- View Details Button (opens modal) -->
                    <button
                        class="text-gray-500 hover:text-blue-600 p-2 rounded-full hover:bg-blue-50 transition-colors duration-300"
                        title="View Details">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
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
            <!-- <p id="modal-specialty" class="text-gray-600 mb-2"></p> -->
            <p id="modal-faculty" class="text-gray-500 mb-4"></p>
            <p id="modal-department" class="text-gray-500 mb-4"></p>
            <p id="modal-designation" class="text-gray-500 mb-4"></p>
            <p id="modal-availability" class="text-gray-700 mb-4"></p>
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
        card.addEventListener('click', () => {
            const lecturerId = card.getAttribute('data-id');
            const lecturerName = card.querySelector('h3').textContent;
            const faculty = card.querySelector('p:nth-child(2)').textContent;
            const department = card.querySelector('p:nth-child(3)').textContent;
            const designation = card.querySelector('p:nth-child(4)').textContent;

            // Set modal content
            modalTitle.textContent = lecturerName;
            modalFaculty.textContent = `Faculty: ${faculty}`;
            modalDepartment.textContent = `Department: ${department}`;
            modalDesignation.textContent = `Designation: ${designation}`;
            modalAvailability.textContent =
                "Available: 24 Apr 2025, 9:00 AM - 12:00 PM | 25 Apr 2025, 2:00 PM - 4:00 PM";
            makeAppointmentBtn.setAttribute('href', `calendar.php?lecturer_id=${lecturerId}`);

            // Show the modal
            modal.classList.remove('modal-hidden');
            modal.classList.add('modal-visible');
        });
    });

    closeModalBtn.addEventListener('click', () => {
        modal.classList.remove('modal-visible');
        modal.classList.add('modal-hidden');
    });

    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('modal-visible');
            modal.classList.add('modal-hidden');
        }
    });
    </script>
</body>

</html>