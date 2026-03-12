<?php require_once "./view/header.php"; ?>

<body>
  <?php 
    require_once "connect.php";
    require_once "view/header1.php";
    
    $success_message = '';
    $error_message = '';
    
    $txt_maloaisp = isset($_POST["txt_maloaisp"]) ? $_POST["txt_maloaisp"] : "";
    $txt_tenloaisp = isset($_POST["txt_tenloaisp"]) ? $_POST["txt_tenloaisp"] : "";
    $txt_motaloaisp = isset($_POST["txt_motaloaisp"]) ? $_POST["txt_motaloaisp"] : "";

    if(isset($_POST["btn_save"])) {
        if(empty($txt_maloaisp) || empty($txt_tenloaisp)) {
            $error_message = "Vui lòng điền đầy đủ thông tin bắt buộc!";
        } else {
            $sql = "SELECT * FROM adproducttype WHERE Ma_loaisp = '$txt_maloaisp'";
            $rel = mysqli_query($conn, $sql);
            
            if(mysqli_num_rows($rel) > 0) {
                $error_message = "Mã loại sản phẩm đã tồn tại!";
            } else {
                $sql1 = "INSERT INTO adproducttype (Ma_loaisp, Ten_loaisp, Mota_loaisp) VALUES ('$txt_maloaisp', '$txt_tenloaisp', '$txt_motaloaisp')";
                $rel = mysqli_query($conn, $sql1);
                
                if($rel) {
                    $_SESSION['success_message'] = "Thêm loại sản phẩm thành công!";
                    header('Location: dongsp.php');
                    exit();
                } else {
                    $error_message = "Có lỗi xảy ra khi thêm loại sản phẩm!";
                }
            }
        }
    }
  ?>

  <div class="main">
    <div class="page-header">
      <h1>➕ Thêm loại sản phẩm mới</h1>
      <p>Điền thông tin để thêm loại sản phẩm mới vào hệ thống</p>
    </div>
    
    <div class="content-wrapper">
      <div class="form-container">
        <?php if(!empty($error_message)): ?>
          <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="" method="post">
          <div class="form-grid">
            <div class="form-group">
              <label for="txt_maloaisp">Mã loại sản phẩm *</label>
              <input type="text" name="txt_maloaisp" id="txt_maloaisp" 
                     value="<?php echo htmlspecialchars($txt_maloaisp); ?>" 
                     class="form-control" placeholder="Nhập mã loại sản phẩm" required/>
            </div>
            
            <div class="form-group">
              <label for="txt_tenloaisp">Tên loại sản phẩm *</label>
              <input type="text" name="txt_tenloaisp" id="txt_tenloaisp" 
                     value="<?php echo htmlspecialchars($txt_tenloaisp); ?>" 
                     class="form-control" placeholder="Nhập tên loại sản phẩm" required/>
            </div>
            
            <div class="form-group full-width">
              <label for="txt_motaloaisp">Mô tả loại sản phẩm</label>
              <textarea name="txt_motaloaisp" id="txt_motaloaisp" 
                        class="form-control" rows="4" 
                        placeholder="Nhập mô tả chi tiết về loại sản phẩm"><?php echo htmlspecialchars($txt_motaloaisp); ?></textarea>
            </div>
          </div>
          
          <div class="form-actions">
            <a href="dongsp.php" class="btn btn-secondary">← Quay lại</a>
            <button type="submit" name="btn_save" class="btn btn-primary">➕ Thêm loại sản phẩm</button>
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
      opacity: 0.9;
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
    
    .form-group label[for="txt_motaloaisp"]::after {
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
    
    .form-control::placeholder {
      color: #adb5bd;
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
    }
  </style>

  <?php require_once "./view/footer.php"; ?>
</body>
</html>