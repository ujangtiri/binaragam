<?php
session_start();

// Check if already logged in
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$messageType = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple single-admin authentication. Credentials come from the environment
    // (BINARAGAM_ADMIN_EMAIL / BINARAGAM_ADMIN_PASSWORD); nothing is hard-coded.
    $adminEmail = getenv('BINARAGAM_ADMIN_EMAIL');
    $adminPassword = getenv('BINARAGAM_ADMIN_PASSWORD');
    if ($adminEmail && $adminPassword && hash_equals($adminEmail, $email) && hash_equals($adminPassword, $password)) {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Admin Bina Ragam';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'admin';
        
        header('Location: dashboard.php');
        exit();
    } else {
        $message = 'Email atau password salah!';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Bina Ragam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        .glassmorphism {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .password-toggle {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .password-toggle:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="font-poppins bg-gradient-to-br from-primary-50 to-secondary-50 min-h-screen flex items-center justify-center p-4">
    
    <!-- Background Animation -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-primary-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob"></div>
        <div class="absolute top-1/3 right-1/4 w-64 h-64 bg-secondary-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-1/4 left-1/3 w-64 h-64 bg-yellow-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Login Container -->
    <div class="relative z-10 w-full max-w-md" data-aos="fade-up">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <i data-lucide="shopping-bag" class="w-8 h-8 text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Bina Ragam</h1>
                    <p class="text-gray-600">Admin Portal</p>
                </div>
            </div>
        </div>

        <!-- Login Form -->
        <div class="glassmorphism rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Selamat Datang Kembali</h2>
                <p class="text-gray-600">Masuk ke panel admin Bina Ragam</p>
            </div>

            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="admin@example.com"
                            class="w-full pl-12 pr-4 py-3 bg-white/50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder="Password"
                            class="w-full pl-12 pr-12 py-3 bg-white/50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle">
                            <i data-lucide="eye" id="eyeIcon" class="w-5 h-5 text-gray-400"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm text-primary-600 hover:text-primary-700 transition-colors duration-200">
                        Lupa password?
                    </a>
                </div>

                <button 
                    type="submit" 
                    id="loginButton"
                    class="w-full bg-gradient-to-r from-primary-600 to-secondary-600 text-white py-3 px-6 rounded-xl hover:from-primary-700 hover:to-secondary-700 transition-all duration-300 transform hover:scale-105 font-semibold flex items-center justify-center space-x-2"
                >
                    <span id="loginText">Masuk</span>
                    <div id="loadingSpinner" class="hidden">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    </div>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Kembali ke halaman utama? 
                    <a href="../index.html" class="text-primary-600 hover:text-primary-700 font-medium transition-colors duration-200">
                        Klik di sini
                    </a>
                </p>
            </div>
        </div>

    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        
        .animate-blob {
            animation: blob 7s infinite;
        }
        
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // GSAP Animation
        gsap.from(".glassmorphism", {
            duration: 1,
            y: 50,
            opacity: 0,
            ease: "power2.out"
        });

        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            
            lucide.createIcons();
        }

        // Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginButton = document.getElementById('loginButton');
            const loginText = document.getElementById('loginText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            // Show loading state
            loginButton.disabled = true;
            loginText.textContent = 'Memproses...';
            loadingSpinner.classList.remove('hidden');
            
            // Re-enable after 2 seconds (for demo)
            setTimeout(() => {
                loginButton.disabled = false;
                loginText.textContent = 'Masuk';
                loadingSpinner.classList.add('hidden');
            }, 2000);
        });

        // Add some interactive effects
        const formInputs = document.querySelectorAll('input');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                gsap.to(this.parentElement, {
                    scale: 1.02,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
            
            input.addEventListener('blur', function() {
                gsap.to(this.parentElement, {
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
        });
    </script>
</body>
</html>