<!-- 4th page -->
<?php
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="student" && $_SESSION['usertype']!="mentor"){ //If the user is not admin, student, or mentor, then it means the user is accessing this page through url editing. So, redirecting to login page
    header("location: index.php");
}

//PHP code to fetch student roll numbers from the database

// Set database connection parameters
$host = 'localhost'; // Database server address
$username = 'root'; // Database username
$password = ''; // Database password
$database = 'mapdb'; // Database name

// Establish a new database connection using MySQLi
$conn = new mysqli($host, $username, $password, $database);

// Check if the database connection was successful
if ($conn->connect_error) {
    // Terminate script and output connection error if connection failed
    die("Connection failed: " . $conn->connect_error);
}

// Define SQL query to select all student roll numbers from the 'info' table
$sql = "SELECT roll FROM info ORDER BY roll ASC"; 

// Execute the SQL query on the database connection
$result = $conn->query($sql);

// Initialize an array to hold the fetched student roll numbers
$students = [];
// Check if the query returned any rows
if ($result->num_rows > 0) {
    // Loop through each row in the result set
    while($row = $result->fetch_assoc()) {
        // Add the student's roll numbers to the $students array
        $students[] = $row['roll'];
    }
}
// Close the database connection
$conn->close();
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
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php include 'studentheaders.php' ?>

    <!-- Main Content -->
    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
        <h2 class="text-2xl font-bold mb-4">Student's Project Details</h2>

        <div class="mb-4">
            <label for="groupCode" class="block text-gray-700">Group Number:</label>
            <input type="text" id="groupCode" class="w-full border p-2" disabled>
        </div>

        <h3 class="text-xl font-bold mb-2">Project Group Details</h3>

        <div id="members" class="space-y-6">
            <!-- Member forms will be dynamically added here -->
        </div>

        <button id="addMemberBtn" class="bg-blue-500 text-white px-4 py-2 mt-4">Add Member</button>
    </div>

    <!-- Responsibilities Section -->
    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto" id="responsibilitiesSection" style="display:none;">
        <h2 class="text-2xl font-bold mb-4">Project Work Distribution</h2>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2">Project Member Roll Number</th>
                    <th class="py-2">Responsibility</th>
                </tr>
            </thead>
            <tbody id="responsibilitiesTable">
                <!-- Responsibilities rows will be dynamically added here -->
            </tbody>
        </table>
    </div>

    <!-- Group Details Section -->
    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
        <h2 class="text-2xl font-bold mb-4">Group Details</h2>

        <div class="mb-4">
            <label for="groupCreationDate" class="block text-gray-700">Group Creation Date:</label>
            <input type="date" id="groupCreationDate" class="w-full border p-2">
            <button id="lockGroupCreationDateBtn" class="bg-red-500 text-white px-4 py-2 mt-2">Lock</button>
        </div>

        <div class="mb-4">
            <label for="decApprovalStatus" class="block text-gray-700">DEC Approval Status:</label>
            <input type="text" id="decApprovalStatus" class="w-full border p-2" disabled>
        </div>

        <div class="mb-4" id="approvalDateDiv" style="display:none;">
            <label for="approvalDate" class="block text-gray-700">Approval Date:</label>
            <input type="date" id="approvalDate" class="w-full border p-2" disabled>
        </div>
    </div>

    <!-- Project Information Section -->
    <div class="w-full bg-white p-8 shadow-lg my-8 mx-auto">
        <h2 class="text-2xl font-bold mb-4">Project Information</h2>

        <div class="mb-4">
            <label for="projectTitle" class="block text-gray-700">Project Title:</label>
            <input type="text" id="projectTitle" class="w-full border p-2">
        </div>

        <div class="mb-4">
            <label for="briefIntroduction" class="block text-gray-700">Brief Introduction:</label>
            <textarea id="briefIntroduction" class="w-full border p-2 h-20"></textarea>
        </div>

        <div class="mb-4">
            <label for="objectiveStatement" class="block text-gray-700">Objective and Problem Statement:</label>
            <textarea id="objectiveStatement" class="w-full border p-2 h-20"></textarea>
        </div>

        <div class="mb-4">
            <label for="technologyUsed" class="block text-gray-700">Technology/Methodology Used:</label>
            <textarea id="technologyUsed" class="w-full border p-2 h-20"></textarea>
        </div>
    </div>

    <!-- Approval Section -->
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

    <!-- Footer -->
    <footer class="bg-blue-500 text-white p-4 mt-8">
        <div class="max-w-6xl mx-auto text-center">
            <p>&copy; 2024 Your College Name. All rights reserved.</p>
        </div>
    </footer>

    <script>
    const members = [];
    const maxMembers = 4;
    const studentRolls = <?php echo json_encode($students); ?>; // Converts the students array into JSON(JS) format

    function memberTemplate(index) {
        return `
            <div class="member-form p-4 border ${members[index]?.locked ? 'locked' : ''}">
                <h4 class="text-lg font-bold">Project Member ${index + 1}</h4>
                <div class="mb-2">
                    <label class="block text-gray-700">Student Roll Number:</label>
                    <select class="w-full border p-2" ${members[index]?.locked ? 'disabled' : ''}>
                        <option value="">Select Roll number...</option>
                        ${studentRolls.map(roll => `<option value="${roll}" ${members[index]?.roll === roll ? 'selected' : ''}>${roll}</option>`).join('')}
                    </select>
                </div>
                <button class="lockMemberBtn bg-red-500 text-white px-4 py-2 mt-2" ${members[index]?.locked ? 'disabled' : ''}>Lock</button>
            </div>
        `;
    }

    function responsibilityTemplate(member) {
        return `
            <tr>
                <td class="border px-4 py-2">${member.roll}</td>
                <td class="border px-4 py-2"><input type="text" class="w-full border p-2"></td>
            </tr>
        `;
    }

    function renderMembers() {
        const membersDiv = document.getElementById('members');
        membersDiv.innerHTML = members.map((_, i) => memberTemplate(i)).join('');
    }

    function renderResponsibilities() {
        const responsibilitiesTable = document.getElementById('responsibilitiesTable');
        responsibilitiesTable.innerHTML = members.filter(member => member.locked).map(responsibilityTemplate).join('');
    }

    function lockMember(index) {
        const memberForm = document.querySelectorAll('.member-form')[index];
        const select = memberForm.querySelector('select');
        members[index] = {
            roll: select.value,
            locked: true
        };
        renderMembers();
        if (members.filter(member => member.locked).length > 0) {
            document.getElementById('responsibilitiesSection').style.display = 'block';
            renderResponsibilities();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('addMemberBtn').addEventListener('click', () => {
            if (members.length < maxMembers) {
                members.push({});
                renderMembers();
            } else {
                alert('Maximum 4 members allowed');
            }
        });

        document.getElementById('members').addEventListener('click', (e) => {
            if (e.target.classList.contains('lockMemberBtn')) {
                const index = [...document.querySelectorAll('.lockMemberBtn')].indexOf(e.target);
                lockMember(index);
            }
        });

        document.getElementById('lockGroupCreationDateBtn').addEventListener('click', () => {
            const groupCreationDateInput = document.getElementById('groupCreationDate');
            groupCreationDateInput.disabled = true;
            document.getElementById('lockGroupCreationDateBtn').disabled = true;
        });

        function fetchSupervisorApprovalStatus() {
            // Mocking the supervisor approval data fetch
            const supervisorApprovalData = {
                approved: true,
                approvalDate: '2024-01-10'
            };

            const supervisorApprovalStatusInput = document.getElementById('supervisorApprovalStatus');
            const supervisorApprovalDateDiv = document.getElementById('supervisorApprovalDateDiv');
            const supervisorApprovalDateInput = document.getElementById('supervisorApprovalDate');

            if (supervisorApprovalData.approved) {
                supervisorApprovalStatusInput.value = 'Approved';
                supervisorApprovalDateInput.value = supervisorApprovalData.approvalDate;
                supervisorApprovalDateDiv.style.display = 'block';
            } else {
                supervisorApprovalStatusInput.value = 'Not Approved';
                supervisorApprovalDateDiv.style.display = 'none';
            }
        }

        function fetchDecApprovalStatus() {
            // Mocking the DEC approval data fetch
            const decApprovalData = {
                approved: true,
                approvalDate: '2024-01-15'
            };

            const decApprovalStatusInput = document.getElementById('decApprovalStatus');
            const decApprovalDateDiv = document.getElementById('decApprovalDateDiv');
            const decApprovalDateInput = document.getElementById('decApprovalDate');

            if (decApprovalData.approved) {
                decApprovalStatusInput.value = 'Approved';
                decApprovalDateInput.value = decApprovalData.approvalDate;
                decApprovalDateDiv.style.display = 'block';
            } else {
                decApprovalStatusInput.value = 'Not Approved';
                decApprovalDateDiv.style.display = 'none';
            }
        }

        // Initial render and data fetch
        renderMembers();
        fetchSupervisorApprovalStatus();
        fetchDecApprovalStatus();
    });
</script>
</body>
</html>
