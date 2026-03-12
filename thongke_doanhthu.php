<?php
require_once("connect.php");
require_once("./view/header.php");
require_once("./view/header1.php");
?>
<link href="public/stylee.css" rel="stylesheet" type="text/css" />


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

// Thống kê doanh thu
$sql_revenue = "SELECT SUM(tongtien) as total_revenue FROM `order` WHERE create_date BETWEEN ? AND ? AND trangthai IN (1, 2)";
$stmt_revenue = mysqli_prepare($conn, $sql_revenue);
mysqli_stmt_bind_param($stmt_revenue, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_revenue);
$result_revenue = mysqli_stmt_get_result($stmt_revenue);
$total_revenue = mysqli_fetch_assoc($result_revenue)['total_revenue'] ?? 0;

// Thống kê chi phí
$sql_cost = "SELECT SUM(od.soluong * p.gianhap) as total_cost 
             FROM orderdetail od 
             JOIN adproduct p ON od.masp = p.ma_sp 
             JOIN `order` o ON od.mahd = o.mahd 
             WHERE o.create_date BETWEEN ? AND ? AND o.trangthai IN (1, 2)";
$stmt_cost = mysqli_prepare($conn, $sql_cost);
mysqli_stmt_bind_param($stmt_cost, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_cost);
$result_cost = mysqli_stmt_get_result($stmt_cost);
$total_cost = mysqli_fetch_assoc($result_cost)['total_cost'] ?? 0;

// Tính lợi nhuận
$profit = $total_revenue - $total_cost;
$profit_rate = $total_revenue > 0 ? ($profit / $total_revenue) * 100 : 0;

// Thống kê đơn hàng
$sql_orders = "SELECT COUNT(*) as total_orders FROM `order` WHERE create_date BETWEEN ? AND ?";
$stmt_orders = mysqli_prepare($conn, $sql_orders);
mysqli_stmt_bind_param($stmt_orders, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_orders);
$result_orders = mysqli_stmt_get_result($stmt_orders);
$total_orders = mysqli_fetch_assoc($result_orders)['total_orders'];

$sql_completed_orders = "SELECT COUNT(*) as completed_orders FROM `order` WHERE create_date BETWEEN ? AND ? AND trangthai IN (1, 2)";
$stmt_completed = mysqli_prepare($conn, $sql_completed_orders);
mysqli_stmt_bind_param($stmt_completed, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_completed);
$result_completed = mysqli_stmt_get_result($stmt_completed);
$completed_orders = mysqli_fetch_assoc($result_completed)['completed_orders'];

// Thống kê theo ngày (cho biểu đồ)
$sql_daily = "SELECT DATE(create_date) as date, 
                     COUNT(*) as orders,
                     SUM(tongtien) as revenue
              FROM `order` 
              WHERE create_date BETWEEN ? AND ? AND trangthai IN (1, 2)
              GROUP BY DATE(create_date) 
              ORDER BY date";
$stmt_daily = mysqli_prepare($conn, $sql_daily);
mysqli_stmt_bind_param($stmt_daily, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_daily);
$result_daily = mysqli_stmt_get_result($stmt_daily);

$daily_data = [];
while($row = mysqli_fetch_assoc($result_daily)) {
    $daily_data[] = $row;
}

// Top sản phẩm bán chạy
$sql_top_products = "SELECT od.masp, od.tensp, 
                            SUM(od.soluong) as total_sold,
                            SUM(od.soluong * od.dongia) as total_revenue,
                            SUM(od.soluong * (od.dongia - p.gianhap)) as total_profit
                     FROM orderdetail od 
                     JOIN adproduct p ON od.masp = p.ma_sp 
                     JOIN `order` o ON od.mahd = o.mahd 
                     WHERE o.create_date BETWEEN ? AND ? AND o.trangthai IN (1, 2)
                     GROUP BY od.masp 
                     ORDER BY total_sold DESC 
                     LIMIT 10";
$stmt_top = mysqli_prepare($conn, $sql_top_products);
mysqli_stmt_bind_param($stmt_top, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_top);
$result_top = mysqli_stmt_get_result($stmt_top);

