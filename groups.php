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
            <div class="mb-6 flex justify-between">
                <input type="text" id="searchInput" placeholder="Search for groups by Project Name..." class="w-full p-2 border rounded">
                <button onclick="filterApproved()" class="bg-blue-500 text-white py-2 px-4 ml-4 rounded hover:bg-blue-600 transition duration-300">Show Approved Groups </button>
            </div>

            <!-- Group List -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="w-1/4 py-2">Group ID</th>
                            <th class="w-1/4 py-2">Project Name</th>
                            <th class="w-1/4 py-2">Project Members</th>
                            <th class="w-1/4 py-2">Mentor Assigned</th>
                            <th class="w-1/4 py-2">Select Group</th>
                        </tr>
                    </thead>
                    <tbody id="groupTable">
                        <!-- Sample group items. Replace with actual group data. -->
                        <tr class="group-item" data-approved="true">
                            <td class="border px-4 py-2">1</td>
                            <td class="border px-4 py-2">Project Alpha</td>
                            <td class="border px-4 py-2">Sarthak Singh, Sharad Srivastava</td>
                            <td class="border px-4 py-2">Dr. Amit Kumar Tiwari</td>
                            <td class="border px-4 py-2 text-center"><input type="radio" name="selectedGroup" value="1"></td>
                        </tr>
                        <tr class="group-item" data-approved="false">
                            <td class="border px-4 py-2">2</td>
                            <td class="border px-4 py-2">Project Beta</td>
                            <td class="border px-4 py-2">Hridyesh Gupta, Harsh Kumar</td>
                            <td class="border px-4 py-2">Prof.Sanjay Srivastava</td>
                            <td class="border px-4 py-2 text-center"><input type="radio" name="selectedGroup" value="2"></td>
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
        function filterApproved() {
            const rows = document.querySelectorAll('.group-item');
            rows.forEach(row => {
                if (row.getAttribute('data-approved') === 'true') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

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
    </script>

</body>
</html>
