<?php
require_once '../../includes/functions.php';
require_once '../../includes/scraper.php';

header('Content-Type: application/json');

// Only allow admin users to refresh products
if (!isAuthenticated() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $scraper = new ProductScraper();
    $scraper->updateDatabase();
    
    echo json_encode([
        'success' => true,
        'message' => 'Products refreshed successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to refresh products: ' . $e->getMessage()
    ]);
}
