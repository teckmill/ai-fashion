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

// Fetch user data
try {
    $stmt = $pdo->prepare("
        SELECT id, username, email, profile_image, notification_preferences, theme_preference, privacy_settings
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION = array();
        session_destroy();
        header("Location: " . SITE_URL . "/auth/login.php");
        exit;
    }

    // Initialize settings if they don't exist
    $notificationPrefs = json_decode($user['notification_preferences'] ?? '{}', true) ?: [
        'email_notifications' => true,
        'outfit_suggestions' => true,
        'community_updates' => true,
        'trend_alerts' => true
    ];
    
    $themePreference = $user['theme_preference'] ?? 'dark';
    
    $privacySettings = json_decode($user['privacy_settings'] ?? '{}', true) ?: [
        'profile_visibility' => 'public',
        'wardrobe_visibility' => 'friends',
        'outfit_visibility' => 'public'
    ];

} catch (PDOException $e) {
    error_log("Error in settings.php: " . $e->getMessage());
    $error = "An error occurred while loading your settings.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update notification preferences
        if (isset($_POST['notification_preferences'])) {
            $notificationPrefs = [
                'email_notifications' => isset($_POST['email_notifications']),
                'outfit_suggestions' => isset($_POST['outfit_suggestions']),
                'community_updates' => isset($_POST['community_updates']),
                'trend_alerts' => isset($_POST['trend_alerts'])
            ];
            
            $stmt = $pdo->prepare("
                UPDATE users 
                SET notification_preferences = ? 
                WHERE id = ?
            ");
            $stmt->execute([json_encode($notificationPrefs), $userId]);
        }

        // Update theme preference
        if (isset($_POST['theme'])) {
            $themePreference = $_POST['theme'];
            $stmt = $pdo->prepare("
                UPDATE users 
                SET theme_preference = ? 
                WHERE id = ?
            ");
            $stmt->execute([$themePreference, $userId]);
        }

        // Update privacy settings
        if (isset($_POST['privacy_settings'])) {
            $privacySettings = [
                'profile_visibility' => $_POST['profile_visibility'],
                'wardrobe_visibility' => $_POST['wardrobe_visibility'],
                'outfit_visibility' => $_POST['outfit_visibility']
            ];
            
            $stmt = $pdo->prepare("
                UPDATE users 
                SET privacy_settings = ? 
                WHERE id = ?
            ");
            $stmt->execute([json_encode($privacySettings), $userId]);
        }

        $success = "Settings updated successfully!";
    } catch (PDOException $e) {
        error_log("Error updating settings: " . $e->getMessage());
        $error = "An error occurred while saving your settings.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/settings.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="settings-page">
        <div class="container">
            <main class="settings-container">
                <h1>Settings</h1>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>

                <div class="settings-grid">
                    <!-- Notification Settings -->
                    <form method="POST" class="settings-card">
                        <h2><i class="fas fa-bell"></i> Notification Preferences</h2>
                        <div class="settings-content">
                            <label class="switch-label">
                                <input type="checkbox" name="email_notifications" 
                                    <?php echo $notificationPrefs['email_notifications'] ? 'checked' : ''; ?>>
                                <span class="switch-text">Email Notifications</span>
                            </label>
                            <label class="switch-label">
                                <input type="checkbox" name="outfit_suggestions" 
                                    <?php echo $notificationPrefs['outfit_suggestions'] ? 'checked' : ''; ?>>
                                <span class="switch-text">Outfit Suggestions</span>
                            </label>
                            <label class="switch-label">
                                <input type="checkbox" name="community_updates" 
                                    <?php echo $notificationPrefs['community_updates'] ? 'checked' : ''; ?>>
                                <span class="switch-text">Community Updates</span>
                            </label>
                            <label class="switch-label">
                                <input type="checkbox" name="trend_alerts" 
                                    <?php echo $notificationPrefs['trend_alerts'] ? 'checked' : ''; ?>>
                                <span class="switch-text">Trend Alerts</span>
                            </label>
                            <input type="hidden" name="notification_preferences" value="1">
                            <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                        </div>
                    </form>

                    <!-- Theme Settings -->
                    <form method="POST" class="settings-card">
                        <h2><i class="fas fa-paint-brush"></i> Theme Settings</h2>
                        <div class="settings-content">
                            <div class="theme-options">
                                <label class="theme-option">
                                    <input type="radio" name="theme" value="dark" 
                                        <?php echo $themePreference === 'dark' ? 'checked' : ''; ?>>
                                    <span class="theme-preview dark">
                                        <i class="fas fa-moon"></i>
                                        Dark Theme
                                    </span>
                                </label>
                                <label class="theme-option">
                                    <input type="radio" name="theme" value="light" 
                                        <?php echo $themePreference === 'light' ? 'checked' : ''; ?>>
                                    <span class="theme-preview light">
                                        <i class="fas fa-sun"></i>
                                        Light Theme
                                    </span>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Theme Settings</button>
                        </div>
                    </form>

                    <!-- Privacy Settings -->
                    <form method="POST" class="settings-card">
                        <h2><i class="fas fa-shield-alt"></i> Privacy Settings</h2>
                        <div class="settings-content">
                            <div class="form-group">
                                <label>Profile Visibility</label>
                                <select name="profile_visibility">
                                    <option value="public" <?php echo $privacySettings['profile_visibility'] === 'public' ? 'selected' : ''; ?>>Public</option>
                                    <option value="friends" <?php echo $privacySettings['profile_visibility'] === 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                                    <option value="private" <?php echo $privacySettings['profile_visibility'] === 'private' ? 'selected' : ''; ?>>Private</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Wardrobe Visibility</label>
                                <select name="wardrobe_visibility">
                                    <option value="public" <?php echo $privacySettings['wardrobe_visibility'] === 'public' ? 'selected' : ''; ?>>Public</option>
                                    <option value="friends" <?php echo $privacySettings['wardrobe_visibility'] === 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                                    <option value="private" <?php echo $privacySettings['wardrobe_visibility'] === 'private' ? 'selected' : ''; ?>>Private</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Outfit Visibility</label>
                                <select name="outfit_visibility">
                                    <option value="public" <?php echo $privacySettings['outfit_visibility'] === 'public' ? 'selected' : ''; ?>>Public</option>
                                    <option value="friends" <?php echo $privacySettings['outfit_visibility'] === 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                                    <option value="private" <?php echo $privacySettings['outfit_visibility'] === 'private' ? 'selected' : ''; ?>>Private</option>
                                </select>
                            </div>
                            <input type="hidden" name="privacy_settings" value="1">
                            <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
                        </div>
                    </form>

                    <!-- Account Settings -->
                    <div class="settings-card">
                        <h2><i class="fas fa-user-cog"></i> Account Settings</h2>
                        <div class="settings-content">
                            <a href="<?php echo SITE_URL; ?>/views/profile.php" class="btn btn-secondary">
                                <i class="fas fa-user"></i>
                                Edit Profile
                            </a>
                            <a href="<?php echo SITE_URL; ?>/auth/change-password.php" class="btn btn-secondary">
                                <i class="fas fa-key"></i>
                                Change Password
                            </a>
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()">
                                <i class="fas fa-trash-alt"></i>
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
    function confirmDeleteAccount() {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            window.location.href = '<?php echo SITE_URL; ?>/auth/delete-account.php';
        }
    }
    </script>
</body>
</html>
