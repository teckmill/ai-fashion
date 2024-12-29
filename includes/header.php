<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$auth = new Auth($pdo);
$currentUser = $auth->getCurrentUser();

// Get the relative path to root based on current script location
$root_path = str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1);
?>

<header class="main-header">
    <nav class="nav-container">
        <div class="logo">
            <a href="<?php echo $root_path; ?>index.php">AI Fashion</a>
        </div>
        
        <div class="nav-links">
            <?php if ($currentUser): ?>
                <a href="<?php echo $root_path; ?>views/designer.php">Design Outfit</a>
                <a href="<?php echo $root_path; ?>views/wardrobe.php">My Wardrobe</a>
                <a href="<?php echo $root_path; ?>views/community.php">Community</a>
                <a href="<?php echo $root_path; ?>views/analytics.php">Analytics</a>
                <div class="user-menu">
                    <img src="<?php echo $root_path . (isset($currentUser['profile_image']) ? $currentUser['profile_image'] : 'images/default-avatar.svg'); ?>" 
                         alt="Profile" 
                         class="profile-image-small">
                    <div class="dropdown-content">
                        <a href="<?php echo $root_path; ?>views/profile.php">Profile</a>
                        <a href="<?php echo $root_path; ?>views/settings.php">Settings</a>
                        <a href="<?php echo $root_path; ?>auth/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $root_path; ?>auth/login.php" class="btn-secondary">Log In</a>
                <a href="<?php echo $root_path; ?>auth/signup.php" class="btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
