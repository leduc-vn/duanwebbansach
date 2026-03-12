<?php require_once "./view/header.php"; ?>

<body>
  <!-- code header -->
  <?php require_once 'view/header1.php'?>

  <div class="main">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">📚 Sản phẩm</h1>
      <p class="page-subtitle">Khám phá bộ sưu tập sách đa dạng của chúng tôi</p>
    </div>

    <!-- Filter and Search Section -->
    <div class="filter-section">
      <div class="filter-container">
        <!-- Category Filter -->
        <div class="filter-group">
          <label for="category-filter" class="filter-label">📂 Loại sản phẩm:</label>
          <select id="category-filter" class="filter-select" onchange="filterProducts()">
            <option value="">Tất cả loại</option>
            <?php
            require_once "connect.php";
            $sql_categories = "SELECT * FROM adproducttype ORDER BY Ten_loaisp";
            $result_categories = mysqli_query($conn, $sql_categories);
            
            if(mysqli_num_rows($result_categories) > 0) {
                while($category = mysqli_fetch_assoc($result_categories)) {
                    $selected = (isset($_GET['category']) && $_GET['category'] == $category['Ma_loaisp']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($category['Ma_loaisp']) . '" ' . $selected . '>' . htmlspecialchars($category['Ten_loaisp']) . '</option>';
                }
            }
            ?>
          </select>
        </div>

        <!-- Price Filter -->
        <div class="filter-group">
          <label for="price-filter" class="filter-label">💰 Giá:</label>
          <select id="price-filter" class="filter-select" onchange="filterProducts()">
            <option value="">Tất cả giá</option>
            <option value="0-50000" <?php echo (isset($_GET['price']) && $_GET['price'] == '0-50000') ? 'selected' : ''; ?>>Dưới 50,000 VNĐ</option>
            <option value="50000-100000" <?php echo (isset($_GET['price']) && $_GET['price'] == '50000-100000') ? 'selected' : ''; ?>>50,000 - 100,000 VNĐ</option>
            <option value="100000-200000" <?php echo (isset($_GET['price']) && $_GET['price'] == '100000-200000') ? 'selected' : ''; ?>>100,000 - 200,000 VNĐ</option>
            <option value="200000-500000" <?php echo (isset($_GET['price']) && $_GET['price'] == '200000-500000') ? 'selected' : ''; ?>>200,000 - 500,000 VNĐ</option>
            <option value="500000-999999999" <?php echo (isset($_GET['price']) && $_GET['price'] == '500000-999999999') ? 'selected' : ''; ?>>Trên 500,000 VNĐ</option>
          </select>
        </div>

        <!-- Sort Filter -->
        <div class="filter-group">
          <label for="sort-filter" class="filter-label">🔄 Sắp xếp:</label>
          <select id="sort-filter" class="filter-select" onchange="filterProducts()">
            <option value="name-asc" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'name-asc') ? 'selected' : ''; ?>>Tên A-Z</option>
            <option value="name-desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name-desc') ? 'selected' : ''; ?>>Tên Z-A</option>
            <option value="price-asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price-asc') ? 'selected' : ''; ?>>Giá tăng dần</option>
            <option value="price-desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price-desc') ? 'selected' : ''; ?>>Giá giảm dần</option>
            <option value="newest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Mới nhất</option>
          </select>
        </div>
      </div>

      <!-- Search Form -->
      <div class="search-container">
        <form method="GET" action="" class="search-form" id="search-form">
          <input type="text" name="search" id="search-input" placeholder="Tìm kiếm sản phẩm..." 
                 value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                 class="search-input" />
          <button type="submit" class="btn btn-primary search-btn">
            <span class="search-icon">🔍</span>
            Tìm kiếm
          </button>
        </form>
      </div>
    </div>

    <!-- Products Display -->
    <div class="content">
      <?php 
        // Xử lý tìm kiếm và lọc
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $price_filter = isset($_GET['price']) ? trim($_GET['price']) : '';
        $sort_filter = isset($_GET['sort']) ? trim($_GET['sort']) : '';
        
        $where_conditions = array();
        $params = array();
        
        if (!empty($search)) {
            $where_conditions[] = "(ma_sp LIKE ? OR tensp LIKE ? OR motasp LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        if (!empty($category)) {
            $where_conditions[] = "Ma_loaisp = ?";
            $params[] = $category;
        }
        
        // Xử lý filter giá
        if (!empty($price_filter)) {
            $price_range = explode('-', $price_filter);
            if (count($price_range) == 2) {
                $min_price = $price_range[0];
                $max_price = $price_range[1];
                $where_conditions[] = "(CASE WHEN khuyenmai > 0 THEN (giaxuat * (1 - khuyenmai / 100)) ELSE giaxuat END) BETWEEN ? AND ?";
                $params[] = $min_price;
                $params[] = $max_price;
            }
        }
        
        $where_clause = "";
        if (!empty($where_conditions)) {
            $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        }
        
        // Xử lý sắp xếp
        $order_clause = "ORDER BY tensp";
        if (!empty($sort_filter)) {
            switch($sort_filter) {
                case 'name-asc':
                    $order_clause = "ORDER BY tensp ASC";
                    break;
                case 'name-desc':
                    $order_clause = "ORDER BY tensp DESC";
                    break;
                case 'price-asc':
                    $order_clause = "ORDER BY (CASE WHEN khuyenmai > 0 THEN (giaxuat * (1 - khuyenmai / 100)) ELSE giaxuat END) ASC";
                    break;
                case 'price-desc':
                    $order_clause = "ORDER BY (CASE WHEN khuyenmai > 0 THEN (giaxuat * (1 - khuyenmai / 100)) ELSE giaxuat END) DESC";
                    break;
                case 'newest':
                    $order_clause = "ORDER BY ngay_nhap DESC";
                    break;
            }
        }
        
        $sql = "SELECT * FROM adproduct $where_clause $order_clause";
        
        if (!empty($params)) {
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                $types = str_repeat('s', count($params));
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $rel = mysqli_stmt_get_result($stmt);
            } else {
                $rel = mysqli_query($conn, $sql);
            }
        } else {
            $rel = mysqli_query($conn, $sql);
        }
        
        if(mysqli_num_rows($rel) > 0) {
            echo "<div class='search-results'>";
            if (!empty($search) || !empty($category) || !empty($price_filter)) {
                echo "<div class='alert alert-info'>";
                echo "<strong>Kết quả tìm kiếm:</strong> ";
                if (!empty($search)) {
                    echo "Từ khóa: " . htmlspecialchars($search) . " ";
                }
                if (!empty($category)) {
                    $cat_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Ten_loaisp FROM adproducttype WHERE Ma_loaisp = '$category'"))['Ten_loaisp'];
                    echo "Loại: " . htmlspecialchars($cat_name) . " ";
                }
                if (!empty($price_filter)) {
                    $price_range = explode('-', $price_filter);
                    if ($price_range[1] == '999999999') {
                        echo "Giá: Trên " . number_format($price_range[0]) . " VNĐ ";
                    } else {
                        echo "Giá: " . number_format($price_range[0]) . " - " . number_format($price_range[1]) . " VNĐ ";
                    }
                }
                echo "(" . mysqli_num_rows($rel) . " sản phẩm)";
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
                $motasp = $r['motasp'];
                $maloai = $r['Ma_loaisp'];
                
                // Lấy tên loại sản phẩm
                $sql_loai = "SELECT Ten_loaisp FROM adproducttype WHERE Ma_loaisp = '$maloai'";
                $result_loai = mysqli_query($conn, $sql_loai);
                $tenloai = mysqli_fetch_assoc($result_loai)['Ten_loaisp'];
                
                // Tính giá sau khuyến mãi
                $gia_sau_km = $khuyenmai > 0 ? ($giatien * (1 - $khuyenmai / 100)) : $giatien;
                ?>
      <div class="card product-card" data-category="<?php echo htmlspecialchars($maloai); ?>" 
           data-price="<?php echo $gia_sau_km; ?>" data-name="<?php echo htmlspecialchars(strtolower($tensp)); ?>">
        <div class="product-image">
          <img src="public/images/<?php echo $anhsp;?>" alt="<?php echo $tensp;?>" />
                      <?php if($khuyenmai > 0): ?>
            <div class="discount-badge">
              <span class="discount-text">-<?php echo $khuyenmai; ?>%</span>
            </div>
          <?php endif; ?>
        </div>
        <div class="card_container">
          <div class="product-category"><?php echo htmlspecialchars($tenloai); ?></div>
          <h4 class="product-title"><?php echo $tensp?></h4>
          <p class="product-code">Mã: <?php echo $masp?></p>
          <p class="product-description"><?php echo substr($motasp, 0, 100) . (strlen($motasp) > 100 ? '...' : ''); ?></p>
          
          <div class="product-pricing">
            <?php if($khuyenmai > 0): ?>
              <?php $gia_sau_km = $giatien * (1 - $khuyenmai / 100); ?>
              <p class="product-price-old"><?php echo number_format($giatien); ?> VNĐ</p>
              <p class="product-price-new"><?php echo number_format($gia_sau_km); ?> VNĐ</p>
            <?php else: ?>
              <p class="product-price"><?php echo number_format($giatien); ?> VNĐ</p>
            <?php endif; ?>
          </div>
          
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
            echo "<div class='no-results'>";
            echo "<div class='alert alert-warning'>";
            echo "<strong>Không tìm thấy sản phẩm nào</strong>";
            if (!empty($search) || !empty($category) || !empty($price_filter)) {
                echo " phù hợp với tiêu chí tìm kiếm.";
            }
            echo "</div>";
            echo "<div class='text-center'>";
            echo "<a href='products.php' class='btn btn-primary'>Xem tất cả sản phẩm</a>";
            echo "</div>";
            echo "</div>";
        }
        ?>
    </div>
  </div>

  <style>
    .page-header {
      text-align: center;
      margin-bottom: 40px;
      padding: 40px 20px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .page-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0 0 15px 0;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .page-subtitle {
      font-size: 1.1rem;
      opacity: 0.9;
      margin: 0;
    }
    
    .filter-section {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      margin-bottom: 40px;
    }
    
    .filter-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    
    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-width: 200px;
    }
    
    .filter-label {
      font-weight: 600;
      color: #333;
      font-size: 14px;
    }
    
    .filter-select {
      padding: 12px 15px;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      font-size: 14px;
      background: white;
      transition: all 0.3s ease;
    }
    
    .filter-select:focus {
      outline: none;
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .search-container {
      border-top: 1px solid #e9ecef;
      padding-top: 20px;
    }
    
    .search-form {
      display: flex;
      gap: 15px;
      align-items: center;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .search-input {
      flex: 1;
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
    }
    
    .search-btn {
      padding: 15px 25px;
      border-radius: 25px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .search-icon {
      font-size: 16px;
    }
    
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 30px;
      margin-top: 30px;
    }
    
    .product-card {
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      border: 1px solid #e1e5e9;
      position: relative;
    }
    
    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .product-image {
      position: relative;
      height: 300px;
      overflow: hidden;
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
      opacity: 0;
      animation: fadeIn 0.8s ease-in-out forwards;
      transform-origin: center;
    }
    
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: scale(0.9) rotate(-1deg);
      }
      to {
        opacity: 1;
        transform: scale(1) rotate(0deg);
      }
    }
    
    @keyframes shimmer {
      0% {
        background-position: -200px 0;
      }
      100% {
        background-position: calc(200px + 100%) 0;
      }
    }
    
    .product-image::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200px 100%;
      animation: shimmer 1.5s infinite;
      z-index: 0;
      opacity: 1;
      transition: opacity 0.3s ease;
    }
    
    .product-image img {
      z-index: 1;
    }
    
    .product-image img[src] {
      opacity: 1;
    }
    
    .product-image img[src] + .product-image::before {
      opacity: 0;
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
    
    .discount-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
      padding: 12px 18px;
      border-radius: 30px;
      font-weight: 700;
      font-size: 14px;
      box-shadow: 0 8px 25px rgba(231,76,60,0.5);
      z-index: 3;
      backdrop-filter: blur(15px);
      border: 2px solid rgba(255,255,255,0.3);
      transform: scale(0.9);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .product-card:hover .discount-badge {
      transform: scale(1.05);
      box-shadow: 0 12px 35px rgba(231,76,60,0.6);
    }
    
    .card_container {
      padding: 25px;
    }
    
    .product-category {
      background: linear-gradient(45deg, #3498db, #2980b9);
      color: white;
      padding: 6px 12px;
      border-radius: 15px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 15px;
    }
    
    .product-title {
      font-size: 1.3rem;
      font-weight: 600;
      margin: 0 0 10px 0;
      color: #333;
      line-height: 1.4;
    }
    
    .product-code {
      color: #666;
      font-size: 14px;
      margin: 0 0 15px 0;
    }
    
    .product-description {
      color: #666;
      line-height: 1.6;
      margin-bottom: 20px;
      font-size: 14px;
    }
    
    .product-pricing {
      margin-bottom: 15px;
    }
    
    .product-price {
      font-size: 1.4rem;
      font-weight: 700;
      color: #28a745;
      margin: 0;
    }
    
    .product-price-old {
      font-size: 1rem;
      color: #999;
      text-decoration: line-through;
      margin: 0 0 5px 0;
    }
    
    .product-price-new {
      font-size: 1.4rem;
      font-weight: 700;
      color: #e74c3c;
      margin: 0;
    }
    
    .product-stock {
      color: #666;
      font-size: 14px;
      margin-bottom: 20px;
    }
    
    .product-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
    }
    
    .btn {
      padding: 12px 20px;
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
    
    .btn-success {
      background: linear-gradient(45deg, #28a745, #218838);
      color: white;
    }
    
    .btn-success:hover {
      background: linear-gradient(45deg, #218838, #1e7e34);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(40,167,69,0.3);
      color: white;
      text-decoration: none;
    }
    
    .out-of-stock {
      background: #dc3545;
      color: white;
      padding: 12px 20px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 14px;
    }
    
    .search-results {
      margin-bottom: 30px;
    }
    
    .alert {
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    
    .alert-info {
      background: linear-gradient(45deg, #d1ecf1, #bee5eb);
      color: #0c5460;
      border: 1px solid #bee5eb;
    }
    
    .alert-warning {
      background: linear-gradient(45deg, #fff3cd, #ffeaa7);
      color: #856404;
      border: 1px solid #ffeaa7;
    }
    
    .no-results {
      text-align: center;
      padding: 60px 20px;
    }
    
    .text-center {
      text-align: center;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .page-header {
        padding: 30px 15px;
        margin-bottom: 30px;
      }
      
      .page-title {
        font-size: 2rem;
      }
      
      .filter-container {
        flex-direction: column;
        gap: 15px;
      }
      
      .filter-group {
        min-width: auto;
      }
      
      .search-form {
        flex-direction: column;
        gap: 15px;
      }
      
      .products-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .product-image {
        height: 240px;
      }
      
      .product-image img {
        transition: all 0.3s ease;
      }
      
      .product-card:hover .product-image img {
        transform: scale(1.03);
      }
      
      .card_container {
        padding: 20px;
      }
      
      .product-actions {
        flex-direction: column;
      }
      
      .btn {
        justify-content: center;
        width: 100%;
      }
      
      .discount-badge {
        padding: 8px 12px;
        font-size: 12px;
      }
    }
    
    @media (max-width: 480px) {
      .product-image {
        height: 200px;
      }
      
      .product-image img {
        transition: all 0.2s ease;
      }
      
      .product-card:hover .product-image img {
        transform: scale(1.02);
      }
      
      .product-title {
        font-size: 1.1rem;
      }
      
      .product-price-new,
      .product-price {
        font-size: 1.2rem;
      }
      
      .discount-badge {
        padding: 6px 10px;
        font-size: 11px;
        top: 10px;
        right: 10px;
      }
    }
  </style>

  <script>
    function filterProducts() {
      const categoryFilter = document.getElementById('category-filter').value;
      const priceFilter = document.getElementById('price-filter').value;
      const sortFilter = document.getElementById('sort-filter').value;
      
      let url = 'products.php?';
      const params = [];
      
      if (categoryFilter) {
        params.push('category=' + encodeURIComponent(categoryFilter));
      }
      
      if (priceFilter) {
        params.push('price=' + encodeURIComponent(priceFilter));
      }
      
      if (sortFilter) {
        params.push('sort=' + encodeURIComponent(sortFilter));
      }
      
      // Giữ lại search parameter nếu có
      const searchInput = document.getElementById('search-input');
      if (searchInput.value.trim()) {
        params.push('search=' + encodeURIComponent(searchInput.value.trim()));
      }
      
      if (params.length > 0) {
        url += params.join('&');
      }
      
      window.location.href = url;
    }
    
    // Auto-submit search form on Enter key
    document.getElementById('search-input').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('search-form').submit();
      }
    });
  </script>

  <?php require_once "./view/footer.php"; ?>
</body>
</html> 