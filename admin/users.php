<?php
// admin/users.php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
checkLogin();
checkAdmin();

$page_title = "User Management";
$active_page = "users";
$message = '';

// ─── Handle CREATE User ───────────────────────────────────────────────────────
if (isset($_POST['create_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'member';

    if (empty($username) || empty($password)) {
        $message = '<div class="bg-amber-50 border-l-4 border-amber-400 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-exclamation-triangle"></i><span>Username and password are required.</span></div>';
    } elseif (strlen($username) < 3) {
        $message = '<div class="bg-amber-50 border-l-4 border-amber-400 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-exclamation-triangle"></i><span>Username must be at least 3 characters.</span></div>';
    } elseif (strlen($password) < 6) {
        $message = '<div class="bg-amber-50 border-l-4 border-amber-400 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-exclamation-triangle"></i><span>Password must be at least 6 characters.</span></div>';
    } elseif (!in_array($role, ['admin', 'member'])) {
        $message = '<div class="bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/20 dark:text-red-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-times-circle"></i><span>Invalid role selected.</span></div>';
    } else {
        // Check duplicate username
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->rowCount() > 0) {
            $message = '<div class="bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/20 dark:text-red-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-times-circle"></i><span>Username already exists. Please choose a different one.</span></div>';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed, $role]);
            $message = '<div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-check-circle"></i><span>User <strong>' . htmlspecialchars($username) . '</strong> created successfully!</span></div>';
        }
    }
}

// ─── Handle EDIT User ─────────────────────────────────────────────────────────
if (isset($_POST['edit_user'])) {
    $edit_id       = intval($_POST['edit_id'] ?? 0);
    $edit_username = trim($_POST['edit_username'] ?? '');
    $edit_role     = $_POST['edit_role'] ?? 'member';
    $edit_password = trim($_POST['edit_password'] ?? '');

    if ($edit_id <= 0 || empty($edit_username)) {
        $message = '<div class="bg-amber-50 border-l-4 border-amber-400 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-exclamation-triangle"></i><span>Invalid user data submitted.</span></div>';
    } elseif (!in_array($edit_role, ['admin', 'member'])) {
        $message = '<div class="bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/20 dark:text-red-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-times-circle"></i><span>Invalid role selected.</span></div>';
    } else {
        // Check duplicate username (excluding current user)
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->execute([$edit_username, $edit_id]);
        if ($check->rowCount() > 0) {
            $message = '<div class="bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/20 dark:text-red-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-times-circle"></i><span>Username already taken by another user.</span></div>';
        } else {
            if (!empty($edit_password)) {
                if (strlen($edit_password) < 6) {
                    $message = '<div class="bg-amber-50 border-l-4 border-amber-400 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-exclamation-triangle"></i><span>New password must be at least 6 characters.</span></div>';
                    goto render_page;
                }
                $hashed = password_hash($edit_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
                $stmt->execute([$edit_username, $edit_role, $hashed, $edit_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$edit_username, $edit_role, $edit_id]);
            }
            // Update session if editing self
            if ($edit_id == $_SESSION['user_id']) {
                $_SESSION['username'] = $edit_username;
                $_SESSION['role']     = $edit_role;
            }
            $message = '<div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-check-circle"></i><span>User <strong>' . htmlspecialchars($edit_username) . '</strong> updated successfully!</span></div>';
        }
    }
}

// ─── Handle DELETE User ───────────────────────────────────────────────────────
if (isset($_POST['delete_user'])) {
    $del_id = intval($_POST['delete_id'] ?? 0);
    if ($del_id <= 0) {
        $message = '<div class="bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/20 dark:text-red-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-times-circle"></i><span>Invalid user ID.</span></div>';
    } elseif ($del_id == $_SESSION['user_id']) {
        $message = '<div class="bg-amber-50 border-l-4 border-amber-400 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-exclamation-triangle"></i><span>You cannot delete your own account.</span></div>';
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$del_id]);
        $message = '<div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 p-4 mb-6 rounded-xl flex items-center space-x-3"><i class="fas fa-check-circle"></i><span>User deleted successfully.</span></div>';
    }
}

render_page:

