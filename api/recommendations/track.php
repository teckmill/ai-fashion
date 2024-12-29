<?php
require_once '../../includes/functions.php';
require_once '../../includes/recommendations.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $data = $_POST;
}

// Validate required fields
$required = ['interaction_type', 'item_id', 'item_type'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$user = getCurrentUser();
$engine = new RecommendationEngine($user['id']);

try {
    $success = $engine->trackInteraction(
        $data['interaction_type'],
        $data['item_id'],
        $data['item_type'],
        $data['interaction_data'] ?? null
    );
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to track interaction');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to track interaction: ' . $e->getMessage()
    ]);
}
