<?php
// Admin 2nd page
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="mentor"){ //If the user is not admin or mentor, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
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
if($_SESSION['usertype'] == "admin"){
    $sql = "SELECT * FROM projinfo"; 
}
else if($_SESSION['usertype'] == "mentor"){
    $sql = "SELECT * FROM projinfo WHERE mid='$_SESSION[username]'";
}
$groupResults = $conn->query($sql); //Executing the query
$groupRows = [];
if($groupResults->num_rows > 0){ //If there are groups in the projinfo table
    $groupExists=true;
    while($groupRow = $groupResults->fetch_assoc()){ //Fetching the group details- Gnum, Group ID, Title, Intro, Tech, Technology, Creator, Mentor, Mentor ID, DEC Approval Date, and Mentor Approval Date
        $groupRows[] = $groupRow;
    }
}

//To fetch the group members details from the groups table
if($_SESSION['usertype'] == "admin"){
    $sql2 = "SELECT * FROM groups"; 
}
else if($_SESSION['usertype'] == "mentor"){
    $sql2 = "SELECT * FROM groups WHERE gnum IN (SELECT gnum FROM projinfo WHERE mid='$_SESSION[username]')"; //To fetch the group members details of the groups which are assigned to the mentor
}
$memberResults = $conn->query($sql2); //Executing the query
$memberRows = [];
if($memberResults->num_rows > 0){ //If there are group members in the groups table
    $memberExists=true;
    while($memberRow = $memberResults->fetch_assoc()){ //Fetching the group members details- Member's Roll Number, Member's Name, Section, Branch, Responsibility, Gnum, Creator and Creation Date
        $memberRows[] = $memberRow;
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
    <style>
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
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
        .group-title {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }

        .group-title:hover {
            color: darkblue;
        }
        h1 {
        font-size: 2em; /* Ensure h1 has a larger font size */
        }        
        .table-container {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php 
include 'adminheaders.php';
?>
    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <center><h2 class="text-2xl font-bold mb-6">Student Groups</h2></center>

            <!-- Filter Box -->
            <div class="mb-6 flex justify-between items-center">
                <input type="text" id="searchInput" placeholder="Search for groups by Group ID, Project Title, or Group Leader..." class="w-full p-2 border rounded">
                    <!-- <label class="ml-4 flex items-center">
                        <input type="checkbox" id="showApprovedCheckbox" class="mr-2">
                        <span>Show Approved Groups</span> -->
                    </label>
            </div>

            <!-- Group List -->
            <div class="table-container bg-white p-6 rounded-lg shadow-lg">
                <table class="min-w-full bg-white table-fixed">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-center w-16">Group ID</th>
                            <th class="px-4 py-2 text-center w-48">Project Title</th>
                            <th class="px-4 py-2 text-center w-48">Group Leader</th>
                            <?php if($_SESSION['usertype'] == "admin"){ ?>
                                <th class="px-4 py-2 text-center w-56">Mentor Assigned</th>
                            <?php } ?>
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
    <!-- Modal for Group and Project Information -->
    <div id="groupProjectInfoModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="groupProjectInfo">
                <!-- Group and project information will be dynamically added here -->
            </div>
        </div>
    </div>
    <!-- Modal for Weekly Analysis -->
    <div id="weeklyAnalysisModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Weekly Analysis</h2>
            <div id="weeklyAnalysisContent">
                <!-- Weekly analysis content will be dynamically added here -->
            </div>
            <button id="saveWeeklyAnalysis" class="bg-blue-500 text-white py-2 px-4 rounded">Save</button>
        </div>
    </div>
    <!-- Modal for Rubrics Review -->
    <div id="rubricsReviewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <center><h1>Rubrics Review</h1></center>
            <div id="rubricsReviewContent">
                <!-- Rubrics review content will be dynamically added here -->
            </div>
            <button id="saveRubricsReview" class="bg-blue-500 text-white py-2 px-4 rounded">Save</button>
        </div>
    </div>

    <script>
        const groupRows = <?php echo json_encode($groupRows); ?>; //Gnum, Group ID, Title, Intro, Tech, Technology, Creator, Mentor, Mentor ID, Creation Date, DEC Approval Date, and Mentor Approval Date
        const memberRows = <?php echo json_encode($memberRows); ?>; //Member's Roll Number, Member's Name, Section, Branch, Responsibility, Gnum, Creator and Creation Date
        const mentors = <?php echo json_encode($mentors); ?>;
        const userType = "<?php echo $_SESSION['usertype']; ?>";

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
                    <td class="border px-4 py-2 text-center group-number">${group.number}</td>
                    <td class="border px-4 py-2 text-center group-title">${group.title}</td>
                    <td class="border px-4 py-2 text-center group-creator">${group.creator}</td>
                    ${userType == "admin" ? `
                    <td class="border px-4 py-2 text-center">
                        <select class="p-2 border rounded">
                            <option value="">Select mentor...</option>
                            ${mentors.map(mentor => `
                                <option value="${mentor}" ${group.mentor === mentor ? 'selected' : ''}>${mentor}</option>
                            `).join('')}
                            // mentors.map(...): This returns an array of option HTML strings, where each element is an option tag for a mentor name & join(''): This method is used to concatenate (join) all these strings together without any separator (since '' is an empty string).
                        </select>
                        <button onclick="changeMentor(this)" style="visibility: hidden;" class="bg-green-500 text-white py-1 px-3 rounded hover:bg-green-800 transition duration-300">Change</button>
                    </td>
                    ` : ''}
                    <td class="border px-4 py-2 text-center">
                        <button onclick="openWeeklyAnalysisModal('${group.number}')" class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-800 transition duration-300">Weekly Analysis</button>
                        <button onclick="openRubricsReviewModal('${group.number}')" class="bg-purple-500 text-white py-1 px-3 rounded hover:bg-purple-800 transition duration-300">Rubrics Review</button>
                        ${userType == "admin" ? `
                            <button onclick="deleteGroup(this)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-800 transition duration-300">Delete</button>
                        ` : ''}
                    </td>
                `;
                // Append the row to the table
                groupTable.appendChild(row);
                // Add event listener to dropdown for mentor selection so that it can detect whenever there is a change in the dropdown and can call the toggleChangeButton function
                <?php if($_SESSION['usertype'] == "admin"){ ?>
                    const dropdown = row.querySelector('select');
                    dropdown.addEventListener('change', function() {
                        toggleChangeButton(this);
                    });
                <?php } ?>
            });
            // Add event listeners to title cells to open the group & project info modal
            document.querySelectorAll('.group-title').forEach(cell => {
                console.log('Attaching event listener to:', cell.textContent);
                cell.addEventListener('click', openGroupProjectInfoModal);
            });
        }
        // Function to open the group and project information modal
        function openGroupProjectInfoModal(event) {
            console.log('Cell clicked:', event.target.textContent); // Debugging line
            const groupId = event.target.closest('tr').querySelector('.group-number').textContent;
            const group = groupRows.find(group => group.number == groupId);
            const creator = event.target.closest('tr').querySelector('.group-creator').textContent;

            const modal = document.getElementById('groupProjectInfoModal');
            const groupProjectInfo = document.getElementById('groupProjectInfo');
            groupProjectInfo.innerHTML = ''; // Clear existing content

            // Populate modal with group and project information
            groupProjectInfo.innerHTML = `
                <center><h3 class="text-xl font-semibold mb-6 text-blue-600">Project Members</h3></center>
                <div class="table-container overflow-auto mb-8 shadow-lg rounded-lg border border-gray-200">
                    <table class="min-w-full bg-white rounded-lg text-gray-700">
                        <thead class="bg-blue-100 rounded-t-lg">
                            <tr>
                                <th class="px-6 py-3 border-b-2 font-medium uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 border-b-2 font-medium uppercase tracking-wider">Roll Number</th>
                                <th class="px-6 py-3 border-b-2 font-medium uppercase tracking-wider">Section</th>
                                <th class="px-6 py-3 border-b-2 font-medium uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 border-b-2 font-medium uppercase tracking-wider">Responsibility</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${memberRows.filter(member => member.creator == creator).map((member, index) => `
                                <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}">
                                    <td class="px-6 py-4 border">${member.name}</td>
                                    <td class="px-6 py-4 border">${member.roll}</td>
                                    <td class="px-6 py-4 border">${member.section}</td>
                                    <td class="px-6 py-4 border">${member.branch}</td>
                                    <td class="px-6 py-4 border">${member.responsibility}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>

                <hr class="my-8 border-gray-300">

                <center><h3 class="text-xl font-semibold mb-6 text-green-600">Group Information</h3></center>
                <div class="mb-8 p-4 bg-green-50 rounded-lg shadow-md border border-gray-300">
                    <p class="text-gray-700"><strong>Group Number:</strong> ${group.number}</p>
                    <p class="text-gray-700"><strong>Group Leader:</strong> ${group.creator}</p>
                    <p class="text-gray-700"><strong>Mentor Assigned:</strong> ${group.mentor}</p>
                    <p class="text-gray-700"><strong>Group Creation Date:</strong> ${group.date}</p>
                    <p class="text-gray-700"><strong>DEC Approval Date:</strong> ${group.dAppDate}</p>
                </div>

                <hr class="my-8 border-gray-300">

                <center><h3 class="text-xl font-semibold mb-6 text-purple-600">Project Information</h3></center>
                <div class="mb-8 p-4 bg-purple-50 rounded-lg shadow-md border border-gray-300">
                    <p class="text-gray-700"><strong>Project Title:</strong> ${group.title}</p>
                    <p class="text-gray-700"><strong>Introduction:</strong> ${group.intro}</p>
                    <p class="text-gray-700"><strong>Objective:</strong> ${group.objective}</p>
                    <p class="text-gray-700"><strong>Technology Used (In short):</strong> ${group.tech}</p>
                    <p class="text-gray-700"><strong>Technology Used (In detail):</strong> ${group.technology}</p>
                </div>
            `;
            modal.style.display = 'block';
        }

        // Function to open the weekly analysis modal
        function openWeeklyAnalysisModal(groupId) {
            console.log('Opening weekly analysis for group:', groupId); // Debugging line
            const group = groupRows.find(group => group.number == groupId);

            const modal = document.getElementById('weeklyAnalysisModal');
            const modalContent = document.getElementById('weeklyAnalysisContent');
            modalContent.innerHTML = ''; // Clear existing content

            // Populate modal with weekly analysis fields
            for (let week = 1; week <= 36; week++) {
                const weekDiv = document.createElement('div');
                weekDiv.classList.add('mb-4');
                weekDiv.innerHTML = `
                    <h3 class="text-lg font-bold mb-2">Week ${week}</h3>
                    <label class="block mb-2">Weekly Summary:</label>
                    <textarea class="w-full p-2 border rounded mb-2" rows="3"></textarea>
                    <label class="block mb-2">Performance:</label>
                    <select class="w-full p-2 border rounded mb-2">
                        <option value="satisfactory">Satisfactory</option>
                        <option value="not_satisfactory">Not Satisfactory</option>
                    </select>
                    <label class="block mb-2">Date of Submission:</label>
                    <input type="date" class="w-full p-2 border rounded mb-2">
                    <label class="block mb-2">Date of Evaluation:</label>
                    <input type="date" class="w-full p-2 border rounded mb-2">
                `;
                modalContent.appendChild(weekDiv);
            }
            // Show existing data if present
            // Assuming `group.weeklyData` contains the existing data for the weeks
            if (group.weeklyData) {
                group.weeklyData.forEach((weekData, index) => {
                    const weekDiv = modalContent.children[index];
                    weekDiv.querySelector('textarea').value = weekData.summary;
                    weekDiv.querySelector('select').value = weekData.performance;
                    weekDiv.querySelector('input[type="date"]').value = weekData.submissionDate;
                    weekDiv.querySelectorAll('input[type="date"]')[1].value = weekData.evaluationDate;
                });
            }
            modal.style.display = 'block';      
            modal.scrollTop = 0;
        }
        // Function to open the rubrics review modal
        function openRubricsReviewModal(groupNumber) {
            console.log('Button clicked for group:', groupNumber); // Debugging line
            const group = groupRows.find(group => group.number == groupNumber);

            const modal = document.getElementById('rubricsReviewModal');
            const modalContent = document.getElementById('rubricsReviewContent');
            modalContent.innerHTML = ''; // Clear existing content

            // Populate modal with rubrics review fields
            for (let i = 1; i <= 8; i++) {
                const rubricDiv = document.createElement('div');
                rubricDiv.classList.add('mb-4', 'rubric-section');
                rubricDiv.innerHTML = `
                    <div class="bg-beige shadow-xl rounded-xl p-6 mb-4 max-h-[500px] overflow-y-auto border-t-4 border-indigo-400">
                        <h3 class="text-2xl font-serif text-gray-800 mb-6">Rubric R${i}</h3>

                        <!-- View PPT -->
                        <div class="mb-5">
                            <label for="ppt-upload" class="block text-gray-700 font-medium mb-2">View PPT:</label>
                            <input id="ppt-upload" type="file" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg">
                        </div>

                        <!-- View Report -->
                        <div class="mb-5">
                            <label for="report-upload" class="block text-gray-700 font-medium mb-2">View Report:</label>
                            <input id="report-upload" type="file" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg">
                        </div>

                        <!-- Submission Date -->
                        <div class="mb-5">
                            <label for="submission-date" class="block text-gray-700 font-medium mb-2">Submission Date:</label>
                            <input id="submission-date" type="date" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg">
                        </div>

                        <!-- Evaluation Date -->
                        <div class="mb-5">
                            <label for="evaluation-date" class="block text-gray-700 font-medium mb-2">Evaluation Date:</label>
                            <input id="evaluation-date" type="date" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg">
                        </div>

                        <!-- Status -->
                        <div class="mb-5">
                            <label for="status" class="block text-gray-700 font-medium mb-2">Status:</label>
                            <select id="status" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg">
                                <option value="completed">Completed</option>
                                <option value="not_completed">Not Completed</option>
                            </select>
                        </div>
                    </div>
                `;

                modalContent.appendChild(rubricDiv);
            }

            modal.style.display = 'block';
        }
        // Close the modals when the close button is clicked
        document.querySelectorAll('.close').forEach(closeButton => {
            closeButton.addEventListener('click', () => {
                // Close the modal
        const modal = closeButton.closest('.modal');
        modal.style.display = 'none';

        // Reset the scroll position to the top of the page
        window.scrollTo(0, 0);
            });
        });

        // Close the modals when clicking outside of the modal content
        window.addEventListener('click', (event) => {
            document.querySelectorAll('.modal').forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                    // Reset the scroll position to the top of the page
        window.scrollTo(0, 0);
                }
            });
        });
        // Save weekly analysis data
        document.getElementById('saveWeeklyAnalysis').addEventListener('click', () => {
            // Collect data from the modal
            const weeklyData = [];
            document.querySelectorAll('#weeklyAnalysisContent > div').forEach((weekDiv, index) => {
                const summary = weekDiv.querySelector('textarea').value;
                const performance = weekDiv.querySelector('select').value;
                const submissionDate = weekDiv.querySelector('input[type="date"]').value;
                const evaluationDate = weekDiv.querySelector('input[type="date"]').value;
                weeklyData.push({
                    week: index + 1,
                    summary,
                    performance,
                    submissionDate,
                    evaluationDate
                });
            });

            // Send data to the server (implement server-side handling)
            console.log(weeklyData);

            // Close the modal
            document.getElementById('weeklyAnalysisModal').style.display = 'none';
        });

        // document.getElementById('showApprovedCheckbox').addEventListener('change', function() {
        //     const rows = document.querySelectorAll('.group-item');
        //     rows.forEach(row => {
        //         if (this.checked) {
        //             if (row.getAttribute('data-approved') === 'true') {
        //                 row.style.display = '';
        //             } else {
        //                 row.style.display = 'none';
        //             }
        //         } else {
        //             row.style.display = '';
        //         }
        //     });
        // });

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.group-item');
            rows.forEach(row => {
                const groupID = row.cells[0].textContent.toLowerCase();
                const projectTitle = row.cells[1].textContent.toLowerCase();
                const groupLeader = row.cells[2].textContent.toLowerCase();
                if (groupID.includes(searchTerm) || projectTitle.includes(searchTerm) || groupLeader.includes(searchTerm)) {
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
        //To toggle the visibility of the "Change" button only when a different mentor is selected
        function toggleChangeButton(dropdown) {
            const row = dropdown.closest('tr');
            const button = row.querySelector('button'); // Select the "Change" button within the same row
            const selectedMentor = dropdown.value;
            const groupId = row.cells[0].textContent;
            const group = groupRows.find(group => group.number == groupId);
            const currentMentor = group ? group.mentor : null; // Existing mentor for the group

            console.log('Selected Mentor:', selectedMentor);  // Debugging line
            console.log('Current Mentor:', currentMentor);    // Debugging line

            // Show the button only if a different mentor is selected
            if (selectedMentor !== currentMentor && selectedMentor !== "") {
                button.style.visibility = 'visible';
                console.log('Button made visible'); // Debugging line
            } else {
                button.style.visibility = 'hidden';
                console.log('Button hidden'); // Debugging line
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
