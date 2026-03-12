<?php require_once "./view/header.php"; ?>

<body>
  <?php
    require_once "view/header1.php";
    require_once "connect.php";

    // Xử lý thông báo
    if(isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    if(isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }

    // Xử lý cập nhật thông tin khách hàng
    if(isset($_GET["id"])) {
        $id = $_GET["id"];
        $sql = "SELECT * FROM customer WHERE makh = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $customer = mysqli_fetch_assoc($result);
        
        if($customer) {
            ?>
            <div class="main">
                <div class="page-header">
                    <h1>Cập nhật thông tin khách hàng</h1>
                    <p>Chỉnh sửa thông tin chi tiết của khách hàng</p>
                </div>
                
                <div class="content-wrapper">
                    <div class="form-container">
  <form action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="txt_fullname">Họ và tên</label>
                                <input type="text" id="txt_fullname" name="txt_fullname" 
                                       placeholder="Nhập đầy đủ họ tên"
                                       value="<?php echo htmlspecialchars($customer["tenkh"]); ?>" 
                                       required />
                            </div>

                            <div class="form-group">
                                <label for="txt_phone">Số điện thoại</label>
                                <input type="tel" id="txt_phone" name="txt_phone" 
                                       placeholder="Nhập số điện thoại" 
                                       value="<?php echo htmlspecialchars($customer["phone"]); ?>" 
                                       required />
                            </div>

                            <div class="form-group">
                                <label for="txt_email">Email</label>
                                <input type="email" id="txt_email" name="txt_email" 
                                       placeholder="Nhập địa chỉ email"
                                       value="<?php echo htmlspecialchars($customer["email"]); ?>" 
                                       required />
                            </div>

                            <div class="form-group">
                                <label for="txt_address_gh">Địa chỉ giao hàng</label>
                                <textarea id="txt_address_gh" name="txt_address_gh" rows="4" 
                                          placeholder="Nhập địa chỉ giao hàng chi tiết"><?php echo htmlspecialchars($customer["diachi_giaohang"]); ?></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="update" class="btn btn-primary">
                                    <span class="btn-icon">💾</span>
                                    Cập nhật
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <span class="btn-icon">🔄</span>
                                    Làm mới
                                </button>
                                <a href="KH.php" class="btn btn-outline">
                                    <span class="btn-icon">⬅️</span>
                                    Quay lại
                                </a>
                            </div>
  </form>
                    </div>
                </div>
            </div>
        <?php

            // Xử lý cập nhật
            if(isset($_POST["update"])) {
                $txt_fullname = trim($_POST["txt_fullname"]);
                $txt_email = trim($_POST["txt_email"]);
                $txt_phone = trim($_POST['txt_phone']);
                $txt_address_gh = trim($_POST["txt_address_gh"]);

                // Validate input
                if(empty($txt_fullname) || empty($txt_email) || empty($txt_phone)) {
                    $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin bắt buộc";
                } else {
                    $sql = "UPDATE customer SET tenkh=?, phone=?, email=?, diachi_giaohang=? WHERE makh=?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "sssss", $txt_fullname, $txt_phone, $txt_email, $txt_address_gh, $id);
                    $result = mysqli_stmt_execute($stmt);
                    
                    if($result) {
                        $_SESSION['success_message'] = "Cập nhật thông tin khách hàng thành công!";
                    } else {
                        $_SESSION['error_message'] = "Lỗi khi cập nhật thông tin";
                    }
                }
                
                header('location: KH.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Không tìm thấy khách hàng";
            header('location: KH.php');
            exit();
        }
    } else {
        // Hiển thị danh sách khách hàng
        $sql = "SELECT * FROM customer ORDER BY tenkh";
        $rel = mysqli_query($conn, $sql);
        ?>
        <div class="main">
            <div class="page-header">
                <h1>Quản lý khách hàng</h1>
                <p>Xem và quản lý thông tin tất cả khách hàng</p>
            </div>
            
            <div class="content-wrapper">
                <div class="table-container">
    <table class="table">
      <thead>
        <tr>
                                <th>Mã KH</th>
                                <th>Họ và tên</th>
                                <th>Số điện thoại</th>
                                <th>Email</th>
                                <th>Địa chỉ giao hàng</th>
                                <th>Hành động</th>
        </tr>
      </thead>
                        <tbody>
                            <?php
                            if(mysqli_num_rows($rel) > 0) {
                                while($row = mysqli_fetch_assoc($rel)) {
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="customer-code">#<?php echo htmlspecialchars($row["makh"]); ?></span>
                                        </td>
                                        <td>
                                            <div class="customer-info">
                                                <h4 class="customer-name"><?php echo htmlspecialchars($row["tenkh"]); ?></h4>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="phone-number"><?php echo htmlspecialchars($row["phone"]); ?></span>
                                        </td>
                                        <td>
                                            <span class="email-address"><?php echo htmlspecialchars($row["email"]); ?></span>
                                        </td>
                                        <td>
                                            <p class="address-text"><?php echo htmlspecialchars($row["diachi_giaohang"]); ?></p>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?id=<?php echo urlencode($row['makh']); ?>" 
                                                   class="btn btn-primary btn-sm" title="Sửa thông tin">
                                                    ✏️ Sửa
                                                </a>
                                                <a href="xoa_kh.php?id=<?php echo urlencode($row['makh']); ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?')"
                                                   title="Xóa khách hàng">
                                                    🗑️ Xóa
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
        <?php
                                }
                            } else {
            ?>
      <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="empty-state">
                                            <div class="empty-icon">👥</div>
                                            <h3>Chưa có khách hàng nào</h3>
                                            <p>Khách hàng sẽ xuất hiện khi họ đặt hàng</p>
                                        </div>
                                    </td>
      </tr>
            <?php   
        }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 40px;
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
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            transform: translateY(-2px);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .table th {
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 20px 15px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .customer-code {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 6px 10px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,123,255,0.3);
        }
        
        .customer-name {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .phone-number {
            font-weight: 600;
            color: #007bff;
            font-size: 14px;
        }
        
        .email-address {
            color: #28a745;
            font-size: 14px;
            word-break: break-all;
        }
        
        .address-text {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.4;
            max-width: 200px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
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
        
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
        }
        
        .btn-danger:hover {
            background: linear-gradient(45deg, #c82333, #bd2130);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
            color: white;
            text-decoration: none;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
        }
        
        .btn-icon {
            font-size: 16px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #6c757d;
            font-size: 1.5rem;
        }
        
        .empty-state p {
            margin: 0;
            color: #adb5bd;
            font-size: 1rem;
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
            
            .table-container {
                border-radius: 10px;
                overflow-x: auto;
            }
            
            .table th,
            .table td {
                padding: 15px 10px;
                font-size: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .btn-sm {
                justify-content: center;
                width: 100%;
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
            
            .table th,
            .table td {
                padding: 12px 8px;
                font-size: 13px;
            }
            
            .customer-code {
                font-size: 11px;
                padding: 4px 8px;
            }
        }
    </style>
    
    <?php require_once "./view/footer.php"; ?>
</body>
</html>
