<?php
// Start session and include required files
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/emailverify.php'; // Assuming this file contains sendVerificationEmail

// Initialize variables
$message = '';
$user_email = ''; // Not strictly needed, but kept for clarity

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Basic validation... (omitted for brevity, keep your existing validation)

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } else {
        // Check if email already exists
        $sql_check = "SELECT id FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $message = "Email already registered. Please use a different email or login.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute the SQL query to insert the user
            $sql = "INSERT INTO users (username, email, password, is_verified) VALUES (?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // --- VERIFICATION MECHANISM SETUP ---
                
                // 1. Generate the long-lived verification token (for button/link)
                $verification_token = bin2hex(random_bytes(32));
                $token_expires_at = date("Y-m-d H:i:s", strtotime('+5 minutes'));

                // 2. Generate the short-lived numeric OTP (for manual entry)
                $otp_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $otp_expires_at = date("Y-m-d H:i:s", strtotime('+5 minutes')); // 5 minutes expiry

                // Update user record with both token and OTP
                $sql_update = "UPDATE users SET verification_token = ?, verification_token_expires_at = ?, otp_code = ?, otp_expires_at = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssi", $verification_token, $token_expires_at, $otp_code, $otp_expires_at, $user_id);
                $stmt_update->execute();
                $stmt_update->close();

                // Build the email content
                $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/pages/verify.php?token=" . $verification_token;
                $subject = "🌱 Verify Your CropDoctor AI Account";

                $body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your CropDoctor AI Account</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap");
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Montserrat", sans-serif;
            line-height: 1.6;
            color: #1e2a1e;
            background: linear-gradient(135deg, #f8fbf8 0%, #ffffff 50%, #f0f7f0 100%);
            margin: 0;
            padding: 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(26, 93, 26, 0.1);
            border: 1px solid #e8f5e8;
        }
        
        .email-header {
            background: linear-gradient(135deg, #1a5d1a 0%, #2e8b2e 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .email-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width=\'100\' height=\'100\' viewBox=\'0 0 100 100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z\' fill=\'%23ffffff\' fill-opacity=\'0.1\' fill-rule=\'evenodd\'/%3E%3C/svg%3E");
            opacity: 0.3;
        }
        
        .logo {
            font-family: "Playfair Display", serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .logo-icon {
            font-size: 2.8rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .email-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .email-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .email-content {
            padding: 40px 30px;
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-section h2 {
            font-family: "Playfair Display", serif;
            font-size: 2rem;
            color: #1a5d1a;
            margin-bottom: 15px;
        }
        
        .welcome-section p {
            font-size: 1.1rem;
            color: #555;
        }
        
        .verification-methods {
            background: #f8fbf8;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            border: 2px dashed #e0e0e0;
        }
        
        .method-title {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a5d1a;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .verification-button {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #1a5d1a 0%, #2e8b2e 100%);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .verification-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 93, 26, 0.4);
        }
        
        .verification-button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .verification-button:hover::before {
            left: 100%;
        }
        
        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider-text {
            background: white;
            padding: 0 20px;
            color: #666;
            font-weight: 500;
            position: relative;
            display: inline-block;
        }
        
        .otp-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .otp-label {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 15px;
            display: block;
        }
        
        .otp-code {
            background: linear-gradient(135deg, #1a5d1a 0%, #2e8b2e 100%);
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            font-family: "Montserrat", monospace;
            padding: 20px;
            border-radius: 15px;
            letter-spacing: 8px;
            margin: 20px auto;
            max-width: 350px;
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.2);
            border: 3px solid #e8f5e8;
            position: relative;
            overflow: hidden;
        }
        
        .otp-code::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .expiry-info {
            background: #fff8e1;
            border-left: 4px solid #e6b325;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 25px 0;
            font-size: 0.95rem;
        }
        
        .expiry-info strong {
            color: #1a5d1a;
        }
        
        .security-note {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 25px 0;
            font-size: 0.9rem;
            color: #555;
        }
        
        .steps-section {
            margin: 40px 0;
        }
        
        .steps-title {
            font-size: 1.3rem;
            color: #1a5d1a;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fbf8;
            border-radius: 10px;
            border-left: 4px solid #2e8b2e;
        }
        
        .step-number {
            background: #1a5d1a;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .step-content h4 {
            color: #1a5d1a;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .email-footer {
            background: #1e2a1e;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .footer-logo {
            font-family: "Playfair Display", serif;
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #2e8b2e;
        }
        
        .contact-info {
            margin: 20px 0;
            font-size: 0.9rem;
            color: #aaa;
        }
        
        .contact-info a {
            color: #2e8b2e;
            text-decoration: none;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            background: #2e8b2e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: #1a5d1a;
            transform: translateY(-2px);
        }
        
        .copyright {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #777;
            font-size: 0.8rem;
        }
        
        @media (max-width: 600px) {
            .email-content {
                padding: 30px 20px;
            }
            
            .verification-methods {
                padding: 20px;
            }
            
            .otp-code {
                font-size: 2rem;
                letter-spacing: 6px;
                padding: 15px;
            }
            
            .email-header {
                padding: 30px 20px;
            }
            
            .logo {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">
                <span class="logo-icon">🌱</span>
                CropDoctor AI
            </div>
            <h1>Email Verification Required</h1>
            <p>One final step to activate your account</p>
        </div>
        
        <!-- Main Content -->
        <div class="email-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2>Welcome to CropDoctor AI!</h2>
                <p>Hello <strong>' . htmlspecialchars($username) . '</strong>,</p>
                <p>Thank you for joining our community of farmers and agricultural experts. Let\'s get your account verified!</p>
            </div>
            
            <!-- Verification Methods -->
            <div class="verification-methods">
                <div class="method-title">
                    <span>📧</span>
                    Choose Your Verification Method
                </div>
                
                <!-- Method 1: Verification Link -->
                <a href="' . $verification_link . '" class="verification-button">
                    <span style="margin-right: 8px;">✓</span> Verify My Email Instantly
                </a>
                
                <!-- Divider -->
                <div class="divider">
                    <span class="divider-text">- OR -</span>
                </div>
                
                <!-- Method 2: OTP Code -->
                <div class="otp-section">
                    <span class="otp-label">Enter this code on the verification page:</span>
                    <div class="otp-code">' . $otp_code . '</div>
                </div>
            </div>
            
            <!-- Expiry Information -->
            <div class="expiry-info">
                <strong>⏰ Important:</strong> The 6-digit code above expires in <strong>5 minutes</strong>. 
                The verification link also expires in 5 minutes for security reasons.
            </div>
            
            <!-- Security Note -->
            <div class="security-note">
                <strong>🔒 Security Notice:</strong> For your protection, please do not share this code or link with anyone. 
                CropDoctor AI will never ask for your password or verification codes.
            </div>
            
            <!-- Steps Section -->
            <div class="steps-section">
                <h3 class="steps-title">Quick Steps to Complete Verification</h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Choose Your Method</h4>
                        <p>Click the verification button above or use the 6-digit code</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Complete Verification</h4>
                        <p>You\'ll be redirected to our secure verification page</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Start Using CropDoctor AI</h4>
                        <p>Access all features including crop disease detection and expert advice</p>
                    </div>
                </div>
            </div>
            
            <!-- Support Section -->
            <div style="text-align: center; margin: 40px 0 20px; padding: 20px; background: #f8fbf8; border-radius: 10px;">
                <h4 style="color: #1a5d1a; margin-bottom: 10px;">Need Help?</h4>
                <p style="color: #666; margin-bottom: 15px;">If you\'re having trouble verifying your account, please contact our support team.</p>
                <a href="mailto:support@cropdoctor.ai" style="color: #1a5d1a; text-decoration: none; font-weight: 500;">support@cropdoctor.ai</a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-logo">CropDoctor AI</div>
            <p>Your trusted partner in agricultural health and crop protection</p>
            
            <div class="contact-info">
                <p>Email: <a href="mailto:remotaskfreelancer@gmail.com">remotaskfreelancer@gmail.com</a></p>
                <p>Phone: <a href="tel:+254102273123">+254 102 273 123</a></p>
            </div>
            
            <div class="social-links">
                <a href="#" class="social-link">f</a>
                <a href="#" class="social-link">t</a>
                <a href="#" class="social-link">in</a>
                <a href="#" class="social-link">ig</a>
            </div>
            
            <div class="copyright">
                &copy; 2023 CropDoctor AI. All rights reserved.<br>
                Protecting crops, ensuring food security.
            </div>
        </div>
    </div>
</body>
</html>
';
                
                // --- REDIRECTION LOGIC ---
                if (sendVerificationEmail($email, $username, $subject, $body)) {
                    
                    // Set session variables to confirm success on the next page
                    $_SESSION['registration_email'] = $email;
                    $_SESSION['success_message'] = "Registration successful! A verification code has been sent to " . htmlspecialchars($email);

                    // Redirect the user immediately to the verification page
                    header("Location: verify.php?email=" . urlencode($email));
                    exit(); // STOP EXECUTION

                } else {
                    // Handle mailer error - show an error message instead of redirecting
                    // Optionally, you might want to delete the user record if email is essential
                    $message = "Registration successful, but we couldn't send the verification email. Please contact support.";
                }

            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CropDoctor AI</title>
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
        
        /* Registration Section */
        .register-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0;
            background: linear-gradient(to bottom, #f8fbf8 0%, #ffffff 100%);
            position: relative;
            min-height: calc(100vh - 160px);
        }
        
        .register-section::before {
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
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 2;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .register-header {
            background: var(--gradient);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .register-header h2 {
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .register-header p {
            opacity: 0.9;
        }
        
        .register-form {
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
        
        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
            background: #eee;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .password-strength.weak .password-strength-bar {
            width: 33%;
            background: #e74c3c;
        }
        
        .password-strength.medium .password-strength-bar {
            width: 66%;
            background: #f39c12;
        }
        
        .password-strength.strong .password-strength-bar {
            width: 100%;
            background: #2ecc71;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
        }
        
        .requirement i {
            margin-right: 5px;
            font-size: 0.7rem;
        }
        
        .requirement.met {
            color: #2ecc71;
        }
        
        .requirement.unmet {
            color: #e74c3c;
        }
        
        .register-btn {
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
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.3);
        }
        
        .register-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        
        .register-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .register-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .register-footer a:hover {
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
            .register-container {
                margin: 0 20px;
            }
            
            .register-form {
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

    <section class="register-section">
        <div class="register-container">
            <div class="register-header">
                <h2>Join CropDoctor AI</h2>
                <p>Create your account to start protecting your crops</p>
            </div>
            
            <div class="register-form">
                <?php if (isset($message) && !empty($message)): ?>
                    <div class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post" id="registrationForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        <i class="fas fa-user form-icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <i class="fas fa-envelope form-icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Create a strong password" required>
                        <i class="fas fa-lock form-icon"></i>
                        <span class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </span>
                        
                        <div class="password-strength" id="passwordStrength">
                            <div class="password-strength-bar"></div>
                        </div>
                        
                        <div class="password-requirements">
                            <div class="requirement unmet" id="lengthReq">
                                <i class="fas fa-circle"></i> At least 8 characters
                            </div>
                            <div class="requirement unmet" id="upperReq">
                                <i class="fas fa-circle"></i> One uppercase letter
                            </div>
                            <div class="requirement unmet" id="lowerReq">
                                <i class="fas fa-circle"></i> One lowercase letter
                            </div>
                            <div class="requirement unmet" id="numberReq">
                                <i class="fas fa-circle"></i> One number
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Confirm your password" required>
                        <i class="fas fa-lock form-icon"></i>
                        <span class="password-toggle" id="confirmPasswordToggle">
                            <i class="fas fa-eye"></i>
                        </span>
                        <div id="passwordMatch" class="password-requirements"></div>
                    </div>

                    <button type="submit" class="register-btn" id="registerBtn">Create Account</button>
                </form>
                
                <div class="register-footer">
                    <p>Already have an account? <a href="login.php">Sign in here</a>.</p>
                    <p>By registering, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>
                </div>
            </div>
        </div>
        
        <div class="floating-elements">
            </div>
    </section>

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
                        <li><a href="#home"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="#features"><i class="fas fa-chevron-right"></i> Features</a></li>
                        <li><a href="#how-it-works"><i class="fas fa-chevron-right"></i> How It Works</a></li>
                        <li><a href="#testimonials"><i class="fas fa-chevron-right"></i> Testimonials</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Disease Library</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Blog</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQs</a></li>
                        <li><a href="./pages/support.php"><i class="fas fa-chevron-right"></i> Support</a></li>
                        <li><a href="./pages/dev.php"><i class="fas fa-chevron-right"></i> Developer</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Contact Us</h5>
                    <ul class="footer-links">
                        <li><a href="mailto:remotaskfreelancer@gmail.com"><i class="fas fa-envelope"></i> remotaskfreelancer@gmail.com</a></li>
                        <li><a href="tel:+254102273123"><i class="fas fa-phone"></i> +254 102 273 123</a></li>
                        <li><a href="https://wa.me/254703917940" target="_blank"><i class="fab fa-whatsapp"></i> +254 703 917 940</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Kisii, Nyanchwa</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 CropDoctor AI. All rights reserved.</p>
            </div>
        </div>
    </footer>

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
        
        document.getElementById('confirmPasswordToggle').addEventListener('click', function() {
            const confirmPasswordField = document.getElementById('confirmPassword');
            const icon = this.querySelector('i');
            
            if (confirmPasswordField.type === 'password') {
                confirmPasswordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthIndicator = strengthBar.querySelector('.password-strength-bar');
            
            // Reset requirements
            document.getElementById('lengthReq').classList.remove('met');
            document.getElementById('upperReq').classList.remove('met');
            document.getElementById('lowerReq').classList.remove('met');
            document.getElementById('numberReq').classList.remove('met');
            
            // Check requirements
            let strength = 0;
            
            // Length requirement
            if (password.length >= 8) {
                strength += 25;
                document.getElementById('lengthReq').classList.add('met');
            }
            
            // Uppercase requirement
            if (/[A-Z]/.test(password)) {
                strength += 25;
                document.getElementById('upperReq').classList.add('met');
            }
            
            // Lowercase requirement
            if (/[a-z]/.test(password)) {
                strength += 25;
                document.getElementById('lowerReq').classList.add('met');
            }
            
            // Number requirement
            if (/[0-9]/.test(password)) {
                strength += 25;
                document.getElementById('numberReq').classList.add('met');
            }
            
            // Update strength bar
            strengthBar.className = 'password-strength';
            if (strength > 0) {
                if (strength <= 25) {
                    strengthBar.classList.add('weak');
                } else if (strength <= 75) {
                    strengthBar.classList.add('medium');
                } else {
                    strengthBar.classList.add('strong');
                }
            }
            
            // Check password match
            checkPasswordMatch();
        });
        
        // Password match checker
        document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const matchIndicator = document.getElementById('passwordMatch');
            const registerBtn = document.getElementById('registerBtn');
            
            if (confirmPassword === '') {
                matchIndicator.innerHTML = '';
                registerBtn.disabled = true;
            } else if (password === confirmPassword) {
                matchIndicator.innerHTML = '<div class="requirement met"><i class="fas fa-check-circle"></i> Passwords match</div>';
                registerBtn.disabled = false;
            } else {
                matchIndicator.innerHTML = '<div class="requirement unmet"><i class="fas fa-times-circle"></i> Passwords do not match</div>';
                registerBtn.disabled = true;
            }
        }
        
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!username || !email || !password || !confirmPassword) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
            
            // Password match validation
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            // Password strength validation
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>