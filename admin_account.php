<?php
session_start();
require_once('./includes/db.php');
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') { header("Location: dangnhap.php"); exit(); }

// Xóa tài khoản
if (isset($_GET['delete_id'])) {
    $id   = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM khachhang WHERE id_kh = ?");
    $stmt->execute([$id]);
    header("Location: admin_account.php?msg=deleted"); exit();
}

$msg = '';
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'] === 'deleted' ? '✅ Đã xóa tài khoản thành công.' : '';
}

$stmt     = $pdo->query("SELECT kh.*, tk.ten_dang_nhap, tk.vai_tro FROM khachhang kh LEFT JOIN tai_khoan tk ON kh.ma_tk = tk.ma_tk ORDER BY kh.id_kh DESC");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title   = 'Quản lý tài khoản – Rice4U Admin';
$active_admin = 'taikhoan';
include 'includes/admin_topbar.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
<style>
  .filter-bar{background:#fff;border-radius:12px;padding:14px 20px;display:flex;gap:14px;flex-wrap:wrap;align-items:center;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,.06);}
  .filter-bar input{padding:8px 13px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;outline:none;font-family:inherit;min-width:240px;}
  .filter-bar input:focus{border-color:#2e7d32;}
  .admin-card table{width:100%;border-collapse:collapse;}
  .admin-card thead th{background:#1b5e20;color:#fff;padding:13px 12px;font-size:13px;font-weight:600;text-align:left;}
  .admin-card tbody td{padding:11px 12px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:middle;}
  .admin-card tbody tr:hover{background:#fafafa;}
  .admin-card tbody tr.hidden-row{display:none;}
  .avatar-sm{width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #eee;}
  .badge-role{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;}
  .role-admin{background:#e8f5e9;color:#1b5e20;}
  .role-kh{background:#e3f2fd;color:#1565c0;}
  .btn-del{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:#fce4ec;color:#c62828;text-decoration:none;border-radius:7px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:background .2s;}
  .btn-del:hover{background:#c62828;color:#fff;}
  .points-badge{display:inline-block;background:#fff8e1;color:#e65100;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;}
</style>

<div class="admin-page">
  <div class="admin-page-header">
    <div>
      <h1>👥 Quản lý tài khoản</h1>
      <p>Tổng: <strong><?= count($accounts) ?></strong> tài khoản khách hàng</p>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-success" style="margin-bottom:16px"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="filter-bar">
    <input type="text" id="filterTxt" placeholder="🔍 Tìm họ tên, email, số điện thoại..." oninput="filterTable()">
  </div>

  <div class="admin-card">
    <table id="accTable">
      <thead>
        <tr>
          <th>ID</th><th>Ảnh</th><th>Họ tên</th><th>Email</th>
          <th>SĐT</th><th>Địa chỉ</th><th>Điểm</th><th>Vai trò</th><th style="text-align:center">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($accounts as $acc):
          $avatar = !empty($acc['anh_dai_dien']) ? $acc['anh_dai_dien'] : '/rice4u/asset/images/avatar.jpg';
          $search_val = strtolower(($acc['ho_ten']??'').' '.($acc['email']??'').' '.($acc['so_dien_thoai']??''));
        ?>
        <tr data-search="<?= htmlspecialchars($search_val) ?>">
          <td><code style="font-size:12px"><?= $acc['id_kh'] ?></code></td>
          <td><img src="<?= htmlspecialchars($avatar) ?>" class="avatar-sm" alt="" onerror="this.src='/rice4u/asset/images/default.jpg'"></td>
          <td><strong><?= htmlspecialchars($acc['ho_ten']??'—') ?></strong></td>
          <td><?= htmlspecialchars($acc['email']??'—') ?></td>
          <td><?= htmlspecialchars($acc['so_dien_thoai']??'—') ?></td>
          <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($acc['dia_chi']??'—') ?></td>
          <td><span class="points-badge">⭐ <?= number_format($acc['diem_tich_luy']??0) ?></span></td>
          <td><span class="badge-role <?= ($acc['vai_tro']??'')==='admin'?'role-admin':'role-kh' ?>"><?= htmlspecialchars($acc['vai_tro']??'khách hàng') ?></span></td>
          <td style="text-align:center">
            <?php if (($acc['vai_tro']??'') !== 'admin'): ?>
              <a href="admin_account.php?delete_id=<?= $acc['id_kh'] ?>"
                 class="btn-del"
                 onclick="return confirm('Xóa tài khoản <?= htmlspecialchars(addslashes($acc['ho_ten']??'')) ?>?')">
                <i class="fa-solid fa-trash"></i> Xóa
              </a>
            <?php else: ?>
              <span style="color:#aaa;font-size:12px">Admin</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function filterTable() {
  const q = document.getElementById('filterTxt').value.toLowerCase().trim();
  document.querySelectorAll('#accTable tbody tr').forEach(tr => {
    tr.classList.toggle('hidden-row', q && !tr.dataset.search.includes(q));
  });
}
</script>
</body>
</html>
