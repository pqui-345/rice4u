<?php
// Bật error reporting để debug
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db.php';

session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Tạo thư mục uploads nếu chưa tồn tại
$upload_dir = __DIR__ . '/../uploads/products';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$action = $_GET['action'] ?? null;

try {
    if ($action === 'add') {
        addProduct();
    } elseif ($action === 'update') {
        updateProduct();
    } elseif ($action === 'delete') {
        deleteProduct();
    } else {
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

/**
 * Tạo ID sản phẩm mới (SP001, SP002, ...)
 */
function generateProductId() {
    global $pdo;
    
    try {
        // Lấy id_sp lớn nhất hiện tại
        $sql = "SELECT MAX(CAST(SUBSTRING(id_sp, 3) AS UNSIGNED)) as max_num FROM sanpham WHERE id_sp LIKE 'SP%'";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $max_num = $result['max_num'] ?? 0;
        $new_num = $max_num + 1;
        
        // Format thành SPxxx (ví dụ SP001, SP002, ...)
        return 'SP' . str_pad($new_num, 3, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        throw new Exception('Lỗi tạo ID sản phẩm: ' . $e->getMessage());
    }
}

/**
 * Upload và lưu hình ảnh sản phẩm
 */
function handleProductImages($id_sp, $delete_old = false) {
    global $pdo, $upload_dir;
    
    // Xóa ảnh cũ nếu cần (lấy danh sách trước khi DELETE)
    if ($delete_old) {
        // Lấy danh sách ảnh cũ
        $old_images = $pdo->prepare("SELECT duong_dan FROM hinhanh_sp WHERE id_sp = ?");
        $old_images->execute([$id_sp]);
        $old_image_list = $old_images->fetchAll(PDO::FETCH_ASSOC);
        
        // Xóa file từ thư mục
        foreach ($old_image_list as $img) {
            $file_path = __DIR__ . '/../' . $img['duong_dan'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        
        // Sau đó xóa từ database
        $sql = "DELETE FROM hinhanh_sp WHERE id_sp = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_sp]);
    }
    
    if (!isset($_FILES['hinh_anh']) || empty($_FILES['hinh_anh']['name'][0])) {
        return true;
    }

    $files = $_FILES['hinh_anh'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $is_main = true;

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $file_name = $files['name'][$i];
        $file_type = $files['type'][$i];
        $file_tmp = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];

        // Validate
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Loại file không hỗ trợ. Chỉ chấp nhận JPG, PNG, WEBP');
        }

        if ($file_size > $max_size) {
            throw new Exception('File quá lớn. Tối đa 5MB');
        }

        // Tạo tên file duy nhất
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_id = uniqid('', true);
        $new_filename = 'product_' . $id_sp . '_' . $unique_id . '.' . $file_ext;
        $upload_path = $upload_dir . '/' . $new_filename;
        $db_path = 'uploads/products/' . $new_filename;

        // Upload file
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            throw new Exception('Lỗi upload file');
        }

        // Lưu vào database (id_anh tự động tăng)
        $sql = "INSERT INTO hinhanh_sp (id_sp, duong_dan, alt_text, la_anh_chinh) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $result = $stmt->execute([
                $id_sp,
                $db_path,
                pathinfo($file_name, PATHINFO_FILENAME),
                $is_main ? 1 : 0
            ]);
            
            if (!$result) {
                $error = $stmt->errorInfo();
                throw new Exception('Lỗi insert: ' . $error[2]);
            }
        } catch (PDOException $e) {
            // Nếu lỗi insert, xóa file đã upload
            @unlink($upload_path);
            throw new Exception('Lỗi lưu hình ảnh - ' . $e->getMessage());
        }

        $is_main = false;
    }

    return true;
}

