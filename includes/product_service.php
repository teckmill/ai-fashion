<?php
require_once 'config.php';

class ProductService {
    public static function getProducts($category = null, $limit = 10) {
        global $pdo;
        
        try {
            $sql = "SELECT * FROM products";
            $params = [];
            
            if ($category) {
                $sql .= " WHERE category = ?";
                $params[] = $category;
            }
            
            $sql .= " ORDER BY RAND() LIMIT ?";
            $params[] = $limit;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting products: " . $e->getMessage());
            return [];
        }
    }
    
    public static function getProductsByIds($productIds) {
        global $pdo;
        
        if (empty($productIds)) {
            return [];
        }
        
        try {
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($productIds);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting products by IDs: " . $e->getMessage());
            return [];
        }
    }
    
    public static function getSimilarProducts($productId, $limit = 5) {
        global $pdo;
        
        try {
            // Get the category of the current product
            $stmt = $pdo->prepare("SELECT category FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return [];
            }
            
            // Get similar products from the same category
            $stmt = $pdo->prepare("
                SELECT * FROM products 
                WHERE category = ? AND id != ?
                ORDER BY RAND()
                LIMIT ?
            ");
            $stmt->execute([$product['category'], $productId, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting similar products: " . $e->getMessage());
            return [];
        }
    }
    
    public static function searchProducts($query, $limit = 10) {
        global $pdo;
        
        try {
            $searchTerm = "%$query%";
            $stmt = $pdo->prepare("
                SELECT * FROM products 
                WHERE name LIKE ? OR description LIKE ? OR brand LIKE ?
                ORDER BY name
                LIMIT ?
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error searching products: " . $e->getMessage());
            return [];
        }
    }
    
    public static function getProductsByStyle($style, $limit = 10) {
        // Map styles to categories and attributes
        $styleMap = [
            'casual' => ['tops', 'bottoms', 'shoes'],
            'formal' => ['dresses', 'outerwear', 'accessories'],
            'sporty' => ['tops', 'bottoms', 'shoes'],
            'bohemian' => ['dresses', 'accessories'],
            'minimal' => ['tops', 'bottoms', 'outerwear']
        ];
        
        $categories = $styleMap[$style] ?? array_keys($styleMap);
        
        global $pdo;
        
        try {
            $placeholders = str_repeat('?,', count($categories) - 1) . '?';
            $sql = "
                SELECT * FROM products 
                WHERE category IN ($placeholders)
                ORDER BY RAND()
                LIMIT ?
            ";
            
            $params = array_merge($categories, [$limit]);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting products by style: " . $e->getMessage());
            return [];
        }
    }
}
