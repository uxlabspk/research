<?php
require_once __DIR__ . '/../functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

 $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        if (loginAdmin($username, $password)) {
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?php echo e(SITE_NAME); ?></title>
    <link rel="shortcut icon" href="/public/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #050505; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: rgba(23,23,23,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 48px; max-width: 420px; width: 100%; backdrop-filter: blur(12px); }
        h1 { font-size: 24px; font-weight: 600; margin-bottom: 4px; letter-spacing: -0.025em; }
        .subtitle { color: #737373; font-size: 14px; margin-bottom: 32px; }
        .error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 500; color: #a3a3a3; margin-bottom: 6px; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; color: #fff; font-size: 14px; font-family: inherit; outline: none; transition: border-color 0.2s;
        }
        input:focus { border-color: rgba(129,140,248,0.5); }
        .field { margin-bottom: 20px; }
        button { width: 100%; background: #fff; color: #000; border: none; padding: 13px; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; font-family: inherit; transition: all 0.15s; }
        button:hover { background: #e5e5e5; }
        .back { display: block; text-align: center; margin-top: 24px; font-size: 13px; color: #525252; text-decoration: none; }
        .back:hover { color: #a3a3a3; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Admin Login</h1>
        <p class="subtitle">Sign in to manage your articles.</p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit">Sign In</button>
        </form>
        <a href="<?php echo SITE_URL; ?>" class="back">← Back to site</a>
    </div>
</body>
</html>