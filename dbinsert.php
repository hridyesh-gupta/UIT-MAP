<?php
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
        $stmtInfo = $conn->prepare("INSERT INTO info (username, name, section, batch, roll, branch, dob, contact, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        //If you just want to insert some more columns to the data which are already there in the database then the previous query will throw duplicate key error(i.e. this primary key already exist) so use this query but with caution that this will take too much time: 
        //$stmtInfo = $conn->prepare("INSERT INTO info (username, name, section, batch, roll, branch, dob, contact, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), section = VALUES(section), batch = VALUES(batch), branch = VALUES(branch), dob = VALUES(dob), contact = VALUES(contact), email = VALUES(email)"); 
        
        // Continuing from second line of the CSV file
        // Loop through each row of the CSV file 
        while (($row = fgetcsv($csvFile)) !== FALSE) { 
            // Bind the data from the CSV row to the SQL query parameters for user table
            if (!empty($row[0])) {
            // Assuming roll is at index 0 and password is at index 4 in the CSV
            $stmtUser->bind_param("ss", $row[0], $row[4]); 
            // Execute the SQL query to insert the data into user table
            $stmtUser->execute(); 
            }            
            // Bind the data from the CSV row to the SQL query parameters for info table
            if (!empty($row[0])) {
            // Assuming roll is at index 0, name is at index 1, section is at index 2, batch is at index 3 in the CSV and so on
            $stmtInfo->bind_param("sssssssss", $row[0], $row[1], $row[2], $row[3], $row[0], $row[5], $row[6], $row[7], $row[8]); 
            // Execute the SQL query to insert the data into info table
            $stmtInfo->execute(); 
            }
        } 
        
        // Clean up: Close the CSV file, the prepared statements, and the database connection 
        fclose($csvFile); 
        $stmtUser->close(); 
        $stmtInfo->close();
        $conn->close(); 
        
        // Notify the user of successful data import 
        echo 'Data imported successfully.';

        // Insert a button to go back to addstudent.php
        echo '<button onclick="window.location.href=\'addstudent.php\'">Go Back</button>';
    } 
    else { 
        // Handle file upload error 
        echo 'Error uploading file.'; 
        echo '<button onclick="window.location.href=\'addstudent.php\'">Go Back</button>';
    } 
} else { 
    // Handle case where the form hasn't been submitted 
    echo 'Form not submitted.'; 
    echo '<button onclick="window.location.href=\'addstudent.php\'">Go Back</button>';
} 
?>