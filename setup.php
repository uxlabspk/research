<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

 $message = '';
 $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDB();

        // Create posts table
        $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(255) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            excerpt TEXT,
            content LONGTEXT,
            featured_image VARCHAR(500),
            category VARCHAR(100) DEFAULT 'Guide',
            author VARCHAR(100) DEFAULT 'MNFST Studio',
            reading_time VARCHAR(20),
            status ENUM('published', 'draft') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Create admins table
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Insert default admin (username: admin, password: admin123)
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO admins (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', $hash]);

        // Insert sample posts
        $samplePosts = [
            [
                'title' => 'Hosted LLM API or self-hosted model: how to actually decide',
                'excerpt' => 'A practical framework for choosing between OpenAI/Claude APIs and running your own LLM — cost, latency, privacy, and control compared.',
                'content' => "## The Core Trade-off\n\nWhen building AI-powered features, one of the first decisions you'll face is whether to use a **hosted LLM API** (like OpenAI, Anthropic, or Google) or **self-host an open-source model** (like Llama 3, Mistral, or Qwen).\n\nThere's no universal right answer — but there *is* a right answer for your specific situation.\n\n## When Hosted APIs Win\n\n### Speed of Integration\nHosted APIs let you go from zero to production in hours. No infrastructure to manage, no model weights to download, no GPU provisioning.\n\n### Cost at Low Volume\nIf you're processing fewer than 1M tokens/day, hosted APIs are almost always cheaper. You pay per token with zero fixed costs.\n\n### State-of-the-Art Quality\nGPT-4o and Claude 3.5 Sonnet still outperform most open-source models on complex reasoning tasks. If quality is paramount and budget allows, hosted wins.\n\n## When Self-Hosting Wins\n\n### Data Privacy\nIf your data can't leave your infrastructure (healthcare, finance, legal), self-hosting is non-negotiable.\n\n### Cost at High Volume\nOnce you cross ~10-50M tokens/day, self-hosting becomes dramatically cheaper. A single H100 can serve millions of tokens per day.\n\n### Latency & Control\nNo network round-trip to an external API. You control the model, the version, the timeouts, the rate limits.\n\n## The Decision Framework\n\n| Factor | Hosted API | Self-Hosted |\n|--------|-----------|-------------|\n| Time to deploy | Hours | Days-weeks |\n| Low volume cost | ✅ Cheaper | ❌ More expensive |\n| High volume cost | ❌ Expensive | ✅ Much cheaper |\n| Data privacy | ⚠️ Shared | ✅ Full control |\n| Model quality | ✅ SOTA | ⚠️ Good but not SOTA |\n| Customization | ❌ Limited | ✅ Full fine-tuning |\n\n## My Recommendation\n\nStart with hosted APIs. Ship fast, validate your use case, understand your actual token volumes. Once you hit scale *or* have a hard privacy requirement, migrate to self-hosted.\n\n> \"Make it work, make it right, make it fast\" — and in AI, hosted APIs help you make it work fastest.",
                'category' => 'Infrastructure',
                'featured_image' => 'https://picsum.photos/seed/hosted-llm/800/500.jpg',
            ],
            [
                'title' => 'Building AI agents: from chatbots to autonomous systems',
                'excerpt' => 'Understand the spectrum of AI agent architectures — from simple prompt-response loops to multi-step autonomous planning systems.',
                'content' => "## What Makes an AI \"Agent\"?\n\nAn AI agent is a system that uses an LLM as its reasoning engine to **perceive, decide, and act** — not just respond to a single prompt.\n\nThe key difference from a chatbot: **agency**. The system can take multiple steps, use tools, and pursue a goal over time.\n\n## The Agent Spectrum\n\n### Level 1: Prompt-Response Chatbot\nSimple input → LLM → output. No tools, no memory, no planning.\n\n### Level 2: Tool-Using Assistant\nThe LLM can call predefined functions (search, calculator, API calls). ReAct pattern.\n\n### Level 3: Multi-Step Planner\nBreaks down complex tasks into subtasks, executes them sequentially, handles errors.\n\n### Level 4: Autonomous Agent\nSets its own sub-goals, iterates on its approach, uses memory across sessions.\n\n## Building Blocks\n\n- **LLM**: The reasoning core\n- **Tools**: Functions the agent can call\n- **Memory**: Short-term (context window) and long-term (vector DB)\n- **Planning**: Task decomposition and strategy\n- **Execution**: Actually running the planned steps\n\n## Common Pitfalls\n\n1. **Over-engineering**: Start at Level 2, not Level 4\n2. **Infinite loops**: Always set max iteration limits\n3. **Tool confusion**: Keep tool descriptions crystal clear\n4. **Cost blowup**: Token usage multiplies fast with multi-step agents",
                'category' => 'AI Engineering',
                'featured_image' => 'https://picsum.photos/seed/ai-agents/800/500.jpg',
            ],
            [
                'title' => 'RAG vs fine-tuning: which path for your AI product?',
                'excerpt' => 'A clear comparison of retrieval-augmented generation and fine-tuning — when each makes sense and why RAG is often the right first move.',
                'content' => "## The Two Paths\n\nWhen you need an AI system that \"knows\" your specific data, two approaches dominate:\n\n1. **RAG** (Retrieval-Augmented Generation): Fetch relevant documents and feed them into the prompt\n2. **Fine-tuning**: Train the model on your data to change its internal weights\n\n## RAG: The Practical Choice\n\n### How It Works\n1. User asks a question\n2. System searches your knowledge base (vector DB)\n3. Relevant chunks are injected into the prompt\n4. LLM generates an answer grounded in those chunks\n\n### Strengths\n- ✅ No training needed — just index your documents\n- ✅ Always up-to-date (re-index when docs change)\n- ✅ Source attribution (you know *which* doc the answer came from)\n- ✅ Cheap to get started\n\n### Weaknesses\n- ⚠️ Retrieval quality matters (garbage in, garbage out)\n- ⚠️ Context window limits how much you can include\n- ⚠️ Adds latency (search + generation)\n\n## Fine-Tuning: The Precision Tool\n\n### When It Makes Sense\n- You need a specific **style** or **format**\n- The knowledge needs to be **internalized** (e.g., domain-specific terminology)\n- You're optimizing for **latency** (no retrieval step)\n\n### When It Doesn't\n- You just need to query documents → use RAG\n- Your data changes frequently → RAG is easier to update\n- You have limited training data → fine-tuning can hurt performance\n\n## The Verdict\n\n> **Start with RAG. Add fine-tuning only when RAG hits a clear wall.**\n\nMost teams that jump straight to fine-tuning regret it. RAG gives you 90% of the value with 10% of the effort.",
                'category' => 'AI Strategy',
                'featured_image' => 'https://picsum.photos/seed/rag-finetuning/800/500.jpg',
            ],
            [
                'title' => 'Vector databases explained: why they matter for AI',
                'excerpt' => 'A developer-friendly guide to vector databases — what embeddings are, how similarity search works, and when you actually need one.',
                'content' => "## The Problem\n\nTraditional databases are great for exact matches. But AI needs **semantic similarity** — finding \"happy customer review\" when you search for \"positive feedback.\"\n\n## Embeddings: The Key Concept\n\nAn embedding is a vector (list of numbers) that captures the *meaning* of text. Similar meanings → similar vectors.\n\n```\n\"I love this product\" → [0.12, -0.34, 0.56, ...]\n\"This is amazing\"    → [0.11, -0.31, 0.58, ...]  ← Very similar!\n\"It broke after 2 days\" → [-0.45, 0.22, -0.11, ...]  ← Different\n```\n\n## How Vector Databases Work\n\n1. **Ingest**: Convert documents into embedding vectors\n2. **Index**: Build an efficient index (HNSW, IVF) for fast search\n3. **Query**: Convert search text to embedding, find nearest neighbors\n\n## Popular Options\n\n| Database | Type | Best For |\n|----------|------|----------|\n| Pinecone | Managed | Quick start, no ops |\n| Weaviate | Self-hosted/Managed | Production RAG |\n| Qdrant | Self-hosted | High performance, Rust-based |\n| pgvector | PostgreSQL ext | If you already use Postgres |\n| Chroma | Embedded | Local dev, prototyping |\n\n## Do You Need One?\n\nIf you're building RAG — **yes**. But start simple: Chroma for prototyping, then migrate to Qdrant or pgvector for production.",
                'category' => 'Infrastructure',
                'featured_image' => 'https://picsum.photos/seed/vector-db/800/500.jpg',
            ],
            [
                'title' => 'Prompt engineering is dead — long live prompt engineering',
                'excerpt' => 'Why basic prompting skills are commoditized, but deep prompt engineering for production systems is more valuable than ever.',
                'content' => "## The Claim\n\n\"Prompt engineering is dead\" — you've heard this. The argument: as models get smarter, they need less careful prompting.\n\n## The Reality\n\nBasic prompting *is* commoditized. \"You are a helpful assistant\" doesn't cut it anymore — but it also doesn't need to.\n\nWhat's *not* dead:\n\n### 1. System Prompt Architecture\nDesigning the full system prompt stack: role, context, constraints, output format, examples. This is **software design**, not typing nice sentences.\n\n### 2. Tool-Use Prompting\nGetting an LLM to reliably call the right tool with the right parameters is genuinely hard. Requires careful schema design and prompt structure.\n\n### 3. Evaluation-Driven Optimization\nThe real skill: define metrics → test prompt variants → measure → iterate. This is **engineering**.\n\n### 4. Failure Mode Analysis\nUnderstanding *why* prompts fail: hallucination patterns, edge cases, adversarial inputs. Then designing mitigations.\n\n## What Changed\n\n- ❌ \"Add 'think step by step'\" → commoditized\n- ✅ \"Design a prompt architecture that handles 50 edge cases with <2% failure rate\" → highly valuable\n\n## The Skill Shift\n\nPrompt engineering evolved from **writing** to **systems engineering**. The title is misleading — the discipline is more important than ever.",
                'category' => 'AI Engineering',
                'featured_image' => 'https://picsum.photos/seed/prompt-eng/800/500.jpg',
            ],
            [
                'title' => 'From prototype to production: the AI engineering playbook',
                'excerpt' => 'The gap between a working demo and a reliable production AI system — and the concrete steps to cross it.',
                'content' => "## The Prototype Trap\n\nEvery AI demo looks amazing. Every production system is a nightmare. The gap between them is real and predictable.\n\n## What Changes at Scale\n\n### Reliability\n- Prototype: Works 80% of the time, you manually retry\n- Production: Needs 99.5%+ success rate with automated fallbacks\n\n### Latency\n- Prototype: 5-10 second responses feel fine\n- Production: Users expect <2 seconds\n\n### Cost\n- Prototype: $0.50/test doesn't matter\n- Production: $0.01/request × 1M requests = $10K/month\n\n### Observability\n- Prototype: Print statements\n- Production: Logging, tracing, alerting, dashboards\n\n## The Playbook\n\n### Phase 1: Hardening\n- Add input validation and sanitization\n- Implement retry logic with exponential backoff\n- Add fallback models (e.g., GPT-4o → GPT-4o-mini)\n- Set timeout limits\n\n### Phase 2: Evaluation\n- Build a test set of 100+ representative inputs\n- Define evaluation metrics (accuracy, relevance, format compliance)\n- Automated regression testing\n\n### Phase 3: Infrastructure\n- Request queuing (don't hammer the API)\n- Caching for repeated queries\n- Rate limiting per user\n- Database for conversation history\n\n### Phase 4: Monitoring\n- Log every request/response pair\n- Track latency percentiles (p50, p95, p99)\n- Alert on error rate spikes\n- Cost tracking per feature/user\n\n## The Hard Truth\n\n> Shipping an AI prototype takes days. Making it production-ready takes **months**. Plan accordingly.",
                'category' => 'AI Strategy',
                'featured_image' => 'https://picsum.photos/seed/prod-playbook/800/500.jpg',
            ],
        ];

        foreach ($samplePosts as $post) {
            $slug = createSlug($post['title']);
            $readingTime = calculateReadingTime($post['content']);
            $stmt = $pdo->prepare("INSERT IGNORE INTO posts (slug, title, excerpt, content, featured_image, category, author, reading_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published')");
            $stmt->execute([
                $slug,
                $post['title'],
                $post['excerpt'],
                $post['content'],
                $post['featured_image'],
                $post['category'],
                'MNFST Studio',
                $readingTime,
            ]);
        }

        $message = 'Setup complete! Database tables created, admin account added (username: <strong>admin</strong>, password: <strong>admin123</strong>), and 6 sample posts inserted. <strong style="color:#ef4444">DELETE THIS FILE NOW.</strong>';
    } catch (Exception $e) {
        $error = 'Setup failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup — MNFST Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #050505; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: rgba(23,23,23,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 48px; max-width: 600px; width: 100%; backdrop-filter: blur(12px); }
        h1 { font-size: 28px; font-weight: 600; margin-bottom: 8px; letter-spacing: -0.025em; }
        p.desc { color: #a3a3a3; font-size: 15px; margin-bottom: 32px; line-height: 1.6; }
        .msg { padding: 16px; border-radius: 12px; margin-bottom: 24px; font-size: 14px; line-height: 1.7; }
        .msg.success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
        .msg.error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }
        button { background: #fff; color: #000; border: none; padding: 14px 32px; border-radius: 12px; font-size: 14px; font-weight: 500; cursor: pointer; font-family: inherit; transition: all 0.15s; }
        button:hover { background: #e5e5e5; }
        .warning { margin-top: 24px; padding: 12px; background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.15); border-radius: 8px; color: #f87171; font-size: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 Database Setup</h1>
        <p class="desc">This will create the required tables, add a default admin account, and insert sample guide posts.</p>

        <?php if ($message): ?>
            <div class="msg success"><?php echo $message; ?></div>
            <div class="warning">⚠️ Delete setup.php from your server immediately after running this.</div>
        <?php elseif ($error): ?>
            <div class="msg error"><?php echo $error; ?></div>
            <form method="POST"><button type="submit">Retry Setup</button></form>
        <?php else: ?>
            <form method="POST"><button type="submit">Run Setup</button></form>
            <div class="warning">Make sure you've updated config.php with your Hostinger database credentials first.</div>
        <?php endif; ?>
    </div>
</body>
</html>