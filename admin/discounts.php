<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
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

// Handle discount actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $code = $_POST['code'];
            $description = $_POST['description'];
            $discount_percentage = $_POST['discount_percentage'];
            $valid_from = $_POST['valid_from'];
            $valid_until = $_POST['valid_until'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $max_usage = $_POST['max_usage'];
            $min_purchase = $_POST['min_purchase'];
            
            $stmt = $pdo->prepare("INSERT INTO discounts (code, description, discount_percentage, valid_from, valid_until, is_active, max_usage, min_purchase) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $description, $discount_percentage, $valid_from, $valid_until, $is_active, $max_usage, $min_purchase]);
            
            $_SESSION['success'] = 'Diskon berhasil ditambahkan';
            header('Location: discounts.php');
            exit();
        }
        
        if ($action === 'update') {
            $discount_id = $_POST['discount_id'];
            $code = $_POST['code'];
            $description = $_POST['description'];
            $discount_percentage = $_POST['discount_percentage'];
            $valid_from = $_POST['valid_from'];
            $valid_until = $_POST['valid_until'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $max_usage = $_POST['max_usage'];
            $min_purchase = $_POST['min_purchase'];
            
            $stmt = $pdo->prepare("UPDATE discounts SET code = ?, description = ?, discount_percentage = ?, valid_from = ?, valid_until = ?, is_active = ?, max_usage = ?, min_purchase = ? WHERE id = ?");
            $stmt->execute([$code, $description, $discount_percentage, $valid_from, $valid_until, $is_active, $max_usage, $min_purchase, $discount_id]);
            
            $_SESSION['success'] = 'Diskon berhasil diperbarui';
            header('Location: discounts.php');
            exit();
        }
        
        if ($action === 'delete') {
            $discount_id = $_POST['discount_id'];
            
            $stmt = $pdo->prepare("DELETE FROM discounts WHERE id = ?");
            $stmt->execute([$discount_id]);
            
            $_SESSION['success'] = 'Diskon berhasil dihapus';
            header('Location: discounts.php');
            exit();
        }
    }
}

