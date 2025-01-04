<?php
// Student 1st page
session_start();
//These headers tell browsers not to cache the page.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="student" && $_SESSION['usertype']!="mentor"){ //If the user is not admin, student, or mentor, then it means the user is accessing this page through url editing. So, redirecting to login page
    header("location: index.php");
}

include 'dbconnect.php';
require 's3client.php';

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

//To fetch batchyr from the info table
$username = $_SESSION['username'];
$query = "SELECT batchyr FROM info WHERE username = '$username'";
$result = $conn->query($query);
$batchyr = $result->fetch_assoc()['batchyr'];

//To save the marks of the student in the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a file upload request to upload image
    if (isset($_FILES['profile_image'])) { // Will be set when a user selects a file using the profile_image input and submits the form

        $file = $_FILES['profile_image']; // Uploaded file data
        
        // Check file size (1MB = 1048576 bytes)
        if ($file['size'] > 1048576) {
            echo "Please upload an image less than 1MB!";
            exit();
        }

        $fileName = $username . '.png'; // Default filename for regular uploads

        try {
            $useFallback = false; // Flag to check if fallback filename is needed

            /**
             * Step 1: Check for an existing image in the database
             * If a previous image exists in the DB then forsure it will be in TEBI, so attempt to delete it from the storage bucket.
             */
            $query = "SELECT image FROM info WHERE username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $previousImage = $result->fetch_assoc();
            $stmt->close();

            if ($previousImage && $previousImage['image']) { // $previousImage: Ensures the query returned some data & $previousImage['image']: Ensures the image key exists in the query result and has a value
                /** Extract the key from the previous URL stored in the DB to identify the file in the bucket as public URL cannot be directly used for deletion.
                 *  Before str_replace: https://s3.tebi.io/myBucket/user123.img
                 *  After str_replace: user123.img
                */
                $previousKey = str_replace("https://s3.tebi.io/{$TEBI_BUCKET2}/", "", $batchYear . '/' .$previousImage['image']); 
                
                try {
                    // Attempt to delete the previous image from Tebi bucket
                    $s3Client2->deleteObject([
                        'Bucket' => $TEBI_BUCKET2,
                        'Key' => $previousKey
                    ]);
                } catch (Exception $e) {
                    // If deletion fails, mark the flag true and use a different filename
                    $useFallback = true;
                    $fileName = $username . '_' . time() . '.png';
                }
            }

            /**
             * Step 2: Upload the new image to the Tebi storage bucket
             * Use either the default or fallback filename based on deletion status.
             * And if no image was found in the DB then upload fresh image in DB
             */
            $result = $s3Client2->putObject([
                'Bucket' => $TEBI_BUCKET2,
                'Key' => $batchyr . '/' . $fileName,
                'Body' => fopen($file['tmp_name'], 'rb'), // Opens the temporary uploaded file in binary read mode, allowing it to be read and sent as part of a request, and uploads the file permanently
                'ACL' => 'public-read' // Make the file publicly accessible
            ]);

            // Retrieve the public URL of the uploaded image
            $imageUrl = $result['ObjectURL'];

            /**
             * Step 3: Update the image URL in the database for the user
             */
            $updateQuery = "UPDATE info SET image = ? WHERE username = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ss", $imageUrl, $username);
            $stmt->execute();
            $stmt->close();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();

        } catch (Exception $e) {
            // Handle any errors during the upload or database update process
            echo "Error: " . $e->getMessage();
        }
    } 
    // Handle JSON data for marks update
    else {
        // Get the raw POST data
        $postData = json_decode(file_get_contents('php://input'), true);
    
        if ($postData === null) {
            echo json_encode(['success' => 'false', 'message' => 'Invalid JSON data received']);
            exit;
        }
    
        $username = $_SESSION['username'];
        $marks_10 = $postData['marks10'] ?? null;
        $marks_12 = $postData['marks12'] ?? null;
    
        // Prepare dynamic fields for query
        $fields = [];
        $values = [];
        $types = '';
    
        if ($marks_10 !== null) {
            $fields[] = 'tenm = ?';
            $values[] = $marks_10;
            $types .= 's';
        }
    
        if ($marks_12 !== null) {
            $fields[] = 'twelm = ?';
            $values[] = $marks_12;
            $types .= 's';
        }
    
        foreach (range(1, 8) as $i) {
            foreach (['m', 'mm', 'cp'] as $prefix) {
                $key = $prefix . $i;
                if (isset($postData[$key])) {
                    $fields[] = "$key = ?";
                    $values[] = $postData[$key];
                    $types .= 's';
                }
            }
        }
    
        if (empty($fields)) {
            echo json_encode(['success' => 'false', 'message' => 'No valid fields to update']);
            exit;
        }
    
        // Add the username for WHERE condition
        $values[] = $username;
        $types .= 's';
    
        // Check if the user already has an entry
        $checkQuery = "SELECT * FROM marks WHERE roll = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Update existing record
            $query = "UPDATE marks SET " . implode(', ', $fields) . " WHERE roll = ?";
        } else {
            // Insert new record
            $fields[] = 'roll = ?';
            $query = "INSERT INTO marks SET " . implode(', ', $fields);
        }
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$values);
    
        if ($stmt->execute()) {
            echo json_encode(['success' => 'true', 'message' => 'Marks saved successfully!']);
            exit;
        } else {
            echo json_encode(['success' => 'false', 'message' => 'Failed to save marks!']);
            exit;
        }
    
        $stmt->close();
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Student Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
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
        .profile-image {
            width: 128px;
            height: 128px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
        }
        .image-upload-form {
            margin-top: 0.5rem;
            text-align: center;
        }
        .file-input {
            display: none;
        }
        .upload-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #4B5563;
            color: white;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .upload-btn:hover {
            background-color: #374151;
        }
        
        /* Cropper Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
        }
        
        .crop-container {
            max-width: 100%;
            height: 400px;
            margin: 20px 0;
        }
        
        #cropImage {
            max-width: 100%;
            max-height: 400px;
        }
        
        .crop-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .crop-btn {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .crop-cancel {
            background-color: #ef4444;
            color: white;
        }
        
        .crop-save {
            background-color: #10b981;
            color: white;
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

        .save-btn-container {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .save-btn-text {
            display: inline-block;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 flex flex-col min-h-screen">

    <?php include 'studentheaders.php' ?>

    <!-- Image Cropper Modal -->
    <div id="cropperModal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-semibold mb-4">Crop Image</h3>
            <div class="crop-container">
                <img id="cropImage" src="" alt="Image to crop">
            </div>
            <div class="crop-buttons">
                <button class="crop-btn crop-cancel" onclick="cancelCrop()">Cancel</button>
                <button class="crop-btn crop-save" onclick="saveCrop()">Save & Upload</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow my-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-5">
                <h2 class="text-2xl font-semibold text-gray-700">Student Bio-data</h2>
                <div class="w-32 h-32 mt-4 md:mt-0">
                <!-- The ?v=timestamp parameter forces the browser to treat it as a new image each time to avoid the problem of new image not uploading. -->
                 <!-- When you append ?v=somevalue to an image URL, it does not change the core URL itself. Instead, it simply acts as a query parameter that most servers and CDNs (like Tebi in your case) ignore by default when serving static files. So even after appending this parameter your image will be fetched as URL isn't changed. -->
                    <img id="student-photo" 
                         src="<?php echo !empty($studentDetails['image']) ? $studentDetails['image'] . '?v=' . time() : 'https://s3.tebi.io/imgbucket/placeholder.png'; ?>" 
                         alt="Student Photo" 
                         class="profile-image">
                    <form class="image-upload-form" method="POST" enctype="multipart/form-data">
                        <input type="file" name="profile_image" id="profile_image" class="file-input" accept="image/*">
                        <label for="profile_image" class="upload-btn">Update Image</label>
                    </form>
                </div>
            </div>
            <br>
            <br>

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
                        <td class="font-semibold text-gray-600">Branch</td>
                        <td>:</td>
                        <td><input type="text" id="student-branch" class="w-full border p-2" disabled></td>
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
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr class="bg-blue-100">
                            <th class="border px-4 py-2" rowspan="2">Semester</th>
                            <th class="border px-4 py-2" colspan="2">Marks</th>
                            <th class="border px-4 py-2" rowspan="2">CP</th>
                        </tr>
                        <tr class="bg-blue-100">
                            <th class="border px-4 py-2">Obtained</th>
                            <th class="border px-4 py-2">Maximum</th>
                        </tr>
                    </thead>
                    <tbody id="academic-record">
                        <tr>
                            <td class="border px-4 py-2"><center>I Semester</center></td>
                            <td class="border px-4 py-2"><input type="text" id="m1" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="mm1" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="cp1" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2"><center>II Semester</center></td>
                            <td class="border px-4 py-2"><input type="text" id="m2" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="mm2" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="cp2" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2"><center>III Semester</center></td>
                            <td class="border px-4 py-2"><input type="text" id="m3" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="mm3" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="cp3" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2"><center>IV Semester</center></td>
                            <td class="border px-4 py-2"><input type="text" id="m4" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="mm4" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="cp4" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2"><center>V Semester</center></td>
                            <td class="border px-4 py-2"><input type="text" id="m5" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="mm5" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="cp5" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2"><center>VI Semester</center></td>
                            <td class="border px-4 py-2"><input type="text" id="m6" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="mm6" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="cp6" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2"><center>VII Semester</center></td>
                            <td class="border px-4 py-2"><input type="text" id="m7" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="mm7" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="cp7" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2"><center>VIII Semester</center></td>
                            <td class="border px-4 py-2"><input type="text" id="m8" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="mm8" class="editable w-full border p-2 text-center" maxlength="3"></td>
                            <td class="border px-4 py-2"><input type="text" id="cp8" class="editable w-full border p-2 text-center" maxlength="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end mb-8">
                <button id="save-btn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors duration-200">
                    <span class="save-btn-text">Save</span>
                </button>
            </div>
        </div>
    </main>

    <div id="spinner" class="spinner-overlay">
        <div class="spinner"></div>
    </div>

    <script>
        let cropper = null;
        
        // Handle file selection with size validation
        document.getElementById('profile_image').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Check file size (1MB = 1048576 bytes)
                if (file.size > 1048576) {
                    alert('Please select an image less than 1MB!');
                    this.value = ''; // Clear the file input
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Show the cropper modal
                    document.getElementById('cropperModal').style.display = 'block';
                    
                    // Set the image in the cropper
                    const image = document.getElementById('cropImage');
                    image.src = e.target.result;
                    
                    // Initialize cropper
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false
                    });
                };
                
                reader.readAsDataURL(file);
            }
        });

        function cancelCrop() {
            document.getElementById('cropperModal').style.display = 'none';
            document.getElementById('profile_image').value = ''; // Clear the file input
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        }

        function saveCrop() {
            if (!cropper) return;
            
            const spinner = document.getElementById('spinner');
            const saveBtn = document.querySelector('.crop-save');
            const cancelBtn = document.querySelector('.crop-cancel');
            
            try {
                // Show spinner and disable buttons
                spinner.style.display = 'flex';
                saveBtn.disabled = true;
                cancelBtn.disabled = true;
                saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Get the cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400
                });
                
                // Convert canvas to blob with quality control
                canvas.toBlob(function(blob) {
                    // Check if the cropped image size is within limits
                    if (blob.size > 1048576) {
                        alert('The cropped image is larger than 1MB. Please try a smaller selection or a different image.');
                        // Hide spinner and enable buttons
                        spinner.style.display = 'none';
                        saveBtn.disabled = false;
                        cancelBtn.disabled = false;
                        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        cancelBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        return;
                    }
                    
                    // Create a new File object
                    const croppedFile = new File([blob], 'cropped_image.png', { type: 'image/png' });
                    
                    // Create FormData and append the cropped file
                    const formData = new FormData();
                    formData.append('profile_image', croppedFile);
                    
                    // Upload the cropped image
                    fetch('studentdetails.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (response.ok) {
                            window.location.href = window.location.pathname + '?t=' + new Date().getTime();
                        } else {
                            throw new Error('Upload failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to upload image. Please try again!');
                        // Hide spinner and enable buttons on error
                        spinner.style.display = 'none';
                        saveBtn.disabled = false;
                        cancelBtn.disabled = false;
                        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        cancelBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    })
                    .finally(() => {
                        // Close the modal and cleanup
                        document.getElementById('cropperModal').style.display = 'none';
                        if (cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                    });
                }, 'image/png');
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing the image.');
                // Hide spinner and enable buttons on error
                spinner.style.display = 'none';
                saveBtn.disabled = false;
                cancelBtn.disabled = false;
                saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                cancelBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('cropperModal');
            if (event.target == modal) {
                cancelCrop();
            }
        }

        // Remove the old automatic form submission
        document.getElementById('profile_image').removeEventListener('change', function() {
            if (this.files && this.files[0]) {
                this.form.submit();
            }
        });
        
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
            document.getElementById("student-batch").value = studentDetails.batchyr;
            document.getElementById("student-branch").value = studentDetails.branch;
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
            document.getElementById("mm1").value = marksDetails.mm1;
            document.getElementById("mm2").value = marksDetails.mm2;
            document.getElementById("mm3").value = marksDetails.mm3;
            document.getElementById("mm4").value = marksDetails.mm4;
            document.getElementById("mm5").value = marksDetails.mm5;
            document.getElementById("mm6").value = marksDetails.mm6;
            document.getElementById("mm7").value = marksDetails.mm7;
            document.getElementById("mm8").value = marksDetails.mm8;
            document.getElementById("cp1").value = marksDetails.cp1;
            document.getElementById("cp2").value = marksDetails.cp2;
            document.getElementById("cp3").value = marksDetails.cp3;
            document.getElementById("cp4").value = marksDetails.cp4;
            document.getElementById("cp5").value = marksDetails.cp5;
            document.getElementById("cp6").value = marksDetails.cp6;
            document.getElementById("cp7").value = marksDetails.cp7;
            document.getElementById("cp8").value = marksDetails.cp8;
        <?php endif; ?>

        });
        //To save the marks of the student in the database
        document.getElementById("save-btn").addEventListener("click", async function() {
            const spinner = document.getElementById('spinner');
            const saveBtn = document.getElementById('save-btn');
            
            try {
                // Show spinner and disable button
                spinner.style.display = 'flex';
                saveBtn.disabled = true;
                saveBtn.classList.add('opacity-50', 'cursor-not-allowed');

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
                    mm1: document.getElementById('mm1').value,
                    mm2: document.getElementById('mm2').value,
                    mm3: document.getElementById('mm3').value,
                    mm4: document.getElementById('mm4').value,
                    mm5: document.getElementById('mm5').value,
                    mm6: document.getElementById('mm6').value,
                    mm7: document.getElementById('mm7').value,
                    mm8: document.getElementById('mm8').value,
                    cp1: document.getElementById('cp1').value,
                    cp2: document.getElementById('cp2').value,
                    cp3: document.getElementById('cp3').value,
                    cp4: document.getElementById('cp4').value,
                    cp5: document.getElementById('cp5').value,
                    cp6: document.getElementById('cp6').value,
                    cp7: document.getElementById('cp7').value,
                    cp8: document.getElementById('cp8').value
                };

                const response = await fetch('studentdetails.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert("Error: " + (error.message || "Failed to save marks"));
            } finally {
                // Hide spinner and enable button
                spinner.style.display = 'none';
                saveBtn.disabled = false;
                saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
    </script>
    <?php include 'footer.php' ?>
</body>
</html>
