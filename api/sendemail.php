<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

// Ensure the user is logged in
requireLogin();

$diagnosis_id = $_GET['id'] ?? null;

if ($diagnosis_id) {
    $user_id = $_SESSION['user_id'];

    // Fetch diagnosis details from the database, ensuring it belongs to the logged-in user
    $sql = "SELECT d.disease_name, d.confidence, d.suggestions, u.email FROM diagnoses d JOIN users u ON d.user_id = u.id WHERE d.id = ? AND d.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $diagnosis_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $diagnosis = $result->fetch_assoc();
    $stmt->close();

    if ($diagnosis) {
        $email_sent = sendDiagnosisEmail($diagnosis['email'], $diagnosis);
        
        if ($email_sent) {
            $_SESSION['message'] = "Diagnosis report sent to your email successfully!";
        } else {
            $_SESSION['error'] = "Failed to send the email. Please try again later.";
        }
    } else {
        $_SESSION['error'] = "Diagnosis not found or you don't have permission to view it.";
    }
} else {
    $_SESSION['error'] = "Invalid diagnosis ID.";
}

// Redirect back to the history or result page
header("Location: " . BASE_URL . "pages/history.php");
exit;
?>