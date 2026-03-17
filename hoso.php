<?php
session_start();
require './includes/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['ma_tk'])) {
    header("Location: dangnhap.php");
    exit();
}

$ma_tk = $_SESSION['ma_tk'];
$thong_bao = '';
$loai_thong_bao = '';

// Lấy điểm tích lũy của khách hàng (nếu không có thì mặc định là 0)
$diem_tich_luy = $khach_hang['diem_tich_luy'] ?? 0;

// Xếp hạng thành viên dựa trên điểm
$ten_hang = 'Thành viên';
$mau_hang = '#555555'; // Màu xám mặc định

if ($diem_tich_luy >= 1000) {
    $ten_hang = 'Hạng Vàng';
    $mau_hang = '#efc65e'; // Màu vàng đậm
} elseif ($diem_tich_luy >= 500) {
    $ten_hang = 'Hạng Bạc';
    $mau_hang = '#C0C0C0'; // Màu bạc
} elseif ($diem_tich_luy >= 100) {
    $ten_hang = 'Hạng Đồng';
    $mau_hang = '#CD7F32'; // Màu đồng
}

// 1. XỬ LÝ CÁC FORM POST ĐƯỢC GỬI LÊN
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // TRƯỜNG HỢP 1: LÀ FORM UPLOAD ẢNH (Chỉ chạy khi có file ảnh được gửi lên)
    if (isset($_FILES['anh_dai_dien'])) {
        $file = $_FILES['anh_dai_dien'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_ext)) {
                $thong_bao = "Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF).";
                $loai_thong_bao = "error";
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $thong_bao = "Dung lượng tối đa 2MB.";
                $loai_thong_bao = "error";
            } else {
                $new_file_name = 'avatar_' . $ma_tk . '_' . time() . '.' . $file_ext;
                $upload_dir = 'uploads/avatars/'; 
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $upload_path = $upload_dir . $new_file_name;
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $db_path = '/rice4u/uploads/avatars/' . $new_file_name;
                    try {
                        $updateImgStmt = $pdo->prepare("UPDATE khachhang SET anh_dai_dien = ? WHERE ma_tk = ?");
                        $updateImgStmt->execute([$db_path, $ma_tk]);
                        $thong_bao = "Cập nhật ảnh đại diện thành công!";
                        $loai_thong_bao = "success";
                    } catch (PDOException $e) {
                        $thong_bao = "Lỗi cập nhật ảnh: " . $e->getMessage();
                        $loai_thong_bao = "error";
                    }
                }
            }
        }
    }
} 
    // TRƯỜNG HỢP 2: LÀ FORM CẬP NHẬT THÔNG TIN (Chỉ chạy khi có gửi 'ho_ten' lên)
    // TRƯỜNG HỢP 2: LÀ FORM CẬP NHẬT THÔNG TIN
    elseif (isset($_POST['ho_ten'])) {
        $ho_ten = trim($_POST['ho_ten']);
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dia_chi = trim($_POST['dia_chi'] ?? ''); // Lấy dữ liệu địa chỉ từ form

        try {
            // Thêm trường dia_chi = ? vào câu lệnh UPDATE
            $updateStmt = $pdo->prepare("UPDATE khachhang SET ho_ten = ?, so_dien_thoai = ?, email = ?, dia_chi = ? WHERE ma_tk = ?");
            
            // Đưa biến $dia_chi vào mảng execute
            $updateStmt->execute([$ho_ten, $so_dien_thoai, $email, $dia_chi, $ma_tk]);

            $thong_bao = "Cập nhật thông tin thành công!";
            $loai_thong_bao = "success";
            
            // Cập nhật lại mảng $khach_hang ngay lập tức để giao diện hiển thị dữ liệu mới
            $khach_hang['ho_ten'] = $ho_ten;
            $khach_hang['so_dien_thoai'] = $so_dien_thoai;
            $khach_hang['email'] = $email;
            $khach_hang['dia_chi'] = $dia_chi;
            
        } catch (PDOException $e) {
            $thong_bao = "Lỗi cập nhật: " . $e->getMessage();
            $loai_thong_bao = "error";
        }
    }
