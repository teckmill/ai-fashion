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

$action = $_POST['action'] ?? '';
$postId = $_POST['post_id'] ?? null;

try {
    switch ($action) {
        case 'like':
            $result = $community->likePost($postId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Post liked' : 'Failed to like post'
            ]);
            break;

        case 'unlike':
            $result = $community->unlikePost($postId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Post unliked' : 'Failed to unlike post'
            ]);
            break;

        case 'comment':
            $content = $_POST['content'] ?? '';
            $parentId = $_POST['parent_id'] ?? null;
            
            if (empty($content)) {
                throw new Exception('Comment content cannot be empty');
            }

            $commentId = $community->addComment($postId, $content, $parentId);
            if ($commentId) {
                echo json_encode([
                    'success' => true,
                    'comment_id' => $commentId,
                    'message' => 'Comment added successfully'
                ]);
            } else {
                throw new Exception('Failed to add comment');
            }
            break;

        case 'follow':
            $targetUserId = $_POST['target_user_id'] ?? null;
            if (!$targetUserId) {
                throw new Exception('Target user ID is required');
            }

            $result = $community->followUser($targetUserId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'User followed' : 'Failed to follow user'
            ]);
            break;

        case 'unfollow':
            $targetUserId = $_POST['target_user_id'] ?? null;
            if (!$targetUserId) {
                throw new Exception('Target user ID is required');
            }

            $result = $community->unfollowUser($targetUserId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'User unfollowed' : 'Failed to unfollow user'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
