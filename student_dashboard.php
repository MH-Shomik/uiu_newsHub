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

// 1. Fetch User Info
// (Simulating some academic info that might exist in a real system)
$userInfo = [
    'name' => $user_name,
    'id' => $_SESSION['user_id'], // In a real app, this would be the actual Student ID
    'department' => 'CSE', // Dummy data
    'credits_completed' => 85,
    'cgpa' => 3.76
];

// 2. Fetch "For You" / Bookmarked / Relevant News 
// (For now, just fetching news from Academic and Notice categories as "relevant")
try {
    $feedStmt = $pdo->query("
        SELECT n.*, c.name as category_name, c.slug as category_slug
        FROM news n 
        JOIN categories c ON n.category_id = c.category_id 
        WHERE c.slug IN ('academic', 'notice') AND n.status = 'published'
        ORDER BY n.created_at DESC LIMIT 5
    ");
    $personalizedFeed = $feedStmt->fetchAll();
} catch (PDOException $e) { $personalizedFeed = []; }

// 3. Fetch Trending News
try {
    $trendingStmt = $pdo->query("SELECT news_id, title, views FROM news WHERE status='published' ORDER BY views DESC LIMIT 3");
    $trendingNews = $trendingStmt->fetchAll();
} catch (PDOException $e) { $trendingNews = []; }

// Helper for colors
function getCategoryColor($slug) {
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
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Alerts Banner (Hidden by default, shown via JS if alerts exist) -->
    <div id="alert-banner" class="bg-slate-900 text-white overflow-hidden relative z-50 hidden">
        <div class="max-w-7xl mx-auto flex items-center h-12">
            <div class="bg-strawberry_red-500 text-white font-bold px-4 h-full flex items-center text-sm tracking-wider uppercase flex-shrink-0 z-10 shadow-lg">
                Live Alerts
            </div>
            <div class="flex-1 overflow-hidden relative h-full flex items-center bg-slate-800">
                <div id="alert-ticker" class="animate-marquee whitespace-nowrap flex gap-12 items-center text-sm font-medium pl-4">
                    <!-- Alerts injected via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-xl border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                 <a href="index.php" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cool_sky-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-glow">
                        U
                    </div>
                    <div class="flex flex-col">
                        <span class="font-heading font-bold text-xl text-slate-900 leading-none">NewsHub</span>
                        <span class="text-xs font-bold text-cool_sky-500 uppercase tracking-widest">Student Portal</span>
                    </div>
                </a>
                <div class="flex items-center gap-4">
                    <a href="search.php" class="p-2 text-slate-400 hover:text-cool_sky-600 transition-colors rounded-full hover:bg-slate-100" title="Search News">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </a>
                     <span class="hidden md:block text-sm font-semibold text-slate-600">
                        Hi, <?php echo htmlspecialchars($userInfo['name']); ?>
                     </span>
                     <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white shadow-sm">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userInfo['name']); ?>&background=random" alt="Avatar">
                     </div>
                     <a href="logout.php" class="text-sm font-bold text-slate-400 hover:text-strawberry_red-500 transition-colors ml-2">
                        Logout
                     </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Sidebar: Student Profile Card -->
            <aside class="lg:col-span-4">
                <div class="bg-white rounded-[2rem] p-8 shadow-soft border border-slate-100 sticky top-28">
                    <div class="text-center mb-8 relative">
                        <div class="w-24 h-24 mx-auto rounded-full p-1 bg-gradient-to-br from-cool_sky-500 to-aquamarine-500 mb-4">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userInfo['name']); ?>&background=ffffff" class="w-full h-full rounded-full border-4 border-white">
                        </div>
                        <h2 class="font-heading text-2xl font-bold text-slate-900"><?php echo htmlspecialchars($userInfo['name']); ?></h2>
                        <span class="inline-block mt-2 px-3 py-1 rounded-full bg-cool_sky-50 text-cool_sky-600 text-sm font-bold border border-cool_sky-100">
                            <?php echo htmlspecialchars($userInfo['department']); ?> Student
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-slate-50 p-4 rounded-2xl text-center border border-slate-100">
                            <span class="block text-3xl font-heading font-bold text-slate-900"><?php echo $userInfo['cgpa']; ?></span>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">CGPA</span>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl text-center border border-slate-100">
                            <span class="block text-3xl font-heading font-bold text-slate-900"><?php echo $userInfo['credits_completed']; ?></span>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Credits</span>
                        </div>
                    </div>

                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-tangerine_dream-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Upcoming Academics
                    </h3>
                    <div class="space-y-4">
                        <div class="flex gap-4 items-start p-3 hover:bg-slate-50 rounded-xl transition-colors cursor-pointer">
                            <div class="flex-shrink-0 w-14 text-center bg-slate-100 rounded-lg py-2">
                                <span class="block text-xs font-bold text-slate-500">JAN</span>
                                <span class="block text-xl font-bold text-slate-900">15</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Course Advising</h4>
                                <p class="text-xs text-slate-500 mt-1">Spring 2026 advising starts at 10:00 AM.</p>
                            </div>
                        </div>
                         <div class="flex gap-4 items-start p-3 hover:bg-slate-50 rounded-xl transition-colors cursor-pointer">
                            <div class="flex-shrink-0 w-14 text-center bg-slate-100 rounded-lg py-2">
                                <span class="block text-xs font-bold text-slate-500">JAN</span>
                                <span class="block text-xl font-bold text-slate-900">20</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Project Submission</h4>
                                <p class="text-xs text-slate-500 mt-1">Final year project proposal deadline.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-slate-100">
                        <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                             <svg class="w-5 h-5 text-strawberry_red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                             Trending Now
                        </h3>
                        <div class="space-y-4">
                            <?php foreach($trendingNews as $index => $trend): ?>
                            <a href="news_details.php?id=<?php echo $trend['news_id']; ?>" class="flex items-center gap-4 group">
                                <span class="text-2xl font-black text-slate-200 group-hover:text-cool_sky-500 transition-colors">0<?php echo $index + 1; ?></span>
                                <div>
                                    <h4 class="font-bold text-slate-700 text-sm group-hover:text-cool_sky-600 transition-colors line-clamp-2"><?php echo htmlspecialchars($trend['title']); ?></h4>
                                    <span class="text-xs text-slate-400 font-medium"><?php echo number_format($trend['views']); ?> reads</span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Feed: My Notices -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- Quick Access -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="index.php" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-cool_sky-200 transition-all text-center group">
                        <div class="w-12 h-12 mx-auto bg-cool_sky-50 text-cool_sky-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                        </div>
                        <span class="font-bold text-slate-700 text-sm">News Feed</span>
                    </a>
                    <a href="#" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-aquamarine-200 transition-all text-center group">
                        <div class="w-12 h-12 mx-auto bg-aquamarine-50 text-aquamarine-500 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="font-bold text-slate-700 text-sm">Class Routine</span>
                    </a>
                    <a href="#" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-tangerine_dream-200 transition-all text-center group">
                        <div class="w-12 h-12 mx-auto bg-orange-50 text-tangerine_dream-500 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="font-bold text-slate-700 text-sm">Tuition Fees</span>
                    </a>
                    <a href="#" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-jasmine-300 transition-all text-center group">
                         <div class="w-12 h-12 mx-auto bg-yellow-50 text-jasmine-500 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="font-bold text-slate-700 text-sm">Support</span>
                    </a>
                </div>

                <!-- Personalized Feed -->
                <div>
                     <div class="flex items-center justify-between mb-6">
                        <h2 class="font-heading text-2xl font-bold text-slate-900">Academic Notices For You</h2>
                        <a href="index.php?category=academic" class="text-sm font-bold text-cool_sky-600 hover:underline">View All</a>
                     </div>

                     <?php if(!empty($personalizedFeed)): ?>
                        <div class="space-y-4">
                            <?php foreach($personalizedFeed as $news): ?>
                            <div class="bg-white rounded-2xl p-6 shadow-soft border border-slate-100 flex flex-col md:flex-row gap-6 hover:shadow-md transition-shadow">
                                <!-- Date Box -->
                                <div class="hidden md:flex flex-col items-center justify-center bg-slate-50 w-24 h-24 rounded-2xl border border-slate-100 flex-shrink-0">
                                    <span class="text-xs font-bold text-slate-400 uppercase"><?php echo date('M', strtotime($news['created_at'])); ?></span>
                                    <span class="text-3xl font-heading font-black text-slate-800"><?php echo date('d', strtotime($news['created_at'])); ?></span>
                                </div>

                                <div class="flex-1">
                                    <span class="<?php echo getCategoryColor($news['category_slug']); ?> text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider mb-2 inline-block">
                                        <?php echo htmlspecialchars($news['category_name']); ?>
                                    </span>
                                    <h3 class="font-heading text-xl font-bold text-slate-900 mb-2">
                                        <a href="news_details.php?id=<?php echo $news['news_id']; ?>" class="hover:text-cool_sky-500 transition-colors">
                                            <?php echo htmlspecialchars($news['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-slate-500 text-sm line-clamp-2 leading-relaxed mb-3">
                                        <?php echo htmlspecialchars(substr(strip_tags($news['content']), 0, 150)) . '...'; ?>
                                    </p>
                                    <div class="flex items-center gap-4 text-xs font-medium text-slate-400 md:hidden">
                                        <span><?php echo date('M d, Y', strtotime($news['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                     <?php else: ?>
                        <div class="text-center py-10 bg-white rounded-2xl border border-dashed border-slate-200">
                            <p class="text-slate-500 font-medium">No new academic notices at the moment.</p>
                        </div>
                     <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <!-- Real-time Alerts Script -->
    <script>
        function fetchAlerts() {
            fetch('api/get_alerts.php')
                .then(response => response.json())
                .then(data => {
                    const alertContainer = document.getElementById('alert-ticker');
                    if (data.status === 'success' && data.data.length > 0) {
                        // Build HTML
                        let html = '';
                        data.data.forEach(alert => {
                            let icon = 'INFO';
                            let color = 'text-aquamarine-400';
                            if (alert.severity === 'warning') { color = 'text-jasmine-400'; icon = '⚠️'; }
                            if (alert.severity === 'danger')  { color = 'text-strawberry_red-300'; icon = '🚨'; }
                            
                            html += `
                                <span class="inline-flex items-center gap-2 mr-12">
                                    <span class="${color} text-lg">${icon}</span>
                                    <span class="font-bold text-white">${alert.title}:</span>
                                    <span class="text-slate-300">${alert.message}</span>
                                </span>
                            `;
                        });
                        
                        alertContainer.innerHTML = html;
                        document.getElementById('alert-banner').classList.remove('hidden');
                    } else {
                        document.getElementById('alert-banner').classList.add('hidden');
                    }
                })
                .catch(err => console.error('Error fetching alerts:', err));
        }

        // Poll every 30 seconds
        setInterval(fetchAlerts, 30000);
        // Initial fetch
        fetchAlerts();
    </script>
</body>
</html>
