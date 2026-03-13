<?php
// admin/towers.php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
checkLogin();
checkAdmin();

$page_title = "Tower Management";
$active_page = "towers";
$message = '';

// Handle Create Tower
if (isset($_POST['create_tower'])) {
    $name = trim($_POST['tower_name']);
    $category = trim($_POST['category']);
    $loc = trim($_POST['location']);
    $user_id = !empty($_POST['assigned_user_id']) ? $_POST['assigned_user_id'] : null;

    $stmt = $pdo->prepare("INSERT INTO towers (tower_name, category, location, assigned_user_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $category, $loc, $user_id]);
    $message = '<div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-check-circle"></i><span>Tower created successfully!</span></div>';
}

// Handle Edit Tower
if (isset($_POST['edit_tower'])) {
    $id = intval($_POST['edit_id']);
    $name = trim($_POST['edit_tower_name']);
    $category = trim($_POST['edit_category']);
    $loc = trim($_POST['edit_location']);
    $user_id = !empty($_POST['edit_assigned_user_id']) ? $_POST['edit_assigned_user_id'] : null;

    $stmt = $pdo->prepare("UPDATE towers SET tower_name = ?, category = ?, location = ?, assigned_user_id = ? WHERE id = ?");
    $stmt->execute([$name, $category, $loc, $user_id, $id]);
    $message = '<div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-check-circle"></i><span>Tower updated successfully!</span></div>';
}

// Handle Delete Tower
if (isset($_POST['delete_tower'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM towers WHERE id = ?");
    $stmt->execute([$id]);
    $message = '<div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-check-circle"></i><span>Tower deleted successfully!</span></div>';
}

// Fetch Towers with Assigned User
$towers = $pdo->query("
    SELECT t.*, u.username as assigned_user 
    FROM towers t 
    LEFT JOIN users u ON t.assigned_user_id = u.id 
    ORDER BY t.created_at DESC
")->fetchAll();

// Fetch all members for assignment dropdown
$members = $pdo->query("SELECT id, username FROM users WHERE role = 'member' ORDER BY username ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="px-4 py-6">
    <?php echo $message; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Tower Assets</h1>
            <p class="text-gray-500 dark:text-gray-400">Manage and monitor all infrastructure and testing towers.</p>
        </div>
        <button onclick="openModal('addTowerModal')" class="bg-primary hover:bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold shadow-lg shadow-blue-500/30 transition-all flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Register New Tower</span>
        </button>
    </div>

    <!-- Towers Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID & Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned To</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if (empty($towers)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">No towers found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($towers as $tower): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-primary">
                                        <i class="fas fa-broadcast-tower"></i>
                                    </div>
                                    <div>
                                        <span class="block font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($tower['tower_name']); ?></span>
                                        <span class="text-xs text-gray-500">#<?php echo $tower['id']; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $tower['category'] === 'Automation Testing' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'; ?>">
                                    <?php echo htmlspecialchars($tower['category'] ?? 'Infrastructure'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center text-gray-600 dark:text-gray-300 text-sm">
                                    <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                                    <?php echo htmlspecialchars($tower['location'] ?? 'Not set'); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($tower['assigned_user']): ?>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-[10px] uppercase font-bold text-gray-600 dark:text-gray-300">
                                            <?php echo substr($tower['assigned_user'], 0, 1); ?>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200"><?php echo htmlspecialchars($tower['assigned_user']); ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button type="button" onclick="openEditModal(<?php echo $tower['id']; ?>, '<?php echo htmlspecialchars(addslashes($tower['tower_name'])); ?>', '<?php echo htmlspecialchars(addslashes($tower['category'])); ?>', '<?php echo htmlspecialchars(addslashes($tower['location'])); ?>', '<?php echo $tower['assigned_user_id']; ?>')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all" title="Edit Tower">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button type="button" onclick="openDeleteModal(<?php echo $tower['id']; ?>, '<?php echo htmlspecialchars(addslashes($tower['tower_name'])); ?>')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all" title="Delete Tower">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- ADD TOWER MODAL                                                             -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<div id="addTowerModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('addTowerModal')"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 dark:border-gray-700 z-10">
        <form method="POST" action="">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h5 class="text-xl font-bold text-gray-800 dark:text-white">Register New Asset</h5>
                </div>
                <button type="button" onclick="closeModal('addTowerModal')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Tower/Device Name</label>
                    <input type="text" name="tower_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm" placeholder="e.g. Tower 101" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Type / Category</label>
                    <select name="category" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm" required>
                        <option value="Infrastructure">Infrastructure Tower</option>
                        <option value="Automation Testing">Automation Testing</option>
                        <option value="Network Node">Network Node</option>
                        <option value="Monitoring Station">Monitoring Station</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Physical Location</label>
                    <input type="text" name="location" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm" placeholder="e.g. Downtown Area">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Assign Responsibility</label>
                    <select name="assigned_user_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm">
                        <option value="">-- No Assignment --</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addTowerModal')" class="px-5 py-2.5 rounded-xl font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all text-sm">Cancel</button>
                <button type="submit" name="create_tower" class="bg-primary hover:bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all text-sm flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Deploy Tower</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- EDIT TOWER MODAL                                                            -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<div id="editTowerModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('editTowerModal')"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 dark:border-gray-700 z-10">
        <form method="POST" action="">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h5 class="text-xl font-bold text-gray-800 dark:text-white">Edit Asset</h5>
                </div>
                <button type="button" onclick="closeModal('editTowerModal')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Tower/Device Name</label>
                    <input type="text" name="edit_tower_name" id="edit_tower_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Type / Category</label>
                    <select name="edit_category" id="edit_category" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm" required>
                        <option value="Infrastructure">Infrastructure Tower</option>
                        <option value="Automation Testing">Automation Testing</option>
                        <option value="Network Node">Network Node</option>
                        <option value="Monitoring Station">Monitoring Station</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Physical Location</label>
                    <input type="text" name="edit_location" id="edit_location" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Assign Responsibility</label>
                    <select name="edit_assigned_user_id" id="edit_assigned_user_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm">
                        <option value="">-- No Assignment --</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editTowerModal')" class="px-5 py-2.5 rounded-xl font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all text-sm">Cancel</button>
                <button type="submit" name="edit_tower" class="bg-primary hover:bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all text-sm flex items-center space-x-2">
                    <i class="fas fa-save"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- DELETE CONFIRMATION MODAL                                                   -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<div id="deleteTowerModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('deleteTowerModal')"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm border border-gray-100 dark:border-gray-700 z-10 text-center p-8">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-trash-alt text-2xl text-red-500"></i>
        </div>
        <h5 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Delete Asset?</h5>
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">You are about to permanently delete:</p>
        <p class="font-bold text-gray-800 dark:text-white text-lg mb-6" id="deleteTowerName">—</p>
        <p class="text-xs text-red-500 mb-6">This action cannot be undone. All daily updates associated with this tower will also be deleted.</p>
        <form method="POST" action="">
            <input type="hidden" name="delete_id" id="delete_id_input">
            <div class="flex space-x-3">
                <button type="button" onclick="closeModal('deleteTowerModal')" class="flex-1 px-5 py-2.5 rounded-xl font-semibold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all text-sm">Cancel</button>
                <button type="submit" name="delete_tower" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-xl font-bold transition-all text-sm flex items-center justify-center space-x-2">
                    <i class="fas fa-trash-alt"></i>
                    <span>Delete</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['addTowerModal', 'editTowerModal', 'deleteTowerModal'].forEach(closeModal);
        }
    });

    function openEditModal(id, name, category, location, assigned_user_id) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_tower_name').value = name;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_location').value = location;
        document.getElementById('edit_assigned_user_id').value = assigned_user_id;
        openModal('editTowerModal');
    }

    function openDeleteModal(id, name) {
        document.getElementById('delete_id_input').value = id;
        document.getElementById('deleteTowerName').textContent = name;
        openModal('deleteTowerModal');
    }
</script>

<?php include '../includes/footer.php'; ?>
