<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure only admins can access this page
requireAdmin();

// Fetch total number of users
$sql_users = "SELECT COUNT(*) AS total_users FROM users";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_assoc()['total_users'];

// Fetch total number of diagnoses
$sql_diagnoses = "SELECT COUNT(*) AS total_diagnoses FROM diagnoses";
$result_diagnoses = $conn->query($sql_diagnoses);
$total_diagnoses = $result_diagnoses->fetch_assoc()['total_diagnoses'];

// Fetch most common diseases
$sql_trends = "SELECT disease_name, COUNT(*) AS count FROM diagnoses GROUP BY disease_name ORDER BY count DESC LIMIT 5";
$result_trends = $conn->query($sql_trends);
$disease_trends = $result_trends->fetch_all(MYSQLI_ASSOC);
?>

<section class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    <div class="stats-container">
        <div class="stat-card">
            <h3>Total Users</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Diagnoses</h3>
            <p><?php echo $total_diagnoses; ?></p>
        </div>
    </div>

    <h3>Top 5 Most Diagnosed Diseases</h3>
    <?php if (empty($disease_trends)): ?>
        <p>No diagnoses data available yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Disease Name</th>
                    <th>Diagnosis Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disease_trends as $trend): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($trend['disease_name']); ?></td>
                        <td><?php echo $trend['count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>