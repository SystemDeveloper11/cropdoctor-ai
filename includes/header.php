<?php
// Start a session and include auth functions if they haven't been already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once 'auth.php';

// Get user data for personalized header
$user_name = '';
$user_initials = '';
$user_role = '';

if (isLoggedIn()) {
    // Use full name if available, otherwise username
    $user_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? '';
    $user_initials = !empty($user_name) ? strtoupper(substr($user_name, 0, 2)) : 'U';
    $user_role = isAdmin() ? 'Administrator' : 'User';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agri-Tech Crop Doctor</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <!-- Font Awesome for icons -->
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
            --gradient-light: linear-gradient(135deg, var(--primary-light) 0%, #4aab4a 100%);
            --shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 5px 20px rgba(0, 0, 0, 0.12);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--dark);
            background-color: #fefefe;
            overflow-x: hidden;
            line-height: 1.7;
        }
        
        /* Compact Header Styles */
        header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            height: 60px; /* Reduced height */
        }
        
        header.scrolled {
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0; /* Reduced padding */
            max-width: 1200px;
            margin: 0 auto;
            padding-left: 20px;
            padding-right: 20px;
            height: 100%;
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem; /* Smaller font */
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .logo:hover {
            color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .logo-icon {
            width: 32px; /* Smaller icon */
            height: 32px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            box-shadow: 0 3px 8px rgba(26, 93, 26, 0.2);
        }
        
        /* Compact Navigation Styles */
        nav ul {
            display: flex;
            list-style: none;
            gap: 3px; /* Reduced gap */
            align-items: center;
            margin: 0;
            padding: 0;
        }
        
        nav li {
            position: relative;
        }
        
        nav a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            padding: 8px 14px; /* Smaller padding */
            border-radius: 20px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
            overflow: hidden;
            font-size: 0.85rem; /* Smaller font */
        }
        
        nav a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            transition: left 0.3s;
            z-index: -1;
            border-radius: 20px;
        }
        
        nav a:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(26, 93, 26, 0.2);
        }
        
        nav a:hover::before {
            left: 0;
        }
        
        nav a i {
            font-size: 0.8rem; /* Smaller icons */
            width: 16px;
            text-align: center;
        }
        
        /* Compact User Menu Styles */
        .user-menu {
            position: relative;
        }
        
        .user-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px; /* Smaller padding */
            border-radius: 20px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .user-trigger:hover {
            background: var(--gradient);
            color: white;
            box-shadow: 0 3px 10px rgba(26, 93, 26, 0.2);
        }
        
        .user-avatar {
            width: 30px; /* Smaller avatar */
            height: 30px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            box-shadow: 0 2px 6px rgba(26, 93, 26, 0.2);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.8rem; /* Smaller font */
        }
        
        .user-role {
            font-size: 0.65rem; /* Smaller font */
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .user-trigger:hover .user-role {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .dropdown-arrow {
            transition: transform 0.3s;
            font-size: 0.7rem;
        }
        
        .user-menu.active .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-hover);
            min-width: 180px;
            padding: 8px 0;
            margin-top: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.3s;
            z-index: 1001;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .user-menu.active .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-dropdown::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 15px;
            width: 12px;
            height: 12px;
            background: white;
            transform: rotate(45deg);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            border-left: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.2s;
            border-left: 2px solid transparent;
            font-size: 0.85rem;
        }
        
        .dropdown-item:hover {
            background: #f8fbf8;
            border-left-color: var(--primary);
            padding-left: 20px;
        }
        
        .dropdown-item i {
            width: 16px;
            text-align: center;
            color: var(--primary);
            font-size: 0.8rem;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 6px 0;
        }
        
        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 20px;
            height: 16px;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        
        .mobile-toggle span {
            height: 2px;
            width: 100%;
            background: var(--primary);
            border-radius: 2px;
            transition: all 0.3s;
        }
        
        .mobile-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .mobile-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }
        
        /* Notification Badge */
        .nav-badge {
            position: absolute;
            top: 3px;
            right: 3px;
            background: var(--secondary);
            color: white;
            font-size: 0.6rem;
            font-weight: 600;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Main Content Adjustment - Reduced margin */
        main {
            margin-top: 60px; /* Reduced to match header height */
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .mobile-toggle {
                display: flex;
            }
            
            nav {
                position: fixed;
                top: 60px; /* Adjusted to match header height */
                left: 0;
                width: 100%;
                background: white;
                box-shadow: var(--shadow);
                padding: 15px;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.4s;
            }
            
            nav.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            
            nav ul {
                flex-direction: column;
                gap: 8px;
            }
            
            nav a {
                padding: 10px 16px;
                border-radius: 8px;
                justify-content: flex-start;
            }
            
            .user-info {
                display: none;
            }
            
            .user-dropdown {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                margin-top: 8px;
                background: #f8f9fa;
            }
            
            .user-dropdown::before {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            .logo-text {
                display: none;
            }
            
            .header-container {
                padding: 8px 15px;
            }
            
            .logo {
                font-size: 1.2rem;
            }
            
            header {
                height: 55px;
            }
            
            main {
                margin-top: 55px;
            }
        }
        
        /* Animation for header on scroll */
        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Notification Styles */
        .notification-bell {
            position: relative;
        }
        
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-hover);
            width: 280px;
            max-height: 350px;
            overflow-y: auto;
            padding: 12px 0;
            margin-top: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.3s;
            z-index: 1001;
        }
        
        .notification-bell.active .notification-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .notification-header {
            padding: 0 12px 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .notification-item {
            padding: 10px 12px;
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.2s;
            font-size: 0.85rem;
        }
        
        .notification-item:hover {
            background: #f8fbf8;
        }
        
        .notification-item.unread {
            background: rgba(26, 93, 26, 0.05);
        }
        
        /* Search Bar */
        .search-container {
            position: relative;
            margin: 0 12px;
        }
        
        .search-input {
            padding: 8px 12px 8px 35px;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            width: 200px;
            font-size: 0.8rem;
            transition: all 0.3s;
            background: #f8f9fa;
            height: 34px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(26, 93, 26, 0.1);
            width: 250px;
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .search-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header id="mainHeader">
        <div class="header-container">
            <a href="<?php echo BASE_URL; ?>" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <span class="logo-text">Agri-Tech AI Crop Doctor</span>
            </a>
            
            <!-- Search Bar (Visible on desktop) -->
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search diagnoses...">
            </div>
            
            <button class="mobile-toggle" id="mobileToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav id="mainNav">
                <ul>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo BASE_URL; ?>../agritech/index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>../agritech/pages/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>../agritech/pages/upload.php"><i class="fas fa-camera"></i> New Diagnosis</a></li>
                        <li><a href="<?php echo BASE_URL; ?>../agritech/pages/history.php"><i class="fas fa-history"></i> History</a></li>
                        
                        <!-- Notifications -->
                        <li class="notification-bell">
                            <a href="#" id="notificationTrigger">
                                <i class="fas fa-bell"></i> Notifications
                                <span class="nav-badge">3</span>
                            </a>
                            <div class="notification-dropdown">
                                <div class="notification-header">
                                    <strong>Notifications</strong>
                                    <a href="#" style="font-size: 0.75rem;">Mark all as read</a>
                                </div>
                                <div class="notification-item unread">
                                    <div style="font-weight: 500;">New diagnosis completed</div>
                                    <div style="font-size: 0.75rem; color: #666;">Tomato blight detected with 92% confidence</div>
                                    <div style="font-size: 0.65rem; color: #999;">2 hours ago</div>
                                </div>
                                <div class="notification-item">
                                    <div style="font-weight: 500;">System update</div>
                                    <div style="font-size: 0.75rem; color: #666;">New plant diseases added to database</div>
                                    <div style="font-size: 0.65rem; color: #999;">1 day ago</div>
                                </div>
                                <div class="notification-item">
                                    <div style="font-weight: 500;">Weekly report ready</div>
                                    <div style="font-size: 0.75rem; color: #666;">Your weekly crop health summary is available</div>
                                    <div style="font-size: 0.65rem; color: #999;">3 days ago</div>
                                </div>
                            </div>
                        </li>
                        
                        <?php if (isAdmin()): ?>
                            <li><a href="<?php echo BASE_URL; ?>../apro/agritech/pages/admin-dashboard.php"><i class="fas fa-cog"></i> Admin</a></li>
                        <?php endif; ?>
                        
                        <!-- User Menu -->
                        <li class="user-menu">
                            <div class="user-trigger" id="userTrigger">
                                <div class="user-avatar"><?php echo $user_initials; ?></div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                                    <div class="user-role"><?php echo $user_role; ?></div>
                                </div>
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </div>
                            <div class="user-dropdown">
                                <a href="<?php echo BASE_URL; ?>../apro/agritech/pages/profile.php" class="dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>Profile</span>
                                </a>
                                <a href="<?php echo BASE_URL; ?>../apro/agritech/pages/setting.php" class="dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo BASE_URL; ?>../apro/agritech/pages/logout.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>../apro/agritech/pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="<?php echo BASE_URL; ?>../apro/agritech/pages/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 30) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const mainNav = document.getElementById('mainNav');
        
        mobileToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mainNav.classList.toggle('active');
        });

        // User dropdown toggle
        const userTrigger = document.getElementById('userTrigger');
        const userMenu = document.querySelector('.user-menu');
        
        if (userTrigger) {
            userTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                userMenu.classList.toggle('active');
            });
        }

        // Notification dropdown toggle
        const notificationTrigger = document.getElementById('notificationTrigger');
        const notificationBell = document.querySelector('.notification-bell');
        
        if (notificationTrigger) {
            notificationTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                notificationBell.classList.toggle('active');
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (userMenu && !userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
            
            if (notificationBell && !notificationBell.contains(e.target)) {
                notificationBell.classList.remove('active');
            }
        });

        // Close mobile menu when clicking on a link
        const navLinks = document.querySelectorAll('nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileToggle.classList.remove('active');
                mainNav.classList.remove('active');
            });
        });

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                this.parentElement.style.zIndex = '1002';
            });
            
            searchInput.addEventListener('blur', function() {
                this.parentElement.style.zIndex = 'auto';
            });
        }

        // Add animation to header on page load
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('mainHeader');
            header.style.animation = 'slideInDown 0.5s ease-out';
        });
    </script>