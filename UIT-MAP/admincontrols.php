<?php
session_start();
error_reporting(0); //To hide the errors
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin"){ //If the user is not admin, then redirecting to login page
    header("location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Admin Controls</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

    <?php include 'adminheaders.php' ?>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <center><h2 class="text-2xl font-bold mb-6">Admin Controls</h2></center>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-2xl mx-auto">
                <button class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-green-600 transition duration-300" onclick="location.href='addstudent.php'">Add Student</button>
                <button class="bg-purple-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-purple-600 transition duration-300" onclick="location.href='manage_dates.php'">Manage Dates</button>
            </div>
        </div>
    </main>

    <?php include 'footer.php' ?>

</body>
</html>
