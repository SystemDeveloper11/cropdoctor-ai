<?php
// app.php - Main application file

require_once __DIR__ . '/../config/config.php';



session_start();

class CropDoctorApp {
    private $config;
    private $mongo;
    private $db;
    private $users;
    private $diagnoses;
    private $subscriptions;
    private $httpClient;
    
    public function __construct() {
        $this->config = new Config();
        $this->mongo = new MongoClient($this->config->MONGODB_URI);
        $this->db = $this->mongo->selectDatabase($this->config->DB_NAME);
        $this->users = $this->db->users;
        $this->diagnoses = $this->db->diagnoses;
        $this->subscriptions = $this->db->subscriptions;
        $this->httpClient = new Client();
        
        $this->setupUploads();
        $this->createAdminUser();
    }
    
    private function setupUploads() {
        if (!file_exists($this->config->UPLOAD_FOLDER)) {
            mkdir($this->config->UPLOAD_FOLDER, 0755, true);
        }
    }
    
    private function createAdminUser() {
        $admin = $this->users->findOne(['email' => $this->config->ADMIN_EMAIL]);
        if (!$admin) {
            $this->users->insertOne([
                'email' => $this->config->ADMIN_EMAIL,
                'password_hash' => password_hash($this->config->ADMIN_PASSWORD, PASSWORD_DEFAULT),
                'is_admin' => true,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);
        }
    }
    
    public function run() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        
        $this->setSecurityHeaders();
        
        // Route mapping
        $routes = [
            'GET' => [
                '/' => 'landing',
                '/dashboard' => 'dashboard',
                '/result/{id}' => 'result',
                '/history' => 'history',
                '/diagnosis/{id}/pdf' => 'diagnosisPdf',
                '/login' => 'login',
                '/register' => 'register',
                '/logout' => 'logout',
                '/admin' => 'adminDashboard',
                '/admin/users' => 'adminUsers',
                '/image/{id}' => 'serveImage',
                '/health' => 'healthCheck',
                '/sitemap.xml' => 'sitemap',
                '/robots.txt' => 'robots'
            ],
            'POST' => [
                '/dashboard' => 'dashboardPost',
                '/login' => 'loginPost',
                '/register' => 'registerPost',
                '/admin/users/{id}/subscription' => 'updateUserSubscription',
                '/admin/users/{id}/delete' => 'deleteUser'
            ]
        ];
        
        $this->route($routes, $path, $method);
    }
    
    private function route($routes, $path, $method) {
        if (isset($routes[$method][$path])) {
            $handler = $routes[$method][$path];
            $this->$handler();
            return;
        }
        
        // Handle dynamic routes
        foreach ($routes[$method] as $route => $handler) {
            if (preg_match('/\{[^}]+\}/', $route)) {
                $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
                if (preg_match("#^$pattern$#", $path, $matches)) {
                    array_shift($matches);
                    $this->$handler(...$matches);
                    return;
                }
            }
        }
        
        $this->notFound();
    }
    
    private function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    private function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    private function isAdmin() {
        if (!$this->isAuthenticated()) return false;
        $user = $this->users->findOne(['_id' => new ObjectId($_SESSION['user_id'])]);
        return $user && $user['is_admin'] ?? false;
    }
    
    private function requireAuth() {
        if (!$this->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
    
    private function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->render('error', ['message' => 'Forbidden']);
            exit;
        }
    }
    
    // Landing page
    private function landing() {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        $this->render('landing');
    }
    
    // Dashboard - GET
    private function dashboard() {
        $this->requireAuth();
        
        $subscription = $this->subscriptions->findOne([
            'user_id' => new ObjectId($_SESSION['user_id'])
        ]);
        
        $this->render('dashboard', ['subscription' => $subscription]);
    }
    
