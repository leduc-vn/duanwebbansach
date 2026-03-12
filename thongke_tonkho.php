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

// Xử lý filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : 'all';

// Lấy danh sách loại sản phẩm
$sql_categories = "SELECT DISTINCT Ma_loaisp FROM adproduct ORDER BY Ma_loaisp";
$result_categories = mysqli_query($conn, $sql_categories);

// Xây dựng query với filter
$where_conditions = [];
$params = [];
$param_types = "";

if(!empty($category_filter)) {
    $where_conditions[] = "Ma_loaisp = ?";
    $params[] = $category_filter;
    $param_types .= "s";
}

if($stock_filter == 'low') {
    $where_conditions[] = "soluong <= 10";
} elseif($stock_filter == 'out') {
    $where_conditions[] = "soluong = 0";
} elseif($stock_filter == 'high') {
    $where_conditions[] = "soluong > 50";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Lấy danh sách sản phẩm với thống kê
$sql_products = "SELECT p.*, 
                        COALESCE(SUM(od.soluong), 0) as total_sold,
                        COALESCE(SUM(od.soluong * od.dongia), 0) as total_revenue
                 FROM adproduct p 
                 LEFT JOIN orderdetail od ON p.ma_sp = od.masp
                 LEFT JOIN `order` o ON od.mahd = o.mahd AND o.trangthai IN (1, 2)
                 $where_clause
                 GROUP BY p.ma_sp 
                 ORDER BY p.soluong ASC";

if(!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql_products);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result_products = mysqli_stmt_get_result($stmt);
} else {
    $result_products = mysqli_query($conn, $sql_products);
}

// Thống kê tổng quan
$sql_stats = "SELECT 
                COUNT(*) as total_products,
                SUM(soluong) as total_stock,
                SUM(soluong * giaxuat) as total_value,
                COUNT(CASE WHEN soluong = 0 THEN 1 END) as out_of_stock,
                COUNT(CASE WHEN soluong <= 10 AND soluong > 0 THEN 1 END) as low_stock,
                COUNT(CASE WHEN soluong > 50 THEN 1 END) as high_stock
              FROM adproduct";
$result_stats = mysqli_query($conn, $sql_stats);
$stats = mysqli_fetch_assoc($result_stats);
?>

