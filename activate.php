<?php
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
// Kiểm tra email và mã kích hoạt trong tham số GET.
if (isset($_GET['email'], $_GET['code']) && !empty($_GET['code'])) {
	// Đủ tham số nên có thể tiến hành kích hoạt.
	if ($stmt = $con->prepare('SELECT * FROM accounts WHERE email = ? AND activation_code = ?')) {
		$stmt->bind_param('ss', $_GET['email'], $_GET['code']);
		$stmt->execute();
		// Lưu kết quả để kiểm tra tài khoản.
		$stmt->store_result();
		// Kiểm tra tài khoản theo email và mã kích hoạt.
		if ($stmt->num_rows > 0) {
			// Tài khoản hợp lệ, cập nhật trạng thái đã kích hoạt.
			if ($stmt = $con->prepare('UPDATE accounts SET activation_code = "activated" WHERE email = ? AND activation_code = ?')) {
				// Gắn các tham số cho câu lệnh.
				$stmt->bind_param('ss', $_GET['email'], $_GET['code']);
				$stmt->execute();
				// Hiển thị thông báo thành công.
				echo 'Your account is now activated! You can now login!<br><a href="index.php">Login</a>';
			}
		} else {
			echo 'The account is already activated or doesn\'t exist!';
		}
	}
}
?>