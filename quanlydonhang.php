<?php
session_start();
require_once("includes/db.php");
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') { header("Location: dangnhap.php"); exit(); }

$sql  = "SELECT dh.*, kh.ho_ten AS ten_kh
         FROM donhang dh
         LEFT JOIN khachhang kh ON dh.id_kh = kh.id_kh
         ORDER BY dh.ngay_dat DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title   = 'Quản lý đơn hàng – Rice4U Admin';
$active_admin = 'donhang';
include 'includes/admin_topbar.php';

function statusLabel($s) {
    $map = ['cho_xac_nhan'=>['Chờ xác nhận','#f39c12'],'dang_chuan_bi'=>['Đang chuẩn bị','#2980b9'],'dang_giao'=>['Đang giao','#8e44ad'],'da_giao'=>['Đã giao','#27ae60'],'da_huy'=>['Đã hủy','#e74c3c']];
    $v = $map[$s] ?? [$s,'#888'];
    return "<span style='color:{$v[1]};font-weight:600;'>{$v[0]}</span>";
}
function ttLabel($s) {
    if ($s==='da_tt') return "<span style='color:#27ae60;font-weight:600'>Đã TT</span>";
    if ($s==='hoan_tien') return "<span style='color:#e67e22;font-weight:600'>Hoàn tiền</span>";
    return "<span style='color:#e74c3c;font-weight:600'>Chưa TT</span>";
}
?>
<style>
  .filter-bar{background:#fff;border-radius:12px;padding:14px 20px;display:flex;gap:14px;flex-wrap:wrap;align-items:center;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,.06);}
  .filter-bar input{padding:8px 13px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;outline:none;font-family:inherit;min-width:220px;}
  .filter-bar input:focus{border-color:#2e7d32;}
  .filter-bar select{padding:8px 13px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;outline:none;font-family:inherit;}
  .admin-card table{width:100%;border-collapse:collapse;}
  .admin-card thead th{background:#1b5e20;color:#fff;padding:13px 12px;font-size:13px;font-weight:600;text-align:left;}
  .admin-card tbody td{padding:11px 12px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:middle;}
  .admin-card tbody tr:hover{background:#fafafa;}
  .admin-card tbody tr.hidden-row{display:none;}
  .btn-view{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#e8f5e9;color:#1b5e20;text-decoration:none;border-radius:8px;font-size:12px;font-weight:600;transition:background .2s;}
  .btn-view:hover{background:#1b5e20;color:#fff;}
  .status-select{padding:5px 10px;border:1.5px solid #ddd;border-radius:8px;font-size:12px;cursor:pointer;outline:none;}
  .status-select:focus{border-color:#2e7d32;}
  .summary-bar{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:18px;}
  .summary-card{background:#fff;border-radius:12px;padding:16px 22px;flex:1;min-width:140px;box-shadow:0 2px 8px rgba(0,0,0,.06);border-left:4px solid #2e7d32;}
  .summary-card .val{font-size:1.6rem;font-weight:700;color:#1b5e20;line-height:1;}
  .summary-card .lbl{font-size:12px;color:#888;margin-top:4px;}
</style>

<div class="admin-page">
  <div class="admin-page-header">
    <div>
      <h1>📦 Quản lý đơn hàng</h1>
      <p>Tổng cộng <strong><?= count($orders) ?></strong> đơn hàng</p>
    </div>
  </div>

  <!-- Summary -->
  <?php
    $cnt = ['cho_xac_nhan'=>0,'dang_giao'=>0,'da_giao'=>0,'da_huy'=>0];
    $tongtt = 0;
    foreach ($orders as $o) {
      $s = $o['trang_thai_dh'] ?? 'cho_xac_nhan';
      if (isset($cnt[$s])) $cnt[$s]++;
      if ($o['trang_thai_dh'] !== 'da_huy') $tongtt += (float)$o['tong_thanh_toan'];
    }
  ?>
  <div class="summary-bar">
    <div class="summary-card" style="border-color:#f39c12">
      <div class="val" style="color:#f39c12"><?= $cnt['cho_xac_nhan'] ?></div>
      <div class="lbl">Chờ xác nhận</div>
    </div>
    <div class="summary-card" style="border-color:#8e44ad">
      <div class="val" style="color:#8e44ad"><?= $cnt['dang_giao'] ?></div>
      <div class="lbl">Đang giao</div>
    </div>
    <div class="summary-card" style="border-color:#27ae60">
      <div class="val" style="color:#27ae60"><?= $cnt['da_giao'] ?></div>
      <div class="lbl">Đã giao</div>
    </div>
    <div class="summary-card" style="border-color:#e74c3c">
      <div class="val" style="color:#e74c3c"><?= $cnt['da_huy'] ?></div>
      <div class="lbl">Đã hủy</div>
    </div>
    <div class="summary-card" style="border-color:#2e7d32;flex:2">
      <div class="val"><?= number_format($tongtt,0,',','.') ?>₫</div>
      <div class="lbl">Tổng doanh thu (trừ đơn hủy)</div>
    </div>
  </div>

  <!-- Filter -->
  <div class="filter-bar">
    <input type="text" id="filterText" placeholder="🔍 Tìm mã đơn, khách hàng..." oninput="filterTable()">
    <select id="filterStatus" onchange="filterTable()">
      <option value="">Tất cả trạng thái</option>
      <option value="cho_xac_nhan">Chờ xác nhận</option>
      <option value="dang_chuan_bi">Đang chuẩn bị</option>
      <option value="dang_giao">Đang giao</option>
      <option value="da_giao">Đã giao</option>
      <option value="da_huy">Đã hủy</option>
    </select>
  </div>

  <div class="admin-card">
    <table id="ordersTable">
      <thead>
        <tr>
          <th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th>
          <th>Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th>Cập nhật</th><th>Chi tiết</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr data-status="<?= htmlspecialchars($o['trang_thai_dh']??'') ?>"
            data-search="<?= strtolower(htmlspecialchars($o['id_dh'].''.($o['ten_kh']??'').($o['ho_ten_nguoi_nhan']??''))) ?>">
          <td><code style="font-size:12px"><?= htmlspecialchars($o['id_dh']) ?></code></td>
          <td>
            <strong><?= htmlspecialchars($o['ho_ten_nguoi_nhan']) ?></strong>
            <?php if(!empty($o['so_dien_thoai'])): ?><br><small style="color:#888"><?= htmlspecialchars($o['so_dien_thoai']) ?></small><?php endif; ?>
          </td>
          <td><?= date('d/m/Y H:i', strtotime($o['ngay_dat'])) ?></td>
          <td><strong style="color:#1b5e20"><?= number_format($o['tong_thanh_toan'],0,',','.') ?>₫</strong></td>
          <td><?= ttLabel($o['trang_thai_tt']??'chua_tt') ?></td>
          <td><?= statusLabel($o['trang_thai_dh']??'cho_xac_nhan') ?></td>
          <td>
            <form method="POST" action="update_trangthaiDH_admin.php" style="display:flex;gap:6px;align-items:center">
              <input type="hidden" name="id_dh" value="<?= htmlspecialchars($o['id_dh']) ?>">
              <select name="trang_thai_dh" class="status-select" onchange="this.form.submit()">
                <?php
                  $statuses = ['cho_xac_nhan'=>'Chờ xác nhận','dang_chuan_bi'=>'Đang chuẩn bị','dang_giao'=>'Đang giao','da_giao'=>'Đã giao','da_huy'=>'Đã hủy'];
                  foreach ($statuses as $k => $v):
                ?>
                  <option value="<?= $k ?>" <?= ($o['trang_thai_dh']??'')===$k?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td><a href="chitietdonhang_admin.php?id=<?= urlencode($o['id_dh']) ?>" class="btn-view">👁 Xem</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function filterTable() {
  const txt    = document.getElementById('filterText').value.toLowerCase().trim();
  const status = document.getElementById('filterStatus').value;
  document.querySelectorAll('#ordersTable tbody tr').forEach(tr => {
    const matchTxt    = !txt    || tr.dataset.search.includes(txt);
    const matchStatus = !status || tr.dataset.status === status;
    tr.classList.toggle('hidden-row', !(matchTxt && matchStatus));
  });
}
</script>
</body>
</html>
