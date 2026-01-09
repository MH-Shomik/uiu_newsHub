<aside class="w-80 bg-slate-900 text-white hidden md:flex flex-col border-r border-slate-800 shadow-2xl z-50 h-screen sticky top-0 font-sans">
    <!-- Brand Logo -->
    <div class="h-24 flex items-center px-8 border-b border-white/5 bg-slate-900/50 backdrop-blur-xl">
        <a href="dashboard.php" class="flex items-center gap-3 group">
            <div class="relative w-10 h-10">
                <img src="image.png" alt="Logo" class="w-full h-full object-contain rounded-full drop-shadow-lg group-hover:scale-110 transition-transform duration-300">
            </div>
            <div class="flex flex-col">
                <span class="font-heading font-bold text-xl tracking-tight text-white leading-none">NewsHub<span class="text-cool_sky-400">.</span></span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1 group-hover:text-slate-400 transition-colors">Admin Console</span>
            </div>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1 custom-scrollbar">
        
        <!-- Section: Overview -->
        <div class="mb-6">
            <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 font-heading">Overview</p>
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all duration-200 group <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-cool_sky-500/10 text-cool_sky-400 font-bold border border-cool_sky-500/20 shadow-glow-sm' : 'font-medium'; ?>">
                <svg class="w-5 h-5 flex-shrink-0 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-cool_sky-400' : 'text-slate-500 group-hover:text-white'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Dashboard</span>
                <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard.php'): ?>
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-cool_sky-400 shadow-[0_0_8px_rgba(96,181,255,0.8)]"></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Section: Content -->
        <div class="mb-6">
            <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 font-heading">Manage Content</p>
            
            <a href="news_manage.php" class="flex items-center gap-3 px-4 py-3.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all duration-200 group <?php echo (basename($_SERVER['PHP_SELF']) == 'news_manage.php' || basename($_SERVER['PHP_SELF']) == 'news_create.php' || basename($_SERVER['PHP_SELF']) == 'news_edit.php') ? 'bg-tangerine_dream-500/10 text-tangerine_dream-400 font-bold border border-tangerine_dream-500/20' : 'font-medium'; ?>">
                <svg class="w-5 h-5 flex-shrink-0 transition-colors <?php echo (strpos($_SERVER['PHP_SELF'], 'news') !== false) ? 'text-tangerine_dream-400' : 'text-slate-500 group-hover:text-white'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <span>News Articles</span>
                <?php if(basename($_SERVER['PHP_SELF']) == 'news_manage.php'): ?>
                    <span class="ml-auto text-[10px] font-bold bg-tangerine_dream-500/20 text-tangerine_dream-400 px-2 py-0.5 rounded-md border border-tangerine_dream-500/20">Active</span>
                <?php endif; ?>
            </a>

            <a href="alerts_manage.php" class="flex items-center gap-3 px-4 py-3.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all duration-200 group <?php echo (basename($_SERVER['PHP_SELF']) == 'alerts_manage.php' || basename($_SERVER['PHP_SELF']) == 'alert_create.php') ? 'bg-strawberry_red-500/10 text-strawberry_red-400 font-bold border border-strawberry_red-500/20' : 'font-medium'; ?>">
                <svg class="w-5 h-5 flex-shrink-0 transition-colors <?php echo (strpos($_SERVER['PHP_SELF'], 'alert') !== false) ? 'text-strawberry_red-400' : 'text-slate-500 group-hover:text-white'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>System Alerts</span>
            </a>

            <a href="categories_manage.php" class="flex items-center gap-3 px-4 py-3.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all duration-200 group <?php echo basename($_SERVER['PHP_SELF']) == 'categories_manage.php' ? 'bg-aquamarine-500/10 text-aquamarine-400 font-bold border border-aquamarine-500/20' : 'font-medium'; ?>">
                <svg class="w-5 h-5 flex-shrink-0 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'categories_manage.php' ? 'text-aquamarine-400' : 'text-slate-500 group-hover:text-white'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>Categories</span>
            </a>
        </div>

        <!-- Section: System -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="mb-6">
            <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 font-heading">System Admin</p>
            
            <a href="users_manage.php" class="flex items-center gap-3 px-4 py-3.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all duration-200 group <?php echo (basename($_SERVER['PHP_SELF']) == 'users_manage.php' || basename($_SERVER['PHP_SELF']) == 'user_edit.php') ? 'bg-indigo-500/10 text-indigo-400 font-bold border border-indigo-500/20' : 'font-medium'; ?>">
                <svg class="w-5 h-5 flex-shrink-0 transition-colors <?php echo (strpos($_SERVER['PHP_SELF'], 'user') !== false) ? 'text-indigo-400' : 'text-slate-500 group-hover:text-white'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>User Roles</span>
            </a>

            <a href="activity_logs.php" class="flex items-center gap-3 px-4 py-3.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all duration-200 group <?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'bg-cool_sky-500/10 text-cool_sky-400 font-bold border border-cool_sky-500/20' : 'font-medium'; ?>">
                <svg class="w-5 h-5 flex-shrink-0 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'text-cool_sky-400' : 'text-slate-500 group-hover:text-white'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Activity Logs</span>
            </a>
        </div>
        <?php endif; ?>

    </nav>

    <!-- User Profile & Logout -->
    <div class="p-6 border-t border-white/5 bg-slate-900/50 backdrop-blur-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-cool_sky-500 to-indigo-500 p-[2px] shadow-lg">
                 <div class="w-full h-full rounded-full bg-slate-800 flex items-center justify-center text-white font-bold text-sm">
                    <?php echo substr($_SESSION['full_name'] ?? 'Admin', 0, 1); ?>
                 </div>
            </div>
            <div class="overflow-hidden">
                <p class="font-bold text-white text-sm truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></p>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Admin'); ?></p>
            </div>
        </div>
        <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-white/5 hover:bg-strawberry_red-500 hover:text-white text-slate-400 text-xs font-bold uppercase tracking-widest transition-all duration-300 border border-white/5 group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Sign Out
        </a>
    </div>
</aside>
