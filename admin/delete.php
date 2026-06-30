<?php
require_once __DIR__ . '/../functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid security token.'];
    header('Location: dashboard.php');
    exit;
}

 $id = (int)($_POST['id'] ?? 0);
if ($id) {
    deletePost($id);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post deleted successfully.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid post ID.'];
}

header('Location: dashboard.php');
exit;