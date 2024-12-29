<?php
class Community {
    private $pdo;
    private $userId;

    public function __construct($userId = null) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    // Post Management
    public function createPost($title, $description, $imageUrl, $tags = []) {
        try {
            $this->pdo->beginTransaction();

            // Create post
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (user_id, title, description, image_url)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$this->userId, $title, $description, $imageUrl]);
            $postId = $this->pdo->lastInsertId();

            // Add tags
            if (!empty($tags)) {
                $this->addTagsToPost($postId, $tags);
            }

            $this->pdo->commit();
            return $postId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error creating post: " . $e->getMessage());
            throw $e;
        }
    }

    private function addTagsToPost($postId, $tags) {
        foreach ($tags as $tag) {
            // Get or create tag
            $slug = $this->createSlug($tag);
            $tagId = $this->getOrCreateTag($tag, $slug);

            // Link tag to post
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO post_tags (post_id, tag_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$postId, $tagId]);
        }
    }

    private function createSlug($text) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    }

    private function getOrCreateTag($name, $slug) {
        // Try to get existing tag
        $stmt = $this->pdo->prepare("SELECT id FROM tags WHERE slug = ?");
        $stmt->execute([$slug]);
        $result = $stmt->fetch();

        if ($result) {
            return $result['id'];
        }

        // Create new tag
        $stmt = $this->pdo->prepare("
            INSERT INTO tags (name, slug)
            VALUES (?, ?)
        ");
        $stmt->execute([$name, $slug]);
        return $this->pdo->lastInsertId();
    }

    // Feed Management
    public function getFeed($page = 1, $limit = 10, $filter = 'all') {
        $offset = ($page - 1) * $limit;
        
        $sql = "
            SELECT 
                p.*,
                u.username,
                u.profile_image,
                GROUP_CONCAT(t.name) as tags,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
        ";

        switch ($filter) {
            case 'following':
                $sql .= " WHERE p.user_id IN (SELECT following_id FROM followers WHERE follower_id = ?)";
                break;
            case 'trending':
                $sql .= " ORDER BY (p.likes_count + p.comments_count) DESC";
                break;
            default:
                $sql .= " ORDER BY p.created_at DESC";
        }

        $sql .= " GROUP BY p.id LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->userId, $offset, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Interaction Management
    public function likePost($postId) {
        try {
            $this->pdo->beginTransaction();

            // Add like
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO likes (user_id, post_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$this->userId, $postId]);

            // Update likes count
            $stmt = $this->pdo->prepare("
                UPDATE posts 
                SET likes_count = (SELECT COUNT(*) FROM likes WHERE post_id = ?)
                WHERE id = ?
            ");
            $stmt->execute([$postId, $postId]);

            // Create notification
            $this->createNotification($postId, 'like');

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error liking post: " . $e->getMessage());
            return false;
        }
    }

    public function unlikePost($postId) {
        try {
            $this->pdo->beginTransaction();

            // Remove like
            $stmt = $this->pdo->prepare("
                DELETE FROM likes
                WHERE user_id = ? AND post_id = ?
            ");
            $stmt->execute([$this->userId, $postId]);

            // Update likes count
            $stmt = $this->pdo->prepare("
                UPDATE posts 
                SET likes_count = (SELECT COUNT(*) FROM likes WHERE post_id = ?)
                WHERE id = ?
            ");
            $stmt->execute([$postId, $postId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error unliking post: " . $e->getMessage());
            return false;
        }
    }

    public function addComment($postId, $content, $parentId = null) {
        try {
            $this->pdo->beginTransaction();

            // Add comment
            $stmt = $this->pdo->prepare("
                INSERT INTO comments (post_id, user_id, content, parent_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$postId, $this->userId, $content, $parentId]);
            $commentId = $this->pdo->lastInsertId();

            // Update comments count
            $stmt = $this->pdo->prepare("
                UPDATE posts 
                SET comments_count = (SELECT COUNT(*) FROM comments WHERE post_id = ?)
                WHERE id = ?
            ");
            $stmt->execute([$postId, $postId]);

            // Create notification
            $this->createNotification($postId, 'comment');

            $this->pdo->commit();
            return $commentId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error adding comment: " . $e->getMessage());
            return false;
        }
    }

    // Follow System
    public function followUser($targetUserId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO followers (follower_id, following_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$this->userId, $targetUserId]);

            // Create notification
            $this->createNotification($targetUserId, 'follow');

            return true;
        } catch (Exception $e) {
            error_log("Error following user: " . $e->getMessage());
            return false;
        }
    }

    public function unfollowUser($targetUserId) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM followers
                WHERE follower_id = ? AND following_id = ?
            ");
            return $stmt->execute([$this->userId, $targetUserId]);
        } catch (Exception $e) {
            error_log("Error unfollowing user: " . $e->getMessage());
            return false;
        }
    }

    // Notification System
    private function createNotification($referenceId, $type) {
        try {
            // Get target user ID based on notification type
            $targetUserId = $this->getNotificationTargetUser($referenceId, $type);
            
            if ($targetUserId === $this->userId) {
                return; // Don't notify users about their own actions
            }

            $content = $this->generateNotificationContent($type);

            $stmt = $this->pdo->prepare("
                INSERT INTO notifications (user_id, type, content, reference_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$targetUserId, $type, $content, $referenceId]);
        } catch (Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
        }
    }

    private function getNotificationTargetUser($referenceId, $type) {
        switch ($type) {
            case 'like':
            case 'comment':
                $stmt = $this->pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
                break;
            case 'follow':
                return $referenceId; // Reference ID is already the target user ID
            default:
                throw new Exception("Invalid notification type");
        }
        
        $stmt->execute([$referenceId]);
        return $stmt->fetchColumn();
    }

    private function generateNotificationContent($type) {
        $username = $this->getUserName();
        switch ($type) {
            case 'like':
                return "$username liked your post";
            case 'comment':
                return "$username commented on your post";
            case 'follow':
                return "$username started following you";
            default:
                return "New notification";
        }
    }

    private function getUserName() {
        $stmt = $this->pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetchColumn();
    }

    // Utility Methods
    public function getPostDetails($postId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                p.*,
                u.username,
                u.profile_image,
                GROUP_CONCAT(t.name) as tags,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            WHERE p.id = ?
            GROUP BY p.id
        ");
        $stmt->execute([$this->userId, $postId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getComments($postId, $parentId = null) {
        $stmt = $this->pdo->prepare("
            SELECT 
                c.*,
                u.username,
                u.profile_image
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ? AND c.parent_id IS ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$postId, $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
