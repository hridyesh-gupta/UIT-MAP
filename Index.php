<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIT-MAP Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include 'favicon.php' ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #466079;
            overflow: hidden; /* Prevents scrolling when images transition */
        }
        /* Slideshow container */
        .slideshow-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1; /* Behind the login box */
        }
        /* Slideshow images */
        .slide {
            display: none;
            position: absolute;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: transform 4s ease-in-out; /* Transition duration set to 4 seconds for a slow effect */
        }
        /* Make the current slide visible */
        .active {
            display: block;
            transform: translateX(0); /* Current slide in place */
        }
        /* Previous and next slides */
        .prev {
            display: block;
            transform: translateX(-100%); /* Slide in from left */
        }
        .next {
            display: block;
            transform: translateX(100%); /* Slide in from right */
        }
        /* Background box styling */
        .login-box {
            background-color: white; /* 100% opaque background */
            width: 90%; /* Dynamic width for responsiveness */
            max-width: 350px; /* Further reduced max width of the box */
            padding: 1rem; /* Reduced padding for the box */
            margin: auto; /* Center the box vertically */
            position: absolute; /* Positioned absolutely */
            top: 50%; /* Centered vertically */
            left: 20px; /* Position from left side */
            transform: translateY(-50%); /* Move it up by half its height */
            box-shadow: 0 10px 20px rgba(0,0,0,0.2); /* Slight shadow for better look */
            border-radius: 8px;
        }

        /* Media Queries for better responsiveness */
        @media (max-width: 768px) {
            .login-box {
                left: 10px; /* Reduce left padding */
                padding: 0.8rem; /* Adjust padding on smaller screens */
            }
        }

        @media (max-width: 640px) {
            .login-box {
                width: 100%; /* Use full width for very small screens */
                max-width: none; /* Remove max-width restriction */
                left: 0; /* No left margin */
                padding: 0.5rem; /* Reduce padding */
                top: 50%; /* Center the box vertically */
                transform: translateY(-50%); /* Adjust for smaller heights */
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
            }

            .login-box img {
                width: 150px; /* Adjust image size */
            }

            .login-box h1 {
                font-size: 1.2rem; /* Smaller font for title */
            }

            .login-box {
                padding: 1rem; /* Adjust padding */
            }

            .slideshow-container {
                height: 80%; /* Adjust slideshow height */
            }
        }
    </style>
</head>
<body class="flex items-center justify-start min-h-screen p-4">

    <!-- Slideshow container -->
    <div class="slideshow-container">
        <div class="slide active" style="background-image: url('Slideshow1.jpg');"></div>
        <div class="slide" style="background-image: url('Slideshow2.jpg');"></div>
        <div class="slide" style="background-image: url('Slideshow3.jpg');"></div>
        <div class="slide" style="background-image: url('Slideshow4.jpg');"></div>
        <div class="slide" style="background-image: url('Slideshow5.jpg');"></div>
        <div class="slide" style="background-image: url('Slideshow7.JPG');"></div>
        <div class="slide" style="background-image: url('Slideshow8.JPG');"></div>
        <div class="slide" style="background-image: url('Slideshow9.JPG');"></div>
        <div class="slide" style="background-image: url('Slideshow10.jpg');"></div>
        <div class="slide" style="background-image: url('Slideshow11.jpg');"></div>
        <div class="slide" style="background-image: url('Slideshow12.jpg');"></div>
    </div>

    <div class="flex flex-col login-box shadow-lg rounded-lg"> <!-- Positioned on the left -->
        <div class="flex justify-center mb-4">
            <img src="COLLEGE.png" alt="United Group Of Institution logo" width="180" height="90">
        </div>
        <h1 class="text-center text-xl font-bold mb-2">Login Here</h1>
        <form action="login_check.php" method="POST">
            <div class="mb-3">
                <label for="uniqueId" class="block text-sm font-medium text-gray-700">Unique Id</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="text" id="uniqueId" name="uniqueId" pattern="[A-Za-z0-9]{4,20}" title="Please enter a valid unique ID (4-20 alphanumeric characters)" class="block w-full pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="password" name="password" id="password" class="block w-full pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                        <i class="fas fa-eye text-gray-400" id="togglePassword"></i>
                    </div>
                </div>
            </div>
            <h4 class="text-center text-red-500">
            <?php
                session_start();
                if (isset($_SESSION['loginMessage'])) {
                    echo htmlspecialchars($_SESSION['loginMessage']); 
                }
                session_destroy();
                if(isset($_SESSION['status'])){
                    echo "<p>" . htmlspecialchars($_SESSION['status']) . "</p>";
                    unset($_SESSION['status']);
                }
            ?>
            </h4>
            <div class="flex justify-center">
                <button type="submit" name="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Login</button>
            </div>
            <div class="mt-4 text-center">
                <a href="forgotpassword.php" class="text-blue-500 text-sm">Forgot your password?</a>
            </div>
        </form>
        <div class="mt-4 text-xs text-center text-gray-500">
            <p>Copyright © 2024 Prototype UNITED INSTITUTE OF TECHNOLOGY</p>
            <p class="mt-2">
                <a href="#" class="text-blue-500">Terms of Service</a> |
                <a href="#" class="text-blue-500">Privacy Policy</a>
            </p>
        </div>
    </div>

    <script>
        const passwordField = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Slideshow functionality
        let slideIndex = 0;
        const slides = document.querySelectorAll('.slide');

        function showSlides() {
            slides.forEach((slide, index) => {
                slide.classList.remove('active', 'prev', 'next'); // Remove classes from all slides
                if (index === slideIndex) {
                    slide.classList.add('active'); // Show the current slide
                } else if (index === (slideIndex - 1 + slides.length) % slides.length) {
                    slide.classList.add('prev'); // Show the previous slide
                } else if (index === (slideIndex + 1) % slides.length) {
                    slide.classList.add('next'); // Show the next slide
                }
            });
            slideIndex = (slideIndex + 1) % slides.length; // Increment the index
        }

        setInterval(showSlides, 5000); // Change the image every 5 seconds
    </script>
</body>
</html>
