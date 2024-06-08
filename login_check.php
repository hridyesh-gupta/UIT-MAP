<?php
$host="localhost";
$user="root";
$password="";
$db="mapdb";

$data=mysqli_connect($host,$user,$password,$db); //To connect with mysql db

if($data===false){
    die("ERROR in connection");
}
if($_SERVER["REQUEST_METHOD"]=="POST"){
    //Will only execute when someone clicks on login button of form
    //Getting the values from the form
    $username=$_POST['uniqueId']; 
    $password=$_POST['password'];
    
    $sql="SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result=mysqli_query($data,$sql); 
    //Through $data parameter, the function knows which database connection to use when executing the query and through $sql parameter, the function knows which query to execute. And mysqli_query() function returns the whole result set of all those rows(users) which satisfy the condition in sql query. But this result set will only contain single row as the username is unique. And this fetched result set can't be used directly. So, we need to fetch the data from the result set using mysqli_fetch_array() function which will fetch the first row of result set. 
    $row=mysqli_fetch_array($result); 

    if($row["usertype"]=="admin"){ //If the user is admin redirecting to adminhome.php
        header("location: adminhome.php"); 
    }
    else if($row["usertype"]=="student"){ //If the user is student redirecting to studenthome.php
        header("location: studenthome.php"); 
    }
    else{
        echo "Invalid username or password"; //If the username or password is incorrect
    }
}
?>