<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$message = ''; 
$error = '';

// Fetch current user information with profile data
$sql = "SELECT u.username, u.email, u.full_name, u.phone, u.location, u.avatar, u.created_at, 
               p.bio, p.website, p.twitter, p.linkedin, p.farm_size, p.farm_type,
               s.email_notifications, s.sms_notifications, s.language, s.theme
        FROM users u 
        LEFT JOIN user_profiles p ON u.id = p.user_id 
        LEFT JOIN user_settings s ON u.id = s.user_id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();
$stmt->close();

// Set default values if null - FIXED: Added all missing keys
$user_info = array_merge([
    'full_name' => '',
    'phone' => '',
    'location' => '',
    'avatar' => '',
    'bio' => '',
    'website' => '',
    'twitter' => '',
    'linkedin' => '',
    'farm_size' => '',
    'farm_type' => '',
    'email_notifications' => 1,
    'sms_notifications' => 0,
    'language' => 'en',
    'theme' => 'light'
], $user_info ?: []);

// Handle form submission for updating profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';
    
    try {
        switch ($action) {
            case 'update_profile':
                $new_username = trim($_POST['username']);
                $new_email = trim($_POST['email']);
                $full_name = trim($_POST['full_name']);
                $phone = trim($_POST['phone']);
                $location = trim($_POST['location']);
                $bio = trim($_POST['bio']);
                $website = trim($_POST['website'] ?? '');
                $twitter = trim($_POST['twitter'] ?? '');
                $linkedin = trim($_POST['linkedin'] ?? '');
                $farm_size = trim($_POST['farm_size'] ?? '');
                $farm_type = trim($_POST['farm_type'] ?? '');

                // Validate required fields
                if (empty($new_username) || empty($new_email)) {
                    throw new Exception("Username and email are required.");
                }

                // Check if username or email already exists (excluding current user)
                $check_sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    throw new Exception("Username or email already exists.");
                }
                $check_stmt->close();

                // Handle avatar upload
                $avatar = $user_info['avatar'];
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../assets/images/avatars/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($file_extension, $allowed_extensions)) {
                        throw new Exception("Invalid file type. Please upload JPG, PNG, or GIF images.");
                    }
                    
                    // Check file size (max 5MB)
                    if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
                        throw new Exception("File size too large. Maximum size is 5MB.");
                    }
                    
                    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                        // Delete old avatar if exists
                        if (!empty($avatar)) {
                            $old_avatar_path = $upload_dir . $avatar;
                            if (file_exists($old_avatar_path)) {
                                unlink($old_avatar_path);
                            }
                        }
                        $avatar = $filename;
                    } else {
                        throw new Exception("Failed to upload avatar. Please try again.");
                    }
                }

                // Start transaction
                $conn->begin_transaction();

                // Update users table
                $update_user_sql = "UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, location = ?, avatar = ? WHERE id = ?";
                $update_user_stmt = $conn->prepare($update_user_sql);
                $update_user_stmt->bind_param("ssssssi", $new_username, $new_email, $full_name, $phone, $location, $avatar, $user_id);
                
                if (!$update_user_stmt->execute()) {
                    throw new Exception("Failed to update user profile.");
                }
                $update_user_stmt->close();

                // Update or insert user_profiles
                $check_profile_sql = "SELECT id FROM user_profiles WHERE user_id = ?";
                $check_profile_stmt = $conn->prepare($check_profile_sql);
                $check_profile_stmt->bind_param("i", $user_id);
                $check_profile_stmt->execute();
                $profile_exists = $check_profile_stmt->get_result()->num_rows > 0;
                $check_profile_stmt->close();

                if ($profile_exists) {
                    $update_profile_sql = "UPDATE user_profiles SET bio = ?, website = ?, twitter = ?, linkedin = ?, farm_size = ?, farm_type = ? WHERE user_id = ?";
                    $update_profile_stmt = $conn->prepare($update_profile_sql);
                    $update_profile_stmt->bind_param("ssssssi", $bio, $website, $twitter, $linkedin, $farm_size, $farm_type, $user_id);
                } else {
                    $update_profile_sql = "INSERT INTO user_profiles (user_id, bio, website, twitter, linkedin, farm_size, farm_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $update_profile_stmt = $conn->prepare($update_profile_sql);
                    $update_profile_stmt->bind_param("issssss", $user_id, $bio, $website, $twitter, $linkedin, $farm_size, $farm_type);
                }

                if (!$update_profile_stmt->execute()) {
                    throw new Exception("Failed to update profile details.");
                }
                $update_profile_stmt->close();

                // Commit transaction
                   $conn->commit();

