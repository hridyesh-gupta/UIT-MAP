<!-- 1st page -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Student Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .table-container {
            overflow-x: auto;
        }
        .editable {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
            display: inline-block;
            min-width: 150px;
        }
        td {
            padding: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 flex flex-col min-h-screen">

    <?php include 'studentheaders.php' ?>

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow my-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-5">
                <h2 class="text-2xl font-semibold text-gray-700">Student Bio-data</h2>
                <div class="w-32 h-32 mt-4 md:mt-0">
                    <img id="student-photo" src="https://via.placeholder.com/150" alt="Student Photo" class="rounded shadow">
                </div>
            </div>

            <table class="w-full text-left mb-5">
                <tbody>
                    <tr>
                        <td class="font-semibold text-gray-600">Name</td>
                        <td>:</td>
                        <td><span id="student-name" class="editable" contenteditable="false"></span></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">Roll Number</td>
                        <td>:</td>
                        <td><span id="student-roll" class="editable" contenteditable="false"></span></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">DOB</td>
                        <td>:</td>
                        <td><span id="student-dob" class="editable" contenteditable="false"></span></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">Contact No</td>
                        <td>:</td>
                        <td><span id="student-contact" class="editable" contenteditable="false"></span></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">E-Mail</td>
                        <td>:</td>
                        <td><span id="student-email" class="editable" contenteditable="false"></span></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">10<sup>th</sup> Marks (in %)</td>
                        <td>:</td>
                        <td><span id="student-marks-10" class="editable" contenteditable="false"></span></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">12<sup>th</sup> Marks (in %)</td>
                        <td>:</td>
                        <td><span id="student-marks-12" class="editable" contenteditable="false"></span></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">Diploma (in %)</td>
                        <td>:</td>
                        <td><span id="student-diploma" class="editable" contenteditable="false"></span></td>
                    </tr>
                </tbody>
            </table>
            <div class="flex justify-end mb-8">
                <button id="edit-btn" class="bg-yellow-500 text-white px-4 py-2 rounded mr-2">Edit</button>
                <button id="save-btn" class="bg-green-500 text-white px-4 py-2 rounded hidden">Save</button>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mb-4">B. Tech. (Obtained / Total)</h2>
            <div class="table-container mb-8">
                <table class="w-full text-left border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-4 py-2">Semester</th>
                            <th class="border px-4 py-2">Marks</th>
                            <th class="border px-4 py-2">CP</th>
                            <th class="border px-4 py-2">Paper Name</th>
                        </tr>
                    </thead>
                    <tbody id="academic-record">
                        <!-- Data will be inserted here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-blue-600 text-white p-4 text-center">
        <div class="max-w-6xl mx-auto">
            &copy; 2023 MAP College. All rights reserved.
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Simulate fetching data from a database
            const studentData = {
                name: "",
                rollNumber: "",
                dob: "",
                contact: "",
                email: "",
                marks10: "",
                marks12: "",
                diploma: "",
                photo: "https://via.placeholder.com/150",
                academicRecord: [
                    { semester: "I Semester", marks: " / 950", cp: "0", paper: "" },
                    { semester: "II Semester", marks: " / 900", cp: "0", paper: "" },
                    { semester: "III Semester", marks: " / 950", cp: "0", paper: "" },
                    { semester: "IV Semester", marks: " / 900", cp: "0", paper: "" },
                    { semester: "V Semester", marks: " / 950", cp: "0", paper: "" },
                    { semester: "VI Semester", marks: " / 950", cp: "0", paper: "" },
                    { semester: "VII Semester", marks: " / 950", cp: "0", paper: "" },
                    { semester: "VIII Semester", marks: " / 950", cp: "0", paper: "" }
                ]
            };

            // Populate student data
            document.getElementById("student-name").innerText = studentData.name;
            document.getElementById("student-roll").innerText = studentData.rollNumber;
            document.getElementById("student-dob").innerText = studentData.dob;
            document.getElementById("student-contact").innerText = studentData.contact;
            document.getElementById("student-email").innerText = studentData.email;
            document.getElementById("student-marks-10").innerText = studentData.marks10;
            document.getElementById("student-marks-12").innerText = studentData.marks12;
            document.getElementById("student-diploma").innerText = studentData.diploma;
            document.getElementById("student-photo").src = studentData.photo;

            // Populate academic record
            const academicRecordTable = document.getElementById("academic-record");
            studentData.academicRecord.forEach(record => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td class="border px-4 py-2">${record.semester}</td>
                    <td class="border px-4 py-2"><span class="editable" contenteditable="false">${record.marks}</span></td>
                    <td class="border px-4 py-2"><span class="editable" contenteditable="false">${record.cp}</span></td>
                    <td class="border px-4 py-2"><span class="editable" contenteditable="false">${record.paper}</span></td>
                `;
                academicRecordTable.appendChild(row);
            });

            // Enable editing
            const editButton = document.getElementById("edit-btn");
            const saveButton = document.getElementById("save-btn");
            const editableFields = document.querySelectorAll(".editable");

            editButton.addEventListener("click", () => {
                editableFields.forEach(field => field.contentEditable = "true");
                editButton.classList.add("hidden");
                saveButton.classList.remove("hidden");
            });

            saveButton.addEventListener("click", () => {
                editableFields.forEach(field => field.contentEditable = "false");
                editButton.classList.remove("hidden");
                saveButton.classList.add("hidden");

                // Here, you can add the code to save the edited data
                // For now, we'll just log the edited data to the console
                const updatedData = {
                    name: document.getElementById("student-name").innerText,
                    rollNumber: document.getElementById("student-roll").innerText,
                    dob: document.getElementById("student-dob").innerText,
                    contact: document.getElementById("student-contact").innerText,
                    email: document.getElementById("student-email").innerText,
                    marks10: document.getElementById("student-marks-10").innerText,
                    marks12: document.getElementById("student-marks-12").innerText,
                    diploma: document.getElementById("student-diploma").innerText,
                    academicRecord: []
                };
                const academicRecordRows = document.getElementById("academic-record").rows;
                for (let i = 0; i < academicRecordRows.length; i++) {
const row = academicRecordRows[i];
                    updatedData.academicRecord.push({
                        semester: row.cells[0].innerText,
                        marks: row.cells[1].firstChild.innerText,
                        cp: row.cells[2].firstChild.innerText,
                        paper: row.cells[3].firstChild.innerText
                    });
                }
                console.log(updatedData);
            });
        });
    </script>
</body>
</html>
