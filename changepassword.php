<?php
    include 'dbconnect.php';
    session_start();

    if(isset($_POST['change-password-button'])){
        $email=mysqli_real_escape_string($conn, $_POST['email']);
        $newPassword=mysqli_real_escape_string($conn, $_POST['newPassword']);
        $confirmPassword=mysqli_real_escape_string($conn, $_POST['confirmPassword']);
        $token=mysqli_real_escape_string($conn, $_POST['token']);

        //To check if the token is there or not in the URL
        if(!empty($token)){

            //To check if the email, new password and confirm password are not empty
            if(!empty($email) && !empty($newPassword) && !empty($confirmPassword)){
                $check_token= "SELECT verify_token FROM info WHERE verify_token='$token' LIMIT 1";
                $check_token_run= mysqli_query($conn, $check_token);
                $row1=mysqli_fetch_array($check_token_run);

                $fetch_username= "SELECT username FROM info WHERE verify_token='$token' LIMIT 1";
                $fetch_username_run= mysqli_query($conn, $fetch_username);
                $row2=mysqli_fetch_array($fetch_username_run);
                $username=$row2['username'];
            
                //To check if the token is valid or not
                if(mysqli_num_rows($check_token_run)>0){
            
                    //To check if the new password and confirm password are same
                    if($newPassword==$confirmPassword){
                        $update_password= "UPDATE user SET password='$newPassword' WHERE username='$username' LIMIT 1";
                        $update_password_run= mysqli_query($conn, $update_password);
            
                        //To check if the password is updated or not
                        if($update_password_run){
                            //To generate new token
                            $new_token=md5(rand())."newtoken";
                            $update_token= "UPDATE info SET verify_token='$new_token' WHERE verify_token='$token' LIMIT 1";
                            $update_token_run= mysqli_query($conn, $update_token);
                            //To inform the user that the password has been successfully reset
                            $_SESSION['status']= "Password updated successfully!";
                            header("Location: index.php");
                            exit(0);
                        }
                        //Means something went wrong and the password is not updated
                        else{
                            $_SESSION['status']= "Please try again! Password not updated.";
                            header("Location: changepassword.php?token=$token&email=$email");
                            exit(0);
                        }
                    }
                    //Means the new password and confirm password are not same
                    else{
                        $_SESSION['status']= "Passwords do not match";
                        header("Location: changepassword.php?token=$token&email=$email");
                        exit(0);
                    }
                }
                //Means the token is invalid
                else{
                    $_SESSION['status']= "Invalid Token";
                    header("Location: forgotpassword.php");
                    exit(0);
                }
            }
            //Means something is missing
            else{
                $_SESSION['status']= "All Fields are mandatory";
                header("Location: changepassword.php?token=$token&email=$email");
                exit(0);
            }
        }
        //Means URL has been tampered
        else{
            $_SESSION['status']= "No Token Available";
            header("Location: forgotpassword.php");
            exit(0);
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #2f3e50;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            display: flex;
            width: 80%;
            max-width: 1200px;
            background-color: #f9f9f9;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .left-side {
            padding: 40px;
            width: 50%;
        }

        .logo {
            width: 100px;
            display: block;
            margin-bottom: 20px;
        }

        h2 {
            color: #34495e;
            font-size: 24px;
            margin-bottom: 10px;
        }

        p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .input-container {
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="number"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #dcdcdc;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.5);
        }

        .change-password-button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .change-password-button:hover {
            background-color: #2980b9;
        }

        .back-to-login {
            margin-top: 20px;
        }

        .back-to-login a {
            color: #3498db;
            text-decoration: none;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .right-side {
            width: 50%;
            background-color: #34495e;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            padding: 20px;
        }

        .right-side img {
            width: 80%;
            max-width: 400px;
            border-radius: 10px;
        }

        .interactive-bg {
            width: 150%;
            height: 150%;
            background-image: radial-gradient(circle at center, #2980b9, #2f3e50);
            border-radius: 50%;
            animation: pulse 3s infinite ease-in-out;
            position: absolute;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <img src="COLLEGE.png" alt="United Institute of Technology Logo" class="logo">
            <h2 id="form-title">Reset Your Password</h2>
            <p id="form-description">Please enter your new password and confirm it.</p>

            
            <form action="changepassword.php" id="changeForm" method="POST">
                <input type="hidden" id="token" name="token" value="<?php if(isset($_GET['token'])){echo $_GET['token'];} ?>">
            
                <div class="input-container" id="email-container">
                    <label for="email">Email Address</label>
                    <input type="text" id="email" value="<?php if(isset($_GET['email'])){echo $_GET['email'];} ?>" name="email" required>
                </div>

                <div class="input-container" id="password-container">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                </div>

                <div class="input-container" id="confirm-password-container">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>

                <button type="submit" class="change-password-button" name="change-password-button" id="change-password-button">Change Password</button>
                <div class="back-to-login">
                    <a href="index.php">Back to Login</a>
                </div> 
        </form>

        </div>
        
        <div class="right-side">
            <img id="dynamic-image" src="resetpswd.gif" alt="Forgot Password Image">
        </div>
    </div>

    <!-- <script>
        const changeForm = document.getElementById('changeForm');
        const changePasswordButton = document.getElementById('change-password-button');
        const passwordContainer = document.getElementById('password-container');
        const confirmPasswordContainer = document.getElementById('confirm-password-container');

        changePasswordButton.addEventListener('click', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword == confirmPassword) {
                alert('Password has been successfully reset.');
                // Redirect to the login page or update the password in the backend
                window.location.href = 'index.php';
            } else {
                alert('Passwords do not match. Please try again.');
            }
        });
    </script> -->
</body>
</html>
