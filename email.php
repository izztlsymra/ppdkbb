<?php
require 'vendor/autoload.php'; // Ensure the autoload file is included

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);

    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com';
    $mail->Password = 'your-app-password'; // Use  your app password
    $mail->SMTPSecure = 'tls'; // Use 'tls'
    $mail->Port = 587;

    // Email content
    $mail->setFrom('your-email@gmail.com', 'Your Name');
    $mail->addAddress('recipient-email@gmail.com');
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email sent using PHPMailer.';

    $mail->send();
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo 'Email could not be sent. Error: ', $mail->ErrorInfo;
}
