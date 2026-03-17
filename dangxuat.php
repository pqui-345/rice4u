<?php
// 1. Khởi động session để nhận diện người dùng hiện tại
session_start();

// 2. Xóa toàn bộ dữ liệu lưu trong session (ví dụ: ma_tk, họ tên...)
$_SESSION = array();

// 3. Hủy hoàn toàn phiên làm việc (session)
session_destroy();



// 4. Chuyển hướng người dùng về trang đăng nhập (hoặc trang chủ)
header("Location: dangnhap.php"); // Nếu trang đăng nhập của bạn tên khác, hãy sửa lại chỗ này nhé
exit();
?>