<?php
require_once("connect.php");

// Kiểm tra quyền admin
if(!isset($_SESSION['quyen']) || $_SESSION['quyen'] != '1') {
    header('location: index.php');
    exit();
}

// Kiểm tra ID tin tức
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tin tức không hợp lệ";
    header('location: tintuc.php');
    exit();
}

$id = $_GET['id'];

// Lấy thông tin tin tức để xóa hình ảnh
$sql = "SELECT hinhanh FROM tintuc WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);

if($news) {
    // Xóa tin tức
    $sql_delete = "DELETE FROM tintuc WHERE id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id);
    
    if(mysqli_stmt_execute($stmt_delete)) {
        // Xóa hình ảnh nếu có
        if(!empty($news['hinhanh'])) {
            $image_path = "public/images/" . $news['hinhanh'];
            if(file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success_message'] = "Xóa tin tức thành công!";
    } else {
        $_SESSION['error_message'] = "Lỗi khi xóa tin tức";
    }
} else {
    $_SESSION['error_message'] = "Không tìm thấy tin tức";
}

header('location: tintuc.php');
exit();
?> 