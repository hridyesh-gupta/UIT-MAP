<?php
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="mentor"){ //If the user is not mentor, then it means the user is someone else and is accessing this page through url editing as we have provided mentor usertype to every user who logged in via mentor credentials. So, redirecting to login page
    header("location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Mentor Panel</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

    <!-- Header -->
    <header class="bg-blue-600 text-white p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <img src="COLLEGE.png" alt="College Logo" class="h-12">
            <h1 class="text-3xl font-bold">MAP - Mentor Panel</h1>
            <div></div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold mb-6">Mentor Panel</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- <button class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-green-600 transition duration-300" onclick="location.href='addstudent.php'">Add Student</button> -->
                <button class="bg-blue-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300" onclick="location.href='groups.php'">Groups</button>
                <button class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-green-600 transition duration-300" onclick="location.href='guidelines.php'">Guidelines</button>
                <button class="bg-red-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-red-600 transition duration-300 col-span-full sm:col-span-2 lg:col-span-1" onclick="location.href='logout.php'">Logout</button>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-blue-500 text-white p-4 mt-8">
        <div class="max-w-6xl mx-auto text-center">
            <p>&copy; 2024 Your College Name. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>