<?php
// Student 1st page
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="student" && $_SESSION['usertype']!="mentor"){ //If the user is not admin, student, or mentor, then it means the user is accessing this page through url editing. So, redirecting to login page
    header("location: index.php");
}

include 'dbconnect.php';

$username=$_SESSION['username'];

//To fetch student details from the database
$studentExists= false;
$studentDetailsQuery= "SELECT * FROM info WHERE username='$username'";
// Procedural style: $detailsResult = mysqli_query($conn, $studentDetails);
$detailsResult= $conn->query($studentDetailsQuery); // Object oriented style, both these lines are same and are used to execute the query to get the details from the db
if ($detailsResult->num_rows > 0){
    $studentExists=true;
    $studentDetails= $detailsResult->fetch_assoc(); // This line is used to fetch the details from the db and store it in the variable studentDetails
}

//To fetch marks details from the database
$marksExists= false;
$studentMarksQuery= "SELECT * FROM marks WHERE roll='$username'";
$marksResult= $conn->query($studentMarksQuery);
if ($marksResult->num_rows > 0){
    $marksExists=true;
    $marksDetails= $marksResult->fetch_assoc();
}

//To save the marks of the student in the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the data from the POST request body (JSON)
    $postData = json_decode(file_get_contents('php://input'), true);

    // Make sure data is available
    $username = $_SESSION['username'];
    $marks_10 = $postData['marks10'];
    $marks_12 = $postData['marks12'];
    $marks = [
        $postData['m1'], $postData['m2'], $postData['m3'], $postData['m4'], $postData['m5'], $postData['m6'], $postData['m7'], $postData['m8']
    ];
    $cp = [
        $postData['cp1'], $postData['cp2'], $postData['cp3'], $postData['cp4'], $postData['cp5'], $postData['cp6'], $postData['cp7'], $postData['cp8']
    ];

    // Insert or update into the 'marks' table
    $query = "INSERT INTO marks (roll, tenm, twelm, m1, m2, m3, m4, m5, m6, m7, m8, cp1, cp2, cp3, cp4, cp5, cp6, cp7, cp8)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            //   ON DUPLICATE KEY UPDATE
            //   tenm=VALUES(tenm), twelm=VALUES(twelm), m1=VALUES(m1), m2=VALUES(m2), m3=VALUES(m3), m4=VALUES(m4),
            //   m5=VALUES(m5), m6=VALUES(m6), m7=VALUES(m7), m8=VALUES(m8), cp1=VALUES(cp1), cp2=VALUES(cp2),
            //   cp3=VALUES(cp3), cp4=VALUES(cp4), cp5=VALUES(cp5), cp6=VALUES(cp6), cp7=VALUES(cp7), cp8=VALUES(cp8)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssssssssssss", $username, $marks_10, $marks_12, $marks[0], $marks[1], $marks[2], $marks[3], $marks[4], $marks[5], $marks[6], $marks[7], $cp[0], $cp[1], $cp[2], $cp[3], $cp[4], $cp[5], $cp[6], $cp[7]);
    $stmt->execute();
    echo json_encode(['success' => true]);
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Student Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
    <style>
        .table-container {
            overflow-x: auto;
        }
        .editable {
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
            display: inline-block;
            min-width: 50px;
        }
        .static {
            display: inline-block;
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
                        <td><input type="text" id="student-name" class="w-full border p-2" disabled></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">Roll Number</td>
                        <td>:</td>
                        <td><input type="text" id="student-roll" class="w-full border p-2" disabled></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">Batch</td>
                        <td>:</td>
                        <td><input type="text" id="student-batch" class="w-full border p-2" disabled></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">DOB</td>
                        <td>:</td>
                        <td><input type="text" id="student-dob" class="w-full border p-2" disabled></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">Contact No</td>
                        <td>:</td>
                        <td><input type="text" id="student-contact" class="w-full border p-2" disabled></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">E-Mail</td>
                        <td>:</td>
                        <td><input type="text" id="student-email" class="w-full border p-2" disabled></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">10<sup>th</sup> Marks (in %)</td>
                        <td>:</td>
                        <td><input type="text" id="student-marks-10" class="w-full border p-2" maxlength="2"></td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-gray-600">12<sup>th</sup> Marks (in %)</td>
                        <td>:</td>
                        <td><input type="text" id="student-marks-12" class="w-full border p-2" maxlength="2"></td>
                    </tr>
                </tbody>
            </table>

            <h2 class="text-2xl font-semibold text-gray-700 mb-4">B. Tech. :</h2>
            <div class="table-container mb-8">
                <table class="w-full text-center border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-4 py-2">Semester</th>
                            <th class="border px-4 py-2" colspan="2">Marks</th>
                            <th class="border px-4 py-2">CP</th>
                        </tr>
                        <tr class="bg-gray-100">
                            <th></th> <!-- Empty column for "Semester" header alignment -->
                            <th class="border px-4 py-2">Obtained</th>
                            <th class="border px-4 py-2">Maximum</th>
                            <th></th> <!-- Empty column for "CP" header alignment -->
                        </tr>
                    </thead>
                    <tbody id="academic-record">
                        <tr>
                            <td class="border px-4 py-2">I Semester</td>
                            <td class="border px-4 py-2"><input type="text" id="m1" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><span class="static">900</span></td>
                            <td class="border px-4 py-2"><input type="text" id="cp1" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">II Semester</td>
                            <td class="border px-4 py-2"><input type="text" id="m2" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><span class="static">900</span></td>
                            <td class="border px-4 py-2"><input type="text" id="cp2" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">III Semester</td>
                            <td class="border px-4 py-2"><input type="text" id="m3" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><span class="static">950</span></td>
                            <td class="border px-4 py-2"><input type="text" id="cp3" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">IV Semester</td>
                            <td class="border px-4 py-2"><input type="text" id="m4" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><span class="static">900</span></td>
                            <td class="border px-4 py-2"><input type="text" id="cp4" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">V Semester</td>
                            <td class="border px-4 py-2"><input type="text" id="m5" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><span class="static">950</span></td>
                            <td class="border px-4 py-2"><input type="text" id="cp5" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">VI Semester</td>
                            <td class="border px-4 py-2"><input type="text" id="m6" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><span class="static">900</span></td>
                            <td class="border px-4 py-2"><input type="text" id="cp6" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">VII Semester</td>
                            <td class="border px-4 py-2"><input type="text" id="m7" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><span class="static">950</span></td>
                            <td class="border px-4 py-2"><input type="text" id="cp7" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">VIII Semester</td>
                            <td class="border px-4 py-2"><input type="text" id="m8" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><span class="static">900</span></td>
                            <td class="border px-4 py-2"><input type="text" id="cp8" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end mb-8">
                <button id="save-btn" class="bg-green-500 text-white px-4 py-2 rounded">Save</button>
            </div>
        </div>
    </main>

    <script>
        //Logic to fill the student details from the database
        <?php if ($studentExists): ?> 
            const studentDetails = <?php echo json_encode($studentDetails); ?>; 
        <?php endif; ?>
        <?php if ($marksExists): ?>
            const marksDetails = <?php echo json_encode($marksDetails); ?>; 
        <?php endif; ?>
        //To fill the student details in the input fields
        document.addEventListener('DOMContentLoaded', () => {
            // Fill the input fields with the student details if they exist
        <?php if ($studentExists): ?> 
            document.getElementById("student-name").value = studentDetails.name;
            document.getElementById("student-roll").value = studentDetails.roll;
            document.getElementById("student-batch").value = studentDetails.batch;
            document.getElementById("student-dob").value = studentDetails.dob;
            document.getElementById("student-contact").value = studentDetails.contact;
            document.getElementById("student-email").value = studentDetails.email;
        <?php endif; ?>
        // Fill the input fields with the marks details if they exist
        <?php if ($marksExists): ?>
            document.getElementById("student-marks-10").value = marksDetails.tenm;
            document.getElementById("student-marks-12").value = marksDetails.twelm;
            document.getElementById("m1").value = marksDetails.m1;
            document.getElementById("m2").value = marksDetails.m2;
            document.getElementById("m3").value = marksDetails.m3;
            document.getElementById("m4").value = marksDetails.m4;
            document.getElementById("m5").value = marksDetails.m5;
            document.getElementById("m6").value = marksDetails.m6;
            document.getElementById("m7").value = marksDetails.m7;
            document.getElementById("m8").value = marksDetails.m8;
            document.getElementById("cp1").value = marksDetails.cp1;
            document.getElementById("cp2").value = marksDetails.cp2;
            document.getElementById("cp3").value = marksDetails.cp3;
            document.getElementById("cp4").value = marksDetails.cp4;
            document.getElementById("cp5").value = marksDetails.cp5;
            document.getElementById("cp6").value = marksDetails.cp6;
            document.getElementById("cp7").value = marksDetails.cp7;
            document.getElementById("cp8").value = marksDetails.cp8;
            document.getElementById('student-marks-10').disabled = true;
            document.getElementById('student-marks-12').disabled = true;
            document.getElementById('m1').disabled = true;
            document.getElementById('m2').disabled = true;
            document.getElementById('m3').disabled = true;
            document.getElementById('m4').disabled = true;
            document.getElementById('m5').disabled = true;
            document.getElementById('m6').disabled = true;
            document.getElementById('m7').disabled = true;
            document.getElementById('m8').disabled = true;
            document.getElementById('cp1').disabled = true;
            document.getElementById('cp2').disabled = true;
            document.getElementById('cp3').disabled = true;
            document.getElementById('cp4').disabled = true;
            document.getElementById('cp5').disabled = true;
            document.getElementById('cp6').disabled = true;
            document.getElementById('cp7').disabled = true;
            document.getElementById('cp8').disabled = true;
            // Hide the save button as the details are already saved
            document.getElementById('save-btn').style.display = 'none';
        <?php endif; ?>
            // document.getElementById("student-photo").src = studentDetails.photo;

        });
        //To save the marks of the student in the database
        document.getElementById("save-btn").addEventListener("click", function() {
            // Collect the data from the input fields
            const data = {
                marks10: document.getElementById('student-marks-10').value,
                marks12: document.getElementById('student-marks-12').value,
                m1: document.getElementById('m1').value,
                m2: document.getElementById('m2').value,
                m3: document.getElementById('m3').value,
                m4: document.getElementById('m4').value,
                m5: document.getElementById('m5').value,
                m6: document.getElementById('m6').value,
                m7: document.getElementById('m7').value,
                m8: document.getElementById('m8').value,
                cp1: document.getElementById('cp1').value,
                cp2: document.getElementById('cp2').value,
                cp3: document.getElementById('cp3').value,
                cp4: document.getElementById('cp4').value,
                cp5: document.getElementById('cp5').value,
                cp6: document.getElementById('cp6').value,
                cp7: document.getElementById('cp7').value,
                cp8: document.getElementById('cp8').value
            };
            // Check if all fields are filled
            let allFieldsFilled = true;
            // Check if all fields are filled
            for (const [key, value] of Object.entries(data)) {
                if (value.trim() === "") {
                    alert(`Please fill out all fields. Missing field: ${key.toUpperCase()}`);
                    return; // Stop the function if any field is empty
                }
            }

            // If all fields are filled, proceed with the fetch request and send the data
            fetch('studentdetails.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.text())//It converts the response to text to make it more readable and to use it in the next .then block (Use response.text() when you expect the response from server to be plain text, such as HTML, XML, or plain text files. Use response.json() when you expect the response to be in JSON format.)
            .then(data2 => {//It is used to access the data returned by the previous .then block and then we can use this data to display the message. Also we can name the data anything we want, here we have named it as data2
                alert('Marks saved successfully!');
                window.location.reload(); // Refresh the page
            })
            .catch(error => console.error('Error occurred:', error));
            // .catch(error => console.error('Error occured!'));          
        });
    </script>
    <?php include 'footer.php' ?>
</body>
</html>
