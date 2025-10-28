<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
} 

// Get online users (users active in last 5 minutes) - FIXED QUERY
$online_time_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes'));
$sql = "SELECT u.username, u.id 
        FROM users u 
        INNER JOIN chat_messages cm ON u.id = cm.user_id 
        WHERE cm.created_at > ? 
        GROUP BY u.id, u.username 
        ORDER BY MAX(cm.created_at) DESC 
        LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $online_time_threshold);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode(['users' => $users]);

$stmt->close();
?>
