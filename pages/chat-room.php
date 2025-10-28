<?php
// chat-room.php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
} 

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle AJAX requests first
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_messages':
            $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
            getMessages($conn, $last_id);
            exit();
            
        case 'get_online_users':
            getOnlineUsers($conn);
            exit();
    }
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $message_id = (int)$_POST['delete_message'];
    deleteMessage($conn, $user_id, $message_id);
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        // Sanitize message
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        // Insert message into database
        $sql = "INSERT INTO chat_messages (user_id, username, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $username, $message);
        $stmt->execute();
        $stmt->close();
        
        // Return success response for AJAX
        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true]);
            exit();
        }
    }
}

// Get online users (users active in last 5 minutes)
$online_users = getOnlineUsersData($conn);

// Get chat messages (exclude deleted messages)
$messages = getChatMessages($conn);

// Function definitions
function getMessages($conn, $last_id) {
    $sql = "SELECT cm.*, u.username 
            FROM chat_messages cm 
            JOIN users u ON cm.user_id = u.id 
            WHERE (cm.is_deleted = 0 OR cm.is_deleted IS NULL)
            AND cm.id > ?
            ORDER BY cm.created_at ASC 
            LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $last_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    
    $last_id = !empty($messages) ? end($messages)['id'] : $last_id;
    
    echo json_encode([
        'messages' => $messages,
        'last_id' => $last_id
    ]);
    exit();
}

function getOnlineUsers($conn) {
    $online_time_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $sql = "SELECT DISTINCT u.id, u.username 
            FROM users u 
            INNER JOIN chat_messages cm ON u.id = cm.user_id 
            WHERE cm.created_at > ? 
            ORDER BY u.username ASC 
            LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $online_time_threshold);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['users' => $users]);
    exit();
}

function deleteMessage($conn, $user_id, $message_id) {
    // Verify the message belongs to the current user
    $sql_check = "SELECT user_id FROM chat_messages WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $message_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        $message = $result->fetch_assoc();
        
        if ($message['user_id'] == $user_id) {
            // Soft delete the message
            $sql_delete = "UPDATE chat_messages SET is_deleted = 1 WHERE id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $message_id);
            
            if ($stmt_delete->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database error']);
            }
            $stmt_delete->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'You can only delete your own messages']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Message not found']);
    }
    $stmt_check->close();
    exit();
}

function getOnlineUsersData($conn) {
    $online_time_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $sql_online = "SELECT DISTINCT u.id, u.username 
                   FROM users u 
                   INNER JOIN chat_messages cm ON u.id = cm.user_id 
                   WHERE cm.created_at > ? 
                   ORDER BY u.username ASC 
                   LIMIT 20";
    $stmt_online = $conn->prepare($sql_online);
    $stmt_online->bind_param("s", $online_time_threshold);
    $stmt_online->execute();
    $online_users = $stmt_online->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_online->close();
    
    return $online_users;
}

