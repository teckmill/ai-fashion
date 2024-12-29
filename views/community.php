<?php
require_once '../includes/functions.php';
require_once '../includes/community.php';

// Ensure user is authenticated
if (!isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

$userId = getCurrentUserId();
$community = new Community($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Fashion - Community</title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/community.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="community-page">
        <!-- Post Creation Form -->
        <section class="post-form-container">
            <form id="post-form" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="title" placeholder="Title" required>
                </div>
                <div class="form-group">
                    <textarea name="description" placeholder="Share your outfit story..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="image" class="file-upload-label">
                        <i class="fas fa-camera"></i>
                        <span>Add Photo</span>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </label>
                </div>
                <div class="form-group">
                    <input type="text" name="tags" placeholder="Add tags (comma separated)">
                </div>
                <button type="submit" class="btn-primary">Share Outfit</button>
            </form>
        </section>

        <!-- Feed Filters -->
        <section class="feed-filters">
            <select id="feed-filter">
                <option value="all">All Posts</option>
                <option value="following">Following</option>
                <option value="trending">Trending</option>
            </select>
        </section>

        <!-- Feed Container -->
        <section id="feed-container" class="feed-container">
            <!-- Posts will be loaded here dynamically -->
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="/js/community.js"></script>
</body>
</html>
