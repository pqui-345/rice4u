<?php
session_start();
require 'db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['ma_tk'])) {
    header("Location: dangnhap.php");
    exit();
}

$ma_tk = $_SESSION['ma_tk'];
$thong_bao = '';
$loai_thong_bao = '';

// 1. XỬ LÝ CẬP NHẬT THÔNG TIN TRƯỚC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_kh = trim($_POST['ten_kh']);
    $sdt = trim($_POST['sdt']);
    
    try {
        $updateStmt = $pdo->prepare("UPDATE KHACH_HANG SET ten_kh = ?, sdt = ? WHERE ma_tk = ?");
        $updateStmt->execute([$ten_kh, $sdt, $ma_tk]);
        
        $thong_bao = "Cập nhật thông tin thành công!";
        $loai_thong_bao = "success";
    } catch (PDOException $e) {
        $thong_bao = "Lỗi cập nhật: " . $e->getMessage();
        $loai_thong_bao = "error";
    }
}

// 2. LẤY THÔNG TIN ĐỂ HIỂN THỊ (Lấy sau khi cập nhật để có data mới nhất)
$stmt = $pdo->prepare("SELECT * FROM KHACH_HANG WHERE ma_tk = ?");
$stmt->execute([$ma_tk]);
$khach_hang = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy khách hàng (lỗi data), khởi tạo mảng rỗng để tránh lỗi giao diện
if (!$khach_hang) {
    $khach_hang = ['ten_kh' => '', 'sdt' => ''];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân - Cửa Hàng Gạo Rice4U</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('https://images.unsplash.com/photo-1586201375761-83865001e8ac?q=80&w=1920&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
        }
        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5); /* Nền tối hơn 1 chút để nổi bật form */
            z-index: 1;
        }
        .profile-container {
            position: relative;
            z-index: 2;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            margin: 20px;
        }
        .avatar-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .avatar-circle {
            width: 80px;
            height: 80px;
            background-color: #e8f5e9;
            color: #2e7d32;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 32px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(46, 125, 50, 0.2);
        }
        .profile-container h2 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 5px;
            font-size: 24px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #fcfcfc;
        }
        .form-group input:focus {
            border-color: #2e7d32;
            outline: none;
            box-shadow: 0 0 8px rgba(46, 125, 50, 0.2);
            background-color: #fff;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #1b5e20;
        }
        .thong-bao {
            text-align: center;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            display: <?php echo empty($thong_bao) ? 'none' : 'block'; ?>;
        }
        .thong-bao.success { color: #1b5e20; background: #e8f5e9; border: 1px solid #c8e6c9; }
        .thong-bao.error { color: #d32f2f; background: #ffebee; border: 1px solid #ffcdd2; }
        
        .action-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 14px;
        }
        .action-links a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }
        .action-links a.logout {
            color: #d32f2f;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="profile-container">
        
        <div class="avatar-section">
            <div class="avatar-circle">
                <?php echo strtoupper(mb_substr($khach_hang['ten_kh'], 0, 1, 'UTF-8')); ?>
            </div>
        </div>

        <h2>Hồ Sơ Của Bạn</h2>
        <p class="subtitle">Quản lý thông tin cá nhân</p>
        
        <div class="thong-bao <?php echo $loai_thong_bao; ?>">
            <?php echo $thong_bao; ?>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="ten_kh">Họ và Tên</label>
                <input type="text" id="ten_kh" name="ten_kh" value="<?php echo htmlspecialchars($khach_hang['ten_kh']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="sdt">Số điện thoại</label>
                <input type="text" id="sdt" name="sdt" value="<?php echo htmlspecialchars($khach_hang['sdt']); ?>" required>
            </div>
            
            <button type="submit" class="btn-submit">Lưu Thay Đổi</button>
        </form>
        
        <div class="action-links">
            <a href="trangchu.php">⬅ Về Trang chủ</a>
            <a href="dangxuat.php" class="logout">Đăng xuất</a>
        </div>
    </div>
</body>
</html>

