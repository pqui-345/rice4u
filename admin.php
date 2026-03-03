<?php
session_start();
// Chặn nếu chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: dangnhap.php");
    exit();
}
echo "<h1>Chào mừng đến Trang Quản Trị</h1>";
// Code quản lý tài khoản ở đây...
?>
