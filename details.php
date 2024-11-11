<?php
//To fetch roll numbers from db to show in dropdown & to insert group details in db & to show grp details if exists
error_reporting(0); //To hide the errors
session_start();
if(!(isset($_SESSION['username']))){ 
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="student" && $_SESSION['usertype']!="mentor"){
    header("location: index.php");
}

include 'dbconnect.php';

// Function to generate a unique identifier with numbers and letters
function generateUniqueId($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Check if a group already exists for the current user
$user = $_SESSION['username']; //As we have stored the username in session variable when the user logged in
$sql = "SELECT gnum FROM groups WHERE roll = '$user'";
$userResult = $conn->query($sql);

$groupExists = false;
if ($userResult->num_rows > 0) {//If there is a group for the current user then it will enter in this block
    $groupExists = true;
    $gnum = $userResult->fetch_assoc()['gnum'];
    $_SESSION['gnum'] = $gnum; //So we have saved 3 things for the whole session username, usertype and gnum
    // Fetch the group details
    $sqlGroupDetails = "SELECT * FROM groups WHERE gnum = '$gnum'";
    $groupResult = $conn->query($sqlGroupDetails);
    $groupMembers = [];
    if ($groupResult->num_rows > 0) {
        while ($row = $groupResult->fetch_assoc()) {
            $groupMembers[] = $row;
        }
    }
    //To fetch the creator of the group from db to declare him as the leader of the group
    $getLeader = "SELECT creator FROM groups WHERE gnum = '$gnum'";
    $leaderResult = $conn->query($getLeader);//Executing the query and saving the resultset in $leaderResult
    $leader= $leaderResult->fetch_assoc()['creator'];//Fetching the creator from the resultset and storing it in $leader

    //To fetch the group creation date from db
    $getCreationDate = "SELECT date FROM groups WHERE roll = '$user'";
    $dateResult = $conn->query($getCreationDate);//Executing the query and saving the resultset in $dateResult(even your result has 1 row $conn->query returns it as a associative set with 'date' as the key)
    $groupCreationDate = $dateResult->fetch_assoc()['date'];//Fetching the date from the resultset and storing it in $groupCreationDate(fetch_assoc() fetches the first row of the resultset and in its index we have passed the 'date' column so it'll return the value of date column of the first row)

}
//Check for project details if the group exists
$projectExists = false;    
if ($groupExists) {
    $sqlProjectDetails = "SELECT * FROM projinfo WHERE gnum = '$gnum'";
    $projectResult = $conn->query($sqlProjectDetails);
    if ($projectResult->num_rows > 0) {
        $projectExists = true;
        $projectDetails = $projectResult->fetch_assoc();
    }
    //To fetch the mentor assigned to the group
    $getMentor = "SELECT mentor, dAppDate FROM projinfo WHERE gnum = '$gnum'";//To fetch the mentor assigned to the group
    $mentorResult = $conn->query($getMentor);//Executing the query and saving the resultset in $mentorResult
    $mentorExists=false;
    if($mentorResult->num_rows > 0){
        $mentorData = $mentorResult->fetch_assoc();//Fetching the mentorData(mentor and approval date) from the resultset and storing it in $mentorData
        $mentor=$mentorData['mentor'];//Fetching the mentor value from the mentorData and storing it in $mentor
        if($mentor!=NULL){
            $mentorExists=true;
            //Fetching the date of approval bcoz as soon as the mentor is assigned the date of approval is also assigned
            $dAppDate=$mentorData['dAppDate'];//Fetching the date of approval from the mentorData and storing it in $dAppDate
        }
    }
}

// If no group exist now check what to do next, means whether we have to fetch student roll numbers or to save details to the db

//Code to fetch the roll numbers of all the students from db and store it in $students to show in dropdown(means when the page is loaded then this request will get generated) 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {//As the browser automatically sends a GET request when the page is loaded
    $sql = "SELECT roll FROM info WHERE roll NOT IN (SELECT roll FROM groups WHERE gnum IS NOT NULL) ORDER BY roll ASC";//To fetch the roll numbers of all the students who are not in any group
    $result = $conn->query($sql);

    $students = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $students[] = $row['roll']; //Roll numbers(roll) from DB are stored in $students, so that can be used in the dropdown
        }
    }
    $conn->close();
}

