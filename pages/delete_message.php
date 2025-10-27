<?php
// delete_message.php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if message_id is provided
if (!isset($_POST['message_id']) || empty($_POST['message_id'])) {
    echo json_encode(['success' => false, 'error' => 'No message ID provided']);
    exit();
}

$message_id = intval($_POST['message_id']);
$user_id = $_SESSION['user_id'];

// Check if the message belongs to the user
$check_sql = "SELECT user_id FROM chat_messages WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $message_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Message not found']);
    exit();
}

$message_data = $check_result->fetch_assoc();
if ($message_data['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'error' => 'You can only delete your own messages']);
    exit();
}

$check_stmt->close();

// Check if soft delete columns exist, if not use permanent delete
$column_check = $conn->query("SHOW COLUMNS FROM chat_messages LIKE 'is_deleted'");
if ($column_check->num_rows > 0) {
    // Soft delete (marks message as deleted but keeps it in database)
    $sql = "UPDATE chat_messages SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $message_id, $user_id);
} else {
    // Permanent delete (if soft delete columns don't exist)
    $sql = "DELETE FROM chat_messages WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $message_id, $user_id);
}

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Message not found or you do not have permission to delete it']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
?>