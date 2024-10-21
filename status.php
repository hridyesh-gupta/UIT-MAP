<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Project Status</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <style>
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

    <!-- Main Content (Dynamic) -->
    <div class="container mx-auto py-8">
        <div id="weeks-container">
            <!-- Week 1 -->
            <div class="week-section mt-8">
                <h2 class="text-3xl font-bold mb-4">Week 1</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="border border-gray-300 p-4">
                        <h3 class="font-bold">Student 1</h3>
                        <p>Progress: Satisfactory</p>
                        <p>Status: <span class="approved">Approved</span></p>
                        <p>Date: 2024-09-19 10:00 AM</p>
                    </div>
                    <div class="border border-gray-300 p-4">
                        <h3 class="font-bold">Student 2</h3>
                        <p>Progress: Not Satisfactory</p>
                        <p>Status: <span class="not-approved">Not Approved</span></p>
                        <p>Date: 2024-09-19 11:00 AM</p>
                    </div>
                    <div class="border border-gray-300 p-4">
                        <h3 class="font-bold">Student 3</h3>
                        <p>Progress: Satisfactory</p>
                        <p>Status: <span class="approved">Approved</span></p>
                        <p>Date: 2024-09-19 12:00 PM</p>
                    </div>
                    <div class="border border-gray-300 p-4">
                        <h3 class="font-bold">Student 4</h3>
                        <p>Progress: Not Satisfactory</p>
                        <p>Status: <span class="not-approved">Not Approved</span></p>
                        <p>Date: 2024-09-19 01:00 PM</p>
                    </div>
                </div>
            </div>
            <!-- More week sections will be added here -->
        </div>

        <!-- Button to add more weeks -->
        <button id="add-week-button" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
            Add More Weeks
        </button>
    </div>

    <!-- Include footer (Visible) -->
    <?php include 'footer.php'; ?>

    <script>
        let weekCount = 1; // Track the number of weeks

        document.getElementById('add-week-button').addEventListener('click', function() {
            weekCount++; // Increment week count
            const weeksContainer = document.getElementById('weeks-container');

            // Create new week section
            const newWeekSection = document.createElement('div');
            newWeekSection.className = 'week-section mt-8';
            newWeekSection.innerHTML = `
                <h2 class="text-3xl font-bold mb-4">Week ${weekCount}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="border border-gray-300 p-4">
                        <h3 class="font-bold">Student 1</h3>
                        <p>Progress: Satisfactory</p>
                        <p>Status: <span class="approved">Approved</span></p>
                        <p>Date: 2024-09-26 10:00 AM</p>
                    </div>
                    <div class="border border-gray-300 p-4">
                        <h3 class="font-bold">Student 2</h3>
                        <p>Progress: Not Satisfactory</p>
                        <p>Status: <span class="not-approved">Not Approved</span></p>
                        <p>Date: 2024-09-26 11:00 AM</p>
                    </div>
                    <div class="border border-gray-300 p-4">
                        <h3 class="font-bold">Student 3</h3>
                        <p>Progress: Satisfactory</p>
                        <p>Status: <span class="approved">Approved</span></p>
                        <p>Date: 2024-09-26 12:00 PM</p>
                    </div>
                    <div class="border border-gray-300 p-4">
                        <h3 class="font-bold">Student 4</h3>
                        <p>Progress: Not Satisfactory</p>
                        <p>Status: <span class="not-approved">Not Approved</span></p>
                        <p>Date: 2024-09-26 01:00 PM</p>
                    </div>
                </div>
            `;
            weeksContainer.appendChild(newWeekSection); // Append new section
        });
    </script>
</body>
</html>