//Code to save group or project details to the db(means save details button is pressed)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON received
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'];
    
    //Code to be executed when the project details save button is pressed
    if ($action === 'save_project') {
        // Handle saving project details
        $title = $data['title'];
        $intro = $data['intro'];
        $objective = $data['objective'];
        $tech = $data['tech'];
        $technology = $data['technology'];

        // Fetch the batchyr, name of the creator & creation date of this grp from the groups table to save it along with the project details in the projinfo table
        $creatorQuery= "SELECT batchyr, creator, date FROM groups WHERE roll='$user' LIMIT 1"; 
        $creatorResult = $conn->query($creatorQuery); //Executing the query and saving the resultset in $creatorResult
        $creatorData = $creatorResult->fetch_assoc(); //Fetching the data from the resultset
        $creator = $creatorData['creator']; //Fetching the creator from the resultset and storing it in $creator
        $date = $creatorData['date']; //Fetching the date from the resultset and storing it in $date
        $batchyr = $creatorData['batchyr']; //Fetching the batchyr from the resultset and storing it in $date
        

        // Prepare the SQL query to insert the project details into the projinfo table
        $sql = "INSERT INTO projinfo (gnum, batchyr, title, intro, objective, tech, technology, creator, date) VALUES ('$gnum', '$batchyr', '$title', '$intro', '$objective', '$tech', '$technology', '$creator', '$date')";
        $sqlResult= $conn->query($sql);
        // Check if the insertion of project details is successful or not means it will only enter in if block if the insertion was not successful
        if (!$sqlResult) {
            echo json_encode(['success' => false, 'message' => 'Error inserting data: ' . $conn->error]);
            $conn->close();
            exit;
        }
        // If project details insertion went successful, return a success response    
        echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
        // Close the database connection
        $conn->close();
        exit;
    }
    //Code to be executed when the grp member details save button is pressed
    else if ($action === 'save_group') {
        // Extract the members array
        $members = $data['members'];    
        // Generate a unique group number (gnum)
        $gnum = generateUniqueId();
        $_SESSION['gnum']=$gnum; //Storing the group number in session variable so that we can use it in other pages
        
        // Fetch the name and batchyr of the present user so that he can be saved as the creator of the group and his batch can be saved as the batchyr of the group
        $creatorQuery= "SELECT name, batchyr FROM info WHERE roll='$user'";//To fetch the name of the creator of the group
        $creatorResult = $conn->query($creatorQuery);//Executing the query and saving the resultset in $creatorResult
        $creatorData = $creatorResult->fetch_assoc();
        $creator = $creatorData['name'];//Fetching the name from the resultset and storing it in $creator
        $batchyr = $creatorData['batchyr'];//Fetching the batchyr from the resultset and storing it in $batchyr
        
        // Loop through each member in the members array
        foreach ($members as $member) {
            // Get the member data from the request body
            $roll = $member['roll'];
            $name = $member['name'];
            $branch = $member['branch'];
            $section = $member['section'];
            $responsibility = $member['responsibility'];
            // Insert the member data into the groups table
            $sql = "INSERT INTO groups (roll, name, batchyr, branch, section, responsibility, gnum, creator) VALUES ('$roll', '$name', '$batchyr', '$branch', '$section', '$responsibility', '$gnum', '$creator')";
            $sqlResult= $conn->query($sql);
            // Check if the insertion of current member is successful or not means it will only enter in if block if the insertion was not successful
            if (!$sqlResult) {
                // If there is an error during insertion, return an error response
                echo json_encode(['success' => false, 'message' => 'Error inserting data: ' . $conn->error]);
                $conn->close();
                exit;
            }
            
        }
        // If all members details insertion went successful, return a success response    
        echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
        // Close the database connection
        $conn->close();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Project Details</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
    <style>
        .locked {
            background-color: #f0f0f0;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php include 'studentheaders.php' ?>

    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
    <center><h1 class="text-3xl font-bold mb-4">Student's Project Details</h1></center>
    <?php if ($groupExists): ?>
        <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
            <h2 class="text-2xl font-bold mb-4">Group Details</h2>
            <?php if ($groupExists): ?>
                <div class="mb-4">
                    <label for="groupCode" class="block text-gray-700">Group ID:</label>
                    <input type="text" id="groupCode" class="w-full border p-2" disabled>
                </div>
            <?php endif; ?>
            <?php if ($mentorExists): ?>
            <div class="mb-4">
                <label for="groupMentor" class="block text-gray-700">Group Mentor:</label>
                <input type="text" id="groupMentor" class="w-full border p-2" value="<?php echo htmlspecialchars($mentor); ?>" disabled>
            </div>
            <?php endif; ?>
            <div class="mb-4">
                <label for="groupLeader" class="block text-gray-700">Group Leader:</label>
                <input type="text" id="groupLeader" class="w-full border p-2" value="<?php echo htmlspecialchars($leader); ?>" disabled>
            </div>
            <div class="mb-4">
                <label for="groupCreationDate" class="block text-gray-700">Group Creation Date:</label>
                <input type="text" id="groupCreationDate" class="w-full border p-2" value="<?php echo htmlspecialchars($groupCreationDate); ?>" disabled>
            </div>
        </div>
        <?php endif; ?>
        
        <h3 class="text-xl font-bold mb-2" id="grpDetails">Project Group Details</h3>

        <div id="members" class="space-y-6"></div>

        <button id="addMemberBtn" class="bg-blue-500 text-white px-4 py-2 mt-4">Add Member</button>
    </div>

    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto" id="responsibilitiesSection" style="display:none;">
        <h2 class="text-2xl font-bold mb-4">Project Work Distribution</h2>
        <table class="min-w-full bg-white border-2">
            <thead>
                <tr>
                    <th class="py-2 border">Roll Number</th>
                    <th class="py-2 border">Name</th>
                    <th class="py-2 border">Section</th>
                    <th class="py-2 border">Branch</th>
                    <th class="py-2 border">Responsibility</th>
                </tr>
            </thead>
            <tbody id="responsibilitiesTable" style="text-align: center;"></tbody>
        </table>
        <button type="submit" id="saveDetailsBtn" class="bg-green-500 text-white px-4 py-2 mt-4">Save Details</button>
    </div>
    <?php if ($groupExists): ?>
    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto" id="projectInfo">
        <h2 class="text-2xl font-bold mb-4">Project Information</h2>

        <div class="mb-4">
            <label for="projectTitle" class="block text-gray-700">Project Title:</label>
            <input type="text" id="projectTitle" class="w-full border p-2" maxlength="50">
        </div>

        <div class="mb-4">
            <label for="briefIntroduction" class="block text-gray-700">Brief Introduction:</label>
            <textarea id="briefIntroduction" class="w-full border p-2 h-20" maxlength="880"></textarea>
        </div>

        <div class="mb-4">
            <label for="objectiveStatement" class="block text-gray-700">Objective and Problem Statement:</label>
            <textarea id="objectiveStatement" class="w-full border p-2 h-20" maxlength="880"></textarea>
        </div>
        
        <div class="mb-4">
            <label for="technology1Word" class="block text-gray-700">Technology Used (In Short):</label>
            <input type="text" id="technology1Word" class="w-full border p-2" maxlength="50">
        </div>

        <div class="mb-4">
            <label for="technologyUsed" class="block text-gray-700">Technology/Methodology Used (In Detail):</label>
            <textarea id="technologyUsed" class="w-full border p-2 h-20" maxlength="880"></textarea>
        </div>
        <button type="submit" id="saveProjDetailsBtn" class="bg-green-500 text-white px-4 py-2 mt-4">Save Details</button>
    </div>

    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
        <h2 class="text-2xl font-bold mb-4">Approval Status</h2>

        <!-- <div class="mb-4">
            <label for="supervisorApprovalStatus" class="block text-gray-700">Mentor Approval Status:</label>
            <input type="text" id="supervisorApprovalStatus" class="w-full border p-2" disabled>
        </div> -->
        <?php if ($mentorExists): ?>
            <div class="mb-4" id="supervisorApprovalDateDiv"">
                <label for="supervisorApprovalDate" class="block text-gray-700">Mentor Approval Date:</label>
                <input type="text" id="supervisorApprovalDate" class="w-full border p-2" disabled>
            </div>
        <?php endif; ?>

        <!-- <div class="mb-4">
            <label for="decApprovalStatus" class="block text-gray-700">DEC Approval Status:</label>
            <input type="text" id="decApprovalStatus" class="w-full border p-2" disabled>
        </div> -->

        <div class="mb-4" id="decApprovalDateDiv"">
            <label for="decApprovalDate" class="block text-gray-700">DEC Approval Date:</label>
            <input type="text" id="decApprovalDate" class="w-full border p-2" value="<?php echo htmlspecialchars($dAppDate); ?>" disabled>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
    const members = [];
    const maxMembers = 4;
    const studentRolls = <?php echo json_encode($students); ?>;
    <?php if ($groupExists): ?>
    const groupExists = <?php echo json_encode($groupExists); ?>;
    const groupMembers = <?php echo json_encode($groupMembers); ?>; //All the detail names getting stored in groupMembers will be same as the column names in the groups table as $groupMembers has fetched the data from the groups table and further it is being converted to JSON so names will be same
    <?php endif; ?>
    
    //Logic to create the member template when the add member button is pressed
    function memberTemplate(index) {
        return `
            <div class="member-form p-4 border ${members[index]?.locked ? 'locked' : ''}">
                <h4 class="text-lg font-bold">Project Member ${index + 1} ${index === 0 ? '(Your Details)' : ''}</h4>
                <div class="mb-2">
                    <label class="block text-gray-700">Student Roll Number:</label>
                    <select class="w-full border p-2 roll-number" data-index="${index}">
                        <option value="">Select...</option>
                        ${studentRolls.map(roll => 
                            `<option value="${roll}" ${members[index]?.roll === roll ? 'selected' : ''}>${roll}</option>
                        `).join('')}
                        //studentRolls.map(...): This returns an array of option tag HTML strings, where each element is an option tag for a student roll no. & join(''): This method is used to concatenate (join) all these strings together without any separator (since '' is an empty string).
                    </select>
                </div>
                <div class="details ${members[index]?.roll ? '' : 'hidden'}">
                    <div class="mb-2">
                        <label class="block text-gray-700">Name:</label>
                        <input type="text" class="w-full border p-2 name" maxlength="35" ${members[index]?.locked ? 'disabled' : ''} value="${members[index]?.name || ''}">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700">Section:</label>
                        <input type="text" class="w-full border p-2 section" maxlength="4" ${members[index]?.locked ? 'disabled' : ''} value="${members[index]?.section || ''}">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700">Branch:</label>
                        <input type="text" class="w-full border p-2 branch" maxlength="4" ${members[index]?.locked ? 'disabled' : ''} value="${members[index]?.branch || ''}">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700">Responsibility:</label>
                        <input type="text" class="w-full border p-2 responsibility" maxlength="35" ${members[index]?.locked ? 'disabled' : ''} value="${members[index]?.responsibility || ''}">
                    </div>
                </div>
                <button class="bg-red-500 text-white px-4 py-2 mt-2 lock-member" data-index="${index}">${members[index]?.locked ? 'Unlock' : 'Lock'} Member</button>
            </div>
        `;
    }
    //Logic to update the members UI when a new member is added
    function updateMembersUI() {
        const membersDiv = document.getElementById('members');
        membersDiv.innerHTML = '';
        members.forEach((member, index) => {
            membersDiv.innerHTML += memberTemplate(index);
        });
        addEventListeners();
    }
    //Logic to add event listeners to the dropdowns and lock/unlock buttons
    function addEventListeners() {
        document.querySelectorAll('.roll-number').forEach(select => {
            select.addEventListener('change', (e) => {
                const index = e.target.dataset.index;
                const roll = e.target.value;
                if (roll) {
                    members[index].roll = roll;
                    members[index].name = '';  // Reset name, section, branch, responsibility
                    members[index].section = '';
                    members[index].branch = '';
                    members[index].responsibility = '';
                    e.target.closest('.member-form').querySelector('.details').classList.remove('hidden');
                } else {
                    members[index] = {};
                    e.target.closest('.member-form').querySelector('.details').classList.add('hidden');
                }
                updateMembersUI();
            });
        });
        //Logic to lock/unlock the member details
        document.querySelectorAll('.lock-member').forEach(button => {
            button.addEventListener('click', (e) => {
                const index = e.target.dataset.index;
                members[index].locked = !members[index]?.locked;
                if (members[index].locked) {
                    members[index].roll = e.target.closest('.member-form').querySelector('.roll-number').value;
                    members[index].name = e.target.closest('.member-form').querySelector('.name').value;
                    members[index].section = e.target.closest('.member-form').querySelector('.section').value;
                    members[index].branch = e.target.closest('.member-form').querySelector('.branch').value;
                    members[index].responsibility = e.target.closest('.member-form').querySelector('.responsibility').value;
                }
                updateMembersUI();
                updateResponsibilitiesTable();
                toggleResponsibilitiesSection();
            });
        });
    }
    //Logic to update the project details UI when the page is loaded and project details exists
    document.addEventListener('DOMContentLoaded', () => {
        // This section assumes that the project details are available in $projectDetails in the PHP script
        <?php if ($projectExists): ?>
        const projectDetails = <?php echo json_encode($projectDetails);?>;

        // Fill the form fields automatically with the project details
        document.getElementById('groupCode').value = projectDetails.number;
        document.getElementById('projectTitle').value = projectDetails.title;
        document.getElementById('briefIntroduction').value = projectDetails.intro;
        document.getElementById('objectiveStatement').value = projectDetails.objective;
        document.getElementById('technology1Word').value = projectDetails.tech;
        document.getElementById('technologyUsed').value = projectDetails.technology;
        // Hide the save button as the details are already saved
        document.getElementById('saveProjDetailsBtn').style.display = 'none';
        // Disable the specified text input boxes
        document.getElementById('projectTitle').disabled = true;
        document.getElementById('briefIntroduction').disabled = true;
        document.getElementById('objectiveStatement').disabled = true;
        document.getElementById('technology1Word').disabled = true;
        document.getElementById('technologyUsed').disabled = true;
        <?php endif; ?>
    });

    //Logic to update the members UI when the page is loaded and group exists
    document.addEventListener('DOMContentLoaded', () => {
        <?php if ($groupExists): ?>
            // Load the existing group members
            groupMembers.forEach(member => {//Loop through each member details in the groupMembers array
                members.push({//Push the member details to the members array
                    roll: member.roll,
                    name: member.name,
                    section: member.section,
                    branch: member.branch,
                    responsibility: member.responsibility,
                    locked: true // Lock the member's details as they are already set
                });
            });
            document.getElementById('addMemberBtn').style.display = 'none';
            document.getElementById('saveDetailsBtn').style.display = 'none';
            document.getElementById('grpDetails').style.display = 'none';
            document.getElementById('members').style.display = 'none';
            // Update the UI
            updateMembersUI();
            updateResponsibilitiesTable();
            toggleResponsibilitiesSection();
        <?php endif; ?>
});
    //Logic to update the responsibilities table when a new member is added
    function updateResponsibilitiesTable() {
        const tableBody = document.getElementById('responsibilitiesTable');
        tableBody.innerHTML = '';
        members.filter(member => member.locked).forEach(member => {
            tableBody.innerHTML += `
                <tr>
                    <td class="py-2 border center-align">${member.roll}</td>
                    <td class="py-2 border center-align">${member.name}</td>
                    <td class="py-2 border center-align">${member.section}</td>
                    <td class="py-2 border center-align">${member.branch}</td>
                    <td class="py-2 border center-align">${member.responsibility}</td>
                </tr>
            `;
        });
        toggleResponsibilitiesSection();
    }
    //Logic to define when to show the responsibilities section
    function toggleResponsibilitiesSection() {
        const responsibilitiesSection = document.getElementById('responsibilitiesSection');
        if (members.some(member => member.locked)) {
            responsibilitiesSection.style.display = 'block';
        }
        <?php if ($groupExists): ?>
        else if (groupExists) {
            responsibilitiesSection.style.display = 'block';
        }
        <?php endif; ?>
        else {
            responsibilitiesSection.style.display = 'none';
        }
    }
    //Logic to check whether a new member can be added or not when the add member button is pressed
    document.getElementById('addMemberBtn').addEventListener('click', () => {
        if (members.length < maxMembers) {
            members.push({});
            updateMembersUI();
        }
    });

    //Logic to save the group details to the db when save details button is pressed
    document.getElementById('saveDetailsBtn').addEventListener('click', (event) => {
        event.preventDefault();
        const responsibilitiesTable = document.getElementById('responsibilitiesTable');
        const rows = responsibilitiesTable.querySelectorAll('tr');
        let allFieldsFilled = true;
        const membersData = [];

        rows.forEach(row => {
            const roll = row.cells[0].innerText;
            const name = row.cells[1].innerText;
            const section = row.cells[2].innerText;
            const branch = row.cells[3].innerText;
            const responsibility = row.cells[4].innerText;

            if (!roll || !name || !branch || !section || !responsibility) {
                allFieldsFilled = false;
            }

            membersData.push({ roll, name, branch, section, responsibility });
        });

        if (!allFieldsFilled) {
            alert('Please fill all fields.');
            return;
        }
        // Prepare the data to be sent to the server, including the action
        const groupData = {
            action: 'save_group',  // Indicate that this request is for saving group details
            members: membersData,
        };
        // Send data to the server to save group member details
        fetch('details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(groupData),
        })
        .then(response => response.json())//It converts the response to JSON to make it more readable and to use it in the next .then block
        .then(data => {//It is used to access the data returned by the previous .then block and then we can use this data to display the message. Also we can name the data anything we want, here we have named it as data
            if (data.success) {
                alert('Group Details saved successfully.');
                window.location.reload(); // Refresh the page
            } 
            else {
                alert('Something went wrong! Group Details not saved successfully.');
                window.location.reload(); // Refresh the page
            }
        })
        .catch(error => {
            console.error('Error occurred:', error);
            alert('An unexpected error occurred. Please try again later.');
        });
    });
    
    //Logic to save the project details to the db when save details button is pressed
    document.getElementById('saveProjDetailsBtn').addEventListener('click', (event) => {
        event.preventDefault(); // Prevent the default form submission

        // Collect the data from the form fields
        const projectTitle = document.getElementById('projectTitle').value;
        const briefIntroduction = document.getElementById('briefIntroduction').value;
        const objectiveStatement = document.getElementById('objectiveStatement').value;
        const technology1Word = document.getElementById('technology1Word').value;
        const technologyUsed = document.getElementById('technologyUsed').value;

        // Ensure all fields are filled
        if (!projectTitle || !briefIntroduction || !objectiveStatement || !technology1Word || !technologyUsed) {
            alert('Please fill all fields.');
            return;
        }

        // Prepare the data to be sent to the server, including the action
        const projectData = {
            action: 'save_project',  // Indicate that this request is for saving project details
            title: projectTitle,
            intro: briefIntroduction,
            objective: objectiveStatement,
            tech: technology1Word,
            technology: technologyUsed
        };

        // Send the data to the server to save project details
        fetch('details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(projectData),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                    alert('Project Details saved successfully.');
                    window.location.reload(); // Refresh the page
                } 
            else {
                alert('Something went wrong! Project Details not saved successfully.');
                window.location.reload(); // Refresh the page
            }
        })
        .catch(error => {
                console.error('Error occurred:', error);
                alert('An unexpected error occurred. Please try again later.');
        });
    });

    updateMembersUI();
    toggleResponsibilitiesSection();
    </script>
<?php include 'footer.php' ?>
</body>
</html>
