<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class MailSender {
    
    private static $config = [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'duisincong1121@gmail.com',
        'password' => 'jpmk bqrz dflt ovqv',
        'from_email' => 'duisincong1121@gmail.com',
        'from_name' => 'JC Restaurant Admin'
    ];
    
    public static function sendOTPEmail($to, $otp, $name) {
        try {
            $mail = new PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = self::$config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = self::$config['username'];
            $mail->Password = self::$config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = self::$config['port'];
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom(self::$config['from_email'], self::$config['from_name']);
            $mail->addAddress($to, $name);
            
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code - JC Restaurant Admin';
            $mail->Body = self::getOTPEmailHTML($otp, $name);
            $mail->AltBody = self::getOTPEmailText($otp, $name);
            
            if ($mail->send()) {
                error_log("Email sent successfully to $to");
                return true;
            } else {
                error_log("Failed to send email to $to");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("PHPMailer Error for $to: " . $e->getMessage());
            return false;
        }
    }
    
    private static function getOTPEmailHTML($otp, $name) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>OTP Code - JC Restaurant</title>
            <style>
                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #16181b 0%, #2c3e50 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    padding: 40px;
                }
                .otp-box {
                    background: #e8f4fc;
                    padding: 30px;
                    border-radius: 10px;
                    margin: 30px 0;
                    text-align: center;
                    border: 3px solid #3498db;
                }
                .otp-code {
                    font-size: 48px;
                    font-weight: bold;
                    color: #e74c3c;
                    letter-spacing: 15px;
                    font-family: monospace;
                    margin: 20px 0;
                    padding: 10px;
                    background: white;
                    border-radius: 5px;
                    display: inline-block;
                    min-width: 300px;
                }
                .warning-box {
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 20px;
                    margin: 25px 0;
                    border-radius: 5px;
                }
                .footer {
                    background: #ecf0f1;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-top: 1px solid #ddd;
                }
                .timer {
                    color: #e74c3c;
                    font-weight: bold;
                    font-size: 14px;
                    margin: 15px 0;
                }
                .note {
                    color: #7f8c8d;
                    font-size: 14px;
                    margin: 20px 0;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🍽️ JC Restaurant Admin</h1>
                </div>
                <div class="content">
                    <h2 style="color: #2c3e50; margin-top: 0;">Password Reset OTP Code</h2>
                    <p>Hello <strong>' . htmlspecialchars($name) . '</strong>,</p>
                    <p>You have requested to reset your password for the JC Restaurant Admin Panel.</p>
                    
                    <div class="otp-box">
                        <p style="margin: 0 0 15px 0; font-size: 16px; color: #555;">Your One-Time Password (OTP):</p>
                        <div class="otp-code">' . $otp . '</div>
                        <div class="timer">
                            ⏰ This OTP is valid for 10 minutes
                        </div>
                    </div>
                    
                    <div class="warning-box">
                        <p style="margin: 0 0 10px 0; font-weight: bold;">
                            🔒 Security Notice:
                        </p>
                        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                            <li>Do not share this OTP with anyone</li>
                            <li>JC Restaurant will never ask for your password</li>
                            <li>If you didn\'t request this, please ignore this email</li>
                        </ul>
                    </div>
                    
                    <div class="note">
                        <p style="margin: 0;">
                            Please enter this OTP code on the verification page to proceed with resetting your password.
                        </p>
                    </div>
                    
                    <p style="margin-top: 30px;">
                        Best regards,<br>
                        <strong>JC Restaurant Admin Team</strong>
                    </p>
                </div>
                <div class="footer">
                    <p style="margin: 0;">
                        &copy; ' . date('Y') . ' JC Restaurant. All rights reserved.<br>
                        This is an automated message, please do not reply to this email.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
    
    private static function getOTPEmailText($otp, $name) {
        return "JC Restaurant Admin - Password Reset OTP

Hello $name,

You have requested to reset your password for the JC Restaurant Admin Panel.

Your One-Time Password (OTP) is:
$otp

⏰ This OTP is valid for 10 minutes

🔒 Security Notice:
• Do not share this OTP with anyone
• JC Restaurant will never ask for your password
• If you didn't request this, please ignore this email

Please enter this OTP code on the verification page to proceed with resetting your password.

Best regards,
JC Restaurant Admin Team

© " . date('Y') . " JC Restaurant. All rights reserved.
This is an automated message, please do not reply to this email.";
    }
}
?>