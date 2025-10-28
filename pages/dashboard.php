<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch the most recent diagnoses for the current user
$sql = "SELECT * FROM diagnoses WHERE user_id = ? ORDER BY diagnosed_at DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); 
$stmt->execute();
$result = $stmt->get_result();
$diagnoses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics for the dashboard
$total_diagnoses_sql = "SELECT COUNT(*) as total FROM diagnoses WHERE user_id = ?";
$stmt = $conn->prepare($total_diagnoses_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_result = $stmt->get_result();
$total_diagnoses = $total_result->fetch_assoc()['total'];
$stmt->close();

$high_confidence_sql = "SELECT COUNT(*) as high_conf FROM diagnoses WHERE user_id = ? AND confidence >= 0.8";
$stmt = $conn->prepare($high_confidence_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$high_conf_result = $stmt->get_result();
$high_confidence = $high_conf_result->fetch_assoc()['high_conf'];
$stmt->close();

// Get recent activity count (last 7 days)
$recent_activity_sql = "SELECT COUNT(*) as recent FROM diagnoses WHERE user_id = ? AND diagnosed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $conn->prepare($recent_activity_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_result = $stmt->get_result();
$recent_activity = $recent_result->fetch_assoc()['recent'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CropDoctor AI</title>
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
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
            background: linear-gradient(to bottom, #f8fbf8 0%, #ffffff 100%);
            min-height: 100vh;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            line-height: 1.3;
        }
        
        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s, visibility 0.5s;
        }

        .loading-screen.loaded {
            opacity: 0;
            visibility: hidden;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .loading-spinner-large {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Dashboard Styles */
        .dashboard {
            padding: 120px 0 60px;
            min-height: 100vh;
        }
        
        .dashboard-header {
            margin-bottom: 40px;
            text-align: center;
            position: relative;
        }
        
        .dashboard-header h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .dashboard-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .user-welcome {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.2);
        }
        
        /* Message Styles */
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 500;
            text-align: center;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            animation: slideInDown 0.5s ease-out;
        }
        
        .message.success {
            background: rgba(46, 139, 46, 0.1);
            color: var(--primary);
            border-left: 4px solid var(--primary);
        }
        
        .message.error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }
        
        /* Dashboard Stats */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: all 0.4s;
            border: 1px solid rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            background: var(--gradient-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.8rem;
            box-shadow: 0 10px 20px rgba(46, 139, 46, 0.2);
            transition: all 0.4s;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
            display: block;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-top: 10px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .trend-up {
            color: #28a745;
        }

        .trend-down {
            color: #dc3545;
        }
        
        /* Dashboard Actions */
        .dashboard-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }
        
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
        }
        
        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
            position: relative;
            overflow: hidden;
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
        
        .btn-secondary {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.2);
        }
        
        .btn-small {
            padding: 8px 20px;
            font-size: 0.9rem;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--accent);
            color: var(--accent);
        }

        .btn-outline:hover {
            background: var(--accent);
            color: white;
        }
        
        /* Recent Diagnoses */
        .recent-diagnoses {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .recent-diagnoses h3 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .recent-diagnoses h3::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: var(--secondary);
        }

        .view-all-link {
            font-size: 1rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .view-all-link:hover {
            color: var(--primary-dark);
            gap: 8px;
        }
        
        .diagnosis-list {
            display: grid;
            gap: 25px;
        }
        
        .diagnosis-item {
            display: flex;
            gap: 25px;
            padding: 25px;
            border-radius: 15px;
            background: #f8fbf8;
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        
        .diagnosis-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .diagnosis-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }

        .diagnosis-item:hover::before {
            left: 100%;
        }
        
        .diagnosis-thumbnail {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .diagnosis-item:hover .diagnosis-thumbnail {
            transform: scale(1.05);
        }
        
        .diagnosis-details {
            flex: 1;
        }
        
        .diagnosis-details h4 {
            font-size: 1.4rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .diagnosis-details p {
            margin-bottom: 8px;
            color: #555;
        }
        
        .diagnosis-details small {
            color: #777;
            display: block;
            margin-bottom: 15px;
        }
        
        .diagnosis-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .diagnosis-actions a {
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .diagnosis-actions a:first-child {
            color: var(--primary);
        }
        
        .diagnosis-actions a:first-child:hover {
            color: var(--primary-dark);
        }
        
        .no-diagnoses {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-diagnoses i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
            display: block;
        }
        
        .no-diagnoses p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .quick-stat {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.3s;
        }

        .quick-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .quick-stat i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .quick-stat .number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }

        .quick-stat .label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        @keyframes fadeInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard {
                padding: 100px 0 40px;
            }
            
            .dashboard-header h2 {
                font-size: 2rem;
            }
            
            .dashboard-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .diagnosis-item {
                flex-direction: column;
                text-align: center;
            }
            
            .diagnosis-thumbnail {
                width: 100%;
                max-width: 250px;
                margin: 0 auto;
            }
            
            .diagnosis-actions {
                justify-content: center;
            }
            
            .recent-diagnoses {
                padding: 25px;
            }

            .recent-diagnoses h3 {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .user-welcome {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Performance Optimizations */
        .lazy-load {
            opacity: 0;
            transition: opacity 0.3s;
        }

        .lazy-load.loaded {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <div class="loading-spinner-large"></div>
            <h4>CropDoctor AI</h4>
            <p>Loading your dashboard...</p>
        </div>
    </div>

    <!-- Navigation -->

           <!-- <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="cropdoctor.kesug.com/pages/dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upload.php">Diagnose</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../includes/logout.php">Logout</a>
                    </li>
                </ul>
            </div> -->
        </div>
    </nav>

    <!-- Dashboard Section -->
    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header animate-on-scroll">
                <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
                <p>Your agricultural insights at a glance.</p>
                <div class="user-welcome">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($username); ?></strong>
                        <div class="text-muted">Member since <?php echo date('F Y'); ?></div>
                    </div>
                </div>
            </div>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success animate-on-scroll">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error animate-on-scroll">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Quick Stats -->
            <div class="quick-stats animate-on-scroll">
                <div class="quick-stat">
                    <i class="fas fa-clock"></i>
                    <span class="number"><?php echo $recent_activity; ?></span>
                    <div class="label">This Week</div>
                </div>
                <div class="quick-stat">
                    <i class="fas fa-chart-bar"></i>
                    <span class="number"><?php echo $high_confidence; ?></span>
                    <div class="label">High Confidence</div>
                </div>
                <div class="quick-stat">
                    <i class="fas fa-percentage"></i>
                    <span class="number"><?php echo $total_diagnoses > 0 ? round(($high_confidence / $total_diagnoses) * 100) : 0; ?>%</span>
                    <div class="label">Success Rate</div>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="dashboard-stats">
                <div class="stat-card animate-on-scroll">
                    <div class="stat-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <span class="stat-number" id="totalDiagnoses">0</span>
                    <div class="stat-label">Total Diagnoses</div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12% this month</span>
                    </div>
                </div>
                
                <div class="stat-card animate-on-scroll">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="stat-number" id="highConfidence">0</span>
                    <div class="stat-label">High Confidence Results</div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8% this month</span>
                    </div>
                </div>
                
                <div class="stat-card animate-on-scroll">
                    <div class="stat-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <span class="stat-number">95%</span>
                    <div class="stat-label">AI Accuracy</div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+2% this month</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Actions -->
            <div class="dashboard-actions animate-on-scroll">
                <a href="upload.php" class="btn btn-primary">
                    <i class="fas fa-camera"></i>
                    Diagnose New Plant
                </a>
                <a href="history.php" class="btn btn-secondary">
                    <i class="fas fa-history"></i>
                    View Diagnosis History
                </a>
                <a href="../index.php#how-it-works" class="btn btn-outline">
                    <i class="fas fa-graduation-cap"></i>
                    How It Works
                </a>
            </div>

            <!-- Recent Diagnoses -->
            <div class="recent-diagnoses animate-on-scroll">
                <h3>
                    Recent Diagnoses
                    <a href="history.php" class="view-all-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </h3>
                <?php if (empty($diagnoses)): ?>
                    <div class="no-diagnoses">
                        <i class="fas fa-seedling"></i>
                        <p>You have no recent diagnoses. Upload an image to get started!</p>
                        <a href="upload.php" class="btn btn-primary">
                            <i class="fas fa-camera"></i>
                            Diagnose Your First Plant
                        </a>
                    </div>
                <?php else: ?>
                    <div class="diagnosis-list">
                        <?php foreach ($diagnoses as $diagnosis): ?>
                            <div class="diagnosis-item">
                                <img src="../assets/images/uploads/<?php echo htmlspecialchars($diagnosis['image_path']); ?>" 
                                     alt="Diagnosed Plant" 
                                     class="diagnosis-thumbnail lazy-load"
                                     onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\' viewBox=\'0 0 120 120\'><rect width=\'120\' height=\'120\' fill=\'%23f8fbf8\'/><path d=\'M60,30 C70,20 90,25 90,40 C90,55 70,60 60,80 C50,60 30,55 30,40 C30,25 50,20 60,30 Z\' fill=\'%234aab4a\'/><circle cx=\'50\' cy=\'45\' r=\'5\' fill=\'%23e6b325\'/><circle cx=\'70\' cy=\'45\' r=\'5\' fill=\'%23e6b325\'/><path d=\'M60,55 Q65,60 60,65 Q55,60 60,55 Z\' fill=\'%234a7c59\'/></svg>'">
                                <div class="diagnosis-details">
                                    <h4><?php echo htmlspecialchars($diagnosis['disease_name']); ?></h4>
                                    <p>
                                        <span class="confidence-badge" style="background: <?php 
                                            $confidence = $diagnosis['confidence'] * 100;
                                            if ($confidence >= 80) echo '#28a745';
                                            elseif ($confidence >= 60) echo '#ffc107';
                                            else echo '#dc3545';
                                        ?>; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.9rem;">
                                            <i class="fas fa-chart-line"></i>
                                            Confidence: <?php echo number_format($confidence, 2); ?>%
                                        </span>
                                    </p>
                                    <small>
                                        <i class="far fa-calendar"></i>
                                        Diagnosed on: <?php echo date("F j, Y, g:i a", strtotime($diagnosis['diagnosed_at'])); ?>
                                    </small>
                                    <div class="diagnosis-actions">
                                        <a href="result.php?id=<?php echo $diagnosis['id']; ?>">
                                            <i class="fas fa-info-circle"></i> View Details
                                        </a>
                                        <a href="../api/send-email.php?id=<?php echo $diagnosis['id']; ?>" class="btn btn-primary btn-small">
                                            <i class="fas fa-envelope"></i> Send to Email
                                        </a>
                                        <a href="#" class="btn btn-outline btn-small" onclick="shareDiagnosis(<?php echo $diagnosis['id']; ?>)">
                                            <i class="fas fa-share-alt"></i> Share
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fast loading - hide loading screen after 2 seconds max
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loadingScreen').classList.add('loaded');
            }, 1500); // Reduced to 1.5 seconds for faster perceived loading
        });

        // If page takes longer than 2 seconds, still hide the loader
        setTimeout(function() {
            document.getElementById('loadingScreen').classList.add('loaded');
        }, 2000);

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
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

        // Animated counters for stats
        function animateCounters() {
            const totalElement = document.getElementById('totalDiagnoses');
            const highConfElement = document.getElementById('highConfidence');
            
            if (totalElement) {
                const totalTarget = <?php echo $total_diagnoses; ?>;
                animateValue(totalElement, 0, totalTarget, 1500);
            }
            
            if (highConfElement) {
                const highConfTarget = <?php echo $high_confidence; ?>;
                animateValue(highConfElement, 0, highConfTarget, 1500);
            }
        }

        function animateValue(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                element.textContent = value;
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Lazy load images
        function lazyLoadImages() {
            const lazyImages = document.querySelectorAll('.lazy-load');
            
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Share diagnosis function
        function shareDiagnosis(diagnosisId) {
            if (navigator.share) {
                navigator.share({
                    title: 'CropDoctor AI Diagnosis',
                    text: 'Check out this plant disease diagnosis from CropDoctor AI',
                    url: window.location.origin + '/pages/result.php?id=' + diagnosisId
                })
                .then(() => console.log('Successful share'))
                .catch((error) => console.log('Error sharing:', error));
            } else {
                // Fallback for browsers that don't support Web Share API
                const url = window.location.origin + '/pages/result.php?id=' + diagnosisId;
                navigator.clipboard.writeText(url).then(() => {
                    alert('Diagnosis link copied to clipboard!');
                });
            }
        }
        
        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            animateOnScroll();
            animateCounters();
            lazyLoadImages();
        });
        
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>
