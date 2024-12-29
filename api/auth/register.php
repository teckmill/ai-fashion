<?php
require_once '../../includes/functions.php';

header('Content-Type: application/json');

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
$required = ['username', 'email', 'password'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Optional fields
$firstName = $data['first_name'] ?? '';
$lastName = $data['last_name'] ?? '';

// Register user
$result = registerUser(
    $data['username'],
    $data['email'],
    $data['password'],
    $firstName,
    $lastName
);

if ($result['success']) {
    http_response_code(201);
} else {
    http_response_code(400);
}

echo json_encode($result);
