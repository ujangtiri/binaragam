<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../user/login.html');
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'binaragam_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get statistics
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products") ? $pdo->query("SELECT COUNT(*) FROM products") : 0;
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users") ? $pdo->query("SELECT COUNT(*) FROM users") : 0;
$totalClicks = $pdo->query("SELECT SUM(total_clicks) FROM product_clicks") ? $pdo->query("SELECT SUM(total_clicks) FROM product_clicks") : 0;
$shopeeClicks = $pdo->query("SELECT SUM(total_clicks) FROM product_clicks WHERE platform = 'shopee'") ? $pdo->query("SELECT SUM(total_clicks) FROM product_clicks WHERE platform = 'shopee'") : 0;
$tokopediaClicks = $pdo->query("SELECT SUM(total_clicks) FROM product_clicks WHERE platform = 'tokopedia'") ? $pdo->query("SELECT SUM(total_clicks) FROM product_clicks WHERE platform = 'tokopedia'") : 0;

// Get recent analytics data for charts
$analyticsData = $pdo->query("SELECT DATE(clicked_at) as date, COUNT(*) as clicks FROM product_clicks GROUP BY DATE(clicked_at) ORDER BY date DESC LIMIT 7") ? $pdo->query("SELECT DATE(clicked_at) as date, COUNT(*) as clicks FROM product_clicks GROUP BY DATE(clicked_at) ORDER BY date DESC LIMIT 7") : [];
$topProducts = $pdo->query("SELECT product_name, SUM(total_clicks) as total_clicks FROM product_clicks GROUP BY product_name ORDER BY total_clicks DESC LIMIT 5") ? $pdo->query("SELECT product_name, SUM(total_clicks) as total_clicks FROM product_clicks GROUP BY product_name ORDER BY total_clicks DESC LIMIT 5") : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Bina Ragam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        .sidebar-item:hover {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
        }
        .sidebar-item.active {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
        }
    </style>
</head>
<body class="font-poppins bg-gradient-to-br from-primary-50 to-secondary-50 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white/90 backdrop-blur-sm shadow-xl">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-xl flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Bina Ragam</h1>
                        <p class="text-sm text-gray-500">Admin Panel</p>
                    </div>
                </div>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="sidebar-item active flex items-center space-x-3 p-3 rounded-xl transition-all duration-200">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="products.php" class="sidebar-item flex items-center space-x-3 p-3 rounded-xl transition-all duration-200">
                            <i data-lucide="package" class="w-5 h-5"></i>
                            <span>Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="sidebar-item flex items-center space-x-3 p-3 rounded-xl transition-all duration-200">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="discounts.php" class="sidebar-item flex items-center space-x-3 p-3 rounded-xl transition-all duration-200">
                            <i data-lucide="tag" class="w-5 h-5"></i>
                            <span>Diskon</span>
                        </a>
                    </li>
                    <li>
                        <a href="analytics.php" class="sidebar-item flex items-center space-x-3 p-3 rounded-xl transition-all duration-200">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                            <span>Analitik</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="sidebar-item flex items-center space-x-3 p-3 rounded-xl transition-all duration-200">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                </div>
                <a href="logout.php" class="w-full bg-red-500 text-white py-2 px-4 rounded-xl hover:bg-red-600 transition-colors duration-200 flex items-center justify-center space-x-2">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white/90 backdrop-blur-sm shadow-lg p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                        <p class="text-gray-600">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>!</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Hari ini</p>
                            <p class="text-lg font-semibold text-gray-800"><?php echo date('d M Y'); ?></p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" data-aos="fade-up">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Total Produk</p>
                                <p class="text-3xl font-bold text-primary-600"><?php echo $totalProducts; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                                <i data-lucide="package" class="w-6 h-6 text-primary-600"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-green-500 text-sm font-medium">+12%</span>
                            <span class="text-gray-500 text-sm ml-1">dari bulan lalu</span>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Total Users</p>
                                <p class="text-3xl font-bold text-secondary-600"><?php echo $totalUsers; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center">
                                <i data-lucide="users" class="w-6 h-6 text-secondary-600"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-green-500 text-sm font-medium">+8%</span>
                            <span class="text-gray-500 text-sm ml-1">dari bulan lalu</span>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Klik Shopee</p>
                                <p class="text-3xl font-bold text-orange-600"><?php echo $shopeeClicks; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                                <i data-lucide="external-link" class="w-6 h-6 text-orange-600"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-green-500 text-sm font-medium">+15%</span>
                            <span class="text-gray-500 text-sm ml-1">dari minggu lalu</span>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Klik Tokopedia</p>
                                <p class="text-3xl font-bold text-green-600"><?php echo $tokopediaClicks; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <i data-lucide="external-link" class="w-6 h-6 text-green-600"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-green-500 text-sm font-medium">+10%</span>
                            <span class="text-gray-500 text-sm ml-1">dari minggu lalu</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Analytics Chart -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">Analitik Klik Harian</h3>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                                <span class="text-sm text-gray-600">Shopee</span>
                                <div class="w-3 h-3 bg-green-500 rounded-full ml-4"></div>
                                <span class="text-sm text-gray-600">Tokopedia</span>
                            </div>
                        </div>
                        <canvas id="analyticsChart" width="400" height="200"></canvas>
                    </div>

                    <!-- Top Products -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6" data-aos="fade-up" data-aos-delay="200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-6">Produk Terpopuler</h3>
                        <div class="space-y-4">
                            <?php foreach ($topProducts as $index => $product): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                                        <span class="text-primary-600 font-semibold text-sm"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($product['product_name']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo $product['total_clicks']; ?> klik</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-primary-600"><?php echo $product['total_clicks']; ?></span>
                                    <p class="text-xs text-gray-500">klik</p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Aktivitas Terbaru</h3>
                        <a href="analytics.php" class="text-primary-600 hover:text-primary-700 font-medium text-sm">Lihat Semua</a>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="external-link" class="w-5 h-5 text-orange-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Klik ke Shopee</p>
                                <p class="text-sm text-gray-500">Produk "Laptop Gaming" - 2 menit yang lalu</p>
                            </div>
                            <span class="text-sm font-semibold text-orange-600">+1</span>
                        </div>
                        
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="external-link" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Klik ke Tokopedia</p>
                                <p class="text-sm text-gray-500">Produk "Smartphone" - 5 menit yang lalu</p>
                            </div>
                            <span class="text-sm font-semibold text-green-600">+1</span>
                        </div>
                        
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="user" class="w-5 h-5 text-primary-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">User Baru</p>
                                <p class="text-sm text-gray-500">John Doe mendaftar - 10 menit yang lalu</p>
                            </div>
                            <span class="text-sm font-semibold text-primary-600">+1</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // GSAP Animation
        gsap.from(".bg-white\/80", {
            duration: 1,
            y: 50,
            opacity: 0,
            stagger: 0.1,
            ease: "power2.out"
        });

        // Analytics Chart
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        const analyticsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                datasets: [{
                    label: 'Shopee',
                    data: [12, 19, 15, 25, 22, 30, 28],
                    borderColor: '#F97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Tokopedia',
                    data: [8, 15, 12, 20, 18, 25, 22],
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Auto refresh dashboard every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>