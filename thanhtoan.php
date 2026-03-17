<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "rice4u";

// 1. Khởi tạo session để bắt đầu làm việc với biến $_SESSION
session_start();
$ma_tk=$_SESSION['ma_tk'] ?? null;
// 2. Kiểm tra xem biến session 'ma_tk' có tồn tại không
// Nếu không tồn tại, tức là chưa đăng nhập -> chuyển hướng về login
if (!$ma_tk) {
    header("Location: /rice4u/dangnhap.php");
    exit(); // Dừng thực thi code phía sau ngay lập tức
}


$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// truy van du lieu khach hang
$sql_kh="SELECT ho_ten, so_dien_thoai, dia_chi FROM khachhang";

$result_kh=$conn->query($sql_kh);
$data_hoten;
$data_sdt;
$data_diachi;
if($result_kh && $result_kh->num_rows > 0)
    {
        while($row=$result_kh->fetch_assoc())
            {
                $data_hoten=$row["ho_ten"];
                $data_sdt=$row["so_dien_thoai"];
                $data_diachi=$row["dia_chi"];
            }
    }



$thong_bao = "";

$danh_sach_sp = [];
$tongtienhang = 0;
$phi_van_chuyen = 20000;

$cartItems = $_SESSION['gio_hang'] ?? [];

foreach ($cartItems as $item) {
    $gia = (float)($item['gia_ban'] ?? $item['gia_tai_thoi_diem'] ?? 0);
    $sl  = (int)($item['so_luong'] ?? 1);
    $thanh_tien = $gia * $sl;
    $tongtienhang += $thanh_tien;

    $danh_sach_sp[] = [
        'hinh'       => $item['hinh'] ?? $item['hinh_anh'] ?? 'assets/images/default-rice.jpg',
        'ten'        => $item['ten_sp'] ?? 'Sản phẩm không tên',
        'gia'        => $gia,
        'sl'         => $sl,
        'thanh_tien' => $thanh_tien
    ];
}

$tongthanhtoan = $tongtienhang + $phi_van_chuyen;

// Xử lý khi nhấn "Đặt hàng" (POST)

