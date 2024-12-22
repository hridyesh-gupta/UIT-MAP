<!-- 3rd page -->
<?php
error_reporting(0); //To hide the errors
include 'dbconnect.php';
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through URL editing, as we have provided session username to every user who logged in. So, redirecting to the login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="student" && $_SESSION['usertype']!="mentor"){ //If the user is not admin, student, or mentor, then it means the user is accessing this page through URL editing. So, redirecting to the login page
    header("location: index.php");
}
$username = $_SESSION['username'];
$gnum = null;
$groupId = null; 

// Check if user has a gnum (is in a group)
$gnumQuery= "SELECT gnum from groups where roll='$username' LIMIT 1";
$gnumResult= $conn->query($gnumQuery);
if ($gnumResult->num_rows > 0) {
    $gnum = $gnumResult->fetch_assoc()['gnum'];
}
// Check if gnum exists, and if it does, check if user has a group ID (has a project)
if ($gnum) {
    $numberQuery = "SELECT number FROM projinfo WHERE gnum = '$gnum' LIMIT 1";
    $numberResult = $conn->query($numberQuery);
    if ($numberResult->num_rows > 0) {
        $groupId = $numberResult->fetch_assoc()['number'];
    }
}
// Fetch rubrics data through gnum
if ($groupId) {
    $sql = "SELECT batchyr,
        examinerR1, statusR1, evalR1,
        examinerR2, statusR2, evalR2,
        examinerR3, statusR3, evalR3,
        examinerR4, statusR4, evalR4,
        examinerR5, statusR5, evalR5,
        examinerR6, statusR6, evalR6,
        examinerR7, statusR7, evalR7,
        examinerR8, statusR8, evalR8,
        r2ppt, r2pdf, r6ppt, r6pdf
        FROM projinfo WHERE gnum = '$gnum'";
    $rubricsResults = $conn->query($sql);
    if ($rubricsResults->num_rows > 0) {
        $rubricsData = $rubricsResults->fetch_assoc();//To fetch a single row of data from the whole result set
        $batchyr = $rubricsData['batchyr'];//As rubricsData consist of all the columns of the row, so we can directly access the column value by using the column name
    }
    //Fetch last date of all rubrics through batchyr
    $lastDateSql = "SELECT lastR1, lastR2, lastR3, lastR4, lastR5, lastR6, lastR7, lastR8 FROM batches WHERE batchyr = '$batchyr'";
    $lastDateResults = $conn->query($lastDateSql);
    if ($lastDateResults->num_rows > 0) {
        $lastDate = $lastDateResults->fetch_assoc();
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Rubrics Review</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <?php include 'favicon.php' ?>
    <style>
        /* Make sure the header and footer are not blurred */
        header, footer {
            z-index: 1001;
            position: relative;
        }
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php include 'studentheaders.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-4"><center>Rubrics Review</center></h1>
        <?php if (!$gnum): ?>
            <hr class="my-8 border-black-300">
            <center>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Oops!</strong>
                <span class="block sm:inline">You are not assigned to any group.</span>
            </div>
            </center>
        <?php elseif (!$groupId): ?>
            <hr class="my-8 border-black-300">
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                <center>
                <strong class="font-bold">Notice:</strong>
                <span class="block sm:inline">Your group has no project records in our database.</span>
                </center>
            </div>
        <?php else: ?>
            <!-- Container for Rubrics Review -->
            <div id="rubricsContainer" class="space-y-6">
                <!-- Rubrics content will be dynamically added here -->
            </div>
        <?php endif; ?>
    </main>
    <script>
        <?php if ($groupId): ?>
            const rubricsData = <?php echo json_encode($rubricsData); ?>;
            const lastDate = <?php echo json_encode($lastDate); ?>;
        <?php endif; ?>
        // console.log(rubricsData);
        // console.log(lastDate);
        function renderRubricsPage() {  
            const container = document.getElementById("rubricsContainer");
            container.innerHTML = ""; // Clear existing content

            // Find the last "Completed" rubric in the new data structure
            let lastCompletedIndex = 0;

            for (let i = 1; i <= 8; i++) {
                if (lastDate[`lastR${i}`] == null) {
                    lastCompletedIndex = i - 1;
                    break;
                }
                if (i === 8) lastCompletedIndex = 8; // All rubrics are completed
            }

            const maxRubricIndex = lastCompletedIndex + 1;

            // Loop through rubrics up to the last completed + 1
            for (let i = 1; i < maxRubricIndex; i++) {
                const rubricDiv = document.createElement("div");
                rubricDiv.classList.add("bg-beige", "shadow-xl", "rounded-xl", "p-6", "mb-6", "border-t-4", "border-indigo-400");
                <!-- Determine existing URLs from the database-->
                let pptUrl = null;
                let pdfUrl = null;
                if (i == 2 || i == 6) {
                    pptUrl = rubricsData[`r${i}ppt`];
                    pdfUrl = rubricsData[`r${i}pdf`];
                }
                rubricDiv.innerHTML = `
                    <h3 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Rubric R${i}</h3>
                    ${i === 2 || i === 6 ? `
                        <!-- Upload PPT -->
                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Upload Presentation Slides:</label>
                            ${pptUrl ? `
                                <a href="${pptUrl}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 max-w-[219px] w-full justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                    </svg>View Uploaded Slides
                                </a>                          
                            `: `
                                <form id="r${i}pptForm" method="post" enctype="multipart/form-data">
                                    <input type="file" name="r${i}ppt" accept=".ppt,.pptx" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" required>
                                    <button type="submit" class="bg-blue-500 text-white mt-2 py-2 px-4 rounded hover:bg-blue-800 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Submit</button>
                                </form>
                            `}
                        </div>
                        <!-- Upload Report -->
                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Upload Report:</label>
                            ${pdfUrl ? `
                                <a href="${pdfUrl}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 max-w-[219px] w-full justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>View Uploaded Report
                                </a>
                            `: `
                                <form id="r${i}pdfForm" method="post" enctype="multipart/form-data">
                                    <input type="file" name="r${i}pdf" accept=".pdf" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" required>
                                    <button type="submit" class="bg-blue-500 text-white mt-2 py-2 px-4 rounded hover:bg-blue-800 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Submit</button>
                                </form>
                            `}
                        </div>
                    ` : ""}
                    <!-- Last Date -->
                    <div class="mb-5">
                        <label class="block text-gray-700 font-medium mb-2">Last Date:</label>
                        <input type="date" value="${lastDate[`lastR${i}`] || ""}" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" disabled>
                    </div>

                    <!-- Examiner Name -->
                    <div class="mb-5">
                        <label class="block text-gray-700 font-medium mb-2">Examiner Name:</label>
                        <input type="text" value="${rubricsData[`examinerR${i}`] || ""}" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" disabled>
                    </div>

                    <!-- Status -->
                    <div class="mb-5">
                        <label class="block text-gray-700 font-medium mb-2">Status:</label>
                        <select class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" disabled>
                            <option value="Not Completed" ${rubricsData[`statusR${i}`] === "Not Completed" ? "selected" : ""}>Not Completed</option>
                            <option value="Completed" ${rubricsData[`statusR${i}`] === "Completed" ? "selected" : ""}>Completed</option>
                        </select>
                    </div>

                    <!-- Evaluation Date -->
                    <div class="mb-5">
                        <label class="block text-gray-700 font-medium mb-2">Evaluation Date:</label>
                        <input type="date" value="${rubricsData[`evalR${i}`] || ""}" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" disabled>
                    </div>
                `;

                container.appendChild(rubricDiv);
            }
        }

        // Call renderRubricsPage on page load
        document.addEventListener("DOMContentLoaded", renderRubricsPage);
    
        // Function to handle form submission
        function handleFormSubmit(formId) {
            document.getElementById(formId).addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('rubrics.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while uploading files.');
                });
            });
        }

        // Apply the event listener to R2 and R6 forms on page load
        [2, 6].forEach(i => {
            // Add listeners for both PPT and PDF forms
            handleFormSubmit(`r${i}pptForm`);
            handleFormSubmit(`r${i}pdfForm`);
        });

    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
