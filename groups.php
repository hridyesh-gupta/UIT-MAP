<?php
// Admin 2nd page
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin"){ //If the user is not admin, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
    header("location: index.php");
}

include 'dbconnect.php'; //Database connection

// To fetch mentors from the mentors table
$sql = "SELECT mname FROM mentors ORDER BY mname ASC";
$result = $conn->query($sql);

$mentors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mentors[] = $row['mname'];//mname is the column name in the mentors table
    }
}

//To fetch the group details from the projinfo table
$groupExists=false;
$sql = "SELECT * FROM projinfo"; 
$groupResults = $conn->query($sql); //Executing the query
$groupRows = [];
if($groupResults->num_rows > 0){ //If there are groups in the projinfo table
    $groupExists=true;
    while($groupRow = $groupResults->fetch_assoc()){ //Fetching the group details
        $groupRows[] = $groupRow;
    }
}

//To handle the incoming POST request and check if the request is to change the mentor or delete the group
if($_SERVER['REQUEST_METHOD'] === 'POST'){ //If the request method is POST
    $data = json_decode(file_get_contents('php://input'), true); //Decode the JSON payload sent from the client side
    $action = $data['action']; //Get the action from the decoded JSON payload
    $gnum = $data['gnum'];
    if ($action === 'change') {
        $mentor = $data['mentor']; // Get the selected mentor

        $mIdQuery="SELECT mid FROM mentors where mname='$mentor'";//Get mentor ID of the selected mentor
        $mIdResults=$conn->query($mIdQuery);
        $mId= $mIdResults->fetch_assoc()['mid'];//As mid is the name of the column in the mentors table whose value is stored in the $mIdResults variable

        // Update the mentor, its Id and DEC approval date for the group in the 'projinfo' table using gnum. DEC approval date also bcoz at the time when DEC allotted mentor it also approved the grp.
        $updateMentor = "UPDATE projinfo SET mentor = '$mentor', mid = '$mId', dAppDate = CURDATE() WHERE gnum = '$gnum'";
        $stmt = $conn->query($updateMentor);
        // $stmt->bind_param("ss", $mentor, $gnum);
        // $stmt->execute();

        // if ($stmt->affected_rows > 0) {
        //     header('Content-Type: text/plain');
        //     echo 'success=true';
        // } 
        echo 'success=true';

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
    else if($action === 'delete'){
        // Delete group members from 'groups' table
        $deleteGroupMembers = "DELETE FROM groups WHERE gnum = '$gnum'";
        $stmt1 = $conn->query($deleteGroupMembers);//Execute the query
        // $stmt1->bind_param("s", $gnum);
        // $stmt1->execute();

        // Delete group info from 'projinfo' table
        $deleteGroupInfo = "DELETE FROM projinfo WHERE gnum = '$gnum'";
        $stmt2 = $conn->query($deleteGroupInfo);
        // $stmt2->bind_param("s", $gnum);
        // $stmt2->execute();
        
        // Check if both queries were successful
        // if ($stmt1->affected_rows > 0 && $stmt2->affected_rows > 0) {
        //     header('Content-Type: text/plain');
        //     echo 'success=true';
        // } 
        echo 'success=true';
        // Close the prepared statements and connection
        $stmt1->close();
        $stmt2->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - View Groups</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php 
if($_SESSION['usertype'] == "admin"){ //If the user is admin show the admin header
    include 'adminheaders.php';
}
elseif($_SESSION['usertype'] == "student"){ //If the user is student show the student header
    include 'studentheaders.php';
}
elseif($_SESSION['usertype'] == "mentor"){ //If the user is mentor show the mentor header
    include 'mentorheaders.php';
} 
?>
    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <center><h2 class="text-2xl font-bold mb-6">View Groups</h2></center>

            <!-- Filter Box -->
            <div class="mb-6 flex justify-between items-center">
                <input type="text" id="searchInput" placeholder="Search for groups by Project Name..." class="w-full p-2 border rounded">
                    <label class="ml-4 flex items-center">
                        <input type="checkbox" id="showApprovedCheckbox" class="mr-2">
                        <span>Show Approved Groups</span>
                    </label>
            </div>

            <!-- Group List -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <table class="min-w-full bg-white table-fixed">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-center w-16">Group ID</th>
                            <th class="px-4 py-2 text-center w-48">Project Title</th>
                            <th class="px-4 py-2 text-center w-48">Technology Used</th>
                            <th class="px-4 py-2 text-center w-56">Mentor Assigned</th>
                            <th class="px-4 py-2 text-center w-16">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="groupTable" >
                        <!-- Rows will be dynamically added here by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const groupRows = <?php echo json_encode($groupRows); ?>;
        const mentors = <?php echo json_encode($mentors); ?>;

        // Populate the table when the page loads
        document.addEventListener('DOMContentLoaded', populateTable);
         
        // Function to dynamically populate the table with mentors and group data
        function populateTable() {
            const groupTable = document.getElementById('groupTable');
            groupTable.innerHTML = ''; // Clear existing rows

            groupRows.forEach((group, index) => {
                // Create a new row
                const row = document.createElement('tr');
                row.classList.add('group-item');
                row.setAttribute('data-approved', group.approved ? 'true' : 'false');

                // Populate row with group data
                row.innerHTML = `
                    <td class="border px-4 py-2 text-center">${group.number}</td>
                    <td class="border px-4 py-2 text-center">${group.title}</td>
                    <td class="border px-4 py-2 text-center">${group.tech}</td>
                    <td class="border px-4 py-2 text-center">
                        <select class="p-2 border rounded">
                            <option value="">Select mentor...</option>
                            ${mentors.map(mentor => `
                                <option value="${mentor}" ${group.mentor === mentor ? 'selected' : ''}>${mentor}</option>
                            `).join('')}
                            // studentRolls.map(...): This returns an array of option HTML strings, where each element is an option tag for a student roll no. & join(''): This method is used to concatenate (join) all these strings together without any separator (since '' is an empty string).
                        </select>
                        <button onclick="changeMentor(this)" class="bg-green-500 text-white py-1 px-3 rounded hover:bg-green-800 transition duration-300">Change</button>
                    </td>
                    <td class="border px-4 py-2 text-center">
                        <button onclick="deleteGroup(this)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-800 transition duration-300">Delete</button>
                    </td>
                `;
                // Append the row to the table
                groupTable.appendChild(row);
            });
        }

        document.getElementById('showApprovedCheckbox').addEventListener('change', function() {
            const rows = document.querySelectorAll('.group-item');
            rows.forEach(row => {
                if (this.checked) {
                    if (row.getAttribute('data-approved') === 'true') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    row.style.display = '';
                }
            });
        });

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.group-item');
            rows.forEach(row => {
                const projectName = row.cells[1].textContent.toLowerCase();
                if (projectName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        //To provide the functionality to change mentor button to change the mentor of the group
        function changeMentor(button) {
            const row = button.closest('tr'); // Get the row containing the button, it looks for the closest tr element to the button
            const groupId = row.cells[0].textContent; // Get the group ID from the first cell of the row
            const gnum = groupRows.find(group => group.number == groupId).gnum; // Get the unique gnum from groupRows, it searches through the groupRows array to find the group whose number(is in groupRows) matches the groupId from the table row. Once found, .gnum retrieves the unique identifier (gnum) of that group.
            const mentorDropdown = row.querySelector('select'); // First it selects the dropdown element in the row
            const selectedMentor = mentorDropdown.value; // Then extracts the value of that dropdown element

            const confirmChange = confirm(`Are you sure you want to change mentor for Group ID ${groupId}?`);
            if (confirmChange) {
                // Send an AJAX request to change the mentor of the group
                fetch('groups.php', {
                    method: 'POST',//It indicates that the request is a POST request
                    headers: {//It indicates that the request contains which type of data
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({//It sends the data to the server in the form of JSON
                        gnum: gnum,         // Unique group number
                        mentor: selectedMentor, // Selected mentor
                        action: 'change'    // Action type
                    }), // Send gnum, mentor, and action as JSON payload
                })
                .then(response => response.text())
                .then(text => {
                    // if (data.success) {
                    alert('Mentor changed successfully');
                    window.location.reload();
                    // } else {
                    //     alert('Error changing mentor: ' + data.message);
                    // }
                })
                .catch(error => console.error('Error:', error));
            }
        }


        //To provide the functionality to delete button to delete the grp members and proj info from the database
        function deleteGroup(button) {
                const row = button.closest('tr'); // Get the row containing the button, it looks for the closest tr element to the button
                const groupId = row.cells[0].textContent; // Get the group ID from the first cell of the row
                const gnum = groupRows.find(group => group.number == groupId).gnum; // Get the unique gnum from groupRows, it searches through the groupRows array to find the group whose number(is in groupRows) matches the groupId from the table row. Once found, .gnum retrieves the unique identifier (gnum) of that group.

                const confirmDelete = confirm(`Are you sure you want to delete Group ID ${groupId}?`);
                if (confirmDelete) {
                    // Send an AJAX request to delete the group from the database
                    fetch('groups.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            gnum: gnum,      // Unique group number
                            action: 'delete' // Action type
                        }), // Send gnum & action as JSON payload
                    })
                    .then(response => response.text())
                    
                    .then(text => {
                            alert(`Group deleted successfully.`);
                            window.location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            }   
    </script>
    <?php include 'footer.php' ?>
    </body>
</html>
