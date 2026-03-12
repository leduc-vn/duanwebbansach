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
  ?>
  
  <div class="main">
    <div class="page-header">
      <h1>Quản lý dòng sản phẩm</h1>
      <p>Thêm, sửa, xóa các loại sản phẩm trong hệ thống</p>
    </div>
    
    <div class="content-wrapper">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>Mã loại</th>
              <th>Tên loại sản phẩm</th>
              <th>Mô tả</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "SELECT * FROM adproducttype ORDER BY Ten_loaisp";
            $result = mysqli_query($conn, $sql);
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr>
                      <td>
                        <span class="product-code"><?php echo htmlspecialchars($row["Ma_loaisp"]); ?></span>
                      </td>
                      <td>
                        <div class="product-info">
                          <h4 class="product-name"><?php echo htmlspecialchars($row["Ten_loaisp"]); ?></h4>
                        </div>
                      </td>
                      <td>
                        <p class="product-description"><?php echo htmlspecialchars($row["Mota_loaisp"]); ?></p>
                      </td>
                      <td>
                        <div class="action-buttons">
                          <a href="./update_loaisp.php?id=<?php echo urlencode($row["Ma_loaisp"]); ?>" 
                             class="btn btn-primary btn-sm" title="Sửa loại sản phẩm">
                            ✏️ Sửa
                          </a>
                          <a href="delete_loaisp.php?id=<?php echo urlencode($row["Ma_loaisp"]); ?>" 
                             class="btn btn-danger btn-sm"
                             onclick="return confirm('Bạn có chắc muốn xóa loại sản phẩm này?')"
                             title="Xóa loại sản phẩm">
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
                  <td colspan="4" class="text-center">
                    <div class="empty-state">
                      <div class="empty-icon">📦</div>
                      <h3>Chưa có loại sản phẩm nào</h3>
                      <p>Hãy thêm loại sản phẩm đầu tiên để bắt đầu</p>
                    </div>
                  </td>
                </tr>
                <?php
            }
            ?>
          </tbody>
        </table>
      </div>
      
      <div class="add-section">
        <a href="./adproducte.php" class="btn btn-success btn-large">
          <span class="btn-icon">➕</span>
          Thêm loại sản phẩm mới
        </a>
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
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
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
    
    .product-code {
      background: linear-gradient(45deg, #007bff, #0056b3);
      color: white;
      padding: 8px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 12px;
      display: inline-block;
      box-shadow: 0 2px 8px rgba(0,123,255,0.3);
    }
    
    .product-name {
      margin: 0 0 5px 0;
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }
    
    .product-description {
      margin: 0;
      color: #666;
      font-size: 14px;
      line-height: 1.5;
    }
    
    .action-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    
    .btn-sm {
      padding: 8px 16px;
      font-size: 12px;
      border-radius: 20px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.3s ease;
      font-weight: 500;
    }
    
    .btn-sm:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      text-decoration: none;
    }
    
    .add-section {
      text-align: center;
      padding: 30px;
    }
    
    .btn-large {
      padding: 15px 30px;
      font-size: 16px;
      border-radius: 25px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: all 0.3s ease;
      font-weight: 600;
      box-shadow: 0 5px 20px rgba(40,167,69,0.3);
    }
    
    .btn-large:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(40,167,69,0.4);
      text-decoration: none;
    }
    
    .btn-icon {
      font-size: 18px;
    }
    
    .empty-state {
      padding: 60px 20px;
      text-align: center;
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
      
      .product-code {
        font-size: 11px;
        padding: 6px 10px;
      }
    }
    
    @media (max-width: 480px) {
      .table th,
      .table td {
        padding: 12px 8px;
        font-size: 13px;
      }
      
      .page-header h1 {
        font-size: 1.8rem;
      }
      
      .btn-large {
        padding: 12px 24px;
        font-size: 14px;
      }
    }
  </style>
  
  <?php require_once "./view/footer.php"; ?>
</body>
</html>