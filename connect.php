<?php
// Khai báo thông tin kết nối
$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname   = 'bookstore';

// Kết nối MySQL
$conn = mysqli_connect($hostname, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die('❌ Không thể kết nối đến CSDL: ' . mysqli_connect_error());
}

// Thiết lập bảng mã UTF-8
mysqli_set_charset($conn, 'utf8');

// Hàm lấy kết quả từ truy vấn (tùy chọn)
function getRes($result)
{
    $res = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $res[] = $row;
        }
    }
    return $res;
}
?>
