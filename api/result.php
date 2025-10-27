<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Ensure the user is logged in
requireLogin();

// Get diagnosis ID from URL
$diagnosis_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($diagnosis_id === 0) {
    die("Invalid diagnosis ID");
}

// Fetch diagnosis details from database
$sql = "SELECT d.*, u.username 
        FROM diagnoses d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.id = ? AND d.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $diagnosis_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$diagnosis = $result->fetch_assoc();
$stmt->close();

if (!$diagnosis) {
    die("Diagnosis not found or access denied");
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_diagnosis'])) {
    $delete_sql = "DELETE FROM diagnoses WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $diagnosis_id, $_SESSION['user_id']);
    
    if ($delete_stmt->execute()) {
        header("Location: ../pages/history.php?deleted=1");
        exit();
    } else {
        $error_message = "Failed to delete diagnosis. Please try again.";
    }
    $delete_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosis Results | CropDoctor AI</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #1a5d1a;
            --primary-light: #2e8b2e;
            --primary-dark: #0d3d0d;
            --secondary: #e6b325;
            --accent: #4a7c59;
            --light: #f8f9fa;
            --dark: #1e2a1e;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
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
            background: linear-gradient(to bottom, #f8fbf8 0%, #ffffff 100%);
            min-height: 100vh;
            line-height: 1.7;
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .result-section {
            padding: 120px 0 60px;
            min-height: 100vh;
            position: relative;
        }
        
        .result-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%231a5d1a" fill-opacity="0.02" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
            z-index: -1;
        }
        
        .result-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .result-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            padding: 50px;
            margin-bottom: 40px;
            border: 1px solid rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
        }
        
        .result-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }
        
        .result-header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid rgba(26, 93, 26, 0.1);
            position: relative;
        }
        
        .result-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 2px;
            background: var(--secondary);
        }
        
        .result-header h1 {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 15px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .result-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .diagnosis-image-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto 40px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
        }
        
        .diagnosis-image-container:hover {
            transform: scale(1.02);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.15);
        }
        
        .diagnosis-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(26,93,26,0.1), rgba(230,179,37,0.05));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .diagnosis-image-container:hover .image-overlay {
            opacity: 1;
        }
        
        .image-overlay i {
            font-size: 3rem;
            color: white;
            background: rgba(26, 93, 26, 0.7);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .disease-name {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 25px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        .disease-name::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--secondary);
            border-radius: 2px;
        }
        
        .confidence {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 40px;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .confidence:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .confidence-high {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.15) 0%, rgba(40, 167, 69, 0.05) 100%);
            color: var(--success);
            border: 2px solid var(--success);
        }
        
        .confidence-medium {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(255, 193, 7, 0.05) 100%);
            color: var(--warning);
            border: 2px solid var(--warning);
        }
        
        .confidence-low {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.05) 100%);
            color: var(--danger);
            border: 2px solid var(--danger);
        }
        
        .treatment-section {
            background: linear-gradient(135deg, #f8fbf8 0%, #e8f5e8 100%);
            border-radius: 20px;
            padding: 40px;
            margin: 40px 0;
            border-left: 5px solid var(--secondary);
            position: relative;
            overflow: hidden;
        }
        
        .treatment-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%232e8b2e" fill-opacity="0.03"><path d="M20,20 Q40,5 60,20 T100,20 Q85,40 100,60 T100,100 Q80,85 60,100 T20,100 Q35,80 20,60 T20,20 Z"/></svg>');
            animation: float 20s infinite linear;
        }
        
        .treatment-section h3 {
            color: var(--primary);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.8rem;
            position: relative;
            z-index: 2;
        }
        
        .treatment-section h3 i {
            color: var(--secondary);
            background: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .treatment-content {
            line-height: 1.8;
            color: #555;
            font-size: 1.1rem;
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.7);
            padding: 20px;
            border-radius: 10px;
        }
        
        .diagnosis-info {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            background: rgba(248, 251, 248, 0.5);
            padding-left: 10px;
            padding-right: 10px;
            border-radius: 8px;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-label i {
            color: var(--secondary);
            width: 20px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 50px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 15px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.4s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: var(--gradient);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 93, 26, 0.3);
        }
        
        .btn-secondary {
            background: var(--secondary);
            color: var(--dark);
        }
        
        .btn-secondary:hover {
            background: #dda20c;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(230, 179, 37, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        }
        
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .tip-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .tip-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--secondary);
        }
        
        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .tip-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(46, 139, 46, 0.2);
        }
        
        .tip-card h5 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .tip-card p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: -1;
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
            left: 5%;
            animation-delay: 0s;
            animation-duration: 20s;
        }
        
        .floating-element:nth-child(2) {
            top: 70%;
            left: 10%;
            animation-delay: 5s;
            animation-duration: 25s;
        }
        
        .floating-element:nth-child(3) {
            top: 30%;
            right: 5%;
            animation-delay: 10s;
            animation-duration: 18s;
        }
        
        .floating-element:nth-child(4) {
            top: 80%;
            right: 8%;
            animation-delay: 7s;
            animation-duration: 22s;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
            100% { transform: translateY(0) rotate(360deg); }
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .modal-header {
            background: var(--gradient);
            color: white;
            border-bottom: none;
            padding: 30px;
        }
        
        .modal-title {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }
        
        .modal-body {
            padding: 30px;
            font-size: 1.1rem;
        }
        
        .modal-footer {
            border-top: 1px solid #eee;
            padding: 20px 30px;
        }
        
        /* Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        @media (max-width: 768px) {
            .result-section {
                padding: 100px 0 40px;
            }
            
            .result-card {
                padding: 30px 20px;
            }
            
            .result-header h1 {
                font-size: 2.2rem;
            }
            
            .disease-name {
                font-size: 1.8rem;
            }
            
            .treatment-section {
                padding: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-element"><i class="fas fa-leaf"></i></div>
        <div class="floating-element"><i class="fas fa-seedling"></i></div>
        <div class="floating-element"><i class="fas fa-apple-alt"></i></div>
        <div class="floating-element"><i class="fas fa-tree"></i></div>
    </div>

    <!-- Result Section -->
    <section class="result-section">
        <div class="result-container">
            <!-- Main Result Card -->
            <div class="result-card animate-on-scroll">
                <div class="result-header">
                    <h1><i class="fas fa-clipboard-check"></i> Diagnosis Complete</h1>
                    <p>Your plant analysis results are ready</p>
                </div>
                
                <!-- Plant Image -->
                <div class="diagnosis-image-container animate-on-scroll">
                    <img src="../assets/images/uploads/<?php echo htmlspecialchars($diagnosis['image_path']); ?>" 
                         alt="Diagnosed Plant" 
                         class="diagnosis-image"
                         onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"400\" height=\"300\" viewBox=\"0 0 400 300\"><rect width=\"400\" height=\"300\" fill=\"%23f8fbf8\"/><path d=\"M200,80 C250,60 300,80 300,120 C300,160 250,180 200,200 C150,180 100,160 100,120 C100,80 150,60 200,80 Z\" fill=\"%234aab4a\"/><circle cx=\"160\" cy=\"110\" r=\"12\" fill=\"%23e6b325\"/><circle cx=\"240\" cy=\"110\" r=\"12\" fill=\"%23e6b325\"/><path d=\"M200,140 Q220,150 200,160 Q180,150 200,140 Z\" fill=\"%234a7c59\"/><text x=\"200\" y=\"250\" text-anchor=\"middle\" font-family=\"Arial\" font-size=\"14\" fill=\"%23666\">Plant Image</text></svg>'">
                    <div class="image-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
                
                <!-- Disease Name -->
                <h2 class="disease-name animate-on-scroll"><?php echo htmlspecialchars($diagnosis['disease_name']); ?></h2>
                
                <!-- Confidence Level -->
                <?php 
                $confidence_percent = $diagnosis['confidence'] * 100;
                $confidence_class = 'confidence-';
                if ($confidence_percent >= 80) $confidence_class .= 'high';
                elseif ($confidence_percent >= 60) $confidence_class .= 'medium';
                else $confidence_class .= 'low';
                ?>
                
                <div class="confidence <?php echo $confidence_class; ?> animate-on-scroll">
                    <i class="fas fa-chart-line"></i>
                    AI Confidence: <?php echo number_format($confidence_percent, 1); ?>%
                </div>
                
                <!-- Treatment Suggestions -->
                <div class="treatment-section animate-on-scroll">
                    <h3><i class="fas fa-heartbeat"></i> Treatment Recommendations</h3>
                    <div class="treatment-content">
                        <?php echo nl2br(htmlspecialchars($diagnosis['suggestions'])); ?>
                    </div>
                </div>
                
                <!-- Diagnosis Information -->
                <div class="diagnosis-info animate-on-scroll">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-hashtag"></i> Diagnosis ID:</span>
                        <span>#<?php echo $diagnosis['id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user"></i> Diagnosed For:</span>
                        <span><?php echo htmlspecialchars($diagnosis['username']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-alt"></i> Diagnosis Date:</span>
                        <span><?php echo date("F j, Y g:i A", strtotime($diagnosis['diagnosed_at'])); ?></span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="../pages/upload.php" class="btn btn-primary animate-on-scroll">
                        <i class="fas fa-upload"></i> Upload Another Image
                    </a>
                    <a href="../pages/history.php" class="btn btn-secondary animate-on-scroll">
                        <i class="fas fa-history"></i> View History
                    </a>
                    <a href="../pages/dashboard.php" class="btn btn-outline animate-on-scroll">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <button type="button" class="btn btn-danger animate-on-scroll" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash-alt"></i> Delete Diagnosis
                    </button>
                </div>
            </div>
            
            <!-- Prevention Tips Section -->
            <div class="result-card animate-on-scroll">
                <h3 class="text-center mb-4"><i class="fas fa-lightbulb"></i> Prevention Tips & Best Practices</h3>
                <div class="tips-grid">
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h5>Regular Monitoring</h5>
                        <p>Check your plants weekly for early signs of disease. Early detection is key to successful treatment.</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <h5>Proper Watering</h5>
                        <p>Avoid overwatering and ensure good drainage. Water at the base of plants to keep foliage dry.</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-wind"></i>
                        </div>
                        <h5>Air Circulation</h5>
                        <p>Space plants properly to allow air flow. This reduces humidity and prevents fungal growth.</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h5>Clean Tools</h5>
                        <p>Disinfect gardening tools regularly to prevent the spread of diseases between plants.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this diagnosis? This action cannot be undone.</p>
                    <p class="mb-0"><strong>Diagnosis:</strong> <?php echo htmlspecialchars($diagnosis['disease_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo date("F j, Y", strtotime($diagnosis['diagnosed_at'])); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="delete_diagnosis" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation on scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            
            elements.forEach((element, index) => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elementTop < windowHeight - 100) {
                    // Add delay based on element index for staggered animation
                    setTimeout(() => {
                        element.classList.add('animated');
                    }, index * 200);
                }
            });
        }
        
        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            animateOnScroll();
            
            // Add loading animation to buttons on click
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!this.classList.contains('btn-danger') || this.type === 'submit') {
                        this.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Loading...';
                        this.disabled = true;
                    }
                });
            });
        });
        
        window.addEventListener('scroll', animateOnScroll);
        
        // Show error message if exists
        <?php if (isset($error_message)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Create and show error toast
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 p-3';
                toast.style.zIndex = '9999';
                toast.innerHTML = `
                    <div class="toast show" role="alert">
                        <div class="toast-header bg-danger text-white">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong class="me-auto">Error</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            <?php echo $error_message; ?>
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            });
        <?php endif; ?>
    </script>
</body>
</html>