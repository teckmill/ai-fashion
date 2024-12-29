<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth = new Auth($pdo);
$error = '';
$success = false;

$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("Location: /auth/forgot-password.php");
    exit;
}

$userId = $auth->validatePasswordReset($token);
if (!$userId) {
    $error = "Invalid or expired reset token";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password)) {
        $error = "Please enter a new password";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        if ($auth->updatePassword($userId, $password)) {
            $success = true;
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - AI Fashion</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Reset Password</h1>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <p>Your password has been successfully reset!</p>
                    <p><a href="/auth/login.php">Click here to log in</a></p>
                </div>
            <?php elseif ($userId): ?>
                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required minlength="8">
                        <small>Must be at least 8 characters long</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn-primary">Reset Password</button>
                </form>
            <?php else: ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <div class="auth-links">
                    <p><a href="/auth/forgot-password.php">Request a new reset link</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
