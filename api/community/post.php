<?php
require_once '../../includes/functions.php';
require_once '../../includes/community.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = getCurrentUserId();
$community = new Community($userId);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        try {
            // Handle file upload
            $imageUrl = handleImageUpload();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $title = $data['title'] ?? '';
            $description = $data['description'] ?? '';
            $tags = $data['tags'] ?? [];

            $postId = $community->createPost($title, $description, $imageUrl, $tags);
            
            echo json_encode([
                'success' => true,
                'post_id' => $postId,
                'message' => 'Post created successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'GET':
        $postId = $_GET['id'] ?? null;
        if ($postId) {
            $post = $community->getPostDetails($postId);
            if ($post) {
                $post['comments'] = $community->getComments($postId);
                echo json_encode(['success' => true, 'post' => $post]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Post not found']);
            }
        } else {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $filter = $_GET['filter'] ?? 'all';
            
            $posts = $community->getFeed($page, $limit, $filter);
            echo json_encode(['success' => true, 'posts' => $posts]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleImageUpload() {
    if (!isset($_FILES['image'])) {
        throw new Exception('No image file uploaded');
    }

    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type');
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large');
    }

    $uploadDir = '../../uploads/posts/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to save file');
    }

    return '/uploads/posts/' . $filename;
}
