<?php
require_once("connect.php");
require_once("./view/header.php");
require_once("./view/header1.php");
?>
<link href="public/stylee.css" rel="stylesheet" type="text/css" />

<style>
/* Override old footer styles to prevent conflicts */
.footer {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
    border-top: none !important;
    color: #ecf0f1 !important;
    font-weight: normal !important;
    height: auto !important;
    line-height: normal !important;
    bottom: auto !important;
    left: auto !important;
    width: 100% !important;
    text-align: left !important;
    padding: 50px 0 0 0 !important;
    margin-top: 80px !important;
    position: relative !important;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #e74c3c, #f39c12, #f1c40f, #27ae60, #3498db, #9b59b6);
}

/* Override old footer grid styles */
.gird_row {
    display: none !important;
}

.gird_coloum {
    display: none !important;
}

.footer_item_link {
    display: none !important;
}

.footer_list {
    display: none !important;
}
</style>

<?php
// Kiểm tra quyền admin
if(!isset($_SESSION['quyen']) || $_SESSION['quyen'] != '1') {
    header('location: index.php');
    exit();
}

// Xử lý filter thời gian
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'month';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

// Tính toán thời gian
switch($filter) {
    case 'day':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        $period_text = "Hôm nay (" . date('d/m/Y') . ")";
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d', strtotime('sunday this week'));
        $period_text = "Tuần này (" . date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date)) . ")";
        break;
    case 'month':
        $start_date = $year . '-' . $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        $period_text = "Tháng " . $month . "/" . $year;
        break;
    case 'year':
        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';
        $period_text = "Năm " . $year;
        break;
    default:
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $period_text = "Tháng " . date('m/Y');
}

// Thống kê tổng quan
$sql_total_products = "SELECT COUNT(*) as total FROM adproduct";
$result_total_products = mysqli_query($conn, $sql_total_products);
$total_products = mysqli_fetch_assoc($result_total_products)['total'];

$sql_total_stock = "SELECT SUM(soluong) as total_stock FROM adproduct";
$result_total_stock = mysqli_query($conn, $sql_total_stock);
$total_stock = mysqli_fetch_assoc($result_total_stock)['total_stock'] ?? 0;

$sql_total_orders = "SELECT COUNT(*) as total FROM `order` WHERE create_date BETWEEN ? AND ?";
$stmt_orders = mysqli_prepare($conn, $sql_total_orders);
mysqli_stmt_bind_param($stmt_orders, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_orders);
$result_orders = mysqli_stmt_get_result($stmt_orders);
$total_orders = mysqli_fetch_assoc($result_orders)['total'];

$sql_total_revenue = "SELECT SUM(tongtien) as total_revenue FROM `order` WHERE create_date BETWEEN ? AND ? AND trangthai IN (1, 2)";
$stmt_revenue = mysqli_prepare($conn, $sql_total_revenue);
mysqli_stmt_bind_param($stmt_revenue, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_revenue);
$result_revenue = mysqli_stmt_get_result($stmt_revenue);
$total_revenue = mysqli_fetch_assoc($result_revenue)['total_revenue'] ?? 0;

// Tính lãi suất (doanh thu - chi phí)
$sql_total_cost = "SELECT SUM(od.soluong * p.gianhap) as total_cost 
                   FROM orderdetail od 
                   JOIN adproduct p ON od.ma_sp = p.ma_sp 
                   JOIN `order` o ON od.mahd = o.mahd 
                   WHERE o.create_date BETWEEN ? AND ? AND o.trangthai IN (1, 2)";
$stmt_cost = mysqli_prepare($conn, $sql_total_cost);
mysqli_stmt_bind_param($stmt_cost, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_cost);
$result_cost = mysqli_stmt_get_result($stmt_cost);
$total_cost = mysqli_fetch_assoc($result_cost)['total_cost'] ?? 0;

$profit = $total_revenue - $total_cost;
$profit_rate = $total_revenue > 0 ? ($profit / $total_revenue) * 100 : 0;

// Sản phẩm bán chạy
$sql_best_sellers = "SELECT od.masp, od.tensp, SUM(od.soluong) as total_sold, 
                     SUM(od.soluong * od.giaxuat) as total_revenue
                     FROM orderdetail od 
                     JOIN `order` o ON od.mahd = o.mahd 
                     WHERE o.create_date BETWEEN ? AND ? AND o.trangthai IN (1, 2)
                     GROUP BY od.masp 
                     ORDER BY total_sold DESC 
                     LIMIT 5";
