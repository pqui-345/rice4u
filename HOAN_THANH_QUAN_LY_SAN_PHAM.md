# ✅ Quy Trình Quản Lý Sản Phẩm - Hoàn Thiện (v2.0)

## 📋 Tổng Quan

Quy trình quản lý sản phẩm (thêm/sửa/xóa) với hỗ trợ upload hình ảnh đã được hoàn tất. Đã khắc phục lỗi **duplicate PRIMARY KEY** bằng cách tạo hàm `generateProductId()` tự động.

---

## 🔧 Các Thay Đổi Chính

### 1. **Khắc Phục Lỗi PRIMARY KEY**

**Vấn đề gốc:**
- Bảng `sanpham` sử dụng `id_sp (CHAR(10))` làm PRIMARY KEY
- Không phải `AUTO_INCREMENT` → `lastInsertId()` không hoạt động
- Khi cố thêm sản phẩm mà không cung cấp `id_sp` → Lỗi duplicate key

**Giải pháp:**
```php
function generateProductId() {
    // Tìm max id_sp hiện tại (ví dụ SP020)
    // Extract số (020) → convert to int (20)
    // Tăng lên 1 → 21
    // Format lại thành SP021
    
    SELECT MAX(CAST(SUBSTRING(id_sp, 3) AS UNSIGNED)) as max_num 
    FROM sanpham WHERE id_sp LIKE 'SP%'
    // sp kế tiếp = 'SP' + str_pad(max_num + 1, 3, '0', STR_PAD_LEFT)
}
```

### 2. **Các File Được Sửa**

#### `api/sanpham_admin.php` - API Backend
- ✅ Thêm hàm `generateProductId()` tạo ID tự động
- ✅ Sửa `addProduct()` để sử dụng `generateProductId()` thay vì `lastInsertId()`
- ✅ Sửa `updateProduct()` để xử lý `id_sp` như STRING (CHAR(10))
- ✅ Sửa `deleteProduct()` để xử lý STRING id và xóa file ảnh trên disk
- ✅ Hỗ trợ upload nhiều ảnh cùng lúc
- ✅ Tự động xóa file ảnh khi xóa sản phẩm

#### `quanly_sanpham.php` - UI Frontend
- ✅ Form upload ảnh với preview trực tiếp
- ✅ Hỗ trợ multiple file upload
- ✅ Xóa ảnh từ preview trước khi submit
- ✅ Hiển thị danh sách sản phẩm với CRUD operations

### 3. **Các File Xóa (Cleanup)**
- ❌ `debug_sanpham.php` - File debug tạm thời
- ❌ `HUONGDAN_QUANLY_SANPHAM.md` - Hướng dẫn cũ
- ❌ `HUONGDAN_UPLOAD_ANH.md` - Hướng dẫn cũ  
- ❌ `KHACPHUC_LOI_DATABASE.md` - Tài liệu khắc phục tạm thời

---

## 🚀 Quy Trình Sử Dụng

### **THÊM SẢN PHẨM MỚI**

1. Truy cập: `http://localhost/rice4u/admin.php` → Click "Quản lý sản phẩm"
2. Click nút `+ Thêm sản phẩm`
3. Điền thông tin:
   - **Tên sản phẩm** *(bắt buộc)
   - **Loại gạo** *(bắt buộc)
   - Xuất xứ
   - **Giá bán** *(bắt buộc)
   - Giá gốc
   - Giảm giá (%)
   - **Số lượng tồn kho** *(bắt buộc)
   - Mô tả ngắn
   - **Hình ảnh** (chọn 1 hoặc nhiều ảnh)
   - Đánh dấu: sản phẩm nổi bật, hàng mới, hiển thị
4. Click **Lưu**

**Kết quả:**
- ✅ Sản phẩm được tạo với ID tự động (ví dụ: **SP021**)
- ✅ Hình ảnh được upload vào thư mục `uploads/products/`
- ✅ Đường dẫn ảnh được lưu vào bảng `hinhanh_sp`
- ✅ Ảnh đầu tiên tự động được đặt làm ảnh chính (`la_anh_chinh = 1`)

### **SỬA SẢN PHẨM**

1. Tìm sản phẩm trong danh sách
2. Click nút **Sửa** (bên cạnh nút Xóa)
3. Modal sẽ mở, thông tin sản phẩm được điền sẵn
4. Thay đổi thông tin:
   - Có thể thay đổi tên, giá, loại, v.v.
   - Có thể thêm ảnh mới (sẽ thay thế ảnh cũ)
   - Ảnh cũ sẽ bị xóa khỏi disk
5. Click **Lưu**

**Kết quả:**
- ✅ Thông tin sản phẩm cập nhật
- ✅ Ảnh mới được upload (nếu có)
- ✅ Ảnh cũ được xóa tự động

### **XÓA SẢN PHẨM**

1. Tìm sản phẩm trong danh sách
2. Click nút **Xóa** (màu đỏ)
3. Xác nhận trong hộp thoại

**Có 2 trường hợp:**

**A) Sản phẩm KHÔNG có đơn hàng tham chiếu:**
- ✅ Xóa hoàn toàn khỏi database
- ✅ Xóa tất cả hình ảnh khỏi disk
- ✅ Xóa các quy cách giá liên quan

