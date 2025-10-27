<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure the user is logged in to access this page
requireLogin();

// Get user's recent uploads for reference
$user_id = $_SESSION['user_id'];
$recent_uploads_sql = "SELECT image_path, disease_name, confidence, diagnosed_at FROM diagnoses WHERE user_id = ? ORDER BY diagnosed_at DESC LIMIT 3";
$stmt = $conn->prepare($recent_uploads_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_result = $stmt->get_result();
$recent_uploads = $recent_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Plant Image | CropDoctor AI</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Your existing CSS remains the same */
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
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--dark);
            background: linear-gradient(to bottom, #f8fbf8 0%, #ffffff 100%);
            min-height: 100vh;
            line-height: 1.7;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            line-height: 1.3;
        }
        
        /* Upload Section */
        .upload-section {
            padding: 120px 0 60px;
            min-height: 100vh;
        }
        
        .upload-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .upload-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .upload-header h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .upload-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        
        /* Upload Card */
        .upload-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        /* Upload Area */
        .upload-area {
            border: 3px dashed #e0e0e0;
            border-radius: 15px;
            padding: 60px 30px;
            text-align: center;
            transition: all 0.3s;
            background: #fafafa;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .upload-area:hover, .upload-area.drag-over {
            border-color: var(--primary);
            background: rgba(26, 93, 26, 0.02);
        }
        
        .upload-area i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
            display: block;
            transition: all 0.3s;
        }
        
        .upload-area:hover i {
            color: var(--primary);
        }
        
        .upload-area h3 {
            font-size: 1.5rem;
            color: #555;
            margin-bottom: 15px;
        }
        
        .upload-area p {
            color: #777;
            margin-bottom: 25px;
        }
        
        .browse-btn {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
        }
        
        .browse-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 93, 26, 0.3);
        }
        
        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        /* Image Preview */
        .image-preview {
            display: none;
            margin-top: 30px;
            text-align: center;
        }
        
        .preview-container {
            position: relative;
            display: inline-block;
            max-width: 100%;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .remove-image {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--danger);
            font-size: 1.2rem;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .remove-image:hover {
            background: white;
            transform: scale(1.1);
        }
        
        /* Upload Options */
        .upload-options {
            margin-top: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .option-card {
            background: #f8fbf8;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .option-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .option-card h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .option-card p {
            font-size: 0.9rem;
            color: #666;
        }
        
        /* Submit Button */
        .submit-container {
            text-align: center;
            margin-top: 40px;
        }
        
        .submit-btn {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 16px 50px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .submit-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 93, 26, 0.3);
        }
        
        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* Upload Status */
        .upload-status {
            display: none;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
        }
        
        .status-content {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .status-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .status-content h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .status-content p {
            color: #666;
            margin-bottom: 30px;
        }
        
        /* Progress Bar */
        .progress-container {
            width: 100%;
            background: #f0f0f0;
            border-radius: 10px;
            height: 10px;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: var(--gradient);
            border-radius: 10px;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        /* Spinner */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border-left-color: var(--primary);
            animation: spin 1s ease infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Recent Uploads */
        .recent-uploads {
            margin-top: 60px;
        }
        
        .recent-uploads h3 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        .recent-uploads h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--secondary);
        }
        
        .uploads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .upload-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        
        .upload-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .upload-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .upload-details {
            padding: 20px;
        }
        
        .upload-disease {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .upload-confidence {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .confidence-high {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }
        
        .confidence-medium {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }
        
        .confidence-low {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }
        
        .upload-date {
            font-size: 0.8rem;
            color: #777;
        }
        
        /* Tips Section */
        .tips-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .tips-section h3 {
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tips-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .tip-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .tip-item i {
            color: var(--secondary);
            margin-top: 3px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .upload-section {
                padding: 100px 0 40px;
            }
            
            .upload-header h2 {
                font-size: 2rem;
            }
            
            .upload-card {
                padding: 25px;
            }
            
            .upload-area {
                padding: 40px 20px;
            }
            
            .upload-options {
                grid-template-columns: 1fr;
            }
        }
        
        /* Animation for elements */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Hidden file input for form */
        .hidden-file-input {
            display: none;
        }

        /* Error message styling */
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Upload Section -->
    <section class="upload-section">
        <div class="upload-container">
            <div class="upload-header animate-on-scroll">
                <h2>Upload Plant Image for Diagnosis</h2>
                <p>Get instant AI-powered diagnosis for your plant diseases. Upload a clear image of a single plant leaf for accurate results.</p>
            </div>
            
            <!-- Display error messages if any -->
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message animate-on-scroll">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php
                    $error_messages = [
                        'invalid_access' => 'Please use the upload form to submit images.',
                        'no_file_data' => 'No file data received. Please try again.',
                        'missing_field' => 'File upload failed. Please try again.',
                        'upload_4' => 'Please select a file before uploading.',
                        'upload_1' => 'File is too large. Please select a smaller image.',
                        'upload_2' => 'File is too large. Please select a smaller image.',
                        'invalid_type' => 'Invalid file type. Please upload JPEG, PNG, or GIF images.',
                        'file_too_large' => 'File is too large. Maximum size is 10MB.',
                        'upload_failed' => 'File upload failed. Please try again.',
                    ];
                    
                    $error_code = $_GET['error'];
                    echo htmlspecialchars($error_messages[$error_code] ?? 'An error occurred. Please try again.');
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="upload-card animate-on-scroll">
                <!-- FIXED FORM ACTION -->
                <form id="upload-form" action="../api/analyze.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h3>Drag & Drop Your Image</h3>
                        <p>Supported formats: JPG, PNG, WEBP (Max 10MB)</p>
                        <button type="button" class="browse-btn">Browse Files</button>
                        <!-- This is the main file input that will be submitted with the form -->
                        <input type="file" id="plant-image" name="plant_image" accept="image/*" class="file-input" required>
                    </div>
                    
                    <div class="image-preview" id="imagePreview">
                        <div class="preview-container">
                            <img src="" alt="Preview" class="preview-image" id="previewImage">
                            <button type="button" class="remove-image" id="removeImage">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="upload-options">
                        <div class="option-card">
                            <i class="fas fa-camera"></i>
                            <h4>Clear Image</h4>
                            <p>Ensure good lighting and focus on the affected area</p>
                        </div>
                        <div class="option-card">
                            <i class="fas fa-leaf"></i>
                            <h4>Single Leaf</h4>
                            <p>Upload image of a single leaf for best accuracy</p>
                        </div>
                        <div class="option-card">
                            <i class="fas fa-ruler"></i>
                            <h4>Close-up Shot</h4>
                            <p>Get close enough to see disease details clearly</p>
                        </div>
                    </div>
                    
                    <div class="submit-container">
                        <button type="submit" class="submit-btn" id="submitBtn" disabled>
                            <i class="fas fa-search"></i>
                            Analyze Image
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="upload-status" id="uploadStatus">
                <div class="status-content">
                    <div class="spinner"></div>
                    <h3>Analyzing Your Image</h3>
                    <p>Our AI is examining your plant image for diseases. This may take a few seconds.</p>
                    <div class="progress-container">
                        <div class="progress-bar" id="progressBar"></div>
                    </div>
                    <p id="progressText">Initializing analysis...</p>
                </div>
            </div>
            
            <!-- Tips Section -->
            <div class="tips-section animate-on-scroll">
                <h3><i class="fas fa-lightbulb"></i> Tips for Better Results</h3>
                <div class="tips-list">
                    <div class="tip-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Natural Lighting</strong>
                            <p>Take photos in natural daylight for accurate color representation</p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Focus on Affected Area</strong>
                            <p>Ensure the diseased part of the leaf is clearly visible</p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Avoid Shadows</strong>
                            <p>Make sure your shadow doesn't cover the plant</p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Multiple Angles</strong>
                            <p>If possible, upload images from different angles</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Uploads -->
            <?php if (!empty($recent_uploads)): ?>
            <div class="recent-uploads animate-on-scroll">
                <h3>Your Recent Diagnoses</h3>
                <div class="uploads-grid">
                    <?php foreach ($recent_uploads as $upload): ?>
                    <div class="upload-item">
                        <img src="../assets/images/uploads/<?php echo htmlspecialchars($upload['image_path']); ?>" 
                             alt="Diagnosed Plant" 
                             class="upload-image"
                             onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'180\' viewBox=\'0 0 300 180\'><rect width=\'300\' height=\'180\' fill=\'%23f8fbf8\'/><path d=\'M150,50 C200,30 250,50 250,90 C250,130 200,150 150,170 C100,150 50,130 50,90 C50,50 100,30 150,50 Z\' fill=\'%234aab4a\'/><circle cx=\'120\' cy=\'80\' r=\'10\' fill=\'%23e6b325\'/><circle cx=\'180\' cy=\'80\' r=\'10\' fill=\'%23e6b325\'/><path d=\'M150,110 Q170,120 150,130 Q130,120 150,110 Z\' fill=\'%234a7c59\'/></svg>'">
                        <div class="upload-details">
                            <div class="upload-disease"><?php echo htmlspecialchars($upload['disease_name']); ?></div>
                            <div class="upload-confidence <?php 
                                $confidence = $upload['confidence'] * 100;
                                if ($confidence >= 80) echo 'confidence-high';
                                elseif ($confidence >= 60) echo 'confidence-medium';
                                else echo 'confidence-low';
                            ?>">
                                Confidence: <?php echo number_format($confidence, 1); ?>%
                            </div>
                            <div class="upload-date">
                                <?php echo date("M j, Y", strtotime($upload['diagnosed_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        // DOM Elements
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('plant-image');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        const removeImage = document.getElementById('removeImage');
        const submitBtn = document.getElementById('submitBtn');
        const uploadForm = document.getElementById('upload-form');
        const uploadStatus = document.getElementById('uploadStatus');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const browseBtn = document.querySelector('.browse-btn');
        
        // Browse button click triggers file input
        browseBtn.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Drag and Drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            uploadArea.classList.add('drag-over');
        }
        
        function unhighlight() {
            uploadArea.classList.remove('drag-over');
        }
        
        // Handle dropped files
        uploadArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }
        
        // Handle file input change
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });
        
        // Process selected files
        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    showError('Please select an image file (JPG, PNG, or WEBP)');
                    return;
                }
                
                // Validate file size (max 10MB)
                if (file.size > 10 * 1024 * 1024) {
                    showError('File size must be less than 10MB');
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    imagePreview.style.display = 'block';
                    submitBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Remove image preview
        removeImage.addEventListener('click', function() {
            imagePreview.style.display = 'none';
            fileInput.value = '';
            submitBtn.disabled = true;
        });
        
        // Form submission - SIMPLIFIED version that just submits the form normally
        uploadForm.addEventListener('submit', function(e) {
            // Don't prevent default - let the form submit normally
            // Just show loading status
            
            if (!fileInput.files.length) {
                e.preventDefault();
                showError('Please select an image first');
                return;
            }
            
            // Show upload status
            uploadStatus.style.display = 'block';
            submitBtn.disabled = true;
            
            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress >= 90) {
                    progress = 90;
                    clearInterval(progressInterval);
                }
                progressBar.style.width = progress + '%';
                progressText.textContent = `Uploading: ${Math.round(progress)}%`;
            }, 200);
            
            // The form will now submit normally and redirect to analyze.php
            // No need for AJAX since analyze.php handles the redirect to result.php
        });
        
        // Error handling
        function showError(message) {
            // Create error notification
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: #dc3545;
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                z-index: 1000;
                max-width: 300px;
            `;
            errorDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(errorDiv);
            
            // Remove after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
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
        
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>