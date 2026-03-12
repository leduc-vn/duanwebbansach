<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
     ob_start();
}
?><header class="main-header">
  <div class="header-container">
    <!-- Logo Section -->
    <div class="logo-section">
      <div class="logo">
        <img src="public/images/123logo.png" alt="Book Store Logo" class="logo-img">
        <span class="logo-text">Book Store</span>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="main-nav">
      <ul class="nav-menu">
        <li class="nav-item">
          <a href="index.php" class="nav-link">
            <span class="nav-icon">🏠</span>
            Trang chủ
          </a>
        </li>
        <li class="nav-item">
          <a href="view_tintuc.php" class="nav-link">
            <span class="nav-icon">📰</span>
            Tin tức
          </a>
        </li>
        <?php if(isset($_SESSION['Username'])): ?>
        <li class="nav-item dropdown">
          <a href="#" class="nav-link">
            <span class="nav-icon">📚</span>
            Sản phẩm
            <span class="dropdown-arrow">▼</span>
          </a>
          <ul class="dropdown-menu">
            <?php
            // Lấy danh sách loại sản phẩm từ database
            require_once "connect.php";
            $sql_categories = "SELECT * FROM adproducttype ORDER BY Ten_loaisp";
            $result_categories = mysqli_query($conn, $sql_categories);
            
            if(mysqli_num_rows($result_categories) > 0) {
                while($category = mysqli_fetch_assoc($result_categories)) {
                    echo '<li><a href="products.php?category=' . urlencode($category['Ma_loaisp']) . '">' . htmlspecialchars($category['Ten_loaisp']) . '</a></li>';
                }
            }
            ?>
            <li class="dropdown-divider"></li>
            <li><a href="products.php">Tất cả sản phẩm</a></li>
          </ul>
        </li>
        <?php endif; ?>
        <?php if(isset($_SESSION['quyen']) && $_SESSION['quyen'] == '1'): ?>
        <li class="nav-item dropdown">
          <a href="#" class="nav-link">
            <span class="nav-icon">⚙️</span>
            Quản lý
            <span class="dropdown-arrow">▼</span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="dongsp.php">Dòng sản phẩm</a></li>
            <li><a href="sp.php">Sản phẩm</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="KH.php" class="nav-link">
            <span class="nav-icon">👥</span>
            Khách hàng
          </a>
        </li>
        <li class="nav-item">
          <a href="donhang.php" class="nav-link">
            <span class="nav-icon">📦</span>
            Đơn hàng
          </a>
        </li>
        <li class="nav-item">
          <a href="tintuc.php" class="nav-link">
            <span class="nav-icon">📰</span>
            Quản lý tin tức
          </a>
        </li>
        <li class="nav-item">
          <a href="thongke.php" class="nav-link">
            <span class="nav-icon">📊</span>
            Thống kê
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>

    <!-- User Section -->
    <div class="user-section">
      <?php if(isset($_SESSION['Username'])): ?>
        <!-- Cart and Orders Section for Customers -->
        <?php if(isset($_SESSION['quyen']) && $_SESSION['quyen'] == '0'): ?>
          <div class="cart-orders-container">
            <div class="cart-section">
              <a href="addtocart.php" class="cart-btn">
                <span class="cart-icon">🛒</span>
                <span class="cart-text">Giỏ hàng</span>
                <?php
                // Đếm số sản phẩm trong giỏ hàng
                $cart_count = 0;
                if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    foreach($_SESSION['cart'] as $item) {
                        $cart_count += $item['qty'];
                    }
                }
                if($cart_count > 0):
                ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
              </a>
            </div>
            <div class="orders-section">
              <a href="my_orders.php" class="orders-btn">
                <span class="orders-icon">📦</span>
                <span class="orders-text">Đơn hàng của tôi</span>
              </a>
            </div>

          </div>
        <?php endif; ?>
        
        <div class="user-info">
          <a href="edit_profile.php" class="user-avatar-link" title="Chỉnh sửa thông tin cá nhân">
            <div class="user-avatar">
              <span class="avatar-icon">👤</span>
            </div>
          </a>
          <div class="user-details">
            <div class="user-name">
              <span class="username"><?php echo htmlspecialchars($_SESSION['Username']); ?></span>
              <?php if(isset($_SESSION['Fullname'])): ?>
                <span class="fullname">(<?php echo htmlspecialchars($_SESSION['Fullname']); ?>)</span>
              <?php endif; ?>
            </div>
            <div class="user-meta">
              <?php if(isset($_SESSION['quyen'])): ?>
                <span class="user-role"><?php echo ($_SESSION['quyen'] == '1') ? 'Admin' : 'Khách hàng'; ?></span>
              <?php endif; ?>
              <?php
              if(isset($_SESSION['counter'])) {
                  $_SESSION['counter'] += 1;
              } else {
                  $_SESSION['counter'] = 1;
              }
              ?>
              <span class="visit-count">Lượt truy cập: <?php echo $_SESSION['counter']; ?></span>
            </div>
          </div>
        </div>
        <div class="user-actions">
          <a href="logout.php" class="logout-btn">
            <span class="logout-icon">🚪</span>
            Đăng xuất
          </a>
        </div>
      <?php else: ?>
        <div class="guest-actions">
          <a href="login.php" class="auth-btn login-btn">
            <span class="auth-icon">🔑</span>
            Đăng nhập
          </a>
          <a href="dangkithanhvien.php" class="auth-btn register-btn">
            <span class="auth-icon">📝</span>
            Đăng ký
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>
