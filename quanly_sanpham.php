<?php
session_start();
require_once("includes/db.php");
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') { header("Location: dangnhap.php"); exit(); }

$search      = trim($_GET['search']      ?? '');
$filter_loai = trim($_GET['filter_loai'] ?? '');
$sql    = "SELECT sp.*, lg.ten_loai, ha.duong_dan AS hinh_chinh
           FROM sanpham sp
           LEFT JOIN loaigao   lg ON sp.id_loai = lg.id_loai
           LEFT JOIN hinhanh_sp ha ON sp.id_sp  = ha.id_sp AND ha.la_anh_chinh = 1
           WHERE 1=1";
$params = [];
if ($search)      { $sql .= " AND sp.ten_sp LIKE ?"; $params[] = '%'.$search.'%'; }
if ($filter_loai) { $sql .= " AND sp.id_loai = ?";  $params[] = $filter_loai; }
$sql .= " ORDER BY sp.id_sp DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt_loai = $pdo->query("SELECT id_loai, ten_loai FROM loaigao WHERE trang_thai = 1 ORDER BY ten_loai");
$loai_list = $stmt_loai->fetchAll(PDO::FETCH_ASSOC);

$page_title   = 'Quản lý sản phẩm – Rice4U Admin';
$active_admin = 'sanpham';
include 'includes/admin_topbar.php';
?>
<style>
  .toolbar{background:#fff;border-radius:12px;padding:16px 20px;display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,.06);}
  .toolbar label{font-size:12px;color:#555;display:block;margin-bottom:5px;font-weight:600;}
  .toolbar input,.toolbar select{padding:9px 13px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;outline:none;font-family:inherit;min-width:190px;}
  .toolbar input:focus,.toolbar select:focus{border-color:#2e7d32;}
  .btn-search{padding:9px 20px;background:#2e7d32;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:14px;}
  .btn-add{background:#f9a825;color:#fff;padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:15px;}
  .admin-card table{width:100%;border-collapse:collapse;}
  .admin-card thead th{background:#1b5e20;color:#fff;padding:14px 12px;font-size:13px;font-weight:600;text-align:left;}
  .admin-card tbody td{padding:11px 12px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:middle;}
  .admin-card tbody tr:hover{background:#fafafa;}
  .product-img{width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid #eee;}
  .badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;}
  .badge-on{background:#e8f5e9;color:#2e7d32;}.badge-off{background:#fce4ec;color:#c62828;}
  .badge-star{background:#fff8e1;color:#e65100;}
  .btn-action{padding:5px 11px;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;transition:all .2s;margin-right:3px;}
  .btn-edit{background:#e3f2fd;color:#1565c0;}.btn-edit:hover{background:#1565c0;color:#fff;}
  .btn-del{background:#fce4ec;color:#e53935;}.btn-del:hover{background:#e53935;color:#fff;}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;align-items:center;justify-content:center;padding:20px;}
  .modal-overlay.show{display:flex;}
  .modal{background:#fff;border-radius:14px;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25);animation:mIn .25s ease;}
  @keyframes mIn{from{opacity:0;transform:translateY(-16px) scale(.97)}to{opacity:1;transform:none}}
  .modal-hd{padding:18px 22px 14px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:#fff;z-index:1;}
  .modal-hd h3{font-size:17px;color:#1b5e20;}
  .modal-close{background:none;border:none;font-size:24px;cursor:pointer;color:#888;line-height:1;}
  .modal-close:hover{color:#e53935;}
  .modal-body{padding:22px;}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px;}
  .form-grid .full{grid-column:1/-1;}
  .fg{display:flex;flex-direction:column;gap:5px;}
  .fg label{font-size:13px;font-weight:600;color:#444;}
  .fg input,.fg select,.fg textarea{padding:10px 13px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;font-family:inherit;outline:none;}
  .fg input:focus,.fg select:focus,.fg textarea:focus{border-color:#2e7d32;}
  .fg textarea{resize:vertical;min-height:76px;}
  .cb-group{display:flex;flex-wrap:wrap;gap:16px;padding:8px 0;}
  .cb-item{display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;}
  .cb-item input{width:15px;height:15px;accent-color:#2e7d32;}
  .img-preview{display:flex;flex-wrap:wrap;gap:8px;margin-top:7px;}
  .img-preview img{width:66px;height:66px;object-fit:cover;border-radius:7px;border:2px solid #eee;}
  .modal-ft{padding:14px 22px 18px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #eee;position:sticky;bottom:0;background:#fff;}
  .btn-save{padding:10px 28px;background:#2e7d32;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:15px;}
  .btn-save:hover{background:#1b5e20;}
  .btn-cancel{padding:10px 20px;background:#f5f5f5;color:#555;border:none;border-radius:8px;cursor:pointer;font-weight:600;}
  .req{color:#e53935;}
  .empty-state{text-align:center;padding:56px 20px;color:#aaa;}
</style>

<div class="admin-page">
  <div class="admin-page-header">
    <div>
      <h1>🍚 Quản lý sản phẩm</h1>
      <p>Tổng: <strong><?= count($products) ?></strong> sản phẩm<?= ($search||$filter_loai)?' (đang lọc)':'' ?></p>
    </div>
    <div class="header-actions">
      <button class="btn-add" onclick="openAdd()">+ Thêm sản phẩm</button>
    </div>
  </div>

  <div id="msg"></div>

  <form method="GET" class="toolbar">
    <div class="fg">
      <label>Tìm kiếm</label>
      <input type="text" name="search" placeholder="Tên sản phẩm..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="fg">
      <label>Loại gạo</label>
      <select name="filter_loai">
        <option value="">-- Tất cả --</option>
        <?php foreach ($loai_list as $l): ?>
          <option value="<?= htmlspecialchars($l['id_loai']) ?>" <?= $filter_loai===$l['id_loai']?'selected':'' ?>><?= htmlspecialchars($l['ten_loai']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn-search">🔍 Tìm</button>
    <?php if($search||$filter_loai): ?>
      <a href="quanly_sanpham.php" style="align-self:flex-end;padding:9px 13px;color:#666;font-size:13px;text-decoration:none;">✕ Xoá lọc</a>
    <?php endif; ?>
  </form>

  <div class="admin-card">
    <?php if (empty($products)): ?>
      <div class="empty-state"><div style="font-size:2.5rem;margin-bottom:12px;">🌾</div><p>Chưa có sản phẩm nào</p></div>
    <?php else: ?>
    <table>
      <thead>
        <tr><th>ID</th><th>Ảnh</th><th>Tên sản phẩm</th><th>Loại</th><th>Giá bán</th><th>Tồn kho</th><th>Trạng thái</th><th style="text-align:center">Thao tác</th></tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p):
          $h = !empty($p['hinh_chinh']) ? '/rice4u/'.ltrim($p['hinh_chinh'],'/') : '/rice4u/asset/images/default.jpg';
        ?>
        <tr>
          <td><code style="font-size:11px"><?= htmlspecialchars($p['id_sp']) ?></code></td>
          <td><img class="product-img" src="<?= $h ?>" alt="" onerror="this.src='/rice4u/asset/images/default.jpg'"></td>
          <td>
            <strong><?= htmlspecialchars($p['ten_sp']) ?></strong>
            <?php if(!empty($p['xuat_xu'])): ?><br><small style="color:#888"><?= htmlspecialchars($p['xuat_xu']) ?></small><?php endif; ?>
          </td>
          <td><?= htmlspecialchars($p['ten_loai']??'—') ?></td>
          <td><strong><?= number_format($p['gia_ban'],0,',','.') ?>₫</strong>
            <?php if($p['phan_tram_giam']>0): ?><br><span class="badge badge-star">-<?= $p['phan_tram_giam'] ?>%</span><?php endif; ?>
          </td>
          <td><?= number_format($p['so_luong_ton'],0,',','.') ?> kg</td>
          <td><span class="badge <?= $p['trang_thai']?'badge-on':'badge-off' ?>"><?= $p['trang_thai']?'Đang bán':'Ẩn' ?></span></td>
          <td style="text-align:center;white-space:nowrap">
            <button class="btn-action btn-edit" onclick="openEdit('<?= htmlspecialchars($p['id_sp']) ?>')">✏️ Sửa</button>
            <button class="btn-action btn-del"  onclick="delProduct('<?= htmlspecialchars($p['id_sp']) ?>','<?= htmlspecialchars(addslashes($p['ten_sp'])) ?>')">🗑️ Xóa</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-hd">
      <h3 id="mTitle">Thêm sản phẩm</h3>
      <button class="modal-close" onclick="closeModal()">×</button>
    </div>
    <form id="pForm" enctype="multipart/form-data" onsubmit="return false;">
      <input type="hidden" id="pId" name="id_sp">
      <div class="modal-body">
        <div class="form-grid">
          <div class="fg full"><label>Tên sản phẩm <span class="req">*</span></label><input type="text" id="fTen" name="ten_sp" placeholder="VD: Gạo ST25..." required></div>
          <div class="fg"><label>Loại gạo <span class="req">*</span></label>
            <select id="fLoai" name="id_loai" required><option value="">-- Chọn --</option>
              <?php foreach($loai_list as $l): ?><option value="<?= htmlspecialchars($l['id_loai']) ?>"><?= htmlspecialchars($l['ten_loai']) ?></option><?php endforeach; ?>
            </select></div>
          <div class="fg"><label>Xuất xứ</label><input type="text" id="fXuat" name="xuat_xu" placeholder="VD: Sóc Trăng..."></div>
          <div class="fg"><label>Giá bán (₫/kg) <span class="req">*</span></label><input type="number" id="fGia" name="gia_ban" placeholder="35000" min="0" step="500" required></div>
          <div class="fg"><label>Giá gốc (₫/kg)</label><input type="number" id="fGoc" name="gia_goc" placeholder="Để trống nếu không giảm" min="0" step="500"></div>
          <div class="fg"><label>Tồn kho (kg)</label><input type="number" id="fTon" name="so_luong_ton" value="0" min="0" step="0.5"></div>
          <div class="fg full"><label>Mô tả ngắn</label><textarea id="fMota" name="mo_ta_ngan" rows="2" placeholder="Mô tả hiển thị trên danh sách..."></textarea></div>
          <div class="fg full">
            <label>Ảnh sản phẩm <small style="color:#aaa">(JPG/PNG/WEBP – tối đa 5MB)</small></label>
            <input type="file" id="fAnh" name="hinh_anh[]" accept="image/*" multiple onchange="prevImg(this)">
            <div class="img-preview" id="imgPrev"></div>
            <div id="curImgs"></div>
          </div>
          <div class="fg full"><label>Tuỳ chọn</label>
            <div class="cb-group">
              <label class="cb-item"><input type="checkbox" id="cNoiBat" name="noi_bat" value="1"> ⭐ Nổi bật</label>
              <label class="cb-item"><input type="checkbox" id="cMoi"    name="hang_moi" value="1"> 🆕 Hàng mới</label>
              <label class="cb-item"><input type="checkbox" id="cChay"   name="ban_chay" value="1"> 🔥 Bán chạy</label>
              <label class="cb-item"><input type="checkbox" id="cTT"     name="trang_thai" value="1" checked> ✅ Đang bán</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-ft">
        <button type="button" class="btn-cancel" onclick="closeModal()">Huỷ</button>
        <button type="button" class="btn-save" id="bSave" onclick="saveProd()">💾 Lưu</button>
      </div>
    </form>
  </div>
</div>

<script>
const modal = document.getElementById('modal');
function openAdd() {
  document.getElementById('pId').value = '';
  document.getElementById('pForm').reset();
  document.getElementById('imgPrev').innerHTML = '';
  document.getElementById('curImgs').innerHTML = '';
  document.getElementById('cTT').checked = true;
  document.getElementById('mTitle').textContent = '➕ Thêm sản phẩm mới';
  modal.classList.add('show');
}
function openEdit(id) {
  document.getElementById('mTitle').textContent = '⏳ Đang tải...';
  modal.classList.add('show');
  fetch('api/sanpham_admin.php?action=get&id_sp=' + encodeURIComponent(id))
    .then(r=>r.json()).then(res => {
      if (!res.success) { showMsg('error', res.message); closeModal(); return; }
      const p = res.data;
      document.getElementById('mTitle').textContent = '✏️ Sửa: ' + p.ten_sp;
      document.getElementById('pId').value    = p.id_sp;
      document.getElementById('fTen').value   = p.ten_sp||'';
      document.getElementById('fLoai').value  = p.id_loai||'';
      document.getElementById('fXuat').value  = p.xuat_xu||'';
      document.getElementById('fGia').value   = p.gia_ban||'';
      document.getElementById('fGoc').value   = p.gia_goc||'';
      document.getElementById('fTon').value   = p.so_luong_ton||0;
      document.getElementById('fMota').value  = p.mo_ta_ngan||'';
      document.getElementById('cNoiBat').checked = p.noi_bat==1;
      document.getElementById('cMoi').checked    = p.hang_moi==1;
      document.getElementById('cChay').checked   = p.ban_chay==1;
      document.getElementById('cTT').checked     = p.trang_thai==1;
      document.getElementById('fAnh').value = '';
      document.getElementById('imgPrev').innerHTML = '';
      const ci = document.getElementById('curImgs');
      if (p.hinh_anh_list&&p.hinh_anh_list.length) {
        let h = '<span style="font-size:11px;color:#888;display:block;margin-top:6px">Ảnh hiện tại:</span><div class="img-preview">';
        p.hinh_anh_list.forEach(i => { h += `<img src="/rice4u/${i.duong_dan}" onerror="this.src='/rice4u/asset/images/default.jpg'">`; });
        ci.innerHTML = h + '</div>';
      } else ci.innerHTML = '';
    }).catch(e => { showMsg('error',e.message); closeModal(); });
}
function closeModal() { modal.classList.remove('show'); }
modal.addEventListener('click', e => { if(e.target===modal) closeModal(); });
function prevImg(input) {
  const w = document.getElementById('imgPrev'); w.innerHTML = '';
  Array.from(input.files).forEach(f => { if(!f.type.startsWith('image/')) return; const r=new FileReader(); r.onload=e=>{const i=document.createElement('img');i.src=e.target.result;w.appendChild(i);};r.readAsDataURL(f); });
}
function saveProd() {
  const id=document.getElementById('pId').value.trim(), ten=document.getElementById('fTen').value.trim(), loai=document.getElementById('fLoai').value, gia=document.getElementById('fGia').value;
  if (!ten)  return showMsg('error','⚠️ Vui lòng nhập tên sản phẩm');
  if (!loai) return showMsg('error','⚠️ Vui lòng chọn loại gạo');
  if (!gia||parseFloat(gia)<=0) return showMsg('error','⚠️ Vui lòng nhập giá bán hợp lệ');
  const d = new FormData(document.getElementById('pForm'));
  d.set('noi_bat',    document.getElementById('cNoiBat').checked?'1':'0');
  d.set('hang_moi',   document.getElementById('cMoi').checked   ?'1':'0');
  d.set('ban_chay',   document.getElementById('cChay').checked  ?'1':'0');
  d.set('trang_thai', document.getElementById('cTT').checked    ?'1':'0');
  const btn=document.getElementById('bSave'); btn.disabled=true; btn.textContent='⏳ Đang lưu...';
  fetch('api/sanpham_admin.php?action='+(id?'update':'add'),{method:'POST',body:d})
    .then(r=>r.json()).then(res=>{
      if(res.success){closeModal();showMsg('success',res.message);setTimeout(()=>location.reload(),1800);}
      else showMsg('error',res.message||'Có lỗi xảy ra');
    }).catch(e=>showMsg('error','❌ '+e.message))
    .finally(()=>{btn.disabled=false;btn.textContent='💾 Lưu';});
}
function delProduct(id,ten) {
  if(!confirm(`Xóa sản phẩm:\n"${ten}" (${id})?\n\nKhông thể hoàn tác!`)) return;
  const d=new FormData(); d.append('id_sp',id);
  fetch('api/sanpham_admin.php?action=delete',{method:'POST',body:d})
    .then(r=>r.json()).then(res=>{showMsg(res.success?'success':'error',res.message);if(res.success)setTimeout(()=>location.reload(),1600);})
    .catch(e=>showMsg('error',e.message));
}
function showMsg(t,m){const d=document.getElementById('msg');d.innerHTML=`<div class="alert alert-${t}">${m}</div>`;d.scrollIntoView({behavior:'smooth',block:'nearest'});if(t==='success')setTimeout(()=>d.innerHTML='',5000);}
</script>
</body></html>
