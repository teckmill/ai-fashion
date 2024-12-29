<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch analytics data
try {
    // Get wardrobe statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_items,
            COUNT(DISTINCT category) as categories,
            COUNT(DISTINCT color) as colors,
            COUNT(DISTINCT brand) as brands
        FROM wardrobe_items 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $wardrobeStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get outfit statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_outfits,
            COUNT(DISTINCT style) as styles,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_outfits
        FROM outfits 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $outfitStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get most used categories
    $stmt = $pdo->prepare("
        SELECT category, COUNT(*) as count
        FROM wardrobe_items
        WHERE user_id = ?
        GROUP BY category
        ORDER BY count DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $topCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get most used colors
    $stmt = $pdo->prepare("
        SELECT color, COUNT(*) as count
        FROM wardrobe_items
        WHERE user_id = ?
        GROUP BY color
        ORDER BY count DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $topColors = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/analytics.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="analytics-page">
        <div class="container">
            <main class="analytics-container">
                <h1>Fashion Analytics</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Overview Cards -->
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="card-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total Items</h3>
                            <p class="card-value"><?php echo number_format($wardrobeStats['total_items']); ?></p>
                        </div>
                    </div>

                    <div class="analytics-card">
                        <div class="card-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div class="card-content">
                            <h3>Unique Colors</h3>
                            <p class="card-value"><?php echo number_format($wardrobeStats['colors']); ?></p>
                        </div>
                    </div>

                    <div class="analytics-card">
                        <div class="card-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="card-content">
                            <h3>Categories</h3>
                            <p class="card-value"><?php echo number_format($wardrobeStats['categories']); ?></p>
                        </div>
                    </div>

                    <div class="analytics-card">
                        <div class="card-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="card-content">
                            <h3>Brands</h3>
                            <p class="card-value"><?php echo number_format($wardrobeStats['brands']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <!-- Categories Chart -->
                    <div class="chart-card">
                        <h3>Top Categories</h3>
                        <canvas id="categoriesChart"></canvas>
                    </div>

                    <!-- Colors Chart -->
                    <div class="chart-card">
                        <h3>Color Distribution</h3>
                        <canvas id="colorsChart"></canvas>
                    </div>
                </div>

                <!-- Style Analysis -->
                <div class="style-analysis">
                    <h2>Style Analysis</h2>
                    <div class="style-grid">
                        <div class="style-card">
                            <h3>Total Outfits</h3>
                            <p class="style-value"><?php echo number_format($outfitStats['total_outfits']); ?></p>
                        </div>
                        <div class="style-card">
                            <h3>Unique Styles</h3>
                            <p class="style-value"><?php echo number_format($outfitStats['styles']); ?></p>
                        </div>
                        <div class="style-card">
                            <h3>Recent Outfits</h3>
                            <p class="style-value"><?php echo number_format($outfitStats['recent_outfits']); ?></p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
    // Prepare chart data
    const categoryLabels = <?php echo json_encode(array_column($topCategories, 'category')); ?>;
    const categoryData = <?php echo json_encode(array_column($topCategories, 'count')); ?>;
    const colorLabels = <?php echo json_encode(array_column($topColors, 'color')); ?>;
    const colorData = <?php echo json_encode(array_column($topColors, 'count')); ?>;

    // Categories Chart
    new Chart(document.getElementById('categoriesChart'), {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Items per Category',
                data: categoryData,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Colors Chart
    new Chart(document.getElementById('colorsChart'), {
        type: 'doughnut',
        data: {
            labels: colorLabels,
            datasets: [{
                data: colorData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>
</body>
</html>
