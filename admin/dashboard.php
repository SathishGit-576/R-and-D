<?php
// admin/dashboard.php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
checkLogin();
checkAdmin();

$page_title = "Admin Dashboard";
$active_page = "dashboard";

// Filters
$selected_date = $_GET['date'] ?? date('Y-m-d');

// 1. Stats Calculation
// Total Towers
$stmt = $pdo->query("SELECT COUNT(*) FROM towers");
$total_towers = $stmt->fetchColumn();

// Status Counts for selected date
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM daily_updates WHERE update_date = ? GROUP BY status");
$stmt->execute([$selected_date]);
$counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$red_count = $counts['Red'] ?? 0;
$orange_count = $counts['Orange'] ?? 0;
$green_count = $counts['Green'] ?? 0;
$pending_count = $total_towers - ($red_count + $orange_count + $green_count);

// 2. Main Table Data
$stmt = $pdo->prepare("
    SELECT t.tower_name, u.username, du.score, du.status, du.remarks, du.update_date
    FROM towers t
    LEFT JOIN daily_updates du ON t.id = du.tower_id AND du.update_date = ?
    LEFT JOIN users u ON du.user_id = u.id
    ORDER BY t.tower_name ASC
");
$stmt->execute([$selected_date]);
$reports = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Admin Insights</h1>
            <p class="text-gray-500 dark:text-gray-400">Real-time infrastructure health and signal monitoring.</p>
        </div>
        
        <form method="GET" class="flex items-center bg-white dark:bg-gray-800 p-1.5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
            <input type="date" name="date" class="bg-transparent border-none text-sm dark:text-white focus:ring-0 px-4 py-2" value="<?php echo $selected_date; ?>">
            <button type="submit" class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-all">
                Update View
            </button>
            <a href="export_csv.php?date=<?php echo urlencode($selected_date); ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-all ml-2 flex items-center">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
            </a>
            <a href="export_pdf.php?date=<?php echo urlencode($selected_date); ?>" class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-all ml-2 flex items-center">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </a>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between transition-all hover:shadow-md">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Assets</p>
                <h3 class="text-3xl font-extrabold text-gray-800 dark:text-white"><?php echo $total_towers; ?></h3>
            </div>
            <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center text-primary">
                <i class="fas fa-broadcast-tower text-2xl"></i>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between transition-all hover:shadow-md">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Operational</p>
                <h3 class="text-3xl font-extrabold text-emerald-500"><?php echo $green_count; ?></h3>
            </div>
            <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl flex items-center justify-center text-emerald-500">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between transition-all hover:shadow-md">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Minor Issues</p>
                <h3 class="text-3xl font-extrabold text-amber-500"><?php echo $orange_count; ?></h3>
            </div>
            <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/20 rounded-2xl flex items-center justify-center text-amber-500">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between transition-all hover:shadow-md">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Critical Alerts</p>
                <h3 class="text-3xl font-extrabold text-rose-500"><?php echo $red_count; ?></h3>
            </div>
            <div class="w-14 h-14 bg-rose-50 dark:bg-rose-900/20 rounded-2xl flex items-center justify-center text-rose-500">
                <i class="fas fa-radiation text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Charts and Table Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Chart Column -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                <h4 class="font-bold text-gray-700 dark:text-gray-300 mb-6 flex items-center">
                    <i class="fas fa-chart-pie mr-2 text-primary"></i>
                    Status Health
                </h4>
                <div class="relative h-64">
                    <canvas id="statusPieChart"></canvas>
                </div>
                <div class="mt-6 space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Operational</span>
                        <span class="font-bold text-emerald-500"><?php echo $total_towers > 0 ? round(($green_count/$total_towers)*100, 1) : 0; ?>%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5" style="width: <?php echo $total_towers > 0 ? ($green_count/$total_towers)*100 : 0; ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-primary to-blue-700 p-8 rounded-3xl shadow-lg text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h4 class="text-xl font-bold mb-2">Need a Report?</h4>
                    <p class="text-blue-100 text-sm mb-6">Access the full history of signals for deep analysis.</p>
                    <a href="history.php" class="bg-white text-primary px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:scale-105 transition-transform inline-block">
                        View Full History
                    </a>
                </div>
                <i class="fas fa-file-invoice text-9xl absolute -right-4 -bottom-4 text-white/10 rotate-12"></i>
            </div>
        </div>

        <!-- Table Column -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors h-full">
                <div class="p-6 border-b border-gray-50 dark:border-gray-700 flex justify-between items-center">
                    <h4 class="font-bold text-gray-700 dark:text-gray-300">Live Status Feed</h4>
                    <span class="text-xs bg-gray-100 dark:bg-gray-900 px-3 py-1 rounded-full text-gray-500 font-medium">
                        <?php echo date('D, d M Y', strtotime($selected_date)); ?>
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-700/30 text-xs font-bold text-gray-500 uppercase tracking-widest">
                                <th class="px-6 py-4">Tower Asset</th>
                                <th class="px-6 py-4 text-center">Signal</th>
                                <th class="px-6 py-4">Remarks</th>
                                <th class="px-6 py-4 text-right">Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            <?php foreach ($reports as $row): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($row['tower_name']); ?></span>
                                        <span class="text-[10px] text-gray-400 flex items-center">
                                            <i class="fas fa-user-edit mr-1"></i>
                                            <?php echo htmlspecialchars($row['username'] ?? 'No Report'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($row['status']): ?>
                                        <?php 
                                            $color = '';
                                            switch($row['status']) {
                                                case 'Green': $color = 'emerald'; break;
                                                case 'Orange': $color = 'amber'; break;
                                                case 'Red': $color = 'rose'; break;
                                            }
                                        ?>
                                        <span class="inline-flex px-3 py-1 bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-700 dark:bg-<?php echo $color; ?>-900/30 dark:text-<?php echo $color; ?>-300 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest italic">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 w-32 truncate" title="<?php echo htmlspecialchars($row['remarks']); ?>">
                                        <?php echo htmlspecialchars($row['remarks'] ?: '-'); ?>
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-black dark:text-white <?php echo $row['score'] === null ? 'text-gray-200' : 'text-gray-700'; ?>">
                                        <?php echo $row['score'] !== null ? $row['score'] : '--'; ?><span class="text-[8px] opacity-30 ml-0.5">/5</span>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const pieCtx = document.getElementById('statusPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Red', 'Orange', 'Green', 'Pending'],
            datasets: [{
                data: [<?php echo "$red_count, $orange_count, $green_count, $pending_count"; ?>],
                backgroundColor: ['#f43f5e', '#f59e0b', '#10b981', '#94a3b8'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Bar chart removed in favor of more focused design, stats cards handle counts better
</script>

<?php include '../includes/footer.php'; ?>
