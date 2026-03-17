<?php
// ============================================================
// DASHBOARD ADMIN — rice4u
// ============================================================
session_start();
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: dangnhap.php"); exit();
}
require_once("includes/db.php");

// ── 1. THỐNG KÊ TỔNG QUAN ──────────────────────────────────
$total_dh    = $pdo->query("SELECT COUNT(*) FROM donhang")->fetchColumn();
$doanh_thu   = $pdo->query("SELECT IFNULL(SUM(tong_thanh_toan),0) FROM donhang WHERE trang_thai_dh != 'da_huy'")->fetchColumn();
$total_sp    = $pdo->query("SELECT COUNT(*) FROM sanpham WHERE trang_thai = 1")->fetchColumn();
$total_kh    = $pdo->query("SELECT COUNT(*) FROM khachhang")->fetchColumn();
$dh_cho_xn   = $pdo->query("SELECT COUNT(*) FROM donhang WHERE trang_thai_dh = 'cho_xac_nhan'")->fetchColumn();
$dh_dang_giao = $pdo->query("SELECT COUNT(*) FROM donhang WHERE trang_thai_dh = 'dang_giao'")->fetchColumn();
$dh_hom_nay  = $pdo->query("SELECT COUNT(*) FROM donhang WHERE DATE(ngay_dat) = CURDATE()")->fetchColumn();
$dt_hom_nay  = $pdo->query("SELECT IFNULL(SUM(tong_thanh_toan),0) FROM donhang WHERE DATE(ngay_dat) = CURDATE() AND trang_thai_dh != 'da_huy'")->fetchColumn();

// ── 2. DOANH THU 30 NGÀY (cho biểu đồ) ────────────────────
$stmt_dt = $pdo->query("
    SELECT DATE(ngay_dat) as ngay, SUM(tong_thanh_toan) as dt
    FROM donhang
    WHERE trang_thai_dh != 'da_huy'
      AND ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
    GROUP BY DATE(ngay_dat)
    ORDER BY ngay ASC
");
$dt_rows = $stmt_dt->fetchAll(PDO::FETCH_ASSOC);

// Tạo mảng đầy đủ 30 ngày (ngày không có đơn = 0)
$dt_map = [];
foreach ($dt_rows as $r) $dt_map[$r['ngay']] = (float)$r['dt'];
$chart_labels = $chart_data = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d/m', strtotime($d));
    $chart_data[]   = $dt_map[$d] ?? 0;
}

