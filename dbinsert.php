<?php
//PHP code to insert the data from the CSV file into the database

// Check if the form is submitted 
if (isset($_POST['upload_file']) && isset($_FILES['uploaded_file'])) { //When the button is clicked and also the file has been uploaded then only this statement will be executed
    // Check for errors in the uploaded file 
    if ($_FILES['uploaded_file']['error'] == 0) { 
        // Successfully uploaded file with no errors 
        
        // Open the uploaded CSV file in read mode 
        $csvFile = fopen($_FILES['uploaded_file']['tmp_name'], 'r');
        
        // Establish database connection using mysqli 
        $db = new mysqli('localhost', 'root', '', 'mapdb'); 
               
        // Check for connection errors 
        if ($db->connect_error) { 
            die("Connection failed: " . $db->connect_error); 
        } 
        
        // It will skip the first line of the CSV if it contains column names as we're not assigning the values of the header line(first line) to any variable
        fgetcsv($csvFile); 
        
        // Prepare the SQL statement for inserting data into the user table
        $stmtUser = $db->prepare("INSERT INTO user (username, usertype, password) VALUES (?, 'student', ?)");        
        // Prepare the SQL statement for inserting data into the info table
        $stmtInfo = $db->prepare("INSERT INTO info (username, name, section, batch, roll, branch) VALUES (?, ?, ?, ?, ?, ?)"); 
        
        // Continuing from second line of the CSV file
        // Loop through each row of the CSV file 
        while (($row = fgetcsv($csvFile)) !== FALSE) { 
            // Bind the data from the CSV row to the SQL query parameters for user table
            // Assuming roll is at index 0 and pass is at index 4 in the CSV
            $stmtUser->bind_param("ss", $row[0], $row[4]); 
            
            // Execute the SQL query to insert the data into user table
            $stmtUser->execute(); 
            
            // Bind the data from the CSV row to the SQL query parameters for info table
            // Assuming roll is at index 0, name is at index 1, sec is at index 2, batch is at index 3 in the CSV
            $stmtInfo->bind_param("ssssss", $row[0], $row[1], $row[2], $row[3], $row[0], $row[5]); 
            
            // Execute the SQL query to insert the data into info table
            $stmtInfo->execute(); 
        } 
        
        // Clean up: Close the CSV file, the prepared statements, and the database connection 
        fclose($csvFile); 
        $stmtUser->close(); 
        $stmtInfo->close();
        $db->close(); 
        
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