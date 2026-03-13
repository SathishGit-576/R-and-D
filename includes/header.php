<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Tower Dashboard'; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#0d6efd',
                        darkBg: '#1a1d20',
                        darkCard: '#2d3238',
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        // Theme initialization
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">
<div class="flex">
<?php include 'sidebar.php'; ?>
<div class="main-content w-full min-h-screen">
    <nav class="bg-white dark:bg-gray-800 shadow-md mb-6 px-6 py-3 rounded-xl mx-4 mt-4 flex justify-between items-center transition-colors">
        <div class="flex items-center">
            <span class="text-xl font-bold text-primary"><?php echo $page_title ?? 'Dashboard'; ?></span>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Theme Toggle Button -->
            <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none" title="Toggle Theme">
                <i class="fas fa-sun dark:hidden text-yellow-500"></i>
                <i class="fas fa-moon hidden dark:block text-blue-400"></i>
            </button>
            <div class="flex items-center space-x-3 border-l pl-4 dark:border-gray-700">
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo ucfirst($_SESSION['role']); ?></p>
                </div>
                <i class="fas fa-user-circle text-2xl text-gray-400"></i>
                <a href="../auth/logout.php" class="text-red-500 hover:text-red-600 transition-colors ml-2" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>
