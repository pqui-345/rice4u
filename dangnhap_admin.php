<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$thong_bao = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_dang_nhap = trim($_POST['ten_dang_nhap']);
    $mat_khau_nhap = trim($_POST['mat_khau']);

    $stmt = $pdo->prepare("SELECT * FROM TAI_KHOAN WHERE ten_dang_nhap = ?");
    $stmt->execute([$ten_dang_nhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($mat_khau_nhap, $user['mat_khau'])) {
        if ($user['vai_tro'] === 'admin') {
            $_SESSION['ID_TK'] = $user['ID_TK'] ?? $user['ma_tk']; 
            $_SESSION['vai_tro'] = $user['vai_tro'];
            header("Location: admin.php");
            exit();
        } else {
            $thong_bao = "Cảnh báo: Tài khoản này không có quyền Quản trị viên!";
        }
    } else {
        $thong_bao = "Tên đăng nhập hoặc mật khẩu không chính xác!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - Cửa Hàng Gạo Rice4U</title>
    <link href="/rice4u/.vscode/asset/header.css" rel="stylesheet">
    <link href="/rice4u/.vscode/asset/footer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Nunito', Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }

        /* --- LAYOUT CHUNG --- */
        body {
            margin: 0 auto;
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
            grid-template-areas: "header" "main" "footer";
        }

        

        /* ================== MAIN CSS (ADMIN) ================== */
        main {
            grid-area: main;
            position: relative;
            display: flex;
            align-items: center; 
            justify-content: center;
            padding: 3em 1em; 
            overflow: hidden; 
        }
        main::before {
            content: "";
            position: absolute;
            top: -20px; left: -20px; right: -20px; bottom: -20px; 
            background: url('/rice4u/.vscode/asset/images/bgr.jpg') no-repeat center center;
            background-size: cover;
            filter: blur(6px);
            z-index: -2;
        }
        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5); 
            z-index: -1;
        }
        
        .admin-login-box {
            position: relative; 
            z-index: 1;
            background-color: #ffffff;
            box-sizing: border-box;
            font-size: 1.1vw; 
            width: 36em; 
            padding: 3.5em;
            border-radius: 1em;
            box-shadow: 0 1.5em 3.5em rgba(0, 0, 0, 0.5);
            border-top: 0.5em solid #fdc350; 
        }
        .admin-login-box h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 0.2em; font-size: 2.5em; text-transform: uppercase; letter-spacing: 0.05em; }
        .subtitle { text-align: center; color: #7f8c8d; margin-bottom: 2.5em; font-size: 1.2em; }
        .form-group { margin-bottom: 1.8em; }
        .form-group label { display: block; margin-bottom: 0.8em; font-weight: bold; color: #34495e; font-size: 1.2em; }
        
        .input-wrapper { position: relative; }
        .form-group input {
            width: 100%; font-family: 'Nunito', sans-serif; box-sizing: border-box; background-color: #f9f9f9;
            transition: all 0.3s ease; font-size: 1.2em; padding: 1.2em 1.5em; border: 0.1em solid #bdc3c7; border-radius: 0.5em;
        }
        .form-group input.password-toggle { padding-right: 3.5em; }
        .form-group input:focus { border-color: #fdc350; outline: none; background-color: #fff; }
        
        .eye-icon {
            position: absolute; top: 50%; right: 1.2em; transform: translateY(-50%); cursor: pointer;
            color: #888; display: flex; align-items: center; justify-content: center; transition: color 0.3s;
        }
        .eye-icon:hover { color: #fdc350; }
        .eye-icon svg { width: 1.6em; height: 1.6em; }

        .btn-submit {
            width: 100%; background-color: #fdc350; color: white; border: none; font-weight: bold; cursor: pointer;
            transition: background 0.3s; text-transform: uppercase; font-size: 1.3em; padding: 1.2em; border-radius: 0.5em; margin-top: 1em;
        }
        .btn-submit:hover { background-color: #ffaa00; }
        .thong-bao { color: #d35400; background-color: #faf4d8; padding: 1em; border-radius: 0.5em; margin-bottom: 2em; font-size: 1.2em; text-align: center; font-weight: bold; display: <?php echo empty($thong_bao) ? 'none' : 'block'; ?>; }
        .back-link { text-align: center; font-size: 1.2em; font-weight: bold; margin-top: 2.5em; }
        .back-link a { color: #5b9764; text-decoration: none; }
        .back-link a:hover { color: #84c88b; text-decoration: underline; }

        @media (max-width: 800px) {
            .admin-login-box { font-size: 14px; width: 90%; max-width: 400px; padding: 30px; border-radius: 12px; }
            header { flex-direction: column; height: auto; padding: 10px; }
            footer { flex-direction: column; align-items: center; text-align: center; }
        }

    </style>
</head>
<body>

    <header>
        <div class="logo-navi">
            <a href="./trangchu.php"><img class="logo" src="/rice4u/.vscode/asset/images/logo.png" alt="Logo"></a>
            <nav>
                <ul class="dieu-huong">
                    <li><a href="./trangchu.php">Trang Chủ</a></li>
                    <li><a href="./sanpham.php">Sản Phẩm</a></li>
                    <li><a href="./lienhe.php">Liên Hệ</a></li>
                </ul>
            </nav>
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
        <div class="admin-login-box">
            <h2>Khu Vực Quản Trị</h2>
            <p class="subtitle">System Administration</p>
            
            <div class="thong-bao">
                <?php echo htmlspecialchars($thong_bao); ?>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="ten_dang_nhap">Tài khoản Admin</label>
                    <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" placeholder="Nhập username" required>
                </div>
                
                <div class="form-group">
                    <label for="mat_khau">Mật khẩu</label>
                    <div class="input-wrapper">
                        <input type="password" id="mat_khau" class="password-toggle" name="mat_khau" placeholder="Nhập password" required>
                        <span class="eye-icon" id="toggle-eye-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Đăng nhập Admin</button>
            </form>
            
            <div class="back-link">
                <a href="trangchu.php">&larr; Quay lại trang khách hàng</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-column">
            <img class="logo" src="/rice4u/.vscode/asset/images/logo.png" alt="Logo">
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
            const passwordInput = document.getElementById('mat_khau');
            const toggleEyeBtn = document.getElementById('toggle-eye-btn');
            const eyeOpenSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            const eyeClosedSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

            toggleEyeBtn.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                this.innerHTML = isPassword ? eyeOpenSVG : eyeClosedSVG;
            });
        });
    </script>
</body>
</html>

