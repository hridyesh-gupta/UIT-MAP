<?php
// Start PHP session to track logged-in users
session_start();

// Suppress error reporting (errors won't be displayed to users)
error_reporting(0);

// Verify user is logged in - if not, redirect to login page
if(!(isset($_SESSION['username']))){
    header("location: index.php");
}

// Verify user is an admin - if not, redirect to login page
// This prevents mentors or students from accessing this page
elseif($_SESSION['usertype']!="admin"){
    header("location: index.php");
}

// Include database connection file to access $conn variable
include 'dbconnect.php';

// Check if form was submitted via POST method and contains update_dates flag
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_dates'])) {
    
    // Initialize validation flag
    $validationError = false;

    // Retrieve and validate the batch year from the form submission
    $batchYearRaw = isset($_POST['batchYear']) ? $_POST['batchYear'] : null;
    $batchYear = filter_var($batchYearRaw, FILTER_VALIDATE_INT);
    if ($batchYear === false) {
        $updateError = "Invalid batch year provided.";
        $validationError = true;
    }
    
    // Retrieve all rubric deadline dates from the form (may be empty if admin didn't fill them)
    $lastR1 = isset($_POST['lastR1']) ? trim($_POST['lastR1']) : '';  // Rubric 1 deadline
    $lastR2 = isset($_POST['lastR2']) ? trim($_POST['lastR2']) : '';  // Rubric 2 deadline
    $lastR3 = isset($_POST['lastR3']) ? trim($_POST['lastR3']) : '';  // Rubric 3 deadline
    $lastR4 = isset($_POST['lastR4']) ? trim($_POST['lastR4']) : '';  // Rubric 4 deadline
    $lastR5 = isset($_POST['lastR5']) ? trim($_POST['lastR5']) : '';  // Rubric 5 deadline
    $lastR6 = isset($_POST['lastR6']) ? trim($_POST['lastR6']) : '';  // Rubric 6 deadline
    $lastR7 = isset($_POST['lastR7']) ? trim($_POST['lastR7']) : '';  // Rubric 7 deadline
    $lastR8 = isset($_POST['lastR8']) ? trim($_POST['lastR8']) : '';  // Rubric 8 deadline
    
    // Validate that all submitted dates fall within the batch year range
    // Academic year: (batchYear - 4) to batchYear (e.g., 2021-2025)
    if (!$validationError) {
        $minYear = $batchYear - 4;  // Start of academic year
        $maxYear = $batchYear;       // End of academic year
        
        // Array of all date fields to validate
        $dates = [
            'Rubric 1' => $lastR1, 'Rubric 2' => $lastR2, 'Rubric 3' => $lastR3, 'Rubric 4' => $lastR4,
            'Rubric 5' => $lastR5, 'Rubric 6' => $lastR6, 'Rubric 7' => $lastR7, 'Rubric 8' => $lastR8
        ];
        
        // Check each date that is not empty
        foreach($dates as $rubricName => $dateValue) {
            if(!empty($dateValue)) {
                // Ensure date is in the correct format (YYYY-MM-DD)
                $dateTime = DateTime::createFromFormat('Y-m-d', $dateValue);
                $dateErrors = DateTime::getLastErrors();
                if ($dateTime === false || $dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0) {
                    $updateError = "$rubricName deadline must be a valid date in YYYY-MM-DD format.";
                    $validationError = true;
                    break;  // Stop checking once we find an invalid date
                }

                // Extract year from the validated date
                $dateYear = (int)$dateTime->format('Y');
                
                // Check if date year is within valid range
                if($dateYear < $minYear || $dateYear > $maxYear) {
                    $updateError = "$rubricName deadline must be between $minYear and $maxYear (batch year range).";
                    $validationError = true;
                    break;  // Stop checking once we find an invalid date
                }
            }
        }
    }
    
    // Only proceed with database update if validation passed
    if(!$validationError) {
        // Prepare SQL UPDATE query to update dates in batches table
        // Uses prepared statement (?) to prevent SQL injection attacks
        $updateQuery = "UPDATE batches SET lastR1=?, lastR2=?, lastR3=?, lastR4=?, lastR5=?, lastR6=?, lastR7=?, lastR8=? WHERE batchyr=?";
        
        // Create a prepared statement from the query
        $stmt = $conn->prepare($updateQuery);
        
        // Bind the date values and batch year to the prepared statement
        // "ssssssssi" = 8 strings (dates) and 1 integer (batch year)
        $stmt->bind_param("ssssssssi", $lastR1, $lastR2, $lastR3, $lastR4, $lastR5, $lastR6, $lastR7, $lastR8, $batchYear);
        
        // Execute the prepared statement and check if update was successful
        if($stmt->execute()) {
            // Success! Set success message to display to admin
            $updateSuccess = "Dates updated successfully for batch " . ($batchYear - 4) . "-" . $batchYear;
        } else {
            // Error occurred during update - set error message to display
            $updateError = "Error updating dates: " . $conn->error;
        }
        
        // Close the prepared statement to free up resources
        $stmt->close();
    }
}

