<?php 
require_once "view/header.php";
require_once "connect.php";
require_once "view/header1.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['Username']) || !isset($_SESSION['quyen']) || $_SESSION['quyen'] != '0') {
    header("Location: login.php");
    exit();
}

// Lấy thông tin khách hàng
$username = $_SESSION['Username'];
$sql_customer = "SELECT * FROM dangkithanhvien WHERE Username = '$username'";
$result_customer = mysqli_query($conn, $sql_customer);
$customer = mysqli_fetch_assoc($result_customer);

if (!$customer) {
    echo "<div class='alert alert-danger'>Không tìm thấy thông tin khách hàng!</div>";
    require_once "./view/footer.php";
    exit();
}

$email = $customer['Email'];

// Lấy tất cả đơn hàng của khách hàng dựa trên email
$sql_orders = "SELECT o.*, c.tenkh, c.phone, c.email 
               FROM `order` o 
               LEFT JOIN customer c ON o.makh = c.makh 
               WHERE c.email = '$email' 
               ORDER BY o.create_date DESC";
$result_orders = mysqli_query($conn, $sql_orders);

// Thử tìm đơn hàng bằng username nếu không tìm thấy bằng email
if(mysqli_num_rows($result_orders) == 0) {
    $sql_orders_username = "SELECT o.*, c.tenkh, c.phone, c.email 
                           FROM `order` o 
                           LEFT JOIN customer c ON o.makh = c.makh 
                           WHERE c.email_dangnhap = '$email' 
                           ORDER BY o.create_date DESC";
    $result_orders = mysqli_query($conn, $sql_orders_username);
}

// Thử tìm đơn hàng bằng tên khách hàng nếu vẫn không tìm thấy
if(mysqli_num_rows($result_orders) == 0) {
    $fullname = $customer['Fullname'];
    $sql_orders_name = "SELECT o.*, c.tenkh, c.phone, c.email 
                        FROM `order` o 
                        LEFT JOIN customer c ON o.makh = c.makh 
                        WHERE c.tenkh = '$fullname' 
                        ORDER BY o.create_date DESC";
    $result_orders = mysqli_query($conn, $sql_orders_name);
}

// Phương pháp cuối cùng: Lấy tất cả đơn hàng và lọc theo username/email
if(mysqli_num_rows($result_orders) == 0) {
    $sql_orders_all = "SELECT o.*, c.tenkh, c.phone, c.email 
                       FROM `order` o 
                       LEFT JOIN customer c ON o.makh = c.makh 
                       WHERE c.email LIKE '%$email%' 
                          OR c.email_dangnhap LIKE '%$email%'
                          OR c.tenkh LIKE '%" . $customer['Fullname'] . "%'
                       ORDER BY o.create_date DESC";
    $result_orders = mysqli_query($conn, $sql_orders_all);
}

// Lấy thông tin khách hàng từ bảng customer (lấy record đầu tiên)
$sql_customer_info = "SELECT * FROM customer WHERE email = '$email' LIMIT 1";
$result_customer_info = mysqli_query($conn, $sql_customer_info);
$customer_info = mysqli_fetch_assoc($result_customer_info);
?>

