<?php 
require_once "connect.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// =============================================
// XỬ LÝ CẬP NHẬT - phải ở đầu file
// =============================================
if (isset($_POST["btn_submit"]) && $_POST["btn_submit"] == "Cập Nhật") {
    $update_success = true;
    foreach($_POST["qty"] as $key => $val) {
        $val = (int)$val;
        if ($val <= 0) {
            $_SESSION['update_error'] = 'Số lượng sản phẩm phải lớn hơn 0!';
            $update_success = false;
            break;
        }
        if ($val > $_SESSION['cart'][$key]['soluong_kho']) {
            $_SESSION['update_error'] = 'Số lượng sản phẩm ' . $_SESSION['cart'][$key]['tensp'] . ' vượt quá số lượng có sẵn trong kho!';
            $update_success = false;
            break;
        }
        $_SESSION['cart'][$key]['qty'] = $val;
    }
    if ($update_success) {
        header('location: addtocart.php?updated=1');
        exit();
    } else {
        header('location: addtocart.php?update_error=1');
        exit();
    }
}

// =============================================
// XỬ LÝ ĐẶT HÀNG - phải ở đầu file, trước HTML
// =============================================
if (isset($_POST["btn_submit"]) && $_POST["btn_submit"] == "Đặt Hàng") {
    
    $txt_tenkh    = isset($_POST["txt_tenkh"])    ? trim($_POST["txt_tenkh"])    : "";
    $txt_email    = isset($_POST["txt_email"])    ? trim($_POST["txt_email"])    : "";
    $txt_phone    = isset($_POST["txt_phone"])    ? trim($_POST["txt_phone"])    : "";
    $txt_address  = isset($_POST["txt_address"])  ? trim($_POST["txt_address"])  : "";
    $txt_giaohang = isset($_POST["txt_giaohang"]) ? trim($_POST["txt_giaohang"]) : "";
    $create_date  = isset($_POST["create_date"])  ? $_POST["create_date"]        : date('Y-m-d');

    if (empty($txt_tenkh)) {
        $_SESSION['order_error'] = "Vui lòng nhập tên khách hàng!";
    } elseif (empty($txt_email)) {
        $_SESSION['order_error'] = "Vui lòng nhập email!";
    } elseif (empty($txt_phone)) {
        $_SESSION['order_error'] = "Vui lòng nhập số điện thoại!";
    } elseif (empty($txt_address)) {
        $_SESSION['order_error'] = "Vui lòng nhập phương thức thanh toán!";
    } elseif (empty($txt_giaohang)) {
        $_SESSION['order_error'] = "Vui lòng nhập địa chỉ giao hàng!";
    } elseif (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
        $_SESSION['order_error'] = "Giỏ hàng trống!";
    } else {
        // Kiểm tra số lượng kho
        $can_order = true;
        $stock_error = "";
        foreach ($_SESSION['cart'] as $val) {
            $sql_check = "SELECT soluong FROM adproduct WHERE ma_sp = '" . mysqli_real_escape_string($conn, $val['ma_sp']) . "'";
            $result_check = mysqli_query($conn, $sql_check);
            $row_check = mysqli_fetch_assoc($result_check);
            $current_stock = $row_check ? $row_check['soluong'] : 0;
            if ($val['qty'] > $current_stock) {
                $can_order = false;
                $stock_error .= $val['tensp'] . " chỉ còn $current_stock sản phẩm. ";
            }
        }

        if (!$can_order) {
            $_SESSION['order_error'] = "Không thể đặt hàng: $stock_error";
        } else {
            mysqli_begin_transaction($conn);
            try {
                // Tính tổng tiền
                $tongtien = 0;
                foreach ($_SESSION['cart'] as $val) {
                    $gia_ban = $val['khuyenmai'] > 0 
                        ? $val['giaxuat'] * (1 - $val['khuyenmai'] / 100) 
                        : $val['giaxuat'];
                    $tongtien += $val['qty'] * $gia_ban;
                }

                $makh = "kh" . mt_rand(10000, 99999);
                $mahd = "hd" . mt_rand(10000, 99999);

                // 1. Lưu KHÁCH HÀNG trước
                $sql4 = "INSERT INTO customer(`makh`,`tenkh`,`phone`,`email`,`email_dangnhap`,`diachi_lienhe`,`diachi_giaohang`) 
                         VALUES ('$makh','$txt_tenkh','$txt_phone','$txt_email','$txt_email','$txt_address','$txt_giaohang')";
                if (!mysqli_query($conn, $sql4)) 
                    throw new Exception("Lỗi lưu khách hàng: " . mysqli_error($conn));

                // 2. Lưu ĐƠN HÀNG sau
                $sql3 = "INSERT INTO `order`(`mahd`,`makh`,`tenkh`,`tongtien`,`create_date`,`trangthai`) 
                         VALUES ('$mahd','$makh','$txt_tenkh','$tongtien','$create_date','0')";
                if (!mysqli_query($conn, $sql3)) 
                    throw new Exception("Lỗi tạo đơn hàng: " . mysqli_error($conn));

                // 3. Lưu CHI TIẾT đơn hàng
                foreach ($_SESSION['cart'] as $val1) {
                    $ma_sp    = mysqli_real_escape_string($conn, $val1['ma_sp']);
                    $tensp    = mysqli_real_escape_string($conn, $val1['tensp']);
                    $soluong  = (int)$val1['qty'];
                    $km       = (int)$val1['khuyenmai'];
                    $dongia   = $km > 0 
                        ? $val1['giaxuat'] * (1 - $km / 100) 
                        : $val1['giaxuat'];

                    $sql5 = "INSERT INTO `orderdetail`(`mahd`,`masp`,`tensp`,`soluong`,`dongia`,`khuyenmai`) 
                             VALUES ('$mahd','$ma_sp','$tensp','$soluong','$dongia','$km')";
                    if (!mysqli_query($conn, $sql5)) 
                        throw new Exception("Lỗi chi tiết đơn hàng: " . mysqli_error($conn));

                    $sql6 = "UPDATE adproduct SET soluong = soluong - $soluong WHERE ma_sp = '$ma_sp'";
                    if (!mysqli_query($conn, $sql6)) 
                        throw new Exception("Lỗi cập nhật kho: " . mysqli_error($conn));
                }

                mysqli_commit($conn);
                unset($_SESSION['cart']);
                $_SESSION['order_success'] = "Đặt hàng thành công! Mã đơn hàng: $mahd";
                header('location: index.php');
                exit();

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $_SESSION['order_error'] = "Lỗi khi đặt hàng: " . $e->getMessage();
            }
        }
    }
    header('location: addtocart.php');
    exit();
}

