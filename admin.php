<?php
session_start();
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: dangnhap.php"); exit();
}
require_once("includes/db.php");

// Stats
$total_sp    = $pdo->query("SELECT COUNT(*) FROM sanpham WHERE trang_thai=1")->fetchColumn();
$total_dh    = $pdo->query("SELECT COUNT(*) FROM donhang")->fetchColumn();
$dh_cho      = $pdo->query("SELECT COUNT(*) FROM donhang WHERE trang_thai_dh='cho_xac_nhan'")->fetchColumn();
$dh_giao     = $pdo->query("SELECT COUNT(*) FROM donhang WHERE trang_thai_dh='dang_giao'")->fetchColumn();
$total_kh    = $pdo->query("SELECT COUNT(*) FROM khachhang")->fetchColumn();
$doanh_thu   = $pdo->query("SELECT IFNULL(SUM(tong_thanh_toan),0) FROM donhang WHERE trang_thai_dh != 'da_huy'")->fetchColumn();

// Đơn hàng gần nhất
$stmt_recent = $pdo->query("SELECT dh.id_dh, dh.ho_ten_nguoi_nhan, dh.tong_thanh_toan, dh.trang_thai_dh, dh.ngay_dat FROM donhang dh ORDER BY dh.ngay_dat DESC LIMIT 6");
$recent = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

