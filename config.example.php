<?php
// Copy file này thành config.php và điền thông tin thật vào
define('DB_HOST', 'localhost');
define('DB_NAME', 'ten_database');
define('DB_USER', 'ten_user');
define('DB_PASS', 'mat_khau');
define('DB_CHARSET', 'utf8mb4');

// Kết nối
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset(DB_CHARSET);
?>