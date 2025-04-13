<?php
error_reporting(0); //To hide the errors
include 'dbconnect.php';
include 's3client.php';
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
    $numberQuery = "SELECT number,dAppDate FROM projinfo WHERE gnum = '$gnum' LIMIT 1";
    $numberResult = $conn->query($numberQuery);
    if ($numberResult->num_rows > 0) {
        $row = $numberResult->fetch_assoc();
        $groupId = $row['number'];
        $dAppDate = $row['dAppDate'];
    }
}
// Fetch rubrics data through gnum
if ($groupId) { //Means grp has a project as grpid is only allotted after the project details have been submitted
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
    $lastDateExists = false;
    $lastDateSql = "SELECT lastR1, lastR2, lastR3, lastR4, lastR5, lastR6, lastR7, lastR8 FROM batches WHERE batchyr = ?";
    $stmt = $conn->prepare($lastDateSql);
    $stmt->bind_param("s", $batchyr);
    $stmt->execute();
    $lastDateResults = $stmt->get_result();
    $lastDate = $lastDateResults->fetch_assoc();
    $last = $lastDate['lastR1'];
    if ($last) {
        $lastDateExists = true;
    }
    $stmt->close();
}
// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES)) {
    try {
        $uploadedFile = null;
        $columnName = '';
        $maxSize = 0; // Will store max allowed size in bytes
        
        // Determine which file was uploaded
        if (isset($_FILES['r2ppt'])) {
            $uploadedFile = $_FILES['r2ppt'];
            $columnName = 'r2ppt';
            $maxSize = 10 * 1024 * 1024; // 10MB for PPT files
        } elseif (isset($_FILES['r2pdf'])) {
            $uploadedFile = $_FILES['r2pdf'];
            $columnName = 'r2pdf';
            $maxSize = 5 * 1024 * 1024; // 5MB for PDF files
        } elseif (isset($_FILES['r6ppt'])) {
            $uploadedFile = $_FILES['r6ppt'];
            $columnName = 'r6ppt';
            $maxSize = 10 * 1024 * 1024; // 10MB for PPT files
        } elseif (isset($_FILES['r6pdf'])) {
            $uploadedFile = $_FILES['r6pdf'];
            $columnName = 'r6pdf';
            $maxSize = 5 * 1024 * 1024; // 5MB for PDF files
        }

        if ($uploadedFile && $uploadedFile['error'] === UPLOAD_ERR_OK) {
            // Check file size
            if ($uploadedFile['size'] > $maxSize) {
                $maxSizeMB = $maxSize / (1024 * 1024);
                echo "File size too large! Maximum allowed size is {$maxSizeMB}MB.";
                exit;
            }

            // Generate unique filename
            $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
            $filename = 'Grp_' . $groupId . '_' . $columnName . '_' . uniqid() . '_' . time() . '.' . $extension;// Max 65 characters: 4 + 10 + 1 + 20 + 1 + 13 + 1 + 10 + 1 + 4 = 65 characters
            
            // Upload to Tebi
            $result = $s3Client->putObject([
                'Bucket' => $TEBI_BUCKET,
                'Key' => $batchyr . '/' . $filename,
                'SourceFile' => $uploadedFile['tmp_name'],
                'ACL' => 'public-read'
            ]);

            // Get the URL
            $fileUrl = $result['ObjectURL'];
            
            // Update database with prepared statement
            $sql = "UPDATE projinfo SET $columnName = ? WHERE gnum = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $fileUrl, $gnum);
            $success = $stmt->execute();
            $stmt->close();

            if(!$success) {
                echo json_encode(['success' => false, 'message' => 'Error inserting data!']);
            } else {
                echo json_encode(['success' => true, 'message' => 'File uploaded successfully!']);
            }
            exit;
        }
    } catch (Exception $e) {
        echo "Something went wrong! Try again later.";
        exit;
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

<?php include 'studentheaders.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-4"><center>Rubrics Review</center></h1>
        <?php if (!$gnum): ?>
            <hr class="my-8 border-black-300">
            <center>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Notice:</strong>
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
        <?php elseif (!$dAppDate): ?>
            <hr class="my-8 border-black-300">
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <center>
                <strong class="font-bold">Notice:</strong>
                <span class="block sm:inline">No mentor has been allotted to your group.</span>
                </center>
            </div>
        <?php elseif (!$lastDateExists): ?>
            <hr class="my-8 border-black-300">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <center>
                <strong class="font-bold">Notice:</strong>
                <span class="block sm:inline">Rubrics Review for your batch has not been started yet.</span>
                </center>
            </div>
        <?php else: ?>
            <!-- Container for Rubrics Review -->
            <div id="rubricsContainer" class="space-y-6">
                <!-- Rubrics content will be dynamically added here -->
            </div>
        <?php endif; ?>
    </main>

    <!-- Spinner Overlay -->
    <div id="spinner" class="spinner-overlay">
        <div class="spinner"></div>
    </div>

    <script>
        <?php if ($groupId): ?>
            const rubricsData = <?php echo json_encode($rubricsData); ?>;
            const lastDate = <?php echo json_encode($lastDate); ?>;
        <?php endif; ?>
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
                // Determine existing URLs from the database
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
                                <a onclick="openDocument('${pptUrl}')" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 max-w-[219px] w-full justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                    </svg>View Uploaded Slides
                                </a>                          
                            `: `
                                <form id="r${i}pptForm" method="post" enctype="multipart/form-data" onsubmit="return validateFileSize(this.querySelector('input[type=file]'))">
                                    <input type="file" name="r${i}ppt" accept=".ppt,.pptx" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" required onchange="validateFileSize(this)">
                                    <button type="submit" class="bg-blue-500 text-white mt-2 py-2 px-4 rounded hover:bg-blue-800 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Submit</button>
                                </form>
                            `}
                        </div>
                        <!-- Upload Report -->
                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Upload Report:</label>
                            ${pdfUrl ? `
                                <a onclick="openDocument('${pdfUrl}')" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 max-w-[219px] w-full justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>View Uploaded Report
                                </a>
                            `: `
                                <form id="r${i}pdfForm" method="post" enctype="multipart/form-data" onsubmit="return validateFileSize(this.querySelector('input[type=file]'))">
                                    <input type="file" name="r${i}pdf" accept=".pdf" class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" required onchange="validateFileSize(this)">
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
                        <input type="text" value="${customEncode(rubricsData[`examinerR${i}`] || "")}" 
                               oninput="this.value = customDecode(this.value)"
                               class="w-full p-4 border-2 border-gray-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out shadow-md hover:shadow-lg" disabled>
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
    
        // Function to handle form submission to upload files when submit button is pressed
        function handleFormSubmit(formId) {
            document.getElementById(formId).addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const spinner = document.getElementById('spinner');
                const submitBtn = this.querySelector('button[type="submit"]');
                const fileInput = this.querySelector('input[type="file"]');
                
                const confirmUpload = confirm('Are you sure you want to upload this file?');
                if (confirmUpload) {
                    // Show spinner and disable button
                    if (spinner) spinner.style.display = 'flex';
                    if (submitBtn) submitBtn.disabled = true;
                    if (submitBtn) submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    if (fileInput) fileInput.disabled = true;

                    fetch('rubrics.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('File uploaded successfully.');
                            window.location.reload();
                        } else {
                            throw new Error(data.message || 'Upload failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while uploading the file. Please try again.');
                    })
                    .finally(() => {
                        // Hide spinner and enable button
                        if (spinner) spinner.style.display = 'none';
                        if (submitBtn) submitBtn.disabled = false;
                        if (submitBtn) submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        if (fileInput) fileInput.disabled = false;
                    });
                }
            });
        }

        // Apply the event listener to R2 and R6 forms on page load
        document.addEventListener('DOMContentLoaded', () => {
    [2, 6].forEach(i => {
        const pptForm = document.getElementById(`r${i}pptForm`);
        const pdfForm = document.getElementById(`r${i}pdfForm`);

        if (pptForm) handleFormSubmit(`r${i}pptForm`);
        if (pdfForm) handleFormSubmit(`r${i}pdfForm`);
    });
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

        // To open the document in new tab
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

        function validateFileSize(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const isPPT = input.name.includes('ppt');
                const maxSize = isPPT ? 10 * 1024 * 1024 : 5 * 1024 * 1024; // 10MB for PPT, 5MB for PDF
                const maxSizeMB = maxSize / (1024 * 1024);
                
                if (file.size > maxSize) {
                    alert(`File size too large! Maximum allowed size is ${maxSizeMB}MB.`);
                    input.value = ''; // Clear the file input
                    return false;
                }
                return true;
            }
            return false;
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
