<?php
session_start();
error_reporting(0); //To hide the errors    
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="mentor"){ //If the user is not admin or mentor, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
    header("location: index.php");
}
include 'dbconnect.php'; // Include database connection

$batchyr = null;
// Check whether batchyr is in the URL
if (!isset($_GET['year']) || empty($_GET['year'])) {
    $batchyr = $_SESSION['selected_year'];
}
else{
    // Extract the batch year from the URL and also store it in the session variable
    $batchyr = $_GET['year'];
    $_SESSION['selected_year'] = $batchyr;
}
// Sanitize the input to prevent SQL injection
$batchyr = mysqli_real_escape_string($conn, $batchyr);
// Query to fetch data from groups and projinfo for the specified batchyr if the user is admin
if($_SESSION['usertype']=="admin"){
    $query = "
        SELECT 
            g.gnum, 
            g.roll, 
            g.name,
            g.r11 + g.r12 + g.r13 AS R1,
            g.r21 + g.r22 + g.r23 AS R2,
            g.r31 + g.r32 AS R3,
            g.r41 + g.r42 AS R4,
            g.r51 + g.r52 + g.r53 AS R5,
            g.r61 + g.r62 + g.r63 AS R6,
            g.r71 + g.r72 AS R7,
            g.r81 + g.r82 + g.r83 AS R8,
            g.r11 + g.r12 + g.r13 + g.r21 + g.r22 + g.r23 + g.r31 + g.r32 + g.r41 + g.r42 + g.r51 + g.r52 + g.r53 + g.r61 + g.r62 + g.r63 + g.r71 + g.r72 + g.r81 + g.r82 + g.r83 AS total,
            p.number AS group_id, 
            p.mentor, 
            p.title
        FROM groups g
        INNER JOIN projinfo p ON g.gnum = p.gnum
        WHERE g.batchyr = '$batchyr' AND p.batchyr = '$batchyr'
        ORDER BY p.number, g.roll";
}
else if($_SESSION['usertype']=="mentor"){ // Query to fetch data from groups and projinfo for the specified batchyr if the user is mentor
    $mentor = $_SESSION['username'];
    $query = "
        SELECT 
            g.gnum, 
            g.roll, 
            g.name,
            g.r11 + g.r12 + g.r13 AS R1,
            g.r21 + g.r22 + g.r23 AS R2,
            g.r31 + g.r32 AS R3,
            g.r41 + g.r42 AS R4,
            g.r51 + g.r52 + g.r53 AS R5,
            g.r61 + g.r62 + g.r63 AS R6,
            g.r71 + g.r72 AS R7,
            g.r81 + g.r82 + g.r83 AS R8,
            g.r11 + g.r12 + g.r13 + g.r21 + g.r22 + g.r23 + g.r31 + g.r32 + g.r41 + g.r42 + g.r51 + g.r52 + g.r53 + g.r61 + g.r62 + g.r63 + g.r71 + g.r72 + g.r81 + g.r82 + g.r83 AS total,
            p.number AS group_id, 
            p.mentor, 
            p.title
        FROM groups g
        INNER JOIN projinfo p ON g.gnum = p.gnum
        WHERE g.batchyr = '$batchyr' AND p.batchyr = '$batchyr' AND p.mid = '$mentor'
        ORDER BY p.number, g.roll";
}
$result = $conn->query($query);

// if ($result->num_rows === 0) {
//     echo '<center>
//         <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
//             <strong class="font-bold">Oops!</strong>
//             <span class="block sm:inline">No data found for batch year '.($batchyr-4).'-'.$batchyr.'.</span>
//         </div>
//     </center>';
// }

// Group the data by group_id
$data = [];
while ($row = $result->fetch_assoc()) {
    $groupId = $row['group_id'];
    if (!isset($data[$groupId])) {
        $data[$groupId] = [
            'group_id' => $groupId,
            'mentor' => $row['mentor'],
            'title' => $row['title'],
            'students' => []
        ];
    }
    $data[$groupId]['students'][] = [
        'roll' => $row['roll'],
        'name' => $row['name'],
        'R1' => $row['R1'],
        'R2' => $row['R2'],
        'R3' => $row['R3'],
        'R4' => $row['R4'],
        'R5' => $row['R5'],
        'R6' => $row['R6'],
        'R7' => $row['R7'],
        'R8' => $row['R8'],
        'total' => $row['total']
    ];
}

