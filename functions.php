<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Parsedown.php';

// ============================================
// SLUG GENERATION
// ============================================
function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug($pdo, $title, $excludeId = null) {
    $base = createSlug($title);
    $slug = $base;
    $i = 1;
    while (true) {
        if ($excludeId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        if ($stmt->fetchColumn() == 0) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// ============================================
// READING TIME
// ============================================
function calculateReadingTime($content) {
    $text = strip_tags($content);
    $words = str_word_count($text);
    $minutes = max(1, (int) ceil($words / 200));
    return $minutes . ' min read';
}

// ============================================
// MARKDOWN PARSING
// ============================================
function parseMarkdown($text) {
    $pd = new Parsedown();
    $pd->setSafeMode(true);
    return $pd->text($text);
}

// ============================================
// POSTS CRUD
// ============================================
function getAllPosts($status = 'published') {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute([$status]);
    return $stmt->fetchAll();
}

function getPostBySlug($slug) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getPostById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAllPostsAdmin() {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function createPost($data) {
    $pdo = getDB();
    $slug = uniqueSlug($pdo, $data['title']);
    $readingTime = calculateReadingTime($data['content'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO posts (slug, title, excerpt, content, featured_image, category, author, reading_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $slug,
        $data['title'],
        $data['excerpt'] ?? '',
        $data['content'] ?? '',
        $data['featured_image'] ?? '',
        $data['category'] ?? 'Guide',
        $data['author'] ?? 'MNFST Studio',
        $readingTime,
        $data['status'] ?? 'draft',
    ]);
    return $pdo->lastInsertId();
}

function updatePost($id, $data) {
    $pdo = getDB();
    $slug = uniqueSlug($pdo, $data['title'], $id);
    $readingTime = calculateReadingTime($data['content'] ?? '');

    $stmt = $pdo->prepare("UPDATE posts SET slug=?, title=?, excerpt=?, content=?, featured_image=?, category=?, author=?, reading_time=?, status=? WHERE id=?");
    $stmt->execute([
        $slug,
        $data['title'],
        $data['excerpt'] ?? '',
        $data['content'] ?? '',
        $data['featured_image'] ?? '',
        $data['category'] ?? 'Guide',
        $data['author'] ?? 'MNFST Studio',
        $readingTime,
        $data['status'] ?? 'draft',
        $id,
    ]);
}

function deletePost($id) {
    $pdo = getDB();
    $post = getPostById($id);
    if ($post && $post['featured_image']) {
        $path = str_replace(SITE_URL . '/', '', $post['featured_image']);
        if (file_exists(__DIR__ . '/' . $path)) {
            unlink(__DIR__ . '/' . $path);
        }
    }
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);
}

// ============================================
// AUTHENTICATION
// ============================================
function isAdminLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
    return isset($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function loginAdmin($username, $password) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        return true;
    }
    return false;
}

function logoutAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
    session_destroy();
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

// ============================================
// CSRF PROTECTION
// ============================================
function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRF()) . '">';
}

// ============================================
// FILE UPLOAD
// ============================================
function handleUpload($file, $prefix = 'img') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return UPLOAD_URL . $filename;
    }
    return null;
}

// ============================================
// SANITIZATION
// ============================================
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function sanitizeInput($data) {
    $clean = [];
    foreach ($data as $key => $value) {
        $clean[$key] = is_string($value) ? trim($value) : $value;
    }
    return $clean;
}