    // Dashboard - POST (file upload and analysis)
    private function dashboardPost() {
        $this->requireAuth();
        
        $subscription = $this->subscriptions->findOne([
            'user_id' => new ObjectId($_SESSION['user_id'])
        ]);
        
        // Check subscription limit
        if ($subscription && 
            ($subscription['diagnoses_used'] ?? 0) >= ($subscription['diagnoses_limit'] ?? 10)) {
            $this->flash('You have reached your diagnosis limit. Please upgrade your plan.', 'error');
            header('Location: /dashboard');
            exit;
        }
        
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('No file uploaded or upload error.', 'error');
            header('Location: /dashboard');
            exit;
        }
        
        $file = $_FILES['image'];
        $plantPart = $_POST['plant_part'] ?? null;
        
        if (!$this->allowedFile($file['name'])) {
            $this->flash('Invalid file type. Please upload a JPG or PNG image.', 'error');
            header('Location: /dashboard');
            exit;
        }
        
        // Process upload
        $filename = $this->secureFilename($file['name']);
        $filepath = $this->config->UPLOAD_FOLDER . '/' . uniqid() . '_' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->flash('File upload failed.', 'error');
            header('Location: /dashboard');
            exit;
        }
        
        try {
            $analysisResult = $this->performAnalysis($filepath);
            $finalPlantPart = $plantPart ?: $analysisResult['plant_part'] ?? 'Unknown';
            
            // Store in database
            $diagnosis = [
                'user_id' => new ObjectId($_SESSION['user_id']),
                'image_path' => $filepath,
                'plant_type' => $analysisResult['plant_type'] ?? 'Unknown',
                'crop_type' => $analysisResult['plant_type'] ?? 'Unknown',
                'plant_part' => $finalPlantPart,
                'issue' => $analysisResult['issue'] ?? 'Unknown',
                'confidence' => $analysisResult['confidence'] ?? '-',
                'symptoms' => $analysisResult['symptoms'] ?? '-',
                'cause' => $analysisResult['cause'] ?? '-',
                'remedies' => $analysisResult['remedies'] ?? '-',
                'prevention' => $analysisResult['prevention'] ?? '-',
                'urgency' => $analysisResult['urgency'] ?? 'Medium',
                'visual_observations' => $analysisResult['visual_observations'] ?? 'No observations',
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ];
            
            $result = $this->diagnoses->insertOne($diagnosis);
            
            // Update subscription usage
            if ($subscription) {
                $this->subscriptions->updateOne(
                    ['user_id' => new ObjectId($_SESSION['user_id'])],
                    ['$inc' => ['diagnoses_used' => 1]]
                );
            }
            
            header('Location: /result/' . $result->getInsertedId());
            exit;
            
        } catch (Exception $e) {
            $this->flash('Diagnosis failed: ' . $e->getMessage(), 'error');
            header('Location: /dashboard');
            exit;
        }
    }
    
    private function result($diagnosisId) {
        $this->requireAuth();
        
        try {
            $diagnosis = $this->diagnoses->findOne([
                '_id' => new ObjectId($diagnosisId),
                'user_id' => new ObjectId($_SESSION['user_id'])
            ]);
        } catch (Exception $e) {
            $diagnosis = null;
        }
        
        if (!$diagnosis) {
            $this->flash('Diagnosis not found.', 'error');
            header('Location: /dashboard');
            exit;
        }
        
        $remediesList = array_filter(
            preg_split("/[\n\r]+/", $diagnosis['remedies'] ?? ''),
            function($item) { return trim($item) !== ''; }
        );
        
        $this->render('result', [
            'diag' => $diagnosis,
            'remediesList' => $remediesList
        ]);
    }
    
    private function history() {
        $this->requireAuth();
        
        $entries = $this->diagnoses->find([
            'user_id' => new ObjectId($_SESSION['user_id'])
        ], [
            'sort' => ['created_at' => -1],
            'limit' => 100
        ])->toArray();
        
        $this->render('history', ['entries' => $entries]);
    }
    
    private function diagnosisPdf($diagnosisId) {
        $this->requireAuth();
        
        try {
            $diagnosis = $this->diagnoses->findOne([
                '_id' => new ObjectId($diagnosisId),
                'user_id' => new ObjectId($_SESSION['user_id'])
            ]);
        } catch (Exception $e) {
            $diagnosis = null;
        }
        
        if (!$diagnosis) {
            $this->flash('Diagnosis not found.', 'error');
            header('Location: /dashboard');
            exit;
        }
        
        $this->generatePdf($diagnosis);
    }
    
    private function generatePdf($diagnosis) {
        // Simple PDF generation - you might want to use a proper PDF library
        $pdfContent = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                h1 { color: #2c5aa0; }
                .field { margin: 10px 0; }
                .label { font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>Crop Doctor Diagnosis</h1>
            
            <div class='field'>
                <span class='label'>Date:</span> " . 
                date('Y-m-d H:i', $diagnosis['created_at']->toDateTime()->getTimestamp()) . "
            </div>
            
            <div class='field'>
                <span class='label'>Plant Type:</span> " . 
                htmlspecialchars($diagnosis['plant_type'] ?? $diagnosis['crop_type'] ?? '-') . "
            </div>
            
            <div class='field'>
                <span class='label'>Plant Part:</span> " . 
                htmlspecialchars($diagnosis['plant_part'] ?? '-') . "
            </div>
            
            <div class='field'>
                <span class='label'>Issue:</span> " . 
                htmlspecialchars($diagnosis['issue'] ?? '-') . "
            </div>
            
            <div class='field'>
                <span class='label'>Confidence:</span> " . 
                htmlspecialchars($diagnosis['confidence'] ?? '-') . "
            </div>
            
            <div class='field'>
                <span class='label'>Symptoms:</span><br>" . 
                nl2br(htmlspecialchars($diagnosis['symptoms'] ?? '-')) . "
            </div>
            
            <div class='field'>
                <span class='label'>Cause:</span><br>" . 
                nl2br(htmlspecialchars($diagnosis['cause'] ?? '-')) . "
            </div>
            
            <div class='field'>
                <span class='label'>Remedies:</span><br>" . 
                nl2br(htmlspecialchars($diagnosis['remedies'] ?? '-')) . "
            </div>
            
            <div class='field'>
                <span class='label'>Prevention:</span><br>" . 
                nl2br(htmlspecialchars($diagnosis['prevention'] ?? '-')) . "
            </div>
            
            <div class='field'>
                <span class='label'>Urgency:</span> " . 
                htmlspecialchars($diagnosis['urgency'] ?? '-') . "
            </div>
        </body>
        </html>
        ";
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="diagnosis_' . $diagnosisId . '.pdf"');
        
        // For a real implementation, use Dompdf or TCPDF
        echo $pdfContent;
        exit;
    }
    
    private function login() {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        $this->render('login');
    }
    
    private function loginPost() {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        
        $user = $this->users->findOne(['email' => $email]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (string)$user['_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'] ?? false;
            
            $next = $_GET['next'] ?? '/dashboard';
            header('Location: ' . $next);
            exit;
        }
        
        $this->flash('Invalid credentials', 'error');
        header('Location: /login');
        exit;
    }
    
    private function register() {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        $this->render('register');
    }
    
    private function registerPost() {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $this->flash('Email and password are required.', 'error');
            header('Location: /register');
            exit;
        }
        
        $existing = $this->users->findOne(['email' => $email]);
        if ($existing) {
            $this->flash('Email already registered.', 'error');
            header('Location: /register');
            exit;
        }
        
        $userDoc = [
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_admin' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $this->users->insertOne($userDoc);
        
        // Create default subscription
        $subscriptionDoc = [
            'user_id' => $result->getInsertedId(),
            'plan' => 'free',
            'status' => 'active',
            'diagnoses_used' => 0,
            'diagnoses_limit' => 10,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'expires_at' => null
        ];
        
        $this->subscriptions->insertOne($subscriptionDoc);
        
        $this->flash('Account created. Please log in.', 'success');
        header('Location: /login');
        exit;
    }
    
    private function logout() {
        $this->requireAuth();
        session_destroy();
        header('Location: /login');
        exit;
    }
    
    // Admin routes
    private function adminDashboard() {
        $this->requireAdmin();
        
        $totalUsers = $this->users->countDocuments();
        $totalDiagnoses = $this->diagnoses->countDocuments();
        $activeSubscriptions = $this->subscriptions->countDocuments(['status' => 'active']);
        
        $recentUsers = $this->users->find([], [
            'sort' => ['created_at' => -1],
            'limit' => 10
        ])->toArray();
        
        $subscriptionStats = $this->subscriptions->aggregate([
            ['$group' => ['_id' => '$plan', 'count' => ['$sum' => 1]]]
        ])->toArray();
        
        $this->render('admin/dashboard', [
            'totalUsers' => $totalUsers,
            'totalDiagnoses' => $totalDiagnoses,
            'activeSubscriptions' => $activeSubscriptions,
            'recentUsers' => $recentUsers,
            'subscriptionStats' => $subscriptionStats
        ]);
    }
    
    private function adminUsers() {
        $this->requireAdmin();
        
        $users = $this->users->find([], ['sort' => ['created_at' => -1]])->toArray();
        
        foreach ($users as &$user) {
            $user['subscription'] = $this->subscriptions->findOne([
                'user_id' => $user['_id']
            ]);
        }
        
        $this->render('admin/users', ['users' => $users]);
    }
    
    private function updateUserSubscription($userId) {
        $this->requireAdmin();
        
        try {
            $plan = $_POST['plan'] ?? 'free';
            $status = $_POST['status'] ?? 'active';
            $diagnosesLimit = intval($_POST['diagnoses_limit'] ?? 10);
            
            $this->subscriptions->updateOne(
                ['user_id' => new ObjectId($userId)],
                ['$set' => [
                    'plan' => $plan,
                    'status' => $status,
                    'diagnoses_limit' => $diagnosesLimit,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]],
                ['upsert' => true]
            );
            
            $this->flash('Subscription updated successfully', 'success');
        } catch (Exception $e) {
            $this->flash('Error updating subscription: ' . $e->getMessage(), 'error');
        }
        
        header('Location: /admin/users');
        exit;
    }
    
    private function deleteUser($userId) {
        $this->requireAdmin();
        
        try {
            $this->users->deleteOne(['_id' => new ObjectId($userId)]);
            $this->diagnoses->deleteMany(['user_id' => new ObjectId($userId)]);
            $this->subscriptions->deleteMany(['user_id' => new ObjectId($userId)]);
            
            $this->flash('User deleted successfully', 'success');
        } catch (Exception $e) {
            $this->flash('Error deleting user: ' . $e->getMessage(), 'error');
        }
        
        header('Location: /admin/users');
        exit;
    }
    
    // Utility methods
    private function allowedFile($filename) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowed);
    }
    
    private function secureFilename($filename) {
        return preg_replace("/[^a-zA-Z0-9\._-]/", "_", $filename);
    }
    
    private function performAnalysis($imagePath) {
        // Gemini AI analysis implementation
        $prompt = "Analyze this plant image for a Kenyan farmer. Identify the plant type, what part is shown, and describe what you see. 
        Look for any signs of disease, pests, or damage. Respond exactly in this format:

        Plant Type: [name the specific plant/crop you see]
        Plant Part: [leaf/stem/fruit/flower/root/whole plant]
        Health Status: [Healthy or Unhealthy]
        Visual Observations: [describe colors, spots, damage, or other details you see]
        Preliminary Diagnosis: [if unhealthy, suggest what might be wrong, or 'None' if healthy]
        Issue: [disease/pest or 'Healthy']
        Confidence: [percentage]
        Symptoms: [brief description]
        Cause: [root cause]
        Remedies: [3-4 practical solutions]
        Prevention: [preventive measures]
        Urgency: [Low/Medium/High]";

        // For now, return mock data - implement Gemini API call
        return $this->callGeminiAPI($prompt, $imagePath);
    }
    
    private function callGeminiAPI($prompt, $imagePath) {
        // Implement Gemini API call
        // This is a placeholder - you'll need to implement the actual API call
        return [
            'plant_type' => 'Maize',
            'plant_part' => 'Leaf',
            'health_status' => 'Unhealthy',
            'visual_observations' => 'Yellow spots on leaves, brown edges',
            'preliminary_diagnosis' => 'Fungal infection',
            'issue' => 'Leaf Rust',
            'confidence' => '85%',
            'symptoms' => 'Yellow and brown spots on leaves',
            'cause' => 'Fungal infection due to humid conditions',
            'remedies' => "1. Apply fungicide\n2. Remove affected leaves\n3. Improve air circulation",
            'prevention' => 'Regular monitoring and proper spacing',
            'urgency' => 'Medium'
        ];
    }
    
    private function serveImage($imageId) {
        // Serve images - implementation depends on how you store them
        // This is a simplified version
        try {
            $diagnosis = $this->diagnoses->findOne(['_id' => new ObjectId($imageId)]);
            if ($diagnosis && file_exists($diagnosis['image_path'])) {
                header('Content-Type: image/jpeg');
                readfile($diagnosis['image_path']);
                exit;
            }
        } catch (Exception $e) {
            // Log error
        }
        
        http_response_code(404);
        exit;
    }
    
    private function healthCheck() {
        try {
            // Test database connection
            $this->users->findOne();
            
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'healthy',
                'timestamp' => date('c'),
                'version' => '2.0.0'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ]);
        }
    }
    
    private function sitemap() {
        header('Content-Type: application/xml');
        
        $baseUrl = $this->getBaseUrl();
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            <url>
                <loc>' . $baseUrl . '</loc>
                <changefreq>daily</changefreq>
                <priority>1.0</priority>
            </url>
            <url>
                <loc>' . $baseUrl . '/history</loc>
                <changefreq>weekly</changefreq>
                <priority>0.8</priority>
            </url>
            <url>
                <loc>' . $baseUrl . '/login</loc>
                <changefreq>monthly</changefreq>
                <priority>0.6</priority>
            </url>
            <url>
                <loc>' . $baseUrl . '/register</loc>
                <changefreq>monthly</changefreq>
                <priority>0.6</priority>
            </url>
        </urlset>';
        
        echo $sitemap;
        exit;
    }
    
    private function robots() {
        header('Content-Type: text/plain');
        echo "User-agent: *\nAllow: /\n";
        exit;
    }
    
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host;
    }
    
    private function render($template, $data = []) {
        extract($data);
        
        // Add user info to all templates
        $isAuthenticated = $this->isAuthenticated();
        $isAdmin = $this->isAdmin();
        $userId = $_SESSION['user_id'] ?? null;
        $userEmail = $_SESSION['user_email'] ?? null;
        
        // Include the template
        $templatePath = __DIR__ . '/templates/' . $template . '.php';
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            $this->notFound();
        }
        exit;
    }
    
    private function flash($message, $type = 'info') {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
    }
    
    private function getFlashMessages() {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
    
    private function notFound() {
        http_response_code(404);
        $this->render('404');
        exit;
    }
}

// Error handling
set_exception_handler(function($e) {
    error_log("Uncaught exception: " . $e->getMessage());
    http_response_code(500);
    echo "Internal Server Error";
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error: $errstr in $errfile on line $errline");
});

// Run the application
$app = new CropDoctorApp();
$app->run();
?>