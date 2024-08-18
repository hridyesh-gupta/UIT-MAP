<?php
//To fetch roll numbers from db to show in dropdown & to insert group details in db & to show grp details if exists
session_start();
if(!(isset($_SESSION['username']))){ 
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="student" && $_SESSION['usertype']!="mentor"){
    header("location: index.php");
}

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'mapdb';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
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
$user = $_SESSION['username'];
$sql = "SELECT gnum FROM groups WHERE roll = '$user'";
$userResult = $conn->query($sql);

$groupExists = false;
if ($userResult->num_rows > 0) {
    $groupExists = true;
    $gnum = $userResult->fetch_assoc()['gnum'];
    // Fetch the group details
    $sqlGroupDetails = "SELECT * FROM groups WHERE gnum = '$gnum'";
    $groupResult = $conn->query($sqlGroupDetails);
    $groupMembers = [];
    if ($groupResult->num_rows > 0) {
        while ($row = $groupResult->fetch_assoc()) {
            $groupMembers[] = $row;
        }
    }
    $getCreationDate = "SELECT date FROM groups WHERE roll = '$user'";//To fetch the group creation date from db
    $dateResult = $conn->query($getCreationDate);//Executing the query and saving the resultset in $dateResult(even your result has 1 row $conn->query returns it as a set)
    $groupCreationDate = $dateResult->fetch_assoc()['date'];//Fetching the date from the resultset and storing it in $groupCreationDate(fetch_assoc() fetches the first row of the resultset and in its index we have passed the 'date' column so it'll return the value of date column of the first row)
}

// If no group exist now check what we have to do next means to fetch student roll numbers or to save group details to the db

//To fetch the roll numbers of all the students from db and store it in $students to show in dropdown(means when the page is loaded) 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {//As the browser automatically sends a GET request when the page is loaded
    $sql = "SELECT roll FROM info ORDER BY roll ASC";
    $result = $conn->query($sql);

    $students = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $students[] = $row['roll']; //Roll numbers are stored in $students, so that can be used in the dropdown
        }
    }
    $conn->close();
}

