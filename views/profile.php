<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize Auth class
$auth = new Auth($pdo);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

// Fetch user data
try {
    $stmt = $pdo->prepare("
        SELECT id, username, email, profile_image, bio, style_preferences, created_at
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
} catch (Exception $e) {
    error_log("Error in profile.php: " . $e->getMessage());
    $errors[] = "Error fetching user data. Please try again.";
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $bio = trim($_POST['bio'] ?? '');
    $style_preferences = trim($_POST['style_preferences'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($filetype, $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed";
        } else {
            $newFilename = uniqid() . "." . $filetype;
            $uploadPath = "../uploads/profile_images/" . $newFilename;
            
            // Create directory if it doesn't exist
            if (!file_exists("../uploads/profile_images")) {
                mkdir("../uploads/profile_images", 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                // Update profile image in database
                $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->execute(["/uploads/profile_images/" . $newFilename, $userId]);
                $user['profile_image'] = "/uploads/profile_images/" . $newFilename;
            } else {
                $errors[] = "Failed to upload profile image";
            }
        }
    }

    // Update profile information
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET bio = ?, style_preferences = ?
                WHERE id = ?
            ");
            $stmt->execute([$bio, $style_preferences, $userId]);
            $user['bio'] = $bio;
            $user['style_preferences'] = $style_preferences;
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Failed to update profile";
            error_log($e->getMessage());
        }
    }

    // Handle password change
    if (!empty($current_password) && !empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $stored_hash = $stmt->fetchColumn();

                if (password_verify($current_password, $stored_hash)) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$new_hash, $userId]);
                    $success = true;
                } else {
                    $errors[] = "Current password is incorrect";
                }
            } catch (PDOException $e) {
                $errors[] = "Failed to update password";
                error_log($e->getMessage());
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/profile.css">
</head>
<body class="profile-page">
    <?php include '../includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-cover"></div>
            <div class="profile-avatar">
                <img src="<?php echo SITE_URL . ($user['profile_image'] ?? '/images/default-avatar.svg'); ?>" 
                     alt="<?php echo htmlspecialchars($user['username']); ?>'s profile picture">
                <form id="avatar-form" method="POST" enctype="multipart/form-data" class="avatar-upload">
                    <label for="profile_image" class="avatar-upload-label">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
                </form>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="member-since">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <p>Profile updated successfully!</p>
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

        <div class="profile-content">
            <div class="profile-section">
                <h2>Profile Information</h2>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <div class="input-group">
                            <i class="fas fa-pen"></i>
                            <textarea id="bio" name="bio" rows="4" 
                                    placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="style_preferences">Style Preferences</label>
                        <div class="input-group">
                            <i class="fas fa-tshirt"></i>
                            <textarea id="style_preferences" name="style_preferences" rows="4" 
                                    placeholder="Describe your fashion preferences..."><?php echo htmlspecialchars($user['style_preferences'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary btn-large">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <div class="profile-section">
                <h2>Change Password</h2>
                <form method="POST" class="password-form">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="current_password" name="current_password" 
                                   placeholder="Enter your current password">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-group">
                            <i class="fas fa-key"></i>
                            <input type="password" id="new_password" name="new_password" 
                                   placeholder="Enter your new password">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-group">
                            <i class="fas fa-key"></i>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm your new password">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary btn-large">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Handle profile image upload
        document.getElementById('profile_image').addEventListener('change', function() {
            document.getElementById('avatar-form').submit();
        });
    </script>
</body>
</html>