// Sản phẩm sắp hết hàng
$stmt_low = $pdo->query("SELECT id_sp, ten_sp, so_luong_ton FROM sanpham WHERE trang_thai=1 AND so_luong_ton < 50 ORDER BY so_luong_ton ASC LIMIT 5");
$low_stock = $stmt_low->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin – Rice4U</title>
  <link rel="icon" href="/rice4u/asset/images/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
  <style>
    :root{--gd:#1b5e20;--gm:#2e7d32;--gl:#519A66;--amber:#f9a825;--bg:#f4f7f4;--white:#fff;}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Be Vietnam Pro',Arial,sans-serif;background:var(--bg);color:#222;}
    a{text-decoration:none;color:inherit;}

    /* ── TOPBAR ── */
    .topbar{height:64px;background:var(--gd);display:flex;align-items:center;padding:0 28px;gap:16px;box-shadow:0 2px 12px rgba(0,0,0,.15);}
    .topbar-logo{display:flex;align-items:center;gap:10px;}
    .topbar-logo img{height:38px;width:auto;}
    .topbar-logo span{font-family:'Playfair Display',serif;font-size:18px;color:#fff;font-weight:700;}
    .topbar-spacer{flex:1;}
    .topbar-user{font-size:13px;color:rgba(255,255,255,.7);margin-right:8px;}
    .topbar-logout{display:flex;align-items:center;gap:6px;padding:7px 16px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:8px;color:rgba(255,255,255,.85);font-size:13px;font-weight:500;transition:background .2s;}
    .topbar-logout:hover{background:rgba(231,76,60,.3);}

    /* ── LAYOUT ── */
    .page{max-width:1320px;margin:0 auto;padding:32px 24px;}

    /* ── WELCOME ── */
    .welcome{background:linear-gradient(135deg,var(--gd),var(--gl));color:#fff;border-radius:16px;padding:28px 32px;margin-bottom:28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;box-shadow:0 4px 20px rgba(27,94,32,.2);}
    .welcome h1{font-family:'Playfair Display',serif;font-size:24px;color:#fff;margin-bottom:6px;}
    .welcome p{font-size:14px;color:rgba(255,255,255,.82);}
    .welcome-time{font-size:13px;color:rgba(255,255,255,.65);}

    /* ── STAT CARDS ── */
    .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px;}
    .stat-card{background:var(--white);border-radius:14px;padding:20px 22px;box-shadow:0 2px 10px rgba(0,0,0,.06);border-left:4px solid var(--gm);position:relative;overflow:hidden;}
    .stat-card .icon{position:absolute;right:16px;top:16px;width:40px;height:40px;background:var(--bg);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
    .stat-card .val{font-size:1.9rem;font-weight:700;color:var(--gd);line-height:1;margin-bottom:4px;}
    .stat-card .lbl{font-size:12px;color:#888;font-weight:400;}
    .stat-card.amber{border-left-color:var(--amber);}
    .stat-card.amber .val{color:#e65100;}
    .stat-card.red{border-left-color:#e53935;}
    .stat-card.red .val{color:#c62828;}
    .stat-card.blue{border-left-color:#1565c0;}
    .stat-card.blue .val{color:#1565c0;}

    /* ── NAV CARDS ── */
    .nav-title{font-size:13px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;}
    .nav-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;}
    .nav-card{background:var(--white);border-radius:14px;padding:22px 20px;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;flex-direction:column;align-items:flex-start;gap:8px;cursor:pointer;transition:transform .2s,box-shadow .2s;border:1.5px solid transparent;}
    .nav-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.1);border-color:#2e7d32;}
    .nav-card .nc-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:4px;}
    .nc-green{background:#e8f5e9;color:#1b5e20;}
    .nc-amber{background:#fff8e1;color:#e65100;}
    .nc-blue{background:#e3f2fd;color:#1565c0;}
    .nc-purple{background:#f3e5f5;color:#6a1b9a;}
    .nav-card h3{font-size:15px;font-weight:700;color:#222;}
    .nav-card p{font-size:12px;color:#888;font-weight:300;}
    .nc-badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;background:#fce4ec;color:#c62828;margin-top:2px;}

    /* ── TABLES ── */
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
    .card{background:var(--white);border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;}
    .card-hd{padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;}
    .card-hd h2{font-size:15px;font-weight:700;color:#222;}
    .card-hd a{font-size:12px;color:var(--gm);font-weight:600;}
    .card-hd a:hover{text-decoration:underline;}
    .mini-table{width:100%;border-collapse:collapse;}
    .mini-table td{padding:10px 16px;border-bottom:1px solid #f5f5f5;font-size:13px;vertical-align:middle;}
    .mini-table tr:last-child td{border-bottom:none;}
    .mini-table tr:hover td{background:#fafafa;}
    .status-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:5px;}
    .s-wait{background:#f39c12;}.s-ship{background:#8e44ad;}.s-done{background:#27ae60;}.s-huy{background:#e74c3c;}
    .low-badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;background:#fff3e0;color:#e65100;}
    .low-badge.critical{background:#fce4ec;color:#c62828;}

    @media(max-width:768px){.grid2{grid-template-columns:1fr;}.welcome{flex-direction:column;}}
  </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <a href="admin.php" class="topbar-logo">
    <img src="asset/images/logo.png" alt="Rice4U">
    <span>Rice4U Admin</span>
  </a>
  <div class="topbar-spacer"></div>
  <span class="topbar-user"><i class="fa-regular fa-circle-user" style="margin-right:5px"></i>Admin ID: <?= $_SESSION['ma_tk'] ?></span>
  <a href="dangxuat.php" class="topbar-logout">
    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
  </a>
</div>

<!-- PAGE -->
<div class="page">

  <!-- Welcome -->
  <div class="welcome">
    <div>
      <h1>🌾 Chào mừng trở lại, Admin!</h1>
      <p>Quản lý toàn bộ hoạt động cửa hàng gạo Rice4U từ đây.</p>
    </div>
    <div class="welcome-time"><?= date('d/m/Y — H:i') ?></div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="icon">🌾</div>
      <div class="val"><?= number_format($total_sp) ?></div>
      <div class="lbl">Sản phẩm đang bán</div>
    </div>
    <div class="stat-card amber">
      <div class="icon">📦</div>
      <div class="val"><?= number_format($total_dh) ?></div>
      <div class="lbl">Tổng đơn hàng</div>
    </div>
    <div class="stat-card red">
      <div class="icon">⏳</div>
      <div class="val"><?= number_format($dh_cho) ?></div>
      <div class="lbl">Chờ xác nhận</div>
    </div>
    <div class="stat-card" style="border-color:#8e44ad">
      <div class="icon">🚚</div>
      <div class="val" style="color:#8e44ad"><?= number_format($dh_giao) ?></div>
      <div class="lbl">Đang giao</div>
    </div>
    <div class="stat-card blue">
      <div class="icon">👥</div>
      <div class="val"><?= number_format($total_kh) ?></div>
      <div class="lbl">Khách hàng</div>
    </div>
    <div class="stat-card" style="border-color:#27ae60;grid-column:span 1">
      <div class="icon">💰</div>
      <div class="val" style="font-size:1.3rem;color:#27ae60"><?= number_format($doanh_thu,0,',','.') ?>₫</div>
      <div class="lbl">Tổng doanh thu</div>
    </div>
  </div>

  <!-- Navigation Cards -->
  <p class="nav-title">Chức năng quản lý</p>
  <div class="nav-grid">
    <a href="quanlydonhang.php" class="nav-card">
      <div class="nc-icon nc-amber">📦</div>
      <h3>Quản lý đơn hàng</h3>
      <p>Xem, cập nhật trạng thái đơn hàng</p>
      <?php if ($dh_cho > 0): ?><span class="nc-badge"><?= $dh_cho ?> chờ xác nhận</span><?php endif; ?>
    </a>
    <a href="quanly_sanpham.php" class="nav-card">
      <div class="nc-icon nc-green">🍚</div>
      <h3>Quản lý sản phẩm</h3>
      <p>Thêm, sửa, xóa sản phẩm gạo</p>
    </a>
    <a href="admin_account.php" class="nav-card">
      <div class="nc-icon nc-blue">👥</div>
      <h3>Quản lý tài khoản</h3>
      <p>Danh sách và xóa khách hàng</p>
    </a>
    <a href="dashboard.php" class="nav-card">
      <div class="nc-icon nc-purple">📊</div>
      <h3>Dashboard</h3>
      <p>Biểu đồ doanh thu và thống kê</p>
    </a>
  </div>

  <!-- Tables -->
  <div class="grid2">

    <!-- Đơn gần nhất -->
    <div class="card">
      <div class="card-hd">
        <h2>📋 Đơn hàng gần nhất</h2>
        <a href="quanlydonhang.php">Xem tất cả →</a>
      </div>
      <table class="mini-table">
        <?php foreach ($recent as $o):
          $sc = match($o['trang_thai_dh']) {
            'cho_xac_nhan' => 's-wait', 'dang_giao','dang_chuan_bi' => 's-ship',
            'da_giao' => 's-done', default => 's-huy'
          };
          $sn = ['cho_xac_nhan'=>'Chờ xác nhận','dang_chuan_bi'=>'Chuẩn bị','dang_giao'=>'Đang giao','da_giao'=>'Đã giao','da_huy'=>'Đã hủy'][$o['trang_thai_dh']] ?? $o['trang_thai_dh'];
        ?>
        <tr>
          <td>
            <code style="font-size:11px"><?= htmlspecialchars($o['id_dh']) ?></code><br>
            <small style="color:#888"><?= htmlspecialchars($o['ho_ten_nguoi_nhan']) ?></small>
          </td>
          <td style="text-align:right">
            <strong><?= number_format($o['tong_thanh_toan'],0,',','.') ?>₫</strong><br>
            <small><span class="status-dot <?= $sc ?>"></span><?= $sn ?></small>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recent)): ?>
        <tr><td colspan="2" style="text-align:center;color:#aaa;padding:24px">Chưa có đơn hàng</td></tr>
        <?php endif; ?>
      </table>
    </div>

    <!-- Sắp hết hàng -->
    <div class="card">
      <div class="card-hd">
        <h2>⚠️ Sản phẩm sắp hết</h2>
        <a href="quanly_sanpham.php">Quản lý →</a>
      </div>
      <table class="mini-table">
        <?php foreach ($low_stock as $sp): ?>
        <tr>
          <td>
            <code style="font-size:11px"><?= htmlspecialchars($sp['id_sp']) ?></code><br>
            <small><?= htmlspecialchars($sp['ten_sp']) ?></small>
          </td>
          <td style="text-align:right">
            <span class="low-badge <?= $sp['so_luong_ton'] < 20 ? 'critical' : '' ?>">
              <?= number_format($sp['so_luong_ton'],0,',','.') ?> kg
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($low_stock)): ?>
        <tr><td colspan="2" style="text-align:center;color:#aaa;padding:24px">Tồn kho ổn định ✅</td></tr>
        <?php endif; ?>
      </table>
    </div>

  </div>
</div>

</body>
</html>
