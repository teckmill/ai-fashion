<?php
require_once 'config.php';

/**
 * User Authentication Functions
 */

function registerUser($username, $email, $password, $firstName = '', $lastName = '') {
    global $pdo;
    
    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, first_name, last_name)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$username, $email, $passwordHash, $firstName, $lastName]);

        return ['success' => true, 'user_id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

function loginUser($username, $password) {
    global $pdo;
    
    try {
        // Get user by username or email
        $stmt = $pdo->prepare("
            SELECT id, username, email, password_hash 
            FROM users 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Generate session token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_DURATION);

        // Store session
        $stmt = $pdo->prepare("
            INSERT INTO user_sessions (user_id, session_token, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user['id'], $token, $expiresAt]);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['token'] = $token;

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
    }
}

function logoutUser() {
    global $pdo;
    
    if (isset($_SESSION['user_id']) && isset($_SESSION['token'])) {
        try {
            // Remove session from database
            $stmt = $pdo->prepare("
                DELETE FROM user_sessions 
                WHERE user_id = ? AND session_token = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['token']]);
        } catch (PDOException $e) {
            // Log error but continue with session destruction
            error_log('Logout database error: ' . $e->getMessage());
        }
    }

    // Destroy session
    session_destroy();
    return ['success' => true];
}

function isAuthenticated() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 1 FROM user_sessions 
            WHERE user_id = ? AND session_token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['token']]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function getCurrentUser() {
    global $pdo;
    
    if (!isAuthenticated()) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, first_name, last_name, created_at
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * User Preferences Functions
 */

function saveUserPreferences($userId, $preferences) {
    global $pdo;
    
    try {
        // Check if preferences exist
        $stmt = $pdo->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            // Update existing preferences
            $stmt = $pdo->prepare("
                UPDATE user_preferences 
                SET style_preference = ?,
                    color_preference = ?,
                    season_preference = ?,
                    height_feet = ?,
                    height_inches = ?,
                    weight = ?
                WHERE user_id = ?
            ");
        } else {
            // Insert new preferences
            $stmt = $pdo->prepare("
                INSERT INTO user_preferences 
                (user_id, style_preference, color_preference, season_preference, height_feet, height_inches, weight)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
        }

        $stmt->execute([
            $preferences['style_preference'],
            $preferences['color_preference'],
            $preferences['season_preference'],
            $preferences['height_feet'],
            $preferences['height_inches'],
            $preferences['weight'],
            $userId
        ]);

        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to save preferences: ' . $e->getMessage()];
    }
}

function getUserPreferences($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Saved Outfits Functions
 */

function saveOutfit($userId, $outfitName, $outfitData) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO saved_outfits (user_id, outfit_name, outfit_data)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $outfitName, json_encode($outfitData)]);
        
        return ['success' => true, 'outfit_id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to save outfit: ' . $e->getMessage()];
    }
}

function getSavedOutfits($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM saved_outfits 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
