<?php
session_start();
require_once 'includes/db_connect.php';

// Auth Check (Students Only)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

// 1. Fetch User Info (Simulated)
$userInfo = [
    'name' => $user_name,
    'id' => $_SESSION['user_id'], 
    'department' => 'CSE', 
    'credits_completed' => 85,
    'cgpa' => 3.76,
    'avatar' => "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=0f172a&color=fff"
];

// 2. Fetch "For You" Feed
try {
    $feedStmt = $pdo->query("
        SELECT n.*, c.name as category_name, c.slug as category_slug
        FROM news n 
        JOIN categories c ON n.category_id = c.category_id 
        WHERE c.slug IN ('academic', 'notice') AND n.status = 'published'
        ORDER BY n.created_at DESC LIMIT 6
    ");
    $personalizedFeed = $feedStmt->fetchAll();
} catch (PDOException $e) { $personalizedFeed = []; }

// 3. Fetch Trending News
try {
    $trendingStmt = $pdo->query("SELECT news_id, title, views FROM news WHERE status='published' ORDER BY views DESC LIMIT 4");
    $trendingNews = $trendingStmt->fetchAll();
} catch (PDOException $e) { $trendingNews = []; }

function getCategoryColor($slug) {
    if(!$slug) return 'bg-slate-500 text-white';
    $map = [
        'academic' => 'bg-cool_sky-500 text-white',
        'notice'   => 'bg-jasmine-400 text-slate-900',
    ];
    return $map[$slug] ?? 'bg-slate-500 text-white';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | UIU NewsHub</title>
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
                        jasmine: { DEFAULT: '#ffe588', 100: '#4f3e00', 400: '#ffd53b', 500: '#ffe588' },
                        tangerine_dream: { DEFAULT: '#f79d65', 500: '#f79d65' },
                        aquamarine: { DEFAULT: '#5ef2d5', 500: '#5ef2d5', 50: '#f0fdfa' },
                        cool_sky: { DEFAULT: '#60b5ff', 500: '#60b5ff', 600: '#1b94ff', 50: '#f0f9ff' }
                    },
                     boxShadow: {
                        'soft': '0 20px 40px -15px rgba(0, 0, 0, 0.05)',
                        'glow': '0 0 20px rgba(96, 181, 255, 0.35)',
                    }
                }
            }
        }
    </script>
    <style>
        .animate-bounce-in { animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes bounceIn { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-cool_sky-500 selection:text-white">

    <!-- Alerts Banner (Hidden by default) -->
    <div id="alert-banner" class="bg-slate-900 text-white overflow-hidden relative z-50 hidden">
        <div class="max-w-7xl mx-auto flex items-center h-12">
            <div class="bg-strawberry_red-500 text-white font-bold px-4 h-full flex items-center text-sm tracking-wider uppercase flex-shrink-0 z-10 shadow-lg">Live Alerts</div>
            <div class="flex-1 overflow-hidden relative h-full flex items-center bg-slate-800">
                <div id="alert-ticker" class="animate-marquee whitespace-nowrap flex gap-12 items-center text-sm font-medium pl-4"></div>
            </div>
        </div>
    </div>

    <!-- Sidebar Mobile -->
    <!-- (Skipping complex mobile sidebar for now, using sticky nav) -->

    <!-- Navbar -->
    <nav class="bg-white/90 backdrop-blur-xl border-b border-slate-200 sticky top-0 z-40 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                 <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cool_sky-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-glow group-hover:rotate-6 transition-transform">U</div>
                    <div class="flex flex-col">
                        <span class="font-heading font-bold text-xl text-slate-900 leading-none">NewsHub</span>
                        <span class="text-[0.65rem] font-bold text-cool_sky-500 uppercase tracking-[0.2em]">Student Portal</span>
                    </div>
                </a>
                <div class="flex items-center gap-4">
                    <a href="search.php" class="p-2.5 text-slate-400 hover:text-cool_sky-600 transition-colors rounded-full hover:bg-slate-100 bg-slate-50 border border-slate-100" title="Search News">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </a>
                     <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                         <div class="hidden md:flex flex-col text-right">
                             <span class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($userInfo['name']); ?></span>
                             <span class="text-xs text-slate-400 font-medium"><?php echo htmlspecialchars($userInfo['department']); ?> Student</span>
                         </div>
                         <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white shadow-sm ring-2 ring-slate-50">
                            <img src="<?php echo $userInfo['avatar']; ?>" alt="Avatar">
                         </div>
                         <a href="logout.php" class="p-2 text-slate-400 hover:text-strawberry_red-500 transition-colors" title="Logout">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                         </a>
                     </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Welcome & Stats Section -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 bg-gradient-to-r from-slate-900 to-slate-800 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl">
                <div class="absolute top-0 right-0 p-32 bg-cool_sky-500/10 rounded-full blur-3xl transform translate-x-12 -translate-y-12"></div>
                <div class="relative z-10">
                    <span class="text-cool_sky-400 font-bold uppercase tracking-wider text-xs mb-2 block"><?php echo date('l, F j, Y'); ?></span>
                    <h1 class="font-heading text-3xl md:text-4xl font-bold mb-4">Good <?php echo (date('H') < 12) ? 'Morning' : ((date('H') < 18) ? 'Afternoon' : 'Evening'); ?>, <?php echo explode(' ', $userInfo['name'])[0]; ?>! 👋</h1>
                    <p class="text-slate-300 max-w-lg mb-6">Stay updated with the latest academic notices, campus events, and department news.</p>
                    <div class="flex gap-3">
                         <a href="#news-feed-container" class="px-6 py-2.5 bg-white text-slate-900 font-bold rounded-xl text-sm hover:bg-cool_sky-50 transition-colors">Latest News</a>
                         <a href="search.php" class="px-6 py-2.5 bg-slate-700 text-white font-bold rounded-xl text-sm hover:bg-slate-600 transition-colors">Find Notices</a>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-soft flex flex-col justify-center items-center hover:scale-[1.02] transition-transform">
                    <div class="w-12 h-12 rounded-full bg-cool_sky-50 text-cool_sky-600 flex items-center justify-center mb-3">
                         <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                    <span class="font-heading text-3xl font-bold text-slate-900"><?php echo $userInfo['cgpa']; ?></span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">CGPA</span>
                </div>
                <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-soft flex flex-col justify-center items-center hover:scale-[1.02] transition-transform">
                     <div class="w-12 h-12 rounded-full bg-aquamarine-50 text-aquamarine-500 flex items-center justify-center mb-3">
                         <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <span class="font-heading text-3xl font-bold text-slate-900"><?php echo $userInfo['credits_completed']; ?></span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Credits</span>
                </div>
            </div>
        </section>

        <!-- Quick Actions Row -->
        <section class="flex gap-4 overflow-x-auto pb-4 no-scrollbar">
            <a href="index.php" class="flex-shrink-0 w-36 h-32 bg-white rounded-3xl border border-slate-100 p-4 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between group hover:border-cool_sky-200">
                <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-600 group-hover:bg-cool_sky-500 group-hover:text-white transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                </div>
                <span class="font-bold text-slate-700 text-sm group-hover:text-cool_sky-600">News Feed</span>
            </a>
            <a href="#" class="flex-shrink-0 w-36 h-32 bg-white rounded-3xl border border-slate-100 p-4 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between group hover:border-aquamarine-200">
                <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-600 group-hover:bg-aquamarine-500 group-hover:text-white transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="font-bold text-slate-700 text-sm group-hover:text-aquamarine-600">Routine</span>
            </a>
            <a href="#" class="flex-shrink-0 w-36 h-32 bg-white rounded-3xl border border-slate-100 p-4 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between group hover:border-tangerine_dream-200">
                <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-600 group-hover:bg-tangerine_dream-500 group-hover:text-white transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="font-bold text-slate-700 text-sm group-hover:text-tangerine_dream-600">Tuition</span>
            </a>
             <a href="#" class="flex-shrink-0 w-36 h-32 bg-white rounded-3xl border border-slate-100 p-4 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between group hover:border-jasmine-300">
                <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-600 group-hover:bg-jasmine-400 group-hover:text-slate-900 transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <span class="font-bold text-slate-700 text-sm group-hover:text-jasmine-600">Support</span>
            </a>
        </section>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: News Feed -->
            <div class="lg:col-span-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-heading text-2xl font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-8 rounded-full bg-cool_sky-500"></span>
                        Academic Feed
                    </h2>
                     <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-widest">
                        Auto-Updating
                     </span>
                </div>

                <div id="news-feed-container" class="space-y-6">
                    <?php if(!empty($personalizedFeed)): ?>
                        <?php foreach($personalizedFeed as $news): ?>
                        <div class="bg-white rounded-[1.5rem] p-6 shadow-soft border border-slate-100 flex flex-col md:flex-row gap-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <!-- Date Stamp -->
                            <div class="hidden md:flex flex-col items-center justify-center bg-slate-50 w-20 h-20 rounded-2xl border border-slate-100 flex-shrink-0 group-hover:bg-cool_sky-50 transition-colors">
                                <span class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider group-hover:text-cool_sky-400"><?php echo date('M', strtotime($news['created_at'])); ?></span>
                                <span class="text-2xl font-heading font-black text-slate-800 group-hover:text-cool_sky-600"><?php echo date('d', strtotime($news['created_at'])); ?></span>
                            </div>

                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                     <span class="<?php echo getCategoryColor($news['category_slug']); ?> text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">
                                        <?php echo htmlspecialchars($news['category_name']); ?>
                                    </span>
                                    <span class="text-xs text-slate-400 font-medium md:hidden"><?php echo date('M d', strtotime($news['created_at'])); ?></span>
                                </div>
                                <h3 class="font-heading text-xl font-bold text-slate-900 mb-2 leading-snug group-hover:text-cool_sky-600 transition-colors">
                                    <a href="news_details.php?id=<?php echo $news['news_id']; ?>">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </a>
                                </h3>
                                <p class="text-slate-500 text-sm line-clamp-2 leading-relaxed mb-4">
                                    <?php echo htmlspecialchars(substr(strip_tags($news['content']), 0, 160)) . '...'; ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12 bg-white rounded-3xl border border-dashed border-slate-200">
                            <p class="text-slate-500 font-medium">✨ All caught up! No new notices.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Upcoming & Trending -->
            <aside class="lg:col-span-4 space-y-8">
                
                <!-- Upcoming Widget -->
                <div class="bg-white rounded-[2rem] p-6 shadow-soft border border-slate-100">
                     <h3 class="font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        Upcoming
                    </h3>
                    <div class="space-y-2">
                        <div class="flex gap-4 items-center p-3 hover:bg-slate-50 rounded-xl transition-colors cursor-pointer group">
                            <div class="flex-shrink-0 w-12 text-center bg-slate-100 rounded-lg py-1.5 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                                <span class="block text-[0.6rem] font-bold text-slate-500 uppercase">JAN</span>
                                <span class="block text-lg font-bold text-slate-900">15</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Course Advising</h4>
                                <span class="text-xs text-slate-400">Spring 2026</span>
                            </div>
                        </div>
                         <div class="flex gap-4 items-center p-3 hover:bg-slate-50 rounded-xl transition-colors cursor-pointer group">
                            <div class="flex-shrink-0 w-12 text-center bg-slate-100 rounded-lg py-1.5 group-hover:bg-strawberry_red-100 group-hover:text-strawberry_red-600 transition-colors">
                                <span class="block text-[0.6rem] font-bold text-slate-500 uppercase">JAN</span>
                                <span class="block text-lg font-bold text-slate-900">20</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Project Deadline</h4>
                                <span class="text-xs text-slate-400">CSE 4100</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trending Widget -->
                <div class="bg-white rounded-[2rem] p-6 shadow-soft border border-slate-100">
                    <h3 class="font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-strawberry_red-50 text-strawberry_red-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </div>
                        Trending
                    </h3>
                    <div class="space-y-4">
                        <?php foreach($trendingNews as $index => $trend): ?>
                        <a href="news_details.php?id=<?php echo $trend['news_id']; ?>" class="flex items-start gap-4 group">
                            <span class="text-xl font-black text-slate-200 group-hover:text-strawberry_red-500 transition-colors mt-1">0<?php echo $index + 1; ?></span>
                            <div>
                                <h4 class="font-bold text-slate-700 text-sm group-hover:text-strawberry_red-600 transition-colors line-clamp-2 leading-snug"><?php echo htmlspecialchars($trend['title']); ?></h4>
                                <span class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-wider mt-1 block"><?php echo number_format($trend['views']); ?> reads</span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </aside>
        </div>
    </main>

    <!-- Emergency Modal -->
    <div id="emergency-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full overflow-hidden relative transform transition-all scale-100 animate-bounce-in">
            <div class="bg-strawberry_red-500 p-8 text-white text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-strawberry_red-600 opacity-50 transform rotate-12 scale-150"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-md shadow-inner animate-pulse">
                        <span class="text-4xl">🚨</span>
                    </div>
                    <h2 class="font-heading text-2xl font-black uppercase tracking-widest">Emergency Alert</h2>
                </div>
            </div>
            <div class="p-8 text-center bg-white relative z-20">
                <h3 id="modal-title" class="font-heading font-bold text-2xl text-slate-900 mb-3"></h3>
                <p id="modal-message" class="text-slate-600 leading-relaxed mb-8 text-lg bg-slate-50 p-4 rounded-xl border border-slate-100"></p>
                <button onclick="closeModal()" class="w-full py-4 bg-slate-900 text-white font-bold rounded-xl hover:bg-strawberry_red-600 transition-colors shadow-lg transform active:scale-95 text-lg">
                    I Understand & Dismiss
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        let seenAlerts = new Set();
        function closeModal() { document.getElementById('emergency-modal').classList.add('hidden'); }

        // Alert Polling
        function fetchAlerts() {
            fetch('api/get_alerts.php').then(r => r.json()).then(data => {
                const con = document.getElementById('alert-ticker');
                const banner = document.getElementById('alert-banner');
                if (data.status === 'success' && data.data.length > 0) {
                    let html = '', hasDanger = false, dangerAlert = null;
                    data.data.forEach(a => {
                        let color = a.severity === 'warning' ? 'text-jasmine-400' : (a.severity === 'danger' ? 'text-strawberry_red-300' : 'text-aquamarine-400');
                        let icon = a.severity === 'warning' ? '⚠️' : (a.severity === 'danger' ? '🚨' : 'ℹ️');
                        if(a.severity === 'danger') { hasDanger = true; dangerAlert = a; }
                        html += `<span class="inline-flex items-center gap-2 mr-12"><span class="${color} text-lg">${icon}</span><span class="font-bold text-white">${a.title}:</span><span class="text-slate-300">${a.message}</span></span>`;
                    });
                    con.innerHTML = html;
                    banner.classList.remove('hidden');
                    if (hasDanger && dangerAlert && !seenAlerts.has(dangerAlert.alert_id)) {
                        document.getElementById('modal-title').textContent = dangerAlert.title;
                        document.getElementById('modal-message').textContent = dangerAlert.message;
                        document.getElementById('emergency-modal').classList.remove('hidden');
                        seenAlerts.add(dangerAlert.alert_id);
                    }
                } else { banner.classList.add('hidden'); }
            });
        }

        // Feed Polling
        function fetchFeed() {
             fetch('api/get_feed.php?category=feed').then(r => r.json()).then(data => {
                if (data.status === 'success' && data.data.length > 0) {
                    const con = document.getElementById('news-feed-container');
                    let html = '';
                    data.data.forEach(n => {
                        const d = new Date(n.created_at);
                        const month = d.toLocaleString('default', { month: 'short' }).toUpperCase();
                        const day = String(d.getDate()).padStart(2, '0');
                        const color = (n.category_slug === 'academic' ? 'bg-cool_sky-500 text-white' : (n.category_slug === 'notice' ? 'bg-jasmine-400 text-slate-900' : 'bg-slate-500 text-white'));
                        
                        // Parse Content
                        const temp = document.createElement("div"); temp.innerHTML = n.content;
                        const preview = (temp.textContent || temp.innerText || "").substring(0, 160) + '...';

                        html += `
                        <div class="bg-white rounded-[1.5rem] p-6 shadow-soft border border-slate-100 flex flex-col md:flex-row gap-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div class="hidden md:flex flex-col items-center justify-center bg-slate-50 w-20 h-20 rounded-2xl border border-slate-100 flex-shrink-0 group-hover:bg-cool_sky-50 transition-colors">
                                <span class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider group-hover:text-cool_sky-400">${month}</span>
                                <span class="text-2xl font-heading font-black text-slate-800 group-hover:text-cool_sky-600">${day}</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                     <span class="${color} text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">${n.category_name}</span>
                                     <span class="text-xs text-slate-400 font-medium md:hidden">${d.toLocaleDateString()}</span>
                                </div>
                                <h3 class="font-heading text-xl font-bold text-slate-900 mb-2 leading-snug group-hover:text-cool_sky-600 transition-colors">
                                    <a href="news_details.php?id=${n.news_id}">${n.title}</a>
                                </h3>
                                <p class="text-slate-500 text-sm line-clamp-2 leading-relaxed mb-4">${preview}</p>
                            </div>
                        </div>`;
                    });
                    con.innerHTML = html;
                }
             });
        }

        setInterval(fetchAlerts, 10000);
        setInterval(fetchFeed, 60000);
        fetchAlerts();
    </script>
</body>
</html>
