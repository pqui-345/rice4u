<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loai_dia_chi = $_POST['loai_dia_chi'] ?? '';   // 'nha_rieng' hoặc 'van_phong'
    $dat_mac_dinh = isset($_POST['mac_dinh']) ? 1 : 0;  // 1 nếu tick, 0 nếu không

    echo "Loại địa chỉ: " . ($loai_dia_chi=== 'nha_rieng' ? 'Nhà Riêng' : 'Văn Phòng') . "<br>";
    echo "Đặt mặc định: " . ($dat_mac_dinh ? 'Có' : 'Không');

    header("Location: thanhtoan.php");
    exit();
}
?>



<html>
    <form method="POST" action="">

           
            <div class="add-form-group1">
                    <label for="name">Họ và tên <span>*</span></label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($name ?? '') ?>" required>
                
                    <label for="phone">Số điện thoại <span>*</span></label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= htmlspecialchars($phone ?? '') ?>" required>
                </div>
                <div class="add-form-group2">
                <label for="address">Tỉnh/Thành phố, Quận/Huyện, Phường/Xã <span>*</span></label>
                <input type="text" id="address" name="address"
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
               
                <label for="spe_address">Tên đường, Tòa nhà, Số nhà. <span>*</span></label>
                <input type="text" id="spe_address" name="spe_address"
                       value="<?= htmlspecialchars($spe_address ?? '') ?>" required>
            </div>

             
            <div class="add_type">
                <input class="add-check-input" type="checkbox" name="mac_dinh" id="dat_mac_dinh" value="1">
                 <label class="add-check-label" for="dat_mac_dinh">
                 Đặt làm địa chỉ mặc định
                </label>
           
               <div class="add-check2">
                 <input class="add-check-input" type="radio" name="loai_dia_chi" id="nha_rieng" value="nha_rieng" checked>
                 <label class="add-check-label" for="nha_rieng">
                  Nhà Riêng
                </label>
                </div>

            <div class="add-check2">
                 <input class="add-check-input" type="radio" name="loai_dia_chi" id="van_phong" value="van_phong">
                 <label class="add-check-label" for="van_phong">
                  Văn Phòng
                 </label>
            </div>
     </div>
            <button type="submit" name="submit" class="btn-send">
                <i class="fas fa-paper-plane"></i>
                Hoàn Thành
            </button>

        </form>

</html>

<style>

  html {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
  }
  form {
    width: 800px;
    padding: 24px;
    border-radius: 4px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    background-color: #f5f5f5;
    margin: 20px;
    
  }
  
  .add-form-group1 {
    display: flex;
    gap: 20px; /* Khoảng cách giữa 2 ô */
    margin-bottom: 15px;
  }

  .add-form-group2 {
    display: flex;
    gap: 12px;
    margin-bottom: 15px;
    flex-direction: column;
  }

  input {
    padding: 12px;
    border: 1px solid #dbdbdb;
    border-radius: 5px;
    outline: none;
  }
  input:focus {
    border-color: #2d5a27; /* Màu nhấn khi click vào */
  }

  .add-type{
    display: flex;
  }

  .btn-send {
     background-color: #ee4d2d; 
    color: white;
    border: none;
    cursor: pointer;
    float: right;
    border-radius: 2px;
  }
</style>