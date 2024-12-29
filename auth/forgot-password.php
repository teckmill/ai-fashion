<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth = new Auth($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Please enter your email address";
    } else {
        $token = $auth->createPasswordReset($email);
        
        if ($token) {
            // In a production environment, you would send an email here
            // For now, we'll just create a direct link
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/auth/reset-password.php?token=" . $token;
            $message = "Password reset link has been sent to your email address. For demo purposes, click here: <a href='$resetLink'>Reset Password</a>";
        } else {
            $error = "If an account exists with this email, you will receive a password reset link";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AI Fashion</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Reset Password</h1>
            
            <?php if ($message): ?>
                <div class="success-message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <button type="submit" class="btn-primary">Send Reset Link</button>
            </form>

            <div class="auth-links">
                <p>Remember your password? <a href="/auth/login.php">Log In</a></p>
                <p>Don't have an account? <a href="/auth/signup.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</body>
</html>
