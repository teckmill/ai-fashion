<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth = new Auth($pdo);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate input
    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password";
    } else {
        try {
            if ($auth->login($email, $password, $remember)) {
                // Redirect to home page
                header("Location: " . SITE_URL);
                exit;
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Login failed. Please try again.";
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/auth.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Enter your credentials to access your account</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required
                               value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="form-group-inline">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/auth/forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Log In
                </button>
            </form>

            <div class="social-login">
                <div class="social-login-divider">
                    <span>Or continue with</span>
                </div>
                <div class="social-buttons">
                    <a href="#" class="social-button">
                        <i class="fab fa-google"></i>
                    </a>
                    <a href="#" class="social-button">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-button">
                        <i class="fab fa-apple"></i>
                    </a>
                </div>
            </div>

            <div class="auth-links">
                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/auth/signup.php">Sign Up</a></p>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
