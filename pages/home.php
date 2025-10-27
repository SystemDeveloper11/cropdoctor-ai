<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
?>

<section class="hero">
    <h1>Your AI-Powered Crop Doctor</h1>
    <p>Instantly diagnose plant diseases and get expert treatment suggestions.</p>
    <?php if (!isLoggedIn()): ?>
        <a href="register.php" class="btn btn-primary">Get Started</a>
    <?php else: ?>
        <a href="upload.php" class="btn btn-primary">Diagnose My Plant Now</a>
    <?php endif; ?>
</section>

<section class="features">
    <h2>How It Works</h2>
    <div class="feature-item">
        <h3>1. Upload an Image</h3>
        <p>Simply take a clear photo of your plant's leaf and upload it to our system.</p>
    </div>
    <div class="feature-item">
        <h3>2. Instant Diagnosis</h3>
        <p>Our AI analyzes the image to identify potential diseases and pests.</p>
    </div>
    <div class="feature-item">
        <h3>3. Receive Solutions</h3>
        <p>Get instant, actionable advice on how to treat your plant, delivered to your dashboard and email.</p>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>