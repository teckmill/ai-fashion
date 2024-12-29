<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$auth = new Auth($pdo);
$currentUser = $auth->getCurrentUser();
?>

<header class="main-header">
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
                    <img src="<?php echo SITE_URL . (isset($currentUser['profile_image']) ? $currentUser['profile_image'] : '/images/default-avatar.svg'); ?>" 
                         alt="Profile" 
                         class="profile-image-small">
                    <div class="dropdown-content">
                        <a href="<?php echo SITE_URL; ?>/views/profile.php">Profile</a>
                        <a href="<?php echo SITE_URL; ?>/views/settings.php">Settings</a>
                        <a href="<?php echo SITE_URL; ?>/auth/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn-secondary">Log In</a>
                <a href="<?php echo SITE_URL; ?>/auth/signup.php" class="btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
