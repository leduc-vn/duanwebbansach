<?php require_once "./view/header.php"; ?>

<body>
  <?php 
    require_once 'view/header1.php';
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
      <h1>Quản lý danh sách sản phẩm</h1>
      <p>Thêm, sửa, xóa các sản phẩm trong hệ thống</p>
    </div>
    
    <div class="content-wrapper">
      <div class="products-grid">
        <?php 
        $sql = "SELECT * FROM adproduct ORDER BY tensp";
        $rel = mysqli_query($conn, $sql);
        
        if(mysqli_num_rows($rel) > 0) {
            while ($r = mysqli_fetch_assoc($rel)) {
                $anhsp = $r['anhsp'];
                $masp = $r['ma_sp'];
                $tensp = $r['tensp'];
                $giatien = $r['giaxuat'];
                $khuyenmai = $r['khuyenmai'];
                $soluong = $r['soluong'];
                $gianhap = $r['gianhap'];
                ?>
                <div class="product-card">
                  <div class="product-image">
                    <img src="public/images/<?php echo htmlspecialchars($anhsp); ?>" 
                         alt="<?php echo htmlspecialchars($tensp); ?>" />
                  </div>
                  
                  <div class="product-info">
                    <div class="product-header">
                      <h3 class="product-name"><?php echo htmlspecialchars($tensp); ?></h3>
                      <span class="product-code">#<?php echo htmlspecialchars($masp); ?></span>
                    </div>
                    
                    <div class="product-details">
                      <div class="price-info">
                        <div class="price-row">
                          <span class="price-label">Giá nhập:</span>
                          <span class="price-value input-price"><?php echo number_format($gianhap); ?> VNĐ</span>
                        </div>
                        <div class="price-row">
                          <span class="price-label">Giá bán:</span>
                          <span class="price-value output-price"><?php echo number_format($giatien); ?> VNĐ</span>
                        </div>
                        <?php if($khuyenmai > 0): ?>
                        <div class="price-row">
                          <span class="price-label">Giá khuyến mại:</span>
                          <span class="price-value discount-price"><?php echo number_format($giatien * (1 - $khuyenmai / 100)); ?> VNĐ</span>
                        </div>
                        <?php endif; ?>
                      </div>
                      
                      <div class="stock-info">
                        <span class="stock-label">Tồn kho:</span>
                        <span class="stock-value <?php echo $soluong > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                          <?php echo $soluong; ?> sản phẩm
                        </span>
                      </div>
                      
                      <div class="product-actions-bottom">
                        <a href="update_sp.php?id=<?php echo urlencode($masp); ?>" 
                           class="btn btn-primary btn-sm" title="Sửa sản phẩm">
                          ✏️ Sửa
                        </a>
                        <a href="delete_sp.php?id=<?php echo urlencode($masp); ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')"
                           title="Xóa sản phẩm">
                          🗑️ Xóa
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="empty-state">
              <div class="empty-icon">📦</div>
              <h3>Chưa có sản phẩm nào</h3>
              <p>Hãy thêm sản phẩm đầu tiên để bắt đầu</p>
            </div>
            <?php
        }
        ?>
      </div>
      
      <div class="add-section">
        <a href="adproduct.php" class="btn btn-success btn-large">
          <span class="btn-icon">➕</span>
          Thêm sản phẩm mới
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
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .product-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    
    .product-image {
      position: relative;
      overflow: hidden;
      height: 240px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 15px 15px 0 0;
    }
    
    .product-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      filter: brightness(0.9) contrast(1.1);
      transform-origin: center;
    }
    
    .product-card:hover .product-image img {
      transform: scale(1.08) rotate(0.5deg);
      filter: brightness(1.1) contrast(1.2) saturate(1.1);
    }
    
    .product-image::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(0,0,0,0.15), transparent 40%, transparent 60%, rgba(0,0,0,0.1));
      opacity: 0;
      transition: opacity 0.4s ease;
      z-index: 1;
    }
    
    .product-card:hover .product-image::before {
      opacity: 1;
    }
    
    .product-image::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
      opacity: 0;
      transition: opacity 0.4s ease;
      z-index: 2;
    }
    
    .product-card:hover .product-image::after {
      opacity: 1;
    }
    

    
    .product-info {
      padding: 20px;
    }
    
    .product-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 15px;
    }
    
    .product-name {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
      color: #333;
      line-height: 1.3;
      flex: 1;
    }
    
    .product-code {
      background: linear-gradient(45deg, #007bff, #0056b3);
      color: white;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      margin-left: 10px;
      flex-shrink: 0;
    }
    
    .product-details {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    
    .price-info {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    
    .price-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .price-label {
      font-size: 14px;
      color: #666;
      font-weight: 500;
    }
    
    .price-value {
      font-weight: 600;
      font-size: 14px;
    }
    
    .input-price {
      color: #6c757d;
    }
    
    .output-price {
      color: #28a745;
    }
    
    .discount-price {
      color: #dc3545;
    }
    
    .stock-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 15px;
      background: #f8f9fa;
      border-radius: 10px;
    }
    
    .stock-label {
      font-size: 14px;
      color: #666;
      font-weight: 500;
    }
    
    .stock-value {
      font-weight: 600;
      font-size: 14px;
    }
    
    .in-stock {
      color: #28a745;
    }
    
    .out-of-stock {
      color: #dc3545;
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
      color: white;
      cursor: pointer;
      border: none;
      outline: none;
      position: relative;
      z-index: 15;
    }
    
    .btn-sm:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      text-decoration: none;
      color: white;
    }
    
    .btn-primary {
      background: linear-gradient(45deg, #007bff, #0056b3);
    }
    
    .btn-danger {
      background: linear-gradient(45deg, #dc3545, #c82333);
    }
    
    .product-actions-bottom {
      display: flex;
      gap: 10px;
      margin-top: 15px;
      justify-content: center;
    }
    
    .product-actions-bottom .btn-sm {
      flex: 1;
      justify-content: center;
      min-width: 80px;
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
      color: white;
    }
    
    .btn-large:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(40,167,69,0.4);
      text-decoration: none;
      color: white;
    }
    
    .btn-icon {
      font-size: 18px;
    }
    
    .empty-state {
      grid-column: 1 / -1;
      text-align: center;
      padding: 80px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
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
      
      .products-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .product-card {
        border-radius: 10px;
      }
      
      .product-image {
        height: 200px;
      }
      
      .product-image img {
        transition: all 0.3s ease;
      }
      
      .product-card:hover .product-image img {
        transform: scale(1.03);
      }
      
      .product-info {
        padding: 15px;
      }
      
      .product-name {
        font-size: 16px;
      }
      
      .product-actions-bottom {
        flex-direction: column;
        gap: 8px;
      }
      
      .btn-sm {
        justify-content: center;
        width: 100%;
      }
    }
    
    @media (max-width: 480px) {
      .page-header h1 {
        font-size: 1.8rem;
      }
      
      .product-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }
      
      .product-code {
        margin-left: 0;
        align-self: flex-start;
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