<div class="main">
    <div class="page-header">
        <h1>📦 Thống kê Hàng tồn kho</h1>
        <p>Quản lý và theo dõi tình trạng hàng tồn kho</p>
    </div>
    
    <div class="content-wrapper">
        <!-- Filter Controls -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="category">Loại sản phẩm:</label>
                    <select name="category" id="category" onchange="this.form.submit()">
                        <option value="">Tất cả loại</option>
                        <?php while($category = mysqli_fetch_assoc($result_categories)): ?>
                        <option value="<?php echo htmlspecialchars($category['Ma_loaisp']); ?>" 
                                <?php echo $category_filter == $category['Ma_loaisp'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['Ma_loaisp']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="stock">Tình trạng tồn kho:</label>
                    <select name="stock" id="stock" onchange="this.form.submit()">
                        <option value="all" <?php echo $stock_filter == 'all' ? 'selected' : ''; ?>>Tất cả</option>
                        <option value="out" <?php echo $stock_filter == 'out' ? 'selected' : ''; ?>>Hết hàng</option>
                        <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>>Tồn kho thấp (≤10)</option>
                        <option value="high" <?php echo $stock_filter == 'high' ? 'selected' : ''; ?>>Tồn kho cao (>50)</option>
                    </select>
                </div>
            </form>
            
            <div class="filter-actions">
                <a href="thongke_tonkho.php" class="btn btn-secondary">Làm mới</a>
                <a href="thongke.php" class="btn btn-primary">Quay lại thống kê</a>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="card-icon">📦</div>
                <div class="card-content">
                    <h3>Tổng sản phẩm</h3>
                    <p class="card-number"><?php echo number_format($stats['total_products']); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">🏪</div>
                <div class="card-content">
                    <h3>Tổng tồn kho</h3>
                    <p class="card-number"><?php echo number_format($stats['total_stock']); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">💰</div>
                <div class="card-content">
                    <h3>Giá trị tồn kho</h3>
                    <p class="card-number"><?php echo number_format($stats['total_value']); ?> VNĐ</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">⚠️</div>
                <div class="card-content">
                    <h3>Hết hàng</h3>
                    <p class="card-number negative"><?php echo number_format($stats['out_of_stock']); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">🔶</div>
                <div class="card-content">
                    <h3>Tồn kho thấp</h3>
                    <p class="card-number warning"><?php echo number_format($stats['low_stock']); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon">✅</div>
                <div class="card-content">
                    <h3>Tồn kho cao</h3>
                    <p class="card-number positive"><?php echo number_format($stats['high_stock']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Products Table -->
        <div class="table-section">
            <div class="table-header">
                <h3>📋 Danh sách sản phẩm</h3>
                <div class="table-actions">
                    <span class="total-count">Tổng: <?php echo mysqli_num_rows($result_products); ?> sản phẩm</span>
                </div>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã SP</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Loại</th>
                            <th>Tồn kho</th>
                            <th>Giá nhập</th>
                            <th>Giá bán</th>
                            <th>Đã bán</th>
                            <th>Doanh thu</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = mysqli_fetch_assoc($result_products)): ?>
                        <tr class="<?php echo $product['soluong'] == 0 ? 'out-of-stock-row' : ($product['soluong'] <= 10 ? 'low-stock-row' : ''); ?>">
                            <td><?php echo htmlspecialchars($product['ma_sp']); ?></td>
                            <td>
                                <?php if(!empty($product['anhsp'])): ?>
                                <img src="public/images/<?php echo htmlspecialchars($product['anhsp']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['tensp']); ?>" 
                                     class="product-thumb" />
                                <?php else: ?>
                                <div class="no-image">📷</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="product-name"><?php echo htmlspecialchars($product['tensp']); ?></div>
                                <div class="product-desc"><?php echo htmlspecialchars(substr($product['motasp'], 0, 50)) . '...'; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($product['Ma_loaisp']); ?></td>
                            <td>
                                <span class="stock-badge <?php echo $product['soluong'] == 0 ? 'out-of-stock' : ($product['soluong'] <= 10 ? 'low-stock' : 'normal-stock'); ?>">
                                    <?php echo number_format($product['soluong']); ?>
                                </span>
                            </td>
                            <td><?php echo number_format($product['gianhap']); ?> VNĐ</td>
                            <td><?php echo number_format($product['giaxuat']); ?> VNĐ</td>
                            <td><?php echo number_format($product['total_sold']); ?></td>
                            <td><?php echo number_format($product['total_revenue']); ?> VNĐ</td>
                            <td>
                                <?php if($product['soluong'] == 0): ?>
                                <span class="status-badge out-of-stock">Hết hàng</span>
                                <?php elseif($product['soluong'] <= 10): ?>
                                <span class="status-badge low-stock">Tồn kho thấp</span>
                                <?php elseif($product['soluong'] > 50): ?>
                                <span class="status-badge high-stock">Tồn kho cao</span>
                                <?php else: ?>
                                <span class="status-badge normal-stock">Bình thường</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="update_sp.php?id=<?php echo $product['ma_sp']; ?>" class="btn btn-edit" title="Sửa">
                                        ✏️
                                    </a>
                                    <a href="product_detail.php?id=<?php echo $product['ma_sp']; ?>" class="btn btn-view" title="Xem chi tiết">
                                        👁️
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
    
    .filter-actions {
        display: flex;
        gap: 10px;
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
    
    .card-number.warning {
        color: #ffc107;
    }
    
    /* Table Section */
    .table-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f4;
    }
    
    .table-header h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 600;
        color: #333;
    }
    
    .total-count {
        font-weight: 600;
        color: #666;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 12px 8px;
        text-align: left;
        border-bottom: 1px solid #e1e5e9;
        vertical-align: middle;
    }
    
    .data-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .data-table tr:hover {
        background: #f8f9fa;
    }
    
    .out-of-stock-row {
        background: #f8d7da !important;
    }
    
    .low-stock-row {
        background: #fff3cd !important;
    }
    
    .product-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e1e5e9;
    }
    
    .no-image {
        width: 50px;
        height: 50px;
        background: #f8f9fa;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #ccc;
    }
    
    .product-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
    }
    
    .product-desc {
        font-size: 0.8rem;
        color: #666;
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
    
    .stock-badge.normal-stock {
        background: #d4edda;
        color: #155724;
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
    
    .status-badge.high-stock {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.normal-stock {
        background: #e2e3e5;
        color: #383d41;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .btn {
        padding: 6px 10px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-weight: 500;
        border: none;
        cursor: pointer;
        font-size: 12px;
        min-width: 30px;
    }
    
    .btn-edit {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
    }
    
    .btn-edit:hover {
        background: linear-gradient(45deg, #20c997, #17a2b8);
        transform: translateY(-1px);
        color: white;
        text-decoration: none;
    }
    
    .btn-view {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
    }
    
    .btn-view:hover {
        background: linear-gradient(45deg, #0056b3, #004085);
        transform: translateY(-1px);
        color: white;
        text-decoration: none;
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        padding: 8px 16px;
    }
    
    .btn-primary:hover {
        background: linear-gradient(45deg, #0056b3, #004085);
        color: white;
        text-decoration: none;
    }
    
    .btn-secondary {
        background: linear-gradient(45deg, #6c757d, #545b62);
        color: white;
        padding: 8px 16px;
    }
    
    .btn-secondary:hover {
        background: linear-gradient(45deg, #545b62, #495057);
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
        
        .data-table {
            font-size: 0.8rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 8px 5px;
        }
        
        .product-thumb,
        .no-image {
            width: 40px;
            height: 40px;
        }
    }
</style>

<?php include('./view/footer.php'); ?> 