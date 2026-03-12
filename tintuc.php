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

// Xử lý thông báo
if(isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if(isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Xử lý thêm tin tức mới
if(isset($_POST["add_news"])) {
    $tieude = trim($_POST["tieude"]);
    $noidung = trim($_POST["noidung"]);
    
    if(empty($tieude) || empty($noidung)) {
        $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin";
    } else {
        // Xử lý upload hình ảnh
        $hinhanh = NULL;
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
        
        $sql = "INSERT INTO tintuc (tieude, noidung, hinhanh) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $tieude, $noidung, $hinhanh);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Thêm tin tức thành công!";
            header('location: tintuc.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Lỗi khi thêm tin tức";
        }
    }
}

// Lấy danh sách tin tức
$sql = "SELECT * FROM tintuc ORDER BY ngaytao DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="main">
    <div class="page-header">
        <h1>📰 Quản lý Tin tức</h1>
        <p>Thêm, sửa, xóa tin tức cho website</p>
    </div>
    
    <div class="content-wrapper">
        <!-- Form thêm tin tức -->
        <div class="form-container">
            <h3 class="section-title">➕ Thêm tin tức mới</h3>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tieude">Tiêu đề <span class="required">*</span></label>
                        <input type="text" id="tieude" name="tieude" 
                               placeholder="Nhập tiêu đề tin tức"
                               required />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="noidung">Nội dung <span class="required">*</span></label>
                    <textarea id="noidung" name="noidung" rows="6" 
                              placeholder="Nhập nội dung tin tức"
                              required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="hinhanh">Hình ảnh (tùy chọn)</label>
                    <div class="file-upload">
                        <input type="file" id="hinhanh" name="hinhanh" accept="image/*" />
                        <div class="file-info">
                            <span class="file-icon">📁</span>
                            <span class="file-text">Chọn hình ảnh cho tin tức</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="add_news" class="btn btn-primary">
                        <span class="btn-icon">➕</span>
                        Thêm tin tức
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Danh sách tin tức -->
        <div class="list-container">
            <h3 class="section-title">📋 Danh sách tin tức</h3>
            
            <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="news-grid">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="news-card">
                    <?php if(!empty($row['hinhanh'])): ?>
                    <div class="news-image">
                        <img src="public/images/<?php echo htmlspecialchars($row['hinhanh']); ?>" 
                             alt="<?php echo htmlspecialchars($row['tieude']); ?>" />
                    </div>
                    <?php endif; ?>
                    
                    <div class="news-content">
                        <h4 class="news-title"><?php echo htmlspecialchars($row['tieude']); ?></h4>
                        <p class="news-excerpt">
                            <?php 
                            $excerpt = substr($row['noidung'], 0, 150);
                            echo htmlspecialchars($excerpt) . (strlen($row['noidung']) > 150 ? '...' : '');
                            ?>
                        </p>
                        <div class="news-meta">
                            <span class="news-date">📅 <?php echo date('d/m/Y H:i', strtotime($row['ngaytao'])); ?></span>
                            <span class="news-status <?php echo $row['trangthai'] ? 'active' : 'inactive'; ?>">
                                <?php echo $row['trangthai'] ? '✅ Hiển thị' : '❌ Ẩn'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="news-actions">
                        <a href="edit_tintuc.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">
                            <span class="btn-icon">✏️</span>
                            Sửa
                        </a>
                        <a href="delete_tintuc.php?id=<?php echo $row['id']; ?>" 
                           class="btn btn-delete"
                           onclick="return confirm('Bạn có chắc muốn xóa tin tức này?')">
                            <span class="btn-icon">🗑️</span>
                            Xóa
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <p>📰 Chưa có tin tức nào</p>
            </div>
            <?php endif; ?>
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
        max-width: 1600px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .form-container, .list-container {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 25px;
        color: #333;
        border-bottom: 2px solid #f1f3f4;
        padding-bottom: 10px;
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
        min-height: 120px;
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
    
    .btn-icon {
        font-size: 16px;
    }
    
    /* News grid */
    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
    }
    
    .news-card {
    display: flex;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e1e5e9;
    flex-direction: column;
    justify-content: flex-end;
    flex-wrap: nowrap;
    align-items: stretch;
}
    
    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .news-image {
        height: 200px;
        overflow: hidden;
    }
    
    .news-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .news-card:hover .news-image img {
        transform: scale(1.05);
    }
    
    .news-content {
        padding: 20px;
    }
    
    .news-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0 0 10px 0;
        color: #333;
        line-height: 1.4;
    }
    
    .news-excerpt {
        color: #666;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .news-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        color: #888;
        margin-bottom: 15px;
    }
    
    .news-status {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .news-status.active {
        background: #d4edda;
        color: #155724;
    }
    
    .news-status.inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .news-actions {
        display: flex;
        gap: 10px;
        padding: 0 20px 20px 20px;
    }
    
    .btn-edit {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        flex: 1;
        justify-content: center;
    }
    
    .btn-edit:hover {
        background: linear-gradient(45deg, #20c997, #17a2b8);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        color: white;
        text-decoration: none;
    }
    
    .btn-delete {
        background: linear-gradient(45deg, #dc3545, #c82333);
        color: white;
        flex: 1;
        justify-content: center;
    }
    
    .btn-delete:hover {
        background: linear-gradient(45deg, #c82333, #bd2130);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        color: white;
        text-decoration: none;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
        font-size: 1.1rem;
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
        
        .form-container, .list-container {
            padding: 20px;
            margin: 0 15px 20px 15px;
        }
        
        .news-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .news-actions {
            flex-direction: column;
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