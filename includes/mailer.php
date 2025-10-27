<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes from Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Function to send a diagnosis email
function sendDiagnosisEmail($recipientEmail, $diagnosis) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        // Recipients
        $mail->setFrom(MAIL_USERNAME, 'Agri-Tech Crop Doctor');
        $mail->addAddress($recipientEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Plant Diagnosis Report';
        $mail->Body    = '
        <p>Hello,</p>
        <p>Your diagnosis is ready! Here is the report for your plant:</p>
        <hr>
        <h3>Diagnosis Report</h3>
        <ul>
            <li><strong>Disease:</strong> ' . htmlspecialchars($diagnosis['disease_name']) . '</li>
            <li><strong>Confidence:</strong> ' . number_format($diagnosis['confidence'] * 100, 2) . '%</li>
        </ul>
        <h3>Treatment Suggestions</h3>
        <p>' . nl2br(htmlspecialchars($diagnosis['suggestions'])) . '</p>
        <p>For more details, log into your dashboard.</p>
        <br>
        <p>Thank you for using Agri-Tech Crop Doctor!</p>
        ';
        $mail->AltBody = 'Your diagnosis is ready! Disease: ' . $diagnosis['disease_name'] . '. Confidence: ' . number_format($diagnosis['confidence'] * 100, 2) . '%. Treatment Suggestions: ' . $diagnosis['suggestions'];

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>