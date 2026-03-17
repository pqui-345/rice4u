<?php
session_start();
require_once("includes/db.php");

/* Kiểm tra vai trò Admin */
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: dangnhap.php");
    exit();
}

/* Lấy ds đơn hàng */
$sql = "SELECT * FROM donhang ORDER BY id_dh DESC";
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f4f6f8;
            margin:0;
            font-size:18px; 
        }

        .container{
            width:98%;          
            max-width:1700px;  
            margin:40px auto;
            padding:0 20px;
            box-sizing:border-box;
        }

        h2{
            text-align:center;
            margin-bottom:30px;
            color:#2e7d32;
            font-size:30px;
            font-weight:bold;
        }

        .table-card{
            background:#fff;
            border-radius:14px;
            box-shadow:0 3px 14px rgba(0,0,0,.08);
            overflow:hidden;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            background:#2e7d32;
            color:white;
            padding:16px 12px;
            font-weight:600;
            font-size:18px;
        }

        td{
            padding:16px 12px;
            border-bottom:1px solid #f0f0f0;
            text-align:center;
            font-size:17px;
        }

        tr:hover{
            background:#fafafa;
        }

        select{
            padding:7px 12px;
            border-radius:8px;
            border:1px solid #ddd;
            background:#fff;
            cursor:pointer;
            font-size:15px;
        }

        .btn{
            display:inline-block;
            padding:8px 18px;
            background:#FFD786;
            color:white;
            text-decoration:none;
            border-radius:10px;
            font-size:16px;
            font-weight:600;
            transition:.25s;
        }

        .btn:hover{
            background:#f4b942;
            transform:scale(1.05);
        }

        .price{
            font-weight:bold;
            color:#2e7d32;
            font-size:18px;
        }

        .status{
            font-weight:bold;
        }

        .wait{ 
            color:#f39c12; 
        }

        .ship{ 
            color:#2980b9; 
        }

        .done{ 
            color:#27ae60; 
        }

        .cancel{ 
            color:#e74c3c; 
        }

        @media(max-width:900px){

            body{
                font-size:16px;
            }

            table{
                font-size:15px;
            }

            th,td{
                padding:12px 8px;
            }

            h2{
                font-size:24px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Quản lý đơn hàng</h2>
        <div class="table-card">
            <table>
                <tr>
                    <th>Mã đơn</th>
                    <th>Người nhận</th>
                    <th>SĐT</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Chi tiết</th>
                </tr>

                <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                <tr>

                    <td><?= $row['id_dh'] ?></td>

                    <td><?= htmlspecialchars($row['ho_ten_nguoi_nhan']) ?></td>

                    <td><?= $row['so_dien_thoai'] ?></td>

                    <td>
                    <b><?= number_format($row['tong_thanh_toan']) ?>đ</b>
                    </td>

                    <td>
                        <form method="POST" action="update_trangthaiDH_admin.php">
                            <input type="hidden" name="id_dh" value="<?= $row['id_dh'] ?>">
                            <select name="trang_thai_dh" onchange="this.form.submit()">
                                <option value="cho_xac_nhan"<?= $row['trang_thai_dh']=='cho_xac_nhan'?'selected':'' ?>>Chờ xác nhận</option>
                                <option value="dang_giao" <?= $row['trang_thai_dh']=='dang_giao'?'selected':'' ?>>Đang giao</option>
                                <option value="hoan_thanh"<?= $row['trang_thai_dh']=='hoan_thanh'?'selected':'' ?>>Hoàn thành</option>
                                <option value="da_huy"<?= $row['trang_thai_dh']=='da_huy'?'selected':'' ?>>Đã hủy</option>
                            </select>
                        </form>
                    </td>

                    <td><?= $row['ngay_dat'] ?></td>

                    <td>
                        <a class="btn"
                        href="chitietdonhang_admin.php?id=<?= $row['id_dh'] ?>">
                        Xem
                        </a>
                    </td>
                </tr>
                <?php } ?>

            </table>
        </div>
    </div>
</body>
</html>
