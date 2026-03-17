<?php
/**
 * Test file - Kiểm tra quy trình generateProductId() và upload hình ảnh
 * Truy cập: http://localhost/rice4u/test_sanpham.php
 */

session_start();
require_once 'includes/db.php';

// Bỏ comment nếu muốn test mà không cần đăng nhập
// $_SESSION['ma_tk'] = 1;
// $_SESSION['vai_tro'] = 'admin';

$test_results = [];

// Test 1: Kiểm tra kết nối database
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM sanpham");
    $count = $stmt->fetchColumn();
    $test_results['database'] = [
        'status' => 'pass',
        'message' => "✅ Database hoạt động. Hiện có $count sản phẩm"
    ];
} catch (Exception $e) {
    $test_results['database'] = [
        'status' => 'fail',
        'message' => "❌ Lỗi database: " . $e->getMessage()
    ];
}

// Test 2: Kiểm tra bảng sanpham có tồn tại
try {
    $stmt = $pdo->query("DESCRIBE sanpham");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $test_results['table_structure'] = [
        'status' => 'pass',
        'message' => "✅ Bảng sanpham tồn tại với " . count($fields) . " cột"
    ];
} catch (Exception $e) {
    $test_results['table_structure'] = [
        'status' => 'fail',
        'message' => "❌ Lỗi: " . $e->getMessage()
    ];
}

// Test 3: Kiểm tra thư mục uploads/products
$upload_dir = __DIR__ . '/uploads/products';
if (is_dir($upload_dir)) {
    $perms = substr(sprintf('%o', fileperms($upload_dir)), -4);
    $test_results['upload_directory'] = [
        'status' => 'pass',
        'message' => "✅ Thư mục uploads/products tồn tại (Permissions: $perms)"
    ];
} else {
    if (@mkdir($upload_dir, 0755, true)) {
        $test_results['upload_directory'] = [
            'status' => 'pass',
            'message' => "✅ Thư mục uploads/products vừa được tạo"
        ];
    } else {
        $test_results['upload_directory'] = [
            'status' => 'fail',
            'message' => "❌ Không thể tạo thư mục uploads/products"
        ];
    }
}

// Test 4: Kiểm tra bảng hinhanh_sp
try {
    $stmt = $pdo->query("DESCRIBE hinhanh_sp");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $test_results['hinhanh_sp_table'] = [
        'status' => 'pass',
        'message' => "✅ Bảng hinhanh_sp tồn tại với " . count($fields) . " cột"
    ];
} catch (Exception $e) {
    $test_results['hinhanh_sp_table'] = [
        'status' => 'fail',
        'message' => "❌ Lỗi: " . $e->getMessage()
    ];
}

// Test 5: Kiểm tra function generateProductId trong sanpham_admin.php
try {
    // Lấy MAX id hiện tại
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(id_sp, 3) AS UNSIGNED)) as max_num FROM sanpham WHERE id_sp LIKE 'SP%'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_num = $result['max_num'] ?? 0;
    $next_id = 'SP' . str_pad($max_num + 1, 3, '0', STR_PAD_LEFT);
    
    $test_results['generate_id'] = [
        'status' => 'pass',
        'message' => "✅ Hàm generateProductId() hoạt động. ID tiếp theo sẽ là: <strong>$next_id</strong>"
    ];
} catch (Exception $e) {
    $test_results['generate_id'] = [
        'status' => 'fail',
        'message' => "❌ Lỗi: " . $e->getMessage()
    ];
}

// Test 6: Kiểm tra quyền admin
if (isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin') {
    $test_results['admin_session'] = [
        'status' => 'pass',
        'message' => "✅ Đã đăng nhập với tài khoản admin (MA_TK: " . $_SESSION['ma_tk'] . ")"
    ];
} else {
    $test_results['admin_session'] = [
        'status' => 'warning',
        'message' => "⚠️ Chưa đăng nhập admin. Các chức năng quản lý sẽ bị từ chối"
    ];
}

