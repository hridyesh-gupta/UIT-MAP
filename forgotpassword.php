<?php
    include 'dbconnect.php';
    session_start();
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    //Load Composer's autoloader
    require 'C:/xampp/htdocs/UIT-MAP/vendor/autoload.php';

    //Load environment variables from .env file
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'cred.env');
    $dotenv->load();    

    function send_password_reset($get_name, $get_email, $token){
        $mail = new PHPMailer(true);
        try{
            $mail->isSMTP();                                            //Send using SMTP
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication

            $mail->Host       = "smtp.gmail.com";                     //Set the SMTP server to send through
            $mail->Username   = $_ENV['SMTP_USERNAME'];                     //SMTP username
            $mail->Password   = $_ENV['SMTP_PASSWORD'];                     //SMTP password
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom("work.hridyesh@gmail.com", "UIT-MAP Team");
            $mail->addAddress($get_email);               
            
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = "Reset your UIT-MAP account password";

            $email_template = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
                        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                        .button { display: inline-block; padding: 10px 20px; background-color: #3498db; color: #ffffff; text-decoration: none; border-radius: 5px; }
                        .button:hover { background-color: #2980b9; } 
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h1>Reset Your UIT-MAP Account Password</h1>
                        <p>Hello $get_name,</p>
                        <p>We have received a request to reset your password for your UIT-MAP account.</p>
                        <p>Please click on the button below to reset your password:</p>
                        <p><a href='http://localhost/UIT-MAP/UIT-MAP/changepassword.php?token=$token&email=$get_email' class='button' style='color: #ffffff;'>Reset Password</a></p>
                        <p>If you did not request a password reset, please ignore this email.</p>
                        <p>Thank you,</p>
                        <p>The UIT-MAP Team</p>
                    </div>
                    <?php include 'footer.php' ?>
                </body>
                </html>
                ";
            $mail->Body = $email_template;
            $mail->send();
            $_SESSION['debug_info'][] = "Email sent successfully to $get_email";
            return true;
        }
        catch(Exception $e){
            $_SESSION['debug_info'][] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
    //When the reset button is clicked
    if(isset($_POST['reset-button'])){
        $email= mysqli_real_escape_string($conn, $_POST['email']);
        //To generate a unique token
        $token= md5(rand());
        
        $check_email= "SELECT email, name FROM info WHERE email='$email' LIMIT 1";
        $check_email_run= mysqli_query($conn, $check_email);

        //To check if the email is valid and exists in the database
        if(mysqli_num_rows($check_email_run) > 0){
            $row=mysqli_fetch_array($check_email_run);
            $get_name= $row['name'];
            $get_email= $row['email'];

            $update_token= "UPDATE info SET verify_token='$token' WHERE email='$get_email' LIMIT 1";
            $update_token_run= mysqli_query($conn, $update_token);
        
            //To update the token in the database
            if($update_token_run){
        
                //To send the password reset link to the email along with the token to the user
                if(send_password_reset($get_name, $get_email, $token)){
                    $_SESSION['status']="Password reset link has been sent to $get_email! Do check your spam folder too.";
                    $_SESSION['debug_info'][] = "Password reset email sent successfully to $get_email";
                }
                //Means the password reset link is not sent to the user
                else{
                    $_SESSION['status']="Failed to send password reset link. Please try again!";
                    $_SESSION['debug_info'][] = "Failed to send password reset email to $get_email";                    
                }
            }
            //Means the token is not updated in the database
            else {
                $_SESSION['status'] = "Failed to update token. Please try again!";
                $_SESSION['debug_info'][] = "Failed to update token for $get_email";
            }            
        }
        //Means the email is not found in the database
        else{
            $_SESSION['status']="Email not found!";
            $_SESSION['debug_info'][] = "Email not found: $email";
        }
        header('Location: forgotpassword.php');
        exit(0);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <?php include 'favicon.php' ?>
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
            <?php
                if(isset($_SESSION['status'])){
                    echo "<p style='color: red;'>" . $_SESSION['status'] . "</p>";
                    unset($_SESSION['status']);
                }
                // Enable this if you're facing any error in sending the mail
                // if(isset($_SESSION['debug_info'])){
                //     echo "<div style='background-color: #f0f0f0; padding: 10px; margin-top: 20px;'>";
                //     echo "<h3>Debug Information:</h3>";
                //     echo "<ul>";
                //     foreach($_SESSION['debug_info'] as $info){
                //         echo "<li>" . htmlspecialchars($info) . "</li>";
                //     }
                //     echo "</ul>";
                //     echo "</div>";
                //     unset($_SESSION['debug_info']);
                // }
            ?>
            <form action="forgotpassword.php" id="resetForm" method="POST">
                <div class="input-container" id="email-container">
                    <label for="email">Email Address</label>
                    <input type="text" id="email" name="email" required>
                </div>
                <button type="submit" class="reset-button" name="reset-button" id="reset-button">Send password reset link</button>
                <div class="back-to-login">
                    <a href="index.php">Back to Login</a>
                </div>
                
            </form>
        </div>
        
        <div class="right-side">
            <img id="dynamic-image" src="Frgtpswd.gif" alt="Forgot Password Image">
        </div>
    </div>

    <!-- <script>
        const resetForm = document.getElementById('resetForm');
        const resetButton = document.getElementById('reset-button');
        const emailContainer = document.getElementById('email-container');

        resetButton.addEventListener('click', function() {
            const email = document.getElementById('email').value;

            if (email) {
                // Simulate sending OTP
                alert(`A password reset link has been sent to ${email}.`);
            } else {
                alert('Please enter a valid Unique ID or Email.');
            }
        }); 
    </script> -->
</body>
</html>
