<?php
require_once __DIR__ . '/../functions.php';
requireAdmin();

 $posts = getAllPostsAdmin();
 $flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?php echo e(SITE_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        .main { margin-left: 240px; padding: 32px 40px; min-height: 100vh; }
        .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; }
        .topbar h1 { font-size: 24px; font-weight: 600; letter-spacing: -0.025em; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; font-family: inherit; text-decoration: none; transition: all 0.15s; }
        .btn-primary { background: #fff; color: #000; }
        .btn-primary:hover { background: #e5e5e5; }
        .btn-ghost { background: transparent; color: #a3a3a3; border: 1px solid rgba(255,255,255,0.1); }
        .btn-ghost:hover { color: #fff; border-color: rgba(255,255,255,0.2); }
        .btn-danger { background: rgba(239,68,68,0.1); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .stat-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 20px; }
        .stat-card .num { font-size: 28px; font-weight: 600; letter-spacing: -0.03em; }
        .stat-card .label { font-size: 12px; color: #737373; margin-top: 4px; }

        .table-wrap { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 14px 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #525252; border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02); }
        td { padding: 14px 20px; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        tr:hover td { background: rgba(255,255,255,0.02); }
        .post-title { font-weight: 500; color: #e5e5e5; max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .status { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 500; padding: 4px 10px; border-radius: 20px; }
        .status.published { background: rgba(16,185,129,0.1); color: #34d399; }
        .status.draft { background: rgba(251,191,36,0.1); color: #fbbf24; }
        .status .dot { width: 6px; height: 6px; border-radius: 50%; }
        .status.published .dot { background: #34d399; }
        .status.draft .dot { background: #fbbf24; }
        .actions { display: flex; gap: 6px; }
        .empty { text-align: center; padding: 60px 20px; color: #525252; }
        .empty p { margin-bottom: 16px; }

        .flash { padding: 14px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; }
        .flash.success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
        .flash.error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }

        .confirm-modal { display: none; position: fixed; inset: 0; z-index: 100; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; }
        .confirm-modal.show { display: flex; }
        .confirm-box { background: #18181b; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 32px; max-width: 400px; width: 90%; }
        .confirm-box h3 { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
        .confirm-box p { font-size: 14px; color: #a3a3a3; margin-bottom: 24px; line-height: 1.6; }
        .confirm-box .btns { display: flex; gap: 10px; justify-content: flex-end; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 20px; }
            .post-title { max-width: 200px; }
            td, th { padding: 10px 12px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">MNFST</div>
        <a href="dashboard.php" class="active">
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
            <h1>Dashboard</h1>
            <a href="create.php" class="btn btn-primary">
                <i data-lucide="plus" style="width:15px;height:15px"></i> New Post
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?php echo $flash['type']; ?>"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <?php
        $published = array_filter($posts, fn($p) => $p['status'] === 'published');
        $drafts = array_filter($posts, fn($p) => $p['status'] === 'draft');
        ?>
        <div class="stats">
            <div class="stat-card">
                <div class="num"><?php echo count($posts); ?></div>
                <div class="label">Total Posts</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo count($published); ?></div>
                <div class="label">Published</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo count($drafts); ?></div>
                <div class="label">Drafts</div>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="table-wrap">
            <?php if (!empty($posts)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <div class="post-title"><?php echo e($post['title']); ?></div>
                        </td>
                        <td><span style="color:#737373"><?php echo e($post['category']); ?></span></td>
                        <td>
                            <span class="status <?php echo $post['status']; ?>">
                                <span class="dot"></span>
                                <?php echo ucfirst($post['status']); ?>
                            </span>
                        </td>
                        <td style="color:#525252; white-space:nowrap"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <?php if ($post['status'] === 'published'): ?>
                                <a href="<?php echo SITE_URL . '/' . e($post['slug']); ?>" target="_blank" class="btn btn-ghost btn-sm" title="View">
                                    <i data-lucide="eye" style="width:13px;height:13px"></i>
                                </a>
                                <?php endif; ?>
                                <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-ghost btn-sm" title="Edit">
                                    <i data-lucide="pencil" style="width:13px;height:13px"></i>
                                </a>
                                <button onclick="confirmDelete(<?php echo $post['id']; ?>, '<?php echo e(addslashes($post['title'])); ?>')" class="btn btn-danger btn-sm" title="Delete">
                                    <i data-lucide="trash-2" style="width:13px;height:13px"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty">
                <p>No posts yet. Create your first guide!</p>
                <a href="create.php" class="btn btn-primary">Create Post</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="confirm-modal" id="deleteModal">
        <div class="confirm-box">
            <h3>Delete Post?</h3>
            <p id="deleteMsg">Are you sure you want to delete this post? This action cannot be undone.</p>
            <div class="btns">
                <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                <form method="POST" action="delete.php" id="deleteForm">
                    <input type="hidden" name="id" id="deleteId">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF()); ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function confirmDelete(id, title) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteMsg').textContent = `Are you sure you want to delete "${title}"? This action cannot be undone.`;
            document.getElementById('deleteModal').classList.add('show');
        }
        function closeModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>