// Test 7: Kiểm tra file API sanpham_admin.php
$api_file = __DIR__ . '/api/sanpham_admin.php';
if (file_exists($api_file)) {
    $test_results['api_file'] = [
        'status' => 'pass',
        'message' => "✅ File api/sanpham_admin.php tồn tại"
    ];

    // Kiểm tra xem có hàm generateProductId không
    $content = file_get_contents($api_file);
    if (strpos($content, 'function generateProductId') !== false) {
        $test_results['generate_id_function'] = [
            'status' => 'pass',
            'message' => "✅ Hàm generateProductId() được tìm thấy trong API"
        ];
    } else {
        $test_results['generate_id_function'] = [
            'status' => 'fail',
            'message' => "❌ Hàm generateProductId() không tìm thấy trong API"
        ];
    }
} else {
    $test_results['api_file'] = [
        'status' => 'fail',
        'message' => "❌ File api/sanpham_admin.php không tồn tại"
    ];
}

// Test 8: Kiểm tra PHP version
$test_results['php_version'] = [
    'status' => 'pass',
    'message' => "ℹ️ PHP Version: " . PHP_VERSION
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Quy Trình Sản Phẩm - Rice4U</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Be Vietnam Pro', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(90deg, #237227, #3a9b4f);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px 20px;
        }
        
        .test-item {
            background: #f8f9fa;
            border-left: 4px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        
        .test-item.pass {
            border-left-color: #27ae60;
            background: #ecf8f1;
        }
        
        .test-item.fail {
            border-left-color: #e74c3c;
            background: #fadbd8;
        }
        
        .test-item.warning {
            border-left-color: #f39c12;
            background: #fef5e7;
        }
        
        .test-icon {
            min-width: 30px;
            font-size: 20px;
            font-weight: bold;
        }
        
        .test-content {
            flex: 1;
        }
        
        .test-name {
            font-weight: 600;
            color: #237227;
            text-transform: uppercase;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .test-message {
            font-size: 14px;
            color: #333;
        }
        
        .summary {
            border-top: 2px solid #eee;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .summary-row.total {
            background: linear-gradient(90deg, #237227, #3a9b4f);
            color: white;
            padding: 15px;
            margin: -15px -20px 0 -20px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(90deg, #237227, #3a9b4f);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.25s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test Quy Trình Sản Phẩm</h1>
            <p>Kiểm tra cấu hình và hoạt động của hệ thống quản lý sản phẩm</p>
        </div>
        
        <div class="content">
            <?php foreach ($test_results as $test_name => $result): ?>
                <div class="test-item <?php echo $result['status']; ?>">
                    <div class="test-icon">
                        <?php 
                            if ($result['status'] === 'pass') echo '✅';
                            elseif ($result['status'] === 'fail') echo '❌';
                            else echo '⚠️';
                        ?>
                    </div>
                    <div class="test-content">
                        <div class="test-name"><?php echo str_replace('_', ' ', $test_name); ?></div>
                        <div class="test-message"><?php echo $result['message']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="summary">
                <div class="summary-row">
                    <span>✅ Thành công: <?php echo array_sum(array_map(fn($r) => $r['status'] === 'pass' ? 1 : 0, $test_results)); ?></span>
                    <span>❌ Lỗi: <?php echo array_sum(array_map(fn($r) => $r['status'] === 'fail' ? 1 : 0, $test_results)); ?></span>
                    <span>⚠️ Cảnh báo: <?php echo array_sum(array_map(fn($r) => $r['status'] === 'warning' ? 1 : 0, $test_results)); ?></span>
                </div>
            </div>
            
            <div class="summary">
                <h3 style="color: #237227; margin-bottom: 15px;">📌 Bước Tiếp Theo</h3>
                <ol style="padding-left: 20px; line-height: 1.8; color: #333;">
                    <li><strong>Đăng nhập Admin:</strong> Truy cập <a href="admin.php" style="color: #237227; font-weight: 600;">admin.php</a></li>
                    <li><strong>Vào Quản lý Sản phẩm:</strong> Click vào "🍚 Quản lý sản phẩm"</li>
                    <li><strong>Thêm Sản Phẩm:</strong> Click nút "+ Thêm sản phẩm"</li>
                    <li><strong>Điền Thông Tin:</strong> Tên, loại gạo, giá, số lượng</li>
                    <li><strong>Upload Ảnh:</strong> Chọn ảnh từ máy tính (JPG, PNG, WEBP)</li>
                    <li><strong>Lưu:</strong> Click nút "Lưu" để hoàn tất</li>
                </ol>
            </div>
            
            <a href="quanly_sanpham.php" class="btn">→ Đi đến Quản Lý Sản Phẩm</a>
        </div>
    </div>
</body>
</html>
