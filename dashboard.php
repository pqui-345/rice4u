
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "rice4u";

// 1. Khởi tạo session để bắt đầu làm việc với biến $_SESSION
session_start();

// 2. Kiểm tra xem biến session 'ma_tk' có tồn tại không
// Nếu không tồn tại, tức là chưa đăng nhập -> chuyển hướng về login
if (!isset($_SESSION['ma_tk'])) {
    header("Location: /rice4u/dangnhap.php");
    exit(); // Dừng thực thi code phía sau ngay lập tức
}


$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy van tong doanh thu theo tung ngay trong 7 ngay gan nhat
//  dung DATE(ngay_dat) de gop cac don hang cung ngay lai
$sql = "SELECT DATE(ngay_dat) as ngay, SUM(tong_thanh_toan) as doanh_thu 
        FROM donhang 
        GROUP BY DATE(ngay_dat) 
        ORDER BY ngay DESC 
        LIMIT 7";

$result = $conn->query($sql);

$labels = [];
$data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // dinh dang lai ngay  (VD: 15/05)
        $labels[] = date("d/m", strtotime($row["ngay"]));
        $data[] = (float)$row["doanh_thu"];
    }
} else {
    // Neu chua co du lieu, tao mang rong de khong bi loi script
    $labels = ["Chưa có dữ liệu"];
    $data = [0];
}

// dao nguoc ngay cu ben trai, ngay moi ben phai
$labels = array_reverse($labels);
$data = array_reverse($data);

//Truy van tong don hang trong mot ngay
$tong_don_hang= "SELECT DATE(ngay_dat) as ngaydat, SUM(id_dh) as tong_don_hang
                 FROM donhang 
                 GRIUP BY DATE( ngay_dat) 
                 ORDER BY ngay DESC 
                 LIMIT 1";

$tdh= $conn->query($tong_don_hang);

$labels_tdh=[];
$data_tdh =0;
if($tdh && $tdh->num_rows > 0)
    {
        while($row=$tdh->fetch_assoc()) {
             // dinh dang lai ngay  (VD: 15/05)
        $labels_tdh[] = date("d/m", strtotime($row["ngay"]));
        $data_tdh = (float)$row["tong_don_hang"];
        }
    }


// Tinh tong ton kho hien tai tru di tong so luong da ban trong ngay

$ton_kho = "SELECT 
        (SELECT SUM(so_luong_ton) FROM sanpham) - 
        (SELECT IFNULL(SUM(ct.so_luong), 0) 
         FROM chitiet_donhang ct 
         JOIN donhang dh ON ct.id_dh = dh.id_dh 
         WHERE DATE(dh.ngay_dat) = CURDATE()) 
        AS tong_ton_kho_cuoi_ngay";

$ttk = $conn->query($ton_kho);
 

//$labels_ttk=[];
$data_ttk = 0;
if($ttk && $ttk->num_rows > 0)
    {
        while($row=$ttk->fetch_assoc()) {
             // dinh dang lai ngay  (VD: 15/05)
       // $labels_ttk[] = date("d/m", strtotime($row["ngay"]));
        $data_ttk = (float)$row["tong_ton_kho_cuoi_ngay"];
        }
    }

// Truy van san pham sap het hang
$sql_sap_het_hang="SELECT id_sp, ten_sp, SUM(so_luong_ton) as ton
                   FROM sanpham
                   WHERE ton < 50";

$sap_het_hang=$conn->query($sql_sap_het_hang);
$data_id_sp="";
$data_ten_sp="";
$data_hang=[];

if($sap_het_hang && $sap_het_hang->num_rows > 0)
    {
        while($row=$sap_het_hang->fetch_assoc())
            {
                $data_id_sp=$row["id_sp"];
                $data_ten_sp=$row["ten_sp"];
                $data_hang=(float)$row["ton"];
            }
    }