// Xử lý khi nhấn "Đặt hàng"

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    if (empty($cartItems)) {
        $thong_bao = "Giỏ hàng trống!";
    } else {
        // Bắt đầu transaction để đảm bảo hoặc lưu hết hoặc không lưu gì
        mysqli_begin_transaction($conn);

        try {
            // Lấy id_kh từ ma_tk
            $sql_kh = "SELECT id_kh FROM khachhang WHERE ma_tk = '$ma_tk'";
            $result_kh = mysqli_query($conn, $sql_kh);

            if (!$result_kh || mysqli_num_rows($result_kh) == 0) {
                throw new Exception("Không tìm thấy khách hàng.");
            }

            $row_kh = mysqli_fetch_assoc($result_kh);
            $id_kh = $row_kh['id_kh'];

            // Sinh mã đơn hàng (DH + yymmdd + số tăng dần)
            $prefix = 'DH' . date('ymd');
            $sql_count = "SELECT COUNT(*) as cnt FROM donhang WHERE id_dh LIKE '$prefix%'";
            $result_count = mysqli_query($conn, $sql_count);
            $row_count = mysqli_fetch_assoc($result_count);
            $next = $row_count['cnt'] + 1;
            $id_dh = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

            // Lấy thông tin khách từ biến đã có
            $ho_ten = mysqli_real_escape_string($conn, $data_hoten);
            $sdt    = mysqli_real_escape_string($conn, $data_sdt);
            $diachi = mysqli_real_escape_string($conn, $data_diachi);
           // $phuong_thuc = mysqli_real_escape_string($conn, $_POST['phuong_thuc_tt'] ?? 'cod');
            $ghi_chu = mysqli_real_escape_string($conn, $_POST['message'] ?? '');

            // Insert donhang
            $sql_don = "INSERT INTO donhang (
                            id_dh, id_kh, ma_don, ho_ten_nguoi_nhan, so_dien_thoai, 
                            dia_chi_giao, tong_tien_hang, phi_van_chuyen, 
                            tong_thanh_toan, ghi_chu, 
                            trang_thai_tt, trang_thai_dh, ngay_dat
                        ) VALUES (
                            '$id_dh', $id_kh, '$id_dh', '$ho_ten', '$sdt',
                            '$diachi', $tongtienhang, $phi_van_chuyen,
                            $tongthanhtoan, '$ghi_chu',
                            'chua_tt', 'cho_xac_nhan', NOW()
                        )";

            if (!mysqli_query($conn, $sql_don)) {
                throw new Exception("Lỗi lưu đơn hàng: " . mysqli_error($conn));
            }

            // Insert chi tiết đơn hàng
            foreach ($cartItems as $item) {
                $id_sp    = (int)($item['id_sp'] ?? 0);
                $sl       = (int)($item['so_luong'] ?? 1);
                $gia      = (float)($item['gia_ban'] ?? 0);

                if ($id_sp <= 0) continue; // Bỏ qua nếu thiếu id_sp

                $thanh_tien = $gia * $sl;

                $sql_ct = "INSERT INTO chitiet_donhang (id_dh, id_sp, so_luong, gia_ban, thanh_tien)
                           VALUES ('$id_dh', $id_sp, $sl, $gia, $thanh_tien)";

                if (!mysqli_query($conn, $sql_ct)) {
                    throw new Exception("Lỗi lưu chi tiết sản phẩm: " . mysqli_error($conn));
                }
            }

            // Thành công
            mysqli_commit($conn);
            unset($_SESSION['gio_hang']);

            $thong_bao = "
                <div style='background:#d4edda; color:#155724; padding:20px; border:1px solid #c3e6cb; border-radius:8px; text-align:center; margin:20px 0;'>
                    <h3>ĐẶT HÀNG THÀNH CÔNG!</h3>
                    <p>Mã đơn: <strong>$id_dh</strong></p>
                    <p>Tổng tiền: <strong>" . number_format($tongthanhtoan) . "đ</strong></p>
                    <p>Cảm ơn bạn! Chúng tôi sẽ liên hệ sớm.</p>
                </div>";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $thong_bao = "<div style='color:red; text-align:center; padding:15px;'>LỖI: " . $e->getMessage() . "</div>";
        }
    }
}
    $conn->close();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Rice4u</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="./header.css">
    <link rel="stylesheet" href="./footer.css">
    <link rel="stylesheet" href="/asset/thanhtoan.css"> 
    <style>
        .error {
             color: red;
             font-weight: bold;
             text-align: center;
             margin: 15px 0;
             }
        .product-item {
             border-bottom: 1px solid #eee;
             padding: 15px 0;
             display: flex; 
             align-items: center;
             }
        .product-item img {
             width: 80px;
             height: 80px;
             object-fit: cover; 
             margin-right: 15px;
              border-radius: 8px;
             }
        .summary {
             background: #f8f9fa;
             padding: 20px;
              border-radius: 8px; 
              margin-top: 20px;
             }
        .total {
             font-size: 1.3em;
             font-weight: bold;
             color: #d32f2f; 
            }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="container" style="max-width: 1000px; margin: 30px auto;">

    <?php if ($thong_bao): ?>
        <div class="error"><?php echo $thong_bao; ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <h3>Giỏ hàng trống</h3>
            <a href="sanpham.php" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>

    <form method="post" action="">
    
        <!-- Địa chỉ nhận hàng -->
        <div class="address">
            <div class="add-header">
                <p><i class="fas fa-map-marker-alt"></i> Địa chỉ nhận hàng</p>
            </div>
            <div class="add-client">
                <span class="info-user">
                    <strong><?php echo $data_hoten; ?> (+84) <?php echo $data_sdt; ?></strong>
                </span>
                <span class="add-user"><?php echo $data_diachi ?: 'Chưa có địa chỉ'; ?></span>
                <span class="add-action">
                    Mặc định | <a href="thaydoidiachi.php" class="change">Thay đổi</a>
                </span>
            </div>
        </div>

        <div class="cart-header">
            <div class="c-item">Sản phẩm</div>
            <div class="c-item">Giá</div>
            <div class="c-item">Số Lượng</div>
            <div class="c-item">Thành Tiền</div>
        </div>
        
<div class="cart-body">
    <?php foreach ($danh_sach_sp as $item): ?>
        <div class="product-item">
            <img src="<?php echo $item['hinh']; ?>" 
                 alt="<?php echo htmlspecialchars($item['ten']); ?>" 
                 >
            
            <span><?php echo htmlspecialchars($item['ten']); ?></span>
            
            <div class="text-end">
             <div>   <?php echo number_format($item['gia'], 0, ',', '.'); ?>đ  </div> 
             <div> <?php echo $item['sl']; ?> </div>  
             <div>   <?php echo number_format($item['thanh_tien'], 0, ',', '.'); ?>đ </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="conclude">
    <span>Tổng tiền hàng: <?php echo number_format($tongtienhang, 0, ',', '.'); ?>đ</span>
