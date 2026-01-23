<!-- Admin 1st page -->
<?php
session_start();
error_reporting(0); //To hide the errors
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin"){ //If the user is not admin, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
    header("location: index.php");
}
//PHP code to insert the data from the CSV file into the database
include 'dbconnect.php'; //Include the database connection file

// It Tracks how many new students were successfully added
$studentsAdded = 0;

// It Tracks how many duplicate students were updated instead of being rejected
$studentsDuplicate = 0;

// Check if the form is submitted 
if (isset($_POST['upload_file']) && isset($_FILES['uploaded_file'])) { //When the button is clicked and also the file has been uploaded then only this statement will be executed
    // Check for errors in the uploaded file 
    if ($_FILES['uploaded_file']['error'] == 0) { 
        // Successfully uploaded file with no errors 
        
        // Open the uploaded CSV file in read mode 
        $csvFile = fopen($_FILES['uploaded_file']['tmp_name'], 'r');
        
        // It will skip the first line of the CSV if it contains column names
        fgetcsv($csvFile); 
        
        // If student username already exists, update their password instead of crashing , this allows existing students to have updated credentials
        $stmtUser = $conn->prepare("INSERT INTO user (username, usertype, password) VALUES (?, 'student', ?) ON DUPLICATE KEY UPDATE password = VALUES(password)");        
        
        // If student record already exists, update their information (name, email, contact, etc.) ,this ensures old records get refreshed with latest data from CSV instead of causing errors or page crashed
        $stmtInfo = $conn->prepare("INSERT INTO info (username, name, section, batchyr, roll, branch, dob, contact, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), section = VALUES(section), batchyr = VALUES(batchyr), branch = VALUES(branch), dob = VALUES(dob), contact = VALUES(contact), email = VALUES(email)"); 
        
        // Loop through each row of the CSV file 
        while (($row = fgetcsv($csvFile)) !== FALSE) { 
            if (!empty($row[0])) {
                // Try to add the student's login credentials (username and password)
                $stmtUser->bind_param("ss", $row[0], $row[4]); 
                if ($stmtUser->execute()) {
                    // Also add or update the student's personal information in the info table
                    $stmtInfo->bind_param("sssssssss", $row[0], $row[1], $row[2], $row[3], $row[0], $row[5], $row[6], $row[7], $row[8]); 
                    if ($stmtInfo->execute()) {
                        // Check if this was a NEW student added (1 row affected) or an EXISTING student updated (2 rows affected)
                        
                        if ($conn->affected_rows == 1) { // affected_rows = 1 means INSERT happened (new student)
                            $studentsAdded++; // Count as new addition

                        } else if ($conn->affected_rows == 2) { // affected_rows = 2 means UPDATE happened (duplicate student, info refreshed)
                            $studentsDuplicate++; // Count as duplicate 
                        }
                    }
                }
            }
        } 
        // Clean up: Closing the CSV file, the prepared statements, and the database connection 
        fclose($csvFile); 
        $stmtUser->close(); 
        $stmtInfo->close();
        $conn->close(); 
        // Checking if anything was actually processed from the CSV file
        if($studentsAdded == 0 && $studentsDuplicate == 0) {
            // The CSV was likely empty or had no valid entries
            echo '<script type="text/javascript">
                    alert("No students added. Please check the CSV file.");
                  </script>';
        }
        else {
            // Build a friendly message showing what happened during the upload
            $summary = "";
            
            // If there were new students added, mention that
            if ($studentsAdded > 0) {
                $summary .= $studentsAdded . " new student" . ($studentsAdded > 1 ? "s" : "") . " added successfully.";
            }
            
            // If there were duplicates that got updated, mention that too
            if ($studentsDuplicate > 0) {
                if ($summary) $summary .= " "; // Add space between messages if both exist
                $summary .= $studentsDuplicate . " duplicate student" . ($studentsDuplicate > 1 ? "s" : "") . " updated with new information.";
            }
            
            // Show the final summary to the admin
            echo '<script type="text/javascript">
                    alert("' . $summary . '");
                    window.location.href = "addstudent.php";
                  </script>';
        }
    } 
    else { 
        // JavaScript alert for file upload error
        echo '<script type="text/javascript">
                alert("Error uploading file. Please recheck the uploaded file.");
              </script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Add Students</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php include 'adminheaders.php' ?>
    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <center><h2 class="text-2xl font-bold mb-6">Add Students</h2></center>

            <div id="form-container" class="mt-8">
                <div id="file-upload-form" class="bg-white p-6 rounded-lg shadow-lg">
                    <form action="addstudent.php" method="POST" enctype="multipart/form-data">
                        <label class="block mb-2">Upload File:</label>
                        <input type="file" name="uploaded_file" class="w-full p-2 border rounded mb-4" required>
                        <button type="submit" name="upload_file" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition duration-300">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php' ?>
</body>
</html>