// =============================================
// XỬ LÝ THÊM SẢN PHẨM VÀO GIỎ
// =============================================
$id = isset($_GET["id"]) ? $_GET["id"] : "";

if (!isset($_SESSION['Username'])) {
    $error_message = 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!';
} elseif (!isset($_SESSION['quyen']) || $_SESSION['quyen'] != '0') {
    $error_message = 'Chỉ khách hàng mới được thêm sản phẩm vào giỏ hàng!';
} elseif ($id != "" && !isset($_GET['added']) && !isset($_GET['updated']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM adproduct WHERE ma_sp = '$id'";
    $rel = mysqli_query($conn, $sql);
    if (mysqli_num_rows($rel) > 0) {
        $r = mysqli_fetch_assoc($rel);
        $available_qty = $r['soluong'];
        $current_cart_qty = isset($_SESSION['cart'][$id]['qty']) ? $_SESSION['cart'][$id]['qty'] : 0;

        if ($current_cart_qty < $available_qty) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty'] += 1;
            } else {
                $_SESSION['cart'][$id]['qty'] = 1;
            }
            $_SESSION['cart'][$id]['ma_sp']      = $r['ma_sp'];
            $_SESSION['cart'][$id]['tensp']       = $r['tensp'];
            $_SESSION['cart'][$id]['giaxuat']     = $r['giaxuat'];
            $_SESSION['cart'][$id]['khuyenmai']   = $r['khuyenmai'];
            $_SESSION['cart'][$id]['soluong_kho'] = $r['soluong'];
            header('location: addtocart.php?added=1');
            exit();
        } else {
            $error_message = 'Không thể thêm! Số lượng trong kho không đủ. (Có sẵn: ' . $available_qty . ')';
        }
    } else {
        $error_message = 'Không tìm thấy sản phẩm với mã: ' . $id;
    }
}

