<?php
require_once __DIR__ . '/functions.php';

 $posts = getAllPosts('published');
 $totalPosts = count($posts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guides — <?php echo e(SITE_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #050505; }
        .guide-card { transition: all 0.3s ease; }
        .guide-card:hover { background: rgba(23,23,23,0.4) !important; transform: translateY(-2px); }
        .guide-card:hover .card-img { opacity: 0.8; transform: scale(1.05); }
        .card-img { transition: all 0.7s ease-out; opacity: 0.6; }
        .tag { background: rgba(129,140,248,0.1); color: #818cf8; }
        .grid-bg {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="text-white antialiased">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 mix-blend-difference">
        <div class="container mx-auto px-6 md:px-12 py-6 flex items-center justify-between">
            <a href="<?php echo SITE_URL; ?>" class="text-sm font-bold tracking-tight "><span class="text-xl uppercase">CODE HUNT'S </span> <span class="text-xl mx-3">/</span> research</a>
            <div class="flex items-center gap-8">
                <a href="<?php echo SITE_URL; ?>" class="text-sm font-medium text-neutral-300 hover:text-white transition-colors">Home</a>
                <a href="#" class="text-sm font-medium text-neutral-300 hover:text-white transition-colors">About</a>
                <a href="mailto:hello@mnfst.studio" class="text-sm font-medium text-neutral-300 hover:text-white transition-colors">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="min-h-[70vh] flex items-center relative grid-bg">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top right, rgba(49,46,129,0.2), #18181b, #000000); opacity: 0.6;"></div>
        <div class="container mx-auto px-6 md:px-12 pt-32 pb-16 relative z-10 w-full">
            <div class="max-w-3xl">
                <p class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 mb-6">Guides & Insights</p>
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-medium tracking-tighter leading-[0.9] mb-8">
                    Build AI<br>
                    <span class="text-neutral-500">products that</span><br>
                    actually ship.
                </h1>
                <p class="text-base md:text-lg font-light text-neutral-400 leading-relaxed max-w-xl">
                    Practical guides on AI engineering, infrastructure decisions, and product strategy — from people who've shipped real AI products.
                </p>
            </div>
        </div>
    </section>

    <!-- Guides Grid -->
    <section class="container mx-auto px-6 md:px-12 py-24">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 mb-3"><?php echo $totalPosts; ?> articles</p>
                <h2 class="text-4xl md:text-5xl font-medium tracking-tighter">Latest Guides</h2>
            </div>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($posts as $post): ?>
            <a href="<?php echo SITE_URL . '/' . e($post['slug']); ?>" class="guide-card group block rounded-2xl border border-white/[0.08] bg-white/[0.02] overflow-hidden cursor-pointer">
                <div class="aspect-[16/10] overflow-hidden">
                    <img src="<?php echo e($post['featured_image']); ?>" alt="<?php echo e($post['title']); ?>" class="card-img w-full h-full object-cover" loading="lazy">
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="tag text-[10px] font-mono uppercase tracking-widest px-3 py-1 rounded-full"><?php echo e($post['category']); ?></span>
                        <span class="text-[11px] text-neutral-500"><?php echo e($post['reading_time']); ?></span>
                    </div>
                    <h3 class="text-lg font-medium leading-snug mb-3 group-hover:text-indigo-400 transition-colors"><?php echo e($post['title']); ?></h3>
                    <p class="text-sm text-neutral-500 font-light leading-relaxed line-clamp-2"><?php echo e($post['excerpt']); ?></p>
                    <div class="mt-5 flex items-center gap-2 text-xs text-neutral-600">
                        <span><?php echo e($post['author']); ?></span>
                        <span>·</span>
                        <span><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($posts)): ?>
        <div class="text-center py-24">
            <p class="text-neutral-500 text-lg">No guides published yet.</p>
            <p class="text-neutral-600 text-sm mt-2">Check back soon or head to the admin panel to create one.</p>
        </div>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/[0.05]">
        <div class="container mx-auto px-6 md:px-12 py-12 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs text-neutral-600">© <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?></p>
            <div class="flex items-center gap-6">
                <a href="#" class="text-neutral-600 hover:text-neutral-400 transition-colors"><i data-lucide="twitter" class="w-4 h-4"></i></a>
                <a href="#" class="text-neutral-600 hover:text-neutral-400 transition-colors"><i data-lucide="github" class="w-4 h-4"></i></a>
                <a href="#" class="text-neutral-600 hover:text-neutral-400 transition-colors"><i data-lucide="linkedin" class="w-4 h-4"></i></a>
            </div>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
</body>
</html>