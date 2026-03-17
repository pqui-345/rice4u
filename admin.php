<?php
session_start();
// Chặn nếu chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: dangnhap.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Rice4u</title>
    <style>
         @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
        body{
        font-family:'Be Vietnam Pro', sans-serif;
        margin:0;
        background:#f6f9f6;
        }

        .sidebar{
        width:230px;
        height:100vh;
        background:var(--green-dark);
        position:fixed;
        color: #237227;
        padding-top:10px;
        box-shadow:3px 0 10px rgba(0,0,0,0.08);
        }

        .sidebar h2{
        text-align:center;
        padding:20px 0;
        font-family:'Playfair Display', serif;
        letter-spacing:1px;
        }

        .sidebar a{
        display:block;
        padding:12px 20px;
        color: #237227;
        text-decoration:none;
        font-size:14px;
        transition:all 0.25s;
        border-left:3px solid transparent;
        }

        .sidebar a:hover{
        background:rgba(255,255,255,0.1);
        border-left:3px solid var(--amber);
        }

        .content{
        margin-left:230px;
        padding:25px;
        }

        .logo{
            text-align:center;
            padding:15px 10px;
        }

        .logo img{
            width:150px;
            height:auto;
        }

        .header{
        background:linear-gradient(90deg,var(--green-dark),var(--green-mid));
        color: #237227;
        padding:16px 20px;
        border-radius:8px;
        font-weight:600;
        margin-bottom:20px;
        box-shadow:0 3px 10px rgba(0,0,0,0.08);
        }

        .content h2, p{
        margin-top:10px;
        color: #237227;
        }

        .banner{
        margin-top:20px;
        }

        .banner img{
        width:100%;
        height:260px;
        object-fit:cover;
        border-radius:10px;
        box-shadow:0 6px 20px rgba(0,0,0,0.08);
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/images/logo.png" alt="">
        </div>
        <a href="dashboard.php">Dashboard</a>
        <a href="quanlydonhang.php">Quản lý đơn hàng</a>
        <a href="quanly_sanpham.php">Quản lý sản phẩm</a>
        <a href="admin_account.php">Quản lý tài khoản</a>
        <a href="#">Quản lý loại gạo</a>
        <a href="dangxuat.php">Đăng xuất</a>
    </div>

    <div class="content">

        <div class="header">
            Chào mừng Admin
        </div>

        <h2>Chào mừng đến với trang quản trị hệ thống cửa hàng bán gạo Rice4U</h2>
        <p>Chọn chức năng bên trái để quản lý.</p>

        <div class="banner">
            <img src="asset/images/banner.png" alt="Banner" />
        </div>

    </div>

</body>
</html>