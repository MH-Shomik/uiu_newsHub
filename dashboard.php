<?php
session_start();
require_once 'includes/db_connect.php';

// Auth Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'moderator'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION;

// Fetch Stats
try {
    $stats = [];
    $stats['total_news'] = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
    $stats['active_alerts'] = $pdo->query("SELECT COUNT(*) FROM alerts WHERE is_active=1")->fetchColumn();
    $stats['total_views'] = $pdo->query("SELECT SUM(views) FROM news")->fetchColumn();
    
    // Fetch Recent News
    $stmt = $pdo->query("
        SELECT n.*, c.name as category_name, u.full_name as author_name 
        FROM news n 
        JOIN categories c ON n.category_id = c.category_id 
        JOIN users u ON n.author_id = u.user_id 
        ORDER BY n.created_at DESC LIMIT 5
    ");
    $recentNews = $stmt->fetchAll();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UIU NewsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        heading: ['"Outfit"', 'sans-serif'],
                    },
                     colors: {
                        jasmine: { DEFAULT: '#ffe588', 100: '#4f3e00', 200: '#9d7b00', 300: '#ecb900', 400: '#ffd53b', 500: '#ffe588', 600: '#ffeba1', 700: '#fff0b9', 800: '#fff5d0', 900: '#fffae8' },
                        tangerine_dream: { DEFAULT: '#f79d65', 100: '#421b03', 200: '#843707', 300: '#c6520a', 400: '#f37222', 500: '#f79d65', 600: '#f8b083', 700: '#fac4a2', 800: '#fcd8c1', 900: '#fdebe0' },
                        strawberry_red: { DEFAULT: '#f35252', 100: '#3d0404', 200: '#7a0808', 300: '#b70d0d', 400: '#ef1616', 500: '#f35252', 600: '#f57676', 700: '#f89898', 800: '#fababa', 900: '#fddddd' },
                        aquamarine: { DEFAULT: '#5ef2d5', 100: '#053e33', 200: '#0a7d66', 300: '#0fbb98', 400: '#20edc4', 500: '#5ef2d5', 600: '#7ff5dd', 700: '#9ff7e6', 800: '#bffaee', 900: '#dffcf7' },
                        cool_sky: { DEFAULT: '#60b5ff', 100: '#002646', 200: '#004b8d', 300: '#0071d3', 400: '#1b94ff', 500: '#60b5ff', 600: '#81c4ff', 700: '#a0d3ff', 800: '#c0e1ff', 900: '#dff0ff' }
                    },
                    boxShadow: {
                        'soft': '0 20px 40px -15px rgba(0, 0, 0, 0.05)',
                        'glow': '0 0 20px rgba(96, 181, 255, 0.35)',
                    }
                }
            }
        }
    </script>
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
                <a href="dashboard.php" class="flex items-center gap-3 px-6 py-3.5 bg-cool_sky-600 rounded-2xl text-white font-semibold shadow-lg shadow-cool_sky-900/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    Dashboard
                </a>
                <a href="#" class="flex items-center gap-3 px-6 py-3.5 text-slate-400 hover:bg-white/5 hover:text-white rounded-2xl transition-all font-medium">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                    News Articles
                </a>
                <a href="#" class="flex items-center gap-3 px-6 py-3.5 text-slate-400 hover:bg-white/5 hover:text-white rounded-2xl transition-all font-medium">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    Alerts
                </a>
                 <?php if($user['role'] === 'admin'): ?>
                <a href="#" class="flex items-center gap-3 px-6 py-3.5 text-slate-400 hover:bg-white/5 hover:text-white rounded-2xl transition-all font-medium">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Users
                </a>
                <?php endif; ?>
            </nav>

            <div class="p-6 border-t border-slate-800">
                <a href="logout.php" class="flex items-center gap-3 text-slate-400 hover:text-white transition-colors px-4 py-2 hover:bg-white/5 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    <span class="font-medium">Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50">
            <!-- Topbar -->
            <header class="h-20 bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 flex justify-between items-center px-8 shadow-sm">
                <h2 class="font-heading text-xl font-bold text-slate-800">Dashboard Overview</h2>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <span class="block text-sm font-bold text-slate-700"><?php echo htmlspecialchars($user['full_name']); ?></span>
                        <span class="block text-xs font-medium text-cool_sky-600 uppercase tracking-wide"><?php echo htmlspecialchars($user['role']); ?></span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold shadow-md">
                        <?php echo substr($user['full_name'], 0, 1); ?>
                    </div>
                </div>
            </header>

            <div class="p-8 max-w-7xl mx-auto">
                
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Card 1 -->
                    <div class="bg-white p-6 rounded-[1.5rem] shadow-soft border border-slate-100 flex items-center justify-between group hover:-translate-y-1 transition-transform">
                        <div>
                            <p class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-2">Total Articles</p>
                            <h3 class="font-heading text-4xl font-bold text-slate-900"><?php echo $stats['total_news']; ?></h3>
                        </div>
                        <div class="w-16 h-16 rounded-2xl bg-cool_sky-50 text-cool_sky-600 flex items-center justify-center group-hover:bg-cool_sky-600 group-hover:text-white transition-colors shadow-sm">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white p-6 rounded-[1.5rem] shadow-soft border border-slate-100 flex items-center justify-between group hover:-translate-y-1 transition-transform">
                        <div>
                            <p class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-2">Active Alerts</p>
                            <h3 class="font-heading text-4xl font-bold text-slate-900"><?php echo $stats['active_alerts']; ?></h3>
                        </div>
                        <div class="w-16 h-16 rounded-2xl bg-jasmine-50 text-jasmine-400 flex items-center justify-center group-hover:bg-jasmine-400 group-hover:text-amber-900 transition-colors shadow-sm">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white p-6 rounded-[1.5rem] shadow-soft border border-slate-100 flex items-center justify-between group hover:-translate-y-1 transition-transform">
                        <div>
                            <p class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-2">Total Reads</p>
                            <h3 class="font-heading text-4xl font-bold text-slate-900"><?php echo number_format($stats['total_views']); ?></h3>
                        </div>
                        <div class="w-16 h-16 rounded-2xl bg-aquamarine-50 text-aquamarine-500 flex items-center justify-center group-hover:bg-aquamarine-500 group-hover:text-white transition-colors shadow-sm">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities / News -->
                <div class="bg-white rounded-[2rem] shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-white">
                        <h3 class="font-heading font-bold text-xl text-slate-900">Recent News Articles</h3>
                        <a href="#" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-cool_sky-500 transition-colors shadow-lg shadow-slate-900/20">+ Post New Story</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-8 py-5">Title</th>
                                    <th class="px-6 py-5">Category</th>
                                    <th class="px-6 py-5">Author</th>
                                    <th class="px-6 py-5">Views</th>
                                    <th class="px-6 py-5">Status</th>
                                    <th class="px-6 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($recentNews as $item): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-slate-900 line-clamp-1 max-w-xs"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="text-xs text-slate-400 mt-1 font-medium"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="inline-block px-3 py-1 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 shadow-sm">
                                            <?php echo htmlspecialchars($item['category_name']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-medium text-slate-600"><?php echo htmlspecialchars($item['author_name']); ?></td>
                                    <td class="px-6 py-5 text-sm font-bold text-slate-700"><?php echo $item['views']; ?></td>
                                    <td class="px-6 py-5">
                                        <?php if($item['status'] == 'published'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-aquamarine-100 text-aquamarine-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-aquamarine-500"></span>
                                                Published
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-jasmine-100 text-jasmine-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-jasmine-500"></span>
                                                Draft
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <button class="text-slate-400 hover:text-cool_sky-600 font-bold text-sm transition-colors px-3 py-1 hover:bg-cool_sky-50 rounded-lg">Edit</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
