<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP UIT Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #466079;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="flex flex-col lg:flex-row bg-white shadow-lg rounded-lg max-w-4xl w-full">
        <div class="p-8 w-full lg:w-1/2">
            <div class="flex justify-center mb-6">
                <img src="COLLEGE.png" alt="United Group Of Institution logo" width="200" height="100">
            </div>
            <h1 class="text-center text-1.5xl font-bold mb-4">Login Here</h1>
            <form action="login_check.php" method="POST">
                <div class="mb-4">
                    <label for="uniqueId" class="block text-sm font-medium text-gray-700">Unique Id</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="text" id="uniqueId" name="uniqueId" pattern="[A-Za-z0-9]{4,20}" title="Please enter a valid unique ID (4-20 alphanumeric characters)" class="block w-full pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="password" name="password" id="password" class="block w-full pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <h4>
                        <!-- To print error message if the username or password is incorrect -->
                        <?php
                            session_start();
                            if (isset($_SESSION['loginMessage'])) {//This will execute only if there is any error message stored in session variable by the login check page
                                echo '<span style="color: red;">' . $_SESSION['loginMessage'] . '</span>'; //Fetching the login error message from login check page and displaying it here if incorrect username or password is entered
                            }
                            session_destroy();
                        ?>
                        </h4>
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="submit" name="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Login</button>
                </div>
                <div class="mt-4 text-center">
                    <a href="forgotpswd.php" class="text-blue-500 text-sm">Forgot your password?</a>
                </div>
            </form>
            <div class="mt-6 text-xs text-center text-gray-500">
                <p>Copyright © 2024 Prototype UNITED INSTITUTE OF TECHNOLOGY</p>
                <p class="mt-2">
                    <a href="#" class="text-blue-500">Terms of Service</a> |
                    <a href="#" class="text-blue-500">Privacy Policy</a>
                </p>
            </div>
        </div>
        <div class="p-8 w-full lg:w-1/2 bg-white">
            <h2 class="text-2xl font-bold mb-4">MAP- Monitoring And Assessment Of Student Project <i class="fas fa-trophy text-yellow-500"></i></h2>
            <p class="mb-4"> A comprehensive web-based application developed to simplify the process of maintaining project diaries for final-year students at the United Institute of Technology, Prayagraj.</p>
            <ul class="list-disc pl-5 space-y-2 mb-4">
                <li><i class="fas fa-arrow-right text-yellow-500"></i> Streamlined Project Diary Management: <a href="#" class="text-blue-500">MAP virtualizes the project diary process, eliminating the need for physical signatures and approvals.</a></li>
                <li><i class="fas fa-arrow-right text-yellow-500"></i> Admin Monitoring and Assessment:<a href="#" class="text-blue-500"> Enables admins to seamlessly monitor and assess student projects.</a></li>
                <li><i class="fas fa-arrow-right text-yellow-500"></i> Responsive Design: <a href="#" class="text-blue-500"> Accessible on multiple devices, including mobile phones and laptops.</a></li>
                <li><i class="fas fa-arrow-right text-yellow-500"></i> Time-Saving and User-Friendly: <a href="#" class="text-blue-500"> Saves time and is easy to use for both students and administrators.</a></li>
            </ul>
        </div>
    </div>

</body>
</html>
