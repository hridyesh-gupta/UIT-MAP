<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Marks Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        /* Header styling */
        thead th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        /* Column styling for 7th semester */
        .semester-7 {
            background-color: #e3f2fd;
        }
        /* Column styling for 8th semester */
        .semester-8 {
            background-color: #ffebee;
        }
        /* Different background colors for R4, R5, R6, etc. */
        .section-r4 {
            background-color: #c8e6c9;
        }
        .section-r5 {
            background-color: #cfd8dc;
        }
        .section-r6 {
            background-color: #ffeb3b;
        }
        .section-r7 {
            background-color: #ef9a9a;
        }
        .section-r8 {
            background-color: #b2dfdb;
        }
    </style>
</head>
<body>
    <h1>Student Marks Table</h1>
    <table id="marksTable">
        <thead>
            <tr>
                <th rowspan="2">Sr. No.</th>
                <th rowspan="2">Group</th>
                <th rowspan="2">Roll Number of Members</th>
                <th rowspan="2">Student Name</th>
                <th rowspan="2">Project Guide</th>
                <th rowspan="2">Project Name</th>
                <th colspan="9" class="semester-7">Project Marks (7th semester) 2022-23</th>
                <th colspan="9" class="semester-8">Project Marks (8th semester) 2022-23</th>
            </tr>
            <tr>
                <!-- 7th Semester Columns -->
                <th class="section-r4">R1 (18)</th>
                <th class="section-r4">R1 (18)</th>
                <th class="section-r4">R2 (24)</th>
                <th class="section-r4">R3 (8)</th>
                <th class="section-r5">R4 (50)</th>
                <th class="section-r5">R4 (100)</th>
                <th class="section-r6">R5 (50)</th>
                <th class="section-r6">R6 (50)</th>
                <th>Total</th>
                
                <!-- 8th Semester Columns -->
                <th class="section-r4">R6 (30)</th>
                <th class="section-r4">R6 (30)</th>
                <th class="section-r6">R7 (35)</th>
                <th class="section-r6">R7 (70)</th>
                <th class="section-r7">R8 (30)</th>
                <th class="section-r7">R8 (30)</th>
                <th class="section-r8">R8 (30)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <!-- Dynamic content will be inserted here -->
        </tbody>
    </table>

    <script>
        // Sample data structure for groups fetched from database
        const groupsData = [
            {
                srNo: 1,
                groupName: '1923CSE1',
                projectGuide: 'Mr. Amit Kumar',
                projectName: 'Blockchain-based malware detection',
                members: [
                    {
                        rollNumber: '1902840100001',
                        name: 'ADITI KUMAR',
                        marks: {
                            semester7: [5, 5, 12, 8, 50, 98, 50, 50, 363],
                            semester8: [30, 30, 35, 70, 30, 30, 30, 285]
                        }
                    },
                    {
                        rollNumber: '1902840100002',
                        name: 'RAM KRISHNA YADAV',
                        marks: {
                            semester7: [4, 4, 10, 7, 49, 97, 48, 49, 358],
                            semester8: [28, 29, 33, 65, 29, 29, 28, 271]
                        }
                    }
                ]
            }
            // Add more groups as needed
        ];

        function populateTable() {
            const tableBody = document.getElementById('tableBody');

            groupsData.forEach(group => {
                group.members.forEach((member, index) => {
                    const row = document.createElement('tr');
                    
                    // Group info only on the first member row
                    if (index === 0) {
                        row.innerHTML += `
                            <td rowspan="${group.members.length}">${group.srNo}</td>
                            <td rowspan="${group.members.length}">${group.groupName}</td>
                            <td rowspan="${group.members.length}">${group.projectGuide}</td>
                            <td rowspan="${group.members.length}">${group.projectName}</td>
                        `;
                    }

                    // Member info
                    row.innerHTML += `
                        <td>${member.rollNumber}</td>
                        <td>${member.name}</td>
                    `;

                    // 7th Semester Marks
                    member.marks.semester7.forEach((mark, i) => {
                        row.innerHTML += `<td class="semester-7">${mark}</td>`;
                    });

                    // 8th Semester Marks
                    member.marks.semester8.forEach((mark, i) => {
                        row.innerHTML += `<td class="semester-8">${mark}</td>`;
                    });

                    tableBody.appendChild(row);
                });
            });
        }

        // Populate table on load
        document.addEventListener('DOMContentLoaded', populateTable);
    </script>
</body>
</html>
