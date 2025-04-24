<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <title>Appointment Management System</title>

    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">


    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="assets/css/fontawesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-574-mexant.css">
    <link rel="stylesheet" href="assets/css/owl.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css">
    <!--

    TemplateMo 574 Mexant

    https://templatemo.com/tm-574-mexant

    -->
</head>

<body>


    <!-- ***** Header Area Start ***** -->
    <header class="header-area header-sticky">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <!-- ***** Logo Start ***** -->
                        <a href="#" class="logo" style="width: 80px">
                            <img src="assets/images/logo - Copy.png" alt="">
                        </a>
                        <!-- ***** Logo End ***** -->
                        <!-- ***** Menu Start ***** -->
                        <ul class="nav">
                            <li class="scroll-to-section"><a href="#" class="active">Home</a></li>
                            <li class="scroll-to-section"><a href="#about">About</a></li>
                            <li class="scroll-to-section"><a href="#contact">Contact Us</a></li>
                            <li><a href="login.php">Get Started</a></li>
                        </ul>
                        <a class='menu-trigger'>
                            <span>Menu</span>
                        </a>
                        <!-- ***** Menu End ***** -->
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!-- ***** Header Area End ***** -->

    <!-- ***** Main Banner Area Start ***** -->
    <div class="swiper-container" id="top">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="slide-inner" style="background-image:url(assets/images/slide-01.jpg)">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="header-text">
                                    <h2><em>Simplify</em> Your Scheduling with <br><em>AMS System!</em></h2>
                                    <div class="div-dec"></div>
                                    <p>AMS System is a powerful system which offers tailored supports to UTAR students
                                        and lecturers, streamlining your appointment management. Book, manage, and track
                                        appointments effortlessly.</p>
                                    <div class="buttons">
                                        <div class="green-button">
                                            <a href="login.php">Get Started</a>
                                        </div>
                                        <div class="orange-button">
                                            <a href="{{ url('contact-us') }}">Contact Us</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ***** Main Banner Area End ***** -->


        <section class="about-us" id="about">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 offset-lg-3">
                        <div class="section-heading">
                            <h6>About Us</h6>
                            <h4>Learn More About AMS</h4>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="naccs">
                            <div class="tabs">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="menu">
                                            <div class="active gradient-border"><span>Appointment <br>Booking</span>
                                            </div>
                                            <div class="gradient-border"><span>Notification <br>System</span></div>
                                            <div class="gradient-border"><span>Data <br>Security</span></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <ul class="nacc">
                                            <li class="active">
                                                <div>
                                                    <div class="main-list">
                                                        <span class="title">Description</span>
                                                    </div>
                                                    <div class="list-item">
                                                        <span class="item-title">Effortlessly schedule appointments with
                                                            a user-friendly interface, reducing booking time and
                                                            minimizing scheduling conflicts for businesses and
                                                            clients.</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div>
                                                    <div class="main-list">
                                                        <span class="title">Description</span>
                                                    </div>
                                                    <div class="list-item">
                                                        <span class="item-title">Automatically sends email and SMS
                                                            reminders to clients, ensuring they never miss an
                                                            appointment while improving attendance rates for service
                                                            providers.</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div>
                                                    <div class="main-list">
                                                        <span class="title">Description</span>
                                                    </div>
                                                    <div class="list-item">
                                                        <span class="item-title">Protects sensitive client data with
                                                            robust encryption and GDPR compliance, ensuring trust and
                                                            safety for all users of the platform.</span>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="right-content">
                            <h4>Login/Register Now To Get More Information</h4>
                            <p>This is the best solution to help you streamline your appointment management.</p>
                            <div class="green-button">
                                <a href="login.php">Get Started</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="calculator">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="left-image">
                            <img src="assets/images/calculator-image.png" alt="">
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="section-heading" id="contact">
                            <h6>CONTACT US</h6>
                            <h4>Feel Free To Contact Us</h4>
                        </div>
                        <form id="calculate" action="" method="get">
                            <div class="row">
                                <div class="col-lg-6">
                                    <fieldset>
                                        <label for="name">Your Name</label>
                                        <input type="name" name="name" id="name" placeholder="" autocomplete="on"
                                            required>
                                    </fieldset>
                                </div>
                                <div class="col-lg-6">
                                    <fieldset>
                                        <label for="email">Your Email</label>
                                        <input type="text" name="email" id="email" pattern="[^ @]*@[^ @]*"
                                            placeholder="" required="">
                                    </fieldset>
                                </div>
                                <div class="col-lg-12">
                                    <fieldset>
                                        <label for="subject">Subject</label>
                                        <input type="subject" name="subject" id="subject" placeholder=""
                                            autocomplete="on">
                                    </fieldset>
                                </div>
                                <div class="col-lg-12">
                                </div>
                                <div class="col-lg-12">
                                    <fieldset>
                                        <button type="submit" id="form-submit" class="orange-button">Submit Now</button>
                                    </fieldset>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <footer>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <p>© 2025 AMS. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Scripts -->
        <!-- Bootstrap core JavaScript -->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <script src="assets/js/isotope.min.js"></script>
        <script src="assets/js/owl-carousel.js"></script>

        <script src="assets/js/tabs.js"></script>
        <script src="assets/js/swiper.js"></script>
        <script src="assets/js/custom.js"></script>
        <script>
            var interleaveOffset = 0.5;

            var swiperOptions = {
                loop: true,
                speed: 1000,
                grabCursor: true,
                watchSlidesProgress: true,
                mousewheelControl: true,
                keyboardControl: true,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev"
                },
                on: {
                    progress: function() {
                        var swiper = this;
                        for (var i = 0; i < swiper.slides.length; i++) {
                            var slideProgress = swiper.slides[i].progress;
                            var innerOffset = swiper.width * interleaveOffset;
                            var innerTranslate = slideProgress * innerOffset;
                            swiper.slides[i].querySelector(".slide-inner").style.transform =
                                "translate3d(" + innerTranslate + "px, 0, 0)";
                        }
                    },
                    touchStart: function() {
                        var swiper = this;
                        for (var i = 0; i < swiper.slides.length; i++) {
                            swiper.slides[i].style.transition = "";
                        }
                    },
                    setTransition: function(speed) {
                        var swiper = this;
                        for (var i = 0; i < swiper.slides.length; i++) {
                            swiper.slides[i].style.transition = speed + "ms";
                            swiper.slides[i].querySelector(".slide-inner").style.transition =
                                speed + "ms";
                        }
                    }
                }
            };

            var swiper = new Swiper(".swiper-container", swiperOptions);
        </script>
</body>

</html>