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
<footer class="main-footer">
    <div class="container footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">AI Fashion</h3>
                <p class="footer-description">Your personal AI-powered fashion assistant helping you look your best every day.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
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
                    <li><a href="<?php echo SITE_URL; ?>/views/about.php">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/views/contact.php">Contact</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/views/faq.php">FAQ</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/blog">Blog</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Legal</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/privacy-policy">Privacy Policy</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/terms">Terms of Service</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/cookie-policy">Cookie Policy</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Newsletter</h4>
                <p>Stay updated with the latest fashion trends and tips.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> AI Fashion. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown menu functionality
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            menu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    });
});
</script>
