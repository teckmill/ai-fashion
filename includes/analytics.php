<?php
class Analytics {
    private $userId;
    private $pdo;

    public function __construct($userId) {
        global $pdo;
        $this->userId = $userId;
        $this->pdo = $pdo;
    }

    public function trackWardrobeUsage($itemId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO wardrobe_usage (user_id, item_id, usage_date)
                VALUES (?, ?, NOW())
            ");
            return $stmt->execute([$this->userId, $itemId]);
        } catch (PDOException $e) {
            error_log("Error tracking wardrobe usage: " . $e->getMessage());
            return false;
        }
    }

    public function getStyleEvolution($timeframe = 'year') {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(u.usage_date, '%Y-%m') as period,
                    i.style_category,
                    COUNT(*) as usage_count
                FROM wardrobe_usage u
                JOIN wardrobe_items i ON u.item_id = i.id
                WHERE u.user_id = ?
                AND u.usage_date >= DATE_SUB(NOW(), INTERVAL 1 " . strtoupper($timeframe) . ")
                GROUP BY period, i.style_category
                ORDER BY period ASC
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting style evolution: " . $e->getMessage());
            return [];
        }
    }

    public function getWardrobeInsights() {
        try {
            // Most worn items
            $mostWorn = $this->pdo->prepare("
                SELECT i.*, COUNT(*) as wear_count
                FROM wardrobe_usage u
                JOIN wardrobe_items i ON u.item_id = i.id
                WHERE u.user_id = ?
                GROUP BY i.id
                ORDER BY wear_count DESC
                LIMIT 5
            ");
            $mostWorn->execute([$this->userId]);
            
            // Least worn items
            $leastWorn = $this->pdo->prepare("
                SELECT i.*, COUNT(*) as wear_count
                FROM wardrobe_items i
                LEFT JOIN wardrobe_usage u ON i.id = u.item_id
                WHERE i.user_id = ?
                GROUP BY i.id
                ORDER BY wear_count ASC
                LIMIT 5
            ");
            $leastWorn->execute([$this->userId]);
            
            // Style distribution
            $styleDistribution = $this->pdo->prepare("
                SELECT 
                    style_category,
                    COUNT(*) as item_count,
                    COUNT(*) * 100.0 / (
                        SELECT COUNT(*) 
                        FROM wardrobe_items 
                        WHERE user_id = ?
                    ) as percentage
                FROM wardrobe_items
                WHERE user_id = ?
                GROUP BY style_category
            ");
            $styleDistribution->execute([$this->userId, $this->userId]);
            
            return [
                'most_worn' => $mostWorn->fetchAll(PDO::FETCH_ASSOC),
                'least_worn' => $leastWorn->fetchAll(PDO::FETCH_ASSOC),
                'style_distribution' => $styleDistribution->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log("Error getting wardrobe insights: " . $e->getMessage());
            return [];
        }
    }

    public function getSeasonalAnalysis() {
        try {
            $sql = "
                SELECT 
                    CASE 
                        WHEN MONTH(u.usage_date) IN (12, 1, 2) THEN 'winter'
                        WHEN MONTH(u.usage_date) IN (3, 4, 5) THEN 'spring'
                        WHEN MONTH(u.usage_date) IN (6, 7, 8) THEN 'summer'
                        ELSE 'fall'
                    END as season,
                    i.category,
                    COUNT(*) as usage_count
                FROM wardrobe_usage u
                JOIN wardrobe_items i ON u.item_id = i.id
                WHERE u.user_id = ?
                AND u.usage_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                GROUP BY season, i.category
                ORDER BY season, usage_count DESC
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting seasonal analysis: " . $e->getMessage());
            return [];
        }
    }

    public function getColorAnalysis() {
        try {
            $sql = "
                SELECT 
                    i.primary_color,
                    COUNT(*) as color_count,
                    COUNT(*) * 100.0 / (
                        SELECT COUNT(*) 
                        FROM wardrobe_items 
                        WHERE user_id = ?
                    ) as percentage
                FROM wardrobe_items i
                WHERE i.user_id = ?
                GROUP BY i.primary_color
                ORDER BY color_count DESC
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->userId, $this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting color analysis: " . $e->getMessage());
            return [];
        }
    }
}
