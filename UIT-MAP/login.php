<?php
//As session variable, we have saved 3 things username, usertype, loginMessage. And if the user is in a group already then this number will increase to 4, i.e. gnum will also be added in this list.

//This code is for checking the login credentials of the user and redirecting to the respective page according to the usertype of the user.
error_reporting(0); //To hide the errors
session_start(); //To start the session

include 'dbconnect.php';
//Including the database connection file in this file so that we can have access of the database

if($_SERVER["REQUEST_METHOD"]=="POST"){
    //Will only execute when someone clicks on login button of form on index.php
    //Getting the values from the form
    $username=$_POST['uniqueId']; 
    $password=$_POST['password'];
    
    // Prepare the SQL query with placeholders
    $sql = "SELECT * FROM user WHERE username=? AND password=?";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind the parameters
    $stmt->bind_param("ss", $username, $password);
    
    // Execute the query
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // Fetch the row
    $row = $result->fetch_array();

    if($row["usertype"]=="admin"){ 
        $_SESSION['username']=$username; //Storing the username in session variable whenever the correct username and password is entered so that when we check those session variables in adminhome.php, we can know that the user is logged in and is not accessing through URL editing. Or we can also use it somewhere else in the whole session.
        $_SESSION['usertype']="admin"; //Storing the usertype in session variable so that we can know that the user is admin
        header("location: adminhome.php"); //If the user is admin redirecting to adminhome.php
    }
    else if($row["usertype"]=="student"){ 
        $_SESSION['username']=$username; //Storing the username in session variable whenever the correct username and password is entered so that when we check those session variables in studentdetails.php, we can know that the user is logged in and is not accessing through URL editing. Or we can also use it somewhere else in the whole session.
        $_SESSION['usertype']="student"; //Storing the usertype in session variable so that we can know that the user is student
        header("location: studentdetails.php"); //If the user is student redirecting to studentdetails.php
    }
    else if($row["usertype"]=="mentor"){ 
        $_SESSION['username']=$username; //Storing the username in session variable whenever the correct username and password is entered so that when we check those session variables in mentorhome.php, we can know that the user is logged in. Or we can also use it somewhere else in the whole session.
        $_SESSION['usertype']="mentor"; //Storing the usertype in session variable so that we can know that the user is student
        header("location: adminhome.php"); //If the user is mentor redirecting to 1st.html
    }
    else{
        $message= "Invalid username or password"; //If the username or password is incorrect(means the username and password entered by user doesn't exist in database), then storing the error message in a variable
        $_SESSION['loginMessage']=$message; //Storing the error message in session variable
        header("location: index.php"); //Redirecting to login page to print the error message there
    }
}
?>