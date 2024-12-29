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
    <nav class="main-nav">
        <div class="container nav-container">
            <a href="<?php echo SITE_URL; ?>" class="nav-logo">
                <i class="fas fa-tshirt"></i>
                <span>AI Fashion</span>
            </a>
            
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo SITE_URL; ?>/views/dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/views/wardrobe.php" class="nav-link">
                        <i class="fas fa-door-open"></i>
                        <span>Wardrobe</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/views/designer.php" class="nav-link">
                        <i class="fas fa-magic"></i>
                        <span>Designer</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/views/community.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Community</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="nav-auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                            <span>Account</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="<?php echo SITE_URL; ?>/views/profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>Profile</span>
                            </a>
                            <a href="<?php echo SITE_URL; ?>/views/settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="nav-link">Login</a>
                    <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