require_once "./view/header.php"; 
?>
<link rel="stylesheet" href="public/cart.css">
<body>
<?php require_once 'view/header1.php'; ?>

<?php if(isset($error_message)): ?>
  <script>alert('<?php echo addslashes($error_message); ?>');</script>
<?php endif; ?>
<?php if(isset($_SESSION['order_error'])): ?>
  <script>alert('<?php echo addslashes($_SESSION['order_error']); ?>');</script>
  <?php unset($_SESSION['order_error']); ?>
<?php endif; ?>
<?php if(isset($_SESSION['update_error'])): ?>
  <script>alert('<?php echo addslashes($_SESSION['update_error']); ?>');</script>
  <?php unset($_SESSION['update_error']); ?>
<?php endif; ?>
<?php if(isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
  <script>
    alert('Cập nhật giỏ hàng thành công!');
    window.history.replaceState({}, document.title, window.location.pathname);
  </script>
<?php endif; ?>
<?php if(isset($_GET['added']) && $_GET['added'] == '1'): ?>
  <script>
    alert('Đã thêm sản phẩm vào giỏ hàng thành công!');
    window.history.replaceState({}, document.title, window.location.pathname);
  </script>
<?php endif; ?>

<div class="cart-container">
<?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
  <form action="" method="post">
    <table class="cart-table">
      <tr><th colspan="9">Danh sách sản phẩm của giỏ hàng</th></tr>
      <tr>
        <th width="40">STT</th>
        <th width="118">Mã sản phẩm</th>
        <th width="87">Tên sản phẩm</th>
        <th width="158">Số lượng</th>
        <th width="81">Giá gốc</th>
        <th width="119">Giá khuyến mãi</th>
        <th width="141">Thành tiền</th>
        <th width="102">Có sẵn</th>
        <th width="102">Xóa</th>
      </tr>
      <?php $i = 1; $tongtien = 0;
      foreach($_SESSION["cart"] as $k => $v):
          $i++;
          $gia_ban = $v['khuyenmai'] > 0 ? $v['giaxuat'] * (1 - $v['khuyenmai'] / 100) : $v['giaxuat'];
          $tt = $v['qty'] * $gia_ban;
          $tongtien += $tt;
      ?>
      <tr>
        <td><?php echo $i; ?></td>
        <td><?php echo htmlspecialchars($v['ma_sp']); ?></td>
        <td><?php echo htmlspecialchars($v['tensp']); ?></td>
        <td>
          <input type="number" class="quantity-input" min="1" max="<?php echo $v['soluong_kho']; ?>"
                 value="<?php echo $v['qty']; ?>" name="qty[<?php echo $k ?>]"
                 data-product-id="<?php echo $k; ?>" onchange="updateCartItem(this)" />
          <div class="update-status" id="status-<?php echo $k; ?>"></div>
        </td>
        <td class="price"><?php echo number_format($v['giaxuat']); ?> VNĐ</td>
        <td class="discount"><?php echo $v['khuyenmai'] > 0 ? $v['khuyenmai'] . '%' : 'Không có'; ?></td>
        <td class="price item-total" id="total-<?php echo $k; ?>"><?php echo number_format($tt); ?> VNĐ</td>
        <td class="stock-info"><?php echo $v['soluong_kho']; ?></td>
        <td><a href="delete_addtocart.php?key=<?php echo $k ?>" class="btn-delete">Xóa</a></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td colspan="9" class="total-price" id="cart-total">
          Tổng tiền cần thanh toán là: <?php echo number_format($tongtien); ?> VNĐ
        </td>
      </tr>
      <tr>
        <td colspan="9">
          <input name="btn_submit" type="submit" value="Cập Nhật" class="btn btn-update" />
          <input name="btn_submit" type="submit" value="Đặt Hàng" class="btn btn-order" onclick="return confirm('Bạn có chắc chắn muốn đặt hàng?')" />
        </td>
      </tr>
    </table>

    <div class="form-header">
      <h3>📋 Thông tin đặt hàng</h3>
      <p style="margin:10px 0 0 0; opacity:0.9; font-size:14px;">Thông tin từ tài khoản đã được tự động điền</p>
    </div>
    <table class="customer-form">
      <tr>
        <td>Tên khách hàng</td>
        <td><input name="txt_tenkh" type="text" value="<?php echo htmlspecialchars($_SESSION['Fullname'] ?? ''); ?>" required /></td>
      </tr>
      <tr>
        <td>Email</td>
        <td>
          <input name="txt_email" type="email" value="<?php echo htmlspecialchars($_SESSION['Email'] ?? ''); ?>" readonly style="background:#f8f9fa;color:#666;" />
          <small style="color:#666;font-size:12px;">(Email từ tài khoản đã đăng nhập)</small>
        </td>
      </tr>
      <tr>
        <td>Phone</td>
        <td><input name="txt_phone" type="text" placeholder="Nhập số điện thoại" required /></td>
      </tr>
      <tr>
        <td>Thanh toán bằng</td>
        <td><input name="txt_address" type="text" placeholder="Ví dụ: Tiền mặt, Chuyển khoản, VNPay..." required /></td>
      </tr>
      <tr>
        <td>Địa chỉ giao hàng</td>
        <td><input name="txt_giaohang" type="text" placeholder="Nhập địa chỉ giao hàng chi tiết" required /></td>
      </tr>
      <tr>
        <td>Ngày đặt hàng</td>
        <td><input name="create_date" type="date" value="<?php echo date('Y-m-d'); ?>" required /></td>
      </tr>
    </table>
  </form>

<?php else: ?>
  <div class="empty-cart">
    <h3>Giỏ hàng trống</h3>
    <a href="index.php">Tiếp tục mua sắm</a>
  </div>
<?php endif; ?>
</div>

<?php require_once "./view/footer.php"; ?>

<style>
.customer-form { width:100%; border-collapse:collapse; margin-top:20px; background:white; border-radius:10px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
.customer-form tr { border-bottom:1px solid #f1f3f4; }
.customer-form td { padding:15px; vertical-align:middle; }
.customer-form td:first-child { background:#f8f9fa; font-weight:600; color:#333; width:200px; }
.customer-form input { width:100%; padding:12px; border:2px solid #e9ecef; border-radius:8px; font-size:14px; box-sizing:border-box; transition:all 0.3s ease; }
.customer-form input:focus { outline:none; border-color:#007bff; box-shadow:0 0 0 3px rgba(0,123,255,0.1); }
.customer-form input[readonly] { background:#f8f9fa; color:#666; cursor:not-allowed; }
.form-header { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:20px; text-align:center; border-radius:10px 10px 0 0; }
.form-header h3 { margin:0; font-size:1.5rem; }
.quantity-input { transition:all 0.3s ease; }
.quantity-input:focus { border-color:#007bff; box-shadow:0 0 0 2px rgba(0,123,255,0.25); }
</style>

<script>
function updateCartItem(input) {
  const productId = input.getAttribute('data-product-id');
  const newQuantity = parseInt(input.value);
  fetch('update_cart_ajax.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(newQuantity)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const itemTotal = document.getElementById('total-' + productId);
      if (itemTotal) itemTotal.textContent = data.item_total + ' VNĐ';
      const cartTotal = document.getElementById('cart-total');
      if (cartTotal) cartTotal.textContent = 'Tổng tiền cần thanh toán là: ' + data.cart_total + ' VNĐ';
    } else {
      input.value = data.old_quantity;
      alert(data.message || 'Cập nhật thất bại!');
    }
  })
  .catch(err => console.error('Error:', err));
}

let updateTimeout;
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('input', function() {
      clearTimeout(updateTimeout);
      updateTimeout = setTimeout(() => updateCartItem(this), 500);
    });
  });
});

function confirmOrder() {
  return confirm('Bạn có chắc chắn muốn đặt hàng?');
}
</script>
</body>
</html>