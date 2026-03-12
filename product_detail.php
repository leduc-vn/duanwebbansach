<?php require_once "./view/header.php"; ?>

<body>
  <!-- code header -->
  <?php require_once 'view/header1.php'?>

  <div class="main">
    <!-- code content -->
    <?php 
      require_once "connect.php";
      
      $id = isset($_GET["id"]) ? $_GET["id"] : "";
      
      if (empty($id)) {
          echo "<div class='error-message'>Không tìm thấy sản phẩm!</div>";
      } else {
          $sql = "SELECT * FROM adproduct WHERE ma_sp = '$id'";
          $rel = mysqli_query($conn, $sql);
          
          if(mysqli_num_rows($rel) > 0) {
              $r = mysqli_fetch_assoc($rel);
              ?>
    <div class="product-detail-container">
      <div class="breadcrumb">
        <a href="index.php">Trang chủ</a> > 
        <span>Chi tiết sản phẩm</span>
      </div>
      
      <div class="product-detail">
        <div class="product-image">
          <img src="public/images/<?php echo $r['anhsp']; ?>" alt="<?php echo $r['tensp']; ?>" />
        </div>
        
        <div class="product-info">
          <h1 class="product-title"><?php echo $r['tensp']; ?></h1>
          
          <div class="product-code">
            <strong>Mã sản phẩm:</strong> <?php echo $r['ma_sp']; ?>
          </div>
          
          <div class="product-price-section">
            <?php if($r['khuyenmai'] > 0): ?>
              <div class="original-price">
                <span>Giá gốc:</span> 
                <span class="price-old"><?php echo number_format($r['giaxuat']); ?> VNĐ</span>
              </div>
              <div class="discount-price">
                <span>Giá khuyến mại:</span> 
                <span class="price-new"><?php echo number_format($r['giaxuat'] * (1 - $r['khuyenmai'] / 100)); ?> VNĐ</span>
              </div>
              <div class="discount-badge">
                Giảm <?php echo round((($r['giaxuat'] - $r['khuyenmai']) / $r['giaxuat']) * 100); ?>%
              </div>
            <?php else: ?>
              <div class="current-price">
                <span>Giá:</span> 
                <span class="price-current"><?php echo number_format($r['giaxuat']); ?> VNĐ</span>
              </div>
            <?php endif; ?>
          </div>
          
          <div class="product-stock">
            <strong>Tình trạng:</strong>
            <?php if($r['soluong'] > 0): ?>
              <span class="in-stock">Còn hàng (<?php echo $r['soluong']; ?> sản phẩm)</span>
            <?php else: ?>
              <span class="out-of-stock">Hết hàng</span>
            <?php endif; ?>
          </div>
          
          <div class="product-description">
            <h3>Mô tả sản phẩm</h3>
            <p><?php echo nl2br(htmlspecialchars($r['motasp'])); ?></p>
          </div>
          
          <div class="product-actions">
            <?php if($r['soluong'] > 0): ?>
              <a href="addtocart.php?id=<?php echo $r['ma_sp']; ?>" class="add-to-cart-btn-large">
                <i class="cart-icon">🛒</i> Thêm vào giỏ hàng
              </a>
            <?php else: ?>
              <span class="out-of-stock-large">Hết hàng</span>
            <?php endif; ?>
            
            <a href="index.php" class="back-to-shop-btn">
              <i class="back-icon">←</i> Quay lại mua sắm
            </a>
          </div>
        </div>
      </div>
      
      <div class="product-details-table">
        <h3>Thông tin chi tiết</h3>
        <table>
          <tr>
            <td><strong>Mã sản phẩm:</strong></td>
            <td><?php echo $r['ma_sp']; ?></td>
          </tr>
          <tr>
            <td><strong>Tên sản phẩm:</strong></td>
            <td><?php echo $r['tensp']; ?></td>
          </tr>
          <tr>
            <td><strong>Loại sản phẩm:</strong></td>
            <td><?php echo $r['Ma_loaisp']; ?></td>
          </tr>
          <tr>
            <td><strong>Giá nhập:</strong></td>
            <td><?php echo number_format($r['gianhap']); ?> VNĐ</td>
          </tr>
          <tr>
            <td><strong>Giá bán:</strong></td>
            <td><?php echo number_format($r['giaxuat']); ?> VNĐ</td>
          </tr>
          <?php if($r['khuyenmai'] > 0): ?>
          <tr>
            <td><strong>Giá khuyến mại:</strong></td>
            <td class="discount-text"><?php echo number_format($r['giaxuat'] * (1 - $r['khuyenmai'] / 100)); ?> VNĐ</td>
          </tr>
          <?php endif; ?>
          <tr>
            <td><strong>Số lượng:</strong></td>
            <td><?php echo $r['soluong']; ?> sản phẩm</td>
          </tr>
          <tr>
            <td><strong>Ngày nhập:</strong></td>
            <td><?php echo date('d/m/Y', strtotime($r['ngay_nhap'])); ?></td>
          </tr>
        </table>
      </div>
    </div>
              <?php
          } else {
              echo "<div class='error-message'>Không tìm thấy sản phẩm!</div>";
          }
      }
    ?>
  </div>
  
  <style>
    .product-detail-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      font-family: Arial, sans-serif;
    }
    
    .breadcrumb {
      margin-bottom: 20px;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }
    
    .breadcrumb a {
      color: #007bff;
      text-decoration: none;
    }
    
    .breadcrumb a:hover {
      text-decoration: underline;
    }
    
    .product-detail {
      display: flex;
      gap: 40px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }
    
    .product-image {
      flex: 1;
      min-width: 300px;
      text-align: center;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: 25px;
      padding: 30px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
      position: relative;
      overflow: hidden;
    }
    
    .product-image::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
      opacity: 0;
      transition: opacity 0.4s ease;
      z-index: 1;
    }
    
    .product-image:hover::before {
      opacity: 1;
    }
    
    .product-image img {
      max-width: 100%;
      height: auto;
      border-radius: 20px;
      box-shadow: 0 12px 35px rgba(0,0,0,0.2);
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      filter: brightness(0.9) contrast(1.1);
      transform-origin: center;
      position: relative;
      z-index: 2;
    }
    
    .product-image:hover img {
      transform: scale(1.05) rotate(1deg);
      filter: brightness(1.1) contrast(1.2) saturate(1.1);
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    
    .product-info {
      flex: 1;
      min-width: 300px;
    }
    
    .product-title {
      font-size: 28px;
      color: #333;
      margin-bottom: 15px;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
    }
    
    .product-code {
      color: #666;
      margin-bottom: 20px;
      font-size: 16px;
    }
    
    .product-price-section {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    
    .original-price {
      margin-bottom: 10px;
    }
    
    .price-old {
      text-decoration: line-through;
      color: #999;
      font-size: 18px;
    }
    
    .discount-price {
      margin-bottom: 10px;
    }
    
    .price-new {
      color: #dc3545;
      font-size: 24px;
      font-weight: bold;
    }
    
    .price-current {
      color: #28a745;
      font-size: 24px;
      font-weight: bold;
    }
    
    .discount-badge {
      background: #dc3545;
      color: white;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 14px;
      display: inline-block;
    }
    
    .product-stock {
      margin-bottom: 20px;
      font-size: 16px;
    }
    
    .in-stock {
      color: #28a745;
      font-weight: bold;
    }
    
    .out-of-stock {
      color: #dc3545;
      font-weight: bold;
    }
    
    .product-description {
      margin-bottom: 30px;
    }
    
    .product-description h3 {
      color: #333;
      margin-bottom: 10px;
      border-bottom: 1px solid #eee;
      padding-bottom: 5px;
    }
    
    .product-description p {
      line-height: 1.6;
      color: #666;
    }
    
    .product-actions {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    
    .add-to-cart-btn-large {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 15px 30px;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      transition: all 0.3s;
    }
    
    .add-to-cart-btn-large:hover {
      background-color: #218838;
      color: white;
      text-decoration: none;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(40,167,69,0.3);
    }
    
    .out-of-stock-large {
      display: inline-flex;
      align-items: center;
      padding: 15px 30px;
      background-color: #dc3545;
      color: white;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
    }
    
    .back-to-shop-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 15px 30px;
      background-color: #6c757d;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s;
    }
    
    .back-to-shop-btn:hover {
      background-color: #545b62;
      color: white;
      text-decoration: none;
      transform: translateY(-2px);
    }
    
    .product-details-table {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .product-details-table h3 {
      color: #333;
      margin-bottom: 20px;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
    }
    
    .product-details-table table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .product-details-table td {
      padding: 12px;
      border-bottom: 1px solid #eee;
    }
    
    .product-details-table td:first-child {
      font-weight: bold;
      width: 200px;
      color: #333;
    }
    
    .discount-text {
      color: #dc3545;
      font-weight: bold;
    }
    
    .error-message {
      text-align: center;
      padding: 50px 20px;
      background: #f8d7da;
      color: #721c24;
      border-radius: 10px;
      margin: 20px;
      font-size: 18px;
    }
    
    @media (max-width: 768px) {
      .product-detail {
        flex-direction: column;
      }
      
      .product-actions {
        flex-direction: column;
      }
      
      .product-title {
        font-size: 24px;
      }
    }
  </style>
  
  <?php require_once "./view/footer.php";?>
</body>

</html> 