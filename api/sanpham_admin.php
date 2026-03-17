<?php
/**
 * API Quản lý sản phẩm - rice4u
 * File: api/sanpham_admin.php
 * Xử lý: add | update | delete | get
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Kiểm tra quyền admin ──
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit();
}

require_once dirname(__DIR__) . '/includes/db.php';

$action = $_GET['action'] ?? '';

// ── Router ──
switch ($action) {
    case 'add':    handleAdd($pdo);    break;
    case 'update': handleUpdate($pdo); break;
    case 'delete': handleDelete($pdo); break;
    case 'get':    handleGet($pdo);    break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

// ════════════════════════════════════════════
// THÊM SẢN PHẨM
// ════════════════════════════════════════════
function handleAdd(PDO $pdo) {
    // Validate bắt buộc
    $ten_sp  = trim($_POST['ten_sp']  ?? '');
    $id_loai = trim($_POST['id_loai'] ?? '');
    $gia_ban = trim($_POST['gia_ban'] ?? '');

    if (!$ten_sp || !$id_loai || $gia_ban === '') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ: Tên sản phẩm, Loại gạo, Giá bán']);
        return;
    }

    // Làm sạch dữ liệu
    $xuat_xu      = trim($_POST['xuat_xu']      ?? '');
    $mo_ta_ngan   = trim($_POST['mo_ta_ngan']   ?? '');
    $mo_ta_chi_tiet = trim($_POST['mo_ta_chi_tiet'] ?? '');
    $gia_goc      = $_POST['gia_goc']  !== '' ? (float)$_POST['gia_goc']  : null;
    $gia_ban      = (float)$gia_ban;
    $so_luong_ton = (float)($_POST['so_luong_ton'] ?? 0);
    $noi_bat      = isset($_POST['noi_bat'])   ? (int)$_POST['noi_bat']   : 0;
    $hang_moi     = isset($_POST['hang_moi'])  ? (int)$_POST['hang_moi']  : 0;
    $ban_chay     = isset($_POST['ban_chay'])  ? (int)$_POST['ban_chay']  : 0;
    $trang_thai   = isset($_POST['trang_thai']) ? (int)$_POST['trang_thai'] : 1;

    // Tính % giảm giá
    $phan_tram_giam = 0;
    if ($gia_goc && $gia_goc > $gia_ban) {
        $phan_tram_giam = (int)round(($gia_goc - $gia_ban) / $gia_goc * 100);
    }

    // Tạo ID sản phẩm tự động
    $id_sp = generateProductId($pdo);

    // Tạo slug từ tên
    $slug = createSlug($ten_sp);
    $slug = ensureUniqueSlug($pdo, $slug);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO sanpham 
                (id_sp, ten_sp, id_loai, xuat_xu, mo_ta_ngan, mo_ta_chi_tiet,
                 gia_ban, gia_goc, phan_tram_giam, so_luong_ton,
                 noi_bat, hang_moi, ban_chay, trang_thai, slug, ngay_tao, ngay_cap_nhat)
            VALUES 
                (?, ?, ?, ?, ?, ?,
                 ?, ?, ?, ?,
                 ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $id_sp, $ten_sp, $id_loai, $xuat_xu, $mo_ta_ngan, $mo_ta_chi_tiet,
            $gia_ban, $gia_goc, $phan_tram_giam, $so_luong_ton,
            $noi_bat, $hang_moi, $ban_chay, $trang_thai, $slug
        ]);

        // Xử lý upload ảnh
        if (!empty($_FILES['hinh_anh']['name'][0])) {
            uploadImages($pdo, $id_sp, $_FILES['hinh_anh']);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => "✅ Đã thêm sản phẩm <strong>$ten_sp</strong> (ID: $id_sp) thành công!",
            'id_sp'   => $id_sp
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("handleAdd error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi database: ' . $e->getMessage()
        ]);
    }
}

// ════════════════════════════════════════════
// SỬA SẢN PHẨM
// ════════════════════════════════════════════
function handleUpdate(PDO $pdo) {
    $id_sp   = trim($_POST['id_sp']   ?? '');
    $ten_sp  = trim($_POST['ten_sp']  ?? '');
    $id_loai = trim($_POST['id_loai'] ?? '');
    $gia_ban = trim($_POST['gia_ban'] ?? '');

    if (!$id_sp || !$ten_sp || !$id_loai || $gia_ban === '') {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc: ID, Tên, Loại, Giá']);
        return;
    }

    // Kiểm tra sản phẩm tồn tại
    $check = $pdo->prepare("SELECT id_sp FROM sanpham WHERE id_sp = ?");
    $check->execute([$id_sp]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => "Sản phẩm ID '$id_sp' không tồn tại"]);
        return;
    }

    $xuat_xu        = trim($_POST['xuat_xu']        ?? '');
    $mo_ta_ngan     = trim($_POST['mo_ta_ngan']     ?? '');
    $mo_ta_chi_tiet = trim($_POST['mo_ta_chi_tiet'] ?? '');
    $gia_goc        = (isset($_POST['gia_goc']) && $_POST['gia_goc'] !== '') ? (float)$_POST['gia_goc'] : null;
    $gia_ban        = (float)$gia_ban;
    $so_luong_ton   = (float)($_POST['so_luong_ton'] ?? 0);
    $noi_bat        = (int)($_POST['noi_bat']   ?? 0);
    $hang_moi       = (int)($_POST['hang_moi']  ?? 0);
    $ban_chay       = (int)($_POST['ban_chay']  ?? 0);
    $trang_thai     = (int)($_POST['trang_thai'] ?? 1);

    // Tính % giảm giá
    $phan_tram_giam = 0;
    if ($gia_goc && $gia_goc > $gia_ban) {
        $phan_tram_giam = (int)round(($gia_goc - $gia_ban) / $gia_goc * 100);
    }

    // Cập nhật slug nếu tên thay đổi
    $slug = createSlug($ten_sp);
    $slug = ensureUniqueSlug($pdo, $slug, $id_sp);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE sanpham SET
                ten_sp          = ?,
                id_loai         = ?,
                xuat_xu         = ?,
                mo_ta_ngan      = ?,
                mo_ta_chi_tiet  = ?,
                gia_ban         = ?,
                gia_goc         = ?,
                phan_tram_giam  = ?,
                so_luong_ton    = ?,
                noi_bat         = ?,
                hang_moi        = ?,
                ban_chay        = ?,
                trang_thai      = ?,
                slug            = ?,
                ngay_cap_nhat   = NOW()
            WHERE id_sp = ?
        ");

        $stmt->execute([
            $ten_sp, $id_loai, $xuat_xu, $mo_ta_ngan, $mo_ta_chi_tiet,
            $gia_ban, $gia_goc, $phan_tram_giam, $so_luong_ton,
            $noi_bat, $hang_moi, $ban_chay, $trang_thai, $slug,
            $id_sp
        ]);

        // Xử lý upload ảnh mới (nếu có)
        if (!empty($_FILES['hinh_anh']['name'][0])) {
            uploadImages($pdo, $id_sp, $_FILES['hinh_anh'], true);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => "✅ Đã cập nhật sản phẩm <strong>$ten_sp</strong> thành công!"
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("handleUpdate error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi database: ' . $e->getMessage()
        ]);
    }
}

// ════════════════════════════════════════════
// XÓA SẢN PHẨM
// ════════════════════════════════════════════
function handleDelete(PDO $pdo) {
    $id_sp = trim($_POST['id_sp'] ?? '');

    if (!$id_sp) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ID sản phẩm']);
        return;
    }

    // Kiểm tra sản phẩm có trong đơn hàng không
    $check_order = $pdo->prepare("SELECT COUNT(*) FROM chitiet_donhang WHERE id_sp = ?");
    $check_order->execute([$id_sp]);
    if ($check_order->fetchColumn() > 0) {
        // Không xóa cứng — chỉ ẩn (soft delete)
        $stmt = $pdo->prepare("UPDATE sanpham SET trang_thai = 0, ngay_cap_nhat = NOW() WHERE id_sp = ?");
        $stmt->execute([$id_sp]);
        echo json_encode([
            'success' => true,
            'message' => "⚠️ Sản phẩm đã có trong đơn hàng nên được ẩn đi thay vì xóa hoàn toàn."
        ]);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Xóa ảnh vật lý
        $stmt_imgs = $pdo->prepare("SELECT duong_dan FROM hinhanh_sp WHERE id_sp = ?");
        $stmt_imgs->execute([$id_sp]);
        $imgs = $stmt_imgs->fetchAll(PDO::FETCH_COLUMN);
        foreach ($imgs as $img_path) {
            $full_path = dirname(__DIR__) . '/' . ltrim($img_path, '/');
            if (file_exists($full_path)) {
                @unlink($full_path);
            }
        }

        // Xóa trong DB (FK cascade sẽ xóa hinhanh_sp, gia_quy_cach)
        $stmt = $pdo->prepare("DELETE FROM sanpham WHERE id_sp = ?");
        $stmt->execute([$id_sp]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => "✅ Đã xóa sản phẩm <strong>$id_sp</strong> thành công!"
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("handleDelete error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa: ' . $e->getMessage()
        ]);
    }
}

// ════════════════════════════════════════════
// LẤY THÔNG TIN SẢN PHẨM (cho modal sửa)
// ════════════════════════════════════════════
function handleGet(PDO $pdo) {
    $id_sp = trim($_GET['id_sp'] ?? '');
    if (!$id_sp) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM sanpham WHERE id_sp = ?");
    $stmt->execute([$id_sp]);
    $sp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sp) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
        return;
    }

    // Lấy ảnh
    $stmt_imgs = $pdo->prepare("SELECT duong_dan, la_anh_chinh FROM hinhanh_sp WHERE id_sp = ? ORDER BY la_anh_chinh DESC, id_anh ASC");
    $stmt_imgs->execute([$id_sp]);
    $sp['hinh_anh_list'] = $stmt_imgs->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $sp]);
}

// ════════════════════════════════════════════
// HÀM HỖ TRỢ
// ════════════════════════════════════════════

function generateProductId(PDO $pdo): string {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(id_sp, 3) AS UNSIGNED)) as max_num FROM sanpham WHERE id_sp REGEXP '^SP[0-9]+$'");
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    $next = ($row['max_num'] ?? 0) + 1;
    return 'SP' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

function createSlug(string $text): string {
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map  = [
        'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a',
        'ă'=>'a','ắ'=>'a','ặ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a',
        'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e',
        'ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
        'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o',
        'ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o',
        'ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
        'ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
        'đ'=>'d',
    ];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function ensureUniqueSlug(PDO $pdo, string $slug, string $exclude_id = ''): string {
    $base   = $slug;
    $i      = 1;
    $params = [$slug];
    $sql    = "SELECT COUNT(*) FROM sanpham WHERE slug = ?";
    if ($exclude_id) {
        $sql .= " AND id_sp != ?";
        $params[] = $exclude_id;
    }
    while (true) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetchColumn() == 0) break;
        $slug     = $base . '-' . $i++;
        $params[0] = $slug;
    }
    return $slug;
}

function uploadImages(PDO $pdo, string $id_sp, array $files, bool $replace_main = false): void {
    $upload_dir = dirname(__DIR__) . '/uploads/products/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $is_first      = true;

    // Nếu update và replace_main = true, bỏ đánh dấu ảnh chính cũ
    if ($replace_main) {
        $pdo->prepare("UPDATE hinhanh_sp SET la_anh_chinh = 0 WHERE id_sp = ?")->execute([$id_sp]);
    }

    // Kiểm tra có ảnh chính chưa
    $has_main = $pdo->prepare("SELECT COUNT(*) FROM hinhanh_sp WHERE id_sp = ? AND la_anh_chinh = 1");
    $has_main->execute([$id_sp]);
    $main_exists = (bool)$has_main->fetchColumn();

    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        if (!in_array($files['type'][$i], $allowed_types))  continue;
        if ($files['size'][$i] > 5 * 1024 * 1024)          continue; // Max 5MB

        $ext      = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        $filename = $id_sp . '_' . time() . '_' . $i . '.' . strtolower($ext);
        $dest     = $upload_dir . $filename;

        if (!move_uploaded_file($files['tmp_name'][$i], $dest)) continue;

        $la_anh_chinh = (!$main_exists && $is_first) ? 1 : 0;

        $stmt = $pdo->prepare("
            INSERT INTO hinhanh_sp (id_sp, duong_dan, alt_text, la_anh_chinh, thu_tu)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_sp,
            'uploads/products/' . $filename,
            $id_sp,
            $la_anh_chinh,
            $i + 1
        ]);

        if ($la_anh_chinh) $main_exists = true;
        $is_first = false;
    }
}