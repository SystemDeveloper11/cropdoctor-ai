<?php
// Start a session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if the logged-in user is an admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Function to redirect users if they are not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "pages/login.php");
        exit;
    }
}

// Function to redirect non-admin users
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: " . BASE_URL . "pages/dashboard.php");
        exit;
    }
}

// Hash a password before storing it
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify a hashed password
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}
?>