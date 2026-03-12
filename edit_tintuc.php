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

// Kiểm tra ID tin tức
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: tintuc.php');
    exit();
}

$id = $_GET['id'];

// Lấy thông tin tin tức
$sql = "SELECT * FROM tintuc WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);

if(!$news) {
    $_SESSION['error_message'] = "Không tìm thấy tin tức";
    header('location: tintuc.php');
    exit();
}

// Xử lý thông báo
if(isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if(isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Xử lý cập nhật tin tức
if(isset($_POST["update_news"])) {
    $tieude = trim($_POST["tieude"]);
    $noidung = trim($_POST["noidung"]);
    $trangthai = isset($_POST["trangthai"]) ? 1 : 0;
    
    if(empty($tieude) || empty($noidung)) {
        $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin";
    } else {
        // Xử lý upload hình ảnh mới
        $hinhanh = $news['hinhanh']; // Giữ ảnh cũ nếu không upload mới
        if(isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0) {
            $file_name = $_FILES['hinhanh']['name'];
            $file_tmp = $_FILES['hinhanh']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = array('jpg', 'jpeg', 'png', 'gif');
            
            if(in_array($file_ext, $allowed_exts)) {
                $new_file_name = time() . '_' . $file_name;
                if(move_uploaded_file($file_tmp, "public/images/" . $new_file_name)) {
                    $hinhanh = $new_file_name;
                }
            }
        }
        
        $sql_update = "UPDATE tintuc SET tieude=?, noidung=?, hinhanh=?, trangthai=? WHERE id=?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sssii", $tieude, $noidung, $hinhanh, $trangthai, $id);
        
        if(mysqli_stmt_execute($stmt_update)) {
            $_SESSION['success_message'] = "Cập nhật tin tức thành công!";
            header('location: tintuc.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Lỗi khi cập nhật tin tức";
        }
    }
}
?>

<div class="main">
    <div class="page-header">
        <h1>✏️ Sửa Tin tức</h1>
        <p>Cập nhật thông tin tin tức</p>
    </div>
    
    <div class="content-wrapper">
        <div class="form-container">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tieude">Tiêu đề <span class="required">*</span></label>
                        <input type="text" id="tieude" name="tieude" 
                               placeholder="Nhập tiêu đề tin tức"
                               value="<?php echo htmlspecialchars($news['tieude']); ?>"
                               required />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="noidung">Nội dung <span class="required">*</span></label>
                    <textarea id="noidung" name="noidung" rows="8" 
                              placeholder="Nhập nội dung tin tức"
                              required><?php echo htmlspecialchars($news['noidung']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="hinhanh">Hình ảnh mới (tùy chọn)</label>
                        <div class="file-upload">
                            <input type="file" id="hinhanh" name="hinhanh" accept="image/*" />
                            <div class="file-info">
                                <span class="file-icon">📁</span>
                                <span class="file-text">Chọn hình ảnh mới (để trống nếu giữ ảnh cũ)</span>
                            </div>
                        </div>
                        <?php if(!empty($news['hinhanh'])): ?>
                        <div class="current-image">
                            <p>Ảnh hiện tại:</p>
                            <img src="public/images/<?php echo htmlspecialchars($news['hinhanh']); ?>" 
                                 alt="Ảnh tin tức" class="news-image-preview" />
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="trangthai" value="1" 
                                   <?php echo $news['trangthai'] ? 'checked' : ''; ?> />
                            <span class="checkbox-custom"></span>
                            Hiển thị tin tức
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_news" class="btn btn-primary">
                        <span class="btn-icon">💾</span>
                        Cập nhật tin tức
                    </button>
                    <a href="tintuc.php" class="btn btn-secondary">
                        <span class="btn-icon">⬅️</span>
                        Quay lại
                    </a>
                </div>
            </form>
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
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .form-container {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    
    .form-group input:focus, .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 200px;
    }
    
    .required {
        color: #dc3545;
    }
    
    /* File upload */
    .file-upload {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .file-upload input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px;
        border: 2px dashed #ddd;
        border-radius: 10px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .file-upload:hover .file-info {
        border-color: #007bff;
        background: #e3f2fd;
    }
    
    .file-icon {
        font-size: 20px;
    }
    
    .file-text {
        color: #666;
        font-weight: 500;
    }
    
    /* Current image */
    .current-image {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e1e5e9;
    }
    
    .current-image p {
        margin: 0 0 10px 0;
        font-weight: 600;
        color: #333;
    }
    
    .news-image-preview {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid #e1e5e9;
    }
    
    /* Checkbox */
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: 500;
        color: #333;
    }
    
    .checkbox-label input[type="checkbox"] {
        display: none;
    }
    
    .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #ddd;
        border-radius: 4px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .checkbox-label input[type="checkbox"]:checked + .checkbox-custom {
        border-color: #007bff;
        background: #007bff;
    }
    
    .checkbox-label input[type="checkbox"]:checked + .checkbox-custom::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
    
    /* Form actions */
    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
    }
    
    .btn {
        padding: 12px 25px;
        border-radius: 25px;
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
    
    .btn-secondary {
        background: linear-gradient(45deg, #6c757d, #545b62);
        color: white;
    }
    
    .btn-secondary:hover {
        background: linear-gradient(45deg, #545b62, #495057);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108,117,125,0.3);
        color: white;
        text-decoration: none;
    }
    
    .btn-icon {
        font-size: 16px;
    }
    
    /* Alert messages */
    .alert {
        padding: 15px 20px;
        margin: 20px;
        border-radius: 10px;
        font-weight: 500;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
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
        
        .form-container {
            padding: 20px;
            margin: 0 15px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            justify-content: center;
            width: 100%;
        }
    }
</style>

<?php include('./view/footer.php'); ?> 