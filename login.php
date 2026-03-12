<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "connect.php";
if(isset($_POST["Login"]) && $_POST["Username"] != '' && $_POST["Password"] != '' ){ 
  $Username = $_POST["Username"]; 
  $Password = $_POST["Password"]; 
  $sql="SELECT * FROM dangkithanhvien WHERE Username ='$Username' AND Password='$Password'"; 
  $rel =mysqli_query($conn,$sql);
    if (mysqli_num_rows($rel) > 0) {
        $res = getRes($rel);
        $user = $res[0]; // Lấy thông tin user đầu tiên
        
        // Lưu thông tin vào session
        $_SESSION['Username'] = $Username;
        $_SESSION['Fullname'] = $user['Fullname'];
        $_SESSION['quyen'] = $user['quyen'];
        $_SESSION['Email'] = $user['Email'];
        
        echo "<script>alert('Đăng nhập thành công! Chào mừng " . htmlspecialchars($user['Fullname']) . "'); window.location.href='index.php';</script>";
    }
    else { 
        echo "<script>alert('Đăng nhập thất bại! Vui lòng kiểm tra lại tên đăng nhập và mật khẩu.');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #333;
            margin: 0;
            font-size: 28px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login {
            background: #667eea;
            color: white;
        }
        
        .btn-login:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-reset {
            background: #6c757d;
            color: white;
        }
        
        .btn-reset:hover {
            background: #5a6268;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Đăng Nhập</h2>
        </div>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="Username" placeholder="Nhập tên đăng nhập" required />
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="Password" placeholder="Nhập mật khẩu" required />
            </div>
            
            <div class="btn-group">
                <button type="submit" name="Login" class="btn btn-login">Đăng nhập</button>
                <button type="reset" class="btn btn-reset">Làm mới</button>
            </div>
        </form>
        
        <div class="register-link">
            <p>Chưa có tài khoản? <a href="dangkithanhvien.php">Đăng ký ngay</a></p>
        </div>
    </div>
</body>
</html>
