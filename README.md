# 📚 Web Bán Sách Online

Website thương mại điện tử bán sách trực tuyến, giúp người dùng dễ dàng tìm kiếm, xem và mua sách mọi lúc mọi nơi.
## 📸 Giao diện

> <img width="1231" height="918" alt="Ảnh chụp màn hình 2026-03-11 140942" src="https://github.com/user-attachments/assets/b2425f13-24c9-4dbb-a2b8-9add313adf31" />


## 🌟 Tính năng

### Người dùng
- 🔍 Tìm kiếm sách theo tên, tác giả, thể loại
- 📖 Xem chi tiết sách (mô tả, giá, tác giả, đánh giá)
- 🛒 Thêm sách vào giỏ hàng
- 💳 Đặt hàng và thanh toán
- 👤 Đăng ký / Đăng nhập tài khoản
- 📦 Theo dõi đơn hàng

### Admin
- ➕ Thêm / Sửa / Xóa sản phẩm
- 📊 Quản lý đơn hàng
- 👥 Quản lý người dùng

## 🛠️ Công nghệ sử dụng

| Công nghệ | Mô tả |
|-----------|-------|
| HTML5 | Cấu trúc trang web |
| CSS3 | Giao diện, responsive |
| JavaScript | Xử lý logic phía client |
| Bootstrap | Framework CSS |
## ⚙️ Cấu hình Database

1. Tạo database trong phpMyAdmin
2. Import file `database.sql` vào
3. Copy file cấu hình:
   - Đổi tên `config.example.php` thành `config.php`
   - Điền thông tin database của bạn vào
## 📦 Cài đặt & Chạy dự án

### Yêu cầu
- Trình duyệt web (Chrome, Firefox, Edge...)
- VS Code + Extension Live Server (khuyến nghị)

### Các bước cài đặt

1. Clone dự án về máy:
```bash
git clone https://github.com/leduc-vn/duanwebbansach.git
```

2. Di chuyển vào thư mục dự án:
```bash
cd duanwebbansach
```

3. Mở dự án:
- Cách 1: Mở thẳng file `index.html` trên trình duyệt
- Cách 2: Dùng **Live Server** trong VS Code (chuột phải vào `index.html` → Open with Live Server)

## 📁 Cấu trúc thư mục
```
duanwebbansach/
├── index.html              # Trang chủ
├── pages/
│   ├── product.html        # Trang danh sách sách
│   ├── detail.html         # Trang chi tiết sách
│   ├── cart.html           # Giỏ hàng
│   ├── checkout.html       # Thanh toán
│   ├── login.html          # Đăng nhập
│   └── register.html       # Đăng ký
├── css/
│   ├── style.css           # CSS chính
│   └── responsive.css      # CSS responsive
├── js/
│   ├── main.js             # Logic chính
│   ├── cart.js             # Xử lý giỏ hàng
│   └── auth.js             # Xử lý đăng nhập
├── images/
│   ├── books/              # Ảnh sách
│   └── banner/             # Ảnh banner
└── README.md
```
## 🔧 Tính năng sắp phát triển

- [ ] Tích hợp thanh toán online (VNPay, Momo)
- [ ] Bình luận & đánh giá sách
- [ ] Gợi ý sách theo sở thích
- [ ] Phiên bản mobile app

## 👤 Tác giả

**Le Duc**
- GitHub: [@leduc-vn](https://github.com/leduc-vn)

## 📄 Giấy phép

Dự án được phát triển cho mục đích học tập.
