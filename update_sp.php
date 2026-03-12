<?php require_once "./view/header.php"; ?>

<body>
  <?php 
    require_once "connect.php";
    require_once "view/header1.php";
    
    $id = $_GET["id"];
    $sql = "SELECT * FROM adproduct WHERE ma_sp = '$id'";
    $rel = mysqli_query($conn, $sql);
    $sanpham = mysqli_fetch_assoc($rel);
    
    if (!$sanpham) {
        echo '<div class="alert alert-danger">Không tìm thấy sản phẩm</div>';
        exit();
    }
    
    $Ma_loaisp = isset($_POST["Ma_loaisp"]) ? $_POST["Ma_loaisp"] : $sanpham["Ma_loaisp"];
    $ma_sp = isset($_POST["ma_sp"]) ? $_POST["ma_sp"] : $sanpham["ma_sp"];
    $tensp = isset($_POST["tensp"]) ? $_POST["tensp"] : $sanpham["tensp"];
    $anhsp = isset($_POST["anhsp"]) ? $_POST["anhsp"] : $sanpham["anhsp"]; 
    $motasp = isset($_POST["motasp"]) ? $_POST["motasp"] : $sanpham["motasp"];
    $gianhap = isset($_POST["gianhap"]) ? $_POST["gianhap"] : $sanpham["gianhap"];
    $giaxuat = isset($_POST["giaxuat"]) ? $_POST["giaxuat"] : $sanpham["giaxuat"];
    $khuyenmai = isset($_POST["khuyenmai"]) ? $_POST["khuyenmai"] : $sanpham["khuyenmai"];
    $soluong = isset($_POST["soluong"]) ? $_POST["soluong"] : $sanpham["soluong"];
    $ngay_nhap = isset($_POST["ngay_nhap"]) ? $_POST["ngay_nhap"] : $sanpham["ngay_nhap"];
    

    if(isset($_FILES['anhsp']) && $_FILES['anhsp']['error'] == 0){
    $target_dir = "public/images/";
    $filename = basename($_FILES["anhsp"]["name"]);
    $target_file = $target_dir . $filename;
    
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if(in_array($imageFileType, $allowed_types)){
        // Upload file lên thư mục
        if(move_uploaded_file($_FILES["anhsp"]["tmp_name"], $target_file)){
            $anhsp = $filename; // Cập nhật lại ảnh mới vào DB
        } else {
            $_SESSION['error_message'] = "Không thể tải ảnh lên.";
        }
    } else {
        $_SESSION['error_message'] = "Chỉ chấp nhận file ảnh JPG, JPEG, PNG, GIF.";
    }
}


    if(isset($_POST["btn_update"])) {
        $sql1 = "UPDATE adproduct SET Ma_loaisp = '$Ma_loaisp', ma_sp = '$ma_sp', tensp = '$tensp', anhsp = '$anhsp', motasp = '$motasp', gianhap = '$gianhap', giaxuat = '$giaxuat', khuyenmai = '$khuyenmai', soluong = '$soluong', ngay_nhap = '$ngay_nhap' WHERE ma_sp = '$id'";
        $rel1 = mysqli_query($conn, $sql1);
        
        if($rel1) {
            $_SESSION['success_message'] = "Cập nhật sản phẩm thành công!";
            header("Location: sp.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật sản phẩm!";
        }
    }
  ?>

  <div class="main">
    <div class="page-header">
      <h1>✏️ Cập nhật sản phẩm</h1>
      <p>Chỉnh sửa thông tin sản phẩm: <?php echo htmlspecialchars($tensp); ?></p>
    </div>
    
    <div class="content-wrapper">
      <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data">
          <div class="form-grid">
            <div class="form-group">
              <label for="Ma_loaisp">Mã loại sản phẩm</label>
              <select name="Ma_loaisp" id="Ma_loaisp" class="form-control">
                <?php 
                $sql = "SELECT * FROM adproducttype";
                $rel = mysqli_query($conn, $sql);
                if(mysqli_num_rows($rel) > 0) {
                    while ($r = mysqli_fetch_assoc($rel)) {
                        $selected = ($r['Ma_loaisp'] == $Ma_loaisp) ? 'selected' : '';
                        echo '<option value="' . $r['Ma_loaisp'] . '" ' . $selected . '>' . $r['Ma_loaisp'] . '</option>';
                    }
                }
                ?>
              </select>
            </div>
            
            <div class="form-group">
              <label for="ma_sp">Mã sản phẩm</label>
              <input type="text" name="ma_sp" id="ma_sp" value="<?php echo htmlspecialchars($ma_sp); ?>" class="form-control" required/>
            </div>
            
            <div class="form-group">
              <label for="tensp">Tên sản phẩm</label>
              <input type="text" name="tensp" id="tensp" value="<?php echo htmlspecialchars($tensp); ?>" class="form-control" required/>
            </div>
            
            <div class="form-group">
              <label for="anhsp">Ảnh sản phẩm</label>
              <div class="file-input-wrapper">
                <input type="file" name="anhsp" id="anhsp" class="form-control file-input" accept="image/*"/>
                <div class="current-image">
                  <img src="public/images/<?php echo htmlspecialchars($anhsp); ?>" alt="Ảnh hiện tại" class="preview-image"/>
                  <span class="current-label">Ảnh hiện tại</span>
                </div>
              </div>
            </div>
            
            <div class="form-group full-width">
              <label for="motasp">Mô tả sản phẩm</label>
              <textarea name="motasp" id="motasp" class="form-control" rows="4"><?php echo htmlspecialchars($motasp); ?></textarea>
            </div>
            
            <div class="form-group">
              <label for="gianhap">Giá nhập (VNĐ)</label>
              <input type="number" name="gianhap" id="gianhap" value="<?php echo $gianhap; ?>" class="form-control" min="0" required/>
            </div>
            
            <div class="form-group">
              <label for="giaxuat">Giá bán (VNĐ)</label>
              <input type="number" name="giaxuat" id="giaxuat" value="<?php echo $giaxuat; ?>" class="form-control" min="0" required/>
            </div>
            
            <div class="form-group">
              <label for="khuyenmai">Khuyến mãi (%)</label>
              <input type="number" name="khuyenmai" id="khuyenmai" value="<?php echo $khuyenmai; ?>" class="form-control" min="0" max="100" placeholder="0-100"/>
            </div>
            
            <div class="form-group">
              <label for="soluong">Số lượng</label>
              <input type="number" name="soluong" id="soluong" value="<?php echo $soluong; ?>" class="form-control" min="0" required/>
            </div>
            
            <div class="form-group">
              <label for="ngay_nhap">Ngày nhập</label>
              <input type="date" name="ngay_nhap" id="ngay_nhap" value="<?php echo $ngay_nhap; ?>" class="form-control" required/>
            </div>
          </div>
          
          <div class="form-actions">
            <a href="sp.php" class="btn btn-secondary">← Quay lại</a>
            <button type="submit" name="btn_update" class="btn btn-primary">💾 Cập nhật sản phẩm</button>
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
    
    .content-wrapper {
      max-width: 1000px;
      margin: 0 auto;
    }
    
    .form-container {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 15px 35px rgba(255, 255, 255, 0.1);
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
    
    .current-image {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 10px;
      border: 2px dashed #dee2e6;
    }
    
    .preview-image {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .current-label {
      font-size: 12px;
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
      
      .current-image {
        padding: 10px;
      }
      
      .preview-image {
        width: 80px;
        height: 80px;
      }
    }
  </style>

  <?php require_once "./view/footer.php"; ?>
</body>
</html> 