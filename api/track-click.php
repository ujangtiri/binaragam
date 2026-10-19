<?php
// track-click.php - API untuk tracking klik produk ke marketplace

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'binaragam_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Create table if not exists
$createTableSQL = "CREATE TABLE IF NOT EXISTS product_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    platform VARCHAR(50) NOT NULL,
    click_count INT DEFAULT 1,
    last_clicked TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_platform (platform)
)";

$createAnalyticsTableSQL = "CREATE TABLE IF NOT EXISTS analytics_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(100) NOT NULL,
    metric_value INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_metric (metric_name)
)";

try {
    $pdo->exec($createTableSQL);
    $pdo->exec($createAnalyticsTableSQL);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Table creation failed']);
    exit();
}

// Handle different request methods
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Track a click
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id']) || !isset($data['product_name']) || !isset($data['platform'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit();
    }
    
    $productId = $data['product_id'];
    $productName = $data['product_name'];
    $platform = strtolower($data['platform']); // 'shopee' or 'tokopedia'
    
    if (!in_array($platform, ['shopee', 'tokopedia'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid platform']);
        exit();
    }
    
    try {
        // Check if click record exists
        $stmt = $pdo->prepare("SELECT id, click_count FROM product_clicks WHERE product_id = ? AND platform = ?");
        $stmt->execute([$productId, $platform]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing record
            $updateStmt = $pdo->prepare("UPDATE product_clicks SET click_count = click_count + 1, last_clicked = NOW() WHERE id = ?");
            $updateStmt->execute([$existing['id']]);
        } else {
            // Insert new record
            $insertStmt = $pdo->prepare("INSERT INTO product_clicks (product_id, product_name, platform) VALUES (?, ?, ?)");
            $insertStmt->execute([$productId, $productName, $platform]);
        }
        
        // Update analytics summary
        $metricName = $platform . '_clicks';
        $summaryStmt = $pdo->prepare("INSERT INTO analytics_summary (metric_name, metric_value) VALUES (?, 1) ON DUPLICATE KEY UPDATE metric_value = metric_value + 1");
        $summaryStmt->execute([$metricName]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Click tracked successfully',
            'product_id' => $productId,
            'platform' => $platform
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get analytics data
    
    if (isset($_GET['action']) && $_GET['action'] === 'summary') {
        try {
            // Get total clicks by platform
            $stmt = $pdo->query("SELECT platform, SUM(click_count) as total_clicks FROM product_clicks GROUP BY platform");
            $platformStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get top clicked products
            $topProductsStmt = $pdo->query("SELECT product_name, platform, click_count FROM product_clicks ORDER BY click_count DESC LIMIT 10");
            $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total products with clicks
            $totalProductsStmt = $pdo->query("SELECT COUNT(DISTINCT product_id) as total FROM product_clicks");
            $totalProducts = $totalProductsStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'platform_stats' => $platformStats,
                    'top_products' => $topProducts,
                    'total_products_with_clicks' => $totalProducts['total']
                ]
            ]);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        
    } elseif (isset($_GET['product_id'])) {
        // Get clicks for specific product
        $productId = $_GET['product_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT platform, click_count FROM product_clicks WHERE product_id = ?");
            $stmt->execute([$productId]);
            $productClicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'product_id' => $productId,
                'clicks' => $productClicks
            ]);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        
    } else {
        // Get all click data
        try {
            $stmt = $pdo->query("SELECT * FROM product_clicks ORDER BY last_clicked DESC LIMIT 100");
            $allClicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $allClicks
            ]);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}

$pdo = null;
?>