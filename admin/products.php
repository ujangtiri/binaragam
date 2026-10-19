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

// Handle CRUD operations
$message = '';
$messageType = '';

// Add/Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category = $_POST['category'];
            $stock = $_POST['stock'];
            $shopee_link = $_POST['shopee_link'];
            $tokopedia_link = $_POST['tokopedia_link'];
            $image_url = $_POST['image_url'];
            $discount = $_POST['discount'] ?? 0;
            $rating = $_POST['rating'] ?? 4.5;
            $weight = $_POST['weight'] ?? '500g';
            $origin = $_POST['origin'] ?? 'Indonesia';

            if ($_POST['action'] === 'add') {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, stock, shopee_link, tokopedia_link, image_url, discount, rating, weight, origin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $category, $stock, $shopee_link, $tokopedia_link, $image_url, $discount, $rating, $weight, $origin]);
                $message = 'Produk berhasil ditambahkan!';
                $messageType = 'success';
            } else if ($_POST['action'] === 'edit' && isset($_POST['id'])) {
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, stock = ?, shopee_link = ?, tokopedia_link = ?, image_url = ?, discount = ?, rating = ?, weight = ?, origin = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $category, $stock, $shopee_link, $tokopedia_link, $image_url, $discount, $rating, $weight, $origin, $id]);
                $message = 'Produk berhasil diperbarui!';
                $messageType = 'success';
            }
        } else if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Produk berhasil dihapus!';
            $messageType = 'success';
        }
    } catch (PDOException $e) {
        $message = 'Terjadi kesalahan: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all products
$products = [];
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Gagal memuat produk: ' . $e->getMessage();
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Bina Ragam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <a href="dashboard.php" class="sidebar-item flex items-center space-x-3 p-3 rounded-xl transition-all duration-200">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="products.php" class="sidebar-item active flex items-center space-x-3 p-3 rounded-xl transition-all duration-200">
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
                        <h1 class="text-2xl font-bold text-gray-800">Kelola Produk</h1>
                        <p class="text-gray-600">Kelola produk toko Bina Ragam</p>
                    </div>
                    <button onclick="showAddModal()" class="bg-primary-600 text-white px-6 py-3 rounded-xl hover:bg-primary-700 transition-colors duration-200 flex items-center space-x-2">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        <span>Tambah Produk</span>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" data-aos="fade-down">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- Products Table -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-800">Produk</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-800">Kategori</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-800">Harga</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-800">Stok</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-800">Diskon</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-800">Rating</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-800">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-4">
                                            <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/60x60?text=Produk'); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="w-12 h-12 object-cover rounded-lg">
                                            <div>
                                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block bg-primary-100 text-primary-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <?php echo htmlspecialchars($product['category']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-semibold text-gray-800">Rp<?php echo number_format($product['price'] * (1 - ($product['discount'] ?? 0) / 100), 0, ',', '.'); ?></p>
                                            <?php if ($product['discount'] > 0): ?>
                                            <p class="text-sm text-gray-500 line-through">Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <?php echo htmlspecialchars($product['stock']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($product['discount'] > 0): ?>
                                        <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                                            -<?php echo htmlspecialchars($product['discount']); ?>%
                                        </span>
                                        <?php else: ?>
                                        <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-1">
                                            <?php 
                                            $rating = $product['rating'] ?? 0;
                                            for ($i = 1; $i <= 5; $i++): 
                                                if ($i <= $rating):
                                            ?>
                                                <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-current"></i>
                                            <?php else: ?>
                                                <i data-lucide="star" class="w-4 h-4 text-gray-300"></i>
                                            <?php 
                                                endif;
                                            endfor; 
                                            ?>
                                            <span class="text-sm text-gray-500 ml-1"><?php echo htmlspecialchars($rating); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                                                    class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors duration-200">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </button>
                                            <button onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>')" 
                                                    class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors duration-200">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
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

    <!-- Add/Edit Modal -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 id="modalTitle" class="text-xl font-bold text-gray-800">Tambah Produk</h2>
                    <button onclick="closeModal()" class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            
            <form id="productForm" method="POST" class="p-6 space-y-4">
                <input type="hidden" id="productId" name="id">
                <input type="hidden" id="action" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Produk</label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <select id="category" name="category" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                            <option value="">Pilih Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Fashion">Fashion</option>
                            <option value="Rumah Tangga">Rumah Tangga</option>
                            <option value="Olahraga">Olahraga</option>
                            <option value="Kecantikan">Kecantikan</option>
                            <option value="Makanan">Makanan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga</label>
                        <input type="number" id="price" name="price" required min="0" step="1000" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stok</label>
                        <input type="number" id="stock" name="stock" required min="0" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Diskon (%)</label>
                        <input type="number" id="discount" name="discount" min="0" max="100" value="0" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" value="4.5" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Berat</label>
                        <input type="text" id="weight" name="weight" value="500g" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asal</label>
                        <input type="text" id="origin" name="origin" value="Indonesia" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="description" name="description" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL Gambar</label>
                    <input type="url" id="image_url" name="image_url" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link Shopee</label>
                        <input type="url" id="shopee_link" name="shopee_link" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link Tokopedia</label>
                        <input type="url" id="tokopedia_link" name="tokopedia_link" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors duration-200">
                        Simpan
                    </button>
                </div>
            </form>
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

        // Show Add Modal
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Produk';
            document.getElementById('action').value = 'add';
            document.getElementById('productForm').reset();
            document.getElementById('productModal').classList.remove('hidden');
            
            gsap.from("#productModal > div", {
                duration: 0.5,
                scale: 0.9,
                opacity: 0,
                ease: "power2.out"
            });
        }

        // Edit Product
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Produk';
            document.getElementById('action').value = 'edit';
            document.getElementById('productId').value = product.id;
            
            // Fill form with product data
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('price').value = product.price;
            document.getElementById('category').value = product.category;
            document.getElementById('stock').value = product.stock;
            document.getElementById('discount').value = product.discount || 0;
            document.getElementById('rating').value = product.rating || 4.5;
            document.getElementById('weight').value = product.weight || '500g';
            document.getElementById('origin').value = product.origin || 'Indonesia';
            document.getElementById('image_url').value = product.image_url;
            document.getElementById('shopee_link').value = product.shopee_link || '';
            document.getElementById('tokopedia_link').value = product.tokopedia_link || '';
            
            document.getElementById('productModal').classList.remove('hidden');
            
            gsap.from("#productModal > div", {
                duration: 0.5,
                scale: 0.9,
                opacity: 0,
                ease: "power2.out"
            });
        }

        // Close Modal
        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        // Delete Product
        function deleteProduct(id, name) {
            Swal.fire({
                title: 'Hapus Produk',
                text: `Apakah Anda yakin ingin menghapus produk "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form to submit delete
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Close modal when clicking outside
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Form validation
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const price = parseFloat(document.getElementById('price').value);
            const stock = parseInt(document.getElementById('stock').value);
            const discount = parseInt(document.getElementById('discount').value);
            
            if (price < 0) {
                e.preventDefault();
                Swal.fire('Error', 'Harga tidak boleh negatif', 'error');
                return;
            }
            
            if (stock < 0) {
                e.preventDefault();
                Swal.fire('Error', 'Stok tidak boleh negatif', 'error');
                return;
            }
            
            if (discount < 0 || discount > 100) {
                e.preventDefault();
                Swal.fire('Error', 'Diskon harus antara 0-100%', 'error');
                return;
            }
        });
    </script>
</body>
</html>