<?php require_once "./view/header.php"; ?>

<body>
  <?php
    require_once "connect.php";
    require_once "view/header1.php";
    
    $success_message = '';
    $error_message = '';
    
    if(isset($_POST["btn_submit"])) {
        $ma_loaisp = $_POST["ma_loaisp"];
        $ma_sp = $_POST["ma_sp"];
        $tensp = $_POST["tensp"];
        $motasp = $_POST["motasp"];
        $gianhap = $_POST["gianhap"];
        $giaxuat = $_POST["giaxuat"];
        $khuyenmai = $_POST["khuyenmai"];
        $soluong = $_POST["soluong"];
        $ngay_nhap = $_POST["ngay_nhap"];
        
        // Xử lý upload ảnh
        $anhsp = '';
        if(isset($_FILES["anhsp"]) && $_FILES["anhsp"]["error"] == 0) {
            $file_name = $_FILES["anhsp"]["name"];
            $file_tmp = $_FILES["anhsp"]["tmp_name"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Kiểm tra định dạng file
            $allowed_exts = array("jpg", "jpeg", "png", "gif");
            if(in_array($file_ext, $allowed_exts)) {
                $new_file_name = $ma_sp . '_' . time() . '.' . $file_ext;
                $upload_path = "public/images/" . $new_file_name;
                
                if(move_uploaded_file($file_tmp, $upload_path)) {
                    $anhsp = $new_file_name;
                } else {
                    $error_message = "Lỗi upload ảnh!";
                }
            } else {
                $error_message = "Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)!";
            }
        }
        
        if(empty($error_message)) {
            // Kiểm tra mã sản phẩm đã tồn tại chưa
            $check_sql = "SELECT * FROM adproduct WHERE ma_sp = '$ma_sp'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if(mysqli_num_rows($check_result) > 0) {
                $error_message = "Mã sản phẩm đã tồn tại!";
            } else {
                // Thêm sản phẩm mới
                $sql = "INSERT INTO adproduct (Ma_loaisp, ma_sp, tensp, anhsp, motasp, gianhap, giaxuat, khuyenmai, soluong, ngay_nhap) 
                        VALUES ('$ma_loaisp', '$ma_sp', '$tensp', '$anhsp', '$motasp', '$gianhap', '$giaxuat', '$khuyenmai', '$soluong', '$ngay_nhap')";
                
                if(mysqli_query($conn, $sql)) {
                    $_SESSION['success_message'] = "Thêm sản phẩm thành công!";
                    header("Location: sp.php");
                    exit();
                } else {
                    $error_message = "Có lỗi xảy ra khi thêm sản phẩm!";
                }
            }
        }
    }
  ?>

  <div class="main">
    <div class="page-header">
      <h1>➕ Thêm sản phẩm mới</h1>
      <p>Điền thông tin để thêm sản phẩm mới vào hệ thống</p>
    </div>
    
    <div class="content-wrapper">
      <div class="form-container">
        <?php if(!empty($error_message)): ?>
          <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data">
          <div class="form-grid">
            <div class="form-group">
              <label for="ma_loaisp">Mã loại sản phẩm *</label>
              <select name="ma_loaisp" id="ma_loaisp" class="form-control" required>
                <option value="">Chọn loại sản phẩm</option>
                <?php 
                $sql = "SELECT * FROM adproducttype ORDER BY Ma_loaisp";
             $rel = mysqli_query($conn, $sql);
                if(mysqli_num_rows($rel) > 0) {
                while ($r = mysqli_fetch_assoc($rel)) {
                        $selected = (isset($_POST['ma_loaisp']) && $_POST['ma_loaisp'] == $r['Ma_loaisp']) ? 'selected' : '';
                        echo '<option value="' . $r['Ma_loaisp'] . '" ' . $selected . '>' . $r['Ma_loaisp'] . '</option>';
                    }
                }
                    ?>
          </select>
            </div>
            
            <div class="form-group">
              <label for="ma_sp">Mã sản phẩm *</label>
              <input type="text" name="ma_sp" id="ma_sp" class="form-control" 
                     value="<?php echo isset($_POST['ma_sp']) ? htmlspecialchars($_POST['ma_sp']) : ''; ?>" 
                     placeholder="Nhập mã sản phẩm" required/>
            </div>
            
            <div class="form-group">
              <label for="tensp">Tên sản phẩm *</label>
              <input type="text" name="tensp" id="tensp" class="form-control" 
                     value="<?php echo isset($_POST['tensp']) ? htmlspecialchars($_POST['tensp']) : ''; ?>" 
                     placeholder="Nhập tên sản phẩm" required/>
            </div>
            
            <div class="form-group">
              <label for="anhsp">Ảnh sản phẩm</label>
              <div class="file-input-wrapper">
                <input type="file" name="anhsp" id="anhsp" class="form-control file-input" accept="image/*"/>
                <div class="file-info">
                  <span class="file-icon">📷</span>
                  <span class="file-text">Chọn ảnh sản phẩm (JPG, PNG, GIF)</span>
                </div>
              </div>
            </div>
            
            <div class="form-group full-width">
              <label for="motasp">Mô tả sản phẩm</label>
              <textarea name="motasp" id="motasp" class="form-control" rows="4" 
                        placeholder="Nhập mô tả chi tiết về sản phẩm"><?php echo isset($_POST['motasp']) ? htmlspecialchars($_POST['motasp']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
              <label for="gianhap">Giá nhập (VNĐ) *</label>
              <input type="number" name="gianhap" id="gianhap" class="form-control" 
                     value="<?php echo isset($_POST['gianhap']) ? $_POST['gianhap'] : ''; ?>" 
                     min="0" placeholder="0" required/>
            </div>
            
            <div class="form-group">
              <label for="giaxuat">Giá bán (VNĐ) *</label>
              <input type="number" name="giaxuat" id="giaxuat" class="form-control" 
                     value="<?php echo isset($_POST['giaxuat']) ? $_POST['giaxuat'] : ''; ?>" 
                     min="0" placeholder="0" required/>
            </div>
            
            <div class="form-group">
              <label for="khuyenmai">Khuyến mãi (%)</label>
              <input type="number" name="khuyenmai" id="khuyenmai" class="form-control" 
                     value="<?php echo isset($_POST['khuyenmai']) ? $_POST['khuyenmai'] : '0'; ?>" 
                     min="0" max="100" placeholder="0-100"/>
            </div>
            
            <div class="form-group">
              <label for="soluong">Số lượng *</label>
              <input type="number" name="soluong" id="soluong" class="form-control" 
                     value="<?php echo isset($_POST['soluong']) ? $_POST['soluong'] : ''; ?>" 
                     min="0" placeholder="0" required/>
            </div>
            
            <div class="form-group">
              <label for="ngay_nhap">Ngày nhập *</label>
              <input type="date" name="ngay_nhap" id="ngay_nhap" class="form-control" 
                     value="<?php echo isset($_POST['ngay_nhap']) ? $_POST['ngay_nhap'] : date('Y-m-d'); ?>" required/>
            </div>
          </div>
          
          <div class="form-actions">
            <a href="sp.php" class="btn btn-secondary">← Quay lại</a>
            <button type="submit" name="btn_submit" class="btn btn-primary">➕ Thêm sản phẩm</button>
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
    
   
    
    .page-header p {
      margin: 0;
      font-size: 1.1rem;
      color: white;
    }
    
    .page-header p {
      margin: 0;
      font-size: 1.1rem;
      color: white;
    }
    
    .content-wrapper {
      max-width: 1600px;
      margin: 0 auto;
    }
    
    .form-container {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .form-group {
      display: flex;
      flex-direction: column;
    }
    
    .form-group.full-width {
      grid-column: 1 / -1;
    }
    
    .form-group label {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      font-size: 14px;
    }
    
    .form-group label::after {
      content: " *";
      color: #dc3545;
      font-weight: bold;
    }
    
    .form-group label:not([for="anhsp"]):not([for="motasp"]):not([for="khuyenmai"])::after {
      content: " *";
      color: #dc3545;
      font-weight: bold;
    }
    
    .form-group label[for="anhsp"]::after,
    .form-group label[for="motasp"]::after,
    .form-group label[for="khuyenmai"]::after {
      content: "";
    }
    
    .form-control {
      padding: 12px 16px;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      font-size: 14px;
      transition: all 0.3s ease;
      background: #f8f9fa;
    }
    
    .form-control:focus {
      outline: none;
      border-color: #667eea;
      background: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-control:invalid {
      border-color: #dc3545;
    }
    
    select.form-control {
      cursor: pointer;
    }
    
    .file-input-wrapper {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    
    .file-info {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 10px;
      border: 2px dashed #dee2e6;
      text-align: center;
    }
    
    .file-icon {
      font-size: 24px;
    }
    
    .file-text {
      font-size: 14px;
      color: #6c757d;
      font-weight: 500;
    }
    
    .file-input {
      padding: 10px;
      border: 2px dashed #667eea;
      background: rgba(102, 126, 234, 0.05);
      cursor: pointer;
    }
    
    .file-input:hover {
      background: rgba(102, 126, 234, 0.1);
    }
    
    textarea.form-control {
      resize: vertical;
      min-height: 100px;
    }
    
    .form-actions {
      display: flex;
      gap: 15px;
      justify-content: center;
      padding-top: 20px;
      border-top: 2px solid #f8f9fa;
    }
    
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 25px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 140px;
      justify-content: center;
    }
    
    .btn-primary {
      background: linear-gradient(45deg, #667eea, #764ba2);
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
      color: white;
    }
    
    .btn-secondary {
      background: linear-gradient(45deg, #6c757d, #495057);
      color: white;
    }
    
    .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
      color: white;
    }
    
    .alert {
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-weight: 500;
    }
    
    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .main {
        padding: 15px;
      }
      
      .page-header {
        padding: 20px;
        margin-bottom: 30px;
      }
      
      .page-header h1 {
        font-size: 2rem;
      }
      
      .form-container {
        padding: 25px;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .form-actions {
        flex-direction: column;
      }
      
      .btn {
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
      
      .file-info {
        flex-direction: column;
        gap: 8px;
      }
    }
  </style>
    
    <?php require_once "./view/footer.php"; ?>
</body>
</html>