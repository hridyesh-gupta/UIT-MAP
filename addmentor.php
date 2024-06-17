<?php
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin"){ //If the user is not admin, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
    header("location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Add Mentor</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

    <!-- Header -->
    <header class="bg-blue-600 text-white p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <img src="COLLEGE.png" alt="College Logo" class="h-12">
            <h1 class="text-3xl font-bold">MAP - Admin Panel</h1>
            <div></div>
        </div>
    </header>

    <!-- Sub-header -->
    <nav class="bg-blue-500 text-white">
    <div class="max-w-6xl mx-auto p-4 flex justify-between">
            <a href="addstudent.php" class="text-lg">Add Student</a>
            <a href="viewstudent.php" class="text-lg">View Students</a>
            <a href="addmentor.php" class="text-lg font-bold underline">Add Mentor</a>
            <a href="viewmentor.php" class="text-lg">View Mentors</a>
            <a href="" class="text-lg">Guidelines</a>
            <a href="logout.php" class="text-lg">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold mb-6">Add Mentor</h2>

            <!-- <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <button onclick="showForm('student')" class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-green-600 transition duration-300">Add Student</button>
                <button class="bg-blue-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">View Students</button>
                <button onclick="showForm('mentor')" class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">Add Mentor</button>
                <button class="bg-blue-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">View Mentors</button>
                <button class="bg-red-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-red-600 transition duration-300 col-span-full sm:col-span-2 lg:col-span-1">Logout</button>
            </div> -->

            <!-- Forms -->
            <div id="form-container" class="mt-8">
                <div id="mentor-form" class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-bold mb-4">Add Mentor</h3>
                    <form>
                        <label class="block mb-2">Mentor Name:</label>
                        <input type="text" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Mentor E-mail ID:</label>
                        <input type="email" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Mentor Login ID:</label>
                        <input type="text" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Mentor Password:</label>
                        <input type="password" class="w-full p-2 border rounded mb-4" required>
                        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-300">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-blue-500 text-white p-4 mt-8">
        <div class="max-w-6xl mx-auto text-center">
            <p>&copy; 2024 Your College Name. All rights reserved.</p>
        </div>
    </footer>

    <!-- <script>
        function showForm(type) {
            document.getElementById('form-container').classList.remove('hidden');
            if (type === 'student') {
                document.getElementById('mentor-form').classList.add('hidden');
                document.getElementById('student-form').classList.remove('hidden');
            } else if (type === 'mentor') {
                document.getElementById('student-form').classList.add('hidden');
                document.getElementById('mentor-form').classList.remove('hidden');
            }
        }
    </script> -->

</body>
</html>
