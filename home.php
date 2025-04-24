<?php
include("header.php");
?>
<main class="container mx-auto px-6 py-8">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Box 1 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($res_username); ?>
                (<?php echo htmlspecialchars($res_role_name); ?>)</h2>
            <p class="mb-2"><strong>Email Address:</strong> <?php echo htmlspecialchars($res_email); ?></p>
            <p class="mb-2"><strong>Current Date & Time:</strong></p>
            <p id="datetime"></p>
        </div>

        <!-- Box 2 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">List of Lecturers</h2>
            <div class="flex justify-end">
                <button
                    class="bg-white px-6 py-3 rounded-lg shadow-md text-blue-600 text-2xl font-bold hover:bg-blue-50 transition-colors duration-300">
                    <a href="lecturer_list.php">Create Appointment</a>
                </button>
            </div>
        </div>

        <!-- Box 3 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Appointments</h2>
            <div class="flex justify-end">
                <button
                    class="bg-white px-6 py-3 rounded-lg shadow-md text-blue-600 text-2xl font-bold hover:bg-blue-50 transition-colors duration-300">
                    <a href="appointment_view.php">More...</a>
                </button>
            </div>
        </div>

        <!-- Box 4 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>
            <div class="flex justify-end">
                <button
                    class="bg-white px-6 py-3 rounded-lg shadow-md text-blue-600 text-2xl font-bold hover:bg-blue-50 transition-colors duration-300">
                    <?php echo "<a href='edit_profile.php?id=$res_id'>" ?>Click to Edit</a>
                </button>
            </div>
        </div>
    </div>
</main>



<?php include("footer.php"); ?>