$stmt_best = mysqli_prepare($conn, $sql_best_sellers);
mysqli_stmt_bind_param($stmt_best, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_best);
$result_best = mysqli_stmt_get_result($stmt_best);

// Sản phẩm bán chậm (không bán được trong thời gian này)
$sql_slow_sellers = "SELECT p.ma_sp, p.tensp, p.soluong as stock, p.giaxuat
                     FROM adproduct p 
                     WHERE p.ma_sp NOT IN (
                         SELECT DISTINCT od.masp 
                         FROM orderdetail od 
                         JOIN `order` o ON od.mahd = o.mahd 
                         WHERE o.create_date BETWEEN ? AND ? AND o.trangthai IN (1, 2)
                     )
                     ORDER BY p.soluong DESC 
                     LIMIT 5";
$stmt_slow = mysqli_prepare($conn, $sql_slow_sellers);
mysqli_stmt_bind_param($stmt_slow, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_slow);
$result_slow = mysqli_stmt_get_result($stmt_slow);

// Hàng tồn kho thấp
$sql_low_stock = "SELECT ma_sp, tensp, soluong, giaxuat 
                  FROM adproduct 
                  WHERE soluong <= 10 
                  ORDER BY soluong ASC 
                  LIMIT 10";
$result_low_stock = mysqli_query($conn, $sql_low_stock);
?>

