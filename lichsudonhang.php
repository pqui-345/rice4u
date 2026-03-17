<?php
session_start();
require_once __DIR__ . '/includes/db.php';

/* kiểm tra đăng nhập */
if (!isset($_SESSION['ma_tk'])) {
    header("Location: dangnhap.php");
    exit();
}

$ma_tk = $_SESSION['ma_tk'];

/* Lấy id_kh từ tài khoản */
$sql_kh = "SELECT id_kh FROM khachhang WHERE ma_tk = ?";
$stmt_kh = $pdo->prepare($sql_kh);
$stmt_kh->execute([$ma_tk]);
$khachhang = $stmt_kh->fetch(PDO::FETCH_ASSOC);

if (!$khachhang) {
    die("Không tìm thấy khách hàng");
}

$id_kh = $khachhang['id_kh'];

/* Lấy thông tin đơn hàng */
$sql = "SELECT dh.*, ct.so_luong, ct.thanh_tien, sp.ten_sp
        FROM donhang dh
        JOIN chitiet_donhang ct ON dh.id_dh = ct.id_dh
        JOIN sanpham sp ON ct.id_sp = sp.id_sp
        WHERE dh.id_kh = ?
        ORDER BY dh.id_dh DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_kh]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Gom các sản phẩm theo từng đơn
$orders = [];

foreach ($data as $row) {
    $orders[$row['id_dh']]['info'] = $row;
    $orders[$row['id_dh']]['products'][] = $row;
}

include 'includes/header.php';
?>

<style>
    .history-container{
    width:900px;
    margin:40px auto;
    }

    .order-card{
        background: white;
        border-radius:12px;
        margin-bottom:25px;
        padding:20px;
        box-shadow:0 3px 10px rgba(0,0,0,0.08);
    }

    .order-header{
        display:flex;
        justify-content:space-between;
        border-bottom:1px solid #eee;
        padding-bottom:10px;
        margin-bottom:15px;
        font-weight:600;
    }

    .product-item{
        display:flex;
        align-items:center;
        gap:15px;
        padding:12px 0;
        border-bottom:1px solid #f2f2f2;
    }

    .product-info{
        flex:1;
    }

    .product-info .name{
        font-weight:600;
    }

    .price{
        color:#d32f2f;
        font-weight:bold;
    }

    .order-footer{
        text-align:right;
        margin-top:15px;
        font-size:18px;
    }

    .view-btn{
        margin-left:15px;
        background:#2e7d32;
        color:white;
        padding:6px 14px;
        border-radius:6px;
        text-decoration:none;
    }

    .view-btn:hover{
        background:#1b5e20;
    }
</style>


<div class="history-container">

    <h2>Lịch sử đơn hàng</h2>

    <?php foreach($orders as $order): ?>
    <div class="order-card">

        <div class="order-header">
            <span>Mã đơn: <b><?= $order['info']['id_dh'] ?></b></span>
            <span><?= $order['info']['trang_thai_dh'] ?></span>
        </div>

        <?php foreach($order['products'] as $sp): ?>
        <div class="product-item">

            <div class="product-info">
                <div class="name"><?= $sp['ten_sp'] ?></div>
                <div>Số lượng: <?= $sp['so_luong'] ?></div>
            </div>

            <div class="price">
                <?= number_format($sp['thanh_tien']) ?>đ
            </div>

        </div>
        <?php endforeach; ?>

        <div class="order-footer">
            Tổng tiền:
            <b><?= number_format($order['info']['tong_thanh_toan']) ?>đ</b>

            <a class="view-btn"
            href="xacnhandonhang.php?madon=<?= $order['info']['id_dh'] ?>">
            Xem chi tiết
            </a>
        </div>

    </div>
    <?php endforeach; ?>

</div>

<?php include 'includes/footer.php'; ?>