<?php
require_once 'config.php';

class RetailerAPI {
    private $retailerId;
    private $apiEndpoint;
    private $apiKey;
    
    public function __construct($retailerId) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM retailers WHERE id = ? AND status = 'active'");
        $stmt->execute([$retailerId]);
        $retailer = $stmt->fetch();
        
        if (!$retailer) {
            throw new Exception("Retailer not found or inactive");
        }
        
        $this->retailerId = $retailerId;
        $this->apiEndpoint = $retailer['api_endpoint'];
        $this->apiKey = $retailer['api_key'];
    }
    
    public function searchProducts($query, $category = null, $limit = 10) {
        // Implementation will vary based on retailer's API
        $params = [
            'q' => $query,
            'limit' => $limit
        ];
        
        if ($category) {
            $params['category'] = $category;
        }
        
        return $this->makeRequest('search', $params);
    }
    
    public function getProductDetails($productId) {
        return $this->makeRequest('products/' . $productId);
    }
    
    private function makeRequest($endpoint, $params = []) {
        $url = $this->apiEndpoint . '/' . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("API request failed with status code: " . $httpCode);
        }
        
        return json_decode($response, true);
    }
}

class ProductRecommendation {
    public static function saveRecommendation($userId, $product) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO product_recommendations 
                (user_id, product_id, retailer_id, product_name, product_url, image_url, price, category)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $userId,
                $product['id'],
                $product['retailer_id'],
                $product['name'],
                $product['url'],
                $product['image_url'],
                $product['price'],
                $product['category']
            ]);
        } catch (PDOException $e) {
            error_log("Error saving recommendation: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getRecommendations($userId, $limit = 10) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM product_recommendations 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting recommendations: " . $e->getMessage());
            return [];
        }
    }
}

// Example retailer implementations
class ASOSRetailer extends RetailerAPI {
    public function __construct() {
        parent::__construct('asos');
    }
    
    public function searchProducts($query, $category = null, $limit = 10) {
        // ASOS-specific implementation
        $params = [
            'q' => $query,
            'limit' => $limit,
            'store' => 'US',
            'currency' => 'USD'
        ];
        
        if ($category) {
            $params['categoryId'] = $this->mapCategory($category);
        }
        
        return $this->makeRequest('products/search', $params);
    }
    
    private function mapCategory($category) {
        // Map internal categories to ASOS category IDs
        $categoryMap = [
            'dresses' => 8799,
            'tops' => 4169,
            'bottoms' => 2623,
            // Add more mappings as needed
        ];
        
        return $categoryMap[$category] ?? null;
    }
}

class ZaraRetailer extends RetailerAPI {
    public function __construct() {
        parent::__construct('zara');
    }
    
    public function searchProducts($query, $category = null, $limit = 10) {
        // Zara-specific implementation
        $params = [
            'term' => $query,
            'limit' => $limit
        ];
        
        if ($category) {
            $params['category'] = $this->mapCategory($category);
        }
        
        return $this->makeRequest('products/search', $params);
    }
    
    private function mapCategory($category) {
        // Map internal categories to Zara category IDs
        $categoryMap = [
            'dresses' => 'woman-dresses',
            'tops' => 'woman-tops',
            'bottoms' => 'woman-trousers',
            // Add more mappings as needed
        ];
        
        return $categoryMap[$category] ?? null;
    }
}
