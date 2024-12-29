<?php
class Auth {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $password, $remember = false) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Start session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($remember) {
                    $this->createRememberToken($user['id']);
                }

                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function signup($username, $email, $password) {
        try {
            // Check if username or email exists
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password, profile_image)
                VALUES (?, ?, ?, '/images/default-profile.png')
            ");
            
            if ($stmt->execute([$username, $email, $hashedPassword])) {
                $userId = $this->pdo->lastInsertId();
                
                // Start session
                session_start();
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';

                return ['success' => true, 'user_id' => $userId];
            }
            return ['success' => false, 'message' => 'Failed to create account'];
        } catch (PDOException $e) {
            error_log("Signup error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    public function logout() {
        // Remove remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $this->removeRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Destroy session
        session_start();
        session_destroy();
        
        // Clear all session cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 3600, '/');
            }
        }
    }

    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            return true;
        }

        // Check remember token
        if (isset($_COOKIE['remember_token'])) {
            return $this->validateRememberToken($_COOKIE['remember_token']);
        }

        return false;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, profile_image, role, status, created_at
                FROM users WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }

    private function createRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO auth_tokens (user_id, token, expires_at)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$userId, $token, $expires]);

            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
            return true;
        } catch (PDOException $e) {
            error_log("Create remember token error: " . $e->getMessage());
            return false;
        }
    }

    private function validateRememberToken($token) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT user_id, expires_at
                FROM auth_tokens
                WHERE token = ? AND expires_at > NOW()
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch();

            if ($result) {
                // Start session
                session_start();
                $_SESSION['user_id'] = $result['user_id'];

                // Get user info
                $stmt = $this->pdo->prepare("SELECT username, role FROM users WHERE id = ?");
                $stmt->execute([$result['user_id']]);
                $user = $stmt->fetch();

                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Validate remember token error: " . $e->getMessage());
            return false;
        }
    }

    private function removeRememberToken($token) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM auth_tokens WHERE token = ?");
            return $stmt->execute([$token]);
        } catch (PDOException $e) {
            error_log("Remove remember token error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }

    public function createPasswordReset($email) {
        try {
            // Get user
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return false;
            }

            // Create reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $stmt = $this->pdo->prepare("
                INSERT INTO password_resets (user_id, token, expires_at)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$user['id'], $token, $expires]) ? $token : false;
        } catch (PDOException $e) {
            error_log("Create password reset error: " . $e->getMessage());
            return false;
        }
    }

    public function validatePasswordReset($token) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT user_id
                FROM password_resets
                WHERE token = ? AND expires_at > NOW()
                AND used = 0
            ");
            $stmt->execute([$token]);
            return $stmt->fetch()['user_id'] ?? false;
        } catch (PDOException $e) {
            error_log("Validate password reset error: " . $e->getMessage());
            return false;
        }
    }
}
