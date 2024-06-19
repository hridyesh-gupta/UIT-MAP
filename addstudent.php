<!-- Admin 1st page -->
<?php
session_start();
if(!(isset($_SESSION['username']))){  //If the session variable is not set, then it means the user is not logged in and is accessing this page through url editing, as we have provided session username to every user who logged in. So, redirecting to login page
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin"){ //If the user is not admin, then it means the user is student and is accessing this page through url editing as we have provided admin usertype to every user who logged in via admin credentials. So, redirecting to login page
    header("location: index.php");
}

if(isset($_SESSION['message'])){ //This will execute if the session variable message is set i.e. if the message is stored in session variable during the time of adding the user
    echo "<script>alert('".$_SESSION['message']."')</script>"; //This will display the message stored in session variable
    unset($_SESSION['message']); //This will unset the session variable message after getting displayed once so that it will not get displayed again and again on reloading the page
}

$host="localhost";
$user="root";
$password="";
$db="mapdb";

$data=mysqli_connect($host,$user,$password,$db); //To connect with mysql db

if(isset($_POST['add_student'])){
    $username=$_POST['username'];
    $phone=$_POST['phone'];
    $email=$_POST['email'];
    $password=$_POST['password'];
    $usertype="student";
    
    if($data === false) {
        die("Failed to connect to database: " . mysqli_connect_error());
    }
    //Logic to only accept unique username
    $check="SELECT * FROM user WHERE username='$username'";
    $check_user=mysqli_query($data,$check);//In check_user all the user with same username will be stored 
    $count=mysqli_num_rows($check_user);//To count the number of rows with same username
    
    if($count>0){ //This will execute if even a single user with same username is found
        $_SESSION['message']="Username already exists!";//Here we are storing the message in session variable so that we can display the message after the page is redirected to the same page, we can have used alert under echo to display the message at that instant but it will not be displayed as the page is getting redirected to the same page, so we are storing the message in session variable and displaying it after the page is redirected to the same page
        header("Location: addstudent.php");//This will redirect to the same page after already present username is entered, to avoid the resubmission of same details on reloading the page
        exit;
    }
    else{ //This will execute if no user with same username is found
        $sql="INSERT INTO user (username, phone, email, usertype, password) VALUES ('$username','$phone','$email', '$usertype', '$password')";
        $result=mysqli_query($data,$sql);//Here in result, the boolean value will be stored whether the query is executed successfully or not
        if($result){//If true  boolean value is present in result
            $_SESSION['message']="Student added successfully!";//Here we are storing the message in session variable so that we can display the message after the page is redirected to the same page, we can have used alert under echo to display the message at that instant but it will not be displayed as the page is getting redirected to the same page, so we are storing the message in session variable and displaying it after the page is redirected to the same page
            header("Location: addstudent.php");//This will redirect to the same page after adding the student to avoid the resubmission of same details on reloading the page
            exit;
        } else {//Boolean value not present in result
            $_SESSION['message']="Failed to add student!";//Here we are storing the message in session variable so that we can display the message after the page is redirected to the same page, we can have used alert under echo to display the message at that instant but it will not be displayed as the page is getting redirected to the same page, so we are storing the message in session variable and displaying it after the page is redirected to the same page
            header("Location: addstudent.php");//This will redirect to the same page after student upload is failed to avoid the resubmission of same details on reloading the page
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Add Student</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">

<?php include 'adminheaders.php' ?>

    <!-- Main Content -->
    <main class="flex-grow bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <center><h2 class="text-2xl font-bold mb-6">Add Student</h2></center>

            <!-- <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <button onclick="showForm('student')" class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-green-600 transition duration-300">Add Student</button>
                <button class="bg-blue-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">View Students</button>
                <button onclick="showForm('mentor')" class="bg-green-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">Add Mentor</button>
                <button class="bg-blue-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300">View Mentors</button>
                <button class="bg-red-500 text-white py-4 px-6 rounded-lg shadow-lg hover:bg-red-600 transition duration-300 col-span-full sm:col-span-2 lg:col-span-1">Logout</button>
            </div> -->

            <!-- Forms -->
            <div id="form-container" class="mt-8">
                <div id="student-form" class="bg-white p-6 rounded-lg shadow-lg">
                    <form action="#" method="POST">
                        <label class="block mb-2">User Name:</label>
                        <input type="text" name="username" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Contact Number:</label>
                        <input type="text" name="phone" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">E-mail ID:</label>
                        <input type="email" name="email" class="w-full p-2 border rounded mb-4" required>
                        <label class="block mb-2">Password:</label>
                        <input type="password" name="password" class="w-full p-2 border rounded mb-4" required>
                        <button type="submit" name="add_student" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition duration-300">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-blue-500 text-white p-4 mt-8">
        <div class="max-w-6xl mx-auto text-center">
            <p>&copy; 2024 Your College Name. All rights reserved.</p>
        </div>
    </footer>

    <!-- <script>
        function showForm(type) {
            document.getElementById('form-container').classList.remove('hidden');
            if (type === 'student') {
                document.getElementById('mentor-form').classList.add('hidden');
                document.getElementById('student-form').classList.remove('hidden');
            } else if (type === 'mentor') {
                document.getElementById('student-form').classList.add('hidden');
                document.getElementById('mentor-form').classList.remove('hidden');
            }
        }
    </script> -->

</body>
</html>
