<?php
// Start session and include files at the very top
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in (replace requireLogin() with direct check)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to access this page.';
    header('Location: ../pages/login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'No diagnosis ID provided.';
    header('Location: ../pages/history.php');
    exit;
}

$diagnosis_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Verify the diagnosis belongs to the current user
$check_sql = "SELECT id, image_path FROM diagnoses WHERE id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    $_SESSION['error'] = 'Database error. Please try again.';
    header('Location: ../pages/history.php');
    exit;
}

$check_stmt->bind_param("ii", $diagnosis_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $_SESSION['error'] = 'Diagnosis not found or you do not have permission to delete it.';
    header('Location: ../pages/history.php');
    exit;
}

$diagnosis = $check_result->fetch_assoc();
$check_stmt->close();

// Delete the diagnosis record
$delete_sql = "DELETE FROM diagnoses WHERE id = ? AND user_id = ?";
$delete_stmt = $conn->prepare($delete_sql);

if ($delete_stmt) {
    $delete_stmt->bind_param("ii", $diagnosis_id, $user_id);
    
    if ($delete_stmt->execute()) {
        // Optionally delete the associated image file
        if (!empty($diagnosis['image_path'])) {
            $image_path = __DIR__ . '/../assets/images/uploads/' . $diagnosis['image_path'];
            if (file_exists($image_path) && is_file($image_path)) {
                unlink($image_path);
            }
        }
        
        $_SESSION['message'] = 'Diagnosis deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete diagnosis. Please try again.';
    }
    
    $delete_stmt->close();
} else {
    $_SESSION['error'] = 'Database error. Please try again.';
}

header('Location: ../pages/history.php');
exit;
?>