// Get all discounts
$stmt = $pdo->query("SELECT * FROM discounts ORDER BY created_at DESC");
$discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get discount statistics
$total_discounts = $pdo->query("SELECT COUNT(*) FROM discounts")->fetchColumn();
$active_discounts = $pdo->query("SELECT COUNT(*) FROM discounts WHERE is_active = 1 AND valid_until >= CURDATE()")->fetchColumn();
$expired_discounts = $pdo->query("SELECT COUNT(*) FROM discounts WHERE valid_until < CURDATE()")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Diskon - Bina Ragam Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Lottie -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Sidebar -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg h-screen fixed left-0 top-0 z-40">
            <div class="p-6">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                    Bina Ragam Admin
                </h1>
            </div>
            
            <nav class="mt-6">
                <a href="dashboard.php" class="sidebar-nav-link">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="products.php" class="sidebar-nav-link">
                    <i data-lucide="package" class="w-5 h-5 mr-3"></i>
                    Kelola Produk
                </a>
                <a href="users.php" class="sidebar-nav-link">
                    <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                    Kelola User
                </a>
                <a href="discounts.php" class="sidebar-nav-link active">
                    <i data-lucide="tag" class="w-5 h-5 mr-3"></i>
                    Kelola Diskon
                </a>
                <div class="border-t border-gray-200 mt-6 pt-6">
                    <a href="logout.php" class="sidebar-nav-link text-red-600 hover:bg-red-50">
                        <i data-lucide="log-out" class="w-5 h-5 mr-3"></i>
                        Logout
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Kelola Diskon</h1>
                            <p class="text-gray-600">Manajemen kode diskon dan promosi</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-600">Halo, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                                <i data-lucide="user" class="w-5 h-5 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="flex items-center">
                            <div class="bg-primary/10 p-3 rounded-xl">
                                <i data-lucide="tag" class="w-6 h-6 text-primary"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Total Diskon</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $total_discounts; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="flex items-center">
                            <div class="bg-success/10 p-3 rounded-xl">
                                <i data-lucide="check-circle" class="w-6 h-6 text-success"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Diskon Aktif</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $active_discounts; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="flex items-center">
                            <div class="bg-danger/10 p-3 rounded-xl">
                                <i data-lucide="x-circle" class="w-6 h-6 text-danger"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Diskon Kadaluarsa</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $expired_discounts; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Discount Button -->
                <div class="mb-6" data-aos="fade-up" data-aos-delay="400">
                    <button onclick="openAddModal()" class="btn-primary">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Tambah Diskon Baru
                    </button>
                </div>

                <!-- Discounts Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="500">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Diskon</h3>
                            <div class="flex items-center space-x-4">
                                <input type="text" 
                                       id="search-input" 
                                       placeholder="Cari diskon..." 
                                       class="form-input w-64">
                                <button class="btn-primary">
                                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                                    Cari
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Deskripsi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Diskon
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Berlaku Dari
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Berlaku Sampai
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($discounts as $discount): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($discount['code']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($discount['description']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $discount['discount_percentage']; ?>%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d M Y', strtotime($discount['valid_from'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d M Y', strtotime($discount['valid_until'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $is_expired = strtotime($discount['valid_until']) < time();
                                        $status_class = $discount['is_active'] && !$is_expired ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        $status_text = $discount['is_active'] && !$is_expired ? 'Aktif' : 'Tidak Aktif';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="editDiscount(<?php echo htmlspecialchars(json_encode($discount)); ?>)" class="text-blue-600 hover:text-blue-900">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </button>
                                            <form method="POST" class="inline" onsubmit="return confirm('Hapus diskon ini?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="discount_id" value="<?php echo $discount['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Discount Modal -->
    <div id="discount-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900 mb-4">Tambah Diskon Baru</h3>
                    <form id="discount-form" method="POST">
                        <input type="hidden" name="action" id="form-action" value="add">
                        <input type="hidden" name="discount_id" id="discount-id">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Diskon</label>
                                <input type="text" name="code" id="code" required class="form-input">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                <textarea name="description" id="description" rows="2" required class="form-input"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Persentase Diskon (%)</label>
                                <input type="number" name="discount_percentage" id="discount-percentage" min="1" max="100" required class="form-input">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date" name="valid_from" id="valid-from" required class="form-input">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                <input type="date" name="valid_until" id="valid-until" required class="form-input">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pemakaian Maksimal</label>
                                <input type="number" name="max_usage" id="max-usage" min="1" required class="form-input">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pembelian Minimal (Rp)</label>
                                <input type="number" name="min_purchase" id="min-purchase" min="0" required class="form-input">
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is-active" class="mr-2">
                                <label for="is-active" class="text-sm font-medium text-gray-700">Aktif</label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                                Batal
                            </button>
                            <button type="submit" class="btn-primary">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Modal functions
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Tambah Diskon Baru';
            document.getElementById('form-action').value = 'add';
            document.getElementById('discount-form').reset();
            document.getElementById('discount-modal').classList.remove('hidden');
        }

        function editDiscount(discount) {
            document.getElementById('modal-title').textContent = 'Edit Diskon';
            document.getElementById('form-action').value = 'update';
            document.getElementById('discount-id').value = discount.id;
            document.getElementById('code').value = discount.code;
            document.getElementById('description').value = discount.description;
            document.getElementById('discount-percentage').value = discount.discount_percentage;
            document.getElementById('valid-from').value = discount.valid_from;
            document.getElementById('valid-until').value = discount.valid_until;
            document.getElementById('max-usage').value = discount.max_usage;
            document.getElementById('min-purchase').value = discount.min_purchase;
            document.getElementById('is-active').checked = discount.is_active == 1;
            document.getElementById('discount-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('discount-modal').classList.add('hidden');
        }

        // Show success message if exists
        <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?php echo $_SESSION['success']; ?>',
            timer: 2000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        // GSAP Animations
        gsap.from(".bg-white.rounded-xl", {
            duration: 1,
            y: 50,
            opacity: 0,
            stagger: 0.2,
            ease: "power2.out"
        });
    </script>
</body>
</html>