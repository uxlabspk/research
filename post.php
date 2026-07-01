<?php
require_once __DIR__ . '/functions.php';

 $slug = $_GET['slug'] ?? '';
 $post = getPostBySlug($slug);

if (!$post) {
    http_response_code(404);
    // You could redirect or show a 404 page
    die('Post not found.');
}

 $parsedContent = parseMarkdown($post['content']);

// Get previous/next posts for navigation
 $pdo = getDB();
 $prevStmt = $pdo->prepare("SELECT slug, title FROM posts WHERE status='published' AND created_at < ? ORDER BY created_at DESC LIMIT 1");
 $prevStmt->execute([$post['created_at']]);
 $prevPost = $prevStmt->fetch();

 $nextStmt = $pdo->prepare("SELECT slug, title FROM posts WHERE status='published' AND created_at > ? ORDER BY created_at ASC LIMIT 1");
 $nextStmt->execute([$post['created_at']]);
 $nextPost = $nextStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($post['title']); ?> — <?php echo e(SITE_NAME); ?></title>
    <meta name="description" content="<?php echo e($post['excerpt']); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Highlight.js CSS (choose a theme) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
    <!-- Highlight.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #050505; }
        .grid-bg {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
        }

        /* Prose styles for rendered markdown */
        .prose { color: #d4d4d4; font-weight: 300; line-height: 1.8; font-size: 17px; }
        .prose h2 { color: #fff; font-size: 1.75rem; font-weight: 500; letter-spacing: -0.025em; margin-top: 3rem; margin-bottom: 1.25rem; line-height: 1.3; }
        .prose h3 { color: #fff; font-size: 1.375rem; font-weight: 500; letter-spacing: -0.015em; margin-top: 2.5rem; margin-bottom: 1rem; }
        .prose h4 { color: #e5e5e5; font-size: 1.125rem; font-weight: 500; margin-top: 2rem; margin-bottom: 0.75rem; }
        .prose p { margin-bottom: 1.5rem; }
        .prose a { color: #818cf8; text-decoration: underline; text-underline-offset: 3px; }
        .prose a:hover { color: #a5b4fc; }
        .prose strong { color: #fff; font-weight: 500; }
        .prose ul, .prose ol { margin-bottom: 1.5rem; padding-left: 1.5rem; }
        .prose ul { list-style-type: disc; }
        .prose ol { list-style-type: decimal; }
        .prose li { margin-bottom: 0.5rem; }
        .prose blockquote {
            border-left: 3px solid #818cf8;
            padding-left: 1.25rem;
            margin: 2rem 0;
            color: #a3a3a3;
            font-style: italic;
        }
        .prose code {
            background: rgba(255,255,255,0.06);
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 0.875em;
            font-family: 'SF Mono', 'Fira Code', monospace;
            color: #c4b5fd;
        }
        .prose pre {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin: 2rem 0;
            overflow-x: auto;
        }
        .prose pre code {
            background: none;
            padding: 0;
            color: #d4d4d4;
            font-size: 0.8125rem;
            line-height: 1.7;
        }
        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            font-size: 0.9375rem;
        }
        .prose th {
            text-align: left;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            color: #fff;
            font-weight: 500;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .prose td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .prose tr:hover td { background: rgba(255,255,255,0.02); }
        .prose img { border-radius: 12px; margin: 2rem 0; max-width: 100%; }
        .prose hr { border: none; border-top: 1px solid rgba(255,255,255,0.08); margin: 3rem 0; }
    </style>
</head>
<body class="text-white antialiased">

        <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 mix-blend-difference bg-black/20 backdrop-blur">
        <div class="container mx-auto px-6 md:px-12 py-6 flex items-center justify-between">
            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>" class="text-sm font-bold tracking-tight">
                <span class="text-xl uppercase">CODE HUNT'S </span>
                <span class="text-xl mx-3">/</span>
                <span>research</span>
            </a>

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex items-center gap-8">
                <a href="https://codehuntspk.com" class="text-sm font-medium text-neutral-300 hover:text-white transition-colors">Our Website</a>
            </div>

            <!-- Hamburger Menu Button (Mobile) -->
            <button id="mobile-menu-button" class="md:hidden p-2 rounded-md text-neutral-300 hover:text-white hover:bg-white/10 transition-colors">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Mobile Menu (Hidden by Default) -->
        <div id="mobile-menu" class="hidden md:hidden bg-black/80 backdrop-blur-sm border-t border-white/[0.05]">
            <div class="container mx-auto px-6 py-4 flex flex-col gap-4">
                <a href="https://codehuntspk.com" class="text-sm font-medium text-neutral-300 hover:text-white transition-colors py-2">Our Website</a>
            </div>
        </div>
    </nav>

    <!-- Article Header -->
    <header class="relative grid-bg">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top right, rgba(49,46,129,0.15), #18181b, #000000); opacity: 0.5;"></div>
        <div class="max-w-7xl mx-auto px-6 md:px-12 pt-36 pb-12 relative z-10">
            <a href="<?php echo SITE_URL; ?>" class="inline-flex items-center gap-2 text-sm text-neutral-500 hover:text-neutral-300 transition-colors mb-8">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to all guides
            </a>
            <div class="flex items-center gap-3 mb-6">
                <span class="text-[10px] font-mono uppercase tracking-widest px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400"><?php echo e($post['category']); ?></span>
                <span class="text-[11px] text-neutral-500"><?php echo e($post['reading_time']); ?></span>
            </div>
            <h1 class="text-3xl md:text-5xl lg:text-6xl font-medium tracking-tighter leading-[0.95] mb-6"><?php echo e($post['title']); ?></h1>
            <p class="text-lg text-neutral-400 font-light leading-relaxed mb-8"><?php echo e($post['excerpt']); ?></p>
            <div class="flex items-center gap-4 text-sm text-neutral-500">
                <span><?php echo e($post['author']); ?></span>
                <span class="w-1 h-1 rounded-full bg-neutral-700"></span>
                <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
            </div>
        </div>

        <?php if ($post['featured_image']): ?>
        <div class="max-w-7xl mx-auto px-6 md:px-12 pb-16 relative z-10">
            <img src="<?php echo e($post['featured_image']); ?>" alt="<?php echo e($post['title']); ?>" class="w-full rounded-2xl border border-white/[0.08] opacity-70">
        </div>
        <?php endif; ?>
    </header>

    <!-- Article Content -->
    <main class="max-w-7xl mx-auto px-6 md:px-12 py-16">
        <article class="prose">
            <?php echo $parsedContent; ?>
        </article>

        <!-- Post Navigation -->
        <nav class="mt-20 pt-12 border-t border-white/[0.08] grid md:grid-cols-2 gap-8">
            <?php if ($prevPost): ?>
            <a href="<?php echo SITE_URL . '/' . e($prevPost['slug']); ?>" class="group p-6 rounded-xl border border-white/[0.05] hover:border-white/[0.15] hover:bg-white/[0.02] transition-all">
                <p class="text-[10px] font-mono uppercase tracking-widest text-neutral-600 mb-3">← Previous</p>
                <p class="text-sm font-medium text-neutral-300 group-hover:text-white transition-colors leading-snug"><?php echo e($prevPost['title']); ?></p>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

            <?php if ($nextPost): ?>
            <a href="<?php echo SITE_URL . '/' . e($nextPost['slug']); ?>" class="group p-6 rounded-xl border border-white/[0.05] hover:border-white/[0.15] hover:bg-white/[0.02] transition-all text-right">
                <p class="text-[10px] font-mono uppercase tracking-widest text-neutral-600 mb-3">Next →</p>
                <p class="text-sm font-medium text-neutral-300 group-hover:text-white transition-colors leading-snug"><?php echo e($nextPost['title']); ?></p>
            </a>
            <?php endif; ?>
        </nav>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/[0.05]">
        <div class="container mx-auto px-6 md:px-12 py-12 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs text-neutral-600">© <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?></p>
            <a href="<?php echo SITE_URL; ?>" class="text-sm text-neutral-500 hover:text-neutral-300 transition-colors">Back to all guides →</a>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
    <script>
    // Wait for the DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Get the mobile menu button and the mobile menu
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        // Toggle the mobile menu when the button is clicked
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });

        // Close the mobile menu when clicking outside of it
        document.addEventListener('click', function(event) {
            const isClickInsideMenu = mobileMenu.contains(event.target);
            const isClickOnButton = mobileMenuButton.contains(event.target);

            if (!isClickInsideMenu && !isClickOnButton) {
                mobileMenu.classList.add('hidden');
            }
        });
    });
</script>

<script>
//   hljs.configure({ languages: ['php', 'javascript', 'html', 'css', 'bash'] });
  hljs.highlightAll();
</script>
</body>
</html>