// 1. XỬ LÝ UPLOAD ẢNH ĐẠI DIỆN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['anh_dai_dien'])) {
    $file = $_FILES['anh_dai_dien'];

    // Kiểm tra xem có lỗi khi upload không
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        // Lấy đuôi file
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Kiểm tra định dạng
        if (!in_array($file_ext, $allowed_ext)) {
            $thong_bao = "Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF).";
            $loai_thong_bao = "error";
        } elseif ($file['size'] > 2 * 1024 * 1024) { // Giới hạn dung lượng 2MB
            $thong_bao = "Dung lượng ảnh quá lớn. Tối đa 2MB.";
            $loai_thong_bao = "error";
        } else {
            // Đặt tên file mới để không bị trùng (Ví dụ: avatar_1_16999999.jpg)
            $new_file_name = 'avatar_' . $ma_tk . '_' . time() . '.' . $file_ext;
            
            // THƯ MỤC LƯU ẢNH (Bạn phải tạo thư mục này trong project)
            $upload_dir = 'uploads/avatars/'; 
            
            // Tạo thư mục nếu nó chưa tồn tại
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Đường dẫn vật lý để di chuyển file vào thư mục
            $upload_path = $upload_dir . $new_file_name;

            // Di chuyển file từ bộ nhớ tạm vào thư mục dự án
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Đường dẫn lưu vào Database (Bắt đầu bằng /rice4u/ để hiển thị web)
                $db_path = '/rice4u/uploads/avatars/' . $new_file_name;
                
                try {
                    $updateImgStmt = $pdo->prepare("UPDATE khachhang SET anh_dai_dien = ? WHERE ma_tk = ?");
                    $updateImgStmt->execute([$db_path, $ma_tk]);
                    $thong_bao = "Cập nhật ảnh đại diện thành công!";
                    $loai_thong_bao = "success";
                } catch (PDOException $e) {
                    $thong_bao = "Lỗi cập nhật ảnh: " . $e->getMessage();
                    $loai_thong_bao = "error";
                }
            } else {
                $thong_bao = "Có lỗi xảy ra khi lưu tệp tin.";
                $loai_thong_bao = "error";
            }
        }
    }
}
// 2. LẤY THÔNG TIN ĐỂ HIỂN THỊ 
$stmt = $pdo->prepare("SELECT * FROM khachhang WHERE ma_tk = ?");
$stmt->execute([$ma_tk]);
$khach_hang = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy khách hàng (lỗi data), khởi tạo mảng rỗng để tránh lỗi giao diện
if (!$khach_hang) {
    $khach_hang = ['ho_ten' => '', 'so_dien_thoai' => ''];
}
// Lấy id_kh của khách hàng hiện tại để truy vấn đơn hàng
$id_kh = $khach_hang['id_kh'] ?? 0;

// 3. LẤY THỐNG KÊ SỐ ĐƠN HÀNG THÀNH CÔNG
// Giả sử trạng thái giao thành công trong DB của bạn là 'hoan_thanh' hoặc 'da_giao'
$stmt_count = $pdo->prepare("SELECT COUNT(*) as so_don FROM donhang WHERE id_kh = ? AND trang_thai_dh = 'hoan_thanh'"); // Thay 'hoan_thanh' bằng đúng enum trong DB của bạn
$stmt_count->execute([$id_kh]);
$so_don_thanh_cong = $stmt_count->fetchColumn();

// 4. LẤY 5 ĐƠN HÀNG GẦN ĐÂY NHẤT
$stmt_orders = $pdo->prepare("SELECT * FROM donhang WHERE id_kh = ? ORDER BY ngay_dat DESC LIMIT 5");
$stmt_orders->execute([$id_kh]);
$danh_sach_don_hang = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

