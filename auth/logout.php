<?php
// auth/logout.php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Auto redirect after 3 seconds -->
    <meta http-equiv="refresh" content="3;url=login.php">
    <title>Logging Out - Tower Signals</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { primary: '#0ea5e9', secondary: '#4f46e5' },
                    animation: {
                        'progress': 'progress 3s linear forwards',
                        'bounce-in': 'bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards'
                    },
                    keyframes: {
                        progress: { '0%': { width: '100%' }, '100%': { width: '0%' } },
                        bounceIn: {
                            '0%': { transform: 'scale(0)', opacity: '0' },
                            '80%': { transform: 'scale(1.1)', opacity: '1' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex items-center justify-center p-4 transition-colors duration-500">

    <!-- Subtle Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-tr from-emerald-400/10 to-transparent dark:from-emerald-500/5 rounded-full blur-3xl"></div>
        <!-- Grid Pattern overlay -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMTU2LCAxNjMsIDE3NSwgMC4yKSIvPjwvc3ZnPg==')] opacity-50 dark:opacity-20"></div>
    </div>

    <div class="w-full max-w-md bg-white/70 dark:bg-gray-800/70 backdrop-blur-2xl p-10 rounded-[2.5rem] shadow-2xl border border-white/50 dark:border-gray-700/50 text-center relative overflow-hidden transform transition-all duration-500 hover:shadow-3xl">
        
        <!-- Animated Icon -->
        <div class="w-24 h-24 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-8 shadow-[0_0_30px_-5px_var(--tw-shadow-color)] shadow-emerald-500/50 animate-bounce-in relative scale-0">
            <i class="fas fa-check text-4xl text-white drop-shadow-md"></i>
            <div class="absolute inset-0 border-4 border-emerald-400 rounded-full animate-ping opacity-20"></div>
        </div>

        <h2 class="text-3xl font-black text-gray-800 dark:text-white mb-3 tracking-tight">Access Terminated</h2>
        <p class="text-gray-500 dark:text-gray-400 font-medium mb-10 leading-relaxed">
            Your secure session has been successfully closed. All connections severed.
        </p>

        <!-- Progress Bar & Redirect Info -->
        <div class="relative w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden mb-8">
            <div class="absolute top-0 right-0 h-full bg-gradient-to-r from-emerald-400 to-emerald-600 animate-progress origin-right w-full"></div>
        </div>
        
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center justify-center gap-2">
            <i class="fas fa-satellite-dish animate-pulse text-emerald-500"></i>
            Redirecting to Gateway...
        </p>

        <!-- Manual Redirect Button -->
        <a href="login.php" class="inline-flex items-center justify-center w-full py-4 rounded-2xl font-black text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-900 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors uppercase tracking-widest text-sm group">
            <i class="fas fa-arrow-left mr-2 transform group-hover:-translate-x-1 transition-transform"></i>
            Return Immediately
        </a>
    </div>

</body>
</html>
