<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

// Get current user from session
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user in footer: " . $e->getMessage());
    }
}

// Check if we're on an auth page
$isAuthPage = strpos($_SERVER['PHP_SELF'], '/auth/') !== false;
$footerClass = $isAuthPage ? 'main-footer auth-footer' : 'main-footer';
?>
<footer class="<?php echo $footerClass; ?>">
    <div class="footer-content">
        <div class="footer-section">
            <h3>AI Fashion</h3>
            <p>Your personal style assistant powered by artificial intelligence.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <?php if ($currentUser): ?>
                    <li><a href="<?php echo SITE_URL; ?>/views/designer.php">Design Outfit</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/views/wardrobe.php">My Wardrobe</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/views/community.php">Community</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/views/analytics.php">Analytics</a></li>
                <?php else: ?>
                    <?php if (!$isAuthPage): ?>
                        <li><a href="<?php echo SITE_URL; ?>/auth/signup.php">Sign Up</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/auth/login.php">Log In</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITE_URL; ?>/auth/forgot-password.php">Forgot Password</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact</h3>
            <ul>
                <li><a href="mailto:support@aifashion.com">support@aifashion.com</a></li>
                <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact Us</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> AI Fashion. All rights reserved.</p>
    </div>
</footer>
