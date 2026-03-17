<?php
require_once __DIR__ . '/includes/db.php';
$thong_bao = '';
$thanh_cong = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_dang_nhap = trim($_POST['ten_dang_nhap']);
    $mat_khau_moi = $_POST['mat_khau_moi'];
    $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'];

    if ($mat_khau_moi !== $xac_nhan_mat_khau) {
        $thong_bao = "Mật khẩu xác nhận không khớp!";
    } else {
        $stmt = $pdo->prepare("SELECT ma_tk FROM TAI_KHOAN WHERE ten_dang_nhap = ?");
        $stmt->execute([$ten_dang_nhap]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $mat_khau_hash = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE TAI_KHOAN SET mat_khau = ? WHERE ten_dang_nhap = ?");

            if ($update_stmt->execute([$mat_khau_hash, $ten_dang_nhap])) {
                $thanh_cong = "Đổi mật khẩu thành công! Bạn có thể đăng nhập ngay.";
            } else {
                $thong_bao = "Có lỗi xảy ra, vui lòng thử lại sau.";
            }
        } else {
            $thong_bao = "Tên đăng nhập không tồn tại trong hệ thống!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi Phục Mật Khẩu - Cửa Hàng Gạo Rice4U</title>
    <link href="/rice4u/asset/header.css" rel="stylesheet">
    <link href="/rice4u/asset/footer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Be Vietnam Pro', Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }

        /* --- LAYOUT CHUNG --- */
        body {
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
            grid-template-areas: "header" "main" "footer";
        }

        /* ================== MAIN CSS ================== */
        main {
            grid-area: main;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3em 1em;
            overflow: hidden;
            margin-bottom: 20px;
        }

        main::before {
            content: "";
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: url('/rice4u/asset/images/bgr.jpg') no-repeat center center;
            background-size: cover;
            filter: blur(6px);
            z-index: -2;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .login-container {
            position: relative;
            z-index: 1;
            background-color: #ffffff;
            box-sizing: border-box;
            font-size: 1.1vw;
            width: 38em;
            padding: 3.5em;
            border-radius: 1em;
            box-shadow: 0 1.5em 4em rgba(0, 0, 0, 0.2);
        }

        .login-container h2 {
            text-align: center;
            color: #519A66;
            margin-top: 0;
            margin-bottom: 0.5em;
            font-size: 2.8em;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2.5em;
            font-size: 1.2em;
        }

        .form-group {
            margin-bottom: 1.5em;
            position: relative;
        }

        .form-group input {
            width: 100%;
            font-family: 'Be Vietnam Pro', Arial, Helvetica, sans-serif;
            box-sizing: border-box;
            transition: all 0.3s ease;
            font-size: 1.2em;
            padding: 1.2em 1.5em;
            border: 0.1em solid #ddd;
            border-radius: 0.8em;
        }

        .form-group input.password-toggle {
            padding-right: 3.5em;
        }

        .form-group input:focus {
            border-color: #519A66;
            outline: none;
            box-shadow: 0 0 0.8em rgba(46, 125, 50, 0.2);
        }

        .eye-icon {
            position: absolute;
            top: 50%;
            right: 1.2em;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s;
        }

        .eye-icon:hover {
            color: #519A66;
        }

        .eye-icon svg {
            width: 1.8em;
            height: 1.8em;
        }

        .btn-submit {
            width: 100%;
            background-color: #519A66;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 1.3em;
            padding: 1.2em;
            border-radius: 0.8em;
            margin-top: 1em;
        }

        .btn-submit:hover {
            background-color: #237227;
        }

        .footer-link {
            text-align: center;
            font-size: 1.1em;
            margin-top: 2.5em;
        }

        .footer-link a {
            color: #519A66;
            text-decoration: none;
            font-weight: bold;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }

        .thong-bao {
            color: #c0392b;
            text-align: center;
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 1.5em;
        }

        .thanh-cong {
            color: #27ae60;
            text-align: center;
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 1.5em;
        }

        @media (max-width: 800px) {
            .login-container {
                font-size: 14px;
                width: 90%;
                max-width: 450px;
                padding: 30px;
                border-radius: 12px;
            }

            header {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }

            footer {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="logo-navi">
            <a href="./trangchu.php"><img class="logo" src="/rice4u/asset/images/logo.png" alt="Logo"></a>
            <nav>
                <ul class="dieu-huong">
                    <li><a href="./trangchu.php">Trang Chủ</a></li>
                    <li><a href="./sanpham.php">Sản Phẩm</a></li>
                    <li><a href="./lienhe.php">Liên Hệ</a></li>
                </ul>
            </nav>
        </div>
        <div class="search-box">
            <input type="text" placeholder="Nhập loại gạo bạn muốn tìm">
            <button class="search">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

        </div>
        <div class="user">
            <button class="login-button"><a href="./dangnhap.php">Đăng Nhập</a></button>
            <button class="signup-button"><a href="./dangky.php">Đăng Ký</a></button>
            <div class="icon">
                <a href="./giohang.php">
                    <i class="fa-solid fa-cart-shopping"><span class="soluong">0</span></i>
                </a>
                <div class="user-icon">
                    <a href="./dangnhap.php"><i class="fa-solid fa-user"></i></a>
                    <div class="log-out">
                        <a href="./hoso.php">Thông tin cá nhân</a>
                        <button class="logout-button">Đăng xuất</button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="overlay"></div>
        <div class="login-container">
            <h2>Đổi Mật Khẩu</h2>
            <p class="subtitle">Thiết lập lại mật khẩu mới cho tài khoản của bạn</p>

            <?php
            if (!empty($thong_bao)) echo "<div class='thong-bao'>" . htmlspecialchars($thong_bao) . "</div>";
            if (!empty($thanh_cong)) echo "<div class='thanh-cong'>" . htmlspecialchars($thanh_cong) . "</div>";
            ?>

            <?php if (empty($thanh_cong)): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="text" name="ten_dang_nhap" placeholder="Nhập Tên đăng nhập của bạn" required>
                    </div>

                    <div class="form-group">
                        <input type="password" id="mat_khau_moi" class="password-toggle" name="mat_khau_moi" placeholder="Mật khẩu mới" required>
                        <span class="eye-icon toggle-eye-btn" data-target="mat_khau_moi">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>

                    <div class="form-group">
                        <input type="password" id="xac_nhan_mat_khau" class="password-toggle" name="xac_nhan_mat_khau" placeholder="Xác nhận mật khẩu mới" required>
                        <span class="eye-icon toggle-eye-btn" data-target="xac_nhan_mat_khau">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>

                    <button type="submit" class="btn-submit">Lưu Mật Khẩu</button>
                </form>
            <?php endif; ?>

            <div class="footer-link">
                <a href="dangnhap.php">Quay lại trang Đăng nhập</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-column">
            <img class="logo" src="/rice4u/asset/images/logo.png" alt="Logo">
        </div>
        <div class="footer-column">
            <h3>Về chúng tôi</h3>
            <ul>
                <li><a href="#">Giới thiệu</a></li>
                <li><a href="#">Tuyển dụng</a></li>
                <li><a href="#">Liên hệ</a></li>
                <li><a href="#">Câu hỏi thường gặp</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3>Chính sách</h3>
            <ul>
                <li><a href="#">Chính sách bảo mật</a></li>
                <li><a href="#">Điều khoản dịch vụ</a></li>
                <li><a href="#">Chính sách đổi trả</a></li>
            </ul>
        </div>
        <div class="media">
            <h3>Liên hệ</h3>
            <p><i class="fas fa-map-marker-alt"></i> Đường 3/2, phường Ninh Kiều, TPCT</p>
            <p><i class="fas fa-phone"></i> (000) 0000 0000</p>
            <p><i class="fas fa-envelope"></i> CT299@ctu.edu.vn</p>
            <p>Giờ làm việc: T2 - CN, 9:00 - 19:00</p>
            <p style="margin-top: 15px; font-weight: bold;">&copy; 2026 Bản quyền thuộc về Rice4U.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtns = document.querySelectorAll('.toggle-eye-btn');
            const eyeOpenSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            const eyeClosedSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

            toggleBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const inputField = document.getElementById(targetId);

                    const isPassword = inputField.type === 'password';
                    inputField.type = isPassword ? 'text' : 'password';
                    this.innerHTML = isPassword ? eyeOpenSVG : eyeClosedSVG;
                });
            });
        });
    </script>
</body>

</html>

