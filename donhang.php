<?php
require_once "connect.php";

// Xử lý các action trước khi xuất HTML
if(isset($_GET["mod"])) {
    switch($_GET["mod"]) {
        case "update": {
            $status = $_GET['status'];
            $id = $_GET["id"];
                
                // Validate status
                if($status == "0" || $status == "1" || $status == "2") {
                    $sql = "UPDATE `order` SET `trangthai`=? WHERE mahd = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ss", $status, $id);
                    $result = mysqli_stmt_execute($stmt);
                    
                    if($result) {
                        $status_text = "";
                        switch($status) {
                            case "0": $status_text = "chưa hoàn thành"; break;
                            case "1": $status_text = "đang giao hàng"; break;
                            case "2": $status_text = "hoàn thành"; break;
                        }
                        $_SESSION['success_message'] = "Cập nhật đơn hàng #$id thành công: $status_text";
                    } else {
                        $_SESSION['error_message'] = "Lỗi khi cập nhật đơn hàng";
                    }
                } else {
                    $_SESSION['error_message'] = "Trạng thái không hợp lệ";
                }
            }
            break;
            
            case "confirm": {
                $id = $_GET["id"];
                
                // Cập nhật trạng thái thành "đang giao hàng"
                $status = "1";
                $sql = "UPDATE `order` SET `trangthai`=? WHERE mahd = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ss", $status, $id);
                $result = mysqli_stmt_execute($stmt);
                
                if($result) {
                    $_SESSION['success_message'] = "✅ Xác nhận đơn hàng #$id thành công! Đơn hàng đã chuyển sang trạng thái 'Đang giao hàng'.";
                } else {
                    $_SESSION['error_message'] = "❌ Lỗi khi xác nhận đơn hàng";
                }
            }
            break;
            
            case "complete": {
                $id = $_GET["id"];
                
                // Cập nhật trạng thái thành "hoàn thành"
                $status = "2";
                $sql = "UPDATE `order` SET `trangthai`=? WHERE mahd = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ss", $status, $id);
                $result = mysqli_stmt_execute($stmt);
                
                if($result) {
                    $_SESSION['success_message'] = "🎉 Đơn hàng #$id đã hoàn thành thành công! Khách hàng đã nhận được hàng.";
                } else {
                    $_SESSION['error_message'] = "❌ Lỗi khi hoàn thành đơn hàng";
                }
            }
            break;
            
            case "delete": {
                $id = $_GET["id"];
                
                // Lấy mã khách hàng trước khi xóa
                $sql = "SELECT makh FROM `order` WHERE `mahd` = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $order = mysqli_fetch_assoc($result);
                $idkh = $order['makh'];
                
                // Bắt đầu transaction
                mysqli_begin_transaction($conn);
                
                try {
                    // Xóa order detail
                    $sql = "DELETE FROM `orderdetail` WHERE `mahd` = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "s", $id);
                    mysqli_stmt_execute($stmt);
                    
                    // Xóa order
                    $sql = "DELETE FROM `order` WHERE `mahd` = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "s", $id);
                    mysqli_stmt_execute($stmt);
                    
                    // Xóa customer
                    $sql = "DELETE FROM `customer` WHERE `makh` = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "s", $idkh);
                    mysqli_stmt_execute($stmt);
                    
                    mysqli_commit($conn);
                    $_SESSION['success_message'] = "Xóa đơn hàng #$id thành công!";
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $_SESSION['error_message'] = "Lỗi khi xóa đơn hàng";
                }
            }
            break;
            
            case "view": {
                header("location: viewDonhang.php?id=" . $_GET['id']);
                exit();
            }
            break;
        }
        
        header('location: donhang.php');
        exit();
    }
    
    // Xử lý bộ lọc
    $where_clause = "";
    if(isset($_GET['filter'])) {
        switch($_GET['filter']) {
            case 'pending':
                $where_clause = "WHERE o.trangthai = 0";
                break;
            case 'shipping':
                $where_clause = "WHERE o.trangthai = 1";
                break;
            case 'completed':
                $where_clause = "WHERE o.trangthai = 2";
                break;
            default:
                $where_clause = "";
        }
    }
    
    // Hiển thị danh sách đơn hàng với thông tin khách hàng
    $sql = "SELECT o.*, c.tenkh, c.phone, c.email 
            FROM `order` o 
            LEFT JOIN customer c ON o.makh = c.makh 
            $where_clause
            ORDER BY o.create_date DESC";
    $rel = mysqli_query($conn, $sql);
