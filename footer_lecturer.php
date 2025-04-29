</main>

<footer class="bg-gradient-to-r from-blue-600 to-indigo-800 text-white py-8">

    <footer class="bg-gradient-to-r from-blue-600 to-indigo-800 text-white py-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-col items-center space-y-6">
                <div class="flex space-x-6">
                    <a href="" class="text-2xl hover:text-blue-200 transition-colors duration-300">
                        <i class="fa-brands fa-facebook"></i>
                    </a>
                    <a href="" class="text-2xl hover:text-blue-200 transition-colors duration-300">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="" class="text-2xl hover:text-blue-200 transition-colors duration-300">
                        <i class="fa-brands fa-google-plus"></i>
                    </a>
                    <a href="" class="text-2xl hover:text-blue-200 transition-colors duration-300">
                        <i class="fa-brands fa-youtube"></i>
                    </a>
                </div>
                <div>
                    <ul class="flex space-x-6">
                        <li>
                            <a href="home_lecturer.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="appointment_view_lecturer.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">
                                Appointments
                            </a>
                        </li>

                        <li>
                            <a href="set_availability.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">
                                Set Availability
                            </a>
                        </li>
                        <li>
                            <?php echo "<a href='edit_profile_lecturer.php?id=$res_id'" ?>
                            class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">
                            Edit Profile
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="text-center">
                    <p class="text-sm">© 2024 LEE JUN KHANG. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    </body>

    </html>