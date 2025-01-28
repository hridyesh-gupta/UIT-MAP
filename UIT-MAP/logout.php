<?php 
session_start(); //session_start function do 2 things. It checks if a session has already been started on other pages which has redirected the user and resumes it if it exists. If a session has not been started anywhere then it will create a new session.
session_destroy(); //To destroy the session

header("location: index.php"); //Redirecting to login page


?>