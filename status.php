<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Project Status</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
    <style>
        /* Main content container */
        .main-content {
            filter: blur(10px);
            opacity: 0.5;
            pointer-events: none;
        }

        /* Overlay message */
        .overlay-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 48px;
            font-weight: bold;
            color: #333;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        /* Make sure the header and footer are not blurred */
        header, footer {
            z-index: 1001;
            position: relative;
        }
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

    <!-- Include header (Visible) -->
    <?php include 'studentheaders.php'; ?>

    <!-- Main Content (Blurred) -->
    <div class="main-content container mx-auto py-8">
        <h2 class="text-3xl font-bold mb-4">Week 1</h2>
        <table class="w-full table-auto border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2">Student Name</th>
                    <th class="border border-gray-300 px-4 py-2">Student Progress (Satisfactory / Not Satisfactory)</th>
                    <th class="border border-gray-300 px-4 py-2">Supervisor Approval Status</th>
                    <th class="border border-gray-300 px-4 py-2">Date/Time of Approval/Disapproval</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 px-4 py-2">Student 1</td>
                    <td class="border border-gray-300 px-4 py-2">Satisfactory</td>
                    <td class="border border-gray-300 px-4 py-2"><span class="approved">Approved</span></td>
                    <td class="border border-gray-300 px-4 py-2">2024-09-19 10:00 AM</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-4 py-2">Student 2</td>
                    <td class="border border-gray-300 px-4 py-2">Not Satisfactory</td>
                    <td class="border border-gray-300 px-4 py-2"><span class="not-approved">Not Approved</span></td>
                    <td class="border border-gray-300 px-4 py-2">2024-09-19 11:00 AM</td>
                </tr>
            </tbody>
        </table>

        <h2 class="text-3xl font-bold mt-8 mb-4">Week 2</h2>
        <table class="w-full table-auto border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2">Student Name</th>
                    <th class="border border-gray-300 px-4 py-2">Student Progress (Satisfactory / Not Satisfactory)</th>
                    <th class="border border-gray-300 px-4 py-2">Supervisor Approval Status</th>
                    <th class="border border-gray-300 px-4 py-2">Date/Time of Approval/Disapproval</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 px-4 py-2">Student 1</td>
                    <td class="border border-gray-300 px-4 py-2">Satisfactory</td>
                    <td class="border border-gray-300 px-4 py-2"><span class="approved">Approved</span></td>
                    <td class="border border-gray-300 px-4 py-2">2024-09-26 10:00 AM</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-4 py-2">Student 2</td>
                    <td class="border border-gray-300 px-4 py-2">Not Satisfactory</td>
                    <td class="border border-gray-300 px-4 py-2"><span class="not-approved">Not Approved</span></td>
                    <td class="border border-gray-300 px-4 py-2">2024-09-26 11:00 AM</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Overlay Message (Coming Soon) -->
    <div class="overlay-message">
        Coming Soon Developers are Working !
    </div>

    <!-- Include footer (Visible) -->
    <?php include 'footer.php'; ?>

</body>
</html>
