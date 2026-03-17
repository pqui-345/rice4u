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
    <link href="/rice4u/asset/header.css" rel="stylesheet">
    <link href="/rice4u/asset/footer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">


</head>

<body>
    <?php include 'includes/header.php'; ?>
    <style>
        * {
            font-family: 'Be Vietnam Pro', Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
        }

        main {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3em 1em;
            overflow: hidden;
            /* Chiều cao = toàn màn hình trừ header (72px) và footer */
            min-height: calc(100vh - 72px - 120px);
        }

        main::before {
            content: "";
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: url(/rice4u/asset/images/bgr.jpg) no-repeat center center;
            background-size: cover;
            filter: blur(6px);
            z-index: -2;
        }

        .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .login-container {
            position: relative;
            z-index: 1;
            background-color: #ffffff;
            box-sizing: border-box;
            width: 100%;
            max-width: 480px;
            padding: 3em 3.5em;
            border-radius: 1em;
            box-shadow: 0 1.5em 4em rgba(0, 0, 0, 0.2);
        }

        .login-container h2 {
            text-align: center;
            color: var(--amber);
            margin-top: 0;
            margin-bottom: 0.3em;
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2em;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.4em;
            position: relative;
        }

        .form-group input {
            width: 100%;
            font-family: 'Be Vietnam Pro', sans-serif;
            box-sizing: border-box;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            padding: 0.9em 3em 0.9em 1.2em;
            border: 1.5px solid #e0e0e0;
            border-radius: 0.8em;
            background: var(--gray-light);
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--green-mid);
            box-shadow: 0 0 0 3px rgba(81, 154, 102, 0.13);
            background: white;
        }

        .eye-icon {
            position: absolute;
            top: 50%;
            right: 1em;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .eye-icon:hover {
            color: var(--green-dark);
        }

        .eye-icon svg {
            width: 1.3em;
            height: 1.3em;
        }

        .a-btn-submit {
            width: 100%;
            background-color: var(--amber);
            color: white;
            border: none;
            font-family: 'Be Vietnam Pro', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95rem;
            padding: 0.95em;
            border-radius: 100px;
            margin-top: 0.5em;
            letter-spacing: 0.03em;
            box-shadow: 0 6px 20px rgba(35, 114, 39, 0.25);
        }

        .a-btn-submit:hover {
            background-color: var(--amber);
            transform: translateY(-1px);
            box-shadow: 0 10px 26px rgba(238, 242, 173, 0.32);
        }

        .thong-bao {
            color: #c0392b;
            background: #ffebee;
            border: 1px solid #ffcdd2;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 10px 14px;
            margin-bottom: 1.2em;
        }

        .forgot-link {
            text-align: right;
            margin: -0.8em 0 1.2em;
        }

        .forgot-link a {
            color: var(--green-mid);
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }

        .footer-link {
            text-align: center;
            font-size: 0.88rem;
            margin-top: 1.5em;
            color: #666;
        }

        .footer-link a {
            color: var(--green-mid);
            text-decoration: none;
            font-weight: 700;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 2em 1.5em;
            }
        }
    </style>
    <main>
        <div class="overlay"></div>
        <div class="login-container">
            <h2 class>KHU VỰC QUẢN TRỊ</h2>
            <p class="subtitle">System AdminStration</p>

            <?php if (!empty($thong_bao)): ?>
                <div class="thong-bao"><?= htmlspecialchars($thong_bao) ?></div>
            <?php endif; ?>

            <form method="POST" action="dangnhap.php">
                <div class="form-group">
                    <input type="text" name="ten_dang_nhap" placeholder="Tên đăng nhập" required autofocus>
                </div>
                <div class="form-group">
                    <input type="password" id="mat-khau-input" name="mat_khau"
                        placeholder="Mật khẩu" required>
                    <span class="eye-icon" onclick="togglePassword('mat-khau-input', 'eye-svg-1')">
                        <svg id="eye-svg-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                    </span>
                </div>

                <button type="submit" class="a-btn-submit">Đăng nhập ngay</button>
            </form>

            <div class="footer-link">
                <a href="khachhang.php">Quay lại trang Khách Hàng</a>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>

    <script>
        function togglePassword(inputId, iconId) {
            var input = document.getElementById(inputId);
            var iconSvg = document.getElementById(iconId);
            var eyeOpen = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
            var eyeClosed = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
            if (input.type === "password") {
                input.type = "text";
                iconSvg.innerHTML = eyeOpen;
            } else {
                input.type = "password";
                iconSvg.innerHTML = eyeClosed;
            }
        }
    </script>
</body>

</html>