<?php
require_once 'config.php';

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
    
    public function updateStylePreferences($category, $score) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO style_preferences (user_id, category, preference_score)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                preference_score = (preference_score * 0.7 + VALUES(preference_score) * 0.3)
            ");
            
            return $stmt->execute([$this->userId, $category, $score]);
        } catch (PDOException $e) {
            error_log("Error updating style preferences: " . $e->getMessage());
            return false;
        }
    }
    
    public function getPersonalizedRecommendations($limit = 10) {
        global $pdo;
        
        try {
            // Get user's style preferences
            $stmt = $pdo->prepare("
                SELECT category, preference_score 
                FROM style_preferences 
                WHERE user_id = ?
                ORDER BY preference_score DESC
            ");
            $stmt->execute([$this->userId]);
            $preferences = $stmt->fetchAll();
            
            // Get recent interactions
            $stmt = $pdo->prepare("
                SELECT item_type, item_id, interaction_type, COUNT(*) as interaction_count
                FROM user_interactions
                WHERE user_id = ?
                GROUP BY item_type, item_id, interaction_type
                ORDER BY interaction_count DESC
                LIMIT ?
            ");
            $stmt->execute([$this->userId, $limit]);
            $interactions = $stmt->fetchAll();
            
            // Combine preferences and interactions to generate recommendations
            $recommendations = $this->generateRecommendations($preferences, $interactions);
            
            return $recommendations;
        } catch (PDOException $e) {
            error_log("Error getting recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    private function generateRecommendations($preferences, $interactions) {
        // Initialize recommendation scores
        $recommendationScores = [];
        
        // Weight factors
        $preferenceWeight = 0.6;
        $interactionWeight = 0.4;
        
        // Process style preferences
        foreach ($preferences as $pref) {
            $category = $pref['category'];
            $score = $pref['preference_score'];
            
            if (!isset($recommendationScores[$category])) {
                $recommendationScores[$category] = 0;
            }
            $recommendationScores[$category] += $score * $preferenceWeight;
        }
        
        // Process interactions
        foreach ($interactions as $interaction) {
            $type = $interaction['interaction_type'];
            $itemType = $interaction['item_type'];
            $count = $interaction['interaction_count'];
            
            // Weight different interaction types
            $typeWeights = [
                'purchase' => 1.0,
                'save' => 0.8,
                'like' => 0.6,
                'view' => 0.3,
                'dismiss' => -0.5
            ];
            
            $weight = $typeWeights[$type] ?? 0;
            $score = $count * $weight * $interactionWeight;
            
            if (!isset($recommendationScores[$itemType])) {
                $recommendationScores[$itemType] = 0;
            }
            $recommendationScores[$itemType] += $score;
        }
        
        // Sort recommendations by score
        arsort($recommendationScores);
        
        // Convert scores to recommendations
        $recommendations = [];
        foreach ($recommendationScores as $category => $score) {
            // Get products for this category
            $products = $this->getProductsByCategory($category);
            $recommendations = array_merge($recommendations, $products);
        }
        
        return array_slice($recommendations, 0, 10); // Return top 10 recommendations
    }
    
    private function getProductsByCategory($category) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM product_recommendations
                WHERE category = ? AND user_id = ?
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$category, $this->userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting products by category: " . $e->getMessage());
            return [];
        }
    }
}