//To save group details to the db(means when save details button is pressed)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON received
    $data = json_decode(file_get_contents('php://input'), true);
    // Extract the members array
    $members = $data['members'];    
    // Generate a unique group number (gnum)
    $gnum = generateUniqueId();
    $_SESSION['gnum']=$gnum; //Storing the group number in session variable so that we can use it in other pages

    // Loop through each member in the members array
    foreach ($members as $member) {
        // Get the member data from the request body
        $roll = $member['roll'];
        $name = $member['name'];
        $branch = $member['branch'];
        $section = $member['section'];
        $responsibility = $member['responsibility'];
        // Insert the member data into the groups table
        $sql = "INSERT INTO groups (roll, name, branch, section, responsibility, gnum) VALUES ('$roll', '$name', '$branch', '$section', '$responsibility', '$gnum')";
        
        // Check if the query was successful
        if (!$conn->query($sql)) {
            // If the query failed, return an error response
            echo json_encode(['success' => false, 'message' => 'Failed to insert data']);            
            $conn->close();
            exit;
        }
    }
    // If everything went well, return a success response    
    echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Project Details</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
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
        <h2 class="text-2xl font-bold mb-4">Student's Project Details</h2>

        <div class="mb-4">
            <label for="groupCode" class="block text-gray-700">Group Number:</label>
            <input type="text" id="groupCode" class="w-full border p-2" disabled>
        </div>

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

    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
        <h2 class="text-2xl font-bold mb-4">Group Details</h2>

        <div class="mb-4">
            <label for="groupCreationDate" class="block text-gray-700">Group Creation Date:</label>
            <input type="text" id="groupCreationDate" class="w-full border p-2" value="<?php echo htmlspecialchars($groupCreationDate); ?>">
            <!-- <button id="lockGroupCreationDateBtn" class="bg-red-500 text-white px-4 py-2 mt-2">Lock</button> -->
        </div>

        <!-- <div class="mb-4">
            <label for="decApprovalStatus" class="block text-gray-700">DEC Approval Status:</label>
            <input type="text" id="decApprovalStatus" class="w-full border p-2" disabled>
        </div>

        <div class="mb-4" id="approvalDateDiv" style="display:none;">
            <label for="approvalDate" class="block text-gray-700">Approval Date:</label>
            <input type="date" id="approvalDate" class="w-full border p-2" disabled>
        </div> -->
    </div>

    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
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
            <label for="technologyUsed" class="block text-gray-700">Technology/Methodology Used:</label>
            <textarea id="technologyUsed" class="w-full border p-2 h-20" maxlength="880"></textarea>
        </div>
    </div>

    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
        <h2 class="text-2xl font-bold mb-4">Approval Status</h2>

        <div class="mb-4">
            <label for="supervisorApprovalStatus" class="block text-gray-700">Supervisor Approval Status:</label>
            <input type="text" id="supervisorApprovalStatus" class="w-full border p-2" disabled>
        </div>

        <div class="mb-4" id="supervisorApprovalDateDiv" style="display:none;">
            <label for="supervisorApprovalDate" class="block text-gray-700">Supervisor Approval Date:</label>
            <input type="date" id="supervisorApprovalDate" class="w-full border p-2" disabled>
        </div>

        <div class="mb-4">
            <label for="decApprovalStatus" class="block text-gray-700">DEC Approval Status:</label>
            <input type="text" id="decApprovalStatus" class="w-full border p-2" disabled>
        </div>

        <div class="mb-4" id="decApprovalDateDiv" style="display:none;">
            <label for="decApprovalDate" class="block text-gray-700">DEC Approval Date:</label>
            <input type="date" id="decApprovalDate" class="w-full border p-2" disabled>
        </div>
    </div>

    <footer class="bg-blue-500 text-white p-4 mt-8">
        <div class="max-w-6xl mx-auto text-center">
            <p>&copy; 2024 Your College Name. All rights reserved.</p>
        </div>
    </footer>

    <script>
    const members = [];
    const maxMembers = 4;
    const studentRolls = <?php echo json_encode($students); ?>;
    <?php if ($groupExists): ?>
    const groupExists = <?php echo json_encode($groupExists); ?>;
    const groupMembers = <?php echo json_encode($groupMembers); ?>;
    <?php endif; ?>

    function memberTemplate(index) {
        return `
            <div class="member-form p-4 border ${members[index]?.locked ? 'locked' : ''}">
                <h4 class="text-lg font-bold">Project Member ${index + 1}</h4>
                <div class="mb-2">
                    <label class="block text-gray-700">Student Roll Number:</label>
                    <select class="w-full border p-2 roll-number" data-index="${index}">
                        <option value="">Select...</option>
                        ${studentRolls.map(roll => `<option value="${roll}" ${members[index]?.roll === roll ? 'selected' : ''}>${roll}</option>`).join('')}
                    </select>
                </div>
                <div class="details ${members[index]?.roll ? '' : 'hidden'}">
                    <div class="mb-2">
                        <label class="block text-gray-700">Name:</label>
                        <input type="text" class="w-full border p-2 name" ${members[index]?.locked ? 'disabled' : ''} value="${members[index]?.name || ''}">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700">Section:</label>
                        <input type="text" class="w-full border p-2 section" ${members[index]?.locked ? 'disabled' : ''} value="${members[index]?.section || ''}">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700">Branch:</label>
                        <input type="text" class="w-full border p-2 branch" ${members[index]?.locked ? 'disabled' : ''} value="${members[index]?.branch || ''}">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700">Responsibility:</label>
                        <input type="text" class="w-full border p-2 responsibility" ${members[index]?.locked ? 'disabled' : ''} value="${members[index]?.responsibility || ''}">
                    </div>
                </div>
                <button class="bg-red-500 text-white px-4 py-2 mt-2 lock-member" data-index="${index}">${members[index]?.locked ? 'Unlock' : 'Lock'} Member</button>
            </div>
        `;
    }

    function updateMembersUI() {
        const membersDiv = document.getElementById('members');
        membersDiv.innerHTML = '';
        members.forEach((member, index) => {
            membersDiv.innerHTML += memberTemplate(index);
        });
        addEventListeners();
    }

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

    document.addEventListener('DOMContentLoaded', () => {
    if (groupExists) {
        // Load the existing group members
        groupMembers.forEach(member => {
            members.push({
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
    }
});

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

    function toggleResponsibilitiesSection() {
        const responsibilitiesSection = document.getElementById('responsibilitiesSection');
        if (members.some(member => member.locked) || groupExists) {
            responsibilitiesSection.style.display = 'block';
        } else {
            responsibilitiesSection.style.display = 'none';
        }
    }

    document.getElementById('addMemberBtn').addEventListener('click', () => {
        if (members.length < maxMembers) {
            members.push({});
            updateMembersUI();
        }
    });

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

        // Send data to the server
        fetch('details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ members: membersData }),
        })
        .then(response => response.json())
        .then(data => {
            alert('Details saved successfully.');
            window.location.reload(); // Refresh the page
        })
        // .catch(error => console.error('Error:', error));
        .catch(error => console.error('Error occured!'));
    });

    updateMembersUI();
    toggleResponsibilitiesSection();
    </script>
</body>
</html>
