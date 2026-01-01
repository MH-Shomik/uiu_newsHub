<?php
session_start();
require_once 'includes/db_connect.php';

// Helper to map DB categories to new Palette Colors
function getCategoryColor($slug) {
    // Returns Tailwind classes based on category slug
    $map = [
        'academic' => 'bg-cool_sky-500 text-white',
        'research' => 'bg-aquamarine-500 text-slate-900',
        'events'   => 'bg-tangerine_dream-500 text-white',
        'sports'   => 'bg-strawberry_red-500 text-white',
        'notice'   => 'bg-jasmine-400 text-slate-900',
    ];
    return $map[$slug] ?? 'bg-slate-700 text-white';
}

function getPlaceholderImage($category, $seed) {
    // Generates a relevant distinct placeholder color based on category
    // Using placehold.co with custom colors from our palette
    $colors = [
        'academic' => '60b5ff', // cool_sky
        'research' => '5ef2d5', // aquamarine
        'events' => 'f79d65',   // tangerine
        'sports' => 'f35252',   // strawberry
        'notice' => 'ffe588',   // jasmine
    ];
    $hex = $colors[$category] ?? 'cbd5e1';
    $textHex = ($category == 'notice' || $category == 'research') ? '0f172a' : 'ffffff';
    // Use placehold.co
    return "https://placehold.co/800x600/$hex/$textHex?text=" . urlencode(ucfirst($category) . "+Update");
}


// 1. Fetch Active Alerts
try {
    $alertStmt = $pdo->query("SELECT * FROM alerts WHERE is_active = 1 AND (expires_at > NOW() OR expires_at IS NULL) ORDER BY created_at DESC");
    $alerts = $alertStmt->fetchAll();
} catch (PDOException $e) { $alerts = []; }