// ─── Fetch All Users ──────────────────────────────────────────────────────────
$users = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="px-4 py-6">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">User Management</h1>
            <p class="text-gray-500 dark:text-gray-400">Manage system accounts, roles, and access permissions.</p>
        </div>
        <button onclick="openModal('addUserModal')"
                class="bg-primary hover:bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold shadow-lg shadow-blue-500/30 transition-all flex items-center space-x-2">
            <i class="fas fa-user-plus"></i>
            <span>Add New User</span>
        </button>
    </div>

    <!-- Flash Message -->
    <?php if ($message): ?>
        <div id="flashMessage"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
        <?php
            $total_users  = count($users);
            $admin_count  = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
            $member_count = count(array_filter($users, fn($u) => $u['role'] === 'member'));
        ?>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center space-x-4">
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center text-primary">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Users</p>
                <p class="text-2xl font-extrabold text-gray-800 dark:text-white"><?php echo $total_users; ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center space-x-4">
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fas fa-user-shield text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Admins</p>
                <p class="text-2xl font-extrabold text-gray-800 dark:text-white"><?php echo $admin_count; ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center space-x-4">
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center text-emerald-500">
                <i class="fas fa-user-check text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Members</p>
                <p class="text-2xl font-extrabold text-gray-800 dark:text-white"><?php echo $member_count; ?></p>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-50 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h4 class="font-bold text-gray-700 dark:text-gray-300 flex items-center space-x-2">
                <i class="fas fa-list text-primary"></i>
                <span>All System Users</span>
            </h4>
            <div class="relative w-full sm:w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text"
                       id="userSearchInput"
                       onkeyup="filterUsers()"
                       placeholder="Search by username or role..."
                       class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="usersTable">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700" id="usersTableBody">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">No users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors user-row"
                            data-username="<?php echo strtolower(htmlspecialchars($user['username'])); ?>"
                            data-role="<?php echo strtolower($user['role']); ?>">
                            <td class="px-6 py-4">
                                <span class="text-xs font-mono bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 px-2 py-1 rounded-lg">#<?php echo $user['id']; ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm uppercase">
                                        <?php echo substr($user['username'], 0, 1); ?>
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['username']); ?></span>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span class="ml-2 text-[10px] bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300 px-2 py-0.5 rounded-full font-bold uppercase">You</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        <i class="fas fa-shield-alt text-[10px]"></i>
                                        <span>Administrator</span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        <i class="fas fa-user text-[10px]"></i>
                                        <span>Member</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                                <span class="block text-xs text-gray-400 dark:text-gray-600"><?php echo date('H:i', strtotime($user['created_at'])); ?></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Edit Button -->
                                    <button type="button"
                                            onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>', '<?php echo $user['role']; ?>')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all"
                                            title="Edit User">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <!-- Delete Button (only if not self) -->
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button type="button"
                                            onclick="openDeleteModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                                            title="Delete User">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- No results message (hidden by default) -->
        <div id="noSearchResults" class="hidden px-6 py-10 text-center text-gray-400 italic text-sm">
            No users match your search.
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- ADD USER MODAL                                                              -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<div id="addUserModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('addUserModal')"></div>
    <!-- Modal Box -->
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 dark:border-gray-700 z-10">
        <form method="POST" action="" onsubmit="return validateAddForm()">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h5 class="text-xl font-bold text-gray-800 dark:text-white">Create New User</h5>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add a new system account</p>
                </div>
                <button type="button" onclick="closeModal('addUserModal')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="add_username"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm"
                           placeholder="e.g. john_doe" minlength="3" required>
                    <p class="text-xs text-gray-400">Minimum 3 characters. Must be unique.</p>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password" id="add_password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm pr-12"
                               placeholder="Minimum 6 characters" minlength="6" required>
                        <button type="button" onclick="togglePasswordVisibility('add_password', 'add_pass_eye')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <i class="fas fa-eye text-sm" id="add_pass_eye"></i>
                        </button>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Role <span class="text-red-500">*</span></label>
                    <select name="role"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm" required>
                        <option value="member">Member (Operator)</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addUserModal')"
                        class="px-5 py-2.5 rounded-xl font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all text-sm">
                    Cancel
                </button>
                <button type="submit" name="create_user"
                        class="bg-primary hover:bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all text-sm flex items-center space-x-2">
                    <i class="fas fa-user-plus"></i>
                    <span>Create User</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- EDIT USER MODAL                                                             -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<div id="editUserModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('editUserModal')"></div>
    <!-- Modal Box -->
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 dark:border-gray-700 z-10">
        <form method="POST" action="" onsubmit="return validateEditForm()">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h5 class="text-xl font-bold text-gray-800 dark:text-white">Edit User</h5>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Update account details</p>
                </div>
                <button type="button" onclick="closeModal('editUserModal')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="edit_username" id="edit_username"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm"
                           minlength="3" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">New Password</label>
                    <div class="relative">
                        <input type="password" name="edit_password" id="edit_password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm pr-12"
                               placeholder="Leave blank to keep current password">
                        <button type="button" onclick="togglePasswordVisibility('edit_password', 'edit_pass_eye')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <i class="fas fa-eye text-sm" id="edit_pass_eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400">Leave blank to keep the current password. Min 6 chars if changing.</p>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Role <span class="text-red-500">*</span></label>
                    <select name="edit_role" id="edit_role"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm" required>
                        <option value="member">Member (Operator)</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editUserModal')"
                        class="px-5 py-2.5 rounded-xl font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all text-sm">
                    Cancel
                </button>
                <button type="submit" name="edit_user"
                        class="bg-primary hover:bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all text-sm flex items-center space-x-2">
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
<div id="deleteUserModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('deleteUserModal')"></div>
    <!-- Modal Box -->
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm border border-gray-100 dark:border-gray-700 z-10 text-center p-8">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-trash-alt text-2xl text-red-500"></i>
        </div>
        <h5 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Delete User?</h5>
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">You are about to permanently delete:</p>
        <p class="font-bold text-gray-800 dark:text-white text-lg mb-6" id="deleteUserName">—</p>
        <p class="text-xs text-red-500 mb-6">This action cannot be undone. All associated data may be affected.</p>
        <form method="POST" action="">
            <input type="hidden" name="delete_id" id="delete_id_input">
            <div class="flex space-x-3">
                <button type="button" onclick="closeModal('deleteUserModal')"
                        class="flex-1 px-5 py-2.5 rounded-xl font-semibold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all text-sm">
                    Cancel
                </button>
                <button type="submit" name="delete_user"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-xl font-bold transition-all text-sm flex items-center justify-center space-x-2">
                    <i class="fas fa-trash-alt"></i>
                    <span>Delete</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // ── Modal Helpers ──────────────────────────────────────────────────────────
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

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['addUserModal', 'editUserModal', 'deleteUserModal'].forEach(closeModal);
        }
    });

    // ── Edit Modal ─────────────────────────────────────────────────────────────
    function openEditModal(id, username, role) {
        document.getElementById('edit_id').value       = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value     = role;
        document.getElementById('edit_password').value = '';
        openModal('editUserModal');
    }

    // ── Delete Modal ───────────────────────────────────────────────────────────
    function openDeleteModal(id, username) {
        document.getElementById('delete_id_input').value = id;
        document.getElementById('deleteUserName').textContent = username;
        openModal('deleteUserModal');
    }

    // ── Password Visibility Toggle ─────────────────────────────────────────────
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // ── Form Validation ────────────────────────────────────────────────────────
    function validateAddForm() {
        const username = document.getElementById('add_username').value.trim();
        const password = document.getElementById('add_password').value.trim();
        if (username.length < 3) {
            alert('Username must be at least 3 characters.');
            return false;
        }
        if (password.length < 6) {
            alert('Password must be at least 6 characters.');
            return false;
        }
        return true;
    }

    function validateEditForm() {
        const username = document.getElementById('edit_username').value.trim();
        const password = document.getElementById('edit_password').value.trim();
        if (username.length < 3) {
            alert('Username must be at least 3 characters.');
            return false;
        }
        if (password.length > 0 && password.length < 6) {
            alert('New password must be at least 6 characters.');
            return false;
        }
        return true;
    }

    // ── Live Search / Filter ───────────────────────────────────────────────────
    function filterUsers() {
        const query = document.getElementById('userSearchInput').value.toLowerCase().trim();
        const rows  = document.querySelectorAll('.user-row');
        let visibleCount = 0;

        rows.forEach(function(row) {
            const username = row.getAttribute('data-username') || '';
            const role     = row.getAttribute('data-role') || '';
            const matches  = username.includes(query) || role.includes(query);
            row.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        const noResults = document.getElementById('noSearchResults');
        noResults.classList.toggle('hidden', visibleCount > 0);
    }

    // ── Auto-open Add modal if there was a validation error on create ──────────
    <?php if (isset($_POST['create_user']) && strpos($message, 'border-red') !== false): ?>
        document.addEventListener('DOMContentLoaded', function() { openModal('addUserModal'); });
    <?php endif; ?>

    // ── Auto-dismiss flash message after 5 seconds ─────────────────────────────
    setTimeout(function() {
        const flash = document.getElementById('flashMessage');
        if (flash) {
            flash.style.transition = 'opacity 0.5s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }
    }, 5000);
</script>

<?php include '../includes/footer.php'; ?>
