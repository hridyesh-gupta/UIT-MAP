<?php
    include 'dbconnect.php';
    session_start();
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

            
            <form action="changepassword.php" id="changeForm">
                <div class="input-container" id="password-container">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                </div>

                <div class="input-container" id="confirm-password-container">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>

                <button type="submit" class="change-password-button" id="change-password-button">Change Password</button>
                <div class="back-to-login">
                    <a href="index.php">Back to Login</a>
                </div> 
        </form>

        </div>
        
        <div class="right-side">
            <img id="dynamic-image" src="resetpswd.gif" alt="Forgot Password Image">
        </div>
    </div>

    <script>
        const changeForm = document.getElementById('changeForm');
        const changePasswordButton = document.getElementById('change-password-button');
        const passwordContainer = document.getElementById('password-container');
        const confirmPasswordContainer = document.getElementById('confirm-password-container');

        changePasswordButton.addEventListener('click', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword && confirmPassword && newPassword === confirmPassword) {
                alert('Password has been successfully reset.');
                // Redirect to the login page or update the password in the backend
                window.location.href = 'index.php';
            } else {
                alert('Passwords do not match. Please try again.');
            }
        });
    </script>
</body>
</html>
