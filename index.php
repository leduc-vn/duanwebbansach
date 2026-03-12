<?php require_once "./view/header.php"; ?>

<body>
  <!-- code header -->
  <?php require_once 'view/header1.php'?>

  <div class="main">
    <!-- code content -->
    <h1 class="page-title">Danh sách sản phẩm</h1>
    
    <!-- Form tìm kiếm -->
    <div class="search-container">
      <form method="GET" action="" class="search-form">
        <input type="text" name="search" placeholder="Nhập mã sản phẩm hoặc tên sản phẩm..." 
               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
               class="search-input" />
        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
      </form>
    </div>
    
    <div class="content">
      <?php 
        require_once "connect.php";
        
        // Xử lý tìm kiếm
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $where_clause = "";
        
        if (!empty($search)) {
            $search = mysqli_real_escape_string($conn, $search);
            $where_clause = "WHERE ma_sp LIKE '%$search%' OR tensp LIKE '%$search%'";
        }
        
        $sql = "SELECT * FROM books $where_clause ORDER BY tensp";
        $rel = mysqli_query($conn, $sql);
        
        if(mysqli_num_rows($rel) > 0) {
            echo "<div class='search-results'>";
            if (!empty($search)) {
                echo "<div class='alert alert-info'>";
                echo "<strong>Kết quả tìm kiếm:</strong> " . htmlspecialchars($search) . " (" . mysqli_num_rows($rel) . " sản phẩm)";
                echo "</div>";
            }
            
            echo "<div class='products-grid'>";
            while ($r = mysqli_fetch_assoc($rel)) {
                $anhsp = $r['anhsp'];
                $masp = $r['ma_sp'];
                $tensp = $r['tensp'];
                $giatien = $r['giaxuat'];
                $khuyenmai = $r['khuyenmai'];
                $soluong = $r['soluong'];
                ?>
      <div class="card product-card">
        <div class="product-image">
          <img src="public/images/<?php echo $anhsp;?>" alt="<?php echo $tensp;?>" />
        </div>
        <div class="card_container">
          <h4 class="product-title"><?php echo $tensp?></h4>
          <p class="product-code">Mã: <?php echo $masp?></p>
          <?php if($khuyenmai > 0): ?>
            <?php 
            $gia_sau_km = $giatien * (1 - $khuyenmai / 100);
            ?>
            <p class="product-price-old">Giá gốc: <?php echo number_format($giatien); ?> VNĐ</p>
            <p class="product-price-new">Giá khuyến mãi: <?php echo number_format($gia_sau_km); ?> VNĐ</p>
          <?php else: ?>
            <p class="product-price">Giá: <?php echo number_format($giatien); ?> VNĐ</p>
          <?php endif; ?>
          <p class="product-stock">Còn lại: <?php echo $soluong; ?> sản phẩm</p>
          <div class="product-actions">
            <a href="product_detail.php?id=<?php echo $masp; ?>" class="btn btn-primary">Xem chi tiết</a>
            <?php if($soluong > 0): ?>
              <a href="addtocart.php?id=<?php echo $masp; ?>" class="btn btn-success">Thêm vào giỏ hàng</a>
            <?php else: ?>
              <span class="out-of-stock">Hết hàng</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
                <?php
            }
            echo "</div>";
            echo "</div>";
        } else {
            if (!empty($search)) {
                echo "<div class='no-results'>";
                echo "<div class='alert alert-warning'>";
                echo "<strong>Không tìm thấy sản phẩm nào</strong> phù hợp với từ khóa: " . htmlspecialchars($search);
                echo "</div>";
                echo "<div class='text-center'>";
                echo "<a href='index.php' class='btn btn-primary'>Xem tất cả sản phẩm</a>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-info'>Không có sản phẩm nào.</div>";
            }
        }
        ?>
    </div>
    
    <!-- Tin tức mới nhất -->
    <div class="latest-news-section">
      <div class="container">
        <h2 class="section-title">📰 Tin tức mới nhất</h2>
        <?php
        // Lấy 3 tin tức mới nhất
        $sql_news = "SELECT * FROM tintuc WHERE trangthai = 1 ORDER BY ngaytao DESC LIMIT 3";
        $result_news = mysqli_query($conn, $sql_news);
        
        if(mysqli_num_rows($result_news) > 0):
        ?>
        <div class="news-grid">
          <?php while($news = mysqli_fetch_assoc($result_news)): ?>
          <div class="news-card">
            <?php if(!empty($news['hinhanh'])): ?>
            <div class="news-image">
              <img src="public/images/<?php echo htmlspecialchars($news['hinhanh']); ?>" 
                   alt="<?php echo htmlspecialchars($news['tieude']); ?>" />
            </div>
            <?php endif; ?>
            
            <div class="news-content">
              <h3 class="news-title"><?php echo htmlspecialchars($news['tieude']); ?></h3>
              <p class="news-excerpt">
                <?php 
                $excerpt = substr($news['noidung'], 0, 120);
                echo htmlspecialchars($excerpt) . (strlen($news['noidung']) > 120 ? '...' : '');
                ?>
              </p>
              <div class="news-meta">
                <span class="news-date">📅 <?php echo date('d/m/Y', strtotime($news['ngaytao'])); ?></span>
              </div>
              <a href="view_tintuc_detail.php?id=<?php echo $news['id']; ?>" class="btn btn-read-more">
                Đọc thêm
              </a>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
        <div class="text-center" style="margin-top: 30px;">
          <a href="view_tintuc.php" class="btn btn-primary">Xem tất cả tin tức</a>
        </div>
        <?php else: ?>
        <div class="text-center" style="padding: 40px 20px; color: #666;">
          <p>Chưa có tin tức nào</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <style>
    .page-title {
      text-align: center;
      color: #333;
      font-size: 2.5rem;
      margin-bottom: 30px;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .search-container {
      margin: 30px 0;
      text-align: center;
    }
    
    .search-form {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px;
      flex-wrap: wrap;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .search-input {
      flex: 1;
      min-width: 300px;
      padding: 15px 20px;
      border: 2px solid #e9ecef;
      border-radius: 25px;
      font-size: 16px;
      background: white;
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      outline: none;
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
      transform: scale(1.02);
    }
    
    
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 25px;
      margin-top: 30px;
    }
    
    .product-card {
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: all 0.3s ease;
    align-items: center;
    justify-content: space-between;
    align-content: space-between;
    flex-wrap: nowrap;
    padding: 20px;
  }
    
    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    
    .product-image {
      position: relative;
      overflow: hidden;
      border-radius: 15px;
      margin-bottom: 15px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      height: 240px;
      display: flex;
      align-items: center;
      justify-content: center;
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
    
    .product-title {
      color: #333;
      font-size: 18px;
      margin-bottom: 10px;
      font-weight: 600;
      line-height: 1.3;
    }
    
    .product-code {
      color: #666;
      font-size: 14px;
      margin-bottom: 8px;
    }
    
    .product-price {
      color: #28a745;
      font-weight: bold;
      font-size: 16px;
      margin-bottom: 8px;
    }
    
    .product-price-old {
      color: #6c757d;
      font-weight: normal;
      font-size: 14px;
      margin-bottom: 4px;
      text-decoration: line-through;
    }
    
    .product-price-new {
      color: #dc3545;
      font-weight: bold;
      font-size: 16px;
      margin-bottom: 8px;
    }
    
    .product-stock {
      color: #6c757d;
      font-size: 14px;
      margin-bottom: 15px;
    }
    
    .product-actions {
      display: flex;
      gap: 10px;
      margin-top: auto;
      flex-wrap: wrap;
    }
    
    .product-actions .btn {
      flex: 1;
      min-width: 120px;
      text-align: center;
      display: flex;
      align-items: center;   
      justify-content: center; 
    }
    
    .out-of-stock {
      display: inline-block;
      padding: 10px 20px;
      background: #dc3545;
      color: white;
      border-radius: 25px;
      font-size: 14px;
      font-weight: 600;
      text-align: center;
      flex: 1;
    }
    
    .no-results {
      text-align: center;
      padding: 50px 20px;
    }
    
    /* Latest News Section */
    .latest-news-section {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 60px 0;
      margin-top: 60px;
    }
    
    .section-title {
      text-align: center;
      font-size: 2.2rem;
      font-weight: 700;
      color: #333;
      margin-bottom: 40px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .news-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
      margin-bottom: 30px;
    }
    
    .news-card {
    display: flex;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e1e5e9;
    flex-direction: column;
    justify-content: flex-end;
    flex-wrap: nowrap;
    align-items: stretch;
}
    
    .news-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .news-image {
      height: 200px;
      overflow: hidden;
    }
    
    .news-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    
    .news-card:hover .news-image img {
      transform: scale(1.08);
    }
    
    .news-content {
      padding: 20px;
    }
    
    .news-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin: 0 0 12px 0;
      color: #333;
      line-height: 1.4;
    }
    
    .news-excerpt {
      color: #666;
      line-height: 1.6;
      margin-bottom: 15px;
      font-size: 0.9rem;
    }
    
    .news-meta {
      font-size: 0.85rem;
      color: #888;
      margin-bottom: 15px;
    }
    
    .btn-read-more {
      background: linear-gradient(45deg, #007bff, #0056b3);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-block;
    }
    
    .btn-read-more:hover {
      background: linear-gradient(45deg, #0056b3, #004085);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,123,255,0.3);
      color: white;
      text-decoration: none;
    }
    
    @media (max-width: 768px) {
      .page-title {
        font-size: 2rem;
      }
      
      .search-form {
        flex-direction: column;
        gap: 10px;
      }
      
      .search-input {
        min-width: auto;
        width: 100%;
      }
      
      .products-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .product-image {
        height: 220px;
      }
      
      .product-image img {
        transition: all 0.3s ease;
      }
      
      .product-card:hover .product-image img {
        transform: scale(1.03);
      }
      
      .product-actions {
        flex-direction: column;
      }
      
      .latest-news-section {
        padding: 40px 0;
        margin-top: 40px;
      }
      
      .section-title {
        font-size: 1.8rem;
      }
      
      .news-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
    }
  </style>
  
  <?php require_once "./view/footer.php";?>
</body>

</html>