<div class="main">
    <div class="container">
        <h1 class="page-title">📦 Đơn hàng của tôi</h1>
        
        <div class="customer-info">
            <div class="info-card">
                <h3>👤 Thông tin khách hàng</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Tên:</strong> <?php echo htmlspecialchars($customer['Fullname']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Username:</strong> <?php echo htmlspecialchars($customer['Username']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong> <?php echo htmlspecialchars($customer['Email']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($customer['Diachi']); ?>
                    </div>
                    <?php if($customer_info): ?>
                    <div class="info-item">
                        <strong>SĐT:</strong> <?php echo htmlspecialchars($customer_info['phone']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($customer_info['diachi_giaohang']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="orders-section">
            <?php 
            $total_orders = mysqli_num_rows($result_orders);
            if($total_orders > 0): 
            ?>
                <div class="orders-grid">
                    <?php while($order = mysqli_fetch_assoc($result_orders)): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h4>Đơn hàng #<?php echo htmlspecialchars($order['mahd']); ?></h4>
                                    <p class="order-date">📅 Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['create_date'])); ?></p>
                                </div>
                                <div class="order-status">
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    $status_icon = '';
                                    
                                    switch($order['trangthai']) {
                                        case 0:
                                            $status_class = 'status-pending';
                                            $status_text = 'Chờ xử lý';
                                            $status_icon = '⏳';
                                            break;
                                        case 1:
                                            $status_class = 'status-shipping';
                                            $status_text = 'Đang giao hàng';
                                            $status_icon = '🚚';
                                            break;
                                        case 2:
                                            $status_class = 'status-completed';
                                            $status_text = 'Hoàn thành';
                                            $status_icon = '✅';
                                            break;
                                        default:
                                            $status_class = 'status-cancelled';
                                            $status_text = 'Đã hủy';
                                            $status_icon = '❌';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_icon; ?> <?php echo $status_text; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="order-details">
                                <?php
                                // Lấy chi tiết đơn hàng
                                $mahd = $order['mahd'];
                                $sql_details = "SELECT od.*, p.tensp, p.anhsp 
                                               FROM orderdetail od 
                                               JOIN adproduct p ON od.masp = p.ma_sp 
                                               WHERE od.mahd = '$mahd'";
                                $result_details = mysqli_query($conn, $sql_details);
                                $total_amount = 0;
                                ?>
                                
                                <div class="order-items">
                                    <?php while($item = mysqli_fetch_assoc($result_details)): ?>
                                        <div class="order-item">
                                            <div class="item-image">
                                                <img src="public/images/<?php echo htmlspecialchars($item['anhsp']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['tensp']); ?>" />
                                            </div>
                                            <div class="item-info">
                                                <h5><?php echo htmlspecialchars($item['tensp']); ?></h5>
                                                <p>Số lượng: <?php echo $item['soluong']; ?></p>
                                                <p>Đơn giá: <?php echo number_format($item['dongia']); ?> VNĐ</p>
                                            </div>
                                            <div class="item-total">
                                                <?php 
                                                $item_total = $item['soluong'] * $item['dongia'];
                                                $total_amount += $item_total;
                                                ?>
                                                <strong><?php echo number_format($item_total); ?> VNĐ</strong>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                
                                <div class="order-summary">
                                    <div class="total-amount">
                                        <strong>Tổng tiền: <?php echo number_format($total_amount); ?> VNĐ</strong>
                                    </div>
                                    <div class="order-actions">
                                        <a href="viewDonhang.php?id=<?php echo $order['mahd']; ?>" 
                                           class="btn btn-primary">Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-orders">
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <h3>Bạn chưa có đơn hàng nào</h3>
                        <p>Hãy mua sắm để tạo đơn hàng đầu tiên!</p>
                        <a href="index.php" class="btn btn-primary">Mua sắm ngay</a>
                    </div>
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

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.customer-info {
    margin-bottom: 40px;
}

.info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.info-card h3 {
    margin: 0 0 20px 0;
    font-size: 1.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.info-item:last-child {
    border-bottom: none;
}

/* .orders-section {
    margin-top: 30px;
} */

.orders-grid {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.order-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.order-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #dee2e6;
}

.order-info h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1.3rem;
}

.order-date {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-shipping {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-completed {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.order-details {
    padding: 20px;
}

.order-items {
    margin-bottom: 20px;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f1f3f4;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    margin-right: 15px;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-info {
    flex: 1;
}

.item-info h5 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1rem;
}

.item-info p {
    margin: 2px 0;
    color: #666;
    font-size: 0.9rem;
}

.item-total {
    font-size: 1.1rem;
    color: #28a745;
    font-weight: 600;
}

.order-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
}

.total-amount {
    font-size: 1.2rem;
    color: #333;
}

.order-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
}

.empty-state {
    max-width: 400px;
    margin: 0 auto;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .order-summary {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .item-image {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .item-total {
        align-self: flex-end;
    }
}
</style>

<?php require_once "./view/footer.php"; ?> 