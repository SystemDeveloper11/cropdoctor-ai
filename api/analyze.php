this file is working better i want to style it nicely when the ai responces let the response be styled nicely, title, content, also recomment a medication for the crop also a nice tagline maybe "as your cropdoctor ai we are looking forward to help you, to get the dose kindly reach out to our agrovet known as cropdoctor agrovet we deliver" hope you got me right?  <?php
// Start session and include files at the very top
session_start();
// Include config early for BASE_URL and GEMINI_API_KEY definitions
require_once __DIR__ . '/../config/config.php';

// =================================================================
// 🚨 CRITICAL TIMEOUT PREVENTION 🚨
// This tells PHP to run indefinitely, ignoring the user aborting the request.
// The user will still see a timeout message in the browser if the analysis is too long,
// but the script will complete and save the result. The next step would be
// to implement an asynchronous/job queue system for true long-running tasks.
// =================================================================
set_time_limit(0); // Set max execution time to infinite
ini_set('max_execution_time', 0); // Also set via ini_set for robustness
ignore_user_abort(true); // Continue running script even if user disconnects

// Set the response headers to prevent browser caching and start flushing
header('Connection: close');
header('Content-Encoding: none');
// Start output buffering (if not already started) and ensure we send headers immediately
if (ob_get_level() == 0) ob_start();
echo "Processing image, please wait..."; // Send a small response to the client
ob_flush();
flush(); // Send all buffered content to the client
// This helps keep the connection alive (for a short time) and lets the script run in the background.

// ULTIMATE DEBUG MODE
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Log start with detailed info
error_log("🚀 ========== ANALYZE.PHP STARTED (TIMEOUTS DISABLED) ========== " . date('Y-m-d H:i:s'));
error_log("📝 PHP Version: " . PHP_VERSION);
ini_set('memory_limit', '512M');
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
error_log("📝 Memory Limit: " . ini_get('memory_limit'));

// =================================================================
// GEMINI ANALYSIS PROMPT (Equivalent to Python's GEMINI_PROMPT)
// =================================================================
const GEMINI_PROMPT = "Analyze this plant image for a Kenyan farmer. Identify the plant type, what part is shown, and describe what you see. "
    . "Look for any signs of disease, pests, or damage. Respond exactly in this format:\n\n"
    . "Plant Type: [name the specific plant/crop you see]\n"
    . "Plant Part: [leaf/stem/fruit/flower/root/whole plant]\n"
    . "Health Status: [Healthy or Unhealthy]\n"
    . "Visual Observations: [describe colors, spots, damage, or other details you see]\n"
    . "Preliminary Diagnosis: [if unhealthy, suggest what might be wrong, or 'None' if healthy]\n"
    . "Issue: [disease/pest or 'Healthy']\n"
    . "Confidence: [percentage]\n"
    . "Symptoms: [brief description]\n"
    . "Cause: [root cause]\n"
    . "Remedies: [3-4 practical solutions]\n"
    . "Prevention: [preventive measures]\n"
    . "Urgency: [Low/Medium/High]\n";
// --- END GLOBAL PROMPT DEFINITION ---

// Variables to track state for error handling
$user_id = null;
$image_name = null;