$conn->close();
?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">\
    <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <title>Gạo Sạch Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
       :root {
  --green-dark : #237227;
  --green-mid  : #519A66;
  --amber      : #FFAA00;
  --cream      : #FFD786;
  --cream-bg   : #fffbe2;
  --white      : #FFFFFF;
  --gray-light : #F5F5F5;
  --text-dark  : #1a2e1a;
  --text-mid   : #4a5e4a;
  --header-h   : 72px;
}

        body {
           font-family: 'Be Vietnam Pro', Arial, Helvetica, sans-serif;
            margin: 0;
            display: flex;
            background-color: var(--gray-light);
        }
       header {
    height: 60px;
    background: #519A66;
    display: flex;
    justify-content: flex-end; /* Đẩy icon sang phải */
    align-items: center;      /* Căn giữa theo chiều dọc */
    padding: 0 30px;
    border-bottom: 1px solid #ddd;
    position: fixed;
    top: 0;
    right: 0;
    left: 280px; /* Tránh đè lên sidebar */
    z-index: 1000;
}

        /* User area */
         .user {
           display: flex; 
           align-items: center;
           gap: 8px; 
           padding: 0 4px;
           border: 1px solid #1a2e1a;
           border-radius: 5px;
           color: #FFD786;
           border-color: #FFAA00;
          }

          
          /* Ẩn menu mặc định */
          .log-out {
           display: none; 
            position: absolute;
            right: 0;
              background: #ffffff;
             border: 1px solid #ddd;
             box-shadow: 0 4px 8px rgba(0,0,0,0.1);
             padding: 10px;
             border-radius: 5px;
            width: 80px;
             z-index: 1000;
            }

            .log-out a {
                 color:#e74c3c;
             text-decoration: none;
            }

/* Khi hover vào container, menu sẽ hiện ra */
.user-icon:hover .log-out {
    display: block;
}
          
                

        /* Sidebar */
        .sidebar {
            width: 240px;
            height: 100vh;
            background: var(--cream);
            color: white;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 { 
            color: var(--green-dark); 
            text-align: center; 
        }
        
        .menu-item {
            color:#237227;
            padding: 15px;
            cursor: pointer;
            border-bottom: 1px solid #FFAA00;
        }

        .menu-item:hover { background: #FFAA00; }

       /* Main Content */
       h1, h3 {
        color:#237227;
       }

        .main-content {
            margin-top: 30px;
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 320px);
        }

        /* Thẻ thống kê nhanh */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 5px solid var(--primary);
        }

        .stat-card h3 { margin: 0;
         font-size: 14px;
          color: #237227;
         }

        .stat-card p {
             font-size: 24px;
              font-weight: bold;
               margin: 10px 0 0;
                color: var(--dark); 
            }

        /* Bảng & Biểu đồ */
        .grid-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .status-low { color: #e74c3c; font-weight: bold; }



    </style>
</head>
<body>

    <header>
    <div class="user">
        <?php if (isset($_SESSION['ma_tk'])) : ?>
            <div class="user-info" style="margin-right: 15px;">
                Xin chào, <strong>Admin ID: <?php echo $_SESSION['ma_tk']; ?></strong>
            </div>
            <div class="icon">
                <div class="user-icon">
                    <a href="#"><i class="fa-solid fa-user"></i></a>
                    <div class="log-out">
                        <a href="/rice4u/dangnhap.php">Đăng xuất</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>

    <div class="sidebar">
        <h2>Rice4u</h2>
        <div class="menu-item">🏠 Tổng quan</div>
        <div class="menu-item">📦 Nhập kho</div>
        <div class="menu-item">🛒 Đơn hàng</div>
        <div class="menu-item">📊 Thống kê</div>
    </div>


   
    <div class="main-content">
        <h1>Bảng Điều Khiển Bán Hàng</h1>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Doanh thu hôm nay</h3>
                <p><?php echo json_encode($data); ?></p>
            </div>
            <div class="stat-card">
                <h3>Đơn hàng mới</h3>
                <p><?php echo json_encode($data_tdh); ?></p>
            </div>
            <div class="stat-card">
                <h3>Tổng tồn kho (kg)</h3>
                <p><?php echo json_encode($data_ttk); ?></p>
            </div>
        </div>

        <div class="grid-layout">
            <div class="card">
                <h3>Doanh thu 7 ngày qua</h3>
                <canvas id="revenueChart"></canvas>
            </div>

            <div class="card">
                <h3>Sắp hết hàng!</h3>
                <table>
                    <tr>
                        <th>Mã sản phẩm</th>
                        <th>Tên sản phẩm</th>
                        <th>Còn lại</th>
                    </tr>
                    <tr>
                        <td><?php echo json_encode($data_id_sp); ?></td>
                        <td><?php echo json_encode($data_ten_sp); ?></td>
                        <td class="status-low"><?php echo json_encode($data_hang); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            // PHP đổ dữ liệu từ bảng donhang vào đây
            labels: <?php echo json_encode($labels); ?>, 
            datasets: [{
                label: 'Doanh thu thực tế (VNĐ)',
                data: <?php echo json_encode($data); ?>,
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderWidth: 3,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Them chu đ sau so tien 
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' đ';
                        }
                    }
                }
            }
        }
    });
</script>
</body>
</html>