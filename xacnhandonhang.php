<?php
session_start();
$conn = new mysqli("localhost","root","","rice4u");

if(!isset($_GET['madon'])){
    header("Location: trangchu.php");
    exit();
}

$madon = $_GET['madon'];

/* lấy thông tin đơn hàng */
$sql = "SELECT * FROM donhang WHERE id_dh='$madon'";
$result = $conn->query($sql);
$donhang = $result->fetch_assoc();

/* lấy thông tin chi tiết đơn hàng */
$sql_ct = "SELECT ct.*, sp.ten_sp
           FROM chitiet_donhang ct
           JOIN sanpham sp ON ct.id_sp = sp.id_sp
           WHERE ct.id_dh='$madon'";

$ct = $conn->query($sql_ct);

include 'includes/header.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Xác nhận đơn hàng</title>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
    body{
        margin:0;
        font-family: Arial;
        background-image:url("asset/images/banner.png");
        background-size:cover;
        background-position:center;
        background-attachment:fixed;
    }

    .overlay{
        background:rgba(0,0,0,0.35);
        min-height:100vh;
        display:flex;
        justify-content:center;
        align-items:center;
    }

    .confirm-card{
        width:750px;
        background:white;
        border-radius:15px;
        padding:35px;
        box-shadow:0 6px 20px rgba(0,0,0,0.2);
    }

    .confirm-title{
        color:#2e7d32;
        font-size:26px;
        margin-bottom:15px;
    }

    .order-table{
        width:100%;
        border-collapse:collapse;
        margin-top:15px;
    }

    .order-table th{
        background:#f1f8f4;
        padding:12px;
    }

    .order-table td{
        padding:12px;
        border-bottom:1px solid #eee;
    }

    .order-table td:nth-child(2),
    .order-table td:nth-child(3){
        text-align:center;
    }

    .customer-box{
    background:#f6fbf7;
    border-left:5px solid #2e7d32;
    padding:18px;
    margin-top:15px;
    border-radius:10px;
    line-height:1.8;
    }

    .customer-box h3{
        margin-top:0;
        color:#2e7d32;
    }

    .total{
        text-align:right;
        font-size:20px;
        font-weight:bold;
        color:#d32f2f;
        margin-top:20px;
    }

    .history-btn{
        display:inline-block;
        margin-top:20px;
        padding:10px 18px;
        background:#2e7d32;
        color:white;
        text-decoration:none;
        border-radius:8px;
    }

    .history-btn:hover{
        background:#1b5e20;
    }

</style>
</head>

<body>

    <div class="overlay">
        <div class="confirm-card">
            <h2 class="confirm-title">Đặt hàng thành công!</h2>
            <p>Mã đơn: <b><?= $donhang['id_dh'] ?></b></p>
            <p>Ngày đặt: <?= $donhang['ngay_dat'] ?></p>

            <div class="customer-box">
                <h3>Thông tin nhận hàng:</h3>
                <p><b>Người nhận:</b>
                <?= $donhang['ho_ten_nguoi_nhan'] ?></p>

                <p><b>Số điện thoại:</b>
                <?= $donhang['so_dien_thoai'] ?></p>

                <p><b>Địa chỉ giao:</b>
                <?= $donhang['dia_chi_giao'] ?></p>
            </div>

            <table class="order-table">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>

                <?php while($row=$ct->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['ten_sp'] ?></td>
                    <td><?= $row['so_luong'] ?></td>
                    <td><?= number_format($row['thanh_tien']) ?>đ</td>
                </tr>
                <?php endwhile; ?>
            </table>

            <div class="total">
                Tổng thanh toán:
                <?= number_format($donhang['tong_thanh_toan']) ?>đ
            </div>

            <a class="history-btn" href="lichsudonhang.php">
                Xem lịch sử đơn hàng
            </a>
        </div>
    </div>
</body>
</html>

<?php
    include 'includes/footer.php';
?>