// Query to get all distinct (unique) batch years from batches table
// ORDER BY ensures years are displayed in ascending order (oldest to newest)
$yearsQuery = "SELECT DISTINCT batchyr FROM batches ORDER BY batchyr ASC";
$yearsResult = $conn->query($yearsQuery);

// Initialize empty array to store years
$years = [];

// Loop through query results and populate years array
if ($yearsResult->num_rows > 0) {
    while ($row = $yearsResult->fetch_assoc()) {
        $years[] = $row['batchyr'];  // Add each batch year to array
    }
}

// Priority order: POST data > GET parameter > first year in database > null
// This handles multiple ways admin can select a year:
// 1. Submit form with year button (POST)
// 2. Click year link from another page (GET)
// 3. Default to first year if neither above applies
$currentDates = null;
$selectedYear = isset($_POST['batchYear']) ? $_POST['batchYear'] : (isset($_GET['year']) ? $_GET['year'] : (count($years) > 0 ? $years[0] : null));

// If a year is selected, retrieve all deadline dates for that batch year
if($selectedYear) {
    // Prepare query to fetch all 8 rubric deadline dates for selected batch year
    $dateQuery = "SELECT lastR1, lastR2, lastR3, lastR4, lastR5, lastR6, lastR7, lastR8 FROM batches WHERE batchyr = ?";
    
    // Create prepared statement to prevent SQL injection
    $stmt = $conn->prepare($dateQuery);
    
    // Bind the selected year to the query (cast to integer for safety)
    $stmt->bind_param("i", $selectedYear);
    
    // Execute the query
    $stmt->execute();
    
    // Get the result set
    $result = $stmt->get_result();
    
    // If records found, fetch and store the dates
    if($result->num_rows > 0) {
        $currentDates = $result->fetch_assoc();  // Associative array with all date fields
    }
    
    // Close the prepared statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Manage Dates</title>
    <!-- Tailwind CSS framework for styling and responsive design -->
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
    <style>
        /* Styling for date input fields */
        .date-field {
            border: 1px solid #cbd5e0;
            border-radius: 0.375rem;
            padding: 0.5rem;
            font-size: 1rem;
        }
        
        /* Focus state for date fields - highlights field when clicked */
        .date-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Styling for rubric labels */
        .rubric-label {
            font-weight: 600;
            color: #1f2937;
        }
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<!-- Include admin navigation header from adminheaders.php -->
<?php include 'adminheaders.php' ?>

    <!-- Main content area that grows to fill available space -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold mb-6">Project Deadline Dates Management</h2>

            <!-- Display success message if date update was successful -->
            <?php if(isset($updateSuccess)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($updateSuccess, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            
            <!-- Display error message if date update failed -->
            <?php if(isset($updateError)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($updateError, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

                <!-- Allow admin to select which batch year to manage -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <label class="block text-lg font-semibold mb-4">Select Batch Year:</label>
                <form method="POST" id="yearForm">
                    <div class="flex flex-wrap gap-2 mb-6">
                        <!-- Loop through all available years and create clickable buttons -->
                        <?php foreach($years as $year): ?>
                            <!-- Highlight button if it's currently selected -->
                            <button 
                                type="submit"
                                name="batchYear"
                                value="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?>"
                                class="px-4 py-2 rounded transition-colors <?php echo ($selectedYear == $year) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'; ?>">
                                <!-- Display year range (e.g., "2021-2025" for batchyr 2025) -->
                                <?php echo htmlspecialchars(($year - 4) . " - " . $year, ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

            <!-- Only show form if dates exist for selected year -->
            <?php if($currentDates): ?>
            <?php 
                // Calculate date range for validation (academic year range)
                $minYear = $selectedYear - 4;  // Start year of academic cycle
                $maxYear = $selectedYear;       // End year (graduation year)
                $minDate = $minYear . "-01-01"; // Earliest allowed date
                $maxDate = $maxYear . "-12-31"; // Latest allowed date
            ?>
            <form method="POST" class="bg-white rounded-lg shadow p-6" id="dateForm">
                <!-- Hidden fields to track which dates to update -->
                <input type="hidden" name="update_dates" value="1">
                <input type="hidden" name="batchYear" value="<?php echo htmlspecialchars((string)$selectedYear, ENT_QUOTES, 'UTF-8'); ?>">
                
                <!-- Display date range info -->
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4">
                    <strong>Note:</strong> All dates must be within the academic year range: <?php echo htmlspecialchars((string)$minYear, ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars((string)$maxYear, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                
                <!-- Grid layout for date input fields (2 columns on medium+ screens, 1 column on mobile) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Rubric 1 Deadline:</label>
                        <!-- Date input field - pre-filled with existing date from database -->
                        <input type="date" name="lastR1" value="<?php echo $currentDates['lastR1']; ?>" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>" class="w-full date-field">
                        <p class="text-xs text-gray-500 mt-1">Final submission deadline for Rubric 1</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Rubric 2 Deadline:</label>
                        <input type="date" name="lastR2" value="<?php echo $currentDates['lastR2']; ?>" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>" class="w-full date-field">
                        <p class="text-xs text-gray-500 mt-1">Final submission deadline for Rubric 2</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Rubric 3 Deadline:</label>
                        <input type="date" name="lastR3" value="<?php echo $currentDates['lastR3']; ?>" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>" class="w-full date-field">
                        <p class="text-xs text-gray-500 mt-1">Final submission deadline for Rubric 3</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Rubric 4 Deadline:</label>
                        <input type="date" name="lastR4" value="<?php echo $currentDates['lastR4']; ?>" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>" class="w-full date-field">
                        <p class="text-xs text-gray-500 mt-1">Final submission deadline for Rubric 4</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Rubric 5 Deadline:</label>
                        <input type="date" name="lastR5" value="<?php echo $currentDates['lastR5']; ?>" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>" class="w-full date-field">
                        <p class="text-xs text-gray-500 mt-1">Final submission deadline for Rubric 5</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Rubric 6 Deadline:</label>
                        <input type="date" name="lastR6" value="<?php echo $currentDates['lastR6']; ?>" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>" class="w-full date-field">
                        <p class="text-xs text-gray-500 mt-1">Final submission deadline for Rubric 6</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Rubric 7 Deadline:</label>
                        <input type="date" name="lastR7" value="<?php echo $currentDates['lastR7']; ?>" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>" class="w-full date-field">
                        <p class="text-xs text-gray-500 mt-1">Final submission deadline for Rubric 7</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Rubric 8 Deadline:</label>
                        <input type="date" name="lastR8" value="<?php echo $currentDates['lastR8']; ?>" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>" class="w-full date-field">
                        <p class="text-xs text-gray-500 mt-1">Final submission deadline for Rubric 8</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <!-- Submit button to save all date changes to database -->
                    <button type="submit" class="bg-green-500 text-white py-2 px-6 rounded-lg hover:bg-green-600 transition duration-300">
                        Save Changes
                    </button>
                    
                    <!-- Cancel button to go back to admin home without saving -->
                    <button type="button" class="bg-gray-500 text-white py-2 px-6 rounded-lg hover:bg-gray-600 transition duration-300" onclick="location.href='adminhome.php'">
                        Cancel
                    </button>
                </div>
            </form>
            <!-- Show warning if no dates exist for selected year -->
            <?php else: ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    Please select a batch year to manage dates.
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Include footer from footer.php -->
    <?php include 'footer.php' ?>

    <script>
        // Get references to UI elements
        const user = document.getElementById('user');          // User display element
        const baldiv = document.getElementById('balancingdiv'); // Spacing element
        const dateForm = document.getElementById('dateForm');   // Date management form
        
        // Check screen size when page loads
        checkScreenSize();

        // Re-check screen size whenever window is resized
        window.addEventListener('resize', checkScreenSize);

        /**
         * Function: checkScreenSize
         * Purpose: Show/hide elements based on screen width for responsive design
         * - Screens >= 768px (medium): Show user info, hide balancing div
         * - Screens < 768px (small): Hide user info (shown in mobile menu)
         */
        function checkScreenSize() {
            if (window.innerWidth >= 768) {
                // Large screens: show user element
                user.classList.remove('hidden');
                baldiv.classList.add('hidden');
            } else {
                // Small screens: hide user element to save space
                user.classList.add('hidden');
                baldiv.classList.remove('hidden');
            }
        }

        /**
         * Client-side form validation before submission
         * Validates that all date inputs follow HTML5 constraints (min/max attributes)
         */
        if (dateForm) {
            dateForm.addEventListener('submit', function(e) {
                const dateInputs = dateForm.querySelectorAll('input[type="date"]');
                let isValid = true;
                let errorMessage = '';

                // Check each date input field
                dateInputs.forEach(function(input) {
                    // Only validate if the field has a value
                    if (input.value) {
                        const inputDate = new Date(input.value);
                        const minDate = new Date(input.min);
                        const maxDate = new Date(input.max);

                        // Check if date is within allowed range
                        if (inputDate < minDate || inputDate > maxDate) {
                            isValid = false;
                            const fieldName = input.name.replace('last', 'Rubric ').replace('R', ' ');
                            errorMessage = `${fieldName} deadline must be between ${input.min} and ${input.max}`;
                            input.style.borderColor = 'red';  // Highlight invalid field
                        } else {
                            input.style.borderColor = '';  // Reset border if valid
                        }
                    }
                });

                // If validation failed, prevent form submission and show error
                if (!isValid) {
                    e.preventDefault();
                    alert(errorMessage);
                    return false;
                }
            });
        }
    </script>
</body>
</html>