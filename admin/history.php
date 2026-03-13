<?php
// admin/history.php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
checkLogin();
checkAdmin();

$page_title = "Signal History";
$active_page = "history";

// Filters
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_month = isset($_GET['month']) ? $_GET['month'] : '';
$filter_tower = isset($_GET['tower_id']) ? $_GET['tower_id'] : '';

$query = "
    SELECT du.*, t.tower_name, t.category, u.username 
    FROM daily_updates du
    JOIN towers t ON du.tower_id = t.id
    JOIN users u ON du.user_id = u.id
    WHERE 1=1
";

$params = [];

if ($filter_date) {
    $query .= " AND du.update_date = ?";
    $params[] = $filter_date;
}

if ($filter_month) {
    $query .= " AND DATE_FORMAT(du.update_date, '%Y-%m') = ?";
    $params[] = $filter_month;
}

if ($filter_tower) {
    $query .= " AND du.tower_id = ?";
    $params[] = $filter_tower;
}

$query .= " ORDER BY du.update_date DESC, du.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$history = $stmt->fetchAll();

// Fetch towers for dropdown
$towers = $pdo->query("SELECT id, tower_name FROM towers ORDER BY tower_name ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="px-4 py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white"><?php echo $page_title; ?></h1>
        <p class="text-gray-500 dark:text-gray-400">Review historical daily traffic signals and performance reports.</p>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-8 transition-colors">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Specific Date</label>
                <input type="date" name="date" value="<?php echo $filter_date; ?>" class="w-full px-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary transition-all">
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Month / Year</label>
                <input type="month" name="month" value="<?php echo $filter_month; ?>" class="w-full px-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary transition-all">
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Tower Asset</label>
                <select name="tower_id" class="w-full px-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary transition-all">
                    <option value="">All Towers</option>
                    <?php foreach ($towers as $tower): ?>
                        <option value="<?php echo $tower['id']; ?>" <?php echo $filter_tower == $tower['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tower['tower_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-primary hover:bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all flex items-center justify-center space-x-2">
                    <i class="fas fa-filter"></i>
                    <span>Apply Filters</span>
                </button>
                <a href="history.php" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 px-4 py-2.5 rounded-xl transition-all flex items-center justify-center">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- History Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tower Details</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reporter</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Signal Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No historical records found for the selected criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $row): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-700 dark:text-gray-200"><?php echo date('M d, Y', strtotime($row['update_date'])); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="block font-bold text-primary"><?php echo htmlspecialchars($row['tower_name']); ?></span>
                                <span class="text-xs text-gray-500 px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded"><?php echo htmlspecialchars($row['category']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-user-circle text-gray-400 text-sm"></i>
                                    <span class="text-sm dark:text-gray-300"><?php echo htmlspecialchars($row['username']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                    $status_class = '';
                                    switch($row['status']) {
                                        case 'Green': $status_class = 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'; break;
                                        case 'Orange': $status_class = 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300'; break;
                                        case 'Red': $status_class = 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'; break;
                                    }
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $status_class; ?>">
                                    <i class="fas fa-circle mr-1 text-[8px]"></i>
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold dark:text-white"><?php echo $row['score']; ?>/5</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="<?php echo htmlspecialchars($row['remarks']); ?>">
                                    <?php echo htmlspecialchars($row['remarks'] ?: 'No remarks'); ?>
                                </p>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
