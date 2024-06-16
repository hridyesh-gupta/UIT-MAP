<?php
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="student"){ //If the user is not student, then it means the user is admin and accessing this page through url editing as we have provided student usertype to every user who logged in via student credentials. So, redirecting to login page
    header("location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
</head>
<body>
    <h1>Student Dashboard</h1>
    <p>Welcome Student!</p>
    <a href="logout.php">Logout</a>
</body>
</html>