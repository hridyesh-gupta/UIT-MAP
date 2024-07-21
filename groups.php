<!-- Admin 2nd page-->
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
    <title>MAP - View Groups</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

    <?php include 'adminheaders.php' ?>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <center><h2 class="text-2xl font-bold mb-6">View Groups</h2></center>

            <!-- Filter Box -->
            <div class="mb-6 flex justify-between items-center">
                <input type="text" id="searchInput" placeholder="Search for groups by Project Name..." class="w-full p-2 border rounded">
                <label class="ml-4 flex items-center">
                    <input type="checkbox" id="showApprovedCheckbox" class="mr-2">
                    <span>Show Approved Groups</span>
                </label>
            </div>

            <!-- Group List -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="w-1/4 py-2">Group ID</th>
                            <th class="w-1/4 py-2">Project Name</th>
                            <th class="w-1/4 py-2">Technology Used</th>
                            <th class="w-1/4 py-2">Mentor Assigned</th>
                            <th class="w-1/4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="groupTable">
                        <tr class="group-item" data-approved="true">
                            <td class="border px-4 py-2"><a href="#" class="text-blue-500 hover:underline">1</a></td>
                            <td class="border px-4 py-2"><a href="#" class="text-blue-500 hover:underline">Project Alpha</a></td>
                            <td class="border px-4 py-2">Python, Django</td>
                            <td class="border px-4 py-2">
                                <select class="w-full p-2 border rounded">
                                    <option>Dr. Amit Kumar Tiwari</option>
                                    <option>Prof. Sanjay Srivastava</option>
                                    <option>Mr. Man Singh</option>
                                </select>
                            </td>
                            <td class="border px-4 py-2 text-center">
                                <button onclick="deleteGroup(this)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600 transition duration-300">Delete</button>
                            </td>
                        </tr>
                        <tr class="group-item" data-approved="false">
                            <td class="border px-4 py-2"><a href="#" class="text-blue-500 hover:underline">2</a></td>
                            <td class="border px-4 py-2"><a href="#" class="text-blue-500 hover:underline">Project Beta</a></td>
                            <td class="border px-4 py-2">Java, Spring Boot</td>
                            <td class="border px-4 py-2">
                                <select class="w-full p-2 border rounded">
                                    <option>Dr. Amit Kumar Tiwari</option>
                                    <option>Prof. Sanjay Srivastava</option>
                                    <option>Mr. Man Singh</option>
                                </select>
                            </td>
                            <td class="border px-4 py-2 text-center">
                                <button onclick="deleteGroup(this)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600 transition duration-300">Delete</button>
                            </td>
                        </tr>
                        <tr class="group-item" data-approved="true">
                            <td class="border px-4 py-2"><a href="#" class="text-blue-500 hover:underline">3</a></td>
                            <td class="border px-4 py-2"><a href="#" class="text-blue-500 hover:underline">Project Gamma</a></td>
                            <td class="border px-4 py-2">JavaScript, React</td>
                            <td class="border px-4 py-2">
                                <select class="w-full p-2 border rounded">
                                    <option>Dr. Amit Kumar Tiwari</option>
                                    <option>Prof. Sanjay Srivastava</option>
                                    <option>Mr. Man Singh</option>
                                </select>
                            </td>
                            <td class="border px-4 py-2 text-center">
                                <button onclick="deleteGroup(this)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600 transition duration-300">Delete</button>
                            </td>
                        </tr>
                        <!-- Add more group items as needed -->
                    </tbody>
                </table>
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
        document.getElementById('showApprovedCheckbox').addEventListener('change', function() {
            const rows = document.querySelectorAll('.group-item');
            rows.forEach(row => {
                if (this.checked) {
                    if (row.getAttribute('data-approved') === 'true') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    row.style.display = '';
                }
            });
        });

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.group-item');
            rows.forEach(row => {
                const projectName = row.cells[1].textContent.toLowerCase();
                if (projectName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function deleteGroup(button) {
            const row = button.closest('tr');
            const groupId = row.cells[0].textContent;
            const confirmDelete = confirm(`Are you sure you want to delete Group ID ${groupId}?`);
            if (confirmDelete) {
                row.remove();
            }
        }
    </script>

</body>
</html>
