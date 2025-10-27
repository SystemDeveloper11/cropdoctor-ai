<?php
// ===============================
// DATABASE CONFIGURATION
// ===============================
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '41730');
define('DB_NAME', 'agritech');

// ===============================
// KINDWISE API 
// ===============================
// Base URL for Plant.id V3 API
define('PLANT_ID_API_BASE_URL', 'https://plant.id/api/v3/');
//Plant.id
define('PLANT_ID_API_KEY', 'h86mRXWExrEIg0nRMz7NzYLmBIT5HGaTk4Pb3IvDpEw1e64bQs');

// ===============================
// EMAIL CONFIGURATION
// ===============================
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'remotaskfreelancer@gmail.com');
define('MAIL_PASSWORD', 'ystm bmgr nvsd xytv');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');



// ===============================
// FILE UPLOAD SETTINGS
// ===============================
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('UPLOAD_DIR', 'assets/images/uploads/');

// ===============================
// DATABASE CONNECTION
// ===============================
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn === false) {
    die("ERROR: Could not connect to the database. " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>