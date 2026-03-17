-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2026 at 07:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rice4u`
--

-- --------------------------------------------------------

--
-- Table structure for table `caidat`
--

CREATE TABLE `caidat` (
  `id` int(11) NOT NULL,
  `khoa` varchar(100) NOT NULL,
  `gia_tri` text DEFAULT NULL,
  `mo_ta` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caidat`
--

INSERT INTO `caidat` (`id`, `khoa`, `gia_tri`, `mo_ta`) VALUES
(1, 'ten_web', 'Rice4U', 'Tên website'),
(2, 'slogan', 'Tinh Hoa Gạo Việt, Giao Tận Nhà', 'Slogan'),
(3, 'email_lienhe', 'info@rice4u.com', 'Email liên hệ'),
(4, 'so_dien_thoai', '1800-rice4u', 'Số điện thoại'),
(5, 'dia_chi', '123 Đường Lúa Vàng, TP.HCM', 'Địa chỉ'),
(6, 'phi_ship_mac_dinh', '30000', 'Phí ship mặc định (VNĐ)'),
(7, 'mien_ship_tu', '500000', 'Miễn phí ship từ (VNĐ)'),
(8, 'facebook', 'https://facebook.com/rice4u', 'Link Facebook'),
(9, 'instagram', 'https://instagram.com/rice4u', 'Link Instagram'),
(10, 'zalo', 'https://zalo.me/rice4u', 'Link Zalo OA');

-- --------------------------------------------------------

--
-- Table structure for table `chitiet_donhang`
--

