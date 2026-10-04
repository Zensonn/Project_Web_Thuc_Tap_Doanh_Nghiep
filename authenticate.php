<?php
// Khởi tạo session.
session_start();
function login_error($message) {
    $_SESSION['login_error'] = $message;
    header('Location: index.php');
    exit;
}
// Thông tin kết nối cơ sở dữ liệu.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';
// Kết nối đến cơ sở dữ liệu.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
// Kiểm tra lỗi kết nối.
if (mysqli_connect_errno()) {
    // Dừng xử lý nếu kết nối thất bại.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Kiểm tra dữ liệu được gửi từ form đăng nhập.
if (!isset($_POST['username'], $_POST['password'])) {
    login_error('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.');
}
// Dùng câu lệnh chuẩn bị để tránh SQL injection.
if ($stmt = $con->prepare('SELECT id, password, activation_code, role, status FROM users WHERE username = ?')) {
    // Gắn tham số username kiểu chuỗi.
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
    // Lưu kết quả để kiểm tra tài khoản có tồn tại.
	$stmt->store_result();
    // Kiểm tra tài khoản theo username.
    if ($stmt->num_rows > 0) {
        // Gắn dữ liệu tài khoản vào các biến.
        $stmt->bind_result($id, $password, $activation_code, $role, $status);
        $stmt->fetch();
        if ($status !== 'active') {
			login_error($status === 'blocked' ? 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.' : 'Tài khoản chưa được kích hoạt.');
        }
        // Kiểm tra tài khoản đã được kích hoạt.
		if ($activation_code != 'activated') {
            login_error('Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email để kích hoạt.');
		}
        // Mật khẩu được lưu dưới dạng đã mã hóa bằng password_hash.
        if (password_verify($_POST['password'], $password)) {
            // Đăng nhập thành công.
            // Tạo lại session ID để tránh session fixation.
            session_regenerate_id();
            // Lưu thông tin tài khoản vào session.
            $_SESSION['account_loggedin'] = TRUE;
            $_SESSION['account_name'] = $_POST['username'];
            $_SESSION['account_id'] = $id;
			$_SESSION['account_role'] = $role;
            $role_dashboards = [
                'admin' => 'admin/index.php',
                'lecturer' => 'lecturer/index.php',
                'company' => 'company/dashboard.php',
                'student' => 'home.php'
            ];
            header('Location: ' . ($role_dashboards[$role] ?? 'home.php'));
            exit;
        } else {
			login_error('Tên đăng nhập hoặc mật khẩu không chính xác.');
        }
    } else {
		login_error('Tên đăng nhập hoặc mật khẩu không chính xác.');
    }
	// Đóng câu lệnh chuẩn bị.
	$stmt->close();
}
?>