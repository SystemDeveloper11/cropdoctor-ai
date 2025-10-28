
<?php
// Start session and include required files
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Initialize variables
$message = '';

// If a user is already logged in, redirect them to the dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
} 

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        // Prepare SQL query to get user data
        $sql = "SELECT id, username, password, role, is_verified FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Check if account is verified
                if (!$user['is_verified']) {
                    $message = "Please verify your email address before logging in. Check your inbox for the verification link.";
                } 
                // Verify password
                elseif (password_verify($password, $user['password'])) {
                    // Password is correct, start a session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;

                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $message = "Invalid email or password.";
                }
            } else {
                $message = "Invalid email or password.";
            }
            $stmt->close();
        } else {
            $message = "Database error. Please try again.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CropDoctor AI</title>
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
            --gradient-light: linear-gradient(135deg, var(--primary-light) 0%, #4aab4a 100%);
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            line-height: 1.3;
        }
        
        /* Header Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 20px 0;
            transition: all 0.4s ease;
            backdrop-filter: blur(10px);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary);
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 10px;
            color: var(--secondary);
            font-size: 2rem;
        }
        
        .nav-link {
            font-weight: 500;
            margin: 0 12px;
            color: var(--dark);
            transition: all 0.3s;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .btn-primary {
            background: var(--gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 93, 26, 0.3);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-secondary {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s;
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.2);
        }
        
        /* Login Section */
        .login-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0;
            background: linear-gradient(to bottom, #f8fbf8 0%, #ffffff 100%);
            position: relative;
            min-height: calc(100vh - 160px);
        }
        
        .login-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%231a5d1a" fill-opacity="0.02" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 2;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .login-header {
            background: var(--gradient);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h2 {
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .login-header p {
            opacity: 0.9;
        }
        
        .login-form {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #f9f9f9;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 93, 26, 0.1);
            background: white;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 40px;
            cursor: pointer;
            color: #777;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.3);
        }
        
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        
        .message.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .login-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            pointer-events: none;
        }
        
        .floating-element {
            position: absolute;
            opacity: 0.1;
            font-size: 2rem;
            color: var(--primary);
            animation: float 15s infinite linear;
        }
        
        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
            animation-duration: 20s;
        }
        
        .floating-element:nth-child(2) {
            top: 70%;
            left: 15%;
            animation-delay: 5s;
            animation-duration: 25s;
        }
        
        .floating-element:nth-child(3) {
            top: 40%;
            left: 85%;
            animation-delay: 10s;
            animation-duration: 18s;
        }
        
        .floating-element:nth-child(4) {
            top: 80%;
            left: 80%;
            animation-delay: 7s;
            animation-duration: 22s;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
            100% { transform: translateY(0) rotate(360deg); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 40px 0 20px;
        }
        
        .footer h5 {
            margin-bottom: 20px;
            color: var(--primary-light);
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #aaa;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--primary-light);
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icons a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #777;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                margin: 0 20px;
            }
            
            .login-form {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-seedling"></i>
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
                        <a class="nav-link" href="../index.php#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section class="login-section">
        <div class="login-container">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your CropDoctor AI account</p>
            </div>
            
<div class="login-form">
    <?php if (isset($message) && !empty($message)): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
                
                <form action="" method="post">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                        <i class="fas fa-envelope form-icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <i class="fas fa-lock form-icon"></i>
                        <span class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <button type="submit" class="login-btn">Login to Dashboard</button>
                </form>
                
                <div class="login-footer">
                    <p>Don't have an account? <a href="register.php">Create one here</a>.</p>
                    <p><a href="forgot-password.php">Forgot your password?</a></p>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="floating-elements">
            <div class="floating-element"><i class="fas fa-leaf"></i></div>
            <div class="floating-element"><i class="fas fa-apple-alt"></i></div>
            <div class="floating-element"><i class="fas fa-seedling"></i></div>
            <div class="floating-element"><i class="fas fa-tree"></i></div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5><i class="fas fa-seedling"></i> CropDoctor AI</h5>
                    <p>Your trusted partner in agricultural health. Using cutting-edge AI to protect crops and ensure food security worldwide.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../index.php#features">Features</a></li>
                        <li><a href="../index.php#how-it-works">How It Works</a></li>
                        <li><a href="../index.php#testimonials">Testimonials</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#">Disease Library</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contact Us</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> info@cropdoctor.ai</li>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Farm Lane, Agriculture City</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 CropDoctor AI. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
            
            return true;
        });
        
        // Add focus effects to form inputs
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>
</html>
