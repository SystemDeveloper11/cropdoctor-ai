<?php
// get_messages.php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
} 

$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// Check if is_deleted column exists
$column_check = $conn->query("SHOW COLUMNS FROM chat_messages LIKE 'is_deleted'");
$soft_delete_exists = $column_check->num_rows > 0;

if ($soft_delete_exists) {
    // Exclude deleted messages
    if ($last_id > 0) {
        $sql = "SELECT cm.*, u.username 
                FROM chat_messages cm 
                JOIN users u ON cm.user_id = u.id 
                WHERE cm.id > ? AND (cm.is_deleted = 0 OR cm.is_deleted IS NULL)
                ORDER BY cm.created_at ASC 
                LIMIT 50";
    } else {
        $sql = "SELECT cm.*, u.username 
                FROM chat_messages cm 
                JOIN users u ON cm.user_id = u.id 
                WHERE (cm.is_deleted = 0 OR cm.is_deleted IS NULL)
                ORDER BY cm.created_at DESC 
                LIMIT 50";
    }
} else {
    // No soft delete column, get all messages
    if ($last_id > 0) {
        $sql = "SELECT cm.*, u.username 
                FROM chat_messages cm 
                JOIN users u ON cm.user_id = u.id 
                WHERE cm.id > ? 
                ORDER BY cm.created_at ASC 
                LIMIT 50";
    } else {
        $sql = "SELECT cm.*, u.username 
                FROM chat_messages cm 
                JOIN users u ON cm.user_id = u.id 
                ORDER BY cm.created_at DESC 
                LIMIT 50";
    }
}

$stmt = $conn->prepare($sql);
if ($last_id > 0) {
    $stmt->bind_param("i", $last_id);
}
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

// If we're getting messages from the beginning, reverse them
if ($last_id === 0) {
    $messages = array_reverse($messages);
}

$last_id = !empty($messages) ? end($messages)['id'] : $last_id;

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'last_id' => $last_id
]);

$stmt->close();
?>
