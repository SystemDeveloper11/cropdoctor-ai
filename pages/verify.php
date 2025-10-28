<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mailer.php';

$message = '';
$email_for_otp = htmlspecialchars($_GET['email'] ?? '');
$verification_successful = false; 

// Function to set user as verified and clear tokens/OTP
function verifyUser($conn, $user_id) {
    $sql = "UPDATE users SET is_verified = 1, verification_token = NULL, verification_token_expires_at = NULL, otp_code = NULL, otp_expires_at = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// --- 1. HANDLE DIRECT TOKEN VERIFICATION (Clicking the link in email) ---
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    // Find the user by the token
    $sql = "SELECT id, is_verified, verification_token_expires_at FROM users WHERE verification_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user['is_verified']) {
            $message = "Your account is already verified! You can now log in. 🟢";
            $verification_successful = true;
        } elseif (strtotime($user['verification_token_expires_at']) < time()) {
            $message = "Verification link has expired. Please log in to resend a new verification email. 🔴";
        } else {
            // Verify the user
            if (verifyUser($conn, $user['id'])) {
                $message = "Email successfully verified! You can now log in. 🟢";
                $verification_successful = true;
            } else {
                $message = "Verification failed due to a database error. Please contact support. 🔴";
            }
        }
    } else {
        $message = "Invalid or previously used verification link. 🔴";
    }
    // Clear GET parameters to prevent re-execution on refresh
    $_GET = [];
}

// --- 2. HANDLE OTP SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');

    if (empty($email) || empty($otp)) {
        $message = "Email and OTP are required. 🔴";
    } else {
        // Find user by email and check OTP
        $sql = "SELECT id, otp_code, otp_expires_at, is_verified FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['is_verified']) {
                $message = "Your account is already verified! You can now log in. 🟢";
                $verification_successful = true;
            } elseif (strtotime($user['otp_expires_at']) < time()) {
                $message = "The OTP code has expired. Please click 'Resend OTP' to get a new one. 🔴";
            } elseif ($user['otp_code'] === $otp) {
                // OTP is correct and not expired: Verify the user
                if (verifyUser($conn, $user['id'])) {
                    $message = "Email successfully verified using OTP! You can now log in. 🟢";
                    $verification_successful = true;
                } else {
                    $message = "Verification failed due to a database error. Please contact support. 🔴";
                }
            } else {
                $message = "Invalid OTP code. Please try again. 🔴";
            }
        } else {
            $message = "No user found with that email. 🔴";
        }
        $stmt->close();
    }
    $email_for_otp = $email; // Keep email filled in the form
}

// --- 3. HANDLE RESEND OTP REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend_otp') {
    $email = trim($_POST['email'] ?? '');

    // Check if the email is registered and unverified
    $sql = "SELECT id, username, is_verified FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_row = $result->fetch_assoc();
    $stmt->close();

    if ($user_row && !$user_row['is_verified']) {
        // Generate new OTP
        $new_otp_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $new_otp_expires_at = date("Y-m-d H:i:s", strtotime('+5 minutes'));

        // Update user record with new OTP
        $sql_update = "UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssi", $new_otp_code, $new_otp_expires_at, $user_row['id']);
        $stmt_update->execute();
        $stmt_update->close();

        // Re-send the email with the new OTP (assuming you have a function to do this)
        // You would need to regenerate the entire email body as done in register.php, 
        // but for simplicity, we'll just send the new code.
        
        // --- RE-SEND EMAIL LOGIC START ---
        // $resend_subject = "New Verification Code for CropDoctor AI";
        // $resend_body = "... new email body with $new_otp_code ...";
        // if (sendGenericEmail($email, $resend_subject, $resend_body)) {
            $message = "A new 6-digit OTP code has been sent to **" . htmlspecialchars($email) . "**. It will expire in 5 minutes. 📬";
        // } else {
        //     $message = "Failed to resend the new OTP email. Please contact support. 🔴";
        // }
        // --- RE-SEND EMAIL LOGIC END ---

    } elseif ($user_row && $user_row['is_verified']) {
        $message = "This account is already verified. Please proceed to login. 🟢";
        $verification_successful = true;
    } else {
        $message = "Email not found or is not linked to a registration requiring verification. 🔴";
    }
    $email_for_otp = $email; // Keep email filled in the form
}

