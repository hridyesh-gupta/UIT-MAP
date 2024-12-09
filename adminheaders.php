<?php 
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

<html>
    <head>
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
    <body>
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
                <!-- Hamburger Icon -->
                <button id="menu-toggle" class="text-white focus:outline-none md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- Sub-header for large screens -->
        <nav id="desktop-menu" class="md:block bg-blue-500 text-white">
            <div class="max-w-6xl mx-auto p-4 flex space-x-4 justify-between">
            <?php if($_SESSION['usertype'] == "admin"){ ?>                
                <a href="addstudent.php" class="text-lg">Admin Controls</a>
            <?php } ?>                
                <a href="#" id="groups-link" class="text-lg">Groups</a>
                <a href="guidelines.php" class="text-lg">Guidelines</a>
                <a href="logout.php" class="text-lg">Logout</a>
            </div>
        </nav>

        <!-- Sub-header for small screens -->
        <nav id="mobile-menu" class="bg-blue-500 text-white hidden">
            <div class="max-w-6xl mx-auto p-4 flex flex-col space-y-4">
            <?php if($_SESSION['usertype'] == "admin"){ ?>                
                <a href="addstudent.php" class="text-lg">Admin Controls</a>
            <?php } ?>
                <a href="#" id="groups-link" class="text-lg">Groups</a>
                <a href="guidelines.php" class="text-lg">Guidelines</a>
                <a href="logout.php" class="text-lg">Logout</a>
            </div>
        </nav>

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

        <!-- JavaScript -->
        <script>
            const menuToggle = document.getElementById('menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const desktopMenu = document.getElementById('desktop-menu');
            
            //Variables for year selection modal
            const years = <?php echo json_encode($years); ?>;//Contains the years from the batches table
            const groupsLink = document.querySelectorAll('#groups-link');
            const yearSelectionModal = document.getElementById('yearSelectionModal');
            const yearButtonsContainer = document.getElementById('yearButtonsContainer');
            const closeModal = document.querySelector('.close');

            function checkScreenSize() {
                if (window.innerWidth >= 768) {
                    desktopMenu.classList.remove('hidden');
                    mobileMenu.classList.add('hidden');
                    menuToggle.classList.add('hidden');
                } else {
                    desktopMenu.classList.add('hidden');
                    menuToggle.classList.remove('hidden');
                    // Don't automatically show mobile menu, keep it hidden until toggled
                }
            }

            menuToggle.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            // Initial check
            checkScreenSize();

            // Check on resize
            window.addEventListener('resize', checkScreenSize);

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

            // Attach the click event listener to each 'groups-link' element
            groupsLink.forEach(link => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    yearSelectionModal.style.display = 'flex';
                });
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
