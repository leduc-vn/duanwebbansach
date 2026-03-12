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
// Lấy danh sách tin tức đang hiển thị
$sql = "SELECT * FROM tintuc WHERE trangthai = 1 ORDER BY ngaytao DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="main">
    <div class="page-header">
        <h1>📰 Tin tức & Thông báo</h1>
        <p>Cập nhật những thông tin mới nhất về sản phẩm và khuyến mãi</p>
    </div>
    
    <div class="content-wrapper">
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
                    <h3 class="news-title"><?php echo htmlspecialchars($row['tieude']); ?></h3>
                    <p class="news-excerpt">
                        <?php 
                        $excerpt = substr($row['noidung'], 0, 200);
                        echo htmlspecialchars($excerpt) . (strlen($row['noidung']) > 200 ? '...' : '');
                        ?>
                    </p>
                    <div class="news-meta">
                        <span class="news-date">📅 <?php echo date('d/m/Y H:i', strtotime($row['ngaytao'])); ?></span>
                    </div>
                    <div class="news-actions">
                        <a href="view_tintuc_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-read">
                            <span class="btn-icon">📖</span>
                            Đọc chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📰</div>
            <h3>Chưa có tin tức nào</h3>
            <p>Hãy quay lại sau để xem những tin tức mới nhất!</p>
        </div>
        <?php endif; ?>
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
    
    .page-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0 0 15px 0;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    
    /* News grid */
    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
    }
    
    .news-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 1px solid #e1e5e9;
    }
    
    .news-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .news-image {
        height: 220px;
        overflow: hidden;
    }
    
    .news-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .news-card:hover .news-image img {
        transform: scale(1.08);
    }
    
    .news-content {
        padding: 25px;
    }
    
    .news-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin: 0 0 15px 0;
        color: #333;
        line-height: 1.4;
    }
    
    .news-excerpt {
        color: #666;
        line-height: 1.7;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }
    
    .news-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        color: #888;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .news-actions {
        display: flex;
        justify-content: center;
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
    
    .btn-read {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        width: 100%;
        justify-content: center;
    }
    
    .btn-read:hover {
        background: linear-gradient(45deg, #0056b3, #004085);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        color: white;
        text-decoration: none;
    }
    
    .btn-icon {
        font-size: 16px;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #666;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .empty-state h3 {
        margin: 0 0 10px 0;
        font-size: 1.5rem;
        color: #333;
    }
    
    .empty-state p {
        margin: 0;
        font-size: 1.1rem;
        opacity: 0.8;
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
        
        .news-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .news-content {
            padding: 20px;
        }
        
        .news-title {
            font-size: 1.2rem;
        }
    }
</style>

<?php include('./view/footer.php'); ?> 