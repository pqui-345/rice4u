<?php
session_start();
require_once("includes/db.php");

/* Kiểm tra có đúng là admin ko */
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: dangnhap.php");
    exit();
}

/* Lấy id của đơn hàng */
if (!isset($_GET['id'])) {
    die("Không tìm thấy đơn hàng");
}

$id_dh = $_GET['id'];

/* Thông tin đơn hàng */
$sql = "
SELECT dh.*, kh.ho_ten, kh.so_dien_thoai
FROM donhang dh
LEFT JOIN khachhang kh ON dh.id_kh = kh.id_kh
WHERE dh.id_dh = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_dh]);
$donhang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donhang) {
    die("Đơn hàng không tồn tại");
}

/* Lấy thông tin chi tiết đơn hàng*/
$sql_ct = "
SELECT ct.*, sp.ten_sp
FROM chitiet_donhang ct
LEFT JOIN sanpham sp ON ct.id_sp = sp.id_sp
WHERE ct.id_dh = ?
";

$stmt_ct = $pdo->prepare($sql_ct);
$stmt_ct->execute([$id_dh]);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f4f6f8;
            margin:0;
            font-size:18px;         
        }

        .container{
            width:95%;               
            max-width:1600px;       
            margin:40px auto;
            padding:0 40px;
            box-sizing:border-box;
        }

        h2{
            text-align:center;
            margin-bottom:30px;
            font-size:32px;          
        }

        .card{
            background:white;
            padding:35px;        
            border-radius:14px;
            box-shadow:0 4px 14px rgba(0,0,0,0.08);
            margin-bottom:30px;
        }

        .card p{
            margin:12px 0;
            font-size:20px;         
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:white;
            border-radius:12px;
            overflow:hidden;
            font-size:19px;        
        }

        th{
            background:#2e7d32;
            color:white;
            padding:16px;
            font-size:20px;
        }

        td{
            padding:16px;
            border-bottom:1px solid #eee;
            text-align:center;
        }

        tr:hover{
            background:#f9f9f9;
        }

        .total{
            text-align:right;
            font-size:24px;
            font-weight:bold;
            margin-top:20px;
            color:#2e7d32;
        }

        .btn{
            display:inline-block;
            margin-top:25px;
            padding:12px 24px;
            background:#2e7d32;
            color:white;
            text-decoration:none;
            border-radius:10px;
            font-size:18px;
            font-weight:bold;
            transition:0.25s;
        }

        .btn:hover{
            background:#1b5e20;
            transform:scale(1.05);
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Chi tiết đơn hàng #<?= $donhang['id_dh'] ?></h2>

        <!-- Thông tin -->
        <div class="card">
            <p><b>Khách hàng:</b> <?= htmlspecialchars($donhang['ho_ten']) ?></p>
            <p><b>SĐT:</b> <?= $donhang['so_dien_thoai'] ?></p>
            <p><b>Địa chỉ giao:</b> <?= htmlspecialchars($donhang['dia_chi_giao']) ?></p>
            <p><b>Ngày đặt:</b> <?= $donhang['ngay_dat'] ?></p>
            <p><b>Trạng thái:</b> <?= $donhang['trang_thai_dh'] ?></p>
        </div>

        <!-- DS sản phâmt -->
        <div class="card">
            <table>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn vị</th>
                    <th>Giá</th>
                    <th>Thành tiền</th>
                </tr>

                <?php while($sp = $stmt_ct->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?= $sp['ten_sp'] ?></td>
                    <td><?= $sp['so_luong'] ?></td>
                    <td><?= $sp['don_vi'] ?></td>
                    <td><?= number_format($sp['gia_ban']) ?>đ</td>
                    <td><?= number_format($sp['thanh_tien']) ?>đ</td>
                </tr>
                <?php } ?>
            </table>

            <div class="total">
                Tổng thanh toán: <?= number_format($donhang['tong_thanh_toan']) ?>đ
            </div>

            <a class="btn" href="quanlydonhang.php">← Quay lại</a>

        </div>
    </div>
</body>
</html>