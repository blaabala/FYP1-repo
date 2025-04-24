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
            <h2 class="text-2xl font-bold mb-4">Function1</h2>
            <div class="flex justify-end">
                <button
                    class="bg-white px-6 py-3 rounded-lg shadow-md text-blue-600 text-2xl font-bold hover:bg-blue-50 transition-colors duration-300">
                    Button 1
                </button>
            </div>
        </div>

        <!-- Box 3 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Function2</h2>
            <div class="flex justify-end">
                <button
                    class="bg-white px-6 py-3 rounded-lg shadow-md text-blue-600 text-2xl font-bold hover:bg-blue-50 transition-colors duration-300">
                    Button 2
                </button>
            </div>
        </div>

        <!-- Box 4 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Function3</h2>
            <div class="flex justify-end">
                <button
                    class="bg-white px-6 py-3 rounded-lg shadow-md text-blue-600 text-2xl font-bold hover:bg-blue-50 transition-colors duration-300">
                    Button 3
                </button>
            </div>
        </div>
    </div>
</main>



<?php include("footer.php"); ?>