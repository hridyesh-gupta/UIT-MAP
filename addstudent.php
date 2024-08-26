<!-- Admin 1st page -->
<!-- Refer to dbinsert.php to modify the backend process to add student in DB -->
<?php
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin"){ //If the user is not admin, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
    header("location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Add Students</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php include 'adminheaders.php' ?>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <center><h2 class="text-2xl font-bold mb-6">Add Students</h2></center>

            <div id="form-container" class="mt-8">
                <div id="file-upload-form" class="bg-white p-6 rounded-lg shadow-lg">
                    <form action="dbinsert.php" method="POST" enctype="multipart/form-data">
                        <label class="block mb-2">Upload File:</label>
                        <input type="file" name="uploaded_file" class="w-full p-2 border rounded mb-4" required>
                        <button type="submit" name="upload_file" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition duration-300">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Refer to dbinsert.php to modify the backend process to add student in DB -->

    <!-- Footer -->
    <footer class="bg-blue-500 text-white p-4 mt-8">
        <div class="max-w-6xl mx-auto text-center">
            <p>&copy; 2024 Your College Name. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>