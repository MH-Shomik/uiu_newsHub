<?php
session_start();
require_once 'includes/db_connect.php';

// Auth Check (Admin/Moderator Only)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'moderator'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']); // In a real app, sanitize HTML from RTE
    $category_id = $_POST['category_id'];
    $image_url = trim($_POST['image_url']);
    $status = $_POST['status'];

    if (empty($title) || empty($content) || empty($category_id)) {
        $error = "Title, Content, and Category are required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO news (title, content, category_id, author_id, image_url, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $content, $category_id, $_SESSION['user_id'], $image_url, $status]);
            $success = "News article published successfully!";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch Categories
try {
    $cats = $pdo->query("SELECT * FROM categories")->fetchAll();
} catch (PDOException $e) { $cats = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post News | UIU NewsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Simple RTE -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        heading: ['"Outfit"', 'sans-serif'],
                    },
                     colors: {
                        jasmine: { DEFAULT: '#ffe588', 400: '#ffd53b' },
                        tangerine_dream: { DEFAULT: '#f79d65', 500: '#f79d65' },
                        aquamarine: { DEFAULT: '#5ef2d5', 500: '#5ef2d5' },
                        cool_sky: { DEFAULT: '#60b5ff', 500: '#60b5ff', 600: '#1b94ff', 50: '#f0f9ff' }
                    }
                }
            }
        }
    </script>
    <style>
        .ck-editor__editable { min-height: 300px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-72 bg-slate-900 text-white hidden md:flex flex-col border-r border-slate-800 shadow-xl z-20">
            <div class="p-8 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-cool_sky-500 flex items-center justify-center font-bold text-white shadow-glow">U</div>
                <span class="font-heading font-bold text-xl tracking-wide">NewsHub<span class="text-cool_sky-400">.</span></span>
            </div>
            <nav class="flex-1 px-4 space-y-2 mt-4">
                <a href="dashboard.php" class="flex items-center gap-3 px-6 py-3.5 text-slate-400 hover:bg-white/5 hover:text-white rounded-2xl transition-all font-medium">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    Dashboard
                </a>
                 <a href="news_create.php" class="flex items-center gap-3 px-6 py-3.5 bg-cool_sky-600 rounded-2xl text-white font-semibold shadow-lg shadow-cool_sky-900/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Post News
                </a>
            </nav>
        </aside>

        <!-- Main -->
        <main class="flex-1 overflow-y-auto bg-slate-50">
             <!-- Header -->
            <header class="h-20 bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 flex justify-between items-center px-8 shadow-sm">
                <h2 class="font-heading text-xl font-bold text-slate-800">Create New Article</h2>
                <a href="dashboard.php" class="text-sm font-bold text-slate-500 hover:text-slate-800">Cancel</a>
            </header>

            <div class="p-8 max-w-4xl mx-auto">
                <?php if ($success): ?>
                <div class="bg-aquamarine-50 border border-aquamarine-200 text-aquamarine-700 px-6 py-4 rounded-2xl mb-6 font-bold flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="bg-strawberry_red-50 border border-strawberry_red-200 text-strawberry_red-700 px-6 py-4 rounded-2xl mb-6 font-bold flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form action="" method="POST" class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Article Title</label>
                        <input type="text" name="title" class="w-full px-5 py-4 text-lg font-heading font-bold rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-cool_sky-500 focus:bg-white transition-all" placeholder="Enter a catchy headline..." required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Category</label>
                            <select name="category_id" class="w-full px-5 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-cool_sky-500 cursor-pointer" required>
                                <?php foreach($cats as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Status</label>
                            <select name="status" class="w-full px-5 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-cool_sky-500 cursor-pointer">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Featured Image URL</label>
                        <input type="url" name="image_url" class="w-full px-5 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-cool_sky-500" placeholder="https://example.com/image.jpg">
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Content</label>
                        <textarea name="content" id="editor" class="w-full rounded-xl border-slate-200"></textarea>
                    </div>

                    <button type="submit" class="w-full py-4 bg-slate-900 text-white font-bold rounded-xl hover:bg-cool_sky-600 transition-all shadow-lg text-lg">
                        Publish Story
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script>
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => {
                console.error(error);
            });
    </script>
</body>
</html>
