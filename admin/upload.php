<?php
require_once __DIR__ . '/../functions.php';

// Only accept POST from admin (basic check)
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No image uploaded']);
    exit;
}

 $url = handleUpload($_FILES['image'], 'content');

if ($url) {
    echo json_encode(['success' => true, 'url' => $url]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Upload failed. Check file type (JPG/PNG/GIF/WebP) and size (max 5MB).']);
}