// Get the expiry timestamp for the countdown
$otp_expiry_timestamp = null;
if (!$verification_successful && !empty($email_for_otp)) {
    $sql = "SELECT otp_expires_at FROM users WHERE email = ? AND is_verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email_for_otp);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && $user['otp_expires_at']) {
        $otp_expiry_timestamp = strtotime($user['otp_expires_at']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | CropDoctor AI</title>
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
        
        /* Verification Container */
        .verification-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            min-height: calc(100vh - 160px);
            background: linear-gradient(to bottom, #f8fbf8 0%, #ffffff 100%);
        }
        
        .verification-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .verification-header {
            background: var(--gradient);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .verification-header h2 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .verification-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .verification-body {
            padding: 40px 30px;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s;
            width: 100%;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 93, 26, 0.25);
        }
        
        .form-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        
        .otp-input {
            text-align: center;
            letter-spacing: 15px;
            font-size: 1.5rem;
            width: 100%;
            max-width: 250px;
            margin: 0 auto 20px;
            font-weight: 600;
        }
        
        .timer {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
            text-align: center;
            font-size: 1.1rem;
        }
        
        .btn-primary {
            background: var(--gradient);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
            position: relative;
            overflow: hidden;
            width: 100%;
            color: white;
            font-size: 1.1rem;
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
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s;
            width: 100%;
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.2);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .message.success {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #155724;
        }
        
        .message.error {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #721c24;
        }
        
        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 40px 0 20px;
            margin-top: auto;
        }
        
        .footer h5 {
            margin-bottom: 25px;
            color: var(--primary-light);
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--secondary);
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
        @media (max-width: 576px) {
            .verification-header {
                padding: 30px 20px;
            }
            
            .verification-body {
                padding: 30px 20px;
            }
            
            .verification-header h2 {
                font-size: 1.8rem;
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
        </div>
    </nav>

    <!-- Verification Section -->
    <section class="verification-container">
        <div class="verification-card">
            <div class="verification-header">
                <h2>Email Verification</h2>
                <p>Enter the 6-digit code sent to your email to activate your account</p>
            </div>
            
            <div class="verification-body">
                <?php if (isset($message) && !empty($message)): ?>
                    <div class="message <?php echo strpos($message, 'successfully verified') !== false || strpos($message, 'already verified') !== false || strpos($message, 'New 6-digit OTP code has been sent') !== false ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($verification_successful): ?>
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--primary);"></i>
                        </div>
                        <h4 class="mb-3">Verification Successful!</h4>
                        <p class="mb-4">Your account has been successfully verified. You can now log in to access all features.</p>
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                <?php else: ?>
                    <form action="verify.php" method="post" id="otpForm">
                        <input type="hidden" name="action" value="verify_otp">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email_for_otp); ?>" readonly required>
                            <i class="fas fa-envelope form-icon"></i>
                        </div>
                        
                        <div class="form-group text-center">
                            <label for="otp">Verification Code (OTP)</label>
                            <input type="text" id="otp" name="otp" class="form-control otp-input" maxlength="6" pattern="\d{6}" placeholder="------" required>
                            <small class="text-muted">Enter the 6-digit code sent to your email</small>
                        </div>

                        <div id="countdownTimer" class="timer text-center"></div>
                        
                        <button type="submit" class="btn btn-primary mb-3" id="verifyBtn">Verify Account</button>
                    </form>

                    <form action="verify.php" method="post" id="resendForm">
                        <input type="hidden" name="action" value="resend_otp">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_for_otp); ?>">
                        <button type="submit" class="btn btn-secondary" id="resendBtn" disabled>
                            <i class="fas fa-redo-alt me-2"></i>Resend OTP Code
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h5><i class="fas fa-seedling"></i> CropDoctor AI</h5>
                    <p>Your trusted partner in agricultural health</p>
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
        const expiryTime = <?php echo $otp_expiry_timestamp ? $otp_expiry_timestamp * 1000 : 'null'; ?>;
        const verifyBtn = document.getElementById('verifyBtn');
        const resendBtn = document.getElementById('resendBtn');
        const countdownTimer = document.getElementById('countdownTimer');
        const otpInput = document.getElementById('otp');

        function startTimer() {
            if (!expiryTime) {
                // No expiry time means no unverified user or expired OTP
                countdownTimer.innerHTML = 'Enter OTP or resend the code.';
                verifyBtn.disabled = true;
                resendBtn.disabled = false;
                return;
            }

            const interval = setInterval(function() {
                const now = new Date().getTime();
                const distance = expiryTime - now;

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (distance < 0) {
                    clearInterval(interval);
                    countdownTimer.innerHTML = 'OTP EXPIRED!';
                    verifyBtn.disabled = true;
                    resendBtn.disabled = false;
                    otpInput.disabled = true;
                } else {
                    countdownTimer.innerHTML = `Code expires in: ${minutes}m ${seconds}s`;
                    verifyBtn.disabled = false;
                    resendBtn.disabled = true;
                    otpInput.disabled = false;
                }
            }, 1000);
        }

        // Only start the timer if a valid, unverified user has an expiry time
        if (expiryTime) {
            startTimer();
        } else if (!<?php echo json_encode($verification_successful); ?>) {
            // If there's no expiry time and verification wasn't successful, allow resend
            countdownTimer.innerHTML = 'Ready to send a new OTP.';
            verifyBtn.disabled = true;
            resendBtn.disabled = false;
        }

        // Prevent resend spam by briefly disabling the resend button after a click
        resendBtn.addEventListener('click', function() {
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            setTimeout(() => {
                document.getElementById('resendForm').submit();
            }, 1000);
        });

        // Ensure OTP is numeric and max 6 digits
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
        
        // Auto-focus on OTP input
        if (otpInput) {
            otpInput.focus();
        }
    </script>
</body>
</html>