try {
    // Check if required files exist
    $required_files = [
        __DIR__ . '/../includes/auth.php'
    ];

    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            throw new Exception("Missing required file: " . $file);
        }
    }

    error_log("✅ config.php loaded successfully (via pre-load)");

    require_once __DIR__ . '/../includes/auth.php';
    error_log("✅ auth.php loaded successfully");

    // Check database connection
    if (!isset($conn)) {
        throw new Exception("Database connection not found. Check if connection script was included/executed.");
    }
    error_log("✅ Database connection active");

    // Check if user is logged in
    requireLogin();
    $user_id = $_SESSION['user_id'];
    error_log("✅ User authenticated: " . $user_id);

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method. Expected POST, got: " . $_SERVER['REQUEST_METHOD']);
    }

    // Check if file was uploaded
    if (!isset($_FILES['plant_image'])) {
        throw new Exception("No file uploaded with name 'plant_image'");
    }

    $image_file = $_FILES['plant_image'];

    error_log("📁 File upload details:");
    error_log("    - Name: " . $image_file['name']);
    error_log("    - Size: " . $image_file['size']);
    error_log("    - Temp Name: " . $image_file['tmp_name']);
    error_log("    - Error: " . $image_file['error']);

    // Validate upload error
    if ($image_file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File is too large (server limit exceeded)',
            UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit exceeded)',
            UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
            UPLOAD_ERR_NO_FILE => 'No file was selected',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension stopped the file upload',
        ];
        throw new Exception($error_messages[$image_file['error']] ?? 'Unknown upload error');
    }

    // Validate file exists in temp location
    if (!file_exists($image_file['tmp_name']) || !is_uploaded_file($image_file['tmp_name'])) {
        throw new Exception('Uploaded file is missing or invalid');
    }

    // Validate file type securely
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!function_exists('mime_content_type')) {
        throw new Exception('PHP Fileinfo extension is missing, cannot validate file type securely.');
    }
    
    $file_type = mime_content_type($image_file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Invalid file type: ' . $file_type . '. Please upload JPEG or PNG images only');
    }

    // Validate file size (5MB max)
    if ($image_file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size must be less than 5MB. Current size: ' . round($image_file['size'] / 1024 / 1024, 2) . 'MB');
    }

    // Create upload directory
    $upload_dir = __DIR__ . '/../assets/images/uploads/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('Could not create upload directory: ' . $upload_dir);
        }
        error_log("✅ Created upload directory: " . $upload_dir);
    }

    // Generate unique filename - FIXED SECTION
    $path_parts = pathinfo($image_file['name']);
    $extension = isset($path_parts['extension']) ? '.' . strtolower($path_parts['extension']) : '.jpg';
    
    // Ensure we have a valid extension
    if (empty($extension) || $extension === '.') {
        $extension = '.jpg'; // Default extension
    }
    
    $image_name = uniqid('plant_') . $extension;
    $image_path_on_server = $upload_dir . $image_name;

    error_log("📸 Generated filename: " . $image_name);
    error_log("📁 Full server path: " . $image_path_on_server);

    // Move uploaded file with verification
    if (!move_uploaded_file($image_file['tmp_name'], $image_path_on_server)) {
        throw new Exception('Failed to move uploaded file to: ' . $image_path_on_server);
    }

    // Verify the file was actually moved
    if (!file_exists($image_path_on_server)) {
        throw new Exception('File was not successfully saved to: ' . $image_path_on_server);
    }

    error_log("✅ File uploaded successfully: " . $image_name);
    error_log("✅ File verified at: " . $image_path_on_server);
    error_log("✅ File size after move: " . filesize($image_path_on_server));

    // Check if Gemini API key is configured
    if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY) || GEMINI_API_KEY === 'your-gemini-api-key-here') {
        throw new Exception('Gemini API key is not properly configured. Check config.php.');
    }

    // =================================================================
    // GEMINI ANALYSIS FUNCTIONS (PHP equivalent of Python's functions)
    // =================================================================
    
    /**
     * Executes a single call to the Gemini API.
     */
    function callGeminiWithImageSingle($image_path, $mime_type) {
        // Access the global constant prompt
        $prompt = GEMINI_PROMPT; 
        
        error_log("🔍 Starting Gemini API single call...");
        
        // Read and encode image
        $image_data = file_get_contents($image_path);
        if ($image_data === false) {
            throw new Exception('Could not read image data from path: ' . $image_path);
        }
        
        $base64_image = base64_encode($image_data);
        
        // Model name for multimodal requests
        $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY;
        
        // Post data for the API call
        $post_data = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mime_type, // Use the detected mime type
                                'data' => $base64_image // Pass Base64 data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4, 
                'maxOutputTokens' => 1024, // High token limit for structured output
            ]
        ]);
        
        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true, 
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 90, // Increased timeout for the API call itself
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            throw new Exception('CURL error: ' . $curl_error);
        }
        
        if ($http_code !== 200) {
            error_log("🚨 Gemini API failed! HTTP Code: {$http_code}, Response: {$response}");
            throw new Exception('Gemini API HTTP error: ' . $http_code . ' - Response: ' . $response);
        }
        
        $api_result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        $candidate = $api_result['candidates'][0] ?? null;

        if ($candidate) {
            if (($candidate['finishReason'] ?? '') === 'SAFETY') {
                throw new Exception("Gemini analysis blocked: The content was filtered by the API.");
            }
            
            if (($candidate['finishReason'] ?? '') === 'MAX_TOKENS') {
                // Triggers the retry loop below
                throw new Exception('Gemini output exceeded max tokens, retrying for full structured data.');
            }

            $gemini_response = $candidate['content']['parts'][0]['text'] ?? null;
            
            if (!empty($gemini_response)) {
                error_log("✅ Gemini API response received");
                return $gemini_response;
            }
        }
        
        // Fallback for empty text (this is what triggers the retry)
        throw new Exception('No response text from Gemini. The API returned an empty response.');
    }
    
    /**
     * Attempts to call the Gemini API, with a retry mechanism.
     */
    function callGeminiWithImageRetry($image_path, $mime_type, $max_retries = 3) {
        $delay = 1;
        for ($i = 0; $i < $max_retries; $i++) {
            try {
                return callGeminiWithImageSingle($image_path, $mime_type);
            } catch (Exception $e) {
                if ($i < $max_retries - 1 && strpos($e->getMessage(), 'blocked') === false) {
                    error_log("⚠️ Gemini API Call failed (Attempt " . ($i + 1) . " of " . $max_retries . "). Retrying in " . $delay . "s... Error: " . $e->getMessage());
                    sleep($delay);
                    $delay *= 2;    
                } else {
                    throw $e;
                }
            }
        }
    }

    // Perform analysis using the retry function
    error_log("🎯 Starting analysis...");
    $gemini_response = callGeminiWithImageRetry($image_path_on_server, $file_type);
    error_log("📄 Gemini Response: \n" . $gemini_response);

    // =================================================================
    // ENHANCED PARSING (Robust multi-line extraction)
    // =================================================================
    
    /**
     * Safely extract a field using multiline regex, capturing text until the next field header or end of response.
     */
    function extract_field_robust(string $response, string $field_name): string {
        // Regex pattern: Match the field name, optional brackets, then capture everything (including newlines, 's' flag) 
        // non-greedily, until the start of the next field name or the end of the string.
        // It looks for a field name starting with a Capital letter followed by a non-space character (e.g., 'Plant Type:', 'Remedies:')
        $pattern = "/^{$field_name}:\s*\[?(.+?)(?=\n[A-Z][a-z]+ [A-Z]|\n[A-Z][a-z]+:|\n[A-Z]+:|$)/ims";
        
        if (preg_match($pattern, $response, $matches)) {
            // The captured text (group 1) needs cleaning (trimming whitespace and newlines)
            $value = trim($matches[1]);
            
            // Remove brackets if they exist
            $value = preg_replace('/^\[(.*)\]$/s', '$1', $value);
            
            return trim($value);
        }
        return "Unknown/Missing";
    }

    $plant_type = extract_field_robust($gemini_response, 'Plant Type');
    $plant_part = extract_field_robust($gemini_response, 'Plant Part');
    $health_status = extract_field_robust($gemini_response, 'Health Status');
    $visual_observations = extract_field_robust($gemini_response, 'Visual Observations');
    $preliminary_diagnosis = extract_field_robust($gemini_response, 'Preliminary Diagnosis');
    $detected_disease = extract_field_robust($gemini_response, 'Issue');
    $confidence_raw = extract_field_robust($gemini_response, 'Confidence');
    $symptoms = extract_field_robust($gemini_response, 'Symptoms');
    $cause = extract_field_robust($gemini_response, 'Cause');
    $remedies = extract_field_robust($gemini_response, 'Remedies');
    $prevention = extract_field_robust($gemini_response, 'Prevention');
    $urgency = extract_field_robust($gemini_response, 'Urgency');

    // Parse Confidence to a numeric ratio (0.0 to 1.0)
    $confidence = floatval(rtrim($confidence_raw, '%')) / 100;
    $confidence = max(0.0, min(1.0, $confidence)); // Ensure it's between 0 and 1

    // Fallback/Cleanup for disease name (using 'Issue' field for DB 'disease_name' column)
    if (empty($detected_disease) || strtolower($detected_disease) === 'healthy' || strtolower($detected_disease) === 'none' || strtolower($detected_disease) === 'unknown/missing') {
        $detected_disease = 'Healthy/Undetermined';
    }

    // Construct the suggestions/raw output for saving (enhanced summary)
    $treatment_suggestions = "### 🌿 AI Diagnosis for Kenyan Farmer 🇰🇪\n\n"
        . "**Plant Type:** {$plant_type}\n"
        . "**Health Status:** {$health_status}\n"
        . "**Primary Issue:** **{$detected_disease}**\n"
        . "**Confidence:** " . round($confidence * 100) . "%\n"
        . "**Urgency:** {$urgency}\n\n"
        . "#### Detailed Analysis\n"
        . "* **Symptoms:** {$symptoms}\n"
        . "* **Root Cause:** {$cause}\n"
        . "* **Remedies:** {$remedies}\n"
        . "* **Prevention:** {$prevention}\n\n"
        . "---\n\n"
        . "#### Raw Output Details\n"
        . "Plant Part: {$plant_part}\n"
        . "Visual Observations: {$visual_observations}\n"
        . "Preliminary Diagnosis: {$preliminary_diagnosis}\n";

    // =================================================================
    // SAVE TO DATABASE - WITH VALIDATION
    // =================================================================
    error_log("💾 Saving to database...");
    
    // Validate critical variables before database insertion
    if (empty($image_name)) {
        throw new Exception('Image name is empty before database insertion');
    }
    
    if (empty($user_id)) {
        throw new Exception('User ID is empty before database insertion');
    }
    
    error_log("🔍 Database insertion values:");
    error_log("    - user_id: " . $user_id);
    error_log("    - image_name: " . $image_name);
    error_log("    - detected_disease: " . $detected_disease);
    error_log("    - confidence: " . $confidence);
    
    $sql = "INSERT INTO diagnoses (user_id, image_path, disease_name, confidence, suggestions) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $confidence_to_bind = strval($confidence);
    
    // Bind all five parameters (i = integer, s = string)
    $stmt->bind_param("issss", $user_id, $image_name, $detected_disease, $confidence_to_bind, $treatment_suggestions);
    
    if (!$stmt->execute()) {
        throw new Exception('Database execute failed: ' . $stmt->error);
    }
    
    $diagnosis_id = $conn->insert_id;
    $stmt->close();
    
    error_log("✅ Diagnosis saved with ID: " . $diagnosis_id);

    // Redirect to results
    $redirect_url = BASE_URL . "pages/result.php?id=" . $diagnosis_id;
    error_log("🔄 Redirecting to: " . $redirect_url);
    
    // Final check to see if headers have been sent by the flush() earlier.
    if (headers_sent()) {
        echo "<script>window.location.href='" . $redirect_url . "';</script>";
    } else {
        header("Location: " . $redirect_url);
    }
    exit;

} catch (Exception $e) {
    error_log("❌ CRITICAL ERROR: " . $e->getMessage());
    error_log("🔄 Stack trace: " . $e->getTraceAsString());
    
    // This section is vital: it logs the error to the DB and redirects to the result page.
    if (isset($conn) && $user_id !== null) {
        try {
            $sql = "INSERT INTO diagnoses (user_id, image_path, disease_name, confidence, suggestions) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                
                // Use a default image name if $image_name is still null
                $image_name_error = !empty($image_name) ? $image_name : 'error_' . uniqid() . '.jpg';
                $disease_name_error = "Analysis Failed";
                $zero_confidence = "0.0";
                $error_msg = "❌ Analysis Failed: " . $e->getMessage() . "\n\nThis result was not successfully processed. Please try again or contact support.";
                
                $stmt->bind_param("issss", $user_id, $image_name_error, $disease_name_error, $zero_confidence, $error_msg); 
                
                $stmt->execute();
                $diagnosis_id = $conn->insert_id;
                $stmt->close();
                
                // Redirect to the newly created error result page
                $redirect_url = BASE_URL . "pages/result.php?id=" . $diagnosis_id;
                error_log("🔄 Redirecting to error result page: " . $redirect_url);

                if (headers_sent()) {
                    echo "<script>window.location.href='" . $redirect_url . "';</script>";
                } else {
                    header("Location: " . $redirect_url);
                }
                exit;
            }
        } catch (Exception $db_error) {
            error_log("❌ Could not save error to database: " . $db_error->getMessage());
        }
    }
    
    // Final fallback: Use the defined BASE_URL, but keep the user on a non-home page error.
    if (defined('BASE_URL')) {
        $generic_error_url = BASE_URL . "pages/generic_error.php?error=" . urlencode("The analysis timed out or failed. Please check the logs for details.");
        error_log("🔄 Redirecting to generic error page: " . $generic_error_url);
        
        // This is the old fallback, now replaced by the better one above
        // header("Location: " . BASE_URL . "index.php?error=" . urlencode("An unexpected error occurred: " . $e->getMessage()));

        if (headers_sent()) {
            echo "<script>window.location.href='" . $generic_error_url . "';</script>";
        } else {
            header("Location: " . $generic_error_url);
        }
    } else {
        http_response_code(500);
        die("Critical Error: Analysis failed and BASE_URL is not defined. Check PHP logs.");
    }
    exit;
}

error_log("✅ ========== ANALYZE.PHP COMPLETED SUCCESSFULLY ==========");
?>