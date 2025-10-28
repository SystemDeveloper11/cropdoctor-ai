<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy(); 

// Redirect to the login page
header("Location: login.php");
exit;
?>
