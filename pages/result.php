<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$diagnosis_id = $_GET['id'] ?? 0;
$diagnosis = null;

if ($diagnosis_id > 0) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM diagnoses WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $diagnosis_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $diagnosis = $result->fetch_assoc();
    $stmt->close();
}
?>

<section class="result-page">
    <?php if ($diagnosis): ?>
        <h2>Diagnosis Report</h2>
        <div class="report-details">
            <div class="image-container">
                <img src="../assets/images/uploads/<?php echo htmlspecialchars($diagnosis['image_path']); ?>" alt="Diagnosed Plant Image">
            </div>
            <div class="text-details">
                <h3>Detected Condition: <?php echo htmlspecialchars($diagnosis['disease_name']); ?></h3>
                <p><strong>Confidence Score:</strong> <?php echo number_format($diagnosis['confidence'] * 100, 2); ?>%</p>
                <p><strong>Diagnosis Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($diagnosis['diagnosed_at'])); ?></p>
                <hr>
                <h4>Treatment Suggestions:</h4>
                <p><?php echo nl2br(htmlspecialchars($diagnosis['suggestions'])); ?></p>
            </div>
        </div>
        <div class="action-links">
            <a href="history.php" class="btn btn-secondary">Back to History</a>
            <a href="upload.php" class="btn btn-primary">Diagnose Another Plant</a>
        </div>
    <?php else: ?>
        <h2>Diagnosis Not Found</h2>
        <p>The report you are looking for does not exist or you do not have permission to view it.</p>
        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>