function getChatMessages($conn) {
    $sql_messages = "SELECT cm.*, u.username 
                     FROM chat_messages cm 
                     JOIN users u ON cm.user_id = u.id 
                     WHERE cm.is_deleted = 0 OR cm.is_deleted IS NULL
                     ORDER BY cm.created_at DESC 
                     LIMIT 50";
    $result_messages = $conn->query($sql_messages);
    $messages = $result_messages->fetch_all(MYSQLI_ASSOC);
    return array_reverse($messages); // Reverse to show oldest first
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Chat Room | CropDoctor AI</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a5d1a;
            --primary-light: #2e8b2e;
            --secondary: #e6b325;
            --accent: #4a7c59;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f8fbf8 0%, #e8f5e8 100%);
            height: 100vh;
            overflow: hidden;
        }
        
        .chat-room-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            background: var(--primary);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chat-main {
            flex: 1;
            display: flex;
            height: calc(100vh - 140px);
        }
        
        .users-sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
        }
        
        .users-list {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .messages-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fbf8;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            animation: messageSlide 0.3s ease-out;
            position: relative;
            transition: all 0.3s ease;
        }
        
        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes messageSlideOut {
            from {
                opacity: 1;
                transform: translateY(0);
                max-height: 200px;
                margin-bottom: 15px;
            }
            to {
                opacity: 0;
                transform: translateY(-10px);
                max-height: 0;
                margin-bottom: 0;
                padding-top: 0;
                padding-bottom: 0;
            }
        }
        
        .message.deleting {
            animation: messageSlideOut 0.3s ease-out forwards;
        }
        
        .message.user {
            background: var(--primary);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }
        
        .message.other {
            background: white;
            color: #333;
            border: 1px solid #e0e0e0;
            margin-right: auto;
            border-bottom-left-radius: 5px;
        }
        
        .message.system {
            background: #fff3cd;
            color: #856404;
            margin: 0 auto;
            max-width: 80%;
            text-align: center;
            border-radius: 10px;
            font-size: 0.9rem;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            font-size: 0.8rem;
            position: relative;
        }
        
        .message-sender {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .message-time {
            opacity: 0.7;
            font-size: 0.75rem;
        }
        
        .message-content {
            word-wrap: break-word;
            line-height: 1.4;
        }
        
        .input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }
        
        .user-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.3s;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .user-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .user-item.current-user {
            background: rgba(26, 93, 26, 0.1);
            border-color: var(--primary);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
            font-size: 0.9rem;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--dark);
        }
        
        .user-status {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .online-dot {
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            margin-left: 10px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .typing-indicator {
            padding: 10px 20px;
            font-style: italic;
            color: #666;
            font-size: 0.9rem;
            display: none;
        }
        
        .typing-dots {
            display: inline-block;
        }
        
        .typing-dots span {
            animation: typing 1.4s infinite;
            display: inline-block;
        }
        
        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
        
        .new-message-alert {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            display: none;
            z-index: 1000;
        }
        
        .message-actions {
            position: absolute;
            top: -25px;
            right: 10px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 5px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            display: flex;
            gap: 5px;
            z-index: 10;
        }
        
        .message:hover .message-actions {
            opacity: 1;
            visibility: visible;
        }
        
        .action-btn {
            background: none;
            border: none;
            padding: 5px 8px;
            cursor: pointer;
            color: #666;
            border-radius: 5px;
            transition: all 0.3s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn:hover {
            background: #f8f9fa;
            color: var(--primary);
        }
        
        .delete-btn {
            color: #dc3545;
        }
        
        .delete-btn:hover {
            background: #dc3545;
            color: white;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
    </style>
</head>
<body>
    <div class="chat-room-container">
        <!-- Header -->
        <div class="chat-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-0"><i class="fas fa-seedling me-2"></i>CropDoctor Community Chat</h4>
                        <small class="opacity-75">Connect with farmers and experts worldwide</small>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex gap-3 align-items-center">
                            <span class="text-white-50">Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                            <a href="dashboard.php" class="btn btn-light btn-sm">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a href="logout.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Chat Area -->
        <div class="chat-main">
            <!-- Online Users Sidebar -->
            <div class="users-sidebar">
                <div class="sidebar-header">
                    <h6 class="mb-2">Online Users (<?php echo count($online_users); ?>)</h6>
                    <small class="text-muted">Active in last 5 minutes</small>
                </div>
                
                <div class="users-list" id="usersList">
                    <?php foreach ($online_users as $user): ?>
                        <div class="user-item <?php echo $user['id'] == $user_id ? 'current-user' : ''; ?>">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                    <?php if ($user['id'] == $user_id): ?>
                                        <small class="text-muted">(You)</small>
                                    <?php endif; ?>
                                </div>
                                <div class="user-status">Online</div>
                            </div>
                            <div class="online-dot"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Chat Messages Area -->
            <div class="chat-area">
                <div class="messages-container" id="messagesContainer">
                    <?php if (empty($messages)): ?>
                        <div class="text-center text-muted mt-5">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo $message['user_id'] == $user_id ? 'user' : 'other'; ?>" data-message-id="<?php echo $message['id']; ?>">
                                <div class="message-header">
                                    <div>
                                        <span class="message-sender">
                                            <?php echo htmlspecialchars($message['username']); ?>
                                            <?php if ($message['user_id'] == $user_id): ?>
                                                <small>(You)</small>
                                            <?php endif; ?>
                                        </span>
                                        <span class="message-time">
                                            <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($message['user_id'] == $user_id): ?>
                                    <div class="message-actions">
                                        <button class="action-btn delete-btn" title="Delete message">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="message-content">
                                    <?php echo $message['message']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Typing Indicator -->
                <div class="typing-indicator" id="typingIndicator">
                    <span id="typingUser">Someone</span> is typing<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
                </div>
                
                <!-- Message Input -->
                <div class="input-area">
                    <form id="messageForm" method="POST">
                        <div class="input-group">
                            <input type="text" name="message" class="form-control" placeholder="Type your message..." id="messageInput" maxlength="500" required>
                            <button type="submit" class="btn btn-primary" id="sendButton">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <small class="text-muted">Press Enter to send</small>
                            <small class="text-muted" id="charCount">0/500</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- New Message Alert -->
        <div class="new-message-alert" id="newMessageAlert">
            <i class="fas fa-comment me-2"></i>New messages
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global chat instance
        let chat;

        class RealTimeChat {
            constructor() {
                this.lastMessageId = <?php echo !empty($messages) ? end($messages)['id'] : 0; ?>;
                this.isTyping = false;
                this.typingTimer = null;
                this.lastScrollPosition = 0;
                this.isAtBottom = true;
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
                this.startPolling();
                this.scrollToBottom();
            }
            
            setupEventListeners() {
                // Message form submission
                document.getElementById('messageForm').addEventListener('submit', (e) => this.sendMessage(e));
                
                // Typing indicator
                document.getElementById('messageInput').addEventListener('input', () => this.handleTyping());
                
                // Character count
                document.getElementById('messageInput').addEventListener('input', () => this.updateCharCount());
                
                // Scroll events
                document.getElementById('messagesContainer').addEventListener('scroll', () => this.handleScroll());
                
                // New message alert click
                document.getElementById('newMessageAlert').addEventListener('click', () => this.scrollToBottom());
                
                // Event delegation for delete buttons
                document.getElementById('messagesContainer').addEventListener('click', (e) => {
                    if (e.target.closest('.delete-btn')) {
                        const messageElement = e.target.closest('.message');
                        const messageId = messageElement.getAttribute('data-message-id');
                        this.deleteMessage(messageId);
                    }
                });
            }
            
            sendMessage(e) {
                e.preventDefault();
                
                const messageInput = document.getElementById('messageInput');
                const message = messageInput.value.trim();
                
                if (message) {
                    const formData = new FormData();
                    formData.append('message', message);
                    formData.append('ajax', 'true');
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            messageInput.value = '';
                            this.updateCharCount();
                            this.hideTypingIndicator();
                            this.lastMessageId = 0; // Force refresh to get new message
                            this.fetchNewMessages();
                        }
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        alert('Error sending message. Please try again.');
                    });
                }
            }
            
            deleteMessage(messageId) {
                if (confirm('Are you sure you want to delete this message?')) {
                    const formData = new FormData();
                    formData.append('delete_message', messageId);
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the message from the DOM
                            const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                            if (messageElement) {
                                messageElement.classList.add('deleting');
                                setTimeout(() => {
                                    messageElement.remove();
                                }, 300);
                            }
                            
                            // Show success message
                            this.showNotification('Message deleted successfully', 'success');
                        } else {
                            this.showNotification('Error deleting message: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting message:', error);
                        this.showNotification('Error deleting message', 'error');
                    });
                }
            }
            
            showNotification(message, type) {
                // Remove existing notifications
                document.querySelectorAll('.alert').forEach(alert => alert.remove());
                
                const notification = document.createElement('div');
                notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show notification`;
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.body.appendChild(notification);
                
                // Auto remove after 3 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 3000);
            }
            
            handleTyping() {
                if (!this.isTyping) {
                    this.isTyping = true;
                    // In a real app, you'd send a typing indicator to the server
                }
                
                // Clear existing timer
                if (this.typingTimer) {
                    clearTimeout(this.typingTimer);
                }
                
                // Set timer to hide typing indicator
                this.typingTimer = setTimeout(() => {
                    this.isTyping = false;
                    this.hideTypingIndicator();
                }, 1000);
            }
            
            showTypingIndicator(username) {
                const indicator = document.getElementById('typingIndicator');
                document.getElementById('typingUser').textContent = username;
                indicator.style.display = 'block';
            }
            
            hideTypingIndicator() {
                document.getElementById('typingIndicator').style.display = 'none';
            }
            
            updateCharCount() {
                const input = document.getElementById('messageInput');
                const charCount = document.getElementById('charCount');
                const count = input.value.length;
                charCount.textContent = `${count}/500`;
                
                if (count > 450) {
                    charCount.style.color = '#dc3545';
                } else if (count > 400) {
                    charCount.style.color = '#ffc107';
                } else {
                    charCount.style.color = '#6c757d';
                }
            }
            
            startPolling() {
                // Poll for new messages every 2 seconds
                setInterval(() => {
                    this.fetchNewMessages();
                    this.updateOnlineUsers();
                }, 2000);
            }
            
            fetchNewMessages() {
                fetch(`?action=get_messages&last_id=${this.lastMessageId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.messages && data.messages.length > 0) {
                            this.addNewMessages(data.messages);
                            this.lastMessageId = data.last_id;
                            
                            // Show new message alert if not at bottom
                            if (!this.isAtBottom) {
                                this.showNewMessageAlert(data.messages.length);
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching messages:', error));
            }
            
            addNewMessages(messages) {
                const container = document.getElementById('messagesContainer');
                const wasAtBottom = this.isAtBottom;
                
                messages.forEach(message => {
                    const messageDiv = this.createMessageElement(message);
                    container.appendChild(messageDiv);
                });
                
                if (wasAtBottom) {
                    this.scrollToBottom();
                }
            }
            
            createMessageElement(message) {
                const messageDiv = document.createElement('div');
                const isCurrentUser = message.user_id == <?php echo $user_id; ?>;
                
                messageDiv.className = `message ${isCurrentUser ? 'user' : 'other'}`;
                messageDiv.setAttribute('data-message-id', message.id);
                
                messageDiv.innerHTML = `
                    <div class="message-header">
                        <div>
                            <span class="message-sender">
                                ${this.escapeHtml(message.username)}
                                ${isCurrentUser ? '<small>(You)</small>' : ''}
                            </span>
                            <span class="message-time">
                                ${this.formatTime(message.created_at)}
                            </span>
                        </div>
                        ${isCurrentUser ? `
                        <div class="message-actions">
                            <button class="action-btn delete-btn" title="Delete message">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        ` : ''}
                    </div>
                    <div class="message-content">
                        ${this.escapeHtml(message.message)}
                    </div>
                `;
                
                return messageDiv;
            }
            
            updateOnlineUsers() {
                fetch('?action=get_online_users')
                    .then(response => response.json())
                    .then(data => {
                        if (data.users) {
                            this.updateUsersList(data.users);
                        }
                    })
                    .catch(error => console.error('Error fetching online users:', error));
            }
            
            updateUsersList(users) {
                const usersList = document.getElementById('usersList');
                const currentUserId = <?php echo $user_id; ?>;
                
                usersList.innerHTML = users.map(user => `
                    <div class="user-item ${user.id == currentUserId ? 'current-user' : ''}">
                        <div class="user-avatar">
                            ${user.username.substring(0, 2).toUpperCase()}
                        </div>
                        <div class="user-info">
                            <div class="user-name">
                                ${this.escapeHtml(user.username)}
                                ${user.id == currentUserId ? '<small class="text-muted">(You)</small>' : ''}
                            </div>
                            <div class="user-status">Online</div>
                        </div>
                        <div class="online-dot"></div>
                    </div>
                `).join('');
            }
            
            handleScroll() {
                const container = document.getElementById('messagesContainer');
                const scrollTop = container.scrollTop;
                const scrollHeight = container.scrollHeight;
                const clientHeight = container.clientHeight;
                
                this.isAtBottom = (scrollHeight - scrollTop - clientHeight) < 50;
                
                if (this.isAtBottom) {
                    this.hideNewMessageAlert();
                }
            }
            
            scrollToBottom() {
                const container = document.getElementById('messagesContainer');
                container.scrollTop = container.scrollHeight;
                this.hideNewMessageAlert();
            }
            
            showNewMessageAlert(count) {
                const alert = document.getElementById('newMessageAlert');
                alert.innerHTML = `<i class="fas fa-comment me-2"></i>${count} new message${count > 1 ? 's' : ''}`;
                alert.style.display = 'block';
            }
            
            hideNewMessageAlert() {
                document.getElementById('newMessageAlert').style.display = 'none';
            }
            
            escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
            
            formatTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }
        
        // Initialize chat when page loads
        document.addEventListener('DOMContentLoaded', () => {
            chat = new RealTimeChat();
        });
    </script>
</body>
</html>
