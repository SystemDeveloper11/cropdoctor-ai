<?php
// File: /api/emailverify.php (or /includes/mailer.php)

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 1. PATH CORRECTION: Changed '/../../vendor/' to '/../vendor/'
// This assumes this file is in 'api/' and 'vendor/' is in the root (../).
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer/src/SMTP.php';

/**
 * Sends a verification email containing a link and an OTP code.
 *
 * @param string $recipientEmail The email address of the recipient.
 * @param string $recipientName The username of the recipient.
 * @param string $subject The email subject line.
 * @param string $body The HTML content of the email.
 * @return bool True on success, False on failure.
 */
function sendVerificationEmail($recipientEmail, $recipientName, $subject, $body) {
    
    // Ensure all necessary constants from config.php are defined
    if (!defined('MAIL_HOST') || !defined('MAIL_USERNAME')) {
        // This check is good but likely won't prevent the error if config.php wasn't loaded
        error_log("Mailer configuration constants are missing.");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings using constants from config.php
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION; 
        $mail->Port       = MAIL_PORT;      
        $mail->CharSet    = 'UTF-8';
        $mail->Debugoutput = 'error_log'; 
        // Set to a high level (e.g., 2) for debugging if you still don't receive emails.
        $mail->SMTPDebug  = 0; 

        // Recipients
        $mail->setFrom(MAIL_USERNAME, 'CropDoctor AI Verification');
        $mail->addAddress($recipientEmail, $recipientName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); 

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Verification email failed to send to {$recipientEmail}. Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>