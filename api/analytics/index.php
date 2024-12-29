<?php
require_once '../../includes/functions.php';
require_once '../../includes/analytics.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = getCurrentUserId();
$analytics = new Analytics($userId);

$endpoint = $_GET['endpoint'] ?? '';
$timeframe = $_GET['timeframe'] ?? 'year';

try {
    switch ($endpoint) {
        case 'style-evolution':
            $data = $analytics->getStyleEvolution($timeframe);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'wardrobe-insights':
            $data = $analytics->getWardrobeInsights();
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'seasonal-analysis':
            $data = $analytics->getSeasonalAnalysis();
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'color-analysis':
            $data = $analytics->getColorAnalysis();
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Invalid endpoint']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
