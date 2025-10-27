<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
// Initialize session and theme
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default theme if not exists
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'default'; // or your default theme
}
requireLogin();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user settings
$sql = "SELECT u.username, u.email, 
               s.email_notifications, s.sms_notifications, s.language, s.theme,
               s.newsletter_subscription, s.auto_save, s.data_sharing
        FROM users u 
        LEFT JOIN user_settings s ON u.id = s.user_id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_settings = $result->fetch_assoc();
$stmt->close();

// Set default values if null
$user_settings = array_merge([
    'email_notifications' => 1,
    'sms_notifications' => 0,
    'language' => 'en',
    'theme' => 'light',
    'newsletter_subscription' => 1,
    'auto_save' => 1,
    'data_sharing' => 0
], $user_settings ?: []);

// Handle form submission for updating settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        $newsletter_subscription = isset($_POST['newsletter_subscription']) ? 1 : 0;
        $auto_save = isset($_POST['auto_save']) ? 1 : 0;
        $data_sharing = isset($_POST['data_sharing']) ? 1 : 0;
        $language = $_POST['language'] ?? 'en';
        $theme = $_POST['theme'] ?? 'light';

        // Update or insert user settings
        $check_settings_sql = "SELECT id FROM user_settings WHERE user_id = ?";
        $check_settings_stmt = $conn->prepare($check_settings_sql);
        $check_settings_stmt->bind_param("i", $user_id);
        $check_settings_stmt->execute();
        $settings_exists = $check_settings_stmt->get_result()->num_rows > 0;
        $check_settings_stmt->close();

        if ($settings_exists) {
            $update_settings_sql = "UPDATE user_settings SET 
                email_notifications = ?, 
                sms_notifications = ?, 
                language = ?, 
                theme = ?,
                newsletter_subscription = ?,
                auto_save = ?,
                data_sharing = ?,
                updated_at = NOW()
                WHERE user_id = ?";
            $update_settings_stmt = $conn->prepare($update_settings_sql);
            $update_settings_stmt->bind_param("iissiiii", 
                $email_notifications, 
                $sms_notifications, 
                $language, 
                $theme,
                $newsletter_subscription,
                $auto_save,
                $data_sharing,
                $user_id
            );
        } else {
            $update_settings_sql = "INSERT INTO user_settings 
                (user_id, email_notifications, sms_notifications, language, theme, newsletter_subscription, auto_save, data_sharing) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $update_settings_stmt = $conn->prepare($update_settings_sql);
            $update_settings_stmt->bind_param("iiissiii", 
                $user_id, 
                $email_notifications, 
                $sms_notifications, 
                $language, 
                $theme,
                $newsletter_subscription,
                $auto_save,
                $data_sharing
            );
        }

        if ($update_settings_stmt->execute()) {
            $message = "Settings updated successfully!";
            
            // Update session with new theme if changed
            if ($theme !== $_SESSION['theme']) {
                $_SESSION['theme'] = $theme;
            }
        } else {
            throw new Exception("Failed to update settings.");
        }
        $update_settings_stmt->close();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | CropDoctor AI</title>
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
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --glass: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --blur: blur(10px);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--dark);
            background: linear-gradient(135deg, #f8fbf8 0%, #e8f5e8 100%);
            min-height: 100vh;
            line-height: 1.7;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Reuse your existing CSS styles from profile.php */
        /* Add only the unique styles needed for settings page */
        
        .settings-page {
            padding: 120px 0 60px;
            min-height: 100vh;
        }
        
        .settings-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .settings-grid {
            display: grid;
            gap: 30px;
        }
        
        .settings-section {
            background: var(--glass);
            backdrop-filter: var(--blur);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .settings-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .section-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            position: relative;
        }
        
        .section-header h3 {
            font-size: 1.8rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: var(--secondary);
        }
        
        /* Toggle Styles */
        .toggle-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s;
        }
        
        .toggle-group:hover {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .toggle-group:last-child {
            border-bottom: none;
        }
        
        .toggle-info {
            flex: 1;
        }
        
        .toggle-info h5 {
            margin-bottom: 5px;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .toggle-info p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        /* Select Styles */
        .select-group {
            margin-bottom: 25px;
        }
        
        .select-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
            display: block;
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 93, 26, 0.1);
        }
        
        /* Privacy Section */
        .privacy-note {
            background: rgba(26, 93, 26, 0.05);
            border-left: 4px solid var(--primary);
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .privacy-note p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }
        
        /* Buttons */
        .btn {
            padding: 14px 35px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
        }
        
        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 93, 26, 0.3);
        }
        
        /* Message Styles */
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 500;
            text-align: center;
        }
        
        .message.success {
            background: rgba(46, 139, 46, 0.1);
            color: var(--primary);
            border-left: 4px solid var(--primary);
        }
        
        .message.error {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        @media (max-width: 768px) {
            .settings-page {
                padding: 100px 0 40px;
            }
            
            .settings-section {
                padding: 25px;
            }
            
            .toggle-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .toggle-info {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Settings Page -->
    <section class="settings-page">
        <div class="settings-container">
            <div class="page-header animate-on-scroll">
                <h2>Application Settings</h2>
                <p>Customize your CropDoctor AI experience</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message success animate-on-scroll">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error animate-on-scroll">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="settings-form">
                <div class="settings-grid">
                    <!-- Notification Settings -->
                    <section class="settings-section animate-on-scroll">
                        <div class="section-header">
                            <h3><i class="fas fa-bell"></i> Notification Settings</h3>
                        </div>
                        
                        <div class="toggle-group">
                            <div class="toggle-info">
                                <h5>Email Notifications</h5>
                                <p>Receive email alerts for new diagnoses, crop updates, and important announcements</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="email_notifications" <?php echo $user_settings['email_notifications'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="toggle-group">
                            <div class="toggle-info">
                                <h5>SMS Notifications</h5>
                                <p>Get text message alerts for urgent crop disease detections and critical updates</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="sms_notifications" <?php echo $user_settings['sms_notifications'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="toggle-group">
                            <div class="toggle-info">
                                <h5>Newsletter Subscription</h5>
                                <p>Receive weekly farming tips, crop insights, and agricultural news</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="newsletter_subscription" <?php echo $user_settings['newsletter_subscription'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </section>

                    <!-- Application Preferences -->
                    <section class="settings-section animate-on-scroll">
                        <div class="section-header">
                            <h3><i class="fas fa-sliders-h"></i> Application Preferences</h3>
                        </div>
                        
                        <div class="select-group">
                            <label class="select-label" for="language">Language</label>
                            <select id="language" name="language" class="form-control">
                                <option value="en" <?php echo $user_settings['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="es" <?php echo $user_settings['language'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                                <option value="fr" <?php echo $user_settings['language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                                <option value="de" <?php echo $user_settings['language'] === 'de' ? 'selected' : ''; ?>>German</option>
                                <option value="zh" <?php echo $user_settings['language'] === 'zh' ? 'selected' : ''; ?>>Chinese</option>
                            </select>
                        </div>
                        
                        <div class="select-group">
                            <label class="select-label" for="theme">Theme</label>
                            <select id="theme" name="theme" class="form-control">
                                <option value="light" <?php echo $user_settings['theme'] === 'light' ? 'selected' : ''; ?>>Light Theme</option>
                                <option value="dark" <?php echo $user_settings['theme'] === 'dark' ? 'selected' : ''; ?>>Dark Theme</option>
                                <option value="auto" <?php echo $user_settings['theme'] === 'auto' ? 'selected' : ''; ?>>Auto (System Default)</option>
                            </select>
                        </div>
                        
                        <div class="toggle-group">
                            <div class="toggle-info">
                                <h5>Auto-save Progress</h5>
                                <p>Automatically save your work while filling forms and creating diagnoses</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="auto_save" <?php echo $user_settings['auto_save'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </section>

                    <!-- Privacy & Data Settings -->
                    <section class="settings-section animate-on-scroll">
                        <div class="section-header">
                            <h3><i class="fas fa-shield-alt"></i> Privacy & Data</h3>
                        </div>
                        
                        <div class="toggle-group">
                            <div class="toggle-info">
                                <h5>Anonymous Data Sharing</h5>
                                <p>Help improve CropDoctor AI by sharing anonymous usage data and crop patterns</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="data_sharing" <?php echo $user_settings['data_sharing'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="privacy-note">
                            <p><i class="fas fa-info-circle"></i> <strong>Note:</strong> We respect your privacy. All shared data is anonymized and used only to improve our agricultural AI models. Your personal information and farm details are never shared with third parties.</p>
                        </div>
                    </section>

                    <!-- Actions Section -->
                    <section class="settings-section animate-on-scroll">
                        <div class="section-header">
                            <h3><i class="fas fa-cog"></i> Actions</h3>
                        </div>
                        
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save All Settings
                            </button>
                            
                            <button type="button" class="btn" style="background: transparent; border: 2px solid #666; color: #666;" onclick="resetToDefaults()">
                                <i class="fas fa-undo"></i>
                                Reset to Defaults
                            </button>
                            
                            <button type="button" class="btn" style="background: transparent; border: 2px solid var(--accent); color: var(--accent);" onclick="exportData()">
                                <i class="fas fa-download"></i>
                                Export My Data
                            </button>
                        </div>
                    </section>
                </div>
            </form>
        </div>
    </section>

    <script>
        // Reset to default settings
        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all settings to their default values?')) {
                document.getElementById('language').value = 'en';
                document.getElementById('theme').value = 'light';
                
                const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    if (checkbox.name === 'email_notifications' || checkbox.name === 'newsletter_subscription' || checkbox.name === 'auto_save') {
                        checkbox.checked = true;
                    } else {
                        checkbox.checked = false;
                    }
                });
                
                alert('Settings have been reset to defaults. Click "Save All Settings" to apply changes.');
            }
        }
        
        // Export data function
        function exportData() {
            alert('Data export feature coming soon! This will allow you to download all your farm data and settings.');
        }
        
        // Auto-save form changes (optional)
        let autoSaveTimeout;
        document.querySelectorAll('#settings-form input, #settings-form select').forEach(element => {
            element.addEventListener('change', function() {
                if (document.querySelector('input[name="auto_save"]').checked) {
                    clearTimeout(autoSaveTimeout);
                    autoSaveTimeout = setTimeout(() => {
                        document.getElementById('settings-form').submit();
                    }, 2000);
                }
            });
        });
        
        // Animation on scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                if (elementTop < windowHeight - 100) {
                    element.classList.add('animated');
                }
            });
        }
        
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>