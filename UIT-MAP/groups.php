<?php
session_start();
error_reporting(0); //To hide the errors
include 'dbconnect.php'; //Database connection
require 's3client.php';

if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="mentor"){ //If the user is not admin or mentor, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
    header("location: index.php");
}

// To fetch mentors from the mentors table
$sql = "SELECT mname FROM mentors ORDER BY mname ASC";
$result = $conn->query($sql);

$mentors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mentors[] = $row['mname'];//mname is the column name in the mentors table
    }
}
$batchYear = null;
// Check whether batchyr is in the URL
if (!isset($_GET['year']) || empty($_GET['year'])) {
    $batchYear = $_SESSION['selected_year'];
}
else{
    // Extract the batch year from the URL and also store it in the session variable
    $batchYear = $_GET['year'];
    $_SESSION['selected_year'] = $batchYear;
}
// Sanitize the input to prevent SQL injection
$batchYear = mysqli_real_escape_string($conn, $batchYear);

//To fetch the group details of that particular batch year from the projinfo table
$groupExists=false;
if($_SESSION['usertype'] == "admin"){
    $sql = "SELECT * FROM projinfo WHERE batchyr ='$batchYear' ORDER BY number ASC"; 
}
else if($_SESSION['usertype'] == "mentor"){
    $sql = "SELECT * FROM projinfo WHERE batchyr ='$batchYear' AND mid='$_SESSION[username]' ORDER BY number ASC";
}
$groupResults = $conn->query($sql); //Executing the query
$groupRows = [];
if($groupResults->num_rows > 0){ //If there are groups in the projinfo table
    $groupExists=true;
    while($groupRow = $groupResults->fetch_assoc()){ //Fetching the group details from projinfo table- Gnum, Group ID, Batch, Title, Intro, Objective, Tech, Technology, Creator, Mentor, Mentor ID, Creation date, DEC Approval Date, and Mentor Approval Date
        $groupRows[] = $groupRow;
    }
}

//To fetch the group members details of that particular batch year from the groups table
$memberExists=false;
if($_SESSION['usertype'] == "admin"){
    $sql2 = "SELECT * FROM groups WHERE batchyr='$batchYear'"; 
}
else if($_SESSION['usertype'] == "mentor"){
    $sql2 = "SELECT * FROM groups WHERE gnum IN (SELECT gnum FROM projinfo WHERE mid='$_SESSION[username]') AND batchyr='$batchYear'"; //To fetch the group members details of the groups which are assigned to the mentor(as mentor name is not present in groups table so we're fetching indirectly from projinfo table)
}
$memberResults = $conn->query($sql2); //Executing the query
$memberRows = [];
if($memberResults->num_rows > 0){ //If there are group members in the groups table
    $memberExists=true;
    while($memberRow = $memberResults->fetch_assoc()){ //Fetching the group members details from groups table- Member's Roll Number, Member's Name, Batch, Section, Branch, Responsibility, Gnum, Creator and Creation Date
        $memberRows[] = $memberRow;
    }
}

//To fetch the weekly analysis details from the wanalysis table
$analysisExists=false;
if($_SESSION['usertype'] == "admin"){
    $sql3 = "SELECT * FROM wanalysis WHERE number IN (SELECT number FROM projinfo WHERE batchyr='$batchYear') ORDER BY weeknum ASC"; 
}
else if($_SESSION['usertype'] == "mentor"){
    $sql3 = "SELECT * FROM wanalysis WHERE number IN (SELECT number FROM projinfo WHERE mid='$_SESSION[username]' AND batchyr='$batchYear') ORDER BY weeknum ASC"; //To fetch the weekly analysis details of the groups which are assigned to the mentor(as mentor name is not present in wanalysis table so we're fetching indirectly from projinfo table)
}
$analysisResults = $conn->query($sql3); //Executing the query
$analysisRows = [];
if($analysisResults->num_rows > 0){ //If there are weekly analysis details in the wanalysis table
    $analysisExists=true;
    while($analysisRow = $analysisResults->fetch_assoc()){ //Slow Method: Fetching the weekly analysis details from wanalysis table- Group ID, Week Number, Summary, Performance, Date of Submission and Date of Evaluation
        $analysisRows[] = $analysisRow;
    }
}

//To fetch last date of all rubrics through batchyr
$lastDateSql = "SELECT lastR1, lastR2, lastR3, lastR4, lastR5, lastR6, lastR7, lastR8 FROM batches WHERE batchyr = '$batchYear'";
$lastDateResults = $conn->query($lastDateSql);
if ($lastDateResults->num_rows > 0) {
    $lastDate = $lastDateResults->fetch_assoc();
}

//To handle the incoming POST request and check if the request is to change the mentor or delete the group
if($_SERVER['REQUEST_METHOD'] === 'POST'){ //If the request method is POST
    $data = json_decode(file_get_contents('php://input'), true); //Decode the JSON payload sent from the client side
    $action = $data['action']; //Get the action from the decoded JSON payload
    $gnum = $data['gnum'];
    // var_dump($gnum);

    //To handle the change mentor action
    if ($action === 'change') {
        $mentor = $data['mentor']; // Get the selected mentor

        $mIdQuery= "SELECT mid FROM mentors where mname='$mentor'";//Get mentor ID of the selected mentor
        $mIdResults= $conn->query($mIdQuery);
        $mId= $mIdResults->fetch_assoc()['mid'];//As mid is the name of the column in the mentors table whose value is stored in the $mIdResults variable

        // Update the mentor, its Id and DEC approval date for the group in the 'projinfo' table using gnum. DEC approval date also bcoz at the time when DEC allotted mentor it also approved the grp.
        $updateMentor = "UPDATE projinfo SET mentor = '$mentor', mid = '$mId', dAppDate = CURDATE() WHERE gnum = '$gnum'";
        $stmt = $conn->query($updateMentor);

        if(!$stmt){
            echo json_encode(['success' => false, 'message' => 'Error changing mentor!']);
        }
        else{
            echo json_encode(['success' => true, 'message' => 'Mentor changed successfully!']);
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
        exit;
    }
    //To handle the delete action
    else if($action === 'delete'){
        // Delete group members from 'groups' table
        $deleteGroupMembers = "DELETE FROM groups WHERE gnum = '$gnum'";
        $stmt1 = $conn->query($deleteGroupMembers);//Execute the query

        // Delete group info from 'projinfo' table
        $deleteGroupInfo = "DELETE FROM projinfo WHERE gnum = '$gnum'";
        $stmt2 = $conn->query($deleteGroupInfo);

        if(!$stmt1 || !$stmt2){
            echo json_encode(['success' => false, 'message' => 'Error deleting group!']);
        }
        else{
            echo json_encode(['success' => true, 'message' => 'Group deleted successfully!']);
        }
        // Close the prepared statements and connection
        $stmt1->close();
        $stmt2->close();
        $conn->close();
        exit;
    }
    //To handle the weekly performance action
    else if($action === 'weekperformance'){
        $groupId = $data['groupId'];
        $weekNum = $data['weekNum'];
        $summary = $data['summary'];
        $performance = $data['performance'];

        // Update the weekly analysis data in the 'wanalysis' table
        $updateAnalysis = "UPDATE wanalysis SET performance = '$performance', deval = CURDATE() WHERE number = '$groupId' AND weeknum = $weekNum";
        $stmt = $conn->query($updateAnalysis);
        // $stmt->bind_param("sssi", $summary, $performance, date('Y-m-d'), $groupId, $weekNum);
        // $stmt->execute();

        // if ($stmt->affected_rows > 0) {
        //     header('Content-Type: text/plain');
        //     echo 'success=true';
        // } 
        // echo 'success=true';
        echo json_encode(['success' => true, 'message' => 'Data inserted successfully!']);
        // Close the statement and connection
        // $stmt->close();
        $conn->close();
        exit;
    }
    //To handle the rubrics review action
    else if($action == 'rubricsreview'){
        $rubric = $data['rubric'];
        $examiner = $data['examiner'];
        $status = $data['status'];
        // Determine the column names dynamically based on the rubric number
        $columnExaminer = "examinerR$rubric";
        $columnStatus = "statusR$rubric";
        $columnEval = "evalR$rubric";
        // Update the projinfo table
        $sql = "UPDATE projinfo SET $columnExaminer = ?, $columnStatus = ?, $columnEval = CURDATE() WHERE gnum = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $examiner, $status, $gnum);
        $stmt->execute();
        
        if ($stmt) {
            echo json_encode(["success" => true, "message" => "Rubric updated successfully!"]);
        }
        else {
            echo json_encode(["success" => false, "message" => $conn->error]);
        }
        $conn->close();
        exit;
    }
    else if($action =='rubricsmarks'){
        $rubric = $data['rubric'];
        $rubricData = $data['rubricData'];

        $errors = [];

        // Loop through each part and update the database
        foreach ($rubricData as $rubricPartData) {
            $part = $rubricPartData['part'];
            $members = $rubricPartData['members'];

            foreach ($members as $member) {
                $roll = $conn->real_escape_string($member['roll']);
                $score = $conn->real_escape_string($member['score']);
                $rubricColumn = $conn->real_escape_string($part);

                // Update the score for each member in the database
                $updateQuery = "UPDATE groups SET $rubricColumn = '$score' WHERE gnum = '$gnum' AND roll = '$roll'";
                if (!$conn->query($updateQuery)) {
                    $errors[] = "Failed to update $rubricColumn for roll $roll: " . $conn->error;
                }
            }
        }
        // Return response
        if (empty($errors)) {
            echo json_encode(["success" => true, "message" => "Rubric marks updated successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => implode(", ", $errors)]);
        }
        exit;
    }
    //To handle the delete document action
    else if($action == 'deleteDocument'){

        $fileUrl = $data['fileUrl'];
        $gnum = $data['gnum'];
        $columnName = $data['columnName'];

        // Extract the filename (Key) from the URL
        $key = basename($fileUrl);
        $finalKey = $batchYear . '/' .$key;
        // Delete from Tebi
        $s3Client->deleteObject([
            'Bucket' => $TEBI_BUCKET,
            'Key' => $finalKey
        ]);

        // Update database to remove the URL
        $sql = "UPDATE projinfo SET $columnName = NULL WHERE gnum = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $gnum);
        $stmt->execute();
        if ($stmt) {
            echo json_encode(["success" => true, "message" => "Document deleted successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Something went wrong! Please try again."]);
        }
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
    <title>MAP - Groups</title>
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
            margin: 1% auto;
            padding: 5px;
            border: 1px solid #888;
            width: 100%;
            border-radius: 8px;
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

        /* Spinner styles */
        .spinner-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            <center><h2 class="text-2xl font-bold mb-6">Student Groups (<?php echo $batchYear-4; ?>-<?php echo $batchYear; ?>)</h2></center>

            <!-- Filter Box -->
            <div class="mb-6 flex justify-between items-center">
                <input type="text" id="searchInput" placeholder="Search for groups by Group ID, Project Title, or Group Leader..." class="w-full p-2 border rounded">
                    <!-- <label class="ml-4 flex items-center">
                        <input type="checkbox" id="showApprovedCheckbox" class="mr-2">
                        <span>Show Approved Groups</span> -->
                    </label>
            </div>

            <!-- Group List -->
            <div class="table-container bg-white mb-8 rounded-lg shadow-lg">
                <table class="w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-center w-12">Group ID</th>
                            <th class="px-4 py-2 text-center w-1/3">Project Title</th>
                            <th class="px-4 py-2 text-center w-1/3">Group Leader</th>
                            <?php if($_SESSION['usertype'] == "admin"){ ?>
                                <th class="px-4 py-2 text-center w-1/5">Mentor Assigned</th>
                            <?php } ?>
                            <th class="px-4 py-2 text-center w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="groupTable" >
                        <!-- Rows will be dynamically added here by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Spinner Overlay -->
    <div id="spinner" class="spinner-overlay">
        <div class="spinner"></div>
    </div>

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
            <center><h1 class="text-3xl font-bold mb-4">Weekly Analysis</h1></center>
            <hr class="my-8 border-gray-300">
            <div id="weeklyAnalysisContent">
                <!-- Weekly analysis content will be dynamically added here -->
            </div>
        </div>
    </div>
    <!-- Modal for Rubrics Review -->
    <div id="rubricsReviewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <center><h1 class="text-3xl font-bold mb-4">&nbsp; Rubrics Review</h1></center>
            <div id="rubricsReviewContent">
                <!-- Rubrics review content will be dynamically added here -->
            </div>
        </div>
    </div>

    <script>
        const groupRows = <?php echo json_encode($groupRows); ?>; //Fetching the group details from projinfo table- Gnum, Group ID, Batch, Title, Intro, Objective, Tech, Technology, Creator, Mentor, Mentor ID, Creation date, DEC Approval Date, and Mentor Approval Date
        const memberRows = <?php echo json_encode($memberRows); ?>; //Fetching the group members details from groups table- Member's Roll Number, Member's Name, Batch, Section, Branch, Responsibility, Gnum, Creator and Creation Date
        const analysisRows = <?php echo json_encode($analysisRows); ?>; //Fetching the weekly analysis details from wanalysis table- Group ID, Week Number, Summary, Performance, Submission Date, Evaluation Date
        const lastDate = <?php echo json_encode($lastDate); ?>;
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
                        <button onclick="changeMentor(this)" style="visibility: hidden; margin-top: 5px;" class="bg-green-500 text-white py-1 px-3 rounded hover:bg-green-800 transition duration-300">Change Mentor</button>
                    </td>
                    ` : ''}
                    <td class="border px-4 py-2 text-center">
                        <button onclick="openWeeklyAnalysisModal('${group.number}')" class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-800 transition duration-300" style="min-width: 140px;">Weekly Analysis</button>
                        <button onclick="openRubricsReviewModal('${group.number}')" class="bg-green-500 text-white py-1 px-3 rounded hover:bg-green-800 transition duration-300" style="min-width: 140px; margin-top: 5px;">Rubrics Review</button>
                        ${userType == "admin" ? `
                            <button onclick="deleteGroup(this)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-800 transition duration-300" style="min-width: 140px; margin-top: 5px;">Delete</button>
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
            const groupId = event.target.closest('tr').querySelector('.group-number').textContent;//To get the group ID of the group whose title is clicked
            const group = groupRows.find(group => group.number == groupId);//This 'group' variable contains all the details of that group whose title is clicked which we have filtered out from rest of the groups using that group ID
            const members = memberRows.filter(member => member.gnum == group.gnum);//This 'member' variable contains all the member details of that group whose title is clicked which we have filtered out using the gnum

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
                            ${members.map((member, index) => `
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
                    <p class="text-gray-700"><strong>Group Creation Date (yyyy-mm-dd):</strong>  ${group.date}</p>
                    <p class="text-gray-700"><strong>DEC Approval Date (yyyy-mm-dd):</strong> ${group.dAppDate}</p>
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
            modal.scrollTop = 0;
        }

        // Function to open the weekly analysis modal
        function openWeeklyAnalysisModal(groupId) {
            console.log('Opening weekly analysis for group:', groupId); // Debugging line
            const analysis = analysisRows.filter(analysis => analysis.number == groupId);
            const group = groupRows.find(group => group.number == groupId);
            const gnum = group.gnum;
            const modal = document.getElementById('weeklyAnalysisModal');
            const modalContent = document.getElementById('weeklyAnalysisContent');
            modalContent.innerHTML = ''; // Clear existing content

            if (analysis.length > 0) {
                // Get the maximum week number from the analysis data
                const maxWeek = Math.max(...analysis.map(item => item.weeknum));

                // Loop through the weeks to render the form for each week
                for (let week = 1; week <= maxWeek; week++) {
                    const weekData = analysis.find(item => item.weeknum == week);
                    const weekDiv = document.createElement('div');
                    weekDiv.classList.add('mb-4');
                    weekDiv.innerHTML = `
                        <h3 class="text-2xl font-semibold text-gray-800 mb-6"><center>Week ${week}</center></h3>
                        <label class="block font-bold mb-2">Weekly Summary:</label>
                        <textarea class="w-full p-2 border rounded mb-2" rows="3" disabled>${weekData?.summary}</textarea>
                        <label class="block font-bold mb-2">Performance:</label>
                        <select class="w-full p-2 border rounded mb-2"}>
                            <option value="">Select...</option>
                            <option value="Satisfactory" ${weekData?.performance === 'Satisfactory' ? 'selected' : ''}>Satisfactory</option>
                            <option value="Unsatisfactory" ${weekData?.performance === 'Unsatisfactory' ? 'selected' : ''}>Unsatisfactory</option>
                        </select>
                        <label class="block font-bold mb-2">Submission Date:</label>
                        <input type="date" class="w-full p-2 border rounded mb-2" value="${weekData?.dsub}" disabled>
                        <label class="block font-bold mb-2">Evaluation Date:</label>
                        <input type="date" class="w-full p-2 border rounded mb-2" value="${weekData?.deval || ''}" disabled>
                            <center>
                                <button class="bg-blue-500 text-white py-2 px-4 rounded mt-4 save-btn hover:bg-blue-800 transition duration-300" style="min-width: 140px;"
                                    data-group-id="${groupId}" 
                                    data-week-num="${week}">Save
                                </button>
                            </center>
                        <hr class="my-8 border-gray-300">
                    `;
                    modalContent.appendChild(weekDiv);
                }
            } else {
                const weekDiv = document.createElement('div');
                weekDiv.classList.add('text-center', 'text-gray-700', 'font-bold', 'p-4');
                weekDiv.innerHTML = `
                    <p>No weekly analysis has been submitted by the student yet.</p>
                `;
                modalContent.appendChild(weekDiv);
            }

            modal.style.display = 'block';
            modal.scrollTop = 0;

            // Attach event listeners to the save buttons to save the weekly analysis data
            const saveButtons = modal.querySelectorAll('.save-btn');
            saveButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const weekNum = this.getAttribute('data-week-num');
                    const groupId = this.getAttribute('data-group-id');
                    const weekDiv = this.closest('div');
                    const spinner = document.getElementById('spinner');

                    const summary = weekDiv.querySelector('textarea').value.trim();
                    const performance = weekDiv.querySelector('select').value;

                    // Show spinner and disable button
                    if (spinner) spinner.style.display = 'flex';
                    if (this) this.disabled = true;
                    if (this) this.classList.add('opacity-50', 'cursor-not-allowed');

                    // Prepare the data to send to the server
                    const requestData = {
                        groupId: groupId,
                        gnum: gnum,
                        weekNum: parseInt(weekNum, 10),//Convert the week number to integer with base 10
                        summary: summary,
                        performance: performance,
                        action: 'weekperformance',
                    };

                    // Save the modal state and position to local storage before sending the data as after it is sent, the page will reload
                    localStorage.setItem('modalState', 'open');
                    localStorage.setItem('modalPosition', document.getElementById('weeklyAnalysisModal').scrollTop);
                    localStorage.setItem('groupId', groupId);

                    // Send the data to the server via Fetch API
                    fetch('groups.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`Details saved successfully for Week ${weekNum}!`);
                            window.location.reload();
                        } else {
                            throw new Error(`Error saving details for Week ${weekNum}: ${data.error}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.message || 'An error occurred while saving details.');
                    })
                    .finally(() => {
                        // Hide spinner and enable button
                        if (spinner) spinner.style.display = 'none';
                        if (this) this.disabled = false;
                        if (this) this.classList.remove('opacity-50', 'cursor-not-allowed');
                    });
                });
            });
            // Function to clear modal state
            function clearModalState() {
                localStorage.removeItem('modalState');
                localStorage.removeItem('modalPosition');
                localStorage.removeItem('groupId');
            }

            // Clear the modal state from local storage when the modal is closed
            document.querySelector('.close').addEventListener('click', () => {
                clearModalState();
                document.getElementById('weeklyAnalysisModal').style.display = 'none';
            });

            // Clear the modal state when clicking outside the modal content
            window.addEventListener('click', (event) => {
                const modal = document.getElementById('weeklyAnalysisModal');
                if (event.target === modal) {
                    clearModalState();
                    modal.style.display = 'none';
                }
            });
        }
        //Function to check whether the modal(weekly/rubrics) state is saved in local storage or not means whether the modal previously was closed by the user or due to page reload, and if it was closed due to page reload then we have to open it again
        document.addEventListener('DOMContentLoaded', () => {
            // Check if the modal state is saved in local storage
            const modalState = localStorage.getItem('modalState');
            const modalPosition = localStorage.getItem('modalPosition');
            const rmodalState = localStorage.getItem('rmodalState');
            const rmodalPosition = localStorage.getItem('rmodalPosition');
            const savedGroupId = localStorage.getItem('groupId');

            if (modalState === 'open' && savedGroupId) {// Means weekly analysis modal is open and group id is saved in local storage
                const modal = document.getElementById('weeklyAnalysisModal');
                openWeeklyAnalysisModal(savedGroupId);

                // Restore the modal position if saved
                if (modalPosition) {
                    modal.scrollTop = parseInt(modalPosition, 10);
                }
            }
            else if(rmodalState === 'open' && savedGroupId){// Means rubrics review modal is open and group id is saved in local storage
                const rmodal = document.getElementById('rubricsReviewModal');
                openRubricsReviewModal(savedGroupId);

                // Restore the modal position if saved
                if (rmodalPosition) {
                    rmodal.scrollTop = parseInt(rmodalPosition, 10);
                }
            }
        });

        // Function to handle custom encoding while preserving spaces
        function customEncode(str) {
            if (!str) return "";
            return str.split(' ').map(part => 
                encodeURIComponent(part)
            ).join(' ');
        }

        // Function to handle custom decoding while preserving spaces
        function customDecode(str) {
            if (!str) return "";
            return str.split(' ').map(part => 
                decodeURIComponent(part)
            ).join(' ');
        }

        // Function to open the rubrics review modal
        function openRubricsReviewModal(groupNumber) {
            console.log('Button clicked for group:', groupNumber); // Debugging line
            const group = groupRows.find(group => group.number == groupNumber);//We're receiving groupNumber as parameter when rubricsreview button is clicked and now we are finding that group which have same group number in the database using this line
            const members = memberRows.filter(member => member.gnum == group.gnum);//This 'member' variable contains all the member details of that group whose title is clicked which we have filtered out using the gnum
            const numberOfMembers = members.length;
            localStorage.setItem('groupId', groupNumber);
            const modal = document.getElementById('rubricsReviewModal');
            const modalContent = document.getElementById('rubricsReviewContent');
            modalContent.innerHTML = ''; // Clear existing content

            // Populate modal with rubrics review fields
            for (let i = 1; i <= 8; i++) {
                const rubricDiv = document.createElement('div');    
                rubricDiv.classList.add('mb-4', 'rubric-section');
                // Determine existing URLs from the database
                let pptUrl = null;
                let pdfUrl = null;
                if (i == 2 || i == 6) {
                    pptUrl = group[`r${i}ppt`];
                    pdfUrl = group[`r${i}pdf`];
                }
                rubricDiv.innerHTML = `
                    <div class="bg-beige shadow-xl rounded-xl p-6 mb-4 max-h-[500px] overflow-y-auto border-t-4 border-indigo-400">
                        <h3 class="text-2xl font-semibold text-gray-800 mb-6"><center>Rubric R${i}</center></h3>
                        ${i === 2 || i === 6 ? `
                            <!-- Upload PPT -->
                            <div class="mb-5">
                                <label class="block text-gray-700 font-medium mb-2">View Presentation Slides:</label>
                                ${pptUrl ? `
                                    <a onclick="openDocument('${pptUrl}')" class="inline-flex items-center px-4 py-2 w-48 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                        </svg>Uploaded Slides
                                    </a>
                                    <!-- Delete Button -->
                                    <button onclick="deleteDocument('${pptUrl}')"
                                            class="inline-flex items-center px-4 py-2 w-48 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12H6L5 7m5 4v4m4-4v4m1-9h3m-8 0H6m3-4h6a2 2 0 012 2v1H7V5a2 2 0 012-2z" />
                                        </svg>
                                        Delete Slides
                                    </button>
                                `: `
                                    <h3 class="text-lg font-bold text-blue-700 mb-2">No Slides Submitted</h3>
                                `}
                            </div>
                            <!-- Upload Report -->
                            <div class="mb-5">
                                <label class="block text-gray-700 font-medium mb-2">View Report:</label>
                                ${pdfUrl ? `
                                    <a onclick="openDocument('${pdfUrl}')" class="inline-flex items-center px-4 py-2 w-48 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>Uploaded Report
                                    </a>
                                    <!-- Delete Button -->
                                    <button onclick="deleteDocument('${pdfUrl}')"
                                            class="inline-flex items-center px-4 py-2 w-48 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12H6L5 7m5 4v4m4-4v4m1-9h3m-8 0H6m3-4h6a2 2 0 012 2v1H7V5a2 2 0 012-2z" />
                                        </svg>
                                        Delete Report
                                    </button>                        
                                `: `
                                    <h3 class="text-lg font-bold text-blue-700 mb-2">No Report Submitted</h3>
                                `}
                            </div>
                        ` : ''}
                        <!-- Last Date -->
                        <div class="mb-5">
                            <label for="last-date" class="block text-gray-700 font-medium mb-2">Last Date:</label>
                            <input id="last-date" type="date" value="${lastDate[`lastR${i}`] || ""}" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" disabled>
                        </div>

                        <!-- Examiner Name -->
                        <div class="mb-5">
                            <label for="examiner" class="block text-gray-700 font-medium mb-2">Examiner Name:</label>
                            <input id="examiner-${i}" type="text" value="${customEncode(group[`examinerR${i}`] || "")}" 
                                   oninput="this.value = customDecode(this.value)"
                                   class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" maxlength="28">
                        </div>

                        <!-- Status -->
                        <div class="mb-5">
                            <label for="status" class="block text-gray-700 font-medium mb-2">Status:</label>
                            <select id="status-${i}" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg">
                                <option value="">Select...</option>
                                <option value="Completed" ${group[`statusR${i}`] === "Completed" ? "selected" : ""}>Completed</option>
                                <option value="Not Completed" ${group[`statusR${i}`] === "Not Completed" ? "selected" : ""}>Not Completed</option>
                            </select>
                        </div>

                        <!-- Evaluation Date -->
                        <div class="mb-5">
                            <label for="evaluation-date" class="block text-gray-700 font-medium mb-2">Evaluation Date:</label>
                            <input id="evaluation-${i}" value="${group[`evalR${i}`] || ""}" type="date" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" disabled>
                        </div>
                        <center><button id="saveRubricsReview" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-800 transition duration-300 save-btn" data-rubric="${i}" style="min-width: 140px;">Save Status</button></center>
                    </div>
                    
                    <div class="table-container overflow-auto mb-8 shadow-lg rounded-lg border border-gray-200">
                    <table class="min-w-full bg-white rounded-lg text-gray-700" style="font-size: 15px !important;">
                        ${i === 1 ? `
                            <thead class="bg-blue-100 rounded-t-lg">
                                <tr>
                                    <th class="bg-blue-200 px-6 py-3 border-b-2 font-medium uppercase tracking-wider" style="font-size: 17px !important;" colspan="6">Level of Achievement</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Review Cases</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Good (6)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Average (5)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Poor (4)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Student</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Identification of Problem Domain and Detailed Analysis</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Detailed and extensive explanation of the purpose and need of the project</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Average explanation of the purpose and need of the project </td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Minimal explanation of the purpose and need of the project </td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r11-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="6" ${Number(members[0].r11) === 6 ? 'selected' : ''}>6</option> //Even the r11 in db is stored as integer but when we use it in JS it is sometimes stored as string so we need to convert it to number using Number() function to compare with numbers 6,5,4
                                            <option value="5" ${Number(members[0].r11) === 5 ? 'selected' : ''}>5</option>
                                            <option value="4" ${Number(members[0].r11) === 4 ? 'selected' : ''}>4</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center></td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r11-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="6" ${Number(member.r11) === 6 ? 'selected' : ''}>6</option> //Even the r11 in db is stored as integer but when we use it in JS it is sometimes stored as string so we need to convert it to number using Number() function to compare with numbers 6,5,4
                                                <option value="5" ${Number(member.r11) === 5 ? 'selected' : ''}>5</option>
                                                <option value="4" ${Number(member.r11) === 4 ? 'selected' : ''}>4</option>
                                            </select>
                                        </td>                                        
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Study of the Existing Systems and Feasibility of Project Proposal</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Detailed and extensive explanation of the specifications and the limitations of the existing systems</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Moderate study of the existing systems; collects some basic information</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Minimal explanation of the specifications and the limitations of the existing systems; incomplete information</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r12-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="6" ${Number(members[0].r12) === 6 ? 'selected' : ''}>6</option> 
                                            <option value="5" ${Number(members[0].r12) === 5 ? 'selected' : ''}>5</option>
                                            <option value="4" ${Number(members[0].r12) === 4 ? 'selected' : ''}>4</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r12-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="6" ${Number(member.r12) === 6 ? 'selected' : ''}>6</option> 
                                                <option value="5" ${Number(member.r12) === 5 ? 'selected' : ''}>5</option>
                                                <option value="4" ${Number(member.r12) === 4 ? 'selected' : ''}>4</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Objectives and Methodology of the Proposed Work</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">All objectives of the proposed work are well defined; steps to be followed to solve the defined problem are clearly specified</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Incomplete justification to the objectives proposed; steps are mentioned but unclear; without justification to objectives</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Objectives of the proposed work are either not identified or not well defined; incomplete and improper specification</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r13-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="6" ${Number(members[0].r13) === 6 ? 'selected' : ''}>6</option> 
                                            <option value="5" ${Number(members[0].r13) === 5 ? 'selected' : ''}>5</option>
                                            <option value="4" ${Number(members[0].r13) === 4 ? 'selected' : ''}>4</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r13-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="6" ${Number(member.r13) === 6 ? 'selected' : ''}>6</option> 
                                                <option value="5" ${Number(member.r13) === 5 ? 'selected' : ''}>5</option>
                                                <option value="4" ${Number(member.r13) === 4 ? 'selected' : ''}>4</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" colspan="6">
                                        <center>
                                            <button id="saveRubricsMarks" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-800 transition duration-300 save-mbtn" data-rubric="1" style="min-width: 140px;">Save Score</button>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>        
                        ` : ''}
                        ${i === 2 ? `
                            <thead class="bg-blue-100 rounded-t-lg">
                                <tr>
                                    <th class="bg-blue-200 px-6 py-3 border-b-2 font-medium uppercase tracking-wider" style="font-size: 17px !important;" colspan="7">Level of Achievement</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Review Cases</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Excellent (8)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Good (7)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Average (6)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Poor (5)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Student</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Project Synopsis Report</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Project Synopsis report is according to the specified format</li><li>References and citations are appropriate and well mentioned</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Project Synopsis report is according to the specified format</li><li>References and citations are appropriate but not mentioned well</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Project Synopsis report is according to the specified format but with some mistakes</li><li>Insufficient references and citations</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Project Synopsis report not prepared according to the specified format</li><li>References and citations are not appropriate</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r21-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="8" ${Number(members[0].r21) === 8 ? 'selected' : ''}>8</option>
                                            <option value="7" ${Number(members[0].r21) === 7 ? 'selected' : ''}>7</option>
                                            <option value="6" ${Number(members[0].r21) === 6 ? 'selected' : ''}>6</option> 
                                            <option value="5" ${Number(members[0].r21) === 5 ? 'selected' : ''}>5</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r21-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="8" ${Number(member.r21) === 8 ? 'selected' : ''}>8</option>
                                                <option value="7" ${Number(member.r21) === 7 ? 'selected' : ''}>7</option>
                                                <option value="6" ${Number(member.r21) === 6 ? 'selected' : ''}>6</option> 
                                                <option value="5" ${Number(member.r21) === 5 ? 'selected' : ''}>5</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Description of Concepts and Technical Details</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Complete explanation of the key concepts</li><li>Strong description of the technical requirements of the project</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Complete explanation of the key concepts</li><li>Insufficient description of the technical requirements of the project</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Complete explanation of the key concepts but little relevance to literature</li><li>Insufficient description of the technical requirements of the project</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Inappropiate explanation of the key concepts</li><li>Poor description of the technical requirements of the project</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r22-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="8" ${Number(members[0].r22) === 8 ? 'selected' : ''}>8</option>
                                            <option value="7" ${Number(members[0].r22) === 7 ? 'selected' : ''}>7</option>
                                            <option value="6" ${Number(members[0].r22) === 6 ? 'selected' : ''}>6</option> 
                                            <option value="5" ${Number(members[0].r22) === 5 ? 'selected' : ''}>5</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r22-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="8" ${Number(member.r22) === 8 ? 'selected' : ''}>8</option>
                                                <option value="7" ${Number(member.r22) === 7 ? 'selected' : ''}>7</option>
                                                <option value="6" ${Number(member.r22) === 6 ? 'selected' : ''}>6</option> 
                                                <option value="5" ${Number(member.r22) === 5 ? 'selected' : ''}>5</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Planning of Project Work and Team Structure</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Time frame properly specified and being followed</li><li>Appropriate distribution of project work</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Time frame properly specified and being followed</li><li>Distribution of project work inappropriate</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Time frame properly specified, but not being followed</li><li>Distribution of project work uneven</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Time frame not properly specified</li><li>Inappropriate distribution of project work</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r23-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="8" ${Number(members[0].r23) === 8 ? 'selected' : ''}>8</option>
                                            <option value="7" ${Number(members[0].r23) === 7 ? 'selected' : ''}>7</option>
                                            <option value="6" ${Number(members[0].r23) === 6 ? 'selected' : ''}>6</option> 
                                            <option value="5" ${Number(members[0].r23) === 5 ? 'selected' : ''}>5</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r23-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="8" ${Number(member.r23) === 8 ? 'selected' : ''}>8</option>
                                                <option value="7" ${Number(member.r23) === 7 ? 'selected' : ''}>7</option>
                                                <option value="6" ${Number(member.r23) === 6 ? 'selected' : ''}>6</option> 
                                                <option value="5" ${Number(member.r23) === 5 ? 'selected' : ''}>5</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" colspan="7">
                                        <center>
                                            <button id="saveRubricsMarks" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-800 transition duration-300 save-mbtn" data-rubric="2" style="min-width: 140px;">Save Score</button>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>
                        ` : ''}
                        ${i === 3 ? `
                            <thead class="bg-blue-100 rounded-t-lg">
                                <tr>
                                    <th class="bg-blue-200 px-6 py-3 border-b-2 font-medium uppercase tracking-wider" style="font-size: 17px !important;" colspan="6">Level of Achievement</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Review Cases</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Good (4)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Average (3)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Poor (2)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Student</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Working within a Team</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Collaborates and communicates in a group situation and integrates the views of others</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Exchanges some views but requires guidance to collaborate with others</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Makes little or no attempt to collaborate in a group situation</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r31-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="4" ${Number(members[0].r31) === 4 ? 'selected' : ''}>4</option>
                                            <option value="3" ${Number(members[0].r31) === 3 ? 'selected' : ''}>3</option>
                                            <option value="2" ${Number(members[0].r31) === 2 ? 'selected' : ''}>2</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r31-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="4" ${Number(member.r31) === 4 ? 'selected' : ''}>4</option>
                                                <option value="3" ${Number(member.r31) === 3 ? 'selected' : ''}>3</option>
                                                <option value="2" ${Number(member.r31) === 2 ? 'selected' : ''}>2</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Regularity</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Reports to the guide regularly and consistent in work</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Not very regular but consistent in the work</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Irregular in attendance and inconsistent in work</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r32-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="4" ${Number(members[0].r32) === 4 ? 'selected' : ''}>4</option>
                                            <option value="3" ${Number(members[0].r32) === 3 ? 'selected' : ''}>3</option>
                                            <option value="2" ${Number(members[0].r32) === 2 ? 'selected' : ''}>2</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r32-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="4" ${Number(member.r32) === 4 ? 'selected' : ''}>4</option>
                                                <option value="3" ${Number(member.r32) === 3 ? 'selected' : ''}>3</option>
                                                <option value="2" ${Number(member.r32) === 2 ? 'selected' : ''}>2</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" colspan="6">
                                        <center>
                                            <button id="saveRubricsMarks" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-800 transition duration-300 save-mbtn" data-rubric="3" style="min-width: 140px;">Save Score</button>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>        
                        ` : ''}
                        ${i === 4 ? `
                            <thead class="bg-blue-100 rounded-t-lg">
                                <tr>
                                    <th class="bg-blue-200 px-6 py-3 border-b-2 font-medium uppercase tracking-wider" style="font-size: 17px !important;" colspan="7">Level of Achievement</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Review Cases</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Excellent (50)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Good (45)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Average (40)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Poor (35)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Student</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Design Methodology </b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Divison of problem into modules and good selection of computing framework</li><li>Appropriate design methodology and properly justification</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Divison of problem into modules and good selection of computing framework</li><li>Design methodology not properly justified</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Divison of problem into modules but inappropriate selection of computing framework</li><li>Design methodology not defined properly</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Modular approach not adopted </li><li>Design methodology not defined</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r41-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="50" ${Number(members[0].r41) === 50 ? 'selected' : ''}>50</option>
                                            <option value="45" ${Number(members[0].r41) === 45 ? 'selected' : ''}>45</option>
                                            <option value="40" ${Number(members[0].r41) === 40 ? 'selected' : ''}>40</option> 
                                            <option value="35" ${Number(members[0].r41) === 35 ? 'selected' : ''}>35</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r41-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="50" ${Number(member.r41) === 50 ? 'selected' : ''}>50</option>
                                                <option value="45" ${Number(member.r41) === 45 ? 'selected' : ''}>45</option>
                                                <option value="40" ${Number(member.r41) === 40 ? 'selected' : ''}>40</option> 
                                                <option value="35" ${Number(member.r41) === 35 ? 'selected' : ''}>35</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Demonstration and Presentation </b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Objectives achieved as per time frame</li><li>Contents of presentations are appropriate and well arranged</li><li>Proper eye contact with audience and clear voice with good spoken language </li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Objectives achieved as per time frame</li><li>Contents of presentations are appropriate but not well arranged</li><li>Satisfactory demonstration, clear voice with good spoken language but eye contact not proper</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Objectives achieved as per time frame</li><li>Contents of presentations are appropriate but not well arranged</li><li>Presentation not satisfactory and average demonstration</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>No objectives achieved</li><li>Contents of presentations are not appropriate and not well delivered </li><li>Poor delivery of presentation</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r42-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="50" ${Number(members[0].r42) === 50 ? 'selected' : ''}>50</option>
                                            <option value="45" ${Number(members[0].r42) === 45 ? 'selected' : ''}>45</option>
                                            <option value="40" ${Number(members[0].r42) === 40 ? 'selected' : ''}>40</option> 
                                            <option value="35" ${Number(members[0].r42) === 35 ? 'selected' : ''}>35</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r42-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="50" ${Number(member.r42) === 50 ? 'selected' : ''}>50</option>
                                                <option value="45" ${Number(member.r42) === 45 ? 'selected' : ''}>45</option>
                                                <option value="40" ${Number(member.r42) === 40 ? 'selected' : ''}>40</option> 
                                                <option value="35" ${Number(member.r42) === 35 ? 'selected' : ''}>35</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" colspan="7">
                                        <center>
                                            <button id="saveRubricsMarks" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-800 transition duration-300 save-mbtn" data-rubric="4" style="min-width: 140px;">Save Score</button>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>
                        ` : ''}
                        ${i === 5 ? `
                            <thead class="bg-blue-100 rounded-t-lg">
                                <tr>
                                    <th class="bg-blue-200 px-6 py-3 border-b-2 font-medium uppercase tracking-wider" style="font-size: 17px !important;" colspan="7">Level of Achievement</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Review Cases</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Excellent (50)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Good (45)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Average (40)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Poor (35)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Student</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Incorporation of Suggestions</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Changes are made as per modifications suggested during mid term evaluation and new innovations added</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Changes are made as per modifications suggested during mid term evaluation and good justification</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Few changes are made as per modifications suggested during mid term evaluation</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Suggestions during mid term evaluation are not incorporated</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r51-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="50" ${Number(members[0].r51) === 50 ? 'selected' : ''}>50</option>
                                            <option value="45" ${Number(members[0].r51) === 45 ? 'selected' : ''}>45</option>
                                            <option value="40" ${Number(members[0].r51) === 40 ? 'selected' : ''}>40</option> 
                                            <option value="35" ${Number(members[0].r51) === 35 ? 'selected' : ''}>35</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r51-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="50" ${Number(member.r51) === 50 ? 'selected' : ''}>50</option>
                                                <option value="45" ${Number(member.r51) === 45 ? 'selected' : ''}>45</option>
                                                <option value="40" ${Number(member.r51) === 40 ? 'selected' : ''}>40</option> 
                                                <option value="35" ${Number(member.r51) === 35 ? 'selected' : ''}>35</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Project Demonstration </b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>All defined objectives are achieved</li><li>Each module working well and properly demonstrated</li><li>All modules of project are well integrated and system working is accurate</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>All defined objectives are achieved</li><li>Each module working well and properly demonstrated</li><li>Integration of all modules not done and system working is not veey satisfactory</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Some of the defined objectives are achieved</li><li>Modules are working well in isolation and properly demonstrated</li><li>Modules of project are not properly integrated</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Defined objectives are not achieved</li><li>Modules are not in proper working form that further leads to failure of integrated system</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r52-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="50" ${Number(members[0].r52) === 50 ? 'selected' : ''}>50</option>
                                            <option value="45" ${Number(members[0].r52) === 45 ? 'selected' : ''}>45</option>
                                            <option value="40" ${Number(members[0].r52) === 40 ? 'selected' : ''}>40</option> 
                                            <option value="35" ${Number(members[0].r52) === 35 ? 'selected' : ''}>35</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r52-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="50" ${Number(member.r52) === 50 ? 'selected' : ''}>50</option>
                                                <option value="45" ${Number(member.r52) === 45 ? 'selected' : ''}>45</option>
                                                <option value="40" ${Number(member.r52) === 40 ? 'selected' : ''}>40</option> 
                                                <option value="35" ${Number(member.r52) === 35 ? 'selected' : ''}>35</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Presentation</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Contents of presentations are appropriate and well delivered</li><li>Proper eye contact with audience and clear voice with good spoken language</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Contents of presentations are appropriate and well delivered</li><li>Clear voice with good spoken language but less eye contact with audience</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Contents of presentations are not appropriate</li><li>Eye contact with few people and unclear voice</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Contents of presentations are not appropriate and not well delivered</li><li>Poor delivery of presentation</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r53-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="50" ${Number(members[0].r53) === 50 ? 'selected' : ''}>50</option>
                                            <option value="45" ${Number(members[0].r53) === 45 ? 'selected' : ''}>45</option>
                                            <option value="40" ${Number(members[0].r53) === 40 ? 'selected' : ''}>40</option> 
                                            <option value="35" ${Number(members[0].r53) === 35 ? 'selected' : ''}>35</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r53-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="50" ${Number(member.r53) === 50 ? 'selected' : ''}>50</option>
                                                <option value="45" ${Number(member.r53) === 45 ? 'selected' : ''}>45</option>
                                                <option value="40" ${Number(member.r53) === 40 ? 'selected' : ''}>40</option> 
                                                <option value="35" ${Number(member.r53) === 35 ? 'selected' : ''}>35</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" colspan="7">
                                        <center>
                                            <button id="saveRubricsMarks" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-800 transition duration-300 save-mbtn" data-rubric="5" style="min-width: 140px;">Save Score</button>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>
                        ` : ''}
                        ${i === 6 ? `
                            <thead class="bg-blue-100 rounded-t-lg">
                                <tr>
                                    <th class="bg-blue-200 px-6 py-3 border-b-2 font-medium uppercase tracking-wider" style="font-size: 17px !important;" colspan="7">Level of Achievement</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Review Cases</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Excellent (30)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Good (27)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Average (24)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Poor (21)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Student</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Project Report</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Project report is according to the specified format</li><li>References and citations are appropriate  and well mentioned</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Project report is according to the specified format</li><li>References and citations are appropriate but not mentioned well </li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Project report is according to the specified format but some mistakes</li><li>In-sufficient references and citations</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Project report not prepared according to the specified format</li><li>References and citations are not  appropriate</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r61-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="30" ${Number(members[0].r61) === 30 ? 'selected' : ''}>30</option>
                                            <option value="27" ${Number(members[0].r61) === 27 ? 'selected' : ''}>27</option>
                                            <option value="24" ${Number(members[0].r61) === 24 ? 'selected' : ''}>24</option> 
                                            <option value="21" ${Number(members[0].r61) === 21 ? 'selected' : ''}>21</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r61-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="30" ${Number(member.r61) === 30 ? 'selected' : ''}>30</option>
                                                <option value="27" ${Number(member.r61) === 27 ? 'selected' : ''}>27</option>
                                                <option value="24" ${Number(member.r61) === 24 ? 'selected' : ''}>24</option> 
                                                <option value="21" ${Number(member.r61) === 21 ? 'selected' : ''}>21</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Description of Concepts and Technical Details </b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Complete explanation of the key concepts</li><li>Strong description of the technical requirements of the project</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Complete explanation of the key concepts</li><li>In-sufficient description of the technical requirements of the project</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Complete explanation of the key concepts but little relevance to literature</li><li>In-sufficient description of the technical requirements of the project</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Inapproiate explanation of the key concepts</li><li>Poor description of the technical requirements of the project</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r62-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="30" ${Number(members[0].r62) === 30 ? 'selected' : ''}>30</option>
                                            <option value="27" ${Number(members[0].r62) === 27 ? 'selected' : ''}>27</option>
                                            <option value="24" ${Number(members[0].r62) === 24 ? 'selected' : ''}>24</option> 
                                            <option value="21" ${Number(members[0].r62) === 21 ? 'selected' : ''}>21</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r62-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="30" ${Number(member.r62) === 30 ? 'selected' : ''}>30</option>
                                                <option value="27" ${Number(member.r62) === 27 ? 'selected' : ''}>27</option>
                                                <option value="24" ${Number(member.r62) === 24 ? 'selected' : ''}>24</option> 
                                                <option value="21" ${Number(member.r62) === 21 ? 'selected' : ''}>21</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Conclusion and Discussion</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Results are presented in very appropriate manner </li><li>Project work is well summarized and concluded</li><li>Future extensions in the project are well specified</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Results are presented in good manner </li><li>Project work summary and conclusion not very appropriate</li><li>Future extensions in the project are  specified </li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Results presented are not much satisfactory</li><li>Project work summary and conclusion not very appropriate</li><li>Future extensions in the project are specified</li></ul></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><ul style="list-style-type: disc;"><li>Results are not presented properly</li><li>Project work is not  summarized and concluded</li><li>Future extensions in the project are not specified</li></ul></td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r63-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="30" ${Number(members[0].r63) === 30 ? 'selected' : ''}>30</option>
                                            <option value="27" ${Number(members[0].r63) === 27 ? 'selected' : ''}>27</option>
                                            <option value="24" ${Number(members[0].r63) === 24 ? 'selected' : ''}>24</option> 
                                            <option value="21" ${Number(members[0].r63) === 21 ? 'selected' : ''}>21</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r63-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="30" ${Number(member.r63) === 30 ? 'selected' : ''}>30</option>
                                                <option value="27" ${Number(member.r63) === 27 ? 'selected' : ''}>27</option>
                                                <option value="24" ${Number(member.r63) === 24 ? 'selected' : ''}>24</option> 
                                                <option value="21" ${Number(member.r63) === 21 ? 'selected' : ''}>21</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" colspan="7">
                                        <center>
                                            <button id="saveRubricsMarks" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-800 transition duration-300 save-mbtn" data-rubric="6" style="min-width: 140px;">Save Score</button>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>
                        ` : ''}
                        ${i === 7 ? `
                            <thead class="bg-blue-100 rounded-t-lg">
                                <tr>
                                    <th class="bg-blue-200 px-6 py-3 border-b-2 font-medium uppercase tracking-wider" style="font-size: 17px !important;" colspan="6">Level of Achievement</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Review Cases</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Good (35)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Average (30)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Poor (25)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Student</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Writing Research Paper related to work done in Project</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Written Research paper related to Project and communicated in any Conference/ Journal</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Written Research paper related to Project but not communicated in any Conference/ Journal</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Not written Research paper related to Project</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r71-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="35" ${Number(members[0].r71) === 35 ? 'selected' : ''}>35</option>
                                            <option value="30" ${Number(members[0].r71) === 30 ? 'selected' : ''}>30</option>
                                            <option value="25" ${Number(members[0].r71) === 25 ? 'selected' : ''}>25</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r71-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="35" ${Number(member.r71) === 35 ? 'selected' : ''}>35</option>
                                                <option value="30" ${Number(member.r71) === 30 ? 'selected' : ''}>30</option>
                                                <option value="25" ${Number(member.r71) === 25 ? 'selected' : ''}>25</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Research paper</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Research paper published in international Conference/Journal <br> <b>OR</b> <br> Research paper published in national Conference/Journal and placed in any company</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Research paper published in national Conference/Journal</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Research paper not published</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r72-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="35" ${Number(members[0].r72) === 35 ? 'selected' : ''}>35</option>
                                            <option value="30" ${Number(members[0].r72) === 30 ? 'selected' : ''}>30</option>
                                            <option value="25" ${Number(members[0].r72) === 25 ? 'selected' : ''}>25</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r72-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="35" ${Number(member.r72) === 35 ? 'selected' : ''}>35</option>
                                                <option value="30" ${Number(member.r72) === 30 ? 'selected' : ''}>30</option>
                                                <option value="25" ${Number(member.r72) === 25 ? 'selected' : ''}>25</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" colspan="6">
                                        <center>
                                            <button id="saveRubricsMarks" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-800 transition duration-300 save-mbtn" data-rubric="7" style="min-width: 140px;">Save Score</button>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>        
                        ` : ''}
                        ${i === 8 ? `
                            <thead class="bg-blue-100 rounded-t-lg">
                                <tr>
                                    <th class="bg-blue-200 px-6 py-3 border-b-2 font-medium uppercase tracking-wider" style="font-size: 17px !important;" colspan="6">Level of Achievement</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Review Cases</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Good (30)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Average (25)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Poor (20)</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Student</th>
                                    <th class="px-3 py-3 border font-medium uppercase tracking-wider">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Working within a Team</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Collaborates and communicates in a group situation and integrates the views of others</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Exchanges some views but requires guidance to collaborate with others</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Makes little or no attempt to collaborate in a group situation</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r81-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="30" ${Number(members[0].r81) === 30 ? 'selected' : ''}>30</option>
                                            <option value="25" ${Number(members[0].r81) === 25 ? 'selected' : ''}>25</option>
                                            <option value="20" ${Number(members[0].r81) === 20 ? 'selected' : ''}>20</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r81-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="30" ${Number(member.r81) === 30 ? 'selected' : ''}>30</option>
                                                <option value="25" ${Number(member.r81) === 25 ? 'selected' : ''}>25</option>
                                                <option value="20" ${Number(member.r81) === 20 ? 'selected' : ''}>20</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Technical  Knowledge and Awareness related to the Project</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Extensive knowledge related to the project</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Fair knowledge related to the project</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Lacks sufficient knowledge</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r82-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="30" ${Number(members[0].r82) === 30 ? 'selected' : ''}>30</option>
                                            <option value="25" ${Number(members[0].r82) === 25 ? 'selected' : ''}>25</option>
                                            <option value="20" ${Number(members[0].r82) === 20 ? 'selected' : ''}>20</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r82-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="30" ${Number(member.r82) === 30 ? 'selected' : ''}>30</option>
                                                <option value="25" ${Number(member.r82) === 25 ? 'selected' : ''}>25</option>
                                                <option value="20" ${Number(member.r82) === 20 ? 'selected' : ''}>20</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}"><b>Regularity</b></td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Reports to the guide regularly and consistent in work</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Not very regular  but consistent in the work</td>
                                    <td class="px-5 py-2 border" rowspan="${numberOfMembers}">Irregular in attendance and inconsistent in work</td>
                                    <td class="px-5 py-2 border"><center>${members[0].name}<br>(${members[0].roll})</center></td>
                                    <td class="px-5 py-2 border">
                                        <select id ="r83-${members[0].roll}" class="p-2 border rounded">
                                            <option value="">...</option>
                                            <option value="30" ${Number(members[0].r83) === 30 ? 'selected' : ''}>30</option>
                                            <option value="25" ${Number(members[0].r83) === 25 ? 'selected' : ''}>25</option>
                                            <option value="20" ${Number(members[0].r83) === 20 ? 'selected' : ''}>20</option>
                                        </select>
                                    </td>
                                </tr>
                                ${members.slice(1).map((member, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}"> 
                                        <td class="px-5 py-2 border"><center>${member.name}<br>(${member.roll})</center</td>
                                        <td class="px-5 py-2 border">
                                            <select id ="r83-${member.roll}" class="p-2 border rounded">
                                                <option value="">...</option>
                                                <option value="30" ${Number(member.r83) === 30 ? 'selected' : ''}>30</option>
                                                <option value="25" ${Number(member.r83) === 25 ? 'selected' : ''}>25</option>
                                                <option value="20" ${Number(member.r83) === 20 ? 'selected' : ''}>20</option>
                                            </select>
                                        </td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td class="px-5 py-2 border" colspan="6">
                                        <center>
                                            <button id="saveRubricsMarks" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-800 transition duration-300 save-mbtn" data-rubric="8" style="min-width: 140px;">Save Score</button>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>        
                        ` : ''}
                    </table>
                </div>
                `;

                modalContent.appendChild(rubricDiv);
            }

            modal.style.display = 'block';
            modal.scrollTop = 0;

            // Add event listeners to Save buttons to save the review for their respective rubric when they are clicked
            const saveButtons = modalContent.querySelectorAll(".save-btn");
            saveButtons.forEach(button => {
                button.addEventListener("click", () => {
                    const rubricNumber = button.dataset.rubric;
                    saveRubricData(group.gnum, rubricNumber, groupNumber);//Taking parameters gnum & rubricNumber to save the details for that particular rubrics of a grp, and groupNumber to further using it to reopen the same group modal after save-reloading   
                });
            });

            // Function to save rubric review data
            function saveRubricData(gnum, rubricNumber, groupId) {
                const examiner = document.getElementById(`examiner-${rubricNumber}`).value;
                const status = document.getElementById(`status-${rubricNumber}`).value;
                const saveButton = document.querySelector(`[data-rubric="${rubricNumber}"]`);
                const spinner = document.getElementById('spinner');

                if (!examiner || !status) {
                    alert("Please fill in all fields before saving.");
                    return;
                }

                // Show spinner and disable button
                if (spinner) spinner.style.display = 'flex';
                if (saveButton) saveButton.disabled = true;
                if (saveButton) saveButton.classList.add('opacity-50', 'cursor-not-allowed');

                const data = {
                    gnum: gnum,
                    rubric: rubricNumber,
                    examiner: examiner,
                    status: status,
                    action: 'rubricsreview',
                };

                // Save the modal state and position to local storage before sending the data as after it is sent, the page will reload
                localStorage.setItem('rmodalState', 'open');
                localStorage.setItem('rmodalPosition', document.getElementById('rubricsReviewModal').scrollTop);
                localStorage.setItem('groupId', groupId);
                
                // Send the data to the server
                fetch("groups.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        alert(`Rubric R${rubricNumber} details saved successfully!`);
                        window.location.reload();
                    } else {
                        throw new Error(`Failed to save Rubric R${rubricNumber} details: ${response.message}`);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert(error.message || 'An error occurred while saving rubric details.');
                })
                .finally(() => {
                    // Hide spinner and enable button
                    if (spinner) spinner.style.display = 'none';
                    if (saveButton) saveButton.disabled = false;
                    if (saveButton) saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }

            // Add event listeners to Save buttons to save the marks for their respective rubric when they are clicked
            const msaveButtons = modalContent.querySelectorAll(".save-mbtn");
            msaveButtons.forEach(button => {
                button.addEventListener("click", () => {
                    const rubricNumber = button.dataset.rubric;
                    saveRubricMarks(group.gnum, rubricNumber, groupNumber, button);//Taking parameters gnum to save the details for that particular grp, grpNumber to save the modal in local storage and button to enable & disable it during spinner
                });
            });
            function saveRubricMarks(gnum, rubricNumber, groupId, saveButton) {
                // Collect data for all members and all subparts of the rubric
                const rubricParts = {
                    1: ['r11', 'r12', 'r13'],
                    2: ['r21', 'r22', 'r23'],
                    3: ['r31', 'r32'],
                    4: ['r41', 'r42'],
                    5: ['r51', 'r52', 'r53'],
                    6: ['r61', 'r62', 'r63'],
                    7: ['r71', 'r72'],
                    8: ['r81', 'r82', 'r83'],
                };
                let allFieldsFilled = true;
                const rubricData = rubricParts[rubricNumber].map(part => ({
                    part,
                    members: memberRows
                        .filter(member => member.gnum === gnum)
                        .map(member => {
                            const roll = member.roll;
                            const scoreElement = document.getElementById(`${part}-${roll}`);
                            const score = scoreElement ? scoreElement.value : null;
                            if (!score) {
                                allFieldsFilled = false;
                            }
                            return {
                                roll,
                                score: score ? parseInt(score) : null, // Ensure score is stored as an integer
                            };
                        }),
                }));
                // Stop execution if any field is empty
                if (!allFieldsFilled) {
                    alert(`Please fill in all the R${rubricNumber} marks fields before saving.`);
                    return;
                }
                // Prepare data payload
                const payload = {
                    action: "rubricsmarks",
                    gnum,
                    rubric: rubricNumber,
                    rubricData,
                };

                // Show spinner and disable button
                const spinner = document.getElementById('spinner');
                if (spinner) spinner.style.display = 'flex';
                if (saveButton) saveButton.disabled = true;
                if (saveButton) saveButton.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Save the modal state and position to local storage before sending the data as after it is sent, the page will reload
                localStorage.setItem('rmodalState', 'open');
                localStorage.setItem('rmodalPosition', document.getElementById('rubricsReviewModal').scrollTop);
                localStorage.setItem('groupId', groupId);
                
                // Send data to the server
                fetch("groups.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(payload),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`Rubric R${rubricNumber} marks saved successfully!`);
                            window.location.reload();
                        } else {
                            throw new Error(`Failed to save Rubric R${rubricNumber} marks: ${data.message}`);
                        }
                    })
                    .catch(error => {
                        console.error("Error saving rubric marks:", error);
                        alert(error.message || 'An error occurred while saving Rubric R${rubricNumber} marks.');
                    })
                    .finally(() => {
                        // Hide spinner and enable button
                        if (spinner) spinner.style.display = 'none';
                        if (saveButton) saveButton.disabled = false;
                        if (saveButton) saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    });
            }

            
            // Function to clear modal state
            function clearModalState() {
                localStorage.removeItem('rmodalState');
                localStorage.removeItem('rmodalPosition');
                localStorage.removeItem('groupId');
            }

            // Clear the modal state from local storage when the modal is closed
            document.querySelector('.close').addEventListener('click', () => {
                clearModalState();
                document.getElementById('rubricsReviewModal').style.display = 'none';
            });

            // Clear the modal state when clicking outside the modal content
            window.addEventListener('click', (event) => {
                const modal = document.getElementById('rubricsReviewModal');
                if (event.target === modal) {
                    clearModalState();
                    modal.style.display = 'none';
                }
            });
        }
        // To open the document(ppt/pdf) in new tab when view button is clicked
        function openDocument(fileUrl) {
            // Extract file extension from the last dot
            const fileExtension = fileUrl.substring(fileUrl.lastIndexOf('.') + 1).toLowerCase();
            
            // First, try Google Viewer for all file types
            tryGoogleViewer(fileUrl, fileExtension)
                .catch(() => {
                    // If Google Viewer fails, check if the file is PDF and show fallback dialog
                    if (fileExtension.toLowerCase() === 'pdf') {
                        console.log('PDF file detected. Showing fallback dialog.');
                        showFallbackDialog(fileUrl);
                    } else {
                        // If it's not PDF, try Microsoft Office Viewer
                        return tryMicrosoftViewer(fileUrl)
                            .catch(() => {
                                // If both fail, show fallback dialog
                                showFallbackDialog(fileUrl);
                            });
                    }
                });
        }
        // To open file in Google Docs
        function tryGoogleViewer(fileUrl) {
            return new Promise((resolve, reject) => {
                const encodedUrl = encodeURIComponent(fileUrl);
                const viewerUrl = 'https://docs.google.com/viewer?url=' + encodedUrl + '&embedded=true';
                // Try to open the Google Docs Online Viewer directly
                try {
                    window.open(viewerUrl, '_blank');
                    return Promise.resolve();  // If successful, resolve the promise
                } catch (error) {
                    console.error('Error opening Google Docs Online Viewer:', error);
                    return Promise.reject(error);    
                }
            });
        }
        //To open file in MS Office
        function tryMicrosoftViewer(fileUrl) {
            return new Promise((resolve, reject) => {
                const encodedUrl = encodeURIComponent(fileUrl);
                const viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodedUrl;
                
                // Try to open the Office Online Viewer directly
                try {
                    window.open(viewerUrl, '_blank');
                    return Promise.resolve();  // If successful, resolve the promise
                } catch (error) {
                    console.error('Error opening Office Online Viewer:', error);
                    return Promise.reject(error);
                }
            });
        }
        // If both are unavailable provide a dialog box
        function showFallbackDialog(fileUrl) {
            // Create a custom dialog
            const dialog = document.createElement('div');
            dialog.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 24px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
                z-index: 1000;
                max-width: 420px;
                width: 90%;
                text-align: center;
                font-family: Arial, sans-serif;
            `;

            dialog.innerHTML = `
                <h3 style="margin: 0 0 12px; font-size: 1.25em; font-weight: bold; color: #1E3A8A;">
                    Document Viewer Unavailable
                </h3>
                <p style="margin: 0 0 20px; color: #4B5563; font-size: 0.95em;">
                    Unable to load document. Please try downloading the file directly:
                </p>
                <a href="${fileUrl}" download
                    style="
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        padding: 10px 20px;
                        max-width: 220px;
                        width: 100%;
                        background: linear-gradient(to right, #3B82F6, #2563EB);
                        color: white;
                        font-size: 0.95em;
                        font-weight: 500;
                        border-radius: 8px;
                        cursor: pointer;
                        text-decoration: none;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    "
                    onmouseover="this.style.background='linear-gradient(to right, #2563EB, #1E40AF)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(0, 0, 0, 0.15)';"
                    onmouseout="this.style.background='linear-gradient(to right, #3B82F6, #2563EB)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)';"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 28px; width: 28px; margin-right: 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 110-8 4.5 4.5 0 019 0h1a3 3 0 110 6h-2m-5 0v4m0 0l-3-3m3 3l3-3" />
                    </svg>
                    Download File
                </a>
            `;

            // Add overlay background
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            `;
            // Add to document
            document.body.appendChild(overlay);
            document.body.appendChild(dialog);
            
            // Close dialog when clicking overlay
            overlay.onclick = function() {
                dialog.remove();
                overlay.remove();
            };
        }
        //To delete the document from the tebi and its URL from the database
        function deleteDocument(fileUrl) {
            const groupId = localStorage.getItem('groupId');
            const group = groupRows.find(group => group.number == groupId);
            const gnum = group.gnum;
            const spinner = document.getElementById('spinner');
            const deleteButton = event.target;

            let columnName = "";
            if (fileUrl.includes("r2ppt")) {
                columnName = "r2ppt";
            }
            else if (fileUrl.includes('r2pdf')) {
                columnName = "r2pdf";
            }
            else if (fileUrl.includes('r6ppt')) {
                columnName = "r6ppt";
            }
            else if (fileUrl.includes('r6pdf')) {
                columnName = "r6pdf";
            }

            if (!fileUrl || !gnum || !columnName) {
                alert("Something unexpected happened! Please try again later.");
                return;
            }

            const confirmDelete = confirm('Are you sure you want to delete this document?');
            if (!confirmDelete) return;

            // Show spinner and disable button
            if (spinner) spinner.style.display = 'flex';
            if (deleteButton) deleteButton.disabled = true;
            if (deleteButton) deleteButton.classList.add('opacity-50', 'cursor-not-allowed');
            // Save the modal state and position to local storage before sending the data as after it is sent, the page will reload

            localStorage.setItem('rmodalState', 'open');
            localStorage.setItem('rmodalPosition', document.getElementById('rubricsReviewModal').scrollTop);
            localStorage.setItem('groupId', groupId);

            // Send delete request to server
            fetch('groups.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: "deleteDocument",
                    fileUrl: fileUrl,
                    gnum: gnum,
                    columnName: columnName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Document deleted successfully!');
                    window.location.reload();
                } else {
                    alert('Error deleting document: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while deleting document.');
            })
            .finally(() => {
                // Hide spinner and enable button
                if (spinner) spinner.style.display = 'none';
                if (deleteButton) deleteButton.disabled = false;
                if (deleteButton) deleteButton.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }
        // Close the modals when the close button is clicked
        document.querySelectorAll('.close').forEach(closeButton => {
            closeButton.addEventListener('click', () => {
                // Close the modal
                const modal = closeButton.closest('.modal');
                modal.style.display = 'none';
            });
        });

        // Close the modals when clicking outside of the modal content
        window.addEventListener('click', (event) => {
            document.querySelectorAll('.modal').forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
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
            const spinner = document.getElementById('spinner');

            const confirmChange = confirm(`Are you sure you want to change mentor for Group ID ${groupId}?`);
            if (confirmChange) {
                // Show spinner and disable button
                if (spinner) spinner.style.display = 'flex';
                if (button) button.disabled = true;
                if (button) button.classList.add('opacity-50', 'cursor-not-allowed');
                if (mentorDropdown) mentorDropdown.disabled = true;
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) {//If SQL query executed successfully
                        alert('Mentor changed successfully!');
                        window.location.reload();
                    } else {//If SQL query isn't executed successfully then throw an error
                        throw new Error('Something went wrong! Mentor not changed successfully.');
                    }
                })
                .catch(error => {//To catch any unexpected error
                    console.error('Error:', error);
                    alert(error.message || 'An error occurred while changing mentor.');
                })
                .finally(() => {
                    // Hide spinner and enable button
                    if (spinner) spinner.style.display = 'none';
                    if (button) button.disabled = false;
                    if (button) button.classList.remove('opacity-50', 'cursor-not-allowed');
                    if (mentorDropdown) mentorDropdown.disabled = false;
                });
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
            const row = button.closest('tr');
            const groupId = row.cells[0].textContent;
            const gnum = groupRows.find(group => group.number == groupId).gnum;
            const spinner = document.getElementById('spinner');

            const confirmDelete = confirm(`Are you sure you want to delete Group ID ${groupId}?`);
            if (confirmDelete) {
                // Show spinner and disable button
                if (spinner) spinner.style.display = 'flex';
                if (button) button.disabled = true;
                if (button) button.classList.add('opacity-50', 'cursor-not-allowed');

                fetch('groups.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        gnum: gnum,
                        action: 'delete'
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Group deleted successfully!');
                        window.location.reload();
                    } else {
                        throw new Error('Something went wrong! Group not deleted successfully.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || 'An error occurred while deleting group.');
                })
                .finally(() => {
                    // Hide spinner and enable button
                    if (spinner) spinner.style.display = 'none';
                    if (button) button.disabled = false;
                    if (button) button.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }
        }   
    </script>
    <?php include 'footer.php' ?>
    </body>
</html>