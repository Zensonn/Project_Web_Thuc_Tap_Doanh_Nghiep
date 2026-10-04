<?php
session_start();
function register_message($message, $type = 'error') {
	$_SESSION['register_message'] = $message;
	$_SESSION['register_message_type'] = $type;
	header('Location: register.php');
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
// Kiểm tra form đã được gửi đầy đủ dữ liệu chưa.
if (!isset($_POST['username'], $_POST['password'], $_POST['email'], $_POST['role'])) {
	register_message('Vui lòng hoàn thành đầy đủ thông tin đăng ký.');
}
// Kiểm tra các trường đăng ký không được để trống.
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	// Có ít nhất một trường đang bị bỏ trống.
	register_message('Vui lòng hoàn thành đầy đủ thông tin đăng ký.');
}
// Kiểm tra địa chỉ email.
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	register_message('Email không hợp lệ.');
}
// Kiểm tra username chỉ gồm chữ cái và chữ số.
if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
	register_message('Tên đăng nhập chỉ được chứa chữ cái và chữ số.');
}
// Chỉ cho phép đăng ký các vai trò không phải admin.
$allowed_roles = ['student', 'company', 'lecturer'];
if (!in_array($_POST['role'], $allowed_roles, true)) {
	register_message('Vai trò đăng ký không hợp lệ.');
}
// Kiểm tra mật khẩu có từ 5 đến 20 ký tự.
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	register_message('Mật khẩu phải có từ 5 đến 20 ký tự.');
}
// Kiểm tra username đã tồn tại chưa.
if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
	// Gắn tham số cho câu lệnh.
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	// Lưu kết quả để kiểm tra tài khoản.
	$stmt->store_result();
	// Kiểm tra tài khoản.
	if ($stmt->num_rows > 0) {
		// Username đã tồn tại.
		register_message('Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.');
	} else {
		// Chuẩn bị dữ liệu tài khoản.
		$registered = date('Y-m-d H:i:s');
		// Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu.
		$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		// Username chưa tồn tại, tiến hành tạo tài khoản.
		if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, registered, activation_code, role) VALUES (?, ?, ?, ?, "activated", ?)')) {
			// Gắn dữ liệu POST vào câu lệnh chuẩn bị.
			$stmt->bind_param('sssss', $_POST['username'], $password, $_POST['email'], $registered, $_POST['role']);
			if (!$stmt->execute()) {
				register_message('Không thể tạo tài khoản. Vui lòng thử lại.');
			}
			// Hiển thị thông báo đăng ký thành công.
			register_message('Đăng ký tài khoản thành công. Bạn có thể đăng nhập ngay.', 'success');
        } else {
			register_message('Không thể tạo tài khoản. Vui lòng thử lại.');
        }
	}
	// Đóng câu lệnh chuẩn bị.
	$stmt->close();
} else {
	// Lỗi câu lệnh SQL, hãy kiểm tra bảng tài khoản.
	register_message('Không thể kiểm tra tài khoản. Vui lòng thử lại.');
}
// Đóng kết nối cơ sở dữ liệu.
$con->close();
?>