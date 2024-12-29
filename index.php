<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth($pdo);
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Fashion - Your Personal Style Assistant</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Your Personal AI Fashion Assistant</h1>
                <p>Get personalized outfit recommendations, manage your wardrobe, and connect with fashion enthusiasts.</p>
                <?php if (!$user): ?>
                    <div class="cta-buttons">
                        <a href="auth/signup.php" class="btn-primary">Get Started</a>
                        <a href="auth/login.php" class="btn-secondary">Log In</a>
                    </div>
                <?php else: ?>
                    <div class="cta-buttons">
                        <a href="views/designer.php" class="btn-primary">Design Outfit</a>
                        <a href="views/community.php" class="btn-secondary">Explore Community</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="features">
            <h2>Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-tshirt"></i>
                    <h3>Smart Wardrobe</h3>
                    <p>Organize and manage your clothing items with AI-powered categorization.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-magic"></i>
                    <h3>Style Recommendations</h3>
                    <p>Get personalized outfit suggestions based on your style preferences and occasions.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Community</h3>
                    <p>Share your outfits and get inspired by other fashion enthusiasts.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Analytics</h3>
                    <p>Track your style evolution and get insights about your fashion choices.</p>
                </div>
            </div>
        </section>

        <?php if (!$user): ?>
        <section class="cta">
            <h2>Join Our Fashion Community</h2>
            <p>Create your account today and start your fashion journey!</p>
            <div class="cta-buttons">
                <a href="auth/signup.php" class="btn-primary">Sign Up Now</a>
                <a href="auth/login.php" class="btn-secondary">Already have an account?</a>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
