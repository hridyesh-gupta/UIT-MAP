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
            g.r11, g.r12, g.r13, -- Fetch individual sub-components for R1
            g.r21, g.r22, g.r23, -- For R2
            g.r31, g.r32,         -- For R3
            g.r41, g.r42,         -- For R4
            g.r51, g.r52, g.r53, -- For R5
            g.r61, g.r62, g.r63, -- For R6
            g.r71, g.r72,         -- For R7
            g.r81, g.r82, g.r83, -- For R8
            p.number AS group_id, 
            p.mentor, 
            p.title
        FROM groups g
        INNER JOIN projinfo p ON g.gnum = p.gnum
        WHERE g.batchyr = '$batchyr' AND p.batchyr = '$batchyr'
        ORDER BY p.number, g.roll";
}
else if($_SESSION['usertype']=="mentor"){
    $mentor = $_SESSION['username'];
    $query = "
        SELECT 
            g.gnum, 
            g.roll, 
            g.name,
            g.r11, g.r12, g.r13,
            g.r21, g.r22, g.r23,
            g.r31, g.r32,
            g.r41, g.r42,
            g.r51, g.r52, g.r53,
            g.r61, g.r62, g.r63,
            g.r71, g.r72,
            g.r81, g.r82, g.r83,
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

$data = [];
$excelData = [];
while ($row = $result->fetch_assoc()) {
    $groupId = $row['group_id'];
    
    // Calculate the sums for on-screen display and Excel export
    $r1 = $row['r11'] + $row['r12'] + $row['r13'];
    $r2 = $row['r21'] + $row['r22'] + $row['r23'];
    $r3 = $row['r31'] + $row['r32'];
    $r4 = $row['r41'] + $row['r42'];
    $r5 = $row['r51'] + $row['r52'] + $row['r53'];
    $r6 = $row['r61'] + $row['r62'] + $row['r63'];
    $r7 = $row['r71'] + $row['r72'];
    $r8 = $row['r81'] + $row['r82'] + $row['r83'];
    $total = $r1 + $r2 + $r3 + $r4 + $r5 + $r6 + $r7 + $r8;

    // For on-screen table
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
        'R1' => $r1,
        'R2' => $r2,
        'R3' => $r3,
        'R4' => $r4,
        'R5' => $r5,
        'R6' => $r6,
        'R7' => $r7,
        'R8' => $r8,
        'total' => $total
    ];

    // For Excel export (include bifurcations and rubric totals)
    if (!isset($excelData[$groupId])) {
        $excelData[$groupId] = [
            'group_id' => $groupId,
            'mentor' => $row['mentor'],
            'title' => $row['title'],
            'students' => []
        ];
    }
    $excelData[$groupId]['students'][] = [
        'roll' => $row['roll'],
        'name' => $row['name'],
        'r11' => $row['r11'],
        'r12' => $row['r12'],
        'r13' => $row['r13'],
        'r1_total' => $r1, // Add R1 Total
        'r21' => $row['r21'],
        'r22' => $row['r22'],
        'r23' => $row['r23'],
        'r2_total' => $r2, // Add R2 Total
        'r31' => $row['r31'],
        'r32' => $row['r32'],
        'r3_total' => $r3, // Add R3 Total
        'r41' => $row['r41'],
        'r42' => $row['r42'],
        'r4_total' => $r4, // Add R4 Total
        'r51' => $row['r51'],
        'r52' => $row['r52'],
        'r53' => $row['r53'],
        'r5_total' => $r5, // Add R5 Total
        'r61' => $row['r61'],
        'r62' => $row['r62'],
        'r63' => $row['r63'],
        'r6_total' => $r6, // Add R6 Total
        'r71' => $row['r71'],
        'r72' => $row['r72'],
        'r7_total' => $r7, // Add R7 Total
        'r81' => $row['r81'],
        'r82' => $row['r82'],
        'r83' => $row['r83'],
        'r8_total' => $r8, // Add R8 Total
        'total' => $total  // Overall total
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
            margin: 0 auto;
            display: flex;
            justify-content: center;
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
    <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow">
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        const result = <?php echo json_encode($result); ?>;
        const data = <?php echo json_encode($data); ?>;
        const excelData = <?php echo json_encode($excelData); ?>;
        if(result!=null && document.getElementById('downloadExcel')){
            document.getElementById('downloadExcel').addEventListener('click', () => {
                // Create a new table for Excel export
                const table = document.createElement('table');
                table.style.display = 'none'; // Hide the table
                document.body.appendChild(table);

                // Add headers
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');
                const headers = [
                    'Sr. No', 'Group ID', 'Roll Number', 'Student Name', 'Group Mentor', 'Project Name',
                    'r11', 'r12', 'r13', 'R1 Total', // R1 with total
                    'r21', 'r22', 'r23', 'R2 Total', // R2 with total
                    'r31', 'r32', 'R3 Total',        // R3 with total
                    'r41', 'r42', 'R4 Total',        // R4 with total
                    'r51', 'r52', 'r53', 'R5 Total', // R5 with total
                    'r61', 'r62', 'r63', 'R6 Total', // R6 with total
                    'r71', 'r72', 'R7 Total',        // R7 with total
                    'r81', 'r82', 'r83', 'R8 Total', // R8 with total
                    'Overall Total'                   // Overall total
                ];
                headers.forEach(header => {
                    const th = document.createElement('th');
                    th.textContent = header;
                    headerRow.appendChild(th);
                });
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Add data rows
                const tbody = document.createElement('tbody');
                let srNo = 1;
                for (const groupId in excelData) {
                    const group = excelData[groupId];
                    const rowspan = group.students.length;
                    group.students.forEach((student, index) => {
                        const row = document.createElement('tr');

                        // Sr. No and Group ID for the first student of the group
                        if (index === 0) {
                            const srNoCell = document.createElement('td');
                            srNoCell.textContent = srNo;
                            srNoCell.setAttribute('rowspan', rowspan);
                            row.appendChild(srNoCell);

                            const groupIdCell = document.createElement('td');
                            groupIdCell.textContent = group.group_id;
                            groupIdCell.setAttribute('rowspan', rowspan);
                            row.appendChild(groupIdCell);
                        }

                        // Student details
                        const rollCell = document.createElement('td');
                        rollCell.textContent = student.roll;
                        row.appendChild(rollCell);

                        const nameCell = document.createElement('td');
                        nameCell.textContent = student.name;
                        row.appendChild(nameCell);

                        // Mentor and Project Name for the first student of the group
                        if (index === 0) {
                            const mentorCell = document.createElement('td');
                            mentorCell.textContent = group.mentor;
                            mentorCell.setAttribute('rowspan', rowspan);
                            row.appendChild(mentorCell);

                            const titleCell = document.createElement('td');
                            titleCell.textContent = group.title;
                            titleCell.setAttribute('rowspan', rowspan);
                            row.appendChild(titleCell);
                        }

                        // Bifurcated rubric marks and their totals
                        row.appendChild(createCell(student.r11));
                        row.appendChild(createCell(student.r12));
                        row.appendChild(createCell(student.r13));
                        row.appendChild(createCell(student.r1_total)); // R1 Total
                        row.appendChild(createCell(student.r21));
                        row.appendChild(createCell(student.r22));
                        row.appendChild(createCell(student.r23));
                        row.appendChild(createCell(student.r2_total)); // R2 Total
                        row.appendChild(createCell(student.r31));
                        row.appendChild(createCell(student.r32));
                        row.appendChild(createCell(student.r3_total)); // R3 Total
                        row.appendChild(createCell(student.r41));
                        row.appendChild(createCell(student.r42));
                        row.appendChild(createCell(student.r4_total)); // R4 Total
                        row.appendChild(createCell(student.r51));
                        row.appendChild(createCell(student.r52));
                        row.appendChild(createCell(student.r53));
                        row.appendChild(createCell(student.r5_total)); // R5 Total
                        row.appendChild(createCell(student.r61));
                        row.appendChild(createCell(student.r62));
                        row.appendChild(createCell(student.r63));
                        row.appendChild(createCell(student.r6_total)); // R6 Total
                        row.appendChild(createCell(student.r71));
                        row.appendChild(createCell(student.r72));
                        row.appendChild(createCell(student.r7_total)); // R7 Total
                        row.appendChild(createCell(student.r81));
                        row.appendChild(createCell(student.r82));
                        row.appendChild(createCell(student.r83));
                        row.appendChild(createCell(student.r8_total)); // R8 Total
                        row.appendChild(createCell(student.total));     // Overall Total

                        tbody.appendChild(row);
                    });
                    srNo++;
                }
                table.appendChild(tbody);

                // Export to Excel
                const workbook = XLSX.utils.book_new();
                const worksheet = XLSX.utils.table_to_sheet(table);
                XLSX.utils.book_append_sheet(workbook, worksheet, 'Rubric Marks');
                XLSX.writeFile(workbook, 'Rubric_Marks.xlsx');

                // Clean up
                document.body.removeChild(table);
            });
        }

        function createCell(value) {
            const cell = document.createElement('td');
            cell.textContent = value || 0; // Default to 0 if null
            return cell;
        }
    </script>
    <?php include 'footer.php' ?>
</body>
</html>