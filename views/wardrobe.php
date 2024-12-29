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
$clothes = [];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['clothing_image']) && $_FILES['clothing_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['clothing_image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($filetype, $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed";
        } else {
            $newFilename = uniqid() . "." . $filetype;
            $uploadDir = "../uploads/wardrobe/";
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['clothing_image']['tmp_name'], $uploadPath)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO wardrobe (user_id, name, category, color, season, image_path, description)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $userId,
                        $_POST['name'] ?? '',
                        $_POST['category'] ?? '',
                        $_POST['color'] ?? '',
                        $_POST['season'] ?? '',
                        "/uploads/wardrobe/" . $newFilename,
                        $_POST['description'] ?? ''
                    ]);
                    
                    $success = true;
                } catch (PDOException $e) {
                    $errors[] = "Failed to save clothing item";
                    error_log($e->getMessage());
                }
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
}

// Fetch user's wardrobe
try {
    $stmt = $pdo->prepare("
        SELECT * FROM wardrobe 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $clothes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Failed to fetch wardrobe items";
    error_log($e->getMessage());
}

// Get categories for dropdown
$categories = ['Tops', 'Bottoms', 'Dresses', 'Outerwear', 'Shoes', 'Accessories'];
$seasons = ['Spring', 'Summer', 'Fall', 'Winter', 'All Seasons'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Wardrobe - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/wardrobe.css">
</head>
<body class="wardrobe-page">
    <?php require_once '../includes/header.php'; ?>

    <div class="wardrobe-container">
        <div class="wardrobe-header">
            <h1>My Wardrobe</h1>
            <button class="btn-primary" onclick="showAddClothingForm()">
                <i class="fas fa-plus"></i> Add New Item
            </button>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <p>Item added successfully!</p>
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

        <!-- Add Clothing Form (Hidden by default) -->
        <div id="addClothingForm" class="add-clothing-form" style="display: none;">
            <form method="POST" enctype="multipart/form-data" class="clothing-form">
                <div class="form-group">
                    <label for="name">Item Name</label>
                    <div class="input-group">
                        <i class="fas fa-tshirt"></i>
                        <input type="text" id="name" name="name" required 
                               placeholder="E.g., Blue Denim Jacket">
                    </div>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <div class="input-group">
                        <i class="fas fa-tags"></i>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="color">Color</label>
                    <div class="input-group">
                        <i class="fas fa-palette"></i>
                        <input type="text" id="color" name="color" required 
                               placeholder="E.g., Navy Blue">
                    </div>
                </div>

                <div class="form-group">
                    <label for="season">Season</label>
                    <div class="input-group">
                        <i class="fas fa-sun"></i>
                        <select id="season" name="season" required>
                            <option value="">Select Season</option>
                            <?php foreach ($seasons as $season): ?>
                                <option value="<?php echo htmlspecialchars($season); ?>">
                                    <?php echo htmlspecialchars($season); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <div class="input-group">
                        <i class="fas fa-align-left"></i>
                        <textarea id="description" name="description" rows="3" 
                                  placeholder="Add notes about the item..."></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label for="clothing_image">Upload Image</label>
                    <div class="input-group file-input">
                        <i class="fas fa-camera"></i>
                        <input type="file" id="clothing_image" name="clothing_image" 
                               accept="image/*" required>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Item
                    </button>
                    <button type="button" class="btn-secondary" onclick="hideAddClothingForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Wardrobe Grid -->
        <div class="wardrobe-grid">
            <?php if (empty($clothes)): ?>
                <div class="empty-wardrobe">
                    <i class="fas fa-tshirt"></i>
                    <p>Your wardrobe is empty. Add some items to get started!</p>
                </div>
            <?php else: ?>
                <?php foreach ($clothes as $item): ?>
                    <div class="clothing-item">
                        <div class="clothing-image">
                            <img src="<?php echo SITE_URL . htmlspecialchars($item['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="clothing-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="category">
                                <i class="fas fa-tags"></i> 
                                <?php echo htmlspecialchars($item['category']); ?>
                            </p>
                            <p class="color">
                                <i class="fas fa-palette"></i> 
                                <?php echo htmlspecialchars($item['color']); ?>
                            </p>
                            <p class="season">
                                <i class="fas fa-sun"></i> 
                                <?php echo htmlspecialchars($item['season']); ?>
                            </p>
                            <?php if (!empty($item['description'])): ?>
                                <p class="description">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        function showAddClothingForm() {
            document.getElementById('addClothingForm').style.display = 'block';
        }

        function hideAddClothingForm() {
            document.getElementById('addClothingForm').style.display = 'none';
        }

        // Preview image before upload
        document.getElementById('clothing_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'image-preview';
                    
                    const container = document.querySelector('.file-input');
                    const existingPreview = container.querySelector('.image-preview');
                    if (existingPreview) {
                        container.removeChild(existingPreview);
                    }
                    container.appendChild(preview);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
