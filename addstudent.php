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

// Check if the form is submitted 
if (isset($_POST['upload_file']) && isset($_FILES['uploaded_file'])) { //When the button is clicked and also the file has been uploaded then only this statement will be executed
    // Check for errors in the uploaded file 
    if ($_FILES['uploaded_file']['error'] == 0) { 
        // Successfully uploaded file with no errors 
        
        // Open the uploaded CSV file in read mode 
        $csvFile = fopen($_FILES['uploaded_file']['tmp_name'], 'r');
        
        // It will skip the first line of the CSV if it contains column names as we're not assigning the values of the header line(first line) to any variable
        fgetcsv($csvFile); 
        
        // Prepare the SQL statement for inserting data into the user table
        $stmtUser = $conn->prepare("INSERT INTO user (username, usertype, password) VALUES (?, 'student', ?)");        
        //If you just want to insert some more columns to the data which are already there in the database then the previous query will throw duplicate key error(i.e. this primary key already exist) so use this query but with caution that this will take too much time: 
        //$stmtUser = $conn->prepare("INSERT INTO user (username, usertype, password) VALUES (?, 'student', ?) ON DUPLICATE KEY UPDATE password = VALUES(password)"); 

        // Prepare the SQL statement for inserting data into the info table
        $stmtInfo = $conn->prepare("INSERT INTO info (username, name, section, batchyr, roll, branch, dob, contact, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        //If you just want to insert some more new columns to the data which are already there in the database then the previous query will throw duplicate key error(i.e. this primary key already exist) so use this query but with caution that this will take too much time: 
        //$stmtInfo = $conn->prepare("INSERT INTO info (username, name, section, batchyr, roll, branch, dob, contact, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), section = VALUES(section), batchyr = VALUES(batchyr), branch = VALUES(branch), dob = VALUES(dob), contact = VALUES(contact), email = VALUES(email)"); 
        
        // Continuing from second line of the CSV file
        // Loop through each row of the CSV file 
        while (($row = fgetcsv($csvFile)) !== FALSE) { 
            if (!empty($row[0])) {
            // Bind the data from the CSV row to the SQL query parameters for user table
            // Assuming roll is at index 0 and password is at index 4 in the CSV
            $stmtUser->bind_param("ss", $row[0], $row[4]); 
            // Execute the SQL query to insert the data into user table
            $stmtUser->execute(); 
            // Bind the data from the CSV row to the SQL query parameters for info table
            // Assuming roll is at index 0, name is at index 1, section is at index 2, batchyr is at index 3 in the CSV and so on
            $stmtInfo->bind_param("sssssssss", $row[0], $row[1], $row[2], $row[3], $row[0], $row[5], $row[6], $row[7], $row[8]); 
            // Execute the SQL query to insert the data into info table
            $stmtInfo->execute(); 

            // Increment the counter
            $studentsAdded++;
            }
        } 
        
        // Clean up: Close the CSV file, the prepared statements, and the database connection 
        fclose($csvFile); 
        $stmtUser->close(); 
        $stmtInfo->close();
        $conn->close(); 
        if($studentsAdded == 0) {
            // JavaScript alert for no students added
            echo '<script type="text/javascript">
                    alert("No students added.");
                  </script>';
        }
        else if ($studentsAdded == 1) {
            // JavaScript alert for success
            echo '<script type="text/javascript">
                    alert("' . $studentsAdded . ' student added successfully.");
                    window.location.href = "addstudent.php";
                  </script>';
        }
        else {
            // JavaScript alert for success
            echo '<script type="text/javascript">
                    alert("' . $studentsAdded . ' students added successfully.");
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
