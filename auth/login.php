<?php
// auth/login.php
session_start();
require_once '../includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: ../index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Access - Tower Signals</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { primary: '#0ea5e9', secondary: '#4f46e5' },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
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
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .dark .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .input-floating:focus ~ .label-floating,
        .input-floating:not(:placeholder-shown) ~ .label-floating {
            transform: translateY(-1.5rem) scale(0.85);
            color: #0ea5e9;
        }
    </style>
    <script>
        // System theme detection
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-500">

    <!-- Animated Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-primary/30 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob dark:mix-blend-color-dodge"></div>
        <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-secondary/30 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000 dark:mix-blend-color-dodge"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-96 h-96 bg-pink-500/30 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000 dark:mix-blend-color-dodge"></div>
        <!-- Grid Pattern overlay -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMTU2LCAxNjMsIDE3NSwgMC4yKSIvPjwvc3ZnPg==')] opacity-50 dark:opacity-20"></div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-center z-10 animate-fade-in">
        
        <!-- Left Side: Branding & Info (Hidden on small screens) -->
        <div class="hidden lg:flex flex-col justify-center pr-12">
            <div class="inline-flex items-center space-x-3 mb-8 bg-white/50 dark:bg-black/50 backdrop-blur-md px-4 py-2 rounded-full w-max border border-gray-200 dark:border-gray-800">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">System Online</span>
            </div>
            
            <h1 class="text-5xl lg:text-7xl font-black mb-6 leading-tight tracking-tighter">
                Tower
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">Signals.</span>
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 font-medium mb-12 max-w-md">
                Secure enterprise access to centralized infrastructure monitoring and automated signal intelligence.
            </p>
            
            <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-500 font-semibold">
                <i class="fas fa-shield-halved text-primary text-xl"></i>
                <span>End-to-End Encrypted Protocol v2.4</span>
            </div>
        </div>

        <!-- Right Side: Login Card -->
        <div class="relative w-full max-w-md mx-auto lg:max-w-none">
            <!-- Decorative border glow -->
            <div class="absolute inset-0 bg-gradient-to-r from-primary to-secondary rounded-[2.5rem] blur opacity-20 dark:opacity-40 -z-10 transition-opacity"></div>
            
            <div class="glass-panel rounded-[2.5rem] p-8 sm:p-12 shadow-2xl relative overflow-hidden group">
                <!-- Inner glare effect -->
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/20 to-transparent dark:from-white/5 rounded-t-[2.5rem] pointer-events-none"></div>

                <div class="lg:hidden text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg text-white">
                        <i class="fas fa-satellite-dish text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">Tower Signals</h2>
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl font-black text-gray-800 dark:text-white mb-2 tracking-tight">Access Portal</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Verify credentials to establish connection.</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 flex items-start space-x-3 text-red-600 dark:text-red-400 animate-fade-in">
                        <i class="fas fa-circle-exclamation mt-0.5"></i>
                        <span class="text-sm font-bold"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <!-- Username Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <input type="text" name="username" id="username" class="input-floating block w-full pl-12 pr-4 pt-4 pb-2 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/50 rounded-2xl appearance-none focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all peer placeholder-transparent" placeholder="Username" required autocomplete="username">
                        <label for="username" class="label-floating absolute text-sm font-bold text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-1.5 scale-85 top-3.5 z-10 origin-[0] left-12 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-85 peer-focus:-translate-y-1.5 cursor-text">
                            Identifier Address
                        </label>
                    </div>

                    <!-- Password Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input type="password" name="password" id="password" class="input-floating block w-full pl-12 pr-4 pt-4 pb-2 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/50 rounded-2xl appearance-none focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all peer placeholder-transparent" placeholder="Password" required autocomplete="current-password">
                        <label for="password" class="label-floating absolute text-sm font-bold text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-1.5 scale-85 top-3.5 z-10 origin-[0] left-12 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-85 peer-focus:-translate-y-1.5 cursor-text">
                            Security Key
                        </label>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between text-sm mt-2">
                        <label class="flex items-center space-x-2 cursor-pointer group/check">
                            <div class="relative w-5 h-5">
                                <input type="checkbox" class="peer sr-only">
                                <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-md peer-checked:bg-primary peer-checked:border-primary transition-all"></div>
                                <i class="fas fa-check absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                            <span class="font-semibold text-gray-600 dark:text-gray-400 group-hover/check:text-gray-900 dark:group-hover/check:text-white transition-colors">Keep Session Active</span>
                        </label>
                        <a href="#" class="font-bold text-primary hover:text-secondary transition-colors">Recovery?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full relative group/btn overflow-hidden rounded-2xl py-4 flex items-center justify-center font-black text-white px-8 bg-gray-900 dark:bg-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 shadow-[0_0_40px_-5px_var(--tw-shadow-color)] shadow-primary/30 transition-all duration-300 hover:-translate-y-0.5 mt-8">
                        <!-- Button hover gradient effect -->
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-primary via-secondary to-primary opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300 -z-10"></div>
                        <span class="mr-2 tracking-widest uppercase text-sm">Initialize Connection</span>
                        <i class="fas fa-arrow-right text-sm transform group-hover/btn:translate-x-1 transition-transform"></i>
                    </button>
                </form>

                <!-- Footer text -->
                <div class="mt-8 text-center border-t border-gray-200 dark:border-gray-700/50 pt-6">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        Tower Ops Core &copy; <?php echo date('Y'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
