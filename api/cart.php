<?php
// ============================================================
// api/giohang.php — Xử lý giỏ hàng qua SESSION
// Nhận POST request từ các trang, trả về JSON
// ============================================================
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

function normalizeImagePath($path) {
    $default = '/rice4u/.vscode/asset/images/default.jpg';
    if (empty($path)) return $default;

    $path = trim((string)$path);
    if (preg_match('#^https?://#i', $path)) return $path;

    $normalized = str_replace('\\', '/', ltrim($path, '/'));
    $candidates = [$normalized];
    if (strpos($normalized, '.vscode/asset/') !== 0) {
        $candidates[] = '.vscode/asset/' . $normalized;
    }

    foreach ($candidates as $candidate) {
        $full = dirname(__DIR__) . '/' . ltrim($candidate, '/');
        if (is_file($full)) {
            return '/rice4u/' . ltrim($candidate, '/');
        }
    }

    return $default;
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['gio_hang'])) {
    $_SESSION['gio_hang'] = [];
}

$action = $_POST['action'] ?? 'add';
$id_sp  = trim($_POST['id_sp'] ?? '');

// ── THÊM VÀO GIỎ ──
if ($action === 'add' && $id_sp) {
    $so_luong = max(1, (int)($_POST['so_luong'] ?? 1));

    // Lấy thông tin sản phẩm từ DB
    $stmt = $pdo->prepare("
        SELECT sp.id_sp, sp.ten_sp, sp.gia_ban, sp.so_luong_ton,
               ha.duong_dan AS hinh
        FROM sanpham sp
        LEFT JOIN hinhanh_sp ha ON sp.id_sp = ha.id_sp AND ha.la_anh_chinh = 1
        WHERE sp.id_sp = ? AND sp.trang_thai = 1
        LIMIT 1
    ");
    $stmt->execute([$id_sp]);
    $sp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sp) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    // Nếu đã có trong giỏ → cộng thêm số lượng
    if (isset($_SESSION['gio_hang'][$id_sp])) {
        $moi = $_SESSION['gio_hang'][$id_sp]['so_luong'] + $so_luong;
        // Không vượt tồn kho
        $_SESSION['gio_hang'][$id_sp]['so_luong'] = min($moi, (int)$sp['so_luong_ton']);
    } else {
        $_SESSION['gio_hang'][$id_sp] = [
            'id_sp'    => $sp['id_sp'],
            'ten_sp'   => $sp['ten_sp'],
            'gia_ban'  => (float)$sp['gia_ban'],
            'so_luong' => $so_luong,
            'hinh'     => normalizeImagePath($sp['hinh'] ?? null),
            'ton_kho'  => (int)$sp['so_luong_ton'],
        ];
    }
}

// ── CẬP NHẬT SỐ LƯỢNG ──
elseif ($action === 'update' && $id_sp) {
    $so_luong = (int)($_POST['so_luong'] ?? 1);
    if ($so_luong <= 0) {
        unset($_SESSION['gio_hang'][$id_sp]);
    } elseif (isset($_SESSION['gio_hang'][$id_sp])) {
        $_SESSION['gio_hang'][$id_sp]['so_luong'] = min($so_luong, $_SESSION['gio_hang'][$id_sp]['ton_kho']);
    }
}

// ── XÓA 1 SẢN PHẨM ──
elseif ($action === 'remove' && $id_sp) {
    unset($_SESSION['gio_hang'][$id_sp]);
}

// ── XÓA TOÀN BỘ GIỎ ──
elseif ($action === 'clear') {
    $_SESSION['gio_hang'] = [];
}

// ── Tính tổng để trả về ──
$total_items = array_sum(array_column($_SESSION['gio_hang'], 'so_luong'));
$total_price = array_sum(array_map(fn($i) => $i['gia_ban'] * $i['so_luong'], $_SESSION['gio_hang']));

echo json_encode([
    'success'     => true,
    'total_items' => $total_items,
    'total_price' => $total_price,
    'cart'        => array_values($_SESSION['gio_hang']),
]);
