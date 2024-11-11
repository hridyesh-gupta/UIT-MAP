//As session variable, we have saved 3 things username, usertype, loginMessage. And if the user is in a group already then this number will increase to 4, i.e. gnum will also be added in this list.

<?php
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
    //Checking whether the entered username and password exists in the database or not
    $sql="SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result=mysqli_query($conn,$sql); 
    //Through $conn parameter(which is initialised in the dbconnect.php), the function knows which database connection to use when executing the query and through $sql parameter, the function knows which query to execute. And mysqli_query() function returns the whole result set of all those rows(users) which satisfy the condition in sql query. But this result set will only contain single row as the username is unique. 
    
    //And this fetched result set can't be used directly. So, we need to fetch the data from the result set using mysqli_fetch_array() function which will fetch the first row of result set. 
    $row=mysqli_fetch_array($result); 

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
