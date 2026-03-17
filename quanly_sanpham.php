<?php
session_start();
require_once("includes/db.php");

/* Kiểm tra vai trò Admin */
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: dangnhap.php");
    exit();
}

/* Lấy danh sách sản phẩm */
$search = trim($_GET['search'] ?? '');
$filter_loai = trim($_GET['filter_loai'] ?? '');

$sql = "SELECT sp.*, lg.ten_loai FROM sanpham sp 
        LEFT JOIN loaigao lg ON sp.id_loai = lg.id_loai 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND sp.ten_sp LIKE ?";
    $params[] = '%' . $search . '%';
}

if ($filter_loai) {
    $sql .= " AND sp.id_loai = ?";
    $params[] = $filter_loai;
}

$sql .= " ORDER BY sp.id_sp DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Lấy danh sách loại gạo */
$sql_loai = "SELECT id_loai, ten_loai FROM loaigao WHERE trang_thai = 1";
$stmt_loai = $pdo->query($sql_loai);
$loai_list = $stmt_loai->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>

    <style>
        body{
            font-family: 'Be Vietnam Pro', Arial, sans-serif;
            background:#f6f9f6;
            margin:0;
            font-size:16px; 
            color: #237227;
        }

        .sidebar{
            width:230px;
            height:100vh;
            background:var(--green-dark);
            position:fixed;
            color: #237227;
            padding-top:10px;
            box-shadow:3px 0 10px rgba(0,0,0,0.08);
        }

        .sidebar h2{
            text-align:center;
            padding:20px 0;
            font-family:'Playfair Display', serif;
            letter-spacing:1px;
        }

        .sidebar a{
            display:block;
            padding:12px 20px;
            color: #237227;
            text-decoration:none;
            font-size:14px;
            transition:all 0.25s;
            border-left:3px solid transparent;
        }

        .sidebar a:hover, .sidebar a.active{
            background:rgba(255,255,255,0.1);
            border-left:3px solid var(--amber);
        }

        .content{
            margin-left:230px;
            padding:25px;
        }

        .logo{
            text-align:center;
            padding:15px 10px;
        }

        .logo img{
            width:150px;
            height:auto;
        }

        .header{
            background:linear-gradient(90deg,var(--green-dark),var(--green-mid));
            color: white;
            padding:16px 20px;
            border-radius:8px;
            font-weight:600;
            margin-bottom:20px;
            box-shadow:0 3px 10px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2{
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
            color: white;
        }

        .add-button{
            background: var(--amber);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.25s;
        }

        .add-button:hover{
            background: #f4b942;
            transform: scale(1.05);
        }

        .table-card{
            background:#fff;
            border-radius:14px;
            box-shadow:0 3px 14px rgba(0,0,0,.08);
            overflow:hidden;
            margin-top: 20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            background:var(--green-dark);
            color:white;
            padding:16px 12px;
            font-weight:600;
            font-size:15px;
            text-align: left;
        }

        td{
            padding:12px;
            border-bottom:1px solid #f0f0f0;
            font-size:14px;
        }

        tr:hover{
            background:#fafafa;
        }

        .btn{
            display:inline-block;
            padding:6px 12px;
            background:#FFD786;
            color:white;
            text-decoration:none;
            border-radius:6px;
            font-size:13px;
            font-weight:600;
            transition:.25s;
            border: none;
            cursor: pointer;
        }

        .btn:hover{
            background:#f4b942;
            transform:scale(1.05);
        }

        .btn-delete{
            background: #e74c3c;
        }

        .btn-delete:hover{
            background: #c0392b;
        }

        .btn-small{
            padding: 6px 10px;
            font-size: 12px;
            margin-right: 5px;
        }

        .status-active{
            color: #27ae60;
            font-weight: 600;
        }

        .status-inactive{
            color: #e74c3c;
            font-weight: 600;
        }

        .image-thumb{
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
        }

        .modal.show {
            display: block;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            background: linear-gradient(90deg,var(--green-dark),var(--green-mid));
            color: white;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        .close {
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }

        .close:hover {
            color: #e8e8e8;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #237227;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--green-dark);
            box-shadow: 0 0 5px rgba(35, 114, 39, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #ddd;
            text-align: right;
            background: #f9f9f9;
        }

        .btn-cancel {
            background: #bdc3c7;
            margin-right: 10px;
        }

        .btn-cancel:hover {
            background: #95a5a6;
        }

        .btn-submit {
            background: var(--green-dark);
            color: white;
        }

        .btn-submit:hover {
            background: var(--green-mid);
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert-success {
            background: #d5f4e6;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }

        .alert-error {
            background: #fadbd8;
            color: #c0392b;
            border-left: 4px solid #c0392b;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .search-filter {
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .search-filter .form-group {
            margin-bottom: 0;
            flex: 1;
            min-width: 200px;
        }

        .search-filter input,
        .search-filter select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .search-filter .btn-search {
            padding: 10px 20px;
            background: var(--green-dark);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.25s;
        }

        .search-filter .btn-search:hover {
            background: var(--green-mid);
        }

        .image-preview-item {
            position: relative;
            width: 80px;
            height: 80px;
            border: 2px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
            background: #f9f9f9;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-item .remove-image {
            position: absolute;
            top: 0;
            right: 0;
            background: rgba(231, 76, 60, 0.9);
            color: white;
            border: none;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview-item .remove-image:hover {
            background: rgba(192, 57, 43, 1);
        }
    </style>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/rice4u/asset/header.css">
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <a href="admin.php" style="text-decoration: none; color: inherit;">
                <img src="asset/images/logo.png" alt="Rice4U">
            </a>
        </div>
        <a href="admin.php">📊 Dashboard</a>
        <a href="quanlydonhang.php">📦 Quản lý đơn hàng</a>
        <a href="quanly_sanpham.php" class="active">🍚 Quản lý sản phẩm</a>
        <a href="admin_account.php">👥 Quản lý tài khoản</a>
        <a href="#">🏷️ Quản lý loại gạo</a>
        <a href="dangxuat.php">🚪 Đăng xuất</a>
    </div>

    <div class="content">
        <div class="header">
            <div>
                <h2>Quản lý sản phẩm</h2>
                <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">
                    Tổng: <strong><?= count($products) ?></strong> sản phẩm 
                    <?php if ($search || $filter_loai): ?>
                        <span>(Kết quả tìm kiếm)</span>
                    <?php endif; ?>
                </p>
            </div>
            <button class="add-button" onclick="openAddModal()">+ Thêm sản phẩm</button>
        </div>

        <div id="message"></div>

        <div class="search-filter">
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; flex: 1; align-items: flex-end;">
                <div class="form-group">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" placeholder="Nhập tên sản phẩm..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="form-group">
                    <label for="filter_loai">Lọc theo loại:</label>
                    <select id="filter_loai" name="filter_loai">
                        <option value="">-- Tất cả loại --</option>
                        <?php foreach ($loai_list as $loai): ?>
                            <option value="<?= htmlspecialchars($loai['id_loai']) ?>" 
                                <?= $filter_loai === $loai['id_loai'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loai['ten_loai']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-search">🔍 Tìm kiếm</button>
                <?php if ($search || $filter_loai): ?>
                    <a href="quanly_sanpham.php" style="text-decoration: none;">
                        <button type="button" class="btn-search" style="background: #95a5a6;">Reset</button>
                    </a>
                <?php endif; ?>
            </form>
        </div>
            <?php if (count($products) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên sản phẩm</th>
                            <th>Loại</th>
                            <th>Giá bán (₫)</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($product['id_sp']) ?></td>
                                <td><?= htmlspecialchars($product['ten_sp']) ?></td>
                                <td><?= htmlspecialchars($product['ten_loai'] ?? 'N/A') ?></td>
                                <td><?= number_format($product['gia_ban'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($product['so_luong_ton']) ?></td>
                                <td>
                                    <span class="<?= $product['trang_thai'] ? 'status-active' : 'status-inactive' ?>">
                                        <?= $product['trang_thai'] ? 'Hiện' : 'Ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-small" onclick="openEditModal(<?= htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8') ?>)">Sửa</button>
                                    <button class="btn btn-small btn-delete" onclick="deleteProduct(<?= htmlspecialchars($product['id_sp']) ?>)">Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>Chưa có sản phẩm nào. <a href="#" onclick="openAddModal(); return false;" style="color: var(--green-dark); font-weight: 600;">Thêm sản phẩm mới</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Thêm/Sửa Sản Phẩm -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Thêm sản phẩm</h2>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="productId" name="id_sp">
                    
                    <div class="form-group">
                        <label for="tenSp">Tên sản phẩm *</label>
                        <input type="text" id="tenSp" name="ten_sp" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="idLoai">Loại gạo *</label>
                            <select id="idLoai" name="id_loai" required>
                                <option value="">-- Chọn loại gạo --</option>
                                <?php foreach ($loai_list as $loai): ?>
                                    <option value="<?= htmlspecialchars($loai['id_loai']) ?>">
                                        <?= htmlspecialchars($loai['ten_loai']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="xuatXu">Xuất xứ</label>
                            <input type="text" id="xuatXu" name="xuat_xu" placeholder="VD: Việt Nam">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="giaBan">Giá bán (₫) *</label>
                            <input type="number" id="giaBan" name="gia_ban" step="1000" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="giaGoc">Giá gốc (₫)</label>
                            <input type="number" id="giaGoc" name="gia_goc" step="1000" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phanTramGiam">Giảm giá (%)</label>
                            <input type="number" id="phanTramGiam" name="phan_tram_giam" min="0" max="100" value="0">
                        </div>
                        <div class="form-group">
                            <label for="soLuongTon">Số lượng tồn kho *</label>
                            <input type="number" id="soLuongTon" name="so_luong_ton" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="moTaNgan">Mô tả ngắn</label>
                        <textarea id="moTaNgan" name="mo_ta_ngan" placeholder="Mô tả sản phẩm ngắn gọn..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="hinhAnhSp">📸 Hình ảnh sản phẩm</label>
                        <input type="file" id="hinhAnhSp" name="hinh_anh[]" multiple accept="image/*" style="padding: 8px;">
                        <small style="display: block; margin-top: 5px; color: #7f8c8d;">
                            Chọn một hoặc nhiều ảnh (JPG, PNG, WEBP). Ảnh đầu tiên sẽ là ảnh chính.
                        </small>
                        <div id="imagePreview" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="noiBat">
                                <input type="checkbox" id="noiBat" name="noi_bat" value="1"> Sản phẩm nổi bật
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="hangMoi">
                                <input type="checkbox" id="hangMoi" name="hang_moi" value="1"> Hàng mới
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="trangThai">
                            <input type="checkbox" id="trangThai" name="trang_thai" value="1" checked> Hiển thị sản phẩm
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal()">Hủy</button>
                <button class="btn btn-submit" onclick="saveProduct()">Lưu</button>
            </div>
        </div>
    </div>

    <script>
        // Lưu trữ hình ảnh được chọn
        let selectedImages = [];

        // Xử lý preview hình ảnh
        document.getElementById('hinhAnhSp').addEventListener('change', function(e) {
            selectedImages = [];
            const previewDiv = document.getElementById('imagePreview');
            previewDiv.innerHTML = '';

            const files = Array.from(e.target.files);
            
            files.forEach((file, index) => {
                // Kiểm tra loại file
                if (!file.type.startsWith('image/')) {
                    showMessage('error', 'Vui lòng chỉ chọn file ảnh');
                    return;
                }

                // Kiểm tra dung lượng (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showMessage('error', 'File ảnh không được vượt quá 5MB');
                    return;
                }

                selectedImages.push(file);

                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'image-preview-item';
                    previewItem.innerHTML = `
                        <img src="${event.target.result}" alt="Preview">
                        <button type="button" class="remove-image" onclick="removeImage(${selectedImages.length - 1})">×</button>
                    `;
                    previewDiv.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });
        });

        // Xóa ảnh từ preview
        function removeImage(index) {
            selectedImages.splice(index, 1);
            const fileInput = document.getElementById('hinhAnhSp');
            const dt = new DataTransfer();
            selectedImages.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;
            // Trigger change event để cập nhật preview
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        }

        // Mở modal thêm
        function openAddModal() {
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('modalTitle').textContent = 'Thêm sản phẩm';
            document.getElementById('imagePreview').innerHTML = '';
            selectedImages = [];
            document.getElementById('productModal').classList.add('show');
        }

        // Mở modal sửa
        function openEditModal(product) {
            document.getElementById('productId').value = product.id_sp;
            document.getElementById('tenSp').value = product.ten_sp;
            document.getElementById('idLoai').value = product.id_loai || '';
            document.getElementById('xuatXu').value = product.xuat_xu || '';
            document.getElementById('giaBan').value = product.gia_ban;
            document.getElementById('giaGoc').value = product.gia_goc || '';
            document.getElementById('phanTramGiam').value = product.phan_tram_giam || 0;
            document.getElementById('soLuongTon').value = product.so_luong_ton;
            document.getElementById('moTaNgan').value = product.mo_ta_ngan || '';
            document.getElementById('noiBat').checked = product.noi_bat == 1;
            document.getElementById('hangMoi').checked = product.hang_moi == 1;
            document.getElementById('trangThai').checked = product.trang_thai == 1;
            
            // Reset file input và preview
            document.getElementById('hinhAnhSp').value = '';
            document.getElementById('imagePreview').innerHTML = '';
            selectedImages = [];
            
            document.getElementById('modalTitle').textContent = 'Sửa sản phẩm';
            document.getElementById('productModal').classList.add('show');
        }

        // Đóng modal
        function closeModal() {
            document.getElementById('productModal').classList.remove('show');
            document.getElementById('message').innerHTML = '';
        }

        // Lưu sản phẩm
        function saveProduct() {
            const form = document.getElementById('productForm');
            const formData = new FormData(form);
            const id = document.getElementById('productId').value;
            
            // Thêm các file ảnh vào FormData
            const fileInput = document.getElementById('hinhAnhSp');
            if (fileInput.files.length > 0) {
                // Xóa các file cũ nếu có
                formData.delete('hinh_anh[]');
                // Thêm file mới
                for (let file of selectedImages) {
                    formData.append('hinh_anh[]', file);
                }
            }
            
            // Chuyển checkbox thành 0 hoặc 1
            formData.set('noi_bat', document.getElementById('noiBat').checked ? 1 : 0);
            formData.set('hang_moi', document.getElementById('hangMoi').checked ? 1 : 0);
            formData.set('trang_thai', document.getElementById('trangThai').checked ? 1 : 0);

            const action = id ? 'update' : 'add';
            
            fetch('api/sanpham_admin.php?action=' + action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('error', data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                showMessage('error', 'Lỗi kết nối: ' + error.message);
            });
        }

        // Xóa sản phẩm
        function deleteProduct(id) {
            if (!confirm('Bạn chắc chắn muốn xóa sản phẩm này?')) {
                return;
            }

            const formData = new FormData();
            formData.append('id_sp', id);

            fetch('api/sanpham_admin.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('error', data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                showMessage('error', 'Lỗi kết nối: ' + error.message);
            });
        }

        // Hiển thị thông báo
        function showMessage(type, message) {
            const msgDiv = document.getElementById('message');
            msgDiv.innerHTML = '<div class="alert alert-' + type + '">' + message + '</div>';
            msgDiv.scrollIntoView({ behavior: 'smooth' });
        }

        // Đóng modal khi click ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
