<?php
require_once __DIR__ . '/../functions.php';
requireAdmin();

 $id = (int)($_GET['id'] ?? 0);
 $post = getPostById($id);

if (!$post) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Post not found.'];
    header('Location: dashboard.php');
    exit;
}

 $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $data = sanitizeInput($_POST);
        $data['featured_image'] = $post['featured_image']; // Keep existing by default

        // Handle new featured image upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = handleUpload($_FILES['featured_image'], 'featured');
            if ($uploaded) {
                // Delete old image
                if ($post['featured_image']) {
                    $oldPath = str_replace(SITE_URL . '/', '', $post['featured_image']);
                    if (file_exists(__DIR__ . '/../' . $oldPath)) {
                        unlink(__DIR__ . '/../' . $oldPath);
                    }
                }
                $data['featured_image'] = $uploaded;
            }
        }

        // Handle remove image checkbox
        if (isset($_POST['remove_image'])) {
            if ($post['featured_image']) {
                $oldPath = str_replace(SITE_URL . '/', '', $post['featured_image']);
                if (file_exists(__DIR__ . '/../' . $oldPath)) {
                    unlink(__DIR__ . '/../' . $oldPath);
                }
            }
            $data['featured_image'] = '';
        }

        if (empty($data['title']) || empty($data['content'])) {
            $error = 'Title and content are required.';
        } else {
            updatePost($id, $data);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post updated successfully!'];
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post — <?php echo e(SITE_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0a0a0a; color: #fff; }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 240px; background: #111; border-right: 1px solid rgba(255,255,255,0.06); padding: 24px 16px; display: flex; flex-direction: column; }
        .sidebar .logo { font-size: 18px; font-weight: 700; letter-spacing: -0.02em; padding: 0 8px 24px; border-bottom: 1px solid rgba(255,255,255,0.06); margin-bottom: 24px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; font-size: 13px; font-weight: 500; color: #737373; text-decoration: none; transition: all 0.15s; margin-bottom: 2px; }
        .sidebar a:hover { color: #d4d4d4; background: rgba(255,255,255,0.04); }
        .sidebar a.active { color: #fff; background: rgba(129,140,248,0.1); }
        .sidebar .bottom { margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06); }
        .main { margin-left: 240px; padding: 32px 40px; min-height: 100vh; max-width: 900px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; }
        .topbar h1 { font-size: 24px; font-weight: 600; letter-spacing: -0.025em; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; font-family: inherit; text-decoration: none; transition: all 0.15s; }
        .btn-primary { background: #fff; color: #000; }
        .btn-primary:hover { background: #e5e5e5; }
        .btn-ghost { background: transparent; color: #a3a3a3; border: 1px solid rgba(255,255,255,0.1); }
        .btn-ghost:hover { color: #fff; border-color: rgba(255,255,255,0.2); }
        .btn-draft { background: rgba(255,255,255,0.06); color: #d4d4d4; }
        .btn-draft:hover { background: rgba(255,255,255,0.1); }

        .field { margin-bottom: 24px; }
        label { display: block; font-size: 13px; font-weight: 500; color: #a3a3a3; margin-bottom: 8px; }
        input[type="text"], textarea, select {
            width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; color: #fff; font-size: 14px; font-family: inherit; outline: none; transition: border-color 0.2s;
        }
        input:focus, textarea:focus, select:focus { border-color: rgba(129,140,248,0.5); }
        select option { background: #18181b; color: #fff; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .error-msg { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 24px; }
        .current-img { margin-top: 12px; position: relative; display: inline-block; }
        .current-img img { width: 200px; height: 120px; object-fit: cover; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); }
        .remove-check { display: flex; align-items: center; gap: 8px; margin-top: 10px; font-size: 12px; color: #f87171; cursor: pointer; }
        .remove-check input { width: auto; }

        /* EasyMDE dark theme override (same as create) */
        .EasyMDEContainer .CodeMirror { background: rgba(255,255,255,0.03) !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 10px !important; color: #d4d4d4 !important; font-family: 'SF Mono', 'Fira Code', monospace !important; font-size: 14px !important; }
        .EasyMDEContainer .CodeMirror-cursor { border-color: #818cf8 !important; }
        .EasyMDEContainer .CodeMirror-selected { background: rgba(129,140,248,0.15) !important; }
        .EasyMDEContainer .cm-header { color: #fff !important; }
        .EasyMDEContainer .cm-strong { color: #fff !important; }
        .EasyMDEContainer .cm-em { color: #c4b5fd !important; }
        .EasyMDEContainer .cm-link { color: #818cf8 !important; }
        .EasyMDEContainer .cm-url { color: #525252 !important; }
        .EasyMDEContainer .cm-string { color: #34d399 !important; }
        .EasyMDEContainer .cm-comment { color: #525252 !important; }
        .EasyMDEContainer .editor-toolbar { background: rgba(255,255,255,0.03) !important; border: 1px solid rgba(255,255,255,0.1) !important; border-bottom: none !important; border-radius: 10px 10px 0 0 !important; }
        .EasyMDEContainer .editor-toolbar a { color: #737373 !important; border-color: transparent !important; }
        .EasyMDEContainer .editor-toolbar a:hover, .EasyMDEContainer .editor-toolbar a.active { background: rgba(255,255,255,0.08) !important; color: #fff !important; border-color: transparent !important; }
        .EasyMDEContainer .editor-preview { background: rgba(255,255,255,0.02) !important; border: 1px solid rgba(255,255,255,0.1) !important; border-top: none !important; border-radius: 0 0 10px 10px !important; color: #d4d4d4 !important; }
        .EasyMDEContainer .editor-preview h1, .EasyMDEContainer .editor-preview h2, .EasyMDEContainer .editor-preview h3 { color: #fff !important; }
        .EasyMDEContainer .editor-preview a { color: #818cf8 !important; }
        .EasyMDEContainer .editor-preview code { background: rgba(255,255,255,0.08) !important; color: #c4b5fd !important; padding: 2px 5px; border-radius: 3px; }
        .EasyMDEContainer .editor-preview pre { background: rgba(0,0,0,0.3) !important; border-radius: 8px; padding: 12px; }
        .EasyMDEContainer .editor-preview pre code { background: none !important; padding: 0 !important; }
        .EasyMDEContainer .editor-statusbar { color: #525252 !important; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 20px; }
            .row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">MNFST</div>
        <a href="dashboard.php">
            <i data-lucide="layout-dashboard" style="width:16px;height:16px"></i> Dashboard
        </a>
        <a href="create.php">
            <i data-lucide="plus" style="width:16px;height:16px"></i> New Post
        </a>
        <a href="<?php echo SITE_URL; ?>" target="_blank">
            <i data-lucide="external-link" style="width:16px;height:16px"></i> View Site
        </a>
        <div class="bottom">
            <a href="logout.php">
                <i data-lucide="log-out" style="width:16px;height:16px"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main">
        <div class="topbar">
            <h1>Edit Post</h1>
            <a href="dashboard.php" class="btn btn-ghost">← Back</a>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="postForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF()); ?>">

            <div class="field">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" value="<?php echo e($post['title']); ?>" required>
            </div>

            <div class="row">
                <div class="field">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <?php
                        $categories = ['Guide', 'AI Engineering', 'AI Strategy', 'Infrastructure', 'Tutorial', 'Opinion'];
                        foreach ($categories as $cat) {
                            $selected = ($post['category'] === $cat) ? 'selected' : '';
                            echo "<option value=\"$cat\" $selected>$cat</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="field">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" value="<?php echo e($post['author']); ?>">
                </div>
            </div>

            <div class="field">
                <label for="excerpt">Excerpt</label>
                <textarea id="excerpt" name="excerpt" rows="3"><?php echo e($post['excerpt']); ?></textarea>
            </div>

            <div class="field">
                <label for="featured_image">Featured Image</label>
                <?php if ($post['featured_image']): ?>
                <div class="current-img">
                    <img src="<?php echo e($post['featured_image']); ?>" alt="Current">
                    <label class="remove-check">
                        <input type="checkbox" name="remove_image" value="1"> Remove current image
                    </label>
                </div>
                <?php endif; ?>
                <input type="file" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top:12px">
                <p style="font-size:11px;color:#525252;margin-top:6px;">Upload a new image to replace the existing one. Max 5MB.</p>
            </div>

            <div class="field">
                <label for="content">Content (Markdown) *</label>
                <textarea id="content" name="content" required><?php echo e($post['content']); ?></textarea>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;padding-top:16px;border-top:1px solid rgba(255,255,255,0.06)">
                <button type="submit" name="status" value="draft" class="btn btn-draft">Save as Draft</button>
                <button type="submit" name="status" value="published" class="btn btn-primary">Update & Publish</button>
            </div>
        </form>
    </div>

<script src="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js"></script>
<script>
    lucide.createIcons();

    const easyMDE = new EasyMDE({
        element: document.getElementById('content'),
        spellChecker: false,
        autosave: { enabled: true, uniqueId: 'mnfst-edit-<?php echo $id; ?>', delay: 5000 },
        toolbar: [
            'bold', 'italic', 'heading', '|',
            'quote', 'unordered-list', 'ordered-list', '|',
            'link', 'image', '|',
            'code', 'table', '|',
            'preview', 'side-by-side', 'fullscreen', '|',
            'guide'
        ],
        uploadImage: true,
        imageUploadEndpoint: 'upload.php',
        imageMaxSize: 5 * 1024 * 1024,
        imageTexts: {
            sbInit: 'Attach files by drag and dropping or pasting from clipboard.',
            sbOnDragEnter: 'Drop image to upload',
            sbOnDrop: 'Uploading image...',
            sbProgress: 'Uploading... %d%',
            sbOnUploaded: 'Image uploaded!',
            sbOnUploadedError: 'Upload failed.'
        },
        maxHeight: '600px',
    });

    document.getElementById('postForm').addEventListener('submit', function(e) {
        var title = document.getElementById('title').value.trim();
        var content = easyMDE.value().trim();

        if (!title) {
            e.preventDefault();
            alert('Please enter a title.');
            document.getElementById('title').focus();
            return false;
        }

        if (!content) {
            e.preventDefault();
            alert('Please write some content.');
            return false;
        }

        document.getElementById('content').value = content;
        localStorage.removeItem('smde_mnfst-edit-<?php echo $id; ?>');

        return true;
    });
</script>
</body>
</html>