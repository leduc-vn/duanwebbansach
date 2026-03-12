<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "connect.php";

// Chỉ cho phép POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['Username'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Kiểm tra quyền - chỉ khách hàng mới được cập nhật giỏ hàng
if (!isset($_SESSION['quyen']) || $_SESSION['quyen'] != '0') {
    echo json_encode(['success' => false, 'message' => 'Chỉ khách hàng mới được cập nhật giỏ hàng']);
    exit;
}

// Lấy dữ liệu từ request
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
$new_quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

// Kiểm tra dữ liệu đầu vào
if (empty($product_id) || $new_quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Kiểm tra sản phẩm có tồn tại trong giỏ hàng không
if (!isset($_SESSION['cart'][$product_id])) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
    exit;
}

// Lấy thông tin sản phẩm từ database
$sql = "SELECT * FROM adproduct WHERE ma_sp = '" . mysqli_real_escape_string($conn, $product_id) . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}

$product = mysqli_fetch_assoc($result);
$available_qty = $product['soluong'];
$old_quantity = $_SESSION['cart'][$product_id]['qty'];

// Kiểm tra số lượng không vượt quá kho
if ($new_quantity > $available_qty) {
    echo json_encode([
        'success' => false, 
        'message' => 'Số lượng vượt quá số lượng có sẵn trong kho (' . $available_qty . ' sản phẩm)',
        'old_quantity' => $old_quantity
    ]);
    exit;
}

// Cập nhật số lượng trong giỏ hàng
$_SESSION['cart'][$product_id]['qty'] = $new_quantity;

// Tính toán tổng tiền sản phẩm
if($product['khuyenmai'] > 0) {
    $gia_ban = $product['giaxuat'] * (1 - $product['khuyenmai'] / 100);
} else {
    $gia_ban = $product['giaxuat'];
}
$item_total = $new_quantity * $gia_ban;

// Tính toán tổng tiền giỏ hàng
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    if($item['khuyenmai'] > 0) {
        $gia_ban_item = $item['giaxuat'] * (1 - $item['khuyenmai'] / 100);
    } else {
        $gia_ban_item = $item['giaxuat'];
    }
    $cart_total += $item['qty'] * $gia_ban_item;
}

// Trả về kết quả
echo json_encode([
    'success' => true,
    'message' => 'Cập nhật thành công',
    'item_total' => number_format($item_total),
    'cart_total' => number_format($cart_total),
    'old_quantity' => $old_quantity
]);
?> 