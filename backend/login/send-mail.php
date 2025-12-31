<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendResetEmail($to, $name, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'duisincong1121@gmail.com'; 
        $mail->Password = 'jpmk bqrz dflt ovqv'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('no-reply@yourdomain.com', 'Admin System');
        $mail->addAddress($to, $name);
        
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "
            <h3>Password Reset Request</h3>
            <p>Hello $name,</p>
            <p>Your OTP for password reset is: <strong>$otp</strong></p>
            <p>This OTP will expire in 10 minutes.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <br>
            <p>Best regards,<br>Admin Team</p>
        ";
        
        $mail->AltBody = "Hello $name,\n\nYour OTP for password reset is: $otp\n\nThis OTP will expire in 10 minutes.\n\nIf you didn't request this, please ignore this email.";
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>