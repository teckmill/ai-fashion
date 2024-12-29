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

// Fetch community posts from database
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_image
        FROM posts p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 20
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_content'])) {
    try {
        $content = trim($_POST['post_content']);
        if (empty($content)) {
            throw new Exception("Post content cannot be empty");
        }

        // Handle image upload if present
        $imageUrl = null;
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['post_image']['type'], $allowedTypes)) {
                throw new Exception("Only JPG, PNG and GIF images are allowed");
            }

            $uploadDir = '../uploads/posts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid('post_') . '_' . basename($_FILES['post_image']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $uploadPath)) {
                $imageUrl = '/uploads/posts/' . $fileName;
            }
        }

        // Insert new post
        $stmt = $pdo->prepare("
            INSERT INTO posts (user_id, content, image_url, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $content, $imageUrl]);

        // Redirect to avoid form resubmission
        header("Location: " . SITE_URL . "/views/community.php");
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/community.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="community-page">
        <div class="container">
            <main class="community-container">
                <h1>Community</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <!-- New Post Form -->
                <div class="new-post-card">
                    <form method="POST" enctype="multipart/form-data" class="new-post-form">
                        <div class="form-group">
                            <textarea name="post_content" 
                                      placeholder="Share your fashion thoughts..."
                                      rows="3"
                                      required></textarea>
                        </div>
                        <div class="form-actions">
                            <label class="btn btn-secondary">
                                <i class="fas fa-image"></i>
                                Add Image
                                <input type="file" 
                                       name="post_image" 
                                       accept="image/*" 
                                       style="display: none;">
                            </label>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Post
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Posts Feed -->
                <div class="posts-feed">
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <article class="post-card">
                                <div class="post-header">
                                    <img src="<?php echo SITE_URL . ($post['profile_image'] ?? '/images/default-avatar.png'); ?>" 
                                         alt="<?php echo htmlspecialchars($post['username']); ?>" 
                                         class="post-avatar">
                                    <div class="post-meta">
                                        <h3><?php echo htmlspecialchars($post['username']); ?></h3>
                                        <time datetime="<?php echo $post['created_at']; ?>">
                                            <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                        </time>
                                    </div>
                                </div>
                                
                                <div class="post-content">
                                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                    <?php if ($post['image_url']): ?>
                                        <img src="<?php echo SITE_URL . htmlspecialchars($post['image_url']); ?>" 
                                             alt="Post image" 
                                             class="post-image">
                                    <?php endif; ?>
                                </div>

                                <div class="post-actions">
                                    <button class="btn-action">
                                        <i class="far fa-heart"></i>
                                        <span>Like</span>
                                    </button>
                                    <button class="btn-action">
                                        <i class="far fa-comment"></i>
                                        <span>Comment</span>
                                    </button>
                                    <button class="btn-action">
                                        <i class="far fa-share-square"></i>
                                        <span>Share</span>
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-posts">
                            <i class="fas fa-users"></i>
                            <h2>No posts yet</h2>
                            <p>Be the first to share your fashion journey!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