<div class="main">
    <div class="page-header">
        <h1>📊 Thống kê & Báo cáo</h1>
        <p>Thống kê hàng tồn kho và doanh thu theo thời gian</p>
    </div>
    
    <div class="content-wrapper">
        <!-- Filter Controls -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="filter">Thời gian:</label>
                    <select name="filter" id="filter" onchange="this.form.submit()">
                        <option value="day" <?php echo $filter == 'day' ? 'selected' : ''; ?>>Hôm nay</option>
                        <option value="week" <?php echo $filter == 'week' ? 'selected' : ''; ?>>Tuần này</option>
                        <option value="month" <?php echo $filter == 'month' ? 'selected' : ''; ?>>Tháng</option>
                        <option value="year" <?php echo $filter == 'year' ? 'selected' : ''; ?>>Năm</option>
                    </select>
                </div>
                
                <?php if($filter == 'month'): ?>
                <div class="filter-group">
                    <label for="month">Tháng:</label>
                    <select name="month" id="month" onchange="this.form.submit()">
                        <?php for($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo $month == sprintf('%02d', $i) ? 'selected' : ''; ?>>
                            Tháng <?php echo $i; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if($filter == 'month' || $filter == 'year'): ?>
                <div class="filter-group">
                    <label for="year">Năm:</label>
                    <select name="year" id="year" onchange="this.form.submit()">
                        <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>
            
            <div class="period-info">
                <span class="period-text">📅 <?php echo $period_text; ?></span>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="card-icon">📦</div>
                <div class="card-content">
                    <h3>Tổng sản phẩm</h3>
                    <p class="card-number"><?php echo number_format($total_products); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">🏪</div>
                <div class="card-content">
                    <h3>Hàng tồn kho</h3>
                    <p class="card-number"><?php echo number_format($total_stock); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">📋</div>
                <div class="card-content">
                    <h3>Đơn hàng</h3>
                    <p class="card-number"><?php echo number_format($total_orders); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">💰</div>
                <div class="card-content">
                    <h3>Doanh thu</h3>
                    <p class="card-number"><?php echo number_format($total_revenue); ?> VNĐ</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">💵</div>
                <div class="card-content">
                    <h3>Lợi nhuận</h3>
                    <p class="card-number <?php echo $profit >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($profit); ?> VNĐ
                    </p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">📈</div>
                <div class="card-content">
                    <h3>Tỷ lệ lãi</h3>
                    <p class="card-number <?php echo $profit_rate >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($profit_rate, 1); ?>%
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Detailed Statistics -->
        <div class="stats-grid">
                    <!-- Sản phẩm bán chạy -->
        <div class="stats-card">
            <div class="stats-header">
                <h3 class="stats-title">🔥 Sản phẩm bán chạy</h3>
                <a href="thongke_doanhthu.php" class="btn btn-primary">Xem chi tiết doanh thu</a>
            </div>
                <div class="stats-content">
                    <?php if(mysqli_num_rows($result_best) > 0): ?>
                    <div class="product-list">
                        <?php $rank = 1; while($product = mysqli_fetch_assoc($result_best)): ?>
                        <div class="product-item">
                            <div class="product-rank">#<?php echo $rank; ?></div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['tensp']); ?></div>
                                <div class="product-details">
                                    <span class="sold-count">Đã bán: <?php echo number_format($product['total_sold']); ?></span>
                                    <span class="revenue">Doanh thu: <?php echo number_format($product['total_revenue']); ?> VNĐ</span>
                                </div>
                            </div>
                        </div>
                        <?php $rank++; endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <p>Chưa có dữ liệu bán hàng</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sản phẩm bán chậm -->
            <div class="stats-card">
                <h3 class="stats-title">🐌 Sản phẩm bán chậm</h3>
                <div class="stats-content">
                    <?php if(mysqli_num_rows($result_slow) > 0): ?>
                    <div class="product-list">
                        <?php while($product = mysqli_fetch_assoc($result_slow)): ?>
                        <div class="product-item">
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['tensp']); ?></div>
                                <div class="product-details">
                                    <span class="stock-count">Tồn kho: <?php echo number_format($product['stock']); ?></span>
                                    <span class="price">Giá: <?php echo number_format($product['giaxuat']); ?> VNĐ</span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <p>Tất cả sản phẩm đều có bán</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Hàng tồn kho thấp -->
        <div class="stats-card full-width">
            <div class="stats-header">
                <h3 class="stats-title">⚠️ Hàng tồn kho thấp (≤ 10)</h3>
                <a href="thongke_tonkho.php" class="btn btn-primary">Xem chi tiết tồn kho</a>
            </div>
            <div class="stats-content">
                <?php if(mysqli_num_rows($result_low_stock) > 0): ?>
                <div class="table-container">
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng tồn</th>
                                <th>Giá bán</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product = mysqli_fetch_assoc($result_low_stock)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['ma_sp']); ?></td>
                                <td><?php echo htmlspecialchars($product['tensp']); ?></td>
                                <td>
                                    <span class="stock-badge <?php echo $product['soluong'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                        <?php echo $product['soluong']; ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($product['giaxuat']); ?> VNĐ</td>
                                <td>
                                    <?php if($product['soluong'] == 0): ?>
                                    <span class="status-badge out-of-stock">Hết hàng</span>
                                    <?php else: ?>
                                    <span class="status-badge low-stock">Tồn kho thấp</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>Không có sản phẩm nào tồn kho thấp</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
  .page-header {
      text-align: center;
      margin-bottom: 40px;
      padding: 40px 30%;
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
        max-width: 1600px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .filter-form {
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .filter-group label {
        font-weight: 600;
        color: #333;
        white-space: nowrap;
    }
    
    .filter-group select {
        padding: 8px 12px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        transition: all 0.3s ease;
    }
    
    .filter-group select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .period-info {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
    }
    
    /* Summary Cards */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .summary-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
    }
    
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .card-icon {
        font-size: 2.5rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        border-radius: 15px;
    }
    
    .card-content h3 {
        margin: 0 0 8px 0;
        font-size: 1rem;
        color: #666;
        font-weight: 500;
    }
    
    .card-number {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
    }
    
    .card-number.positive {
        color: #28a745;
    }
    
    .card-number.negative {
        color: #dc3545;
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .stats-card.full-width {
        grid-column: 1 / -1;
    }
    
    .stats-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin: 0;
        color: #333;
    }
    
    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #f1f3f4;
        padding-bottom: 10px;
    }
    
    /* Product List */
    .product-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .product-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .product-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .product-rank {
        background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }
    
    .product-info {
        flex: 1;
    }
    
    .product-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .product-details {
        display: flex;
        gap: 15px;
        font-size: 0.9rem;
        color: #666;
    }
    
    /* Table */
    .table-container {
        overflow-x: auto;
    }
    
    .stats-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    
    .stats-table th,
    .stats-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e1e5e9;
    }
    
    .stats-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    
    .stats-table tr:hover {
        background: #f8f9fa;
    }
    
    .stock-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .stock-badge.out-of-stock {
        background: #f8d7da;
        color: #721c24;
    }
    
    .stock-badge.low-stock {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-badge.out-of-stock {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-badge.low-stock {
        background: #fff3cd;
        color: #856404;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }
    
    .empty-state p {
        margin: 0;
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
        
        .filter-section {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-form {
            justify-content: center;
        }
        
        .summary-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .product-details {
            flex-direction: column;
            gap: 5px;
        }
        
        .stats-table {
            font-size: 0.9rem;
        }
        
        .stats-table th,
        .stats-table td {
            padding: 8px 10px;
        }
    }
</style>

<?php include('./view/footer.php'); ?> 