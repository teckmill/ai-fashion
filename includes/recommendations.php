<?php
require_once 'config.php';
require_once 'product_service.php';

class RecommendationEngine {
    private $userId;
    
    public function __construct($userId) {
        $this->userId = $userId;
    }
    
    public function trackInteraction($interactionType, $itemId, $itemType, $data = null) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO user_interactions 
                (user_id, interaction_type, item_id, item_type, interaction_data)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $this->userId,
                $interactionType,
                $itemId,
                $itemType,
                $data ? json_encode($data) : null
            ]);
        } catch (PDOException $e) {
            error_log("Error tracking interaction: " . $e->getMessage());
            return false;
        }
    }
    
    public function getPersonalizedRecommendations($limit = 10) {
        try {
            // Get user's style preferences
            $preferences = $this->getUserPreferences();
            
            // Get user's recent interactions
            $interactions = $this->getUserInteractions();
            
            // Generate recommendations based on preferences and interactions
            $recommendations = $this->generateRecommendations($preferences, $interactions, $limit);
            
            return $recommendations;
        } catch (Exception $e) {
            error_log("Error getting recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    private function getUserPreferences() {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT category, preference_score 
                FROM style_preferences 
                WHERE user_id = ?
                ORDER BY preference_score DESC
            ");
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting user preferences: " . $e->getMessage());
            return [];
        }
    }
    
    private function getUserInteractions($limit = 20) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT item_id, item_type, interaction_type, COUNT(*) as count
                FROM user_interactions
                WHERE user_id = ?
                GROUP BY item_id, item_type, interaction_type
                ORDER BY COUNT(*) DESC
                LIMIT ?
            ");
            $stmt->execute([$this->userId, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting user interactions: " . $e->getMessage());
            return [];
        }
    }
    
    private function generateRecommendations($preferences, $interactions, $limit) {
        // Calculate category weights based on preferences and interactions
        $categoryWeights = $this->calculateCategoryWeights($preferences, $interactions);
        
        // Get recommended products for each category
        $recommendations = [];
        foreach ($categoryWeights as $category => $weight) {
            $categoryProducts = ProductService::getProducts($category, ceil($limit * $weight));
            $recommendations = array_merge($recommendations, $categoryProducts);
        }
        
        // Shuffle and limit results
        shuffle($recommendations);
        return array_slice($recommendations, 0, $limit);
    }
    
    private function calculateCategoryWeights($preferences, $interactions) {
        $weights = [
            'tops' => 0.2,
            'bottoms' => 0.2,
            'dresses' => 0.15,
            'outerwear' => 0.15,
            'shoes' => 0.15,
            'accessories' => 0.15
        ];
        
        // Adjust weights based on preferences
        foreach ($preferences as $pref) {
            if (isset($weights[$pref['category']])) {
                $weights[$pref['category']] += $pref['preference_score'] * 0.1;
            }
        }
        
        // Adjust weights based on interactions
        foreach ($interactions as $interaction) {
            if ($interaction['item_type'] === 'product') {
                $product = ProductService::getProductsByIds([$interaction['item_id']])[0] ?? null;
                if ($product && isset($weights[$product['category']])) {
                    $interactionWeight = $this->getInteractionTypeWeight($interaction['interaction_type']);
                    $weights[$product['category']] += $interactionWeight * 0.05;
                }
            }
        }
        
        // Normalize weights
        $total = array_sum($weights);
        foreach ($weights as &$weight) {
            $weight = $weight / $total;
        }
        
        return $weights;
    }
    
    private function getInteractionTypeWeight($type) {
        return [
            'purchase' => 1.0,
            'save' => 0.8,
            'like' => 0.6,
            'view' => 0.3,
            'dismiss' => -0.5
        ][$type] ?? 0;
    }
}
