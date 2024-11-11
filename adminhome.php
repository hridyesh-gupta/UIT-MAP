<?php
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="mentor"){ //If the user is not admin or mentor, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
    header("location: index.php");
}
include 'dbconnect.php'; //Database connection

// Fetch years from the batches table
$yearsQuery = "SELECT DISTINCT batchyr FROM batches ORDER BY batchyr ASC";
$yearsResult = $conn->query($yearsQuery);
$years = [];
if ($yearsResult->num_rows > 0) {
    while ($row = $yearsResult->fetch_assoc()) {
        $years[] = $row['batchyr'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if($_SESSION['usertype'] == "admin"){ ?>                
        <title>MAP - Admin Panel</title>
    <?php } ?>
    <?php if($_SESSION['usertype'] == "mentor"){ ?>
        <title>MAP - Mentor Panel</title>
    <?php } ?>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
        <style>
            .modal {
                display: none; /* Hidden by default */
                position: fixed;
                z-index: 1000; /* Ensure the modal appears above other elements */
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0, 0, 0, 0.4); /* Black with opacity */
                padding-top: 60px;
            }
            .modal-content {
                background-color: #fefefe;
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                z-index: 1001; /* Ensure the modal content appears above the modal background */
            }
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }
            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
            }
            .bg-blue-500 {
                background-color: #3b82f6;
            }
            .text-white {
                color: #ffffff;
            }
            .py-2 {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }
            .px-4 {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .rounded {
                border-radius: 0.25rem;
            }
            .hover\:bg-blue-700:hover {
                background-color: #1d4ed8;
            }
            .transition {
                transition: all 0.3s ease;
            }
            .duration-300 {
                transition-duration: 300ms;
            }
        </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

    <!-- Header -->
    <header class="bg-blue-600 text-white p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <img src="COLLEGE.png" alt="College Logo" class="h-12">
            <?php if($_SESSION['usertype'] == "admin"){ ?>                
                    <h1 class="text-3xl font-bold text-center">MAP - Admin Panel</h1>
            <?php } ?>
            <?php if($_SESSION['usertype'] == "mentor"){ ?>                
                    <h1 class="text-3xl font-bold text-center">MAP - Mentor Panel</h1>
            <?php } ?>
            <div></div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <?php if($_SESSION['usertype'] == "admin"){ ?>                
                <h2 class="text-2xl font-bold mb-6">Admin Home</h2>
            <?php } ?>
            <?php if($_SESSION['usertype'] == "mentor"){ ?>                
                <h2 class="text-2xl font-bold mb-6">Mentor Home</h2>
            <?php } ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if($_SESSION['usertype'] == "admin"){ ?>                
                <button class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-green-600 transition duration-300" onclick="location.href='addstudent.php'">Admin Controls</button>
            <?php } ?>
                <button class="bg-blue-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300" onclick="location.href='#'" id="groups-link">Groups</button>
                <button class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-green-600 transition duration-300" onclick="location.href='guidelines.php'">Guidelines</button>
                <button class="bg-red-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-red-600 transition duration-300" onclick="location.href='logout.php'">Logout</button>
            </div>
        </div>
    </main>
        <!-- Modal for Year Selection -->
        <div id="yearSelectionModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="modal-content bg-white p-6 rounded-lg shadow-lg">
            <span class="close text-gray-500 cursor-pointer">&times;</span>
            <h2 class="text-xl font-bold mb-4">Select Batch:</h2>
            <div id="yearButtonsContainer" class="space-y-2">
                <!-- Year buttons will be dynamically added here -->
            </div>
        </div>
    </div>
    <?php include 'footer.php' ?>
    <!-- JavaScript -->
    <script>
        //Variables for year selection modal
        const years = <?php echo json_encode($years); ?>;//Contains the years from the batches table
        const groupsLink = document.getElementById('groups-link');
        const yearSelectionModal = document.getElementById('yearSelectionModal');
        const yearButtonsContainer = document.getElementById('yearButtonsContainer');
        const closeModal = document.querySelector('.close');

        // Populate the modal with year buttons
        years.forEach(year => {
            const button = document.createElement('button');
            button.classList.add('bg-blue-500', 'text-white', 'py-2', 'px-4', 'rounded', 'hover:bg-blue-700', 'transition', 'duration-300');
            button.textContent = year;
            button.style.marginRight = '10px';
            button.addEventListener('click', () => {
                // Handle year button click
                console.log(`Year ${year} selected`);
                // Redirect to the groups page with the selected year as a query parameter
                window.location.href = `groups.php?year=${year}`;
            });
            yearButtonsContainer.appendChild(button);
        });

        // Show the modal when the "Groups" link is clicked
        groupsLink.addEventListener('click', (event) => {
            event.preventDefault();
            yearSelectionModal.style.display = 'flex';
        });

        // Close the modal when the close button is clicked
        closeModal.addEventListener('click', () => {
            yearSelectionModal.style.display = 'none';
        });

        // Close the modal when clicking outside of the modal content
        window.addEventListener('click', (event) => {
            if (event.target == yearSelectionModal) {
                yearSelectionModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>
