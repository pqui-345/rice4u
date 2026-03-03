<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// Kiểm tra bảo mật: Phải đăng nhập và là Admin
if (!isset($_SESSION['ID_TK']) || $_SESSION['vai_tro'] !== 'admin') {
    die("Bạn không có quyền truy cập trang này!");
}

// Lấy danh sách kết hợp 2 bảng
$sql = "SELECT TK.ID_TK, TK.ten_dang_nhap, TK.vai_tro, KH.ten_kh, KH.sdt 
        FROM TAI_KHOAN TK 
        LEFT JOIN KHACHHANG KH ON TK.ID_TK = KH.ID_TK";
$stmt = $pdo->query($sql);
$danh_sach = $stmt->fetchAll();
?>

<h2>Trang Quản Trị Hệ Thống</h2>
<table border="1" cellpadding="10" style="border-collapse: collapse;">
    <tr style="background:#ddd;">
        <th>ID_TK</th>
        <th>Tên đăng nhập</th>
        <th>Họ tên khách</th>
        <th>Số điện thoại</th>
        <th>Vai trò</th>
    </tr>
    <?php foreach ($danh_sach as $row): ?>
    <tr>
        <td><?= $row['ID_TK'] ?></td>
        <td><?= $row['ten_dang_nhap'] ?></td>
        <td><?= $row['ten_kh'] ?? 'N/A' ?></td>
        <td><?= $row['sdt'] ?? 'N/A' ?></td>
        <td><?= $row['vai_tro'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<br>
<a href="dangxuat.php">Đăng xuất</a>
