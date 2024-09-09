<?php
    include 'dbconnect.php';
    session_start();
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    //Load Composer's autoloader
    require 'C:/xampp/htdocs/UIT-MAP/vendor/autoload.php';

    function send_password_reset($get_name, $get_email, $token){
        $mail = new PHPMailer(true);
        $mail->isSMTP();                                            //Send using SMTP
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication

        $mail->Host       = "smtp.gmail.com";                     //Set the SMTP server to send through
        $mail->Username   = "work.hridyesh@gmail.com";                     //SMTP username
        $mail->Password   = "eakqpwyyhpfzzef";                               //SMTP password
        
        $mail->SMTPSecure = "tls";            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom("work.hridyesh@gmail.com", $get_name);
        $mail->addAddress($get_email);               
        
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = "Reset your UIT-MAP account password";

        // $email_template = file_get_contents('email_template.html');
        $email_template = "
                <h1>Reset Your UIT-MAP Account Password</h1>
                <p>Hello $get_name,</p>
                <p>We have received a request to reset your password for your UIT-MAP account.</p>
                <p>Please click on the link below to reset your password:</p>
                <a href='http://localhost/UIT-MAP/changepassword.php?token=$token&email=$get_email'>Reset Password</a>
                <p>If you did not request a password reset, please ignore this email.</p>
                <p>Thank you,</p>
                <p>The UIT-MAP Team</p>
        ";
        $mail->Body = $email_template;
        $mail->send();

    }

    if(isset($_POST['reset-button'])){
        $email= mysqli_real_escape_string($conn, $_POST['uniqueId']);
        $token= md5(rand());

        $check_email= "SELECT email FROM info WHERE email='$email' LIMIT 1";
        $check_email_run= mysqli_query($conn, $check_email);
        if(mysqli_num_rows($check_email_run) > 0){
            $row=mysqli_fetch_array(check_email_run);
            $get_name= $row['name'];
            $get_email= $row['email'];

            $update_token= "UPDATE info SET verify_token='$token' WHERE email='$get_email' LIMIT 1";
            $update_token_run= mysqli_query($conn, $update_token);
            if($update_token_run){
                send_password_reset($get_name, $get_email, $token);
                $_SESSION['status']="Password reset link has been sent to your email!";
                // header('Location: forgotpassword.php');
                exit(0);
            }
            else{
                $_SESSION['status']="Something went wrong. Please try again!";
                header('Location: forgotpassword.php');
                exit(0);
            }
        }
        else{
            $_SESSION['status']="Email not found!";
            header('Location: forgotpassword.php');
            exit(0);
        }
    }
?>
<!DOCTYPE html>
<html lang="en>
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

        .reset-button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .reset-button:hover {
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

            <form action="forgotpassword.php" id="resetForm">
                <div class="input-container" id="email-container">
                    <label for="uniqueId">Email Address</label>
                    <input type="text" id="uniqueId" name="uniqueId" required>
                </div>
                <button type="submit" class="reset-button" name="reset" id="reset-button">Send password reset link</button>
                <div class="back-to-login">
                    <a href="index.php">Back to Login</a>
                </div>
                
            </form>
        </div>
        
        <div class="right-side">
            <img id="dynamic-image" src="Frgtpswd.gif" alt="Forgot Password Image">
        </div>
    </div>

    <script>
        const resetForm = document.getElementById('resetForm');
        const resetButton = document.getElementById('reset-button');
        const emailContainer = document.getElementById('email-container');

        resetButton.addEventListener('click', function() {
            const uniqueId = document.getElementById('uniqueId').value;

            if (uniqueId) {
                // Simulate sending OTP
                alert(`A password reset link has been sent to ${uniqueId}.`);
            } else {
                alert('Please enter a valid Unique ID or Email.');
            }
        }); 
    </script>
</body>
</html>
