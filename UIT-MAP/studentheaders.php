<?php
include 'dbconnect.php'; //Database connection
error_reporting(0); //To hide the errors
// Fetch the logged-in user's name
$username = $_SESSION['username'];
$nameQuery = "SELECT name FROM info WHERE username = '$username' LIMIT 1";
$nameResult = $conn->query($nameQuery);
$loggedInName = $nameResult->num_rows > 0 ? $nameResult->fetch_assoc()['name'] : 'User';
?>
<!-- Header --> 
<header class="bg-blue-600 text-white p-4">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <img src="img/COLLEGE.png" alt="College Logo" class="h-12">
        <div class="flex-1 text-center">
            <h1 class="text-3xl font-bold text-center">MAP - Student Panel</h1>        
        </div>
        <div id="balancingdiv"></div><!-- Div to maintain the space in mobile view between right side boundary and h1 when the user name is hidden -->
        <!-- Logged-in User -->
        <div id="user" class="absolute right-8 top-7 flex items-center space-x-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM6 21v-2a4 4 0 014-4h4a4 4 0 014 4v2" />
            </svg>
            <span class="text-lg font-medium"><?php echo htmlspecialchars($loggedInName); ?></span>
        </div>
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
        <a href="studentdetails.php" class="text-lg">Student Details</a>
        <a href="guidelines.php" class="text-lg">Guidelines</a>
        <a href="details.php" class="text-lg">Group Details</a>
        <a href="weekanalysis.php" class="text-lg">Weekly Analysis</a>
        <a href="rubrics.php" class="text-lg">Rubrics</a>
        <a href="logout.php" class="text-lg">Logout</a>
    </div>
</nav>

<!-- Sub-header for small screens -->
<nav id="mobile-menu" class="bg-blue-500 text-white hidden">
    <div class="max-w-6xl mx-auto p-4 flex flex-col space-y-4">
        <a href="studentdetails.php" class="text-lg">Student Details</a>
        <a href="guidelines.php" class="text-lg">Guidelines</a>
        <a href="details.php" class="text-lg">Group Details</a>
        <a href="weekanalysis.php" class="text-lg">Weekly Analysis</a>
        <a href="rubrics.php" class="text-lg">Rubrics</a>
        <a href="logout.php" class="text-lg">Logout</a>
    </div>
</nav>

<!-- JavaScript -->
<script>
    const menuToggle = document.getElementById('menu-toggle');
    const user = document.getElementById('user');
    const baldiv = document.getElementById('balancingdiv');
    const mobileMenu = document.getElementById('mobile-menu');
    const desktopMenu = document.getElementById('desktop-menu');

    function checkScreenSize() {
        if (window.innerWidth >= 768) {//If screen if of large size
            desktopMenu.classList.remove('hidden');//Show desktop menu
            user.classList.remove('hidden');//Show user
            baldiv.classList.add('hidden');//Hide balancing div
            mobileMenu.classList.add('hidden');//Hide mobile menu
            menuToggle.classList.add('hidden');//Hide hamburger icon
        } else {//If screen if of small size
            desktopMenu.classList.add('hidden');//Hide desktop menu
            user.classList.add('hidden');//Hide user
            menuToggle.classList.remove('hidden');//Show hamburger icon
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
</script>