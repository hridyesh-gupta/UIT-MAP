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

        .reset-button,
        .verify-button,
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

        .reset-button:hover,
        .verify-button:hover,
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
            <h2 id="form-title">Forgot Your Password?</h2>
            <p id="form-description">No worries! Enter your email address, and we'll send you a link to reset your password.</p>

            <form id="resetForm">
                <div class="input-container" id="email-container">
                    <label for="uniqueId">Unique ID or Email</label>
                    <input type="text" id="uniqueId" name="uniqueId" required>
                </div>

                <div class="input-container" id="otp-container" style="display: none;">
                    <label for="otp">Enter OTP</label>
                    <input type="number" id="otp" name="otp" required>
                </div>

                <div class="input-container" id="password-container" style="display: none;">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                </div>

                <div class="input-container" id="confirm-password-container" style="display: none;">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>

                <button type="button" class="reset-button" id="reset-button">Send OTP</button>
                <button type="button" class="verify-button" id="verify-button" style="display: none;">Verify OTP</button>
                <button type="button" class="change-password-button" id="change-password-button" style="display: none;">Change Password</button>
            </form>

            <div class="back-to-login">
                <a href="index.php">Back to Login</a>
            </div>
        </div>
        
        <div class="right-side">
            <img id="dynamic-image" src="Frgtpswd.gif" alt="Forgot Password Image">
        </div>
    </div>

    <script>
        const resetForm = document.getElementById('resetForm');
        const resetButton = document.getElementById('reset-button');
        const verifyButton = document.getElementById('verify-button');
        const changePasswordButton = document.getElementById('change-password-button');
        const emailContainer = document.getElementById('email-container');
        const otpContainer = document.getElementById('otp-container');
        const passwordContainer = document.getElementById('password-container');
        const confirmPasswordContainer = document.getElementById('confirm-password-container');
        const formTitle = document.getElementById('form-title');
        const formDescription = document.getElementById('form-description');
        const dynamicImage = document.getElementById('dynamic-image');
        let generatedOtp = '';

        resetButton.addEventListener('click', function() {
            const uniqueId = document.getElementById('uniqueId').value;

            if (uniqueId) {
                // Simulate sending OTP
                generatedOtp = Math.floor(100000 + Math.random() * 900000); // Generate a 6-digit OTP
                alert(`OTP has been sent to ${uniqueId}. Your OTP is ${generatedOtp} (This is a simulation).`);

                // Update the form to ask for the OTP
                emailContainer.style.display = 'none';
                otpContainer.style.display = 'block';
                resetButton.style.display = 'none';
                verifyButton.style.display = 'block';

                formTitle.textContent = 'Enter the OTP';
                formDescription.textContent = 'Please enter the OTP sent to your registered email address.';
                dynamicImage.src = 'OTP.gif';
            } else {
                alert('Please enter a valid Unique ID or Email.');
            }
        });

        verifyButton.addEventListener('click', function() {
            const enteredOtp = document.getElementById('otp').value;

            if (enteredOtp == generatedOtp) {
                alert('OTP verified successfully.');

                // Update the form to ask for a new password
                otpContainer.style.display = 'none';
                passwordContainer.style.display = 'block';
                confirmPasswordContainer.style.display = 'block';
                verifyButton.style.display = 'none';
                changePasswordButton.style.display = 'block';

                formTitle.textContent = 'Reset Your Password';
                formDescription.textContent = 'Please enter your new password and confirm it.';
                dynamicImage.src = 'resetpswd.gif';
            } else {
                alert('Invalid OTP. Please try again.');
            }
        });

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
