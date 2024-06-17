<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Admin Home</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

    <!-- Header -->
    <header class="bg-blue-600 text-white p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <img src="COLLEGE.png" alt="College Logo" class="h-12">
            <h1 class="text-3xl font-bold">MAP - Admin Home</h1>
            <div></div>
        </div>
    </header>

    <!-- Sub-header -->
    <nav class="bg-blue-500 text-white">
        <div class="max-w-6xl mx-auto p-4 flex justify-between">
            <a href="1.1stdraft.html" class="text-lg">Student Details</a>
            <a href="2nddraft.html" class="text-lg">Guidelines</a>
            <a href="3rddraft.html" class="text-lg">View Rubrics</a>
            <a href="4thdraft.html" class="text-lg">Project Detail</a>
            <a href="#" class="text-lg">Project Status</a>
            <a href="#" class="text-lg">Evaluation</a>
            <a href="#" class="text-lg">Project Marks</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold mb-6">Admin Rights</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <button onclick="showForm('student')" class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-green-600 transition duration-300">Add Student</button>
                <button onclick="showForm('teacher')" class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">Add Teacher</button>
                <button class="bg-blue-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">View Student</button>
                <button class="bg-blue-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">View Teacher</button>
                <button class="bg-red-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-red-600 transition duration-300 col-span-full sm:col-span-2 lg:col-span-1">Logout</button>
            </div>

            <!-- Forms -->
            <div id="form-container" class="mt-8 hidden">
                <div id="student-form" class="bg-white p-6 rounded-lg shadow-lg hidden">
                    <h3 class="text-xl font-bold mb-4">Add Student</h3>
                    <form>
                        <label class="block mb-2">Student Name:</label>
                        <input type="text" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Student E-mail ID:</label>
                        <input type="email" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Student Login ID:</label>
                        <input type="text" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Student Password:</label>
                        <input type="password" class="w-full p-2 border rounded mb-4" required>
                        <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition duration-300">Submit</button>
                    </form>
                </div>
                <div id="teacher-form" class="bg-white p-6 rounded-lg shadow-lg hidden">
                    <h3 class="text-xl font-bold mb-4">Add Teacher</h3>
                    <form>
                        <label class="block mb-2">Teacher Name:</label>
                        <input type="text" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Teacher E-mail ID:</label>
                        <input type="email" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Teacher Login ID:</label>
                        <input type="text" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Teacher Password:</label>
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

    <script>
        function showForm(type) {
            document.getElementById('form-container').classList.remove('hidden');
            if (type === 'student') {
                document.getElementById('teacher-form').classList.add('hidden');
                document.getElementById('student-form').classList.remove('hidden');
            } else if (type === 'teacher') {
                document.getElementById('student-form').classList.add('hidden');
                document.getElementById('teacher-form').classList.remove('hidden');
            }
        }
    </script>

</body>
</html>
