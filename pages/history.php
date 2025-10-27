<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get filter parameters
$filter_disease = $_GET['disease'] ?? '';
$filter_confidence = $_GET['confidence'] ?? '';
$filter_date = $_GET['date'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build SQL query with filters
$sql = "SELECT * FROM diagnoses WHERE user_id = ?";
$params = [$user_id];
$param_types = "i";

// Add search filter
if (!empty($search_query)) {
    $sql .= " AND (disease_name LIKE ? OR treatment_plan LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $param_types .= "ss";
}

// Add disease filter
if (!empty($filter_disease) && $filter_disease !== 'all') {
    $sql .= " AND disease_name = ?";
    $params[] = $filter_disease;
    $param_types .= "s";
}

// Add confidence filter
if (!empty($filter_confidence)) {
    switch ($filter_confidence) {
        case 'high':
            $sql .= " AND confidence >= 0.8";
            break;
        case 'medium':
            $sql .= " AND confidence >= 0.6 AND confidence < 0.8";
            break;
        case 'low':
            $sql .= " AND confidence < 0.6";
            break;
    }
}

// Add date filter
if (!empty($filter_date)) {
    switch ($filter_date) {
        case 'today':
            $sql .= " AND DATE(diagnosed_at) = CURDATE()";
            break;
        case 'week':
            $sql .= " AND diagnosed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $sql .= " AND diagnosed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

$sql .= " ORDER BY diagnosed_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);

// Bind parameters dynamically
if (count($params) > 1) {
    $stmt->bind_param($param_types, ...$params);
} else {
    $stmt->bind_param($param_types, $params[0]);
}

$stmt->execute();
$result = $stmt->get_result();
$diagnoses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique diseases for filter dropdown
$diseases_sql = "SELECT DISTINCT disease_name FROM diagnoses WHERE user_id = ? ORDER BY disease_name";
$stmt = $conn->prepare($diseases_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$diseases_result = $stmt->get_result();
$unique_diseases = $diseases_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$total_diagnoses = count($diagnoses);
$high_confidence = array_filter($diagnoses, function($d) { return $d['confidence'] >= 0.8; });
$high_confidence_count = count($high_confidence);
$success_rate = $total_diagnoses > 0 ? round(($high_confidence_count / $total_diagnoses) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosis History | CropDoctor AI</title>
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
        
        /* History Page Styles */
        .history-page {
            padding: 120px 0 60px;
            min-height: 100vh;
        }
        
        .history-container {
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
        }
        
        .page-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Stats Overview */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Filters Section */
        .filters-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filters-title {
            font-size: 1.3rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .results-count {
            color: #666;
            font-size: 0.9rem;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
        }
        
        .filter-select, .filter-input {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s;
            background: white;
        }
        
        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(26, 93, 26, 0.1);
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 93, 26, 0.3);
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
        
        .btn-danger {
            background: var(--danger);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
        }
        
        .btn-small {
            padding: 8px 15px;
            font-size: 0.8rem;
        }
        
        /* History List */
        .history-list {
            display: grid;
            gap: 20px;
        }
        
        .history-item {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            display: flex;
            gap: 25px;
            border-left: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        
        .history-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .history-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        
        .history-item:hover::before {
            left: 100%;
        }
        
        .history-thumbnail {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .history-item:hover .history-thumbnail {
            transform: scale(1.05);
        }
        
        .history-details {
            flex: 1;
        }
        
        .history-details h4 {
            font-size: 1.4rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .confidence-badge {
            display: inline-block;
            padding: 6px 12px;
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
        
        .history-date {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .history-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .history-actions a {
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .view-report {
            color: var(--primary);
        }
        
        .view-report:hover {
            color: var(--primary-dark);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
            display: block;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #777;
            margin-bottom: 25px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
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
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }
        
        .pagination a, .pagination span {
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }
        
        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination .current {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Export Section */
        .export-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .export-section h3 {
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .export-options {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .history-page {
                padding: 100px 0 40px;
            }
            
            .page-header h2 {
                font-size: 2rem;
            }
            
            .history-item {
                flex-direction: column;
                text-align: center;
            }
            
            .history-thumbnail {
                width: 100%;
                max-width: 250px;
                margin: 0 auto;
            }
            
            .history-actions {
                justify-content: center;
            }
            
            .filters-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                width: 100%;
                justify-content: center;
            }
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
    </style>
</head>
<body>
    <!-- History Page -->
    <section class="history-page">
        <div class="history-container">
            <div class="page-header animate-on-scroll">
                <h2>Your Diagnosis History</h2>
                <p>Review all your past plant disease diagnoses and treatment recommendations</p>
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

            <!-- Stats Overview -->
            <div class="stats-overview animate-on-scroll">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $total_diagnoses; ?></span>
                    <div class="stat-label">Total Diagnoses</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $high_confidence_count; ?></span>
                    <div class="stat-label">High Confidence</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $success_rate; ?>%</span>
                    <div class="stat-label">Success Rate</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo count($unique_diseases); ?></span>
                    <div class="stat-label">Unique Diseases</div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section animate-on-scroll">
                <div class="filters-header">
                    <h3 class="filters-title">
                        <i class="fas fa-filter"></i>
                        Filter Diagnoses
                    </h3>
                    <div class="results-count">
                        Showing <?php echo count($diagnoses); ?> of <?php echo $total_diagnoses; ?> results
                    </div>
                </div>
                
                <form method="GET" action="" id="filterForm">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label">Search</label>
                            <input type="text" name="search" class="filter-input" placeholder="Search diseases..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Disease Type</label>
                            <select name="disease" class="filter-select">
                                <option value="all">All Diseases</option>
                                <?php foreach ($unique_diseases as $disease): ?>
                                    <option value="<?php echo htmlspecialchars($disease['disease_name']); ?>" 
                                        <?php echo $filter_disease === $disease['disease_name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($disease['disease_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Confidence Level</label>
                            <select name="confidence" class="filter-select">
                                <option value="">All Levels</option>
                                <option value="high" <?php echo $filter_confidence === 'high' ? 'selected' : ''; ?>>High (80%+)</option>
                                <option value="medium" <?php echo $filter_confidence === 'medium' ? 'selected' : ''; ?>>Medium (60-79%)</option>
                                <option value="low" <?php echo $filter_confidence === 'low' ? 'selected' : ''; ?>>Low (Below 60%)</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Time Period</label>
                            <select name="date" class="filter-select">
                                <option value="">All Time</option>
                                <option value="today" <?php echo $filter_date === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="week" <?php echo $filter_date === 'week' ? 'selected' : ''; ?>>Past Week</option>
                                <option value="month" <?php echo $filter_date === 'month' ? 'selected' : ''; ?>>Past Month</option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Apply Filters
                            </button>
                            <a href="history.php" class="btn btn-outline">
                                <i class="fas fa-redo"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (empty($diagnoses)): ?>
                <div class="empty-state animate-on-scroll">
                    <i class="fas fa-seedling"></i>
                    <h3>No Diagnoses Found</h3>
                    <p><?php echo $total_diagnoses > 0 ? 'Try adjusting your filters to see more results.' : 'You haven\'t made any diagnoses yet.'; ?></p>
                    <a href="upload.php" class="btn btn-primary">
                        <i class="fas fa-camera"></i>
                        Upload Your First Image
                    </a>
                </div>
            <?php else: ?>
                <div class="history-list">
                    <?php foreach ($diagnoses as $diagnosis): 
                        $confidence = $diagnosis['confidence'] * 100;
                        $confidence_class = $confidence >= 80 ? 'confidence-high' : ($confidence >= 60 ? 'confidence-medium' : 'confidence-low');
                    ?>
                        <div class="history-item animate-on-scroll">
                            <img src="../assets/images/uploads/<?php echo htmlspecialchars($diagnosis['image_path']); ?>" 
                                 alt="Diagnosed Plant" 
                                 class="history-thumbnail"
                                 onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\' viewBox=\'0 0 120 120\'><rect width=\'120\' height=\'120\' fill=\'%23f8fbf8\'/><path d=\'M60,30 C70,20 90,25 90,40 C90,55 70,60 60,80 C50,60 30,55 30,40 C30,25 50,20 60,30 Z\' fill=\'%234aab4a\'/><circle cx=\'50\' cy=\'45\' r=\'5\' fill=\'%23e6b325\'/><circle cx=\'70\' cy=\'45\' r=\'5\' fill=\'%23e6b325\'/><path d=\'M60,55 Q65,60 60,65 Q55,60 60,55 Z\' fill=\'%234a7c59\'/></svg>'">
                            <div class="history-details">
                                <h4><?php echo htmlspecialchars($diagnosis['disease_name']); ?></h4>
                                <div class="confidence-badge <?php echo $confidence_class; ?>">
                                    <i class="fas fa-chart-line"></i>
                                    Confidence: <?php echo number_format($confidence, 1); ?>%
                                </div>
                                <div class="history-date">
                                    <i class="far fa-calendar"></i>
                                    Diagnosed on: <?php echo date("F j, Y, g:i a", strtotime($diagnosis['diagnosed_at'])); ?>
                                </div>
                                <div class="history-actions">
                                    <a href="result.php?id=<?php echo $diagnosis['id']; ?>" class="view-report">
                                        <i class="fas fa-info-circle"></i> View Full Report
                                    </a>
                                    <a href="../api/sendemail.php?id=<?php echo $diagnosis['id']; ?>" class="btn btn-primary btn-small">
                                        <i class="fas fa-envelope"></i> Send to Email
                                    </a>
                                    <a href="#" class="btn btn-outline btn-small" onclick="shareDiagnosis(<?php echo $diagnosis['id']; ?>)">
                                        <i class="fas fa-share-alt"></i> Share
                                    </a>
                                    <a href="../api/delete-diagnosis.php?id=<?php echo $diagnosis['id']; ?>" class="btn btn-danger btn-small" onclick="return confirmDelete(<?php echo $diagnosis['id']; ?>, '<?php echo htmlspecialchars($diagnosis['disease_name']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Simple Pagination (you can enhance this with actual pagination) -->
                <div class="pagination animate-on-scroll">
                    <a href="#" class="prev"><i class="fas fa-chevron-left"></i> Previous</a>
                    <span class="current">1</span>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#" class="next">Next <i class="fas fa-chevron-right"></i></a>
                </div>
            <?php endif; ?>

            <!-- Export Section -->
            <div class="export-section animate-on-scroll">
                <h3><i class="fas fa-download"></i> Export Your Data</h3>
                <p>Download your diagnosis history for record keeping or analysis</p>
                <div class="export-options">
                    <a href="../api/export-history.php?format=csv" class="btn btn-outline">
                        <i class="fas fa-file-csv"></i> Export as CSV
                    </a>
                    <a href="../api/export-history.php?format=pdf" class="btn btn-outline">
                        <i class="fas fa-file-pdf"></i> Export as PDF
                    </a>
                    <a href="../api/print-history.php" class="btn btn-outline" target="_blank">
                        <i class="fas fa-print"></i> Print History
                    </a>
                </div>
            </div>
        </div>
    </section>

    <script>
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
        
        // Confirm delete function
        function confirmDelete(diagnosisId, diseaseName) {
            return confirm(`Are you sure you want to delete the diagnosis for "${diseaseName}"? This action cannot be undone.`);
        }
        
        // Auto-submit form when filters change (optional)
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });
        
        // Initialize animations
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>