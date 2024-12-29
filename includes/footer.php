<?php
require_once __DIR__ . '/config.php';
// Get the relative path to root based on current script location if not already set
if (!isset($root_path)) {
    $root_path = str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1);
}
?>
<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>AI Fashion</h3>
            <p>Your personal style assistant powered by artificial intelligence.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <?php if (isset($currentUser)): ?>
                    <li><a href="<?php echo SITE_URL; ?>/views/designer.php">Design Outfit</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/views/wardrobe.php">My Wardrobe</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/views/community.php">Community</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/views/analytics.php">Analytics</a></li>
                <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/auth/signup.php">Sign Up</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/auth/login.php">Log In</a></li>
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
