<div class="sidebar w-64 min-h-screen bg-gray-900 dark:bg-black text-white shadow-2xl transition-all duration-300 fixed left-0 top-0 z-50 flex flex-col">
    <div class="p-6 mb-4">
        <div class="flex items-center space-x-3">
            <div class="bg-primary p-2 rounded-lg">
                <i class="fas fa-broadcast-tower text-xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-lg leading-tight">Tower System</h4>
                <p class="text-gray-500 text-xs uppercase tracking-widest">Signal Manager</p>
            </div>
        </div>
    </div>

    <nav class="px-4 space-y-1 flex-1 overflow-y-auto">
        <?php if ($_SESSION['role'] === 'admin'): ?>

            <!-- Dashboard -->
            <a href="../admin/dashboard.php"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all <?php echo $active_page === 'dashboard' ? 'bg-primary text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400 hover:text-white'; ?>">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- User Accounts -->
            <a href="../admin/users.php"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all <?php echo $active_page === 'users' ? 'bg-primary text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400 hover:text-white'; ?>">
                <i class="fas fa-users-cog w-5 text-center"></i>
                <span class="font-medium">User Accounts</span>
            </a>

            <!-- Manage Towers (Collapsible Group) -->
            <?php
                $towers_submenu_open = in_array($active_page, ['towers', 'history']);
            ?>
            <div class="sidebar-group">
                <!-- Group Toggle Button -->
                <button type="button"
                        onclick="toggleSidebarGroup('towersGroup')"
                        id="towersGroupBtn"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all <?php echo $towers_submenu_open ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 text-gray-400 hover:text-white'; ?>">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-satellite w-5 text-center"></i>
                        <span class="font-medium">Manage Towers</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 <?php echo $towers_submenu_open ? 'rotate-180' : ''; ?>" id="towersGroupChevron"></i>
                </button>

                <!-- Submenu -->
                <div id="towersGroup"
                     class="overflow-hidden transition-all duration-300 <?php echo $towers_submenu_open ? '' : 'hidden'; ?>">
                    <div class="ml-4 mt-1 space-y-1 border-l-2 border-gray-700 pl-3">

                        <!-- Tower Assets -->
                        <a href="../admin/towers.php"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all <?php echo $active_page === 'towers' ? 'bg-primary text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400 hover:text-white'; ?>">
                            <i class="fas fa-broadcast-tower w-4 text-center text-sm"></i>
                            <span class="font-medium text-sm">Tower Assets</span>
                        </a>

                        <!-- Signal History -->
                        <a href="../admin/history.php"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all <?php echo $active_page === 'history' ? 'bg-primary text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400 hover:text-white'; ?>">
                            <i class="fas fa-history w-4 text-center text-sm"></i>
                            <span class="font-medium text-sm">Signal History</span>
                        </a>

                    </div>
                </div>
            </div>

        <?php else: ?>

            <!-- Member: Assigned Tasks -->
            <a href="../member/dashboard.php"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all <?php echo $active_page === 'dashboard' ? 'bg-primary text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400 hover:text-white'; ?>">
                <i class="fas fa-tasks w-5 text-center"></i>
                <span class="font-medium">Assigned Tasks</span>
            </a>

        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-gray-800 mt-auto">
        <p class="text-xs text-gray-600 text-center uppercase tracking-widest">Tower Ops Core &copy; <?php echo date('Y'); ?></p>
    </div>
</div>

<script>
    function toggleSidebarGroup(groupId) {
        const group = document.getElementById(groupId);
        const chevron = document.getElementById(groupId + 'Chevron');
        const btn = document.getElementById(groupId + 'Btn');

        if (group.classList.contains('hidden')) {
            group.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
            if (btn) btn.classList.add('bg-gray-800', 'text-white');
            if (btn) btn.classList.remove('text-gray-400');
            // Save state
            localStorage.setItem('sidebar_' + groupId, 'open');
        } else {
            group.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
            if (btn) btn.classList.remove('bg-gray-800', 'text-white');
            if (btn) btn.classList.add('text-gray-400');
            localStorage.setItem('sidebar_' + groupId, 'closed');
        }
    }

    // On page load: restore state from localStorage only if PHP didn't already open it
    document.addEventListener('DOMContentLoaded', function () {
        const towersGroup = document.getElementById('towersGroup');
        // Only apply localStorage state if PHP hasn't forced it open (i.e., it's currently hidden)
        if (towersGroup && towersGroup.classList.contains('hidden')) {
            const savedState = localStorage.getItem('sidebar_towersGroup');
            if (savedState === 'open') {
                toggleSidebarGroup('towersGroup');
            }
        }
    });
</script>