**B) Sản phẩm CÓ đơn hàng tham chiếu:**
- ⚠️ Sản phẩm sẽ được ẨN (thay vì xóa)
- ✅ Lý do: Giữ toàn vẹn dữ liệu lịch sử đơn hàng
- ℹ️ Sản phẩm ẩn không hiển thị trên website khách

---

## 📊 Cấu Trúc Database

### Bảng `sanpham`
```sql
CREATE TABLE sanpham (
  id_sp CHAR(10) PRIMARY KEY,        -- Format: SP001, SP002, ..., SP021, ...
  ten_sp VARCHAR(255) NOT NULL,
  id_loai CHAR(10) NOT NULL,
  xuat_xu VARCHAR(150),
  mo_ta_ngan VARCHAR(500),
  gia_ban DECIMAL(15,2) NOT NULL,
  gia_goc DECIMAL(15,2),
  phan_tram_giam TINYINT(4),
  so_luong_ton DECIMAL(10,2),
  noi_bat TINYINT(1),
  hang_moi TINYINT(1),
  ban_chay TINYINT(1),
  slug VARCHAR(300) UNIQUE,
  trang_thai TINYINT(1) DEFAULT 1,   -- 1: hiện, 0: ẩn
  ngay_tao DATETIME,
  ngay_cap_nhat DATETIME ON UPDATE CURRENT_TIMESTAMP
);
```

### Bảng `hinhanh_sp` (Hình Ảnh Sản Phẩm)
```sql
CREATE TABLE hinhanh_sp (
  id_anh INT AUTO_INCREMENT PRIMARY KEY,
  id_sp CHAR(10) NOT NULL,
  duong_dan VARCHAR(500) NOT NULL,   -- Path: uploads/products/product_SP021_xxx.jpg
  alt_text VARCHAR(255),
  la_anh_chinh TINYINT(1) DEFAULT 0, -- 1: ảnh chính, 0: ảnh phụ
  thu_tu INT DEFAULT 1,
  ngay_tao DATETIME,
  FOREIGN KEY (id_sp) REFERENCES sanpham(id_sp) ON DELETE CASCADE
);
```

---

## 📁 Cấu Trúc Thư Mục

```
rice4u/
├── api/
│   └── sanpham_admin.php          ← API backend (sửa)
├── quanly_sanpham.php             ← UI frontend (không sửa)
├── uploads/
│   └── products/                  ← Thư mục lưu ảnh sản phẩm
│       ├── product_SP001_xxxxx.jpg
│       ├── product_SP021_xxxxx.jpg
│       └── ...
└── ...
```

---

## ✅ Checklist Kiểm Tra

- [x] Thêm sản phẩm mới (không bị lỗi duplicate key)
- [x] ID sản phẩm được tạo tự động (SP021, SP022, ...)
- [x] Upload ảnh khi thêm sản phẩm
- [x] Ảnh được lưu vào database + folder `uploads/products/`
- [x] Ảnh đầu tiên được đặt làm ảnh chính tự động
- [x] Sửa sản phẩm (thay đổi thông tin)
- [x] Thay đổi ảnh khi sửa (ảnh cũ xóa, ảnh mới upload)
- [x] Xóa sản phẩm không có đơn hàng (xóa hình ảnh trên disk)
- [x] Xóa sản phẩm có đơn hàng (ẩn thay vì xóa)
- [x] Tìm kiếm sản phẩm theo tên
- [x] Lọc sản phẩm theo loại gạo
- [x] Hiển thị danh sách sản phẩm

---

## 🔍 Kiểm Tra Lỗi

### Nếu gặp lỗi "Có lỗi xảy ra"

1. **Kiểm tra thư mục upload:**
   - Đảm bảo thư mục `uploads/products/` tồn tại
   - Quyền ghi (write permission) phải được cấp
   ```bash
   # Windows: Chuột phải → Properties → Security → Modify
   # Linux: chmod 755 uploads/products/
   ```

2. **Kiểm tra file upload:**
   - Chỉ chấp nhận: **JPG, PNG, WEBP**
   - Kích thước tối đa: **5MB**

3. **Kiểm tra session admin:**
   - Đảm bảo đã đăng nhập với tài khoản admin
   - Session phải có `vai_tro = 'admin'`

4. **Kiểm tra file log:**
   ```bash
   # Mở C:\xampp\apache\logs\error.log để xem chi tiết lỗi PHP
   ```

---

## 🎯 Các Bước Tiếp Theo (Tùy Chọn)

1. **Thêm chức năng edit slug** - Cho phép admin tùy chỉnh URL slug
2. **Thêm chức năng sắp xếp** - Drag & drop để thay đổi thứ tự ảnh
3. **Thêm chức năng nén ảnh** - Tự động nén ảnh để tối ưu hiệu suất
4. **Thêm chức năng crop ảnh** - Cho phép admin crop ảnh trước upload
5. **Thêm chức năng watermark** - Tự động thêm logo vào ảnh

---

## 📞 Hỗ Trợ

Nếu có vấn đề, kiểm tra:
- ✅ Browser console (F12 → Console) để xem lỗi JavaScript
- ✅ Network tab để xem request/response API
- ✅ Server logs (`C:\xampp\apache\logs\error.log`)
- ✅ Database logs cho lỗi SQL

---

**Cập nhật:** 17/03/2026
**Trạng thái:** ✅ Hoàn thiện
