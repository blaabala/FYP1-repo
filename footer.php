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
                    <ul class="flex space-x-4 md:space-x-6">
                        <li>
                            <a href="home.php"
                                class="text-base md:text-lg font-medium hover:text-blue-200 transition-colors duration-300">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="lecturer_list.php"
                                class="text-base md:text-lg font-medium hover:text-blue-200 transition-colors duration-300">
                                Create Appointments
                            </a>
                        </li>
                        <li>
                            <a href="appointment_view.php"
                                class="text-base md:text-lg font-medium hover:text-blue-200 transition-colors duration-300">
                                Appointments
                            </a>
                        </li>
                        <li>
                            <?php
							echo '<a href="edit_profile.php?id=' . htmlspecialchars($res_id) . '" class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Edit Profile</a>';
							?>
                        </li>
                    </ul>
                </div>
                <div class="text-center">
                    <p class="text-sm">Contact us: <a href="tel:+60123456789"
                            class="underline hover:text-blue-200 transition-colors duration-300">+60123456789</a> | <a
                            href="mailto:info@utarhospital.my"
                            class="underline hover:text-blue-200 transition-colors duration-300">info@ams.1utar.my</a>
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm">© <?php echo date('Y'); ?> LEE JUN KHANG. All rights reserved.</p>
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