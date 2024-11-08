<!-- Header --> 
<header class="bg-blue-600 text-white p-4">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <img src="COLLEGE.png" alt="College Logo" class="h-12">
        <h1 class="text-3xl font-bold text-center">MAP - Student Panel</h1>
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
        <a href="studentdetails.php" class="text-lg">Student Details</a>
        <a href="guidelines.php" class="text-lg">Guidelines</a>
        <a href="details.php" class="text-lg">Group Details</a>
        <a href="status.php" class="text-lg">Weekly Analysis</a>
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
        <a href="status.php" class="text-lg">Weekly Analysis</a>
        <a href="rubrics.php" class="text-lg">Rubrics</a>
        <a href="logout.php" class="text-lg">Logout</a>
    </div>
</nav>

<!-- JavaScript -->
<script>
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const desktopMenu = document.getElementById('desktop-menu');

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
</script>