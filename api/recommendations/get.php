<?php
require_once '../../includes/functions.php';
require_once '../../includes/recommendations.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$engine = new RecommendationEngine($user['id']);

try {
    $recommendations = $engine->getPersonalizedRecommendations();
    echo json_encode([
        'success' => true,
        'recommendations' => $recommendations
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get recommendations: ' . $e->getMessage()
    ]);
}