function addProduct() {
    global $pdo;
    
    $ten_sp = trim($_POST['ten_sp'] ?? '');
    $id_loai = $_POST['id_loai'] ?? null;
    $xuat_xu = trim($_POST['xuat_xu'] ?? '');
    $gia_ban = (float)($_POST['gia_ban'] ?? 0);
    $gia_goc = (float)($_POST['gia_goc'] ?? 0);
    $phan_tram_giam = (int)($_POST['phan_tram_giam'] ?? 0);
    $so_luong_ton = (int)($_POST['so_luong_ton'] ?? 0);
    $mo_ta_ngan = trim($_POST['mo_ta_ngan'] ?? '');
    $noi_bat = (int)($_POST['noi_bat'] ?? 0);
    $hang_moi = (int)($_POST['hang_moi'] ?? 0);
    $trang_thai = (int)($_POST['trang_thai'] ?? 1);

    // Validate
    if (empty($ten_sp) || empty($id_loai) || $gia_ban <= 0) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
        return;
    }

    try {
        // Tạo string tìm kiếm slug từ tên sản phẩm
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $ten_sp), '-'));
        
        // Generate ID sản phẩm mới
        $id_sp = generateProductId();
        
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO sanpham 
                (id_sp, ten_sp, id_loai, xuat_xu, gia_ban, gia_goc, phan_tram_giam, 
                 so_luong_ton, mo_ta_ngan, noi_bat, hang_moi, trang_thai, slug, ngay_tao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $id_sp, $ten_sp, $id_loai, $xuat_xu, $gia_ban, $gia_goc, 
            $phan_tram_giam, $so_luong_ton, $mo_ta_ngan, 
            $noi_bat, $hang_moi, $trang_thai, $slug
        ]);

        if (!$result) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Lỗi thêm sản phẩm']);
            return;
        }
        
        // Upload hình ảnh
        if (isset($_FILES['hinh_anh']) && !empty($_FILES['hinh_anh']['name'][0])) {
            handleProductImages($id_sp);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm ' . $id_sp . ' thành công']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function updateProduct() {
    global $pdo;
    
    $id_sp = trim($_POST['id_sp'] ?? '');
    $ten_sp = trim($_POST['ten_sp'] ?? '');
    $id_loai = $_POST['id_loai'] ?? null;
    $xuat_xu = trim($_POST['xuat_xu'] ?? '');
    $gia_ban = (float)($_POST['gia_ban'] ?? 0);
    $gia_goc = (float)($_POST['gia_goc'] ?? 0);
    $phan_tram_giam = (int)($_POST['phan_tram_giam'] ?? 0);
    $so_luong_ton = (int)($_POST['so_luong_ton'] ?? 0);
    $mo_ta_ngan = trim($_POST['mo_ta_ngan'] ?? '');
    $noi_bat = (int)($_POST['noi_bat'] ?? 0);
    $hang_moi = (int)($_POST['hang_moi'] ?? 0);
    $trang_thai = (int)($_POST['trang_thai'] ?? 1);

    // Validate
    if (empty($id_sp) || empty($ten_sp) || empty($id_loai) || $gia_ban <= 0) {
        echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ']);
        return;
    }

    try {
        // Tạo slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $ten_sp), '-'));
        
        $sql = "UPDATE sanpham 
                SET ten_sp = ?, id_loai = ?, xuat_xu = ?, gia_ban = ?, gia_goc = ?, 
                    phan_tram_giam = ?, so_luong_ton = ?, mo_ta_ngan = ?, 
                    noi_bat = ?, hang_moi = ?, trang_thai = ?, slug = ? 
                WHERE id_sp = ?";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $ten_sp, $id_loai, $xuat_xu, $gia_ban, $gia_goc, 
            $phan_tram_giam, $so_luong_ton, $mo_ta_ngan, 
            $noi_bat, $hang_moi, $trang_thai, $slug, $id_sp
        ]);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật sản phẩm']);
            return;
        }
        
        // Upload hình ảnh mới nếu có
        if (isset($_FILES['hinh_anh']) && !empty($_FILES['hinh_anh']['name'][0])) {
            handleProductImages($id_sp, true);
        }
        
        echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function deleteProduct() {
    global $pdo, $upload_dir;
    
    $id_sp = trim($_POST['id_sp'] ?? '');

    if (empty($id_sp)) {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
        return;
    }

    try {
        // Kiểm tra có đơn hàng nào tham chiếu không
        $check_sql = "SELECT COUNT(*) FROM chitiet_donhang WHERE id_sp = ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$id_sp]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Có đơn hàng tham chiếu, chỉ ẩn sản phẩm (soft delete)
            $sql = "UPDATE sanpham SET trang_thai = 0 WHERE id_sp = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$id_sp]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được ẩn (vì có đơn hàng tham chiếu)']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi ẩn sản phẩm']);
            }
        } else {
            // Không có tham chiếu, xóa hoàn toàn (hard delete)
            $pdo->beginTransaction();
            
            try {
                // Xóa hình ảnh từ disk trước
                $old_images = $pdo->prepare("SELECT duong_dan FROM hinhanh_sp WHERE id_sp = ?");
                $old_images->execute([$id_sp]);
                $old_image_list = $old_images->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($old_image_list as $img) {
                    $file_path = __DIR__ . '/../' . $img['duong_dan'];
                    if (file_exists($file_path)) {
                        @unlink($file_path);
                    }
                }
                
                // Xóa hình ảnh từ database
                $del_img = "DELETE FROM hinhanh_sp WHERE id_sp = ?";
                $stmt = $pdo->prepare($del_img);
                $stmt->execute([$id_sp]);

                // Xóa quy cách giá
                $del_qc = "DELETE FROM gia_quy_cach WHERE id_sp = ?";
                $stmt = $pdo->prepare($del_qc);
                $stmt->execute([$id_sp]);

                // Xóa sản phẩm
                $sql = "DELETE FROM sanpham WHERE id_sp = ?";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$id_sp]);

                if ($result) {
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
                } else {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Lỗi xóa sản phẩm']);
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}
?>