// ── 3. DOANH THU THEO THÁNG (12 tháng gần nhất) ────────────
$stmt_thang = $pdo->query("
    SELECT DATE_FORMAT(ngay_dat,'%Y-%m') as thang,
           SUM(tong_thanh_toan) as dt,
           COUNT(*) as so_don
    FROM donhang
    WHERE trang_thai_dh != 'da_huy'
      AND ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(ngay_dat,'%Y-%m')
    ORDER BY thang ASC
");
$thang_rows = $stmt_thang->fetchAll(PDO::FETCH_ASSOC);
$bar_labels = array_map(fn($r) => date('T/Y', strtotime($r['thang'].'-01')), $thang_rows);
$bar_dt     = array_map(fn($r) => (float)$r['dt'],     $thang_rows);
$bar_don    = array_map(fn($r) => (int)$r['so_don'],   $thang_rows);

// ── 4. ĐƠN HÀNG GẦN ĐÂY ───────────────────────────────────
$stmt_recent = $pdo->query("
    SELECT dh.id_dh, dh.ho_ten_nguoi_nhan, dh.so_dien_thoai,
           dh.tong_thanh_toan, dh.trang_thai_dh, dh.trang_thai_tt, dh.ngay_dat
    FROM donhang dh
    ORDER BY dh.ngay_dat DESC
    LIMIT 8
");
$recent_orders = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

// ── 5. TỒN KHO SẮP HẾT ────────────────────────────────────
$stmt_low = $pdo->query("
    SELECT id_sp, ten_sp, so_luong_ton, id_loai
    FROM sanpham
    WHERE trang_thai = 1 AND so_luong_ton < 100
    ORDER BY so_luong_ton ASC
    LIMIT 8
");
$low_stock = $stmt_low->fetchAll(PDO::FETCH_ASSOC);

// ── 6. PHÂN BỐ TRẠNG THÁI ĐƠN HÀNG ───────────────────────
$stmt_pie = $pdo->query("
    SELECT trang_thai_dh, COUNT(*) as cnt
    FROM donhang
    GROUP BY trang_thai_dh
");
$pie_map = ['cho_xac_nhan'=>0,'dang_chuan_bi'=>0,'dang_giao'=>0,'da_giao'=>0,'da_huy'=>0];
foreach ($stmt_pie->fetchAll(PDO::FETCH_ASSOC) as $r) {
    if (isset($pie_map[$r['trang_thai_dh']])) $pie_map[$r['trang_thai_dh']] = (int)$r['cnt'];
}

// ── HELPERS ────────────────────────────────────────────────
function fmt($n) { return number_format($n, 0, ',', '.'); }
function fmtMoney($n) { return number_format($n, 0, ',', '.') . '₫'; }
function statusInfo($s) {
    return match($s) {
        'cho_xac_nhan'  => ['Chờ xác nhận', '#f39c12', 'wait'],
        'dang_chuan_bi' => ['Đang chuẩn bị', '#2980b9', 'prep'],
        'dang_giao'     => ['Đang giao',     '#8e44ad', 'ship'],
        'da_giao'       => ['Đã giao',        '#27ae60', 'done'],
        'da_huy'        => ['Đã hủy',         '#e74c3c', 'cancel'],
        default         => [$s, '#888', 'other']
    };
}

$page_title   = 'Dashboard – Rice4U Admin';
$active_admin = 'dashboard';
include 'includes/admin_topbar.php';
?>
<style>
/* ── LAYOUT ── */
.dash { max-width:1400px; margin:0 auto; padding:26px 22px; }

/* ── HEADER BAR ── */
.dash-header {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:22px; flex-wrap:wrap; gap:10px;
}
.dash-header h1 {
  font-family:'Playfair Display',serif;
  font-size:22px; color:#1b5e20; margin:0;
}
.dash-header .sub { font-size:13px; color:#888; margin-top:3px; }


/* ── STAT CARDS ── */
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:22px; }
.stat-card {
  background:#fff; border-radius:14px; padding:20px 22px;
  box-shadow:0 2px 10px rgba(0,0,0,.06);
  border-left:4px solid #2e7d32;
  display:flex; align-items:center; gap:16px;
  transition:transform .2s, box-shadow .2s;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.1); }
.stat-icon {
  width:50px; height:50px; border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  font-size:22px; flex-shrink:0;
}
.stat-body { flex:1; min-width:0; }
.stat-val  { font-size:1.7rem; font-weight:700; line-height:1.1; color:#1b5e20; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.stat-lbl  { font-size:12px; color:#888; margin-top:3px; }
.stat-sub  { font-size:11px; color:#aaa; margin-top:2px; }

/* color variants */
.sc-green  { border-color:#2e7d32; } .sc-green  .stat-icon { background:#e8f5e9; }
.sc-amber  { border-color:#f9a825; } .sc-amber  .stat-icon { background:#fff8e1; } .sc-amber  .stat-val { color:#e65100; }
.sc-blue   { border-color:#1565c0; } .sc-blue   .stat-icon { background:#e3f2fd; } .sc-blue   .stat-val { color:#1565c0; }
.sc-purple { border-color:#6a1b9a; } .sc-purple .stat-icon { background:#f3e5f5; } .sc-purple .stat-val { color:#6a1b9a; }
.sc-red    { border-color:#e53935; } .sc-red    .stat-icon { background:#fce4ec; } .sc-red    .stat-val { color:#c62828; }
.sc-teal   { border-color:#00838f; } .sc-teal   .stat-icon { background:#e0f7fa; } .sc-teal   .stat-val { color:#006064; }
.sc-lime   { border-color:#558b2f; } .sc-lime   .stat-icon { background:#f1f8e9; } .sc-lime   .stat-val { color:#33691e; }
.sc-pink   { border-color:#ad1457; } .sc-pink   .stat-icon { background:#fce4ec; } .sc-pink   .stat-val { color:#880e4f; }

/* stats row 2 */
.stats-row-2 { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:22px; }

/* ── CARD ── */
.card {
  background:#fff; border-radius:14px;
  box-shadow:0 2px 10px rgba(0,0,0,.06); overflow:hidden;
}
.card-hd {
  padding:16px 20px 12px; border-bottom:1px solid #f0f0f0;
  display:flex; justify-content:space-between; align-items:center; gap:10px;
}
.card-hd h2 { font-size:15px; font-weight:700; color:#222; margin:0; }
.card-hd a  { font-size:12px; color:#2e7d32; font-weight:600; text-decoration:none; white-space:nowrap; }
.card-hd a:hover { text-decoration:underline; }

/* Tab switcher */
.tab-sw { display:flex; gap:4px; }
.tab-btn {
  padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600;
  border:1.5px solid #e0e0e0; background:#fff; cursor:pointer; color:#666;
  transition:all .18s;
}
.tab-btn.active { background:#1b5e20; color:#fff; border-color:#1b5e20; }

/* ── CHARTS GRID ── */
.charts-row { display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:20px; }
.chart-wrap { padding:18px 20px 16px; }
.chart-wrap canvas { max-height:260px; }

/* ── TABLES GRID ── */
.tables-row { display:grid; grid-template-columns:3fr 2fr; gap:16px; }

/* mini table */
.mini-table { width:100%; border-collapse:collapse; }
.mini-table thead th {
  background:#f8faf8; padding:10px 14px;
  font-size:12px; font-weight:700; color:#555;
  text-align:left; border-bottom:1.5px solid #eee;
}
.mini-table tbody td {
  padding:10px 14px; border-bottom:1px solid #f5f5f5;
  font-size:13px; vertical-align:middle;
}
.mini-table tbody tr:last-child td { border-bottom:none; }
.mini-table tbody tr:hover td { background:#fafafa; }

/* status pill */
.status-pill {
  display:inline-block; padding:3px 9px; border-radius:20px;
  font-size:11px; font-weight:700; white-space:nowrap;
}

/* stock bar */
.stock-bar-wrap { display:flex; align-items:center; gap:8px; }
.stock-bar-bg { flex:1; height:7px; background:#f0f0f0; border-radius:4px; overflow:hidden; min-width:60px; }
.stock-bar { height:100%; border-radius:4px; transition:width .4s; }

/* empty */
.empty-row td { text-align:center; color:#bbb; padding:32px!important; font-size:13px; }

/* today highlight */
.today-box {
  background:linear-gradient(135deg,#1b5e20,#2e7d32);
  color:#fff; border-radius:14px; padding:20px 24px;
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:22px; flex-wrap:wrap; gap:14px;
  box-shadow:0 4px 18px rgba(27,94,32,.22);
}
.today-box .t-item { text-align:center; }
.today-box .t-val  { font-size:1.8rem; font-weight:700; color:#fff; line-height:1.1; }
.today-box .t-lbl  { font-size:11px; color:rgba(255,255,255,.7); margin-top:3px; text-transform:uppercase; letter-spacing:.06em; }
.today-box .divider { width:1px; height:48px; background:rgba(255,255,255,.2); }

@media(max-width:1100px){
  .stats-row { grid-template-columns:repeat(2,1fr); }
  .stats-row-2 { grid-template-columns:repeat(2,1fr); }
  .charts-row { grid-template-columns:1fr; }
  .tables-row { grid-template-columns:1fr; }
}
@media(max-width:640px){
  .stats-row { grid-template-columns:1fr 1fr; }
  .today-box { flex-direction:column; }
}
</style>

<div class="dash">

  <!-- Header -->
  <div class="dash-header">
    <div>
      <h1>📊 Dashboard</h1>
      <div class="sub">Tổng quan hoạt động cửa hàng Rice4U</div>
    </div>


  </div>

  <!-- Hôm nay -->
  <div class="today-box">
    <div class="t-item">
      <div class="t-val"><?= fmt($dh_hom_nay) ?></div>
      <div class="t-lbl">Đơn hàng hôm nay</div>
    </div>
    <div class="divider"></div>
    <div class="t-item">
      <div class="t-val"><?= fmtMoney($dt_hom_nay) ?></div>
      <div class="t-lbl">Doanh thu hôm nay</div>
    </div>
    <div class="divider"></div>
    <div class="t-item">
      <div class="t-val"><?= fmt($dh_cho_xn) ?></div>
      <div class="t-lbl">Chờ xác nhận</div>
    </div>
    <div class="divider"></div>
    <div class="t-item">
      <div class="t-val"><?= fmt($dh_dang_giao) ?></div>
      <div class="t-lbl">Đang giao</div>
    </div>
    <div class="divider" style="display:none" class="d-md"></div>
    <a href="quanlydonhang.php" style="background:rgba(255,255,255,.18);color:#fff;padding:10px 22px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;border:1.5px solid rgba(255,255,255,.3);transition:background .2s;"
       onmouseover="this.style.background='rgba(255,255,255,.28)'"
       onmouseout="this.style.background='rgba(255,255,255,.18)'">
      Xem đơn hàng →
    </a>
  </div>

  <!-- Stats row 1 -->
  <div class="stats-row">
    <div class="stat-card sc-amber">
      <div class="stat-icon">💰</div>
      <div class="stat-body">
        <div class="stat-val" style="font-size:1.3rem;"><?= fmtMoney($doanh_thu) ?></div>
        <div class="stat-lbl">Tổng doanh thu</div>
        <div class="stat-sub">Trừ đơn đã hủy</div>
      </div>
    </div>
    <div class="stat-card sc-blue">
      <div class="stat-icon">📦</div>
      <div class="stat-body">
        <div class="stat-val"><?= fmt($total_dh) ?></div>
        <div class="stat-lbl">Tổng đơn hàng</div>
        <div class="stat-sub">Toàn thời gian</div>
      </div>
    </div>
    <div class="stat-card sc-green">
      <div class="stat-icon">🌾</div>
      <div class="stat-body">
        <div class="stat-val"><?= fmt($total_sp) ?></div>
        <div class="stat-lbl">Sản phẩm đang bán</div>
        <div class="stat-sub">Đang hiển thị</div>
      </div>
    </div>
    <div class="stat-card sc-purple">
      <div class="stat-icon">👥</div>
      <div class="stat-body">
        <div class="stat-val"><?= fmt($total_kh) ?></div>
        <div class="stat-lbl">Khách hàng</div>
        <div class="stat-sub">Tài khoản đã đăng ký</div>
      </div>
    </div>
  </div>

  <!-- Stats row 2 - trạng thái đơn -->
  <div class="stats-row-2">
    <?php
      $st_cards = [
        ['cho_xac_nhan', '⏳', 'Chờ xác nhận', '#f39c12', '#fff8e1'],
        ['dang_chuan_bi','📋', 'Đang chuẩn bị','#2980b9', '#e3f2fd'],
        ['dang_giao',    '🚚', 'Đang giao',    '#8e44ad', '#f3e5f5'],
        ['da_giao',      '✅', 'Đã giao',       '#27ae60', '#e8f5e9'],
      ];
      foreach ($st_cards as [$key, $icon, $label, $color, $bg]):
        $cnt = $pie_map[$key] ?? 0;
    ?>
    <div class="stat-card" style="border-color:<?= $color ?>">
      <div class="stat-icon" style="background:<?= $bg ?>;font-size:20px"><?= $icon ?></div>
      <div class="stat-body">
        <div class="stat-val" style="color:<?= $color ?>"><?= fmt($cnt) ?></div>
        <div class="stat-lbl"><?= $label ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Charts -->
  <div class="charts-row">

    <!-- Biểu đồ doanh thu -->
    <div class="card">
      <div class="card-hd">
        <h2>📈 Doanh thu</h2>
        <div class="tab-sw">
          <button class="tab-btn active" id="btn30" onclick="switchChart('30')">30 ngày</button>
          <button class="tab-btn"        id="btn12" onclick="switchChart('12')">12 tháng</button>
        </div>
      </div>
      <div class="chart-wrap">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <!-- Biểu đồ pie trạng thái -->
    <div class="card">
      <div class="card-hd"><h2>🍩 Trạng thái đơn</h2></div>
      <div class="chart-wrap" style="display:flex;align-items:center;justify-content:center;padding:24px">
        <canvas id="pieChart" style="max-height:220px;max-width:220px"></canvas>
      </div>
    </div>

  </div>

  <!-- Tables -->
  <div class="tables-row">

    <!-- Đơn hàng gần đây -->
    <div class="card">
      <div class="card-hd">
        <h2>🧾 Đơn hàng gần đây</h2>
        <a href="quanlydonhang.php">Xem tất cả →</a>
      </div>
      <table class="mini-table">
        <thead>
          <tr>
            <th>Mã đơn</th><th>Khách hàng</th><th>Tổng tiền</th>
            <th>Thanh toán</th><th>Trạng thái</th><th>Ngày đặt</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recent_orders)): ?>
            <tr class="empty-row"><td colspan="6">Chưa có đơn hàng nào</td></tr>
          <?php else: ?>
          <?php foreach ($recent_orders as $o):
            [$sn, $sc] = statusInfo($o['trang_thai_dh']);
            $tt_color = $o['trang_thai_tt'] === 'da_tt' ? '#27ae60' : '#e74c3c';
            $tt_label = match($o['trang_thai_tt'] ?? '') { 'da_tt'=>'Đã TT', 'hoan_tien'=>'Hoàn tiền', default=>'Chưa TT' };
          ?>
          <tr>
            <td><a href="chitietdonhang_admin.php?id=<?= urlencode($o['id_dh']) ?>" style="color:#1b5e20;font-weight:600;font-size:12px;text-decoration:none;"><?= htmlspecialchars($o['id_dh']) ?></a></td>
            <td>
              <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($o['ho_ten_nguoi_nhan']) ?></div>
              <div style="font-size:11px;color:#aaa"><?= htmlspecialchars($o['so_dien_thoai']??'') ?></div>
            </td>
            <td><strong style="color:#1b5e20"><?= fmtMoney($o['tong_thanh_toan']) ?></strong></td>
            <td><span class="status-pill" style="background:<?= $o['trang_thai_tt']==='da_tt'?'#e8f5e9':'#fce4ec' ?>;color:<?= $tt_color ?>"><?= $tt_label ?></span></td>
            <td><span class="status-pill" style="background:<?= $sc ?>22;color:<?= $sc ?>"><?= $sn ?></span></td>
            <td style="font-size:12px;color:#888;white-space:nowrap"><?= date('d/m H:i', strtotime($o['ngay_dat'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Tồn kho sắp hết -->
    <div class="card">
      <div class="card-hd">
        <h2>⚠️ Sắp hết hàng</h2>
        <a href="quanly_sanpham.php">Quản lý →</a>
      </div>
      <table class="mini-table">
        <thead>
          <tr><th>Sản phẩm</th><th>Tồn kho</th></tr>
        </thead>
        <tbody>
          <?php if (empty($low_stock)): ?>
            <tr class="empty-row"><td colspan="2">Tồn kho ổn định ✅</td></tr>
          <?php else: ?>
          <?php foreach ($low_stock as $sp):
            $pct     = min(100, ($sp['so_luong_ton'] / 100) * 100);
            $bar_clr = $sp['so_luong_ton'] < 20 ? '#e53935' : ($sp['so_luong_ton'] < 50 ? '#f9a825' : '#2e7d32');
          ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:13px;line-height:1.3"><?= htmlspecialchars($sp['ten_sp']) ?></div>
              <div style="font-size:11px;color:#aaa"><?= htmlspecialchars($sp['id_sp']) ?></div>
            </td>
            <td>
              <div style="font-size:13px;font-weight:700;color:<?= $bar_clr ?>;margin-bottom:5px">
                <?= fmt($sp['so_luong_ton']) ?> kg
              </div>
              <div class="stock-bar-bg">
                <div class="stock-bar" style="width:<?= $pct ?>%;background:<?= $bar_clr ?>"></div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const data30  = { labels: <?= json_encode($chart_labels) ?>, data: <?= json_encode($chart_data) ?> };
const data12  = { labels: <?= json_encode($bar_labels) ?>,   data: <?= json_encode($bar_dt) ?>,   don: <?= json_encode($bar_don) ?> };
const pieData = <?= json_encode(array_values($pie_map)) ?>;
const pieLabels = ['Chờ xác nhận','Đang chuẩn bị','Đang giao','Đã giao','Đã hủy'];
const pieColors = ['#f39c12','#2980b9','#8e44ad','#27ae60','#e74c3c'];

// ── Biểu đồ doanh thu ──
const ctx = document.getElementById('revenueChart').getContext('2d');
let revenueChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: data30.labels,
    datasets: [{
      label: 'Doanh thu (₫)',
      data: data30.data,
      borderColor: '#2e7d32',
      backgroundColor: 'rgba(46,125,50,.08)',
      borderWidth: 2.5,
      pointBackgroundColor: '#2e7d32',
      pointRadius: 3,
      pointHoverRadius: 6,
      tension: 0.35,
      fill: true
    }]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ' ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + '₫'
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 11 } } },
      y: {
        beginAtZero: true,
        grid: { color: '#f0f0f0' },
        ticks: {
          font: { size: 11 },
          callback: v => {
            if (v >= 1e6) return (v/1e6).toFixed(1) + 'M';
            if (v >= 1e3) return (v/1e3).toFixed(0) + 'K';
            return v;
          }
        }
      }
    }
  }
});

// ── Switch tab ──
let mode = '30';
function switchChart(m) {
  mode = m;
  document.getElementById('btn30').classList.toggle('active', m==='30');
  document.getElementById('btn12').classList.toggle('active', m==='12');

  if (m === '30') {
    revenueChart.config.type = 'line';
    revenueChart.data.labels = data30.labels;
    revenueChart.data.datasets = [{
      label: 'Doanh thu (₫)', data: data30.data,
      borderColor: '#2e7d32', backgroundColor: 'rgba(46,125,50,.08)',
      borderWidth: 2.5, pointBackgroundColor: '#2e7d32',
      pointRadius: 3, pointHoverRadius: 6, tension: 0.35, fill: true
    }];
  } else {
    revenueChart.config.type = 'bar';
    revenueChart.data.labels = data12.labels;
    revenueChart.data.datasets = [
      {
        label: 'Doanh thu (₫)', data: data12.data,
        backgroundColor: 'rgba(46,125,50,.75)',
        borderColor: '#2e7d32', borderWidth: 1.5,
        borderRadius: 6, yAxisID: 'y'
      },
      {
        label: 'Số đơn', data: data12.don,
        type: 'line',
        borderColor: '#f9a825', backgroundColor: 'rgba(249,168,37,.1)',
        borderWidth: 2, pointBackgroundColor: '#f9a825',
        pointRadius: 4, tension: 0.3, fill: false, yAxisID: 'y2'
      }
    ];
    revenueChart.options.scales.y2 = {
      position: 'right', beginAtZero: true,
      grid: { drawOnChartArea: false },
      ticks: { font: { size: 11 }, stepSize: 1 }
    };
  }
  revenueChart.update();
}

// ── Biểu đồ Pie ──
const ctxPie = document.getElementById('pieChart').getContext('2d');
new Chart(ctxPie, {
  type: 'doughnut',
  data: {
    labels: pieLabels,
    datasets: [{ data: pieData, backgroundColor: pieColors, borderWidth: 3, borderColor: '#fff', hoverOffset: 6 }]
  },
  options: {
    responsive: true,
    cutout: '62%',
    plugins: {
      legend: {
        position: 'bottom',
        labels: { font: { size: 11 }, padding: 10, usePointStyle: true, pointStyleWidth: 8 }
      },
      tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw + ' đơn' } }
    }
  }
});
</script>
</body>
</html>