// Hàm phụ trợ: Chuyển đổi trạng thái enum tiếng Anh sang tiếng Việt có dấu
function getTenTrangThai($status) {
    $map = [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'dang_chuan_bi' => 'Đang chuẩn bị',
        'dang_giao' => 'Đang giao',
        'hoan_thanh' => 'Hoàn tất', // Cập nhật lại cho khớp enum của bạn
        'da_huy' => 'Đã hủy'
    ];
    return $map[$status] ?? $status;
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân - Cửa Hàng Gạo Rice4U</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>

<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <style>
            h1 {
            text-align: center;
            color: var(--green-dark);
            margin-top: 0;
            margin-bottom: 0.3em;
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            }

            main {
                position: relative;
                width: 100%;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 40px 20px;
                box-sizing: border-box;
                overflow: hidden;
            }

            main::before {
                content: "";
                position: absolute;
                top: -20px;
                left: -20px;
                right: -20px;
                bottom: -20px;
                background: url(/rice4u/asset/images/bgr.jpg) no-repeat center center;
                background-size: cover;
                filter: blur(6px);
                z-index: -2;
            }

            main::after {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.2);
                z-index: -1;
            }

            .profile-container {
                background-color: #ffffff;
                max-width: 850px;
                width: 100%;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                z-index: 1;
                box-sizing: border-box;
            }

            .header { text-align: center; margin-bottom: 40px; }
            .header h1 { margin: 0; font-size: 28px; font-weight: bold; }
            .header p { margin: 5px 0 0; font-size: 16px; color: #555; letter-spacing: 1px; }

            .top-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px; flex-wrap: wrap; gap: 20px; }
            .profile-card { display: flex; align-items: center; gap: 20px; }
            
            .profile-img { width: 100px; height: 100px; border-radius: 50%; border: 3px solid #D4AF37; object-fit: cover; }
            .profile-info h2 { margin: 0 0 5px 0; font-size: 22px; }
            .profile-info p { margin: 0 0 10px 0; color: #555; font-size: 14px; }
            .badge { background-color: #8B5A2B; color: white; padding: 5px 12px; border-radius: 20px; font-size: 13px; display: inline-flex; align-items: center; gap: 5px; }

            .stats-card { background-color: #F5EFE6; padding: 20px 25px; border-radius: 10px; width: 320px; }
            .stat-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
            .stat-item:last-child { margin-bottom: 0; }
            .stat-label { display: flex; align-items: center; gap: 10px; }
            .stat-value { font-weight: bold; }
            .gold-text { color: #B8860B; font-weight: normal; }

            .bottom-section { display: flex; justify-content: space-between; gap: 40px; flex-wrap: wrap; }
            .section-title { font-size: 18px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }

            .personal-info, .recent-orders { flex: 1; min-width: 300px; }
            .info-table { width: 100%; border-collapse: collapse; }
            .info-table td { padding: 8px 0; vertical-align: top; font-size: 15px; }
            .info-table td:first-child { font-weight: bold; width: 140px; }

            .order-card { border: 1px solid #E0E0E0; border-radius: 8px; padding: 15px; margin-bottom: 15px; display: flex; justify-content: space-between; }
            .order-details { font-size: 15px; line-height: 1.5; }
            .order-status { color: #2E7D32; font-weight: bold; font-size: 14px; }

            .logout-btn { margin-top: 30px; background-color: #d32f2f; color: white; border: none; padding: 10px 24px; font-size: 15px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background-color 0.3s; display: inline-flex; align-items: center; gap: 8px; }
            .logout-btn:hover { background-color: #b71c1c; }

            @media (max-width: 768px) {
                .top-section, .bottom-section { flex-direction: column; }
                .stats-card { width: 100%; box-sizing: border-box; }
                .profile-container { padding: 20px; }
            }
            .avatar-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px; /* Khoảng cách giữa ảnh và nút */
            }

            .upload-btn {
                font-size: 13px;
                color: #444;
                cursor: pointer;
                background: #f0f0f0;
                padding: 6px 12px;
                border-radius: 20px;
                transition: all 0.3s;
                border: 1px solid #ddd;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .upload-btn:hover {
                background: #D4AF37; /* Đổi sang màu vàng khi di chuột qua */
                color: white;
                border-color: #D4AF37;
            }
            /* CSS cho nút Chỉnh sửa */
            .btn-edit {
                margin-top: 15px;
                background-color: #f0f0f0;
                color: #333;
                border: 1px solid #ccc;
                padding: 8px 15px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                font-weight: bold;
                transition: all 0.3s;
            }
            .btn-edit:hover {
                background-color: #e0e0e0;
            }

            /* CSS cho Form nhập liệu */
            .edit-form .form-group {
                margin-bottom: 15px;
            }
            .edit-form label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                font-size: 14px;
                color: #555;
            }
            .edit-form input[type="text"], .edit-form input[type="email"]{
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #ccc;
                border-radius: 6px;
                box-sizing: border-box;
                font-family: inherit;
                font-size: 14px;
                transition: border-color 0.3s;
            }
            .edit-form input[type="text"]:focus {
                border-color: #8B5A2B;
                outline: none;
            }

            /* CSS cho Cụm nút Lưu và Hủy */
            .form-actions {
                display: flex;
                gap: 10px;
                margin-top: 20px;
            }
            .btn-save {
                background-color: #2E7D32;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: background-color 0.3s;
            }
            .btn-save:hover { background-color: #1b5e20; }

            .btn-cancel {
                background-color: #f44336;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: background-color 0.3s;
            }
            .btn-cancel:hover { background-color: #d32f2f; }

            /* Header của đơn hàng gần đây */
            .orders-header{
                display:flex;
                justify-content:space-between;
                align-items:center;
                margin-bottom:15px;
            }

            /* Nút xem lịch các đơn hàng */
            .history-btn{
                text-decoration:none;
                background:#2E7D32;
                color:white;
                padding:7px 14px;
                border-radius:6px;
                font-size:13px;
                font-weight:bold;
                transition:0.3s;
                display:flex;
                align-items:center;
                gap:6px;
            }

            .history-btn:hover{
                background:#1b5e20;
                transform:translateY(-1px);
            }
        </style>

        <div class="profile-container">
            <?php if (!empty($thong_bao)): ?>
                <div style="padding: 10px; margin-bottom: 20px; background-color: <?= $loai_thong_bao == 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $loai_thong_bao == 'success' ? '#155724' : '#721c24' ?>; border-radius: 5px;">
                    <?= $thong_bao ?>
                </div>
            <?php endif; ?>

            <div class="header">
                <h1>HỒ SƠ CỦA TÔI</h1>
                <p>MY PROFILE</p>
            </div>

            <div class="top-section">
                <div class="profile-card">
    <div class="avatar-wrapper">
        <?php
        // Kiểm tra xem khách hàng có ảnh đại diện trong CSDL chưa
        $avatar_src = !empty($khach_hang['anh_dai_dien']) ? htmlspecialchars($khach_hang['anh_dai_dien']) : '/rice4u/asset/images/avatar.jpg';
        ?>
        <img src="<?= $avatar_src ?>" alt="Avatar" class="profile-img">
        
        <form action="hoso.php" method="POST" enctype="multipart/form-data">
            <label for="file-upload" class="upload-btn">
                <i class="fa-solid fa-camera"></i> Đổi ảnh
            </label>
            <input id="file-upload" type="file" name="anh_dai_dien" accept="image/jpeg, image/png, image/gif" onchange="this.form.submit()" style="display: none;">
        </form>
    </div>

    <div class="profile-info">
        <h2><?= htmlspecialchars($khach_hang['ho_ten'] ?? 'Khách hàng') ?></h2>
        <p>Thành viên từ <?= !empty($khach_hang['ngay_tao']) ? date('m/Y', strtotime($khach_hang['ngay_tao'])) : '...' ?></p>
        </div>
</div>

                <div class="stats-card" style="color: #333333;">
    <div class="stat-item">
        <span class="stat-label" style="color: #555; text-transform: none;">
            <i class="fa-solid fa-clipboard-list"></i> Đơn hàng thành công:
        </span>
        <span class="stat-value"><?= number_format($so_don_thanh_cong ?? 0) ?></span>
    </div>
    
    <div class="stat-item">
        <span class="stat-label" style="color: #555; text-transform: none;">
            <i class="fa-solid fa-trophy"></i> Điểm tích lũy:
        </span>
        <span class="stat-value">
            <?= number_format($diem_tich_luy, 0, ',', '.') ?> 
            <span style="color: <?= $mau_hang ?>; font-weight: normal;">(<?= $ten_hang ?>)</span>
        </span>
    </div>
    
    <div class="stat-item">
        <span class="stat-label" style="color: #555; text-transform: none;">
            <i class="fa-solid fa-ticket"></i> Voucher hiện có:
        </span>
        <span class="stat-value">0</span>
    </div>
</div>
            </div>

            <div class="bottom-section">
                <div class="personal-info">
    <div class="section-title">THÔNG TIN CÁ NHÂN</div>
    
    <div id="view-mode">
        <table class="info-table">
            <tr>
                <td>Họ tên:</td>
                <td><?= htmlspecialchars($khach_hang['ho_ten'] ?? 'Chưa cập nhật') ?></td>
            </tr>
            <tr>
                <td>Số điện thoại:</td>
                <td><?= htmlspecialchars($khach_hang['so_dien_thoai'] ?? 'Chưa cập nhật') ?></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><?= htmlspecialchars($khach_hang['email'] ?? 'Chưa cập nhật') ?></td>
            </tr>
            <tr>
                <td>Địa chỉ mặc định:</td>
                <td><?= htmlspecialchars($khach_hang['dia_chi'] ?? 'Chưa cập nhật') ?></td>
            </tr>
        </table>
        
        <button type="button" class="btn-edit" onclick="toggleEditMode()">
            <i class="fa-solid fa-pen"></i> Chỉnh sửa thông tin
        </button>
    </div>

    <div id="edit-mode" style="display: none;">
        <form action="hoso.php" method="POST" class="edit-form">
            <div class="form-group">
                <label>Họ tên:</label>
                <input type="text" name="ho_ten" value="<?= htmlspecialchars($khach_hang['ho_ten'] ?? '') ?>" placeholder="Nhập họ tên mới" required>
            </div>
            
            <div class="form-group">
                <label>Số điện thoại:</label>
                <input type="text" name="so_dien_thoai" value="<?= htmlspecialchars($khach_hang['so_dien_thoai'] ?? '') ?>" placeholder="Nhập số điện thoại mới" required>
            </div>
            
            <div class="form-group" >
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($khach_hang['email'] ?? '') ?>" placeholder="Nhập email mới" required>
            </div>
            
            <div class="form-group">
                <label>Địa chỉ mặc định:</label>
                <input type="text" name="dia_chi" value="<?= htmlspecialchars($khach_hang['dia_chi'] ?? '') ?>" placeholder="Nhập địa chỉ nhận hàng (VD: 123 Lê Lợi, Quận 1, TP.HCM)" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                </button>
                <button type="button" class="btn-cancel" onclick="toggleEditMode()">Hủy</button>
            </div>
        </form>
    </div>

    <form action="dangxuat.php" method="POST">
        <button type="submit" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
        </button>
    </form>
</div>
    <div class="recent-orders">
        <div class="orders-header">
            <div class="section-title">ĐƠN HÀNG GẦN ĐÂY</div>
            <a href="lichsudonhang.php" class="history-btn">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Xem tất cả
            </a>
        </div>
    <?php if (count($danh_sach_don_hang) > 0): ?>
        <?php foreach ($danh_sach_don_hang as $don): ?>
            <div class="order-card">
                <div class="order-details">
                    <strong><?= htmlspecialchars($don['ma_don']) ?></strong><br>
                    <span style="color: #666; font-size: 13px;">
                        Ngày: <?= date('d/m/Y', strtotime($don['ngay_dat'])) ?>
                    </span><br>
                    <strong><?= number_format($don['tong_thanh_toan'], 0, ',', '.') ?>đ</strong>
                </div>
                <div class="order-status"><?= getTenTrangThai($don['trang_thai_dh']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #666; font-style: italic;">Bạn chưa có đơn hàng nào.</p>
    <?php endif; ?>
</div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
<script>
    // Hàm này có nhiệm vụ: Nếu bảng đang hiện thì ẩn nó đi và bật Form lên, ngược lại.
    function toggleEditMode() {
        var viewMode = document.getElementById('view-mode');
        var editMode = document.getElementById('edit-mode');
        
        if (viewMode.style.display === 'none') {
            viewMode.style.display = 'block';
            editMode.style.display = 'none';
        } else {
            viewMode.style.display = 'none';
            editMode.style.display = 'block';
        }
    }
</script>
</body>

</html>