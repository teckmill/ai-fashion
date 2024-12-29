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
$errors = [];
$success = false;
$outfits = [];

// Handle outfit generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $occasion = $_POST['occasion'] ?? '';
    $style = $_POST['style'] ?? '';
    $weather = $_POST['weather'] ?? '';
    
    // TODO: Integrate with AI for outfit generation
    // For now, just show a success message
    $success = true;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Fashion Designer - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/designer.css">
</head>
<body class="designer-page">
    <?php require_once '../includes/header.php'; ?>

    <div class="designer-container">
        <div class="designer-header">
            <h1>AI Fashion Designer</h1>
            <p>Let our AI help you create the perfect outfit for any occasion</p>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <p>Outfit generated successfully!</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="designer-content">
            <div class="designer-form-section">
                <form method="POST" class="designer-form">
                    <div class="form-group">
                        <label for="occasion">Occasion</label>
                        <div class="input-group">
                            <i class="fas fa-calendar"></i>
                            <select id="occasion" name="occasion" required>
                                <option value="">Select Occasion</option>
                                <option value="casual">Casual</option>
                                <option value="work">Work</option>
                                <option value="formal">Formal</option>
                                <option value="party">Party</option>
                                <option value="date">Date Night</option>
                                <option value="workout">Workout</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="style">Style Preference</label>
                        <div class="input-group">
                            <i class="fas fa-tshirt"></i>
                            <select id="style" name="style" required>
                                <option value="">Select Style</option>
                                <option value="classic">Classic</option>
                                <option value="modern">Modern</option>
                                <option value="minimalist">Minimalist</option>
                                <option value="bohemian">Bohemian</option>
                                <option value="streetwear">Streetwear</option>
                                <option value="preppy">Preppy</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="weather">Weather</label>
                        <div class="input-group">
                            <i class="fas fa-cloud-sun"></i>
                            <select id="weather" name="weather" required>
                                <option value="">Select Weather</option>
                                <option value="hot">Hot</option>
                                <option value="warm">Warm</option>
                                <option value="mild">Mild</option>
                                <option value="cool">Cool</option>
                                <option value="cold">Cold</option>
                                <option value="rainy">Rainy</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary btn-large">
                        <i class="fas fa-magic"></i> Generate Outfit
                    </button>
                </form>
            </div>

            <div class="outfit-display">
                <div class="outfit-placeholder">
                    <i class="fas fa-tshirt"></i>
                    <p>Your AI-generated outfit will appear here</p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