?>
<?php require_once "./view/header.php"; ?>
<?php require_once "view/header1.php"; ?>
<body>
    
    <div class="main">
        <div class="page-header">
            <h1>📦 Quản lý đơn hàng</h1>
            <p>Quản lý và theo dõi tất cả đơn hàng của khách hàng</p>
        </div>
        
        <!-- Quy trình xử lý đơn hàng -->
        <div class="process-guide">
            <h3>🔄 Quy trình xử lý đơn hàng</h3>
            <div class="process-steps">
                <div class="process-step">
                    <div class="step-icon">⏳</div>
                    <div class="step-content">
                        <h4>Chờ xác nhận</h4>
                        <p>Đơn hàng mới được đặt, cần admin xác nhận</p>
                    </div>
                </div>
                <div class="step-arrow">→</div>
                <div class="process-step">
                    <div class="step-icon">🚚</div>
                    <div class="step-content">
                        <h4>Đang giao hàng</h4>
                        <p>Đơn hàng đã được xác nhận và đang vận chuyển</p>
                    </div>
                </div>
                <div class="step-arrow">→</div>
                <div class="process-step">
                    <div class="step-icon">✅</div>
                    <div class="step-content">
                        <h4>Hoàn thành</h4>
                        <p>Đơn hàng đã được giao thành công</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Thống kê nhanh -->
        <div class="stats-container">
            <?php
            $sql_pending = "SELECT COUNT(*) as count FROM `order` WHERE trangthai = 0";
            $result_pending = mysqli_query($conn, $sql_pending);
            $pending_count = mysqli_fetch_assoc($result_pending)['count'];
            
            $sql_shipping = "SELECT COUNT(*) as count FROM `order` WHERE trangthai = 1";
            $result_shipping = mysqli_query($conn, $sql_shipping);
            $shipping_count = mysqli_fetch_assoc($result_shipping)['count'];
            
            $sql_completed = "SELECT COUNT(*) as count FROM `order` WHERE trangthai = 2";
            $result_completed = mysqli_query($conn, $sql_completed);
            $completed_count = mysqli_fetch_assoc($result_completed)['count'];
            
            $sql_total = "SELECT COUNT(*) as count FROM `order`";
            $result_total = mysqli_query($conn, $sql_total);
            $total_count = mysqli_fetch_assoc($result_total)['count'];
            ?>
            <div class="stats-grid">
                <div class="stat-card stat-pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $pending_count; ?></div>
                        <div class="stat-label">Chờ xác nhận</div>
                    </div>
                </div>
                <div class="stat-card stat-shipping">
                    <div class="stat-icon">🚚</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $shipping_count; ?></div>
                        <div class="stat-label">Đang giao hàng</div>
                    </div>
                </div>
                <div class="stat-card stat-completed">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $completed_count; ?></div>
                        <div class="stat-label">Hoàn thành</div>
                    </div>
                </div>
                <div class="stat-card stat-total">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $total_count; ?></div>
                        <div class="stat-label">Tổng đơn hàng</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bộ lọc đơn hàng -->
        <div class="filter-container">
            <div class="filter-buttons">
                <a href="?filter=all" class="filter-btn <?php echo (!isset($_GET['filter']) || $_GET['filter'] == 'all') ? 'active' : ''; ?>">
                    📊 Tất cả
                </a>
                <a href="?filter=pending" class="filter-btn <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'pending') ? 'active' : ''; ?>">
                    ⏳ Chờ xác nhận
                </a>
                <a href="?filter=shipping" class="filter-btn <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'shipping') ? 'active' : ''; ?>">
                    🚚 Đang giao hàng
                </a>
                <a href="?filter=completed" class="filter-btn <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'completed') ? 'active' : ''; ?>">
                    ✅ Hoàn thành
                </a>
            </div>
            <div class="filter-note">
                <small>💡 <strong>Lưu ý:</strong> Đơn hàng phải được xác nhận trước khi có thể chuyển sang vận chuyển</small>
            </div>
        </div>
        
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(mysqli_num_rows($rel) > 0) {
                        while($row = mysqli_fetch_assoc($rel)) {
                            $status_class = "";
                            $status_text = "";
                            $status_icon = "";
                            
                            switch($row["trangthai"]) {
                                case "0":
                                    $status_class = "status-pending";
                                    $status_text = "Chờ xác nhận";
                                    $status_icon = "⏳";
                                    break;
                                case "1":
                                    $status_class = "status-shipping";
                                    $status_text = "Đang giao hàng";
                                    $status_icon = "🚚";
                                    break;
                                case "2":
                                    $status_class = "status-completed";
                                    $status_text = "Hoàn thành";
                                    $status_icon = "✅";
                                    break;
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo htmlspecialchars($row["mahd"]); ?></strong>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-name">
                                            <strong><?php echo htmlspecialchars($row["tenkh"]); ?></strong>
                                        </div>
                                        <div class="customer-details">
                                            <small>📞 <?php echo htmlspecialchars($row["phone"]); ?></small><br>
                                            <small>📧 <?php echo htmlspecialchars($row["email"]); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($row["create_date"])); ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_icon; ?> <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo number_format($row["tongtien"]); ?> VNĐ</strong>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="viewDonhang.php?id=<?php echo $row['mahd']; ?>" 
                                           class="btn btn-primary btn-sm" title="Xem chi tiết">
                                            👁️ Xem
                                        </a>
                                        
                                        <?php if($row["trangthai"] == "0"): ?>
                                            <a href="?id=<?php echo $row['mahd']; ?>&mod=confirm" 
                                               class="btn btn-warning btn-sm" 
                                               onclick="return confirm('Xác nhận đơn hàng này để bắt đầu vận chuyển?')"
                                               title="Xác nhận đơn hàng">
                                                ✅ Xác nhận
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($row["trangthai"] == "1"): ?>
                                            <a href="?id=<?php echo $row['mahd']; ?>&mod=complete" 
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('Đánh dấu đơn hàng đã hoàn thành?')"
                                               title="Hoàn thành đơn hàng">
                                                🎉 Hoàn thành
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($row["trangthai"] == "0"): ?>
                                            <a href="?id=<?php echo $row['mahd']; ?>&mod=delete" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?')"
                                               title="Xóa đơn hàng">
                                                🗑️ Xóa
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Dropdown cho các tùy chọn khác -->
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                ⚙️
                                            </button>
                                            <div class="dropdown-menu">
                                                <div class="dropdown-header">Thay đổi trạng thái</div>
                                                <a class="dropdown-item" href="?id=<?php echo $row['mahd']; ?>&mod=update&status=0">
                                                    ⏳ Chờ xác nhận
                                                </a>
                                                <a class="dropdown-item" href="?id=<?php echo $row['mahd']; ?>&mod=update&status=1">
                                                    🚚 Đang giao hàng
                                                </a>
                                                <a class="dropdown-item" href="?id=<?php echo $row['mahd']; ?>&mod=update&status=2">
                                                    ✅ Hoàn thành
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="?id=<?php echo $row['mahd']; ?>&mod=delete"
                                                   onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                                                    🗑️ Xóa đơn hàng
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="empty-state">
                                    <p>Chưa có đơn hàng nào</p>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .page-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .page-header p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* Quy trình xử lý đơn hàng */
        .process-guide {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .process-guide h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .process-steps {
            display: flex;
            justify-content: space-around;
            align-items: center;
            position: relative;
        }

        .process-step {
            display: flex;
            align-items: center;
            flex: 1;
            text-align: center;
        }

        .step-icon {
            font-size: 2.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(45deg, #e0e0e0, #b0b0b0);
            color: #333;
            margin-bottom: 15px;
        }

        .step-content h4 {
            margin-bottom: 5px;
            color: #333;
            font-size: 1.1rem;
        }

        .step-content p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0;
        }

        .step-arrow {
            font-size: 2rem;
            color: #ccc;
            margin: 0 20px;
            font-weight: bold;
        }

        /* Stats Container */
        .stats-container {
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .stat-pending .stat-icon {
            background: linear-gradient(45deg, #ffc107, #ffb300);
            color: #000;
        }
        
        .stat-shipping .stat-icon {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
        }
        
        .stat-completed .stat-icon {
            background: linear-gradient(45deg, #28a745, #218838);
            color: white;
        }
        
        .stat-total .stat-icon {
            background: linear-gradient(45deg, #6f42c1, #5a32a3);
            color: white;
        }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        /* Customer Info */
        .customer-info {
            min-width: 200px;
        }
        
        .customer-name {
            margin-bottom: 5px;
        }
        
        .customer-details {
            color: #666;
            font-size: 0.85rem;
        }
        
        .customer-details small {
            display: block;
            margin-bottom: 2px;
        }
        
        /* Status Badge */
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-pending {
            background: linear-gradient(45deg, #ffc107, #ffb300);
            color: #000;
        }
        
        .status-shipping {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
        }
        
        .status-completed {
            background: linear-gradient(45deg, #28a745, #218838);
            color: white;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .btn-sm {
            padding: 8px 12px;
            font-size: 12px;
            border-radius: 20px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #218838);
            color: white;
        }
        
        .btn-success:hover {
            background: linear-gradient(45deg, #218838, #1e7e34);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
        }
        
        .btn-danger:hover {
            background: linear-gradient(45deg, #c82333, #bd2130);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }
        
        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #e0a800);
            color: #000;
        }
        
        .btn-warning:hover {
            background: linear-gradient(45deg, #e0a800, #d39e00);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,193,7,0.3);
        }
        
        .dropdown-header {
            padding: 10px 20px;
            font-weight: 600;
            color: #495057;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.9rem;
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #5a6268);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(45deg, #5a6268, #495057);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108,117,125,0.3);
        }
        
        /* Dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-toggle {
            background: linear-gradient(45deg, #6c757d, #5a6268);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-radius: 10px;
            z-index: 1000;
            padding: 10px 0;
        }
        
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: #007bff;
            text-decoration: none;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #dee2e6;
            margin: 10px 0;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .text-danger:hover {
            color: #c82333 !important;
        }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 15px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        /* Empty State */
        .empty-state {
            padding: 40px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state p {
            font-size: 1.2rem;
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .process-steps {
                flex-direction: column;
                gap: 20px;
            }
            
            .step-arrow {
                transform: rotate(90deg);
                margin: 10px 0;
            }
            
            .process-step {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-icon {
                font-size: 2rem;
                width: 50px;
                height: 50px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .filter-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .filter-btn {
                width: 100%;
                max-width: 200px;
                justify-content: center;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn-sm {
                text-align: center;
                justify-content: center;
            }
            
            .customer-info {
                min-width: 150px;
            }
            
            .table th,
            .table td {
                padding: 10px;
                font-size: 0.9rem;
            }
        }

        /* Filter Container */
        .filter-container {
            margin-bottom: 30px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .filter-btn {
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            background: white;
            color: #666;
            border: 2px solid #e9ecef;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-btn:hover {
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-color: #007bff;
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .filter-btn.active:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,123,255,0.4);
        }
        
        .filter-note {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            color: #856404;
        }
    </style>
    
    <?php require_once "./view/footer.php"; ?>
</body>
</html>