CREATE TABLE `chitiet_donhang` (
  `id_ct` int(11) NOT NULL,
  `id_dh` char(20) NOT NULL,
  `id_sp` char(10) NOT NULL,
  `ten_sp` varchar(255) NOT NULL,
  `quy_cach` varchar(50) DEFAULT NULL,
  `so_luong` decimal(10,2) NOT NULL,
  `don_vi` varchar(20) DEFAULT 'kg',
  `gia_ban` decimal(15,2) NOT NULL,
  `thanh_tien` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `danhgia`
--

CREATE TABLE `danhgia` (
  `id_dg` int(11) NOT NULL,
  `id_sp` char(10) NOT NULL,
  `id_kh` int(11) NOT NULL,
  `id_dh` char(20) DEFAULT NULL,
  `so_sao` tinyint(4) NOT NULL CHECK (`so_sao` between 1 and 5),
  `tieu_de` varchar(200) DEFAULT NULL,
  `noi_dung` text DEFAULT NULL,
  `trang_thai` enum('cho_duyet','da_duyet','bi_an') DEFAULT 'cho_duyet',
  `ngay_dg` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diachi_giaohang`
--

CREATE TABLE `diachi_giaohang` (
  `id_dc` int(11) NOT NULL,
  `id_kh` int(11) NOT NULL,
  `ho_ten` varchar(200) NOT NULL,
  `so_dien_thoai` varchar(20) NOT NULL,
  `dia_chi` varchar(500) NOT NULL,
  `phuong_xa` varchar(150) DEFAULT NULL,
  `quan_huyen` varchar(150) DEFAULT NULL,
  `tinh_tp` varchar(150) DEFAULT NULL,
  `la_mac_dinh` tinyint(1) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donhang`
--

CREATE TABLE `donhang` (
  `id_dh` char(20) NOT NULL,
  `id_kh` int(11) DEFAULT NULL,
  `ma_don` varchar(50) NOT NULL,
  `ho_ten_nguoi_nhan` varchar(200) NOT NULL,
  `so_dien_thoai` varchar(20) NOT NULL,
  `dia_chi_giao` varchar(500) NOT NULL,
  `phuong_xa` varchar(150) DEFAULT NULL,
  `quan_huyen` varchar(150) DEFAULT NULL,
  `tinh_tp` varchar(150) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `tong_tien_hang` decimal(15,2) NOT NULL DEFAULT 0.00,
  `phi_van_chuyen` decimal(15,2) DEFAULT 0.00,
  `giam_gia` decimal(15,2) DEFAULT 0.00,
  `tong_thanh_toan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `id_pttt` int(11) DEFAULT NULL,
  `trang_thai_tt` enum('chua_tt','da_tt','hoan_tien') DEFAULT 'chua_tt',
  `trang_thai_dh` enum('cho_xac_nhan','dang_chuan_bi','dang_giao','da_giao','da_huy') DEFAULT 'cho_xac_nhan',
  `ma_van_don` varchar(100) DEFAULT NULL,
  `ngay_dat` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gia_quy_cach`
--

CREATE TABLE `gia_quy_cach` (
  `id` int(11) NOT NULL,
  `id_sp` char(10) NOT NULL,
  `trong_luong` decimal(10,2) NOT NULL,
  `don_vi` varchar(10) DEFAULT 'kg',
  `gia_ban` decimal(15,2) NOT NULL,
  `gia_goc` decimal(15,2) DEFAULT NULL,
  `so_luong_ton` decimal(10,2) DEFAULT 0.00,
  `ma_sku` varchar(50) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gia_quy_cach`
--

INSERT INTO `gia_quy_cach` (`id`, `id_sp`, `trong_luong`, `don_vi`, `gia_ban`, `gia_goc`, `so_luong_ton`, `ma_sku`, `trang_thai`) VALUES
(1, 'SP001', 1.00, 'kg', 35000.00, NULL, 500.00, 'SP001-1KG', 1),
(2, 'SP001', 5.00, 'kg', 165000.00, NULL, 100.00, 'SP001-5KG', 1),
(3, 'SP001', 25.00, 'kg', 800000.00, NULL, 20.00, 'SP001-25KG', 1),
(4, 'SP003', 1.00, 'kg', 22000.00, NULL, 600.00, 'SP003-1KG', 1),
(5, 'SP003', 5.00, 'kg', 105000.00, NULL, 120.00, 'SP003-5KG', 1),
(6, 'SP003', 25.00, 'kg', 510000.00, NULL, 30.00, 'SP003-25KG', 1),
(7, 'SP008', 1.00, 'kg', 15000.00, NULL, 1000.00, 'SP008-1KG', 1),
(8, 'SP008', 5.00, 'kg', 72000.00, NULL, 200.00, 'SP008-5KG', 1),
(9, 'SP008', 25.00, 'kg', 350000.00, NULL, 50.00, 'SP008-25KG', 1),
(10, 'SP014', 1.00, 'kg', 38000.00, NULL, 120.00, 'SP014-1KG', 1),
(11, 'SP014', 5.00, 'kg', 180000.00, NULL, 40.00, 'SP014-5KG', 1);

-- --------------------------------------------------------

--
-- Table structure for table `giohang`
--

CREATE TABLE `giohang` (
  `id_gh` int(11) NOT NULL,
  `id_kh` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `id_sp` char(10) NOT NULL,
  `id_quy_cach` int(11) DEFAULT NULL,
  `so_luong` decimal(10,2) NOT NULL DEFAULT 1.00,
  `gia_tai_thoi_diem` decimal(15,2) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hinhanh_sp`
--

CREATE TABLE `hinhanh_sp` (
  `id_anh` int(11) NOT NULL,
  `id_sp` char(10) NOT NULL,
  `duong_dan` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `la_anh_chinh` tinyint(1) DEFAULT 0,
  `thu_tu` int(11) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hinhanh_sp`
--

INSERT INTO `hinhanh_sp` (`id_anh`, `id_sp`, `duong_dan`, `alt_text`, `la_anh_chinh`, `thu_tu`, `ngay_tao`) VALUES
(1, 'SP001', 'images/products/ST25-1kg.jpg', 'Gạo ST25 - Túi 1kg', 1, 1, '2026-03-01 11:08:29'),
(2, 'SP002', 'images/products/gao-st24-tui-5kg.jpg', 'Gạo ST24 - Túi 5kg', 1, 1, '2026-03-01 11:08:29'),
(3, 'SP003', 'images/products/jasmine_rice_vn.jpg', 'Gạo Jasmine Đồng Tháp', 1, 1, '2026-03-01 11:08:29'),
(4, 'SP004', 'images/products/jasmine-85-5kg.jpg', 'Gạo Jasmine 85 - Túi 5kg', 1, 1, '2026-03-01 11:08:29'),
(5, 'SP005', 'images/products/OM5451 Rice.jpg', 'Gạo OM5451 Cần Thơ', 1, 1, '2026-03-01 11:08:29'),
(6, 'SP006', 'images/products/gao-4900.jpg', 'Gạo OM4900 Vĩnh Long', 1, 1, '2026-03-01 11:08:29'),
(7, 'SP007', 'images/products/gaoom18.jpg', 'Gạo OM18 An Giang', 1, 1, '2026-03-01 11:08:29'),
(8, 'SP008', 'images/products/gao-ir50404.jpg', 'Gạo IR50404 Kiên Giang', 1, 1, '2026-03-01 11:08:29'),
(9, 'SP009', 'images/products/gao-nang-hoa.jpg', 'Gạo Nàng Hoa Long An', 1, 1, '2026-03-01 11:08:29'),
(10, 'SP010', 'images/products/gao-huong-lai.jpg', 'Gạo Hương Lài Tiền Giang', 1, 1, '2026-03-01 11:08:29'),
(11, 'SP011', 'images/products/cai-hoa-vang.jpg', 'Nếp Cái Hoa Vàng Bắc Ninh', 1, 1, '2026-03-01 11:08:29'),
(12, 'SP012', 'images/products/nep-than.jpg', 'Gạo Nếp Than Sóc Trăng', 1, 1, '2026-03-01 11:08:29'),
(13, 'SP013', 'images/products/gao-lut-do.jpg', 'Gạo Lứt Đỏ Quảng Nam', 1, 1, '2026-03-01 11:08:29'),
(14, 'SP014', 'images/products/gao-lut-huu-co.jpg', 'Gạo Lứt Hữu Cơ Lâm Đồng', 1, 1, '2026-03-01 11:08:29'),
(15, 'SP015', 'images/products/gao-huyet-rong.jpg', 'Gạo Huyết Rồng An Giang', 1, 1, '2026-03-01 11:08:29'),
(16, 'SP016', 'images/products/gao-japonica.jpg', 'Gạo Japonica Đồng Nai', 1, 1, '2026-03-01 11:08:29'),
(17, 'SP017', 'images/products/gao-sushi.jpg', 'Gạo Sushi nhập khẩu', 1, 1, '2026-03-01 11:08:29'),
(18, 'SP018', 'images/products/Gao-Nang-Nhen.jpg', 'Gạo Nàng Nhen An Giang', 1, 1, '2026-03-01 11:08:29'),
(19, 'SP019', 'images/products/gao-tai-nguyen.jpg', 'Gạo Tài Nguyên Tây Nguyên', 1, 1, '2026-03-01 11:08:29'),
(20, 'SP020', 'images/products/gao-trang.jpg', 'Gạo Trắng Phổ Thông ĐBSCL', 1, 1, '2026-03-01 11:08:29');

-- --------------------------------------------------------

--
-- Table structure for table `khachhang`
--

CREATE TABLE `khachhang` (
  `id_kh` int(11) NOT NULL,
  `ho_ten` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('Nam','Nữ','Khác') DEFAULT NULL,
  `anh_dai_dien` varchar(500) DEFAULT NULL,
  `diem_tich_luy` int(11) DEFAULT 0,
  `trang_thai` tinyint(1) DEFAULT 1,
  `email_xac_nhan` tinyint(1) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dia_chi` varchar(100) DEFAULT NULL,
  `ma_tk` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `khachhang`
--

INSERT INTO `khachhang` (`id_kh`, `ho_ten`, `email`, `mat_khau`, `so_dien_thoai`, `ngay_sinh`, `gioi_tinh`, `anh_dai_dien`, `diem_tich_luy`, `trang_thai`, `email_xac_nhan`, `ngay_tao`, `ngay_cap_nhat`, `dia_chi`, `ma_tk`) VALUES
(2, 'Dang Ngoc My', 'myb2303766@gmail.com', '', '0123456789', NULL, NULL, '/rice4u/uploads/avatars/avatar_5_1773034454.jpg', 0, 1, 0, '2026-03-09 11:20:58', '2026-03-09 12:41:32', 'Ninh Kiều - Cần Thơ', 5);

-- --------------------------------------------------------

--
-- Table structure for table `lienhe`
--

CREATE TABLE `lienhe` (
  `id_lh` int(11) NOT NULL,
  `ho_ten` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `so_dt` varchar(20) DEFAULT NULL,
  `chu_de` varchar(300) DEFAULT NULL,
  `noi_dung` text NOT NULL,
  `trang_thai` enum('chua_xu_ly','da_xu_ly') DEFAULT 'chua_xu_ly',
  `ngay_gui` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loaigao`
--

CREATE TABLE `loaigao` (
  `id_loai` char(10) NOT NULL,
  `ten_loai` varchar(150) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(500) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loaigao`
--

INSERT INTO `loaigao` (`id_loai`, `ten_loai`, `mo_ta`, `hinh_anh`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
('LG001', 'Gạo thơm', 'Gạo có mùi thơm tự nhiên, hạt dài, mềm', NULL, 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('LG002', 'Gạo trắng phổ thông', 'Gạo sử dụng hàng ngày, giá mềm', NULL, 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('LG003', 'Gạo trắng hạt dài', 'Gạo hạt dài, phù hợp xuất khẩu', NULL, 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('LG004', 'Gạo nếp', 'Gạo dùng nấu xôi, làm bánh', NULL, 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('LG005', 'Gạo lứt', 'Gạo giữ lớp cám, tốt cho sức khỏe', NULL, 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('LG006', 'Gạo hữu cơ', 'Gạo sản xuất theo quy trình hữu cơ', NULL, 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('LG007', 'Gạo đặc sản', 'Gạo đặc trưng theo từng địa phương', NULL, 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('LG008', 'Gạo hạt tròn', 'Gạo dùng làm sushi, cơm cuộn', NULL, 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19');

-- --------------------------------------------------------

--
-- Table structure for table `magiamgia`
--

CREATE TABLE `magiamgia` (
  `id_mgk` int(11) NOT NULL,
  `ma_code` varchar(50) NOT NULL,
  `ten` varchar(200) DEFAULT NULL,
  `loai` enum('phan_tram','so_tien') DEFAULT 'phan_tram',
  `gia_tri` decimal(10,2) NOT NULL,
  `giam_toi_da` decimal(15,2) DEFAULT NULL,
  `don_toi_thieu` decimal(15,2) DEFAULT 0.00,
  `so_luong` int(11) DEFAULT NULL,
  `da_su_dung` int(11) DEFAULT 0,
  `ngay_bat_dau` datetime DEFAULT NULL,
  `ngay_ket_thuc` datetime DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `magiamgia`
--

INSERT INTO `magiamgia` (`id_mgk`, `ma_code`, `ten`, `loai`, `gia_tri`, `giam_toi_da`, `don_toi_thieu`, `so_luong`, `da_su_dung`, `ngay_bat_dau`, `ngay_ket_thuc`, `trang_thai`, `ngay_tao`) VALUES
(1, 'RICE10', 'Giảm 10% cho đơn đầu tiên', 'phan_tram', 10.00, 50000.00, 100000.00, 500, 0, '2026-01-01 00:00:00', '2026-12-31 00:00:00', 1, '2026-03-01 10:21:21'),
(2, 'WELCOME50K', 'Tặng 50K cho đơn từ 500K', 'so_tien', 50000.00, NULL, 500000.00, 200, 0, '2026-01-01 00:00:00', '2026-06-30 00:00:00', 1, '2026-03-01 10:21:21'),
(3, 'SUMMER15', 'Khuyến mãi hè giảm 15%', 'phan_tram', 15.00, 100000.00, 200000.00, 100, 0, '2026-06-01 00:00:00', '2026-08-31 00:00:00', 1, '2026-03-01 10:21:21');

-- --------------------------------------------------------

--
-- Table structure for table `phuong_thuc_tt`
--

CREATE TABLE `phuong_thuc_tt` (
  `id_pttt` int(11) NOT NULL,
  `ten` varchar(100) NOT NULL,
  `ma` varchar(50) NOT NULL,
  `mo_ta` varchar(300) DEFAULT NULL,
  `hinh_anh` varchar(500) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phuong_thuc_tt`
--

INSERT INTO `phuong_thuc_tt` (`id_pttt`, `ten`, `ma`, `mo_ta`, `hinh_anh`, `trang_thai`) VALUES
(1, 'Thanh toán khi nhận hàng', 'COD', 'Trả tiền mặt khi nhận hàng tại nhà', NULL, 1),
(2, 'Chuyển khoản ngân hàng', 'BANK_TRANSFER', 'Chuyển khoản qua tài khoản ngân hàng', NULL, 1),
(3, 'Ví MoMo', 'MOMO', 'Thanh toán qua ví điện tử MoMo', NULL, 1),
(4, 'VNPay', 'VNPAY', 'Thanh toán qua cổng VNPay', NULL, 1),
(5, 'ZaloPay', 'ZALOPAY', 'Thanh toán qua ví ZaloPay', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sanpham`
--

CREATE TABLE `sanpham` (
  `id_sp` char(10) NOT NULL,
  `ten_sp` varchar(255) NOT NULL,
  `id_loai` char(10) NOT NULL,
  `xuat_xu` varchar(150) DEFAULT NULL,
  `mo_ta_ngan` varchar(500) DEFAULT NULL,
  `mo_ta_chi_tiet` text DEFAULT NULL,
  `thanh_phan` text DEFAULT NULL,
  `don_vi` varchar(20) DEFAULT 'kg',
  `gia_ban` decimal(15,2) NOT NULL,
  `gia_goc` decimal(15,2) DEFAULT NULL,
  `phan_tram_giam` tinyint(4) DEFAULT 0,
  `so_luong_ton` decimal(10,2) DEFAULT 0.00,
  `luot_xem` int(11) DEFAULT 0,
  `luot_ban` int(11) DEFAULT 0,
  `noi_bat` tinyint(1) DEFAULT 0,
  `hang_moi` tinyint(1) DEFAULT 0,
  `ban_chay` tinyint(1) DEFAULT 0,
  `slug` varchar(300) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sanpham`
--

INSERT INTO `sanpham` (`id_sp`, `ten_sp`, `id_loai`, `xuat_xu`, `mo_ta_ngan`, `mo_ta_chi_tiet`, `thanh_phan`, `don_vi`, `gia_ban`, `gia_goc`, `phan_tram_giam`, `so_luong_ton`, `luot_xem`, `luot_ban`, `noi_bat`, `hang_moi`, `ban_chay`, `slug`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
('SP001', 'Gạo ST25', 'LG001', 'Sóc Trăng', 'Giống gạo ngon nhất thế giới, hương thơm tự nhiên, cơm dẻo mịn đặc trưng.', NULL, NULL, 'kg', 35000.00, 40000.00, 13, 500.00, 0, 0, 1, 0, 1, 'gao-st25', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP002', 'Gạo ST24', 'LG001', 'Sóc Trăng', 'Gạo thơm hạt dài, cơm mềm dẻo, hương vị thanh nhẹ.', NULL, NULL, 'kg', 32000.00, NULL, 0, 400.00, 0, 0, 1, 0, 0, 'gao-st24', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP003', 'Gạo Jasmine', 'LG001', 'Đồng Tháp', 'Hương thơm nhài tự nhiên, hạt gạo trắng trong, cơm mềm xốp.', NULL, NULL, 'kg', 22000.00, NULL, 0, 600.00, 0, 0, 1, 0, 1, 'gao-jasmine', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP004', 'Gạo Jasmine 85', 'LG001', 'An Giang', 'Tiêu chuẩn xuất khẩu 85%, hương thơm nhẹ, phù hợp mọi gia đình.', NULL, NULL, 'kg', 21000.00, NULL, 0, 450.00, 0, 0, 0, 0, 0, 'gao-jasmine-85', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP005', 'Gạo OM5451', 'LG003', 'Cần Thơ', 'Gạo hạt dài thơm nhẹ, cơm tơi xốp, phù hợp nấu cơm rang.', NULL, NULL, 'kg', 18000.00, NULL, 0, 700.00, 0, 0, 0, 0, 1, 'gao-om5451', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP006', 'Gạo OM4900', 'LG003', 'Vĩnh Long', 'Gạo hạt dài chất lượng cao, phổ biến tại vùng ĐBSCL.', NULL, NULL, 'kg', 17500.00, NULL, 0, 650.00, 0, 0, 0, 0, 0, 'gao-om4900', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP007', 'Gạo OM18', 'LG002', 'An Giang', 'Gạo trắng thông dụng, hạt đều, phù hợp nấu cơm hàng ngày.', NULL, NULL, 'kg', 16000.00, NULL, 0, 800.00, 0, 0, 0, 0, 0, 'gao-om18', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP008', 'Gạo IR50404', 'LG002', 'Kiên Giang', 'Gạo phổ thông, giá tốt, đáp ứng nhu cầu hàng ngày.', NULL, NULL, 'kg', 15000.00, NULL, 0, 1000.00, 0, 0, 0, 0, 1, 'gao-ir50404', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP009', 'Gạo Nàng Hoa', 'LG001', 'Long An', 'Gạo đặc sản Long An, hương thơm tự nhiên, cơm mềm dẻo.', NULL, NULL, 'kg', 25000.00, NULL, 0, 350.00, 0, 0, 1, 0, 0, 'gao-nang-hoa', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP010', 'Gạo Hương Lài', 'LG001', 'Tiền Giang', 'Gạo thơm hương lài nhẹ nhàng, cơm trong dẻo.', NULL, NULL, 'kg', 24000.00, NULL, 0, 300.00, 0, 0, 0, 0, 0, 'gao-huong-lai', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP011', 'Nếp Cái Hoa Vàng', 'LG004', 'Bắc Ninh', 'Giống nếp quý hiếm, hạt tròn bóng, dẻo thơm dùng làm xôi và bánh truyền thống.', NULL, NULL, 'kg', 28000.00, NULL, 0, 200.00, 0, 0, 1, 0, 1, 'nep-cai-hoa-vang', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP012', 'Gạo Nếp Than', 'LG004', 'Sóc Trăng', 'Nếp đen đặc sản, giàu anthocyanin, thơm dẻo đặc trưng.', NULL, NULL, 'kg', 26000.00, NULL, 0, 180.00, 0, 0, 0, 0, 0, 'gao-nep-than', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP013', 'Gạo Lứt Đỏ', 'LG005', 'Quảng Nam', 'Gạo lứt đỏ giàu chất xơ, hỗ trợ tiêu hóa và kiểm soát cân nặng.', NULL, NULL, 'kg', 30000.00, NULL, 0, 150.00, 0, 0, 0, 0, 1, 'gao-lut-do', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP014', 'Gạo Lứt Hữu Cơ', 'LG006', 'Lâm Đồng', 'Gạo hữu cơ 100%, không thuốc trừ sâu, tốt cho sức khỏe toàn diện.', NULL, NULL, 'kg', 38000.00, 42000.00, 10, 120.00, 0, 0, 1, 0, 0, 'gao-lut-huu-co', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP015', 'Gạo Huyết Rồng', 'LG007', 'An Giang', 'Gạo đặc sản An Giang, màu đỏ tự nhiên, vị ngậy thơm đặc trưng.', NULL, NULL, 'kg', 40000.00, NULL, 0, 100.00, 0, 0, 1, 0, 1, 'gao-huyet-rong', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP016', 'Gạo Japonica', 'LG008', 'Đồng Nai', 'Gạo hạt tròn Nhật Bản, cơm dẻo kết dính, phù hợp làm sushi.', NULL, NULL, 'kg', 33000.00, NULL, 0, 250.00, 0, 0, 0, 0, 0, 'gao-japonica', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP017', 'Gạo Sushi', 'LG008', 'Nhập khẩu', 'Gạo sushi nhập khẩu cao cấp, cơm bóng mướt, kết dính tốt.', NULL, NULL, 'kg', 36000.00, NULL, 0, 220.00, 0, 0, 0, 0, 1, 'gao-sushi', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP018', 'Gạo Nàng Nhen', 'LG007', 'An Giang', 'Gạo thơm đặc sản vùng Bảy Núi, hương vị khó quên.', NULL, NULL, 'kg', 42000.00, NULL, 0, 90.00, 0, 0, 1, 0, 0, 'gao-nang-nhen', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP019', 'Gạo Tài Nguyên', 'LG001', 'Tây Nguyên', 'Gạo thơm vùng cao Tây Nguyên, hạt đều, cơm ngọt thanh tao.', NULL, NULL, 'kg', 23000.00, NULL, 0, 370.00, 0, 0, 0, 0, 0, 'gao-tai-nguyen', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19'),
('SP020', 'Gạo Trắng Phổ Thông', 'LG002', 'ĐBSCL', 'Gạo trắng phổ thông chất lượng ổn định, phù hợp mua số lượng lớn.', NULL, NULL, 'kg', 14000.00, NULL, 0, 1200.00, 0, 0, 0, 0, 1, 'gao-trang-pho-thong', 1, '2026-03-01 10:21:19', '2026-03-01 10:21:19');

-- --------------------------------------------------------

--
-- Table structure for table `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `ma_tk` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `vai_tro` varchar(70) NOT NULL DEFAULT 'khach_hang'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tai_khoan`
--

INSERT INTO `tai_khoan` (`ma_tk`, `ten_dang_nhap`, `mat_khau`, `vai_tro`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(5, 'nmy', '$2y$10$mZYtv2SDZEhbQH3e97YtAuawcs5HDlCW3E8BhzwtJrbxm.QAOhGaW', 'khachhang');

-- --------------------------------------------------------

--
-- Table structure for table `thanhtoan`
--

CREATE TABLE `thanhtoan` (
  `thanhtoan_id` int(11) NOT NULL COMMENT 'ID giao dịch thanh toán',
  `id_dh` char(20) NOT NULL COMMENT 'Mã đơn hàng - FK → donhang.id_dh',
  `id_pttt` int(11) NOT NULL COMMENT 'Phương thức thanh toán - FK → phuong_thuc_tt.id_pttt',
  `so_tien` decimal(15,2) NOT NULL COMMENT 'Số tiền thực tế của giao dịch này',
  `ma_giao_dich` varchar(100) DEFAULT NULL COMMENT 'Mã giao dịch từ cổng thanh toán (VNPay, MoMo...)',
  `trang_thai` enum('chua_tt','da_tt','hoan_tien') DEFAULT 'chua_tt' COMMENT 'Trạng thái: chua_tt | da_tt | hoan_tien',
  `noi_dung` varchar(500) DEFAULT NULL COMMENT 'Ghi chú hoặc phản hồi từ cổng thanh toán',
  `ngay_thanhtoan` datetime DEFAULT current_timestamp() COMMENT 'Thời điểm tạo giao dịch',
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Thời điểm cập nhật trạng thái'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lịch sử giao dịch thanh toán của các đơn hàng';

-- --------------------------------------------------------

--
-- Table structure for table `tintuc`
--

CREATE TABLE `tintuc` (
  `id_tin` int(11) NOT NULL,
  `tieu_de` varchar(500) NOT NULL,
  `slug` varchar(500) NOT NULL,
  `tom_tat` text DEFAULT NULL,
  `noi_dung` longtext DEFAULT NULL,
  `hinh_anh` varchar(500) DEFAULT NULL,
  `danh_muc` varchar(100) DEFAULT NULL,
  `luot_xem` int(11) DEFAULT 0,
  `trang_thai` tinyint(1) DEFAULT 1,
  `ngay_dang` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_danhgia_sp`
-- (See below for the actual view)
--
CREATE TABLE `v_danhgia_sp` (
`id_sp` char(10)
,`tong_danh_gia` bigint(21)
,`diem_trung_binh` decimal(5,1)
,`sao_5` decimal(23,0)
,`sao_4` decimal(23,0)
,`sao_3` decimal(23,0)
,`sao_thap` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_doanhthu_thang`
-- (See below for the actual view)
--
CREATE TABLE `v_doanhthu_thang` (
`nam` int(4)
,`thang` int(2)
,`so_don` bigint(21)
,`doanh_thu` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_sanpham_day_du`
-- (See below for the actual view)
--
CREATE TABLE `v_sanpham_day_du` (
`id_sp` char(10)
,`ten_sp` varchar(255)
,`slug` varchar(300)
,`xuat_xu` varchar(150)
,`mo_ta_ngan` varchar(500)
,`gia_ban` decimal(15,2)
,`gia_goc` decimal(15,2)
,`phan_tram_giam` tinyint(4)
,`so_luong_ton` decimal(10,2)
,`luot_ban` int(11)
,`noi_bat` tinyint(1)
,`ban_chay` tinyint(1)
,`hang_moi` tinyint(1)
,`ten_loai` varchar(150)
,`hinh_chinh` varchar(500)
);

-- --------------------------------------------------------

--
-- Structure for view `v_danhgia_sp`
--
DROP TABLE IF EXISTS `v_danhgia_sp`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_danhgia_sp`  AS SELECT `danhgia`.`id_sp` AS `id_sp`, count(0) AS `tong_danh_gia`, round(avg(`danhgia`.`so_sao`),1) AS `diem_trung_binh`, sum(`danhgia`.`so_sao` = 5) AS `sao_5`, sum(`danhgia`.`so_sao` = 4) AS `sao_4`, sum(`danhgia`.`so_sao` = 3) AS `sao_3`, sum(`danhgia`.`so_sao` <= 2) AS `sao_thap` FROM `danhgia` WHERE `danhgia`.`trang_thai` = 'da_duyet' GROUP BY `danhgia`.`id_sp` ;

-- --------------------------------------------------------

--
-- Structure for view `v_doanhthu_thang`
--
DROP TABLE IF EXISTS `v_doanhthu_thang`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_doanhthu_thang`  AS SELECT year(`donhang`.`ngay_dat`) AS `nam`, month(`donhang`.`ngay_dat`) AS `thang`, count(0) AS `so_don`, sum(`donhang`.`tong_thanh_toan`) AS `doanh_thu` FROM `donhang` WHERE `donhang`.`trang_thai_dh` <> 'da_huy' GROUP BY year(`donhang`.`ngay_dat`), month(`donhang`.`ngay_dat`) ORDER BY year(`donhang`.`ngay_dat`) DESC, month(`donhang`.`ngay_dat`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_sanpham_day_du`
--
DROP TABLE IF EXISTS `v_sanpham_day_du`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_sanpham_day_du`  AS SELECT `sp`.`id_sp` AS `id_sp`, `sp`.`ten_sp` AS `ten_sp`, `sp`.`slug` AS `slug`, `sp`.`xuat_xu` AS `xuat_xu`, `sp`.`mo_ta_ngan` AS `mo_ta_ngan`, `sp`.`gia_ban` AS `gia_ban`, `sp`.`gia_goc` AS `gia_goc`, `sp`.`phan_tram_giam` AS `phan_tram_giam`, `sp`.`so_luong_ton` AS `so_luong_ton`, `sp`.`luot_ban` AS `luot_ban`, `sp`.`noi_bat` AS `noi_bat`, `sp`.`ban_chay` AS `ban_chay`, `sp`.`hang_moi` AS `hang_moi`, `lg`.`ten_loai` AS `ten_loai`, `ha`.`duong_dan` AS `hinh_chinh` FROM ((`sanpham` `sp` left join `loaigao` `lg` on(`sp`.`id_loai` = `lg`.`id_loai`)) left join `hinhanh_sp` `ha` on(`sp`.`id_sp` = `ha`.`id_sp` and `ha`.`la_anh_chinh` = 1)) WHERE `sp`.`trang_thai` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `caidat`
--
ALTER TABLE `caidat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_khoa` (`khoa`);

--
-- Indexes for table `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD PRIMARY KEY (`id_ct`),
  ADD KEY `fk_ct_dh` (`id_dh`),
  ADD KEY `fk_ct_sp` (`id_sp`);

--
-- Indexes for table `danhgia`
--
ALTER TABLE `danhgia`
  ADD PRIMARY KEY (`id_dg`),
  ADD UNIQUE KEY `uq_dg_kh_sp` (`id_kh`,`id_sp`,`id_dh`),
  ADD KEY `fk_dg_sp` (`id_sp`),
  ADD KEY `fk_dg_kh` (`id_kh`);

--
-- Indexes for table `diachi_giaohang`
--
ALTER TABLE `diachi_giaohang`
  ADD PRIMARY KEY (`id_dc`),
  ADD KEY `fk_dc_kh` (`id_kh`);

--
-- Indexes for table `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`id_dh`),
  ADD UNIQUE KEY `uq_ma_don` (`ma_don`),
  ADD KEY `fk_dh_kh` (`id_kh`),
  ADD KEY `fk_dh_pttt` (`id_pttt`);

--
-- Indexes for table `gia_quy_cach`
--
ALTER TABLE `gia_quy_cach`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sp_kl` (`id_sp`,`trong_luong`);

--
-- Indexes for table `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`id_gh`),
  ADD KEY `fk_gh_kh` (`id_kh`),
  ADD KEY `fk_gh_sp` (`id_sp`);

--
-- Indexes for table `hinhanh_sp`
--
ALTER TABLE `hinhanh_sp`
  ADD PRIMARY KEY (`id_anh`),
  ADD KEY `fk_anh_sp` (`id_sp`);

--
-- Indexes for table `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`id_kh`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD KEY `fk_taikhoan` (`ma_tk`);

--
-- Indexes for table `lienhe`
--
ALTER TABLE `lienhe`
  ADD PRIMARY KEY (`id_lh`);

--
-- Indexes for table `loaigao`
--
ALTER TABLE `loaigao`
  ADD PRIMARY KEY (`id_loai`),
  ADD UNIQUE KEY `uq_ten_loai` (`ten_loai`);

--
-- Indexes for table `magiamgia`
--
ALTER TABLE `magiamgia`
  ADD PRIMARY KEY (`id_mgk`),
  ADD UNIQUE KEY `uq_ma_code` (`ma_code`);

--
-- Indexes for table `phuong_thuc_tt`
--
ALTER TABLE `phuong_thuc_tt`
  ADD PRIMARY KEY (`id_pttt`),
  ADD UNIQUE KEY `uq_ma_pttt` (`ma`);

--
-- Indexes for table `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`id_sp`),
  ADD UNIQUE KEY `uq_slug` (`slug`),
  ADD KEY `fk_sp_loaigao` (`id_loai`);
ALTER TABLE `sanpham` ADD FULLTEXT KEY `ft_search` (`ten_sp`,`mo_ta_ngan`);

--
-- Indexes for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`ma_tk`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`);

--
-- Indexes for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`thanhtoan_id`),
  ADD KEY `idx_tt_donhang` (`id_dh`),
  ADD KEY `idx_tt_pttt` (`id_pttt`),
  ADD KEY `idx_tt_ma_gd` (`ma_giao_dich`);

--
-- Indexes for table `tintuc`
--
ALTER TABLE `tintuc`
  ADD PRIMARY KEY (`id_tin`),
  ADD UNIQUE KEY `uq_slug_tin` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `caidat`
--
ALTER TABLE `caidat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  MODIFY `id_ct` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `danhgia`
--
ALTER TABLE `danhgia`
  MODIFY `id_dg` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diachi_giaohang`
--
ALTER TABLE `diachi_giaohang`
  MODIFY `id_dc` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gia_quy_cach`
--
ALTER TABLE `gia_quy_cach`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `giohang`
--
ALTER TABLE `giohang`
  MODIFY `id_gh` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hinhanh_sp`
--
ALTER TABLE `hinhanh_sp`
  MODIFY `id_anh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `id_kh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lienhe`
--
ALTER TABLE `lienhe`
  MODIFY `id_lh` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `magiamgia`
--
ALTER TABLE `magiamgia`
  MODIFY `id_mgk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `phuong_thuc_tt`
--
ALTER TABLE `phuong_thuc_tt`
  MODIFY `id_pttt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  MODIFY `ma_tk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `thanhtoan_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID giao dịch thanh toán';

--
-- AUTO_INCREMENT for table `tintuc`
--
ALTER TABLE `tintuc`
  MODIFY `id_tin` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD CONSTRAINT `fk_ct_dh` FOREIGN KEY (`id_dh`) REFERENCES `donhang` (`id_dh`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ct_sp` FOREIGN KEY (`id_sp`) REFERENCES `sanpham` (`id_sp`);

--
-- Constraints for table `danhgia`
--
ALTER TABLE `danhgia`
  ADD CONSTRAINT `fk_dg_kh` FOREIGN KEY (`id_kh`) REFERENCES `khachhang` (`id_kh`),
  ADD CONSTRAINT `fk_dg_sp` FOREIGN KEY (`id_sp`) REFERENCES `sanpham` (`id_sp`);

--
-- Constraints for table `diachi_giaohang`
--
ALTER TABLE `diachi_giaohang`
  ADD CONSTRAINT `fk_dc_kh` FOREIGN KEY (`id_kh`) REFERENCES `khachhang` (`id_kh`) ON DELETE CASCADE;

--
-- Constraints for table `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `fk_dh_kh` FOREIGN KEY (`id_kh`) REFERENCES `khachhang` (`id_kh`),
  ADD CONSTRAINT `fk_dh_pttt` FOREIGN KEY (`id_pttt`) REFERENCES `phuong_thuc_tt` (`id_pttt`);

--
-- Constraints for table `gia_quy_cach`
--
ALTER TABLE `gia_quy_cach`
  ADD CONSTRAINT `fk_gia_sp` FOREIGN KEY (`id_sp`) REFERENCES `sanpham` (`id_sp`) ON DELETE CASCADE;

--
-- Constraints for table `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `fk_gh_kh` FOREIGN KEY (`id_kh`) REFERENCES `khachhang` (`id_kh`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_gh_sp` FOREIGN KEY (`id_sp`) REFERENCES `sanpham` (`id_sp`);

--
-- Constraints for table `hinhanh_sp`
--
ALTER TABLE `hinhanh_sp`
  ADD CONSTRAINT `fk_anh_sp` FOREIGN KEY (`id_sp`) REFERENCES `sanpham` (`id_sp`) ON DELETE CASCADE;

--
-- Constraints for table `khachhang`
--
ALTER TABLE `khachhang`
  ADD CONSTRAINT `fk_taikhoan` FOREIGN KEY (`ma_tk`) REFERENCES `tai_khoan` (`ma_tk`);

--
-- Constraints for table `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `fk_sp_loaigao` FOREIGN KEY (`id_loai`) REFERENCES `loaigao` (`id_loai`) ON UPDATE CASCADE;

--
-- Constraints for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `fk_tt_donhang` FOREIGN KEY (`id_dh`) REFERENCES `donhang` (`id_dh`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tt_pttt` FOREIGN KEY (`id_pttt`) REFERENCES `phuong_thuc_tt` (`id_pttt`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