</div>


                <div class="mess">
                    <label for="message">Lời nhắn cho người bán:</label><br>
                    <textarea id="message" name="message" rows="3" class="form-control" placeholder="Ghi chú cho Rice4u..."></textarea>
                </div>

        <!-- Phương thức thanh toán -->
        <div class="payment">
            <div class="pay-header">
                <p>Phương thức thanh toán</p>
            </div>
            <div class="card-body">
                <select name="phuong_thuc_tt" class="form-select">
                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                </select>
            </div>
        </div>

        <!-- Tổng kết -->
        <div class="sum-conclude">
            <div class="sum">
                <span>Tổng tiền hàng:</span>
                <span><?php echo number_format($tongtienhang, 0, ',', '.'); ?>đ</span>
            </div>
            <div class="sum">
                <span>Phí vận chuyển:</span>
                <span><?php echo number_format($phi_van_chuyen, 0, ',', '.'); ?>đ</span>
            </div>
            <hr>
            <div class="total">
                <span>Tổng thanh toán:</span>
                <span><?php echo number_format($tongthanhtoan, 0, ',', '.'); ?>đ</span>
            </div>
        </div>

        <div class="order">
    <p>Nhấn "Đặt hàng" đồng nghĩa với việc bạn tuân theo <a href="#">điều khoản Rice4u</a></p>
    
    <?php if ($ma_tk): ?>
        <button type="submit" name="submit" class="btn">
            Đặt hàng
        </button>
    <?php else: ?>
        <button type="button" class="btn" disabled>
            Đặt hàng (Vui lòng đăng nhập/đăng ký)
        </button>
        <div class="alert">
            Bạn cần <a href="dangnhap.php?redirect=thanhtoan.php" class="alert-link">đăng nhập</a> 
            hoặc <a href="dangky.php?redirect=thanhtoan.php" class="alert-link">đăng ký</a> 
            để hoàn tất thanh toán.
        </div>
    <?php endif; ?>
</div>

    </form>

    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>

<style>

    body {
        background-color: #F5F5F5;
    }
    
    form {
        background-color: #fff;
    }
    /* 1. Phần Địa chỉ */
.address {
    background: #fff;
    padding: 20px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.add-header {
    color: #237227;
    font-size: 18px;
    margin-top: 0;
    padding: 10px;
}


.add-client {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 15px;
    justify-content: space-between;
}

.cart-header {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    padding: 12px 16px;
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
    justify-content: space-between;
}

.cart-header div {
    flex: 1;
    text-align: center;
}

/*
.card-body {
    display: flex;
    justify-content: space-between;
}*/

.product-item {
     margin-left: 40px;
}

.text-end {
     padding-left: 100px;
     padding-right: 100px;
    display: flex;
    flex: 1;
    text-align: center;
    justify-content: space-between;
   
}
.change {
    color: #4080ff;
    text-decoration: none;
    margin-left: auto;
}


.mess {
    display: flex;
    gap: 15px;
    color:#237227;
    margin: 10px;
    padding-top: 20px;
}

.payment {
    display: flex;
    color:#237227;
    gap: 28px;
    margin: 10px;
    padding-top: 20px;
}

.conclude {
    display: flex;
    float: right;
    margin: 10px;
    padding-top: 10px;
    flex-direction: column;
    gap: 10px;
     font-weight: 600;
     color: #237227;

}


.product-item img {
    width:80px;
     height:80px; 
     object-fit:cover; 
     border-radius:8px;
      margin-right:15px;
}
.sum-conclude {
    margin-left: auto;          /* đẩy toàn bộ khối sang phải */
    margin-right: 0;
    text-align: right;          /* chữ bên trong căn phải */
    padding-top: 5px;
    padding-bottom: 5px;
}

.sum {
    padding-top: 5px;
    padding-bottom: 5px;
}
.total {
    padding-top: 10px;
}

.order {
     margin: 10px;
    padding-top: 20px;
    display: flex;
    justify-content: space-between;
}

.btn {
    color: #eee;
    background-color: #519A66;
    border: none;
    padding: 10px;
     margin: 20px;
    border-radius: 5px;
    width: 100px;
    transition: all 0.3s ease;     /* thời gian chuyển động mượt 0.3 giây */
    transform: scale(1);
}

.btn:hover {
    background-color: #237227;
    transform: scale(1.08);        /* phóng to 8% - có thể chỉnh 1.05 đến 1.12 tùy ý */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);  /* thêm bóng đổ để nổi bật hơn (tùy chọn) */
    cursor: pointer;

}
</style>