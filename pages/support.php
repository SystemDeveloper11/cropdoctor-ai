<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center | CropDoctor AI</title>
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
            --primary-dark: #0d3d0d;
            --secondary: #e6b325;
            --accent: #4a7c59;
            --light: #f8f9fa;
            --dark: #1e2a1e;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8fbf8;
            color: var(--dark);
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }
        
        .support-hero {
            background: var(--gradient);
            color: white;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .support-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
        }
        
        .support-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            height: 100%;
            transition: all 0.3s ease;
            border-top: 4px solid transparent;
        }
        
        .support-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .support-card.technical {
            border-top-color: var(--primary);
        }
        
        .support-card.billing {
            border-top-color: #3498db;
        }
        
        .support-card.diagnosis {
            border-top-color: var(--secondary);
        }
        
        .support-card.account {
            border-top-color: #9b59b6;
        }
        
        .support-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .technical .support-icon {
            background: rgba(26, 93, 26, 0.1);
            color: var(--primary);
        }
        
        .billing .support-icon {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .diagnosis .support-icon {
            background: rgba(230, 179, 37, 0.1);
            color: var(--secondary);
        }
        
        .account .support-icon {
            background: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }
        
        .faq-accordion .accordion-button {
            background: white;
            color: var(--dark);
            font-weight: 500;
            padding: 20px;
            border: none;
            box-shadow: none;
        }
        
        .faq-accordion .accordion-button:not(.collapsed) {
            background: rgba(26, 93, 26, 0.05);
            color: var(--primary);
            border-bottom: 1px solid rgba(26, 93, 26, 0.1);
        }
        
        .faq-accordion .accordion-body {
            padding: 20px;
            background: rgba(26, 93, 26, 0.02);
        }
        
        .contact-method {
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        
        .contact-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .contact-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
            background: var(--gradient);
            color: white;
        }
        
        .ticket-form {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .btn-support {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-support:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.3);
            color: white;
        }
        
        .status-indicator {
            display: inline-flex;
            align-items: center;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-operational {
            background: rgba(46, 204, 113, 0.2);
            color: #27ae60;
        }
        
        .status-maintenance {
            background: rgba(241, 196, 15, 0.2);
            color: #f39c12;
        }
        
        .status-outage {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }
        
        /* Enhanced Chat Styles */
        .live-chat-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 5px 20px rgba(26, 93, 26, 0.3);
            z-index: 1000;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .live-chat-btn:hover {
            transform: scale(1.1);
        }
        
        .chat-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 380px;
            height: 550px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            display: none;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        
        .chat-header {
            background: var(--gradient);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-header-info h5 {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .chat-header-info small {
            opacity: 0.9;
        }
        
        .online-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }
        
        .online-dot {
            width: 8px;
            height: 8px;
            background: #2ecc71;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fbf8;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .chat-input-area {
            padding: 20px;
            border-top: 1px solid #e0e0e0;
            background: white;
        }
        
        .message {
            max-width: 85%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            animation: messageSlide 0.3s ease-out;
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
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .message.user {
            background: var(--primary);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }
        
        .message.other {
            background: white;
            color: var(--dark);
            border: 1px solid #e0e0e0;
            margin-right: auto;
            border-bottom-left-radius: 5px;
        }
        
        .message.support {
            background: #e3f2fd;
            color: var(--dark);
            border-bottom-left-radius: 5px;
            margin-right: auto;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            font-size: 0.8rem;
        }
        
        .message-sender {
            font-weight: 600;
        }
        
        .message-time {
            opacity: 0.7;
            font-size: 0.75rem;
        }
        
        .message-content {
            word-wrap: break-word;
            line-height: 1.4;
        }
        
        .user-typing {
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
        
        .chat-room-info {
            background: white;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 0.9rem;
            color: #666;
        }
        
        .room-users {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .user-badge {
            background: rgba(26, 93, 26, 0.1);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .user-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--primary);
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="../index.php">
                <i class="fas fa-seedling me-2"></i>
                CropDoctor AI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="support.php">Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="chat-room.php">Community Chat</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-3" href="register.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Support Hero Section -->
    <section class="support-hero">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">How Can We Help You?</h1>
                    <p class="lead mb-4">Our dedicated support team is here to assist you with any questions or issues you may have. Find answers quickly or get in touch with our experts.</p>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="status-indicator status-operational">
                            <i class="fas fa-circle me-2" style="font-size: 8px;"></i>
                            All Systems Operational
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-clock me-1"></i>
                            Average response: <strong>2 hours</strong>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="contact-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Options Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="support-card technical">
                        <div class="support-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h4>Technical Support</h4>
                        <p class="text-muted">Issues with the platform, app, or technical features</p>
                        <a href="#technical" class="btn btn-outline-primary btn-sm">Get Help</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="support-card billing">
                        <div class="support-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Billing & Account</h4>
                        <p class="text-muted">Payment issues, subscription changes, or account questions</p>
                        <a href="#billing" class="btn btn-outline-primary btn-sm">Get Help</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="support-card diagnosis">
                        <div class="support-icon">
                            <i class="fas fa-diagnoses"></i>
                        </div>
                        <h4>Diagnosis Help</h4>
                        <p class="text-muted">Questions about plant disease diagnosis results</p>
                        <a href="#diagnosis" class="btn btn-outline-primary btn-sm">Get Help</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="support-card account">
                        <div class="support-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h4>Account Settings</h4>
                        <p class="text-muted">Profile updates, password reset, or privacy settings</p>
                        <a href="#account" class="btn btn-outline-primary btn-sm">Get Help</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Community Chat Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">Join Our Community Chat</h2>
                    <p class="lead mb-4">Connect with other farmers, gardeners, and agricultural enthusiasts in our live community chat room.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users text-primary me-3 fs-4"></i>
                                <div>
                                    <h5 class="mb-1">Live Discussions</h5>
                                    <p class="text-muted mb-0">Real-time conversations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-share-alt text-primary me-3 fs-4"></i>
                                <div>
                                    <h5 class="mb-1">Share Experiences</h5>
                                    <p class="text-muted mb-0">Learn from others</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-leaf text-primary me-3 fs-4"></i>
                                <div>
                                    <h5 class="mb-1">Expert Advice</h5>
                                    <p class="text-muted mb-0">Get tips from pros</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-bolt text-primary me-3 fs-4"></i>
                                <div>
                                    <h5 class="mb-1">Instant Help</h5>
                                    <p class="text-muted mb-0">Quick responses</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="chat-room.php" class="btn btn-support mt-3">
                        <i class="fas fa-comments me-2"></i>
                        Enter Chat Room
                    </a>
                </div>
                <div class="col-lg-6">
                    <div class="chat-window" style="position: relative; display: flex; right: 0; bottom: 0; width: 100%; height: 400px;">
                        <div class="chat-header">
                            <div class="chat-header-info">
                                <h5>Community Chat Room</h5>
                                <small>Live discussions with members</small>
                            </div>
                            <div class="online-indicator">
                                <div class="online-dot"></div>
                                <span>24 Online</span>
                            </div>
                        </div>
                        <div class="chat-room-info">
                            <div class="room-users">
                                <span class="me-2">Active users:</span>
                                <div class="user-badge">Farmer John</div>
                                <div class="user-badge">Garden Expert</div>
                                <div class="user-badge">Tomato Grower</div>
                                <div class="user-badge">+21 more</div>
                            </div>
                        </div>
                        <div class="chat-messages">
                            <div class="message other">
                                <div class="message-header">
                                    <span class="message-sender">Farmer John</span>
                                    <span class="message-time">2:30 PM</span>
                                </div>
                                <div class="message-content">
                                    Has anyone tried the new organic fungicide?
                                </div>
                            </div>
                            <div class="message support">
                                <div class="message-header">
                                    <span class="message-sender">Garden Expert</span>
                                    <span class="message-time">2:32 PM</span>
                                </div>
                                <div class="message-content">
                                    Yes! It worked great on my tomato plants. No more blight issues.
                                </div>
                            </div>
                            <div class="message other">
                                <div class="message-header">
                                    <span class="message-sender">Tomato Grower</span>
                                    <span class="message-time">2:35 PM</span>
                                </div>
                                <div class="message-content">
                                    How often did you apply it? I'm having similar issues.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold">Frequently Asked Questions</h2>
                        <p class="text-muted">Quick answers to common questions</p>
                    </div>
                    
                    <div class="faq-accordion">
                        <div class="accordion" id="faqAccordion">
                            <!-- FAQ Items remain the same -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Chat Button -->
    <div class="live-chat-btn" id="chatButton">
        <i class="fas fa-comments"></i>
    </div>

    <!-- Enhanced Live Chat Window -->
    <div class="chat-window" id="chatWindow">
        <div class="chat-header">
            <div class="chat-header-info">
                <h5>Community Support</h5>
                <small>Chat with our team & community</small>
            </div>
            <div class="online-indicator">
                <div class="online-dot"></div>
                <span id="onlineCount">15 Online</span>
            </div>
        </div>
        
        <div class="chat-room-info">
            <div class="room-users" id="roomUsers">
                <span class="me-2">Active now:</span>
                <!-- Users will be added dynamically -->
            </div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message support">
                <div class="message-header">
                    <span class="message-sender">Support Bot</span>
                    <span class="message-time" id="currentTime"></span>
                </div>
                <div class="message-content">
                    Welcome to CropDoctor AI Community Chat! You can ask questions here and get help from our team and other community members.
                </div>
            </div>
        </div>
        
        <div class="user-typing" id="userTyping">
            <span id="typingUser">Someone</span> is typing<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
        </div>
        
        <div class="chat-input-area">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Type your message..." id="chatInput">
                <button class="btn btn-primary" type="button" id="sendMessage">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="mt-2 text-center">
                <small class="text-muted">Press Enter to send</small>
            </div>
        </div>
    </div>



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced Chat Simulation
        class ChatRoom {
            constructor() {
                this.users = ['Farmer John', 'Garden Expert', 'Tomato Grower', 'Organic Farmer', 'Agri Student'];
                this.supportTeam = ['Dr. Plant', 'Crop Specialist', 'Botany Expert'];
                this.messages = [];
                this.currentUser = 'Guest_' + Math.floor(Math.random() * 1000);
                this.isTyping = false;
                this.typingTimer = null;
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
                this.updateOnlineUsers();
                this.showWelcomeMessage();
                this.startAutoMessages();
            }
            
            setupEventListeners() {
                // Chat toggle
                document.getElementById('chatButton').addEventListener('click', () => this.toggleChat());
                
                // Send message
                document.getElementById('sendMessage').addEventListener('click', () => this.sendMessage());
                document.getElementById('chatInput').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.sendMessage();
                    }
                });
                
                // Typing indicator
                document.getElementById('chatInput').addEventListener('input', () => this.showTypingIndicator());
            }
            
            toggleChat() {
                const chatWindow = document.getElementById('chatWindow');
                chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
            }
            
            sendMessage() {
                const input = document.getElementById('chatInput');
                const message = input.value.trim();
                
                if (message) {
                    this.addMessage(this.currentUser, message, 'user');
                    input.value = '';
                    this.hideTypingIndicator();
                    
                    // Simulate responses
                    setTimeout(() => this.generateAutoResponse(message), 1000 + Math.random() * 2000);
                }
            }
            
            addMessage(sender, content, type) {
                const messagesContainer = document.getElementById('chatMessages');
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${type}`;
                
                const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                messageDiv.innerHTML = `
                    <div class="message-header">
                        <span class="message-sender">${sender}</span>
                        <span class="message-time">${time}</span>
                    </div>
                    <div class="message-content">${content}</div>
                `;
                
                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                
                // Store message
                this.messages.push({ sender, content, type, time });
            }
            
            showTypingIndicator() {
                if (!this.isTyping) {
                    this.isTyping = true;
                    const typingIndicator = document.getElementById('userTyping');
                    const typingUser = document.getElementById('typingUser');
                    
                    // Randomly select a user who's "typing"
                    const randomUser = this.supportTeam[Math.floor(Math.random() * this.supportTeam.length)];
                    typingUser.textContent = randomUser;
                    
                    typingIndicator.style.display = 'block';
                    
                    // Clear previous timer
                    if (this.typingTimer) clearTimeout(this.typingTimer);
                    
                    // Hide typing indicator after 3 seconds
                    this.typingTimer = setTimeout(() => {
                        this.hideTypingIndicator();
                    }, 3000);
                }
            }
            
            hideTypingIndicator() {
                this.isTyping = false;
                document.getElementById('userTyping').style.display = 'none';
            }
            
            generateAutoResponse(userMessage) {
                const responses = [
                    "That's a great question! I've had similar experiences with my crops.",
                    "Thanks for sharing. Have you tried checking the soil pH levels?",
                    "I can help with that! What specific symptoms are you seeing?",
                    "Interesting point. Many farmers in our community have discussed this.",
                    "Let me share some resources that might help with that issue.",
                    "I recommend checking our knowledge base for detailed guides on this topic."
                ];
                
                const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                const randomUser = this.supportTeam[Math.floor(Math.random() * this.supportTeam.length)];
                
                this.addMessage(randomUser, randomResponse, 'support');
            }
            
            showWelcomeMessage() {
                const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                document.getElementById('currentTime').textContent = time;
            }
            
            updateOnlineUsers() {
                const onlineCount = 15 + Math.floor(Math.random() * 10);
                document.getElementById('onlineCount').textContent = onlineCount + ' Online';
                
                const roomUsers = document.getElementById('roomUsers');
                roomUsers.innerHTML = '<span class="me-2">Active now:</span>';
                
                // Show 3-5 random active users
                const showCount = 3 + Math.floor(Math.random() * 3);
                const shuffledUsers = [...this.users].sort(() => 0.5 - Math.random());
                
                shuffledUsers.slice(0, showCount).forEach(user => {
                    const userBadge = document.createElement('div');
                    userBadge.className = 'user-badge';
                    userBadge.textContent = user;
                    roomUsers.appendChild(userBadge);
                });
                
                const remaining = onlineCount - showCount;
                if (remaining > 0) {
                    const moreBadge = document.createElement('div');
                    moreBadge.className = 'user-badge';
                    moreBadge.textContent = `+${remaining} more`;
                    roomUsers.appendChild(moreBadge);
                }
            }
            
            startAutoMessages() {
                // Add occasional auto messages to simulate active community
                setInterval(() => {
                    if (Math.random() > 0.7 && this.messages.length > 0) { // 30% chance
                        const randomUser = this.users[Math.floor(Math.random() * this.users.length)];
                        const autoMessages = [
                            "Has anyone tried the new organic pest control method?",
                            "My tomato plants are thriving with these tips!",
                            "Great discussion everyone! Learning a lot.",
                            "Can someone recommend good fertilizer for citrus trees?",
                            "The weather has been perfect for growing this season!"
                        ];
                        
                        const randomMessage = autoMessages[Math.floor(Math.random() * autoMessages.length)];
                        this.addMessage(randomUser, randomMessage, 'other');
                    }
                }, 15000); // Every 15 seconds
                
                // Update online users periodically
                setInterval(() => this.updateOnlineUsers(), 30000);
            }
        }
        
        // Initialize chat room when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new ChatRoom();
        });
    </script>
</body>
</html>