// Thống kê theo loại sản phẩm
$sql_category_stats = "SELECT p.Ma_loaisp,
                              SUM(od.soluong) as total_sold,
                              SUM(od.soluong * od.dongia) as total_revenue
                       FROM orderdetail od 
                       JOIN adproduct p ON od.masp = p.ma_sp 
                       JOIN `order` o ON od.mahd = o.mahd 
                       WHERE o.create_date BETWEEN ? AND ? AND o.trangthai IN (1, 2)
                       GROUP BY p.Ma_loaisp 
                       ORDER BY total_revenue DESC";
$stmt_category = mysqli_prepare($conn, $sql_category_stats);
mysqli_stmt_bind_param($stmt_category, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_category);
$result_category = mysqli_stmt_get_result($stmt_category);
?>

<div class="main">
    <div class="page-header">
        <h1>💰 Thống kê Doanh thu</h1>
        <p>Phân tích chi tiết doanh thu và lợi nhuận</p>
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
                <div class="card-icon">💰</div>
                <div class="card-content">
                    <h3>Doanh thu</h3>
                    <p class="card-number"><?php echo number_format($total_revenue); ?> VNĐ</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">💸</div>
                <div class="card-content">
                    <h3>Chi phí</h3>
                    <p class="card-number"><?php echo number_format($total_cost); ?> VNĐ</p>
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
            
            <div class="summary-card">
                <div class="card-icon">📋</div>
                <div class="card-content">
                    <h3>Tổng đơn hàng</h3>
                    <p class="card-number"><?php echo number_format($total_orders); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">✅</div>
                <div class="card-content">
                    <h3>Đơn hoàn thành</h3>
                    <p class="card-number"><?php echo number_format($completed_orders); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-grid">
            <!-- Revenue Chart -->
            <div class="chart-card">
                <h3 class="chart-title">📊 Biểu đồ doanh thu theo ngày</h3>
                <div class="chart-container">
                    <?php if(!empty($daily_data)): ?>
                    <div class="chart-bars">
                        <?php 
                        $max_revenue = max(array_column($daily_data, 'revenue'));
                        foreach($daily_data as $data): 
                            $height = $max_revenue > 0 ? ($data['revenue'] / $max_revenue) * 200 : 0;
                        ?>
                        <div class="chart-bar">
                            <div class="bar" style="height: <?php echo $height; ?>px;" 
                                 title="<?php echo date('d/m', strtotime($data['date'])); ?>: <?php echo number_format($data['revenue']); ?> VNĐ">
                            </div>
                            <div class="bar-label"><?php echo date('d/m', strtotime($data['date'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-chart">
                        <p>Không có dữ liệu doanh thu</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Category Stats -->
            <div class="chart-card">
                <h3 class="chart-title">📊 Doanh thu theo loại sản phẩm</h3>
                <div class="chart-container">
                    <?php if(mysqli_num_rows($result_category) > 0): ?>
                    <div class="category-stats">
                        <?php while($category = mysqli_fetch_assoc($result_category)): ?>
                        <div class="category-item">
                            <div class="category-name"><?php echo htmlspecialchars($category['Ma_loaisp']); ?></div>
                            <div class="category-bar">
                                <div class="bar-fill" style="width: <?php echo $total_revenue > 0 ? ($category['total_revenue'] / $total_revenue) * 100 : 0; ?>%"></div>
                            </div>
                            <div class="category-value"><?php echo number_format($category['total_revenue']); ?> VNĐ</div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-chart">
                        <p>Không có dữ liệu theo loại</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="stats-card full-width">
            <div class="stats-header">
                <h3 class="stats-title">🔥 Top 10 sản phẩm bán chạy</h3>
                <a href="thongke.php" class="btn btn-primary">Quay lại thống kê</a>
            </div>
            <div class="stats-content">
                <?php if(mysqli_num_rows($result_top) > 0): ?>
                <div class="table-container">
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Hạng</th>
                                <th>Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng bán</th>
                                <th>Doanh thu</th>
                                <th>Lợi nhuận</th>
                                <th>Tỷ lệ lãi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; while($product = mysqli_fetch_assoc($result_top)): ?>
                            <tr>
                                <td>
                                    <span class="rank-badge rank-<?php echo $rank <= 3 ? $rank : 'other'; ?>">
                                        #<?php echo $rank; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($product['masp']); ?></td>
                                <td><?php echo htmlspecialchars($product['tensp']); ?></td>
                                <td><?php echo number_format($product['total_sold']); ?></td>
                                <td><?php echo number_format($product['total_revenue']); ?> VNĐ</td>
                                <td class="<?php echo $product['total_profit'] >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo number_format($product['total_profit']); ?> VNĐ
                                </td>
                                <td class="<?php echo $product['total_revenue'] > 0 ? ($product['total_profit'] / $product['total_revenue'] * 100) >= 0 ? 'positive' : 'negative' : ''; ?>">
                                    <?php echo $product['total_revenue'] > 0 ? number_format($product['total_profit'] / $product['total_revenue'] * 100, 1) : 0; ?>%
                                </td>
                            </tr>
                            <?php $rank++; endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>Chưa có dữ liệu bán hàng</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
        max-width: 1400px;
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .summary-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.3s ease;
    }
    
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .card-icon {
        font-size: 2rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        border-radius: 12px;
    }
    
    .card-content h3 {
        margin: 0 0 5px 0;
        font-size: 0.9rem;
        color: #666;
        font-weight: 500;
    }
    
    .card-number {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 700;
        color: #333;
    }
    
    .card-number.positive {
        color: #28a745;
    }
    
    .card-number.negative {
        color: #dc3545;
    }
    
    /* Charts Grid */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .chart-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin: 0 0 20px 0;
        color: #333;
        border-bottom: 2px solid #f1f3f4;
        padding-bottom: 10px;
    }
    
    .chart-container {
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Bar Chart */
    .chart-bars {
        display: flex;
        align-items: end;
        gap: 10px;
        height: 250px;
        width: 100%;
        padding: 20px 0;
    }
    
    .chart-bar {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    
    .bar {
        width: 30px;
        background: linear-gradient(45deg, #007bff, #0056b3);
        border-radius: 4px 4px 0 0;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .bar:hover {
        background: linear-gradient(45deg, #0056b3, #004085);
        transform: scale(1.05);
    }
    
    .bar-label {
        font-size: 0.8rem;
        color: #666;
        font-weight: 500;
    }
    
    /* Category Stats */
    .category-stats {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .category-item {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .category-name {
        min-width: 120px;
        font-weight: 600;
        color: #333;
    }
    
    .category-bar {
        flex: 1;
        height: 20px;
        background: #f1f3f4;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .bar-fill {
        height: 100%;
        background: linear-gradient(45deg, #28a745, #20c997);
        transition: width 0.3s ease;
    }
    
    .category-value {
        min-width: 100px;
        text-align: right;
        font-weight: 600;
        color: #333;
    }
    
    /* Empty Chart */
    .empty-chart {
        text-align: center;
        color: #666;
        font-size: 1.1rem;
    }
    
    /* Stats Card */
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .stats-card.full-width {
        grid-column: 1 / -1;
    }
    
    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #f1f3f4;
        padding-bottom: 10px;
    }
    
    .stats-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin: 0;
        color: #333;
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
    
    .rank-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        color: white;
    }
    
    .rank-badge.rank-1 {
        background: linear-gradient(45deg, #ffd700, #ffed4e);
        color: #333;
    }
    
    .rank-badge.rank-2 {
        background: linear-gradient(45deg, #c0c0c0, #e5e5e5);
        color: #333;
    }
    
    .rank-badge.rank-3 {
        background: linear-gradient(45deg, #cd7f32, #daa520);
        color: white;
    }
    
    .rank-badge.rank-other {
        background: linear-gradient(45deg, #6c757d, #545b62);
        color: white;
    }
    
    .positive {
        color: #28a745;
        font-weight: 600;
    }
    
    .negative {
        color: #dc3545;
        font-weight: 600;
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
    
    /* Buttons */
    .btn {
        padding: 8px 16px;
        border-radius: 20px;
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
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .charts-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .chart-bars {
            gap: 5px;
        }
        
        .bar {
            width: 20px;
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