// Update session variables
$_SESSION['username'] = $new_username;
$_SESSION['email'] = $new_email;
$_SESSION['full_name'] = $full_name; // 

                $message = "Profile updated successfully!";
                break;

            case 'change_password':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                // Validate passwords
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    throw new Exception("All password fields are required.");
                }

                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords do not match.");
                }

                if (strlen($new_password) < 8) {
                    throw new Exception("New password must be at least 8 characters long.");
                }

                // Verify current password
                $password_sql = "SELECT password FROM users WHERE id = ?";
                $password_stmt = $conn->prepare($password_sql);
                $password_stmt->bind_param("i", $user_id);
                $password_stmt->execute();
                $password_result = $password_stmt->get_result();
                
                if ($user = $password_result->fetch_assoc()) {
                    if (password_verify($current_password, $user['password'])) {
                        // Update password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_password_sql = "UPDATE users SET password = ? WHERE id = ?";
                        $update_password_stmt = $conn->prepare($update_password_sql);
                        $update_password_stmt->bind_param("si", $hashed_password, $user_id);
                        
                        if ($update_password_stmt->execute()) {
                            $message = "Password updated successfully!";
                        } else {
                            throw new Exception("Failed to update password.");
                        }
                        $update_password_stmt->close();
                    } else {
                        throw new Exception("Current password is incorrect.");
                    }
                } else {
                    throw new Exception("User not found.");
                }
                $password_stmt->close();
                break;

            case 'update_settings':
                $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
                $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
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
                    $update_settings_sql = "UPDATE user_settings SET email_notifications = ?, sms_notifications = ?, language = ?, theme = ? WHERE user_id = ?";
                    $update_settings_stmt = $conn->prepare($update_settings_sql);
                    $update_settings_stmt->bind_param("iissi", $email_notifications, $sms_notifications, $language, $theme, $user_id);
                } else {
                    $update_settings_sql = "INSERT INTO user_settings (user_id, email_notifications, sms_notifications, language, theme) VALUES (?, ?, ?, ?, ?)";
                    $update_settings_stmt = $conn->prepare($update_settings_sql);
                    $update_settings_stmt->bind_param("iiiss", $user_id, $email_notifications, $sms_notifications, $language, $theme);
                }

                if ($update_settings_stmt->execute()) {
                    $message = "Settings updated successfully!";
                } else {
                    throw new Exception("Failed to update settings.");
                }
                $update_settings_stmt->close();
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        if (isset($conn) && $conn->begin_transaction()) {
            $conn->rollback();
        }
    }

    // Refresh user info after update
    $refresh_sql = "SELECT u.username, u.email, u.full_name, u.phone, u.location, u.avatar, u.created_at, 
                           p.bio, p.website, p.twitter, p.linkedin, p.farm_size, p.farm_type,
                           s.email_notifications, s.sms_notifications, s.language, s.theme
                    FROM users u 
                    LEFT JOIN user_profiles p ON u.id = p.user_id 
                    LEFT JOIN user_settings s ON u.id = s.user_id 
                    WHERE u.id = ?";
    $refresh_stmt = $conn->prepare($refresh_sql);
    $refresh_stmt->bind_param("i", $user_id);
    $refresh_stmt->execute();
    $refresh_result = $refresh_stmt->get_result();
    $user_info = $refresh_result->fetch_assoc();
    $refresh_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | CropDoctor AI</title>
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
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="%231a5d1a" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
            z-index: -1;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            line-height: 1.3;
        }
        
        /* Profile Page Styles */
        .profile-page {
            padding: 120px 0 60px;
            min-height: 100vh;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .page-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }
        
        .page-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 20px auto 0;
        }
        
        /* Profile Layout */
        .profile-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        /* Profile Sidebar */
        .profile-sidebar {
            background: var(--glass);
            backdrop-filter: var(--blur);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 100px;
            transition: all 0.3s ease;
        }
        
        .profile-sidebar:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .profile-avatar {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .avatar-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .avatar-container:hover .avatar-image {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .avatar-container:hover .avatar-placeholder {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .avatar-upload-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 36px;
            height: 36px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        
        .avatar-upload-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }
        
        .profile-info h3 {
            color: var(--primary);
            margin-bottom: 5px;
            text-align: center;
        }
        
        .profile-info p {
            color: #666;
            text-align: center;
            margin-bottom: 5px;
        }
        
        .member-since {
            font-size: 0.9rem;
            color: #999;
            text-align: center;
            margin-top: 10px;
        }
        
        /* Profile Navigation - UPDATED */
        .profile-nav {
            list-style: none;
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .profile-nav-item {
            margin-bottom: 0;
        }
        
        .profile-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            color: var(--dark);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(5px);
        }
        
        .profile-nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            transition: left 0.3s;
            z-index: -1;
        }
        
        .profile-nav-link:hover, .profile-nav-link.active {
            color: white;
            transform: translateX(5px);
            border-color: transparent;
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.2);
        }
        
        .profile-nav-link:hover::before, .profile-nav-link.active::before {
            left: 0;
        }
        
        .profile-nav-link i {
            width: 20px;
            text-align: center;
            transition: transform 0.3s;
            font-size: 1.1rem;
        }
        
        .profile-nav-link:hover i, .profile-nav-link.active i {
            transform: scale(1.2);
        }
        
        /* Profile Content */
        .profile-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .profile-section {
            background: var(--glass);
            backdrop-filter: var(--blur);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            min-height: 400px;
        }
        
        .profile-section:hover {
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
        
        /* Forms */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
            display: block;
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
            transform: translateY(-2px);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-help {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        /* Toggle Switch */
        .toggle-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
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
        }
        
        .toggle-info p {
            color: #666;
            font-size: 0.9rem;
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
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 93, 26, 0.3);
            color: white;
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
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--accent);
            color: var(--accent);
        }
        
        .btn-outline:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-3px);
        }
        
        .btn-danger {
            background: transparent;
            border: 2px solid var(--danger);
            color: var(--danger);
        }
        
        .btn-danger:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-3px);
        }
        
        /* Message Styles */
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 500;
            text-align: center;
            animation: slideInDown 0.5s ease-out;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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
        
        /* Progress Bar */
        .profile-completion {
            margin-bottom: 30px;
        }
        
        .completion-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .completion-percentage {
            font-weight: 600;
            color: var(--primary);
        }
        
        .progress-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--gradient);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        /* Animations */
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
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                position: static;
            }
            
            .profile-nav {
                display: flex;
                overflow-x: auto;
                gap: 10px;
                padding-bottom: 10px;
            }
            
            .profile-nav-item {
                flex-shrink: 0;
                margin-bottom: 0;
            }
            
            .profile-nav-link {
                white-space: nowrap;
            }
        }
        
        @media (max-width: 768px) {
            .profile-page {
                padding: 100px 0 40px;
            }
            
            .page-header h2 {
                font-size: 2rem;
            }
            
            .profile-section {
                padding: 25px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header h3 {
                font-size: 1.5rem;
            }
        }
        
        /* Tab Content Management */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        /* Floating Elements */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .floating-element {
            position: absolute;
            border-radius: 50%;
            background: var(--gradient-light);
            opacity: 0.1;
            animation: float 15s infinite linear;
        }
        
        .floating-element:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            left: 80%;
            animation-delay: -5s;
        }
        
        .floating-element:nth-child(3) {
            width: 70px;
            height: 70px;
            top: 80%;
            left: 20%;
            animation-delay: -10s;
        }
        
        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(30px, -50px) rotate(120deg);
            }
            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
            100% {
                transform: translate(0, 0) rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <!-- Profile Page -->
    <section class="profile-page">
        <div class="profile-container">
            <div class="page-header animate-on-scroll">
                <h2>Profile Settings</h2>
                <p>Manage your account settings and preferences</p>
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

            <!-- Profile Completion -->
            <div class="profile-completion animate-on-scroll">
                <div class="completion-header">
                    <span>Profile Completion</span>
                    <span class="completion-percentage" id="completion-percentage">0%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
                </div>
            </div>

            <div class="profile-layout">
                <!-- Profile Sidebar -->
                <aside class="profile-sidebar animate-on-scroll">
                    <div class="profile-avatar">
                        <div class="avatar-container">
                            <?php if (!empty($user_info['avatar'])): ?>
                                <img src="../assets/images/avatars/<?php echo htmlspecialchars($user_info['avatar']); ?>" 
                                     alt="<?php echo htmlspecialchars($user_info['username']); ?>" 
                                     class="avatar-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-placeholder" style="display: none;">
                                    <?php echo strtoupper(substr($user_info['username'], 0, 1)); ?>
                                </div>
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?php echo strtoupper(substr($user_info['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <label class="avatar-upload-btn" for="avatar-upload">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($user_info['full_name'] ?: $user_info['username']); ?></h3>
                            <p><?php echo htmlspecialchars($user_info['email']); ?></p>
                            <?php if (!empty($user_info['location'])): ?>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user_info['location']); ?></p>
                            <?php endif; ?>
                            <div class="member-since">
                                Member since <?php echo date('F Y', strtotime($user_info['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Updated Navigation with proper spacing -->
                    <ul class="profile-nav">
                        <li class="profile-nav-item">
                            <a href="#personal-info" class="profile-nav-link active" data-tab="personal-info">
                                <i class="fas fa-user"></i>
                                <span>Personal Info</span>
                            </a>
                        </li>
                        <li class="profile-nav-item">
                            <a href="#farm-details" class="profile-nav-link" data-tab="farm-details">
                                <i class="fas fa-tractor"></i>
                                <span>Farm Details</span>
                            </a>
                        </li>
                        <li class="profile-nav-item">
                            <a href="#security" class="profile-nav-link" data-tab="security">
                                <i class="fas fa-shield-alt"></i>
                                <span>Security</span>
                            </a>
                        </li>
                        <li class="profile-nav-item">
                            <a href="#preferences" class="profile-nav-link" data-tab="preferences">
                                <i class="fas fa-cog"></i>
                                <span>Preferences</span>
                            </a>
                        </li>
                    </ul>
                </aside>

                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- Personal Information Section -->
                    <section id="personal-info" class="profile-section tab-content active animate-on-scroll">
                        <div class="section-header">
                            <h3><i class="fas fa-user"></i> Personal Information</h3>
                        </div>
                        
                        <form action="" method="POST" enctype="multipart/form-data" id="profile-form">
                            <input type="hidden" name="action" value="update_profile">
                            <input type="file" name="avatar" id="avatar-upload" accept="image/*" style="display: none;">
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="username">Username *</label>
                                    <input type="text" id="username" name="username" class="form-control" 
                                           value="<?php echo htmlspecialchars($user_info['username']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="full_name">Full Name</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user_info['full_name']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user_info['phone']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="location">Location</label>
                                    <input type="text" id="location" name="location" class="form-control" 
                                           value="<?php echo htmlspecialchars($user_info['location']); ?>">
                                    <div class="form-help">Your farm location or city</div>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label" for="bio">Bio</label>
                                    <textarea id="bio" name="bio" class="form-control" 
                                              placeholder="Tell us about yourself and your farming experience..."><?php echo htmlspecialchars($user_info['bio']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Update Personal Information
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- Farm Details Section -->
                    <section id="farm-details" class="profile-section tab-content animate-on-scroll">
                        <div class="section-header">
                            <h3><i class="fas fa-tractor"></i> Farm Details</h3>
                        </div>
                        
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="farm_size">Farm Size</label>
                                    <select id="farm_size" name="farm_size" class="form-control">
                                        <option value="">Select farm size</option>
                                        <option value="small" <?php echo $user_info['farm_size'] === 'small' ? 'selected' : ''; ?>>Small (1-10 acres)</option>
                                        <option value="medium" <?php echo $user_info['farm_size'] === 'medium' ? 'selected' : ''; ?>>Medium (11-50 acres)</option>
                                        <option value="large" <?php echo $user_info['farm_size'] === 'large' ? 'selected' : ''; ?>>Large (51+ acres)</option>
                                        <option value="commercial" <?php echo $user_info['farm_size'] === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="farm_type">Farm Type</label>
                                    <select id="farm_type" name="farm_type" class="form-control">
                                        <option value="">Select farm type</option>
                                        <option value="vegetable" <?php echo $user_info['farm_type'] === 'vegetable' ? 'selected' : ''; ?>>Vegetable Farm</option>
                                        <option value="fruit" <?php echo $user_info['farm_type'] === 'fruit' ? 'selected' : ''; ?>>Fruit Orchard</option>
                                        <option value="grain" <?php echo $user_info['farm_type'] === 'grain' ? 'selected' : ''; ?>>Grain Farm</option>
                                        <option value="livestock" <?php echo $user_info['farm_type'] === 'livestock' ? 'selected' : ''; ?>>Livestock Farm</option>
                                        <option value="mixed" <?php echo $user_info['farm_type'] === 'mixed' ? 'selected' : ''; ?>>Mixed Farming</option>
                                        <option value="greenhouse" <?php echo $user_info['farm_type'] === 'greenhouse' ? 'selected' : ''; ?>>Greenhouse</option>
                                        <option value="hobby" <?php echo $user_info['farm_type'] === 'hobby' ? 'selected' : ''; ?>>Hobby/Home Garden</option>
                                    </select>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label" for="website">Website</label>
                                    <input type="url" id="website" name="website" class="form-control" 
                                           value="<?php echo htmlspecialchars($user_info['website']); ?>" 
                                           placeholder="https://yourfarm.com">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="twitter">Twitter</label>
                                    <input type="text" id="twitter" name="twitter" class="form-control" 
                                           value="<?php echo htmlspecialchars($user_info['twitter']); ?>" 
                                           placeholder="@username">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="linkedin">LinkedIn</label>
                                    <input type="url" id="linkedin" name="linkedin" class="form-control" 
                                           value="<?php echo htmlspecialchars($user_info['linkedin']); ?>" 
                                           placeholder="https://linkedin.com/in/username">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Update Farm Details
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- Security Section -->
                    <section id="security" class="profile-section tab-content animate-on-scroll">
                        <div class="section-header">
                            <h3><i class="fas fa-shield-alt"></i> Security Settings</h3>
                        </div>
                        
                        <form action="" method="POST" id="password-form">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="current_password">Current Password *</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="new_password">New Password *</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8">
                                    <div class="form-help">Minimum 8 characters</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="confirm_password">Confirm New Password *</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                        
                        <!-- Danger Zone -->
                        <div class="danger-zone" style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee;">
                            <div class="section-header">
                                <h3 style="color: var(--danger);"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                            </div>
                            
                            <p style="color: #666; margin-bottom: 20px;">
                                Once you delete your account, there is no going back. Please be certain.
                            </p>
                            
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i>
                                Delete My Account
                            </button>
                        </div>
                    </section>

                    <!-- Preferences Section -->
                    <section id="preferences" class="profile-section tab-content animate-on-scroll">
                        <div class="section-header">
                            <h3><i class="fas fa-cog"></i> Preferences</h3>
                        </div>
                        
                        <form action="" method="POST" id="settings-form">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <h4 style="color: var(--primary); margin-bottom: 20px;">Notifications</h4>
                            
                            <div class="toggle-group">
                                <div class="toggle-info">
                                    <h5>Email Notifications</h5>
                                    <p>Receive email alerts for new diagnoses and updates</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="email_notifications" <?php echo $user_info['email_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-group">
                                <div class="toggle-info">
                                    <h5>SMS Notifications</h5>
                                    <p>Get text message alerts for urgent diagnoses</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="sms_notifications" <?php echo $user_info['sms_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="form-grid" style="margin-top: 30px;">
                                <div class="form-group">
                                    <label class="form-label" for="language">Language</label>
                                    <select id="language" name="language" class="form-control">
                                        <option value="en" <?php echo $user_info['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="es" <?php echo $user_info['language'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                                        <option value="fr" <?php echo $user_info['language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="theme">Theme</label>
                                    <select id="theme" name="theme" class="form-control">
                                        <option value="light" <?php echo $user_info['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                                        <option value="dark" <?php echo $user_info['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                        <option value="auto" <?php echo $user_info['theme'] === 'auto' ? 'selected' : ''; ?>>Auto</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top: 30px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Save Preferences
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Tab navigation
        document.querySelectorAll('.profile-nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links and tabs
                document.querySelectorAll('.profile-nav-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Show corresponding tab
                const targetTab = this.getAttribute('data-tab');
                document.getElementById(targetTab).classList.add('active');
                
                // Update URL hash
                window.location.hash = targetTab;
            });
        });

        // Check URL hash on page load
        window.addEventListener('load', function() {
            if (window.location.hash) {
                const targetTab = window.location.hash.substring(1);
                const tabLink = document.querySelector(`.profile-nav-link[data-tab="${targetTab}"]`);
                if (tabLink) {
                    tabLink.click();
                }
            }
            
            // Initialize with first tab active
            document.querySelector('.profile-nav-link.active').click();
        });

        // Avatar upload preview
        const avatarUpload = document.getElementById('avatar-upload');
        const avatarImage = document.querySelector('.avatar-image');
        const avatarPlaceholder = document.querySelector('.avatar-placeholder');
        
        if (avatarUpload) {
            avatarUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (avatarImage) {
                            avatarImage.src = e.target.result;
                            avatarImage.style.display = 'block';
                            if (avatarPlaceholder) {
                                avatarPlaceholder.style.display = 'none';
                            }
                        } else if (avatarPlaceholder) {
                            avatarPlaceholder.style.display = 'flex';
                        }
                    };
                    reader.readAsDataURL(file);
                    
                    // Auto-submit the form when avatar is selected
                    setTimeout(() => {
                        document.getElementById('profile-form').submit();
                    }, 500);
                }
            });
        }

        // Password confirmation validation
        const passwordForm = document.getElementById('password-form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match. Please try again.');
                    return false;
                }
                
                if (newPassword.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long.');
                    return false;
                }
            });
        }

        // Account deletion confirmation
        function confirmDelete() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently lost.')) {
                window.location.href = '../api/delete-account.php';
            }
        }

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

        // Calculate profile completion percentage
        function calculateProfileCompletion() {
            const fields = [
                'username', 'email', 'full_name', 'phone', 'location', 
                'bio', 'farm_size', 'farm_type'
            ];
            
            let completed = 0;
            
            fields.forEach(field => {
                const element = document.getElementById(field);
                if (element) {
                    const value = element.value;
                    if (value && value.trim() !== '') {
                        completed++;
                    }
                }
            });
            
            const percentage = Math.round((completed / fields.length) * 100);
            const percentageElement = document.getElementById('completion-percentage');
            const progressFill = document.getElementById('progress-fill');
            
            if (percentageElement && progressFill) {
                percentageElement.textContent = `${percentage}%`;
                progressFill.style.width = `${percentage}%`;
            }
        }

        // Initialize profile completion calculation
        document.addEventListener('DOMContentLoaded', function() {
            calculateProfileCompletion();
            
            // Update completion when form fields change
            document.querySelectorAll('.form-control').forEach(field => {
                field.addEventListener('input', calculateProfileCompletion);
            });
        });

        // Initialize animations
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>
