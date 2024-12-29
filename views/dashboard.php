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

// Initialize variables
$wardrobeStats = [
    'total_items' => 0,
    'categories' => 0,
    'colors' => 0
];
$recentOutfits = [];
$styleTips = [
    [
        'icon' => 'lightbulb',
        'title' => 'Color Coordination',
        'tip' => 'Try pairing complementary colors for a balanced look'
    ],
    [
        'icon' => 'star',
        'title' => 'Seasonal Transition',
        'tip' => 'Layer light pieces for versatile outfits'
    ],
    [
        'icon' => 'magic',
        'title' => 'Accessorizing',
        'tip' => 'Add personality with statement accessories'
    ]
];

// Fetch user data and statistics
try {
    // Get user info
    $stmt = $pdo->prepare("
        SELECT username, email, profile_image
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get wardrobe statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_items,
            COUNT(DISTINCT category) as categories,
            COUNT(DISTINCT color) as colors
        FROM wardrobe 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($stats) {
        $wardrobeStats = $stats;
    }

    // Get recent outfits
    $stmt = $pdo->prepare("
        SELECT id, title as name, image_url, created_at
        FROM posts
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 4
    ");
    $stmt->execute([$userId]);
    $recentOutfits = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = "An error occurred while loading your dashboard: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/dashboard.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="dashboard-page">
        <div class="container">
            <main class="dashboard-container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="welcome-content">
                        <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                        <p>Let's create your perfect outfit today</p>
                    </div>
                    <div class="quick-actions">
                        <a href="<?php echo SITE_URL; ?>/views/wardrobe.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add Item
                        </a>
                        <a href="<?php echo SITE_URL; ?>/views/outfits.php" class="btn btn-secondary">
                            <i class="fas fa-tshirt"></i>
                            Create Outfit
                        </a>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Total Items</h3>
                            <p class="stat-value"><?php echo number_format((int)$wardrobeStats['total_items']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Categories</h3>
                            <p class="stat-value"><?php echo number_format((int)$wardrobeStats['categories']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Colors</h3>
                            <p class="stat-value"><?php echo number_format((int)$wardrobeStats['colors']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="content-grid">
                    <!-- Recent Outfits -->
                    <section class="recent-outfits">
                        <div class="section-header">
                            <h2>Recent Outfits</h2>
                            <a href="<?php echo SITE_URL; ?>/views/outfits.php" class="btn btn-text">View All</a>
                        </div>
                        <div class="outfits-grid">
                            <?php if (!empty($recentOutfits)): ?>
                                <?php foreach ($recentOutfits as $outfit): ?>
                                    <div class="outfit-card">
                                        <div class="outfit-image">
                                            <img src="<?php echo SITE_URL . ($outfit['image_url'] ?? '/images/default-outfit.png'); ?>" 
                                                 alt="<?php echo htmlspecialchars($outfit['name']); ?>">
                                        </div>
                                        <div class="outfit-info">
                                            <h3><?php echo htmlspecialchars($outfit['name']); ?></h3>
                                            <time datetime="<?php echo $outfit['created_at']; ?>">
                                                <?php echo date('M j, Y', strtotime($outfit['created_at'])); ?>
                                            </time>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-outfits">
                                    <i class="fas fa-tshirt"></i>
                                    <h3>No outfits yet</h3>
                                    <p>Start creating your perfect combinations!</p>
                                    <a href="<?php echo SITE_URL; ?>/views/outfits.php" class="btn btn-primary">
                                        Create Outfit
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Style Tips -->
                    <section class="style-tips">
                        <div class="section-header">
                            <h2>Style Tips</h2>
                        </div>
                        <div class="tips-grid">
                            <?php foreach ($styleTips as $tip): ?>
                                <div class="tip-card">
                                    <div class="tip-icon">
                                        <i class="fas fa-<?php echo $tip['icon']; ?>"></i>
                                    </div>
                                    <div class="tip-content">
                                        <h3><?php echo htmlspecialchars($tip['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($tip['tip']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