// 2. Fetch Featured/Urgent News (Top 1)
try {
    $heroStmt = $pdo->query("
        SELECT n.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name 
        FROM news n 
        JOIN categories c ON n.category_id = c.category_id 
        JOIN users u ON n.author_id = u.user_id 
        WHERE n.status = 'published' AND n.is_urgent = 1 
        ORDER BY n.created_at DESC LIMIT 1
    ");
    $heroNews = $heroStmt->fetch();
} catch (PDOException $e) { $heroNews = null; }

// 3. Fetch Latest News
try {
    $sql = "
        SELECT n.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name 
        FROM news n 
        JOIN categories c ON n.category_id = c.category_id 
        JOIN users u ON n.author_id = u.user_id 
        WHERE n.status = 'published' 
    ";
    if ($heroNews) {
        $sql .= " AND n.news_id != " . $heroNews['news_id'];
    }
    $sql .= " ORDER BY n.created_at DESC LIMIT 9";
    $newsStmt = $pdo->query($sql);
    $latestNews = $newsStmt->fetchAll();
} catch (PDOException $e) { $latestNews = []; }

// 4. Fetch Categories
try {
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll();
} catch (PDOException $e) { $categories = []; }
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU News Hub | Campus Pulse</title>
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
                    },
                    animation: {
                        'marquee': 'marquee 25s linear infinite',
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(100%)' },
                            '100%': { transform: 'translateX(-100%)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .hero-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-cool_sky-500 selection:text-white">

    <!-- Alerts Banner (Ticker Style) -->
    <?php if (!empty($alerts)): ?>
    <div class="bg-slate-900 text-white overflow-hidden relative z-50">
        <div class="max-w-7xl mx-auto flex items-center h-12">
            <div class="bg-strawberry_red-500 text-white font-bold px-4 h-full flex items-center text-sm tracking-wider uppercase flex-shrink-0 z-10 shadow-lg">
                Live Alerts
            </div>
            <div class="flex-1 overflow-hidden relative h-full flex items-center bg-slate-800">
                <div class="animate-marquee whitespace-nowrap flex gap-12 items-center text-sm font-medium pl-4">
                    <?php foreach ($alerts as $alert): 
                        $icon = 'INFO';
                        $color = 'text-aquamarine-400';
                        if ($alert['severity'] == 'warning') { $color = 'text-jasmine-400'; $icon = '⚠️'; }
                        if ($alert['severity'] == 'danger')  { $color = 'text-strawberry_red-300'; $icon = '🚨'; }
                    ?>
                    <span class="inline-flex items-center gap-2">
                        <span class="<?php echo $color; ?> text-lg"><?php echo $icon; ?></span>
                        <span class="font-bold text-white"><?php echo htmlspecialchars($alert['title']); ?>:</span>
                        <span class="text-slate-300"><?php echo htmlspecialchars($alert['message']); ?></span>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
    <!-- Student Logged In Nav -->
    <nav class="sticky top-0 w-full z-40 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="student_dashboard.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cool_sky-500 to-indigo-600 flex items-center justify-center text-white font-heading font-black text-xl shadow-glow">U</div>
                    <span class="font-heading font-bold text-2xl tracking-tight text-slate-900">UIU <span class="text-cool_sky-500">NewsHub</span></span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="student_dashboard.php" class="px-5 py-2 rounded-full bg-slate-100 text-slate-600 font-medium text-sm hover:bg-cool_sky-50 hover:text-cool_sky-600 transition-colors">Dashboard</a>
                    <a href="logout.php" class="px-5 py-2 rounded-full bg-slate-900 text-white font-semibold text-sm hover:bg-strawberry_red-500 transition-colors">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <?php else: ?>
    <!-- Public Nav -->
    <nav class="sticky top-0 w-full z-40 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cool_sky-500 to-indigo-600 flex items-center justify-center text-white font-heading font-black text-xl shadow-glow transition-transform group-hover:rotate-6">
                        U
                    </div>
                    <span class="font-heading font-bold text-2xl tracking-tight text-slate-900">
                        UIU <span class="text-cool_sky-500">NewsHub</span>
                    </span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-1 items-center bg-slate-100/50 p-1.5 rounded-full border border-slate-200/50 backdrop-blur-sm">
                    <a href="index.php" class="px-5 py-2 rounded-full bg-white text-slate-900 font-semibold shadow-sm text-sm transition-all hover:shadow-md">Home</a>
                    <a href="#categories" class="px-5 py-2 rounded-full text-slate-600 hover:text-cool_sky-600 font-medium text-sm transition-colors hover:bg-white/50">Categories</a>
                    <a href="#latest" class="px-5 py-2 rounded-full text-slate-600 hover:text-cool_sky-600 font-medium text-sm transition-colors hover:bg-white/50">Latest News</a>
                </div>

                <div class="hidden md:flex items-center gap-4">
                     <a href="login.php" class="px-6 py-2.5 rounded-full bg-slate-900 text-white font-semibold hover:bg-cool_sky-500 hover:shadow-glow transition-all transform hover:-translate-y-0.5 text-sm duration-300">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="hero-pattern min-h-screen pb-20 pt-8">

        <!-- Hero Section -->
        <?php if ($heroNews): ?>
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
            <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl h-[550px] group transform transition-all hover:shadow-glow">
                <!-- Parallax Background Image -->
                <img src="<?php echo $heroNews['image_url'] ? htmlspecialchars($heroNews['image_url']) : getPlaceholderImage($heroNews['category_slug'], 0); ?>" 
                     alt="Top Story" 
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/95 via-slate-900/50 to-transparent"></div>
                
                <!-- Hero Content -->
                <div class="absolute bottom-0 left-0 p-8 md:p-16 w-full md:w-3/4 lg:w-2/3 flex flex-col items-start z-10">
                    <div class="flex items-center gap-3 mb-6 animate-fade-in-up">
                        <span class="bg-strawberry_red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider shadow-lg shadow-strawberry_red-500/30 ring-2 ring-strawberry_red-400/50">
                            Urgent Update
                        </span>
                        <span class="<?php echo getCategoryColor($heroNews['category_slug']); ?> text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider shadow-lg">
                            <?php echo htmlspecialchars($heroNews['category_name']); ?>
                        </span>
                    </div>

                    <h1 class="font-heading text-4xl md:text-6xl font-bold text-white mb-6 leading-tight drop-shadow-lg">
                        <?php echo htmlspecialchars($heroNews['title']); ?>
                    </h1>
                    
                    <p class="text-slate-200 text-lg md:text-xl mb-8 line-clamp-3 font-light leading-relaxed max-w-2xl">
                        <?php echo htmlspecialchars(substr(strip_tags($heroNews['content']), 0, 200)) . '...'; ?>
                    </p>
                    
                    <a href="news_details.php?id=<?php echo $heroNews['news_id']; ?>" 
                       class="inline-flex items-center gap-2 px-8 py-4 bg-white text-slate-900 rounded-full font-bold hover:bg-cool_sky-500 hover:text-white transition-all shadow-xl hover:shadow-glow transform hover:-translate-y-1">
                        Read Full Story
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                <!-- Sidebar: Categories -->
                <aside class="hidden lg:block lg:col-span-3 space-y-8 sticky top-32 h-fit" id="categories">
                    <div class="bg-white rounded-3xl p-6 shadow-soft border border-slate-100">
                        <h3 class="font-heading font-bold text-slate-900 text-xl mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-tangerine_dream-500 rounded-full"></span>
                            Explore
                        </h3>
                        <div class="space-y-3">
                            <a href="index.php" class="flex items-center justify-between px-4 py-3 rounded-2xl bg-slate-50 text-slate-900 font-semibold border border-slate-200 transition-all hover:border-cool_sky-200 hover:shadow-md">
                                <span>All Stories</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                            <?php foreach($categories as $cat): ?>
                                <a href="?category=<?php echo urlencode($cat['slug']); ?>" class="flex items-center justify-between px-4 py-3 rounded-2xl text-slate-500 hover:bg-slate-50 hover:text-cool_sky-600 font-medium transition-all group">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 text-xs flex items-center justify-center group-hover:bg-cool_sky-100 group-hover:text-cool_sky-600 transition-colors">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Newsletter Card -->
                    <div class="relative overflow-hidden rounded-3xl p-8 bg-gradient-to-br from-cool_sky-600 to-indigo-700 text-white shadow-xl text-center group">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl group-hover:opacity-20 transition-opacity duration-500"></div>
                        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-aquamarine-400 opacity-20 rounded-full blur-2xl group-hover:opacity-30 transition-opacity duration-500"></div>
                        
                        <span class="text-4xl mb-4 block animate-bounce">📬</span>
                        <h3 class="font-heading font-bold text-2xl mb-2">Stay Updated</h3>
                        <p class="text-cool_sky-100 text-sm mb-6 opacity-90 leading-relaxed">Daily curated news from UIU campus directly to your inbox.</p>
                        <button class="w-full py-3 bg-white text-cool_sky-700 font-bold rounded-xl hover:bg-cool_sky-50 shadow-lg transition-colors ring-2 ring-transparent focus:ring-cool_sky-300">
                            Subscribe Now
                        </button>
                    </div>
                </aside>

                <!-- News Grid -->
                <div class="lg:col-span-9" id="latest">
                    <div class="flex items-end justify-between mb-8 border-b border-slate-200 pb-4">
                        <div>
                            <span class="text-tangerine_dream-500 font-bold tracking-wider text-sm uppercase mb-1 block">What's Happening</span>
                            <h2 class="font-heading text-3xl font-bold text-slate-900">Latest Updates</h2>
                        </div>
                        <div class="hidden sm:block">
                            <span class="text-xs text-slate-400 font-medium bg-slate-100 px-3 py-1 rounded-full">Showing <?php echo count($latestNews); ?> articles</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <?php if (count($latestNews) > 0): ?>
                            <?php foreach($latestNews as $index => $news): 
                                $placeholder = getPlaceholderImage($news['category_slug'], $index);
                            ?>
                            <article class="bg-white rounded-[1.5rem] shadow-soft hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-slate-100 overflow-hidden flex flex-col h-full group relative">
                                <!-- Hover Gradient Overlay -->
                                <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-cool_sky-500 to-aquamarine-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 z-20"></div>

                                <!-- Image Wrapper -->
                                <div class="h-56 overflow-hidden relative">
                                    <div class="absolute top-4 left-4 z-10 flex gap-2">
                                        <span class="<?php echo getCategoryColor($news['category_slug']); ?> text-xs font-bold px-3 py-1.5 rounded-lg shadow-md backdrop-blur-md bg-opacity-90">
                                            <?php echo htmlspecialchars($news['category_name']); ?>
                                        </span>
                                    </div>
                                    <img src="<?php echo $news['image_url'] ? htmlspecialchars($news['image_url']) : $placeholder; ?>" 
                                         alt="News Thumbnail" 
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 group-hover:rotate-1">
                                    
                                    <!-- Date Badge -->
                                    <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg text-xs font-bold text-slate-600 shadow-sm border border-white/50">
                                        <?php echo date('M d', strtotime($news['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="p-6 flex-1 flex flex-col relative z-10 bg-white">
                                    <h3 class="font-heading text-xl font-bold text-slate-900 mb-3 leading-tight group-hover:text-cool_sky-600 transition-colors">
                                        <a href="news_details.php?id=<?php echo $news['news_id']; ?>" class="stretched-link">
                                            <?php echo htmlspecialchars($news['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-slate-500 text-sm mb-4 line-clamp-3 leading-relaxed">
                                        <?php echo htmlspecialchars(substr(strip_tags($news['content']), 0, 100)) . '...'; ?>
                                    </p>
                                    
                                    <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold border border-slate-200">
                                                <?php echo substr($news['author_name'], 0, 1); ?>
                                            </div>
                                            <span class="text-xs font-semibold text-slate-500 truncate max-w-[100px]"><?php echo htmlspecialchars($news['author_name']); ?></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium bg-slate-50 px-2 py-1 rounded-md">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <?php echo number_format($news['views']); ?>
                                        </div>
                                    </div>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Empty State -->
                            <div class="col-span-full py-16 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                                <span class="bg-slate-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 text-3xl">📭</span>
                                <h3 class="font-heading text-xl font-bold text-slate-900">No updates yet</h3>
                                <p class="text-slate-500 mt-2">Check back later for fresh news.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-6">
                         <div class="w-10 h-10 rounded-xl bg-cool_sky-500 flex items-center justify-center text-white font-bold text-xl drop-shadow-lg">U</div>
                         <span class="font-heading font-bold text-2xl">UIU <span class="text-cool_sky-400">NewsHub</span></span>
                    </div>
                    <p class="text-slate-400 text-base leading-relaxed max-w-sm">
                        Stay connected with United International University's latest academic updates, research breakthroughs, and vibrant campus life events.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-6 text-lg">Quick Links</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="#" class="hover:text-cool_sky-400 transition-colors">Academic Calendar</a></li>
                        <li><a href="#" class="hover:text-cool_sky-400 transition-colors">Transport Schedule</a></li>
                        <li><a href="#" class="hover:text-cool_sky-400 transition-colors">Library Portal</a></li>
                        <li><a href="#" class="hover:text-cool_sky-400 transition-colors">Student Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-6 text-lg">Connect</h4>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-cool_sky-500 hover:text-white transition-all transform hover:-translate-y-1">
                            <span class="sr-only">Facebook</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-gradient-to-br hover:from-tangerine_dream-500 hover:to-strawberry_red-500 hover:text-white transition-all transform hover:-translate-y-1">
                            <span class="sr-only">Instagram</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.85-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 text-center text-slate-500 text-sm">
                &copy; <?php echo date('Y'); ?> UIU NewsHub. All rights reserved. Designed for Excellence.
            </div>
        </div>
    </footer>
</body>
</html>
