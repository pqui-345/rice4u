<?php
if (isset($_POST['submit']))
    {
        // lấy dữ liệu từ form
        $name=trim($POST['name']);
        $phone=trim($_POST['phone']);
        $email=trim($_POST['email']);
        $message=trim($_POST['message']);
        $error="";

        //kiểm tra định dạng email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error="Email không hợp lệ. Vui lòng nhập lại!";
        }
        //kiểm tra các ô khác đã điền chưa
        if(empty($name) || empty($phone) || empty($email) || empty($message))
            {
                $erroe="Vui lòng nhập đầy đủ thông tin!";
            }
        //phản hồi kết quả
        if($error != ""){
            echo "<script> alert('error');
                   window.history.back();
                   </script>"    ;
    }
    else {
        echo "<script> alert('Cảm ơn $name. Yêu cầu của bạn đã gửi thành công. Rice4u sẽ liên hệ sớm nhất');
                   window.location.href='lienhe.php';
                   </script>";
    }
    }
?>
