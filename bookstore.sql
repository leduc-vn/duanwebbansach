-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2025 at 12:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `a`
--

-- --------------------------------------------------------

--
-- Table structure for table `adproduct`
--

CREATE TABLE `adproduct` (
  `Ma_loaisp` varchar(50) NOT NULL,
  `ma_sp` varchar(50) NOT NULL,
  `tensp` varchar(100) NOT NULL,
  `anhsp` varchar(255) NOT NULL,
  `motasp` text NOT NULL,
  `gianhap` int(11) NOT NULL,
  `giaxuat` int(11) NOT NULL,
  `khuyenmai` int(11) NOT NULL,
  `soluong` int(11) NOT NULL,
  `ngay_nhap` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `adproduct`
--

INSERT INTO `adproduct` (`Ma_loaisp`, `ma_sp`, `tensp`, `anhsp`, `motasp`, `gianhap`, `giaxuat`, `khuyenmai`, `soluong`, `ngay_nhap`) VALUES
('Sach_Van_Hoc', 'B001', 'Chí Phèo - Nam Cao', 'chi_pheo.jpg', 'Tác phẩm kinh điển phản ánh hiện thực xã hội nông thôn Việt Nam.', 30000, 50000, 10, 79, '2025-06-30'),
('Sach_Khoa_Hoc', 'B002', 'Vũ Trụ Trong Vỏ Hạt Dẻ - Stephen Hawking', 'vu_tru_vo_hat_de.jpg', 'Giải thích các khái niệm vật lý vũ trụ phức tạp một cách dễ hiểu.', 70000, 120000, 15, 20, '2025-06-30'),
('Sach_Tam_Ly', 'B003', 'Đắc Nhân Tâm - Dale Carnegie', 'dac_nhan_tam.jpg', 'Cẩm nang giao tiếp và xây dựng mối quan hệ hiệu quả.', 40000, 85000, 20, 21, '2025-06-30'),
('Sach_Kinh_Te', 'B004', 'Cha Giàu Cha Nghèo - Robert Kiyosaki', 'cha_giau_cha_ngheo.jpg', 'Bí quyết tư duy tài chính và cách xây dựng tài sản.', 60000, 110000, 10, 35, '2025-07-01'),
('Sach_Lich_Su', 'B005', 'Lược Sử Loài Người - Yuval Noah Harari', 'luoc_su_loai_nguoi.jpg', 'Hành trình tiến hóa và phát triển của nhân loại từ cổ đại đến hiện đại.', 85000, 140000, 15, 25, '2025-07-01'),
('Sach_Ton_Giao', 'B006', 'Đường Xưa Mây Trắng - Thích Nhất Hạnh', 'duong_xua_may_trang.jpg', 'Cuộc đời Đức Phật Thích Ca được kể bằng giọng văn nhẹ nhàng, sâu sắc.', 50000, 95000, 20, 18, '2025-07-01'),
('Sach_Tam_Ly', 'B007', 'Tâm Lý Học Về Tiền - Morgan Housel', 'tam_ly_tien.jpg', 'Phân tích tâm lý con người trong hành vi tài chính.', 65000, 115000, 10, 22, '2025-07-01'),
('Sach_Van_Hoc', 'B008', 'Tuổi Trẻ Đáng Giá Bao Nhiêu - Rosie Nguyễn', 'tuoi_tre.jpg', 'Sách truyền cảm hứng cho giới trẻ về học tập, du lịch và trải nghiệm.', 50000, 98000, 5, 40, '2025-07-01'),
('Sach_Thieu_Nhi', 'B009', 'Dế Mèn Phiêu Lưu Ký - Tô Hoài', 'de_men.jpg', 'Tác phẩm thiếu nhi kinh điển với hành trình phiêu lưu đầy bài học.', 30000, 60000, 10, 50, '2025-07-02'),
('Sach_Thieu_Nhi', 'B010', 'Truyện Cổ Grimm', 'truyen_co_grimm.jpg', 'Tổng hợp các truyện cổ tích nổi tiếng của anh em nhà Grimm.', 45000, 75000, 5, 45, '2025-07-02'),
('Sach_Ngoai_Ngu', 'B011', 'English Grammar In Use - Raymond Murphy', 'grammar_in_use.jpg', 'Tài liệu học ngữ pháp tiếng Anh thông dụng nhất.', 90000, 160000, 20, 30, '2025-07-02'),
('Sach_Ngoai_Ngu', 'B012', 'Minna no Nihongo I - Sách Chính + Bài Tập', 'minna_no_nihongo.jpg', 'Giáo trình học tiếng Nhật phổ biến cho người mới bắt đầu.', 95000, 170000, 15, 25, '2025-07-02'),
('Truyen_Trang', 'B013', 'One Piece Tập 1 - Eiichiro Oda', 'one_piece_tap1.jpg', 'Khởi đầu hành trình trở thành Vua Hải Tặc của Luffy.', 20000, 40000, 5, 100, '2025-07-02'),
('Truyen_Trang', 'B014', 'Doraemon Tập 5 - Fujiko F. Fujio', 'doraemon_5.jpg', 'Những câu chuyện hài hước, giàu trí tưởng tượng của Doraemon và Nobita.', 25000, 45000, 0, 80, '2025-07-02');

-- --------------------------------------------------------

--
-- Table structure for table `adproducttype`
--

CREATE TABLE `adproducttype` (
  `Ma_loaisp` varchar(50) NOT NULL,
  `Ten_loaisp` varchar(100) NOT NULL,
  `Mota_loaisp` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `adproducttype`
--

INSERT INTO `adproducttype` (`Ma_loaisp`, `Ten_loaisp`, `Mota_loaisp`) VALUES
('Sach_Khoa_Hoc', 'Sách Khoa Học', 'Sách về khoa học tự nhiên, công nghệ, khám phá.'),
('Sach_Kinh_Te', 'Sách Kinh Tế', 'Sách về kinh doanh, tài chính, quản lý.'),
('Sach_Lich_Su', 'Sách Lịch Sử', 'Tìm hiểu các sự kiện lịch sử trong nước và thế giới.'),
('Sach_Ngoai_Ngu', 'Sách Ngoại Ngữ', 'Sách học tiếng Anh, Nhật, Hàn, và các ngôn ngữ khác.'),
('Sach_Tam_Ly', 'Sách Tâm Lý', 'Sách phát triển bản thân, tâm lý học ứng dụng.'),
('Sach_Thieu_Nhi', 'Sách Thiếu Nhi', 'Sách dành cho trẻ em, truyện cổ tích, sách học chữ.'),
('Sach_Ton_Giao', 'Sách Tôn Giáo', 'Sách nghiên cứu về các tôn giáo lớn.'),
('Sach_Van_Hoc', 'Sách Văn Học', 'Sách Văn Học'),
('Truyen_Trang', 'Truyện Tranh', 'Truyện tranh thiếu nhi, manga, comic.');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `makh` varchar(50) NOT NULL,
  `tenkh` varchar(100) NOT NULL,
  `phone` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `email_dangnhap` varchar(255) DEFAULT NULL,
  `diachi_lienhe` varchar(300) NOT NULL,
  `diachi_giaohang` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dangkithanhvien`
--

CREATE TABLE `dangkithanhvien` (
  `Fullname` varchar(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Gioitinh` varchar(255) NOT NULL,
  `Quoctich` varchar(255) NOT NULL,
  `Diachi` varchar(255) NOT NULL,
  `Hinhanh` varchar(255) NOT NULL,
  `Sothich` varchar(255) NOT NULL,
  `quyen` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dangkithanhvien`
--

INSERT INTO `dangkithanhvien` (`Fullname`, `Username`, `Password`, `Email`, `Gioitinh`, `Quoctich`, `Diachi`, `Hinhanh`, `Sothich`, `quyen`) VALUES
('Nguyễn Văn A', '1', '1', '1@gmail.com', 'Nam', 'Vietnam', 'Hà Nội', 'user1.jpg', 'Đọc sách,Tản bộ', 1),
('Trần Thị B', '2', '2', '2@gmail.com', 'Nữ', 'Vietnam', 'TP.HCM', 'user2.jpg', 'Du lịch,Đọc sách', 0);

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `mahd` varchar(50) NOT NULL,
  `makh` varchar(50) NOT NULL,
  `tenkh` varchar(50) NOT NULL,
  `tongtien` int(11) NOT NULL,
  `create_date` date NOT NULL,
  `shipping_date` datetime DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  `trangthai` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderdetail`
--

CREATE TABLE `orderdetail` (
  `mahd` varchar(40) NOT NULL,
  `masp` varchar(50) NOT NULL,
  `tensp` varchar(100) NOT NULL,
  `soluong` int(11) NOT NULL,
  `dongia` int(11) NOT NULL,
  `khuyenmai` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tintuc`
--

CREATE TABLE `tintuc` (
  `id` int(11) NOT NULL,
  `tieude` varchar(255) NOT NULL,
  `noidung` text NOT NULL,
  `hinhanh` varchar(255) DEFAULT NULL,
  `ngaytao` timestamp NOT NULL DEFAULT current_timestamp(),
  `trangthai` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tintuc`
--

INSERT INTO `tintuc` (`id`, `tieude`, `noidung`, `hinhanh`, `ngaytao`, `trangthai`) VALUES
(1, 'Ra mắt sách mới: Chí Phèo - Phiên bản đặc biệt', 'Chúng tôi vừa phát hành phiên bản đặc biệt của tác phẩm Chí Phèo với minh họa độc đáo.', 'chi_pheo.jpg', '2025-07-12 23:43:48', 1),
(2, 'Khuyến mãi tháng 7: Giảm giá 20% toàn bộ sách', 'Áp dụng đến hết ngày 31/07/2025. Cơ hội tuyệt vời để bổ sung kho sách cá nhân.', 'chuong-trinh-khuyen-mai-thang-7-2.png', '2025-07-12 23:43:48', 1),
(3, 'Hướng dẫn chọn sách phát triển bản thân', 'Bạn đang tìm cuốn sách giúp thay đổi tư duy, cải thiện bản thân? Hãy xem ngay gợi ý của chúng tôi.', 'cach-chon-sach.jpg', '2025-07-12 23:43:48', 1),
(4, 'Top 5 sách khoa học nên đọc trong đời', 'Những cuốn sách mang lại kiến thức khoa học lý thú, dễ tiếp cận với độc giả phổ thông.', 'top 5 anh-mo-ta.jpg', '2025-07-12 23:43:48', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adproduct`
--
ALTER TABLE `adproduct`
  ADD PRIMARY KEY (`ma_sp`);

--
-- Indexes for table `adproducttype`
--
ALTER TABLE `adproducttype`
  ADD PRIMARY KEY (`Ma_loaisp`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`makh`);

--
-- Indexes for table `dangkithanhvien`
--
ALTER TABLE `dangkithanhvien`
  ADD PRIMARY KEY (`Username`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`mahd`),
  ADD KEY `idx_order_create_date` (`create_date`),
  ADD KEY `idx_order_trangthai` (`trangthai`);

--
-- Indexes for table `orderdetail`
--
ALTER TABLE `orderdetail`
  ADD PRIMARY KEY (`mahd`,`masp`);

--
-- Indexes for table `tintuc`
--
ALTER TABLE `tintuc`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tintuc`
--
ALTER TABLE `tintuc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
