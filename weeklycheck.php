<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Weekly Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .approved {
            background-color: green;
            color: white;
            padding: 5px;
            border-radius: 4px;
        }
        .not-approved {
            background-color: red;
            color: white;
            padding: 5px;
            border-radius: 4px;
        }
        .container {
            max-width: 90%;
            margin: auto;
        }
        .text-area {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            resize: vertical;
        }
        /* New header styles */
        header {
            background-color: #003366;
            color: white;
            padding: 10px;
            text-align: center;
        }
        header img {
            height: 40px; /* Reduced image size */
            width: auto;
            vertical-align: middle;
        }
        header h1 {
            display: inline;
            font-size: 24px;
            margin-left: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<!-- Header -->
<header>
    <img src="COLLEGE.png" alt="College Logo">
    <h1>MAP - Weekly Check</h1>
</header>

<!-- Sub-header -->
<nav class="bg-blue-500 text-white">
    <div class="max-w-6xl mx-auto p-4 flex justify-between">
        <a href="mentorgroups.php" class="text-lg">Groups</a>
        <a href="guidelines.php" class="text-lg">Guidelines</a>
        <a href="logout.php" class="text-lg">Logout</a>
    </div>
</nav>

<!-- Weekly Performance Section -->
<div class="container">
    <!-- Week 1 -->
    <h2>Week 1</h2>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Student Progress (Satisfactory / Not Satisfactory)</th>
                <th>Supervisor Approval Status</th>
                <th>Date/Time of Approval/Disapproval</th>
            </tr>
        </thead>
        <tbody>
            <!-- Individual Performance for 4 students -->
            <tr>
                <td>Student 1</td>
                <td>Satisfactory</td>
                <td><span class="approved">Approved</span></td>
                <td>2024-09-19 10:00 AM</td>
            </tr>
            <tr>
                <td>Student 2</td>
                <td>Not Satisfactory</td>
                <td><span class="not-approved">Not Approved</span></td>
                <td>2024-09-19 11:00 AM</td>
            </tr>
            <tr>
                <td>Student 3</td>
                <td>Satisfactory</td>
                <td><span class="approved">Approved</span></td>
                <td>2024-09-19 12:00 PM</td>
            </tr>
            <tr>
                <td>Student 4</td>
                <td>Satisfactory</td>
                <td><span class="approved">Approved</span></td>
                <td>2024-09-19 01:00 PM</td>
            </tr>
        </tbody>
    </table>

    <!-- Group Performance -->
    <h3>Group Performance</h3>
    <textarea class="text-area" rows="4" placeholder="Enter group performance for Week 33"></textarea>

    <!-- Week 2 -->
    <h2>Week 2</h2>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Student Progress (Satisfactory / Not Satisfactory)</th>
                <th>Supervisor Approval Status</th>
                <th>Date/Time of Approval/Disapproval</th>
            </tr>
        </thead>
        <tbody>
            <!-- Individual Performance for 4 students -->
            <tr>
                <td>Student 1</td>
                <td>Satisfactory</td>
                <td><span class="approved">Approved</span></td>
                <td>2024-09-26 10:00 AM</td>
            </tr>
            <tr>
                <td>Student 2</td>
                <td>Not Satisfactory</td>
                <td><span class="not-approved">Not Approved</span></td>
                <td>2024-09-26 11:00 AM</td>
            </tr>
            <tr>
                <td>Student 3</td>
                <td>Satisfactory</td>
                <td><span class="approved">Approved</span></td>
                <td>2024-09-26 12:00 PM</td>
            </tr>
            <tr>
                <td>Student 4</td>
                <td>Satisfactory</td>
                <td><span class="approved">Approved</span></td>
                <td>2024-09-26 01:00 PM</td>
            </tr>
        </tbody>
    </table>

    <!-- Group Performance -->
    <h3>Group Performance</h3>
    <textarea class="text-area" rows="4" placeholder="Enter group performance for Week 34"></textarea>

    <!-- You can continue the same structure for Week 35, Week 36, etc. -->
</div>

<!-- Footer -->
<footer class="bg-blue-600 text-white p-4 text-center">
    <div class="max-w-6xl mx-auto text-center">
        <p class="text-sm">&copy; 2024 UNITED INSTITUTE OF TECHNOLOGY. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
