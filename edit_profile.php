<?php
require_once("connect.php");
require_once ("./view/header.php");
require_once("./view/header1.php");
?>
<link href="public/stylee.css" rel="stylesheet" type="text/css" />

<style>
/* Override old footer styles to prevent conflicts */
.footer {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
    border-top: none !important;
    color: #ecf0f1 !important;
    font-weight: normal !important;
    height: auto !important;
    line-height: normal !important;
    bottom: auto !important;
    left: auto !important;
    width: 100% !important;
    text-align: left !important;
    padding: 50px 0 0 0 !important;
    margin-top: 80px !important;
    position: relative !important;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #e74c3c, #f39c12, #f1c40f, #27ae60, #3498db, #9b59b6);
}

/* Override old footer grid styles */
.gird_row {
    display: none !important;
}

.gird_coloum {
    display: none !important;
}

.footer_item_link {
    display: none !important;
}

.footer_list {
    display: none !important;
}
</style>

<?php
// Kiểm tra đăng nhập
if(!isset($_SESSION['Username'])) {
    header('location: login.php');
    exit();
}

// Xử lý thông báo
if(isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if(isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Lấy thông tin user hiện tại
$current_username = $_SESSION['Username'];
$sql = "SELECT * FROM dangkithanhvien WHERE Username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $current_username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if(!$user) {
    $_SESSION['error_message'] = "Không tìm thấy thông tin người dùng";
    header('location: index.php');
    exit();
}

// Xử lý cập nhật thông tin
if(isset($_POST["update_profile"])) {
    $Fullname = trim($_POST["Fullname"]);
    $Email = trim($_POST["Email"]);
    $Gioitinh = trim($_POST["Gioitinh"]);
    $Quoctich = trim($_POST["Quoctich"]);
    $Diachi = trim($_POST["Diachi"]);
    $Password = trim($_POST["Password"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    
    // Validate input
    if(empty($Fullname) || empty($Email)) {
        $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin bắt buộc";
    } else {
        // Xử lý sở thích
        $xemphim = isset($_POST["xemphim"]) ? $_POST["xemphim"] : "";
        $web = isset($_POST["web"]) ? $_POST["web"] : "";
        $ngu = isset($_POST["ngu"]) ? $_POST["ngu"] : "";
        $Sothich = array_filter(array($xemphim, $web, $ngu));
        $sothichcd = implode(",", $Sothich);
        
        // Xử lý upload file
        $Hinhanh = $user['Hinhanh']; // Giữ ảnh cũ nếu không upload mới
        if(isset($_FILES['uploadfile']) && $_FILES['uploadfile']['error'] == 0) {
            $file_name = $_FILES['uploadfile']['name'];
            $file_tmp = $_FILES['uploadfile']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = array('jpg', 'jpeg', 'png', 'gif');
            
            if(in_array($file_ext, $allowed_exts)) {
                $new_file_name = time() . '_' . $file_name;
                if(move_uploaded_file($file_tmp, "public/images/" . $new_file_name)) {
                    $Hinhanh = $new_file_name;
                }
            }
        }
        
        // Xử lý mật khẩu
        $final_password = $user['Password']; // Giữ mật khẩu cũ nếu không đổi
        if(!empty($Password)) {
            // Kiểm tra mật khẩu cũ
            if($Password !== $user['Password']) {
                $_SESSION['error_message'] = "Mật khẩu hiện tại không đúng";
            } elseif(empty($new_password)) {
                $_SESSION['error_message'] = "Vui lòng nhập mật khẩu mới";
            } elseif($new_password !== $confirm_password) {
                $_SESSION['error_message'] = "Mật khẩu xác nhận không khớp";
            } else {
                $final_password = $new_password;
            }
        }
        
        // Cập nhật thông tin nếu không có lỗi
        if(!isset($_SESSION['error_message'])) {
            $sql_update = "UPDATE dangkithanhvien SET Fullname=?, Email=?, Gioitinh=?, Quoctich=?, Diachi=?, Hinhanh=?, Sothich=?, Password=? WHERE Username=?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "sssssssss", $Fullname, $Email, $Gioitinh, $Quoctich, $Diachi, $Hinhanh, $sothichcd, $final_password, $current_username);
            $result_update = mysqli_stmt_execute($stmt_update);
            
            if($result_update) {
                // Cập nhật session
                $_SESSION['Fullname'] = $Fullname;
                $_SESSION['success_message'] = "Cập nhật thông tin cá nhân thành công!";
                header('location: edit_profile.php');
                exit();
            } else {
                $_SESSION['error_message'] = "Lỗi khi cập nhật thông tin";
            }
        }
    }
}
?>

<div class="main">
        <div class="page-header">
            <h1>Chỉnh sửa thông tin cá nhân</h1>
            <p>Cập nhật thông tin tài khoản của bạn</p>
        </div>
        
        <div class="content-wrapper">
            <div class="form-container">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3 class="section-title">👤 Thông tin cơ bản</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="Username">Tên đăng nhập</label>
                                <input type="text" id="Username" value="<?php echo htmlspecialchars($user['Username']); ?>" disabled />
                                <small class="form-text">Tên đăng nhập không thể thay đổi</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="Fullname">Họ và tên <span class="required">*</span></label>
                                <input type="text" id="Fullname" name="Fullname" 
                                       placeholder="Nhập đầy đủ họ tên"
                                       value="<?php echo htmlspecialchars($user['Fullname']); ?>"
                                       required />
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="Email">Email <span class="required">*</span></label>
                                <input type="email" id="Email" name="Email" 
                                       placeholder="Nhập địa chỉ email"
                                       value="<?php echo htmlspecialchars($user['Email']); ?>"
                                       required />
                            </div>
                            
                            <div class="form-group">
                                <label for="Quoctich">Quốc tịch</label>
                                <select id="Quoctich" name="Quoctich" class="select-styled">
                                    <option value="Vietnam" <?php echo ($user['Quoctich'] == 'Vietnam') ? 'selected' : ''; ?>>Việt Nam</option>
                                    <option value="Canada" <?php echo ($user['Quoctich'] == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                    <option value="Us" <?php echo ($user['Quoctich'] == 'Us') ? 'selected' : ''; ?>>Mỹ</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Giới tính</label>
                                <div class="radio-group">
                                    <label class="radio-item">
                                        <input type="radio" name="Gioitinh" value="Nam" 
                                               <?php echo ($user['Gioitinh'] == 'Nam') ? 'checked' : ''; ?> />
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Nam</span>
                                    </label>
                                    <label class="radio-item">
                                        <input type="radio" name="Gioitinh" value="Nữ" 
                                               <?php echo ($user['Gioitinh'] == 'Nữ') ? 'checked' : ''; ?> />
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Nữ</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="Diachi">Địa chỉ</label>
                                <input type="text" id="Diachi" name="Diachi" 
                                       placeholder="Nhập địa chỉ"
                                       value="<?php echo htmlspecialchars($user['Diachi']); ?>" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">🔐 Thay đổi mật khẩu</h3>
                        <div class="password-info">
                            <p>Để thay đổi mật khẩu, vui lòng điền mật khẩu hiện tại và mật khẩu mới</p>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="Password">Mật khẩu hiện tại</label>
                                <input type="password" id="Password" name="Password" 
                                       placeholder="Nhập mật khẩu hiện tại" />
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Mật khẩu mới</label>
                                <input type="password" id="new_password" name="new_password" 
                                       placeholder="Nhập mật khẩu mới" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu mới</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Nhập lại mật khẩu mới" />
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">🖼️ Hình ảnh & Sở thích</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="uploadfile">Hình ảnh đại diện</label>
                                <div class="file-upload">
                                    <input type="file" id="uploadfile" name="uploadfile" accept="image/*" />
                                    <div class="file-info">
                                        <span class="file-icon">📁</span>
                                        <span class="file-text">Chọn file hình ảnh mới</span>
                                    </div>
                                </div>
                                <?php if(!empty($user['Hinhanh'])): ?>
                                <div class="current-image">
                                    <p>Ảnh hiện tại:</p>
                                    <img src="public/images/<?php echo htmlspecialchars($user['Hinhanh']); ?>" 
                                         alt="Ảnh đại diện" class="profile-image" />
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label>Sở thích</label>
                                <div class="checkbox-group">
                                    <?php 
                                    $current_hobbies = explode(',', $user['Sothich']);
                                    ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="xemphim" value="xemphim" 
                                               <?php echo in_array('xemphim', $current_hobbies) ? 'checked' : ''; ?> />
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-label">🎬 Xem phim</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="web" value="web" 
                                               <?php echo in_array('web', $current_hobbies) ? 'checked' : ''; ?> />
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-label">🌐 Lướt web</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="ngu" value="ngu" 
                                               <?php echo in_array('ngu', $current_hobbies) ? 'checked' : ''; ?> />
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-label">😴 Ngủ</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <span class="btn-icon">💾</span>
                            Cập nhật thông tin
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <span class="btn-icon">🔄</span>
                            Làm mới
                        </button>
                        <a href="index.php" class="btn btn-outline">
                            <span class="btn-icon">⬅️</span>
                            Quay lại trang chủ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
       .page-header {
      text-align: center;
      margin-bottom: 40px;
      padding: 40px 30%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
        
        .page-header h1 {
            margin: 0 0 15px 0;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .page-header p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 40px;
        }
        
        .form-section {
            margin-bottom: 40px;
        }
        
        .section-title {
            margin: 0 0 25px 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f3f4;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .required {
            color: #dc3545;
        }
        
        .form-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .form-group input,
        .form-group textarea,
        .select-styled {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group input:disabled {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .select-styled:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            transform: translateY(-2px);
        }
        
        .password-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .password-info p {
            margin: 0;
            color: #1976d2;
            font-size: 14px;
        }
        
        .current-image {
            margin-top: 15px;
        }
        
        .current-image p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .profile-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        /* Radio buttons */
        .radio-group {
            display: flex;
            gap: 20px;
        }
        
        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-item input[type="radio"] {
            display: none;
        }
        
        .radio-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #ddd;
            border-radius: 50%;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .radio-item input[type="radio"]:checked + .radio-custom {
            border-color: #007bff;
            background: #007bff;
        }
        
        .radio-item input[type="radio"]:checked + .radio-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
        }
        
        .radio-label {
            font-weight: 500;
            color: #333;
        }
        
        /* Checkboxes */
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .checkbox-item input[type="checkbox"] {
            display: none;
        }
        
        .checkbox-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #ddd;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .checkbox-item input[type="checkbox"]:checked + .checkbox-custom {
            border-color: #007bff;
            background: #007bff;
        }
        
        .checkbox-item input[type="checkbox"]:checked + .checkbox-custom::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .checkbox-label {
            font-weight: 500;
            color: #333;
        }
        
        /* File upload */
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .file-upload:hover .file-info {
            border-color: #007bff;
            background: #e3f2fd;
        }
        
        .file-icon {
            font-size: 20px;
        }
        
        .file-text {
            color: #666;
            font-weight: 500;
        }
        
        /* Form actions */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f1f3f4;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #545b62);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(45deg, #545b62, #495057);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108,117,125,0.3);
            color: white;
            text-decoration: none;
        }
        
        .btn-outline {
            background: transparent;
            color: #007bff;
            border: 2px solid #007bff;
        }
        
        .btn-outline:hover {
            background: #007bff;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
            text-decoration: none;
        }
        
        .btn-icon {
            font-size: 16px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
                margin-bottom: 30px;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .content-wrapper {
                padding: 0 15px;
            }
            
            .form-container {
                padding: 25px;
                margin: 0 15px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                justify-content: center;
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
        }
    </style>
</div>

<?php include('./view/footer.php'); ?> 