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
            background-image: url('img/uit.png');
            background-size: cover;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

<div class="p-8 w-full lg:w-1/3 bg-white rounded bg-opacity-80">
<h2 class="text-2xl font-bold mb-4"><center>UIT-MAP: Monitoring and Assessment of Project</center></h2>
        <div class="flex justify-center mb-6">
            <img src="img/COLLEGE_T.png" alt="United Institute of Technology logo" width="200" height="100">
            </div>
            <h1 class="text-center text-2xl font-bold mb-4">Login Here</h1>
            <form action="login.php" method="POST">
                <div class="mb-4">
                    <label for="uniqueId" class="block text-sm font-medium text-black-700"><b>Unique Id</b></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="text" id="uniqueId" name="uniqueId" pattern="[A-Za-z0-9]{4,20}" title="Please enter a valid unique ID (4-20 alphanumeric characters)" class="block w-full pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-black-700"><b>Password</b></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="password" name="password" id="password" class="block w-full pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                        <i class="fas fa-eye text-gray-400" id="togglePassword"></i>
                    </div>
                        <h4>
                        <!-- To print error message if the username or password is incorrect -->
                        <?php
                            session_start();
                            if (isset($_SESSION['loginMessage'])) {//This will execute only if there is any error message stored in session variable by the login page
                                echo '<span style="color: red;">' . $_SESSION['loginMessage'] . '</span>'; //Fetching the login error message from login page and displaying it here if incorrect username or password is entered
                            }
                            session_destroy();
                            if(isset($_SESSION['status'])){
                                echo "<p style='color: red;'>" . $_SESSION['status'] . "</p>";
                                unset($_SESSION['status']);
                            }
                        ?>
                        </h4>
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="submit" name="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Login</button>
                </div>
                <div class="mt-4 text-center">
                    <a href="forgotpassword.php" class="text-blue-500 text-sm"><b>Forgot your password?</b></a>
                </div>
            </form>
            <div class="mt-4 text-xs text-center text-black-500">
                <p class="mt-2">
                        <b>Made with &#10084; by <a href="https://www.linkedin.com/in/hridyesh-gupta/" class="text-blue-500">Hridyesh</a> and team</b>
                </p>
            </div>
            <div class="mt-2 text-xs text-center text-gray-500">
                <p class="text-sm">&copy; 2025 • UNITED INSTITUTE OF TECHNOLOGY</p>
            </div>
        </div>
    <script>
        const passwordField = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            // Toggle password visibility
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle eye icon between open and closed
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>