// HTML Table rendering
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rubrics Marks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
    <style>
        .table-container {
            max-width: 100%;
            overflow-x: auto;
        }
        .flex-container {
            display: flex;
            align-items: center; /* Align items vertically in the center */
        }
        .flex-container h1 {
            flex: 1; /* Allow the heading to take up available space */
            text-align: center; /* Center the heading text */
        }
        .flex-container button {
            margin-left: auto;
        }
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php 
include 'adminheaders.php';
?>
    <div class="max-w-7xl bg-white p-6 rounded-lg shadow">
        <div class="flex-container">
            <h1 class="text-2xl font-bold mb-4">Batch Year (<?php echo htmlspecialchars($batchyr-4); ?>-<?php echo htmlspecialchars($batchyr); ?>)</h1>
    <?php if ($result->num_rows === 0): ?>  
        </div>      
        <hr class="my-8 border-black-300">
        <center>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Oops!</strong>
                <span class="block sm:inline">No data found for batch year <?php echo $batchyr-4; ?>-<?php echo $batchyr; ?>.</span>
            </div>
        </center>
    <?php else: ?>
            <button id="downloadExcel" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-700 transition duration-300">
            Download
            </button>
        </div>
        <div class="table-container mb-8 shadow-lg rounded-lg border border-gray-200">
        <table class="w-full bg-white border border-gray-300">
            <thead>
                <tr class="bg-blue-100">
                    <th class="px-4 py-2 border">Sr. No</th>
                    <th class="px-4 py-2 border">Group ID</th>
                    <th class="px-4 py-2 border">Roll Number</th>
                    <th class="px-4 py-2 border">Student Name</th>
                    <th class="px-4 py-2 border">Group Mentor</th>
                    <th class="px-4 py-2 border">Project Name</th>
                    <th class="px-4 py-2 border">R1</th>
                    <th class="px-4 py-2 border">R2</th>
                    <th class="px-4 py-2 border">R3</th>
                    <th class="px-4 py-2 border">R4</th>
                    <th class="px-4 py-2 border">R5</th>
                    <th class="px-4 py-2 border">R6</th>
                    <th class="px-4 py-2 border">R7</th>
                    <th class="px-4 py-2 border">R8</th>
                    <th class="px-4 py-2 border">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $srNo = 1;
                foreach ($data as $group) {
                    $rowspan = count($group['students']);
                    foreach ($group['students'] as $index => $student) {
                        echo "<tr>";

                        // Sr. No and Group ID for the first student of the group
                        if ($index === 0) {
                            echo "<td class='px-4 py-2 border' rowspan='$rowspan'>$srNo</td>";
                            echo "<td class='px-4 py-2 border' rowspan='$rowspan'>{$group['group_id']}</td>";
                        }

                        // Student details
                        echo "<td class='px-4 py-2 border'>{$student['roll']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['name']}</td>";

                        // Mentor and Project Name for the first student of the group
                        if ($index === 0) {
                            echo "<td class='px-4 py-2 border' rowspan='$rowspan'>{$group['mentor']}</td>";
                            echo "<td class='px-4 py-2 border' rowspan='$rowspan'>{$group['title']}</td>";
                        }

                        // Rubrics marks
                        echo "<td class='px-4 py-2 border'>{$student['R1']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['R2']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['R3']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['R4']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['R5']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['R6']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['R7']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['R8']}</td>";
                        echo "<td class='px-4 py-2 border'>{$student['total']}</td>";

                        echo "</tr>";
                    }
                    $srNo++;
                }
                ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
    </div>
    <!-- Include SheetJS before your custom JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- Your custom script -->
    <script>
        const result = <?php echo json_encode($result); ?>;
        const data= <?php echo json_encode($data); ?>;
        console.log(data);
        if(result!=null && document.getElementById('downloadExcel')){
            document.getElementById('downloadExcel').addEventListener('click', () => {
                const table = document.querySelector('table'); // Select the table element
                const workbook = XLSX.utils.book_new();
                const worksheet = XLSX.utils.table_to_sheet(table);
                XLSX.utils.book_append_sheet(workbook, worksheet, 'Rubric Marks');
                XLSX.writeFile(workbook, 'Rubric_Marks.xlsx');
            });
        }
    </script>
    <?php include 'footer.php' ?>
</body>
</html>
