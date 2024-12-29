<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$auth = new Auth($pdo);
$currentUser = null;

// Get current user from session
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user in header: " . $e->getMessage());
    }
}

// Check if we're on an auth page
$isAuthPage = strpos($_SERVER['PHP_SELF'], '/auth/') !== false;
$headerClass = $isAuthPage ? 'main-header auth-header' : 'main-header';
?>

<header class="<?php echo $headerClass; ?>">
    <nav class="nav-container">
        <div class="logo">
            <a href="<?php echo SITE_URL; ?>/index.php">AI Fashion</a>
        </div>
        
        <div class="nav-links">
            <?php if ($currentUser): ?>
                <a href="<?php echo SITE_URL; ?>/views/designer.php">Design Outfit</a>
                <a href="<?php echo SITE_URL; ?>/views/wardrobe.php">My Wardrobe</a>
                <a href="<?php echo SITE_URL; ?>/views/community.php">Community</a>
                <a href="<?php echo SITE_URL; ?>/views/analytics.php">Analytics</a>
                <div class="user-menu">
                    <img src="<?php echo SITE_URL . ($currentUser['profile_image'] ?? '/images/default-avatar.svg'); ?>" 
                         alt="Profile" 
                         class="profile-image-small">
                    <div class="dropdown-content">
                        <a href="<?php echo SITE_URL; ?>/views/profile.php">Profile</a>
                        <a href="<?php echo SITE_URL; ?>/views/settings.php">Settings</a>
                        <a href="<?php echo SITE_URL; ?>/auth/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!$isAuthPage): ?>
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn-secondary">Log In</a>
                    <a href="<?php echo SITE_URL; ?>/auth/signup.php" class="btn-primary">Sign Up</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </nav>
</header>
