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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $bio = trim($_POST['bio']);
        
        // Basic validation
        if (empty($username) || empty($email)) {
            throw new Exception("Username and email are required fields.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        // Handle profile image upload
        $profileImage = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                throw new Exception("Only JPG, PNG and GIF images are allowed.");
            }
            
            if ($_FILES['profile_image']['size'] > $maxSize) {
                throw new Exception("File size must be less than 5MB.");
            }
            
            $uploadDir = '../uploads/profile/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = uniqid('profile_') . '_' . basename($_FILES['profile_image']['name']);
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                $profileImage = '/uploads/profile/' . $fileName;
            } else {
                throw new Exception("Failed to upload profile image.");
            }
        }

        // Update user data
        $sql = "UPDATE users SET username = ?, email = ?, bio = ?";
        $params = [$username, $email, $bio];
        
        if ($profileImage) {
            $sql .= ", profile_image = ?";
            $params[] = $profileImage;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $userId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $success = "Profile updated successfully!";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch user data
try {
    $stmt = $pdo->prepare("
        SELECT id, username, email, bio, profile_image, created_at
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Unable to load profile data. Please try logging in again.");
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/profile.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="profile-page">
        <div class="container">
            <main class="profile-container">
                <h1>Profile Settings</h1>

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

                <?php if (isset($user)): ?>
                    <form method="POST" enctype="multipart/form-data" class="profile-form">
                        <div class="profile-image-section">
                            <div class="profile-image-container">
                                <img src="<?php echo SITE_URL . ($user['profile_image'] ?? '/images/default-avatar.png'); ?>" 
                                     alt="Profile" 
                                     id="profile-preview">
                                <label for="profile_image" class="profile-image-upload">
                                    <i class="fas fa-camera"></i>
                                    <span>Change Photo</span>
                                </label>
                                <input type="file" 
                                       id="profile_image" 
                                       name="profile_image" 
                                       accept="image/*" 
                                       onchange="previewImage(this)">
                            </div>
                            <p class="profile-image-help">Click to upload a new profile photo</p>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" 
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       required>
                            </div>

                            <div class="form-group full-width">
                                <label for="bio">Bio</label>
                                <textarea id="bio" 
                                          name="bio" 
                                          rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>

                    <div class="profile-info">
                        <p class="member-since">
                            <i class="fas fa-calendar"></i>
                            Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('profile-preview').src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>
