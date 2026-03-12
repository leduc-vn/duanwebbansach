

<?php
require_once("connect.php");
require_once("./view/header.php");

// Xử lý thông báo
if(isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if(isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Xử lý đăng ký
if(isset($_POST["btn_dangkithanhvien"])) {
    $Fullname = trim($_POST["Fullname"]);
    $Username = trim($_POST["Username"]);
    $Password = trim($_POST["Password"]);
    $Quoctich = trim($_POST["Quoctich"]);
    $Email = trim($_POST["Email"]);
    $Diachi = trim($_POST["Diachi"]);
    $Gioitinh = trim($_POST["Gioitinh"]);
    $quyen = trim($_POST["quyen"]);
    
    // Validate input
    if(empty($Fullname) || empty($Username) || empty($Password) || empty($Email)) {
        $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin bắt buộc";
    } else {
        // Xử lý sở thích
        $xemphim = isset($_POST["xemphim"]) ? $_POST["xemphim"] : "";
        $web = isset($_POST["web"]) ? $_POST["web"] : "";
        $ngu = isset($_POST["ngu"]) ? $_POST["ngu"] : "";
        $Sothich = array_filter(array($xemphim, $web, $ngu));
	$sothichcd = implode(",", $Sothich);
        
        // Xử lý upload file
        $Hinhanh = "";
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
        
        // Kiểm tra username đã tồn tại
        $sql = "SELECT * FROM dangkithanhvien WHERE Username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $Username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) > 0) {
            $_SESSION['error_message'] = "Tên đăng nhập đã tồn tại, vui lòng chọn tên khác";
        } else {
            // Thêm thành viên mới
            $sql1 = "INSERT INTO dangkithanhvien (Fullname, Username, Password, Email, Gioitinh, Quoctich, Diachi, Hinhanh, Sothich, quyen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt1 = mysqli_prepare($conn, $sql1);
            mysqli_stmt_bind_param($stmt1, "ssssssssss", $Fullname, $Username, $Password, $Email, $Gioitinh, $Quoctich, $Diachi, $Hinhanh, $sothichcd, $quyen);
            $result1 = mysqli_stmt_execute($stmt1);
            
            if($result1) {
                $_SESSION['success_message'] = "Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.";
                header('location: login.php');
                exit();
            } else {
                $_SESSION['error_message'] = "Lỗi khi đăng ký, vui lòng thử lại";
            }
        }
    }
}
?>

<body>
    <div class="main">
        <div class="page-header">
            <h1>Đăng ký thành viên</h1>
            <p>Tạo tài khoản mới để truy cập vào hệ thống</p>
        </div>
        
        <div class="content-wrapper">
            <div class="form-container">
<form action="" method="post" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3 class="section-title">📝 Thông tin cá nhân</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="Fullname">Họ và tên <span class="required">*</span></label>
                                <input type="text" id="Fullname" name="Fullname" 
                                       placeholder="Nhập đầy đủ họ tên"
                                       value="<?php echo isset($_POST['Fullname']) ? htmlspecialchars($_POST['Fullname']) : ''; ?>"
                                       required />
                            </div>
                            
                            <div class="form-group">
                                <label for="Username">Tên đăng nhập <span class="required">*</span></label>
                                <input type="text" id="Username" name="Username" 
                                       placeholder="Nhập tên đăng nhập"
                                       value="<?php echo isset($_POST['Username']) ? htmlspecialchars($_POST['Username']) : ''; ?>"
                                       required />
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="Password">Mật khẩu <span class="required">*</span></label>
                                <input type="password" id="Password" name="Password" 
                                       placeholder="Nhập mật khẩu"
                                       required />
                            </div>
                            
                            <div class="form-group">
                                <label for="Email">Email <span class="required">*</span></label>
                                <input type="email" id="Email" name="Email" 
                                       placeholder="Nhập địa chỉ email"
                                       value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>"
                                       required />
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Giới tính</label>
                                <div class="radio-group">
                                    <label class="radio-item">
                                        <input type="radio" name="Gioitinh" value="Nam" 
                                               <?php echo (isset($_POST['Gioitinh']) && $_POST['Gioitinh'] == 'Nam') ? 'checked' : ''; ?> />
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Nam</span>
                                    </label>
                                    <label class="radio-item">
                                        <input type="radio" name="Gioitinh" value="Nữ" 
                                               <?php echo (!isset($_POST['Gioitinh']) || $_POST['Gioitinh'] == 'Nữ') ? 'checked' : ''; ?> />
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Nữ</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="Quoctich">Quốc tịch</label>
                                <select id="Quoctich" name="Quoctich" class="select-styled">
                                    <option value="Vietnam" <?php echo (isset($_POST['Quoctich']) && $_POST['Quoctich'] == 'Vietnam') ? 'selected' : ''; ?>>Việt Nam</option>
                                    <option value="Canada" <?php echo (isset($_POST['Quoctich']) && $_POST['Quoctich'] == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                    <option value="Us" <?php echo (isset($_POST['Quoctich']) && $_POST['Quoctich'] == 'Us') ? 'selected' : ''; ?>>Mỹ</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="Diachi">Địa chỉ</label>
                            <textarea id="Diachi" name="Diachi" rows="4" 
                                      placeholder="Nhập địa chỉ chi tiết"><?php echo isset($_POST['Diachi']) ? htmlspecialchars($_POST['Diachi']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">🖼️ Hình ảnh & Sở thích</h3>
                        
                        <div class="form-group">
                            <label for="uploadfile">Hình ảnh đại diện</label>
                            <div class="file-upload">
                                <input type="file" id="uploadfile" name="uploadfile" accept="image/*" />
                                <div class="file-info">
                                    <span class="file-icon">📁</span>
                                    <span class="file-text">Chọn file hình ảnh</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Sở thích</label>
                            <div class="checkbox-group">
                                <label class="checkbox-item">
                                    <input type="checkbox" name="xemphim" value="xemphim" 
                                           <?php echo (isset($_POST['xemphim'])) ? 'checked' : ''; ?> />
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-label">🎬 Xem phim</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="web" value="web" 
                                           <?php echo (isset($_POST['web'])) ? 'checked' : ''; ?> />
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-label">🌐 Lướt web</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="ngu" value="ngu" 
                                           <?php echo (isset($_POST['ngu'])) ? 'checked' : ''; ?> />
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-label">😴 Ngủ</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="quyen">Mức độ truy cập</label>
                            <select id="quyen" name="quyen" class="select-styled">
                                <option value="0" <?php echo (!isset($_POST['quyen']) || $_POST['quyen'] == '0') ? 'selected' : ''; ?>>Khách hàng</option>
                                <option value="1" <?php echo (isset($_POST['quyen']) && $_POST['quyen'] == '1') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="btn_dangkithanhvien" class="btn btn-primary">
                            <span class="btn-icon">✅</span>
                            Đăng ký thành viên
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <span class="btn-icon">🔄</span>
                            Làm mới
                        </button>
                        <a href="login.php" class="btn btn-outline">
                            <span class="btn-icon">⬅️</span>
                            Đã có tài khoản? Đăng nhập
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
            padding: 30px;
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
        
        .form-group input:focus,
        .form-group textarea:focus,
        .select-styled:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            transform: translateY(-2px);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
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
            border: 10px solid #ddd;
            border-radius: 4px;
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
            border: 10px solid #ddd;
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
            border: 10px dashed #ddd;
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
    
    <?php require_once "./view/footer.php"; ?>
</body>
</html>
