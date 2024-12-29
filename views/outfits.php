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
$message = '';
$error = '';

// Handle outfit creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle image upload
        if (isset($_FILES['outfit_image']) && $_FILES['outfit_image']['error'] === UPLOAD_ERR_OK) {
            $filetype = strtolower(pathinfo($_FILES['outfit_image']['name'], PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($filetype, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
            }
            
            $newFilename = uniqid() . "." . $filetype;
            $uploadDir = "../uploads/outfits/";
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['outfit_image']['tmp_name'], $uploadPath)) {
                // Insert into posts table (used for outfits)
                $stmt = $pdo->prepare("
                    INSERT INTO posts (user_id, title, description, image_url)
                    VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $userId,
                    $_POST['title'] ?? '',
                    $_POST['description'] ?? '',
                    "/uploads/outfits/" . $newFilename
                ]);
                
                $message = "Outfit created successfully!";
            } else {
                throw new Exception("Failed to upload image.");
            }
        } else {
            throw new Exception("Please select an image for your outfit.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch user's outfits
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_image
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$userId]);
    $outfits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error loading outfits: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Outfits - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/outfits.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="outfits-page">
        <div class="container">
            <main class="outfits-container">
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <p><?php echo htmlspecialchars($message); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Create Outfit Section -->
                <section class="create-outfit">
                    <h2>Create New Outfit</h2>
                    <form action="" method="POST" enctype="multipart/form-data" class="outfit-form">
                        <div class="form-group">
                            <label for="title">Outfit Title</label>
                            <input type="text" id="title" name="title" required 
                                   placeholder="Give your outfit a name">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3" 
                                    placeholder="Describe your outfit..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="outfit_image">Outfit Image</label>
                            <input type="file" id="outfit_image" name="outfit_image" required 
                                   accept="image/jpeg,image/png,image/gif">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create Outfit
                        </button>
                    </form>
                </section>

                <!-- Outfits Grid -->
                <section class="outfits-grid">
                    <h2>My Outfits</h2>
                    
                    <?php if (empty($outfits)): ?>
                        <div class="no-outfits">
                            <i class="fas fa-tshirt"></i>
                            <h3>No outfits yet</h3>
                            <p>Start by creating your first outfit above!</p>
                        </div>
                    <?php else: ?>
                        <div class="outfits-list">
                            <?php foreach ($outfits as $outfit): ?>
                                <div class="outfit-card">
                                    <div class="outfit-image">
                                        <img src="<?php echo SITE_URL . $outfit['image_url']; ?>" 
                                             alt="<?php echo htmlspecialchars($outfit['title']); ?>">
                                    </div>
                                    <div class="outfit-info">
                                        <h3><?php echo htmlspecialchars($outfit['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($outfit['description']); ?></p>
                                        <time datetime="<?php echo $outfit['created_at']; ?>">
                                            <?php echo date('M j, Y', strtotime($outfit['created_at'])); ?>
                                        </time>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
