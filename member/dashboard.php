<?php
// member/dashboard.php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
checkLogin();
checkMember();

$page_title = "Member Dashboard";
$active_page = "dashboard";

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$message = '';

// Handle Signal Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_signal'])) {
    $tower_id = $_POST['tower_id'];
    $score = (int)$_POST['score'];
    $remarks = trim($_POST['remarks']);

    // Check if already updated today
    $check = $pdo->prepare("SELECT id FROM daily_updates WHERE tower_id = ? AND update_date = ?");
    $check->execute([$tower_id, $today]);
    
    if ($check->rowCount() > 0) {
        $message = '<div class="alert alert-warning">This tower has already been updated today.</div>';
    } else {
        // Determine Status Color
        if ($score >= 0 && $score <= 2) $status = 'Red';
        elseif ($score >= 3 && $score <= 4) $status = 'Orange';
        else $status = 'Green';

        $stmt = $pdo->prepare("INSERT INTO daily_updates (tower_id, user_id, score, status, remarks, update_date) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$tower_id, $user_id, $score, $status, $remarks, $today])) {
            $message = '<div class="alert alert-success">Tower signal updated successfully!</div>';
        }
    }
}

// Fetch Assigned Towers
$stmt = $pdo->prepare("
    SELECT t.*, du.score as today_score, du.status as today_status 
    FROM towers t 
    LEFT JOIN daily_updates du ON t.id = du.tower_id AND du.update_date = ?
    WHERE t.assigned_user_id = ?
");
$stmt->execute([$today, $user_id]);
$assigned_towers = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="px-6 py-8">
    <?php echo $message; ?>

    <div class="mb-10">
        <h1 class="text-4xl font-black text-gray-800 dark:text-white tracking-tight">Your Control Center</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">Transmit accurate signals for your assigned assets.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        <?php if (empty($assigned_towers)): ?>
            <div class="col-span-full bg-white dark:bg-gray-800 p-12 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-6 text-gray-400">
                    <i class="fas fa-satellite-dish text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold dark:text-white mb-2">No Assignments Detected</h3>
                <p class="text-gray-500 dark:text-gray-400">You currently have no systems to monitor. Check back later.</p>
            </div>
        <?php else: ?>
            <?php foreach ($assigned_towers as $tower): ?>
                <div class="group relative bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-xl shadow-gray-100 dark:shadow-black/20 border border-gray-50 dark:border-gray-700 overflow-hidden transform transition-all hover:-translate-y-2">
                    <!-- Card Top Header -->
                    <div class="p-8 pb-4 flex justify-between items-start">
                        <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                            <i class="fas fa-broadcast-tower text-2xl"></i>
                        </div>
                        <?php if ($tower['today_status']): ?>
                            <?php 
                                $color = '';
                                switch($tower['today_status']) {
                                    case 'Green': $color = 'emerald'; break;
                                    case 'Orange': $color = 'amber'; break;
                                    case 'Red': $color = 'rose'; break;
                                }
                            ?>
                            <div class="flex flex-col items-end">
                                <span class="bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-700 dark:bg-<?php echo $color; ?>-900/30 dark:text-<?php echo $color; ?>-300 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-<?php echo $color; ?>-200 dark:border-<?php echo $color; ?>-700/50">
                                    <?php echo $tower['today_status']; ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="bg-gray-100 dark:bg-gray-900 text-gray-400 px-4 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                Offline
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="px-8 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white truncate"><?php echo htmlspecialchars($tower['tower_name']); ?></h3>
                        <div class="flex items-center text-gray-400 text-sm mt-1">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span class="truncate"><?php echo htmlspecialchars($tower['location'] ?? 'Remote Command'); ?></span>
                        </div>
                    </div>

                    <div class="px-8 pb-8">
                        <?php if ($tower['today_status']): ?>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-6 text-center border border-gray-100 dark:border-gray-800 transition-colors">
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-black mb-1">Health Index</p>
                                <div class="text-4xl font-black text-gray-800 dark:text-white mb-2">
                                    <?php echo $tower['today_score']; ?><span class="text-sm text-gray-400">/5</span>
                                </div>
                                <div class="flex items-center justify-center text-emerald-500 font-bold text-sm">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    Signals Verified
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="" class="space-y-4">
                                <input type="hidden" name="tower_id" value="<?php echo $tower['id']; ?>">
                                <div class="relative">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Signal Strength</label>
                                    <select name="score" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl px-4 py-3 text-sm font-bold dark:text-white focus:ring-2 focus:ring-primary transition-all appearance-none cursor-pointer" required>
                                        <option value="">Select Level...</option>
                                        <option value="0">0 - Critical Failure</option>
                                        <option value="1">1 - Severe Instability</option>
                                        <option value="2">2 - Weak Reception</option>
                                        <option value="3">3 - Operational Balance</option>
                                        <option value="4">4 - High Performance</option>
                                        <option value="5">5 - Optimal Peak</option>
                                    </select>
                                    <div class="absolute right-4 top-8 pointer-events-none text-gray-400">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Transmission Log</label>
                                    <textarea name="remarks" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl px-4 py-3 text-sm dark:text-white focus:ring-2 focus:ring-primary transition-all" rows="2" placeholder="Describe system anomalies..."></textarea>
                                </div>
                                <button type="submit" name="update_signal" class="w-full bg-primary hover:bg-blue-600 text-white py-4 rounded-xl font-black text-sm shadow-xl shadow-blue-500/20 active:scale-95 transition-all uppercase tracking-widest">
                                    Transmit Update
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Decorative Background element -->
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-colors"></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
