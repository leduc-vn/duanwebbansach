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
// Kiểm tra ID tin tức
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: view_tintuc.php');
    exit();
}

$id = $_GET['id'];

// Lấy thông tin tin tức
$sql = "SELECT * FROM tintuc WHERE id = ? AND trangthai = 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);

if(!$news) {
    header('location: view_tintuc.php');
    exit();
}

// Lấy tin tức liên quan (3 tin tức khác)
$sql_related = "SELECT * FROM tintuc WHERE id != ? AND trangthai = 1 ORDER BY ngaytao DESC LIMIT 3";
$stmt_related = mysqli_prepare($conn, $sql_related);
mysqli_stmt_bind_param($stmt_related, "i", $id);
mysqli_stmt_execute($stmt_related);
$result_related = mysqli_stmt_get_result($stmt_related);
?>

<div class="main">
    <div class="content-wrapper">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Trang chủ</a>
            <span class="separator">›</span>
            <a href="view_tintuc.php">Tin tức</a>
            <span class="separator">›</span>
            <span class="current"><?php echo htmlspecialchars($news['tieude']); ?></span>
        </div>
        
        <!-- Article -->
        <div class="article-container">
            <article class="news-article">
                <header class="article-header">
                    <h1 class="article-title"><?php echo htmlspecialchars($news['tieude']); ?></h1>
                    <div class="article-meta">
                        <span class="article-date">📅 <?php echo date('d/m/Y H:i', strtotime($news['ngaytao'])); ?></span>
                    </div>
                </header>
                
                <?php if(!empty($news['hinhanh'])): ?>
                <div class="article-image">
                    <img src="public/images/<?php echo htmlspecialchars($news['hinhanh']); ?>" 
                         alt="<?php echo htmlspecialchars($news['tieude']); ?>" />
                </div>
                <?php endif; ?>
                
                <div class="article-content">
                    <?php 
                    // Chuyển đổi xuống dòng thành <br> để hiển thị đúng
                    $content = nl2br(htmlspecialchars($news['noidung']));
                    echo $content;
                    ?>
                </div>
            </article>
        </div>
        
        <!-- Related News -->
        <?php if(mysqli_num_rows($result_related) > 0): ?>
        <div class="related-news">
            <h3 class="section-title">📰 Tin tức liên quan</h3>
            <div class="related-grid">
                <?php while($related = mysqli_fetch_assoc($result_related)): ?>
                <div class="related-card">
                    <?php if(!empty($related['hinhanh'])): ?>
                    <div class="related-image">
                        <img src="public/images/<?php echo htmlspecialchars($related['hinhanh']); ?>" 
                             alt="<?php echo htmlspecialchars($related['tieude']); ?>" />
                    </div>
                    <?php endif; ?>
                    
                    <div class="related-content">
                        <h4 class="related-title"><?php echo htmlspecialchars($related['tieude']); ?></h4>
                        <p class="related-excerpt">
                            <?php 
                            $excerpt = substr($related['noidung'], 0, 100);
                            echo htmlspecialchars($excerpt) . (strlen($related['noidung']) > 100 ? '...' : '');
                            ?>
                        </p>
                        <div class="related-meta">
                            <span class="related-date">📅 <?php echo date('d/m/Y', strtotime($related['ngaytao'])); ?></span>
                        </div>
                        <a href="view_tintuc_detail.php?id=<?php echo $related['id']; ?>" class="btn btn-read-more">
                            Đọc thêm
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Back to News -->
        <div class="back-to-news">
            <a href="view_tintuc.php" class="btn btn-back">
                <span class="btn-icon">⬅️</span>
                Quay lại danh sách tin tức
            </a>
        </div>
    </div>
</div>

<style>
    .content-wrapper {
        max-width: 1600px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Breadcrumb */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 30px;
        padding: 15px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        font-size: 0.9rem;
    }
    
    .breadcrumb a {
        color: #007bff;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .breadcrumb a:hover {
        color: #0056b3;
    }
    
    .breadcrumb .separator {
        color: #ccc;
    }
    
    .breadcrumb .current {
        color: #666;
        font-weight: 500;
    }
    
    /* Article */
    .article-container {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        margin-bottom: 40px;
    }
    
    .news-article {
        padding: 0;
    }
    
    .article-header {
        padding: 40px 40px 20px 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .article-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 15px 0;
        line-height: 1.3;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .article-meta {
        font-size: 1rem;
        opacity: 0.9;
    }
    
    .article-image {
        width: 100%;
        max-height: 400px;
        overflow: hidden;
    }
    
    .article-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .article-content {
        padding: 40px;
        line-height: 1.8;
        font-size: 1.1rem;
        color: #333;
    }
    
    .article-content p {
        margin-bottom: 20px;
    }
    
    /* Related News */
    .related-news {
        margin-bottom: 40px;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 25px;
        color: #333;
        border-bottom: 2px solid #f1f3f4;
        padding-bottom: 10px;
    }
    
    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }
    
    .related-card {
    display: flex;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e1e5e9;
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-end;
    flex-wrap: nowrap;
}
    
    .related-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .related-image {
        height: 180px;
        overflow: hidden;
    }
    
    .related-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .related-card:hover .related-image img {
        transform: scale(1.05);
    }
    
    .related-content {
        padding: 20px;
    }
    
    .related-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0 0 10px 0;
        color: #333;
        line-height: 1.4;
    }
    
    .related-excerpt {
        color: #666;
        line-height: 1.6;
        margin-bottom: 15px;
        font-size: 0.9rem;
    }
    
    .related-meta {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 15px;
    }
    
    .btn-read-more {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .btn-read-more:hover {
        background: linear-gradient(45deg, #0056b3, #004085);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        color: white;
        text-decoration: none;
    }
    
    /* Back to News */
    .back-to-news {
        text-align: center;
        margin-top: 40px;
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
    
    .btn-back {
        background: linear-gradient(45deg, #6c757d, #545b62);
        color: white;
    }
    
    .btn-back:hover {
        background: linear-gradient(45deg, #545b62, #495057);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108,117,125,0.3);
        color: white;
        text-decoration: none;
    }
    
    .btn-icon {
        font-size: 16px;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .content-wrapper {
            padding: 0 15px;
        }
        
        .article-header {
            padding: 25px 20px 15px 20px;
        }
        
        .article-title {
            font-size: 1.8rem;
        }
        
        .article-content {
            padding: 25px 20px;
            font-size: 1rem;
        }
        
        .related-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .breadcrumb {
            flex-wrap: wrap;
            gap: 5px;
        }
    }
    
    @media (max-width: 480px) {
        .article-title {
            font-size: 1.5rem;
        }
        
        .article-content {
            padding: 20px 15px;
        }
    }
</style>

<?php include('./view/footer.php'); ?> 