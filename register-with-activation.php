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
// Kiểm tra form đã được gửi đầy đủ dữ liệu chưa.
if (!isset($_POST['username'], $_POST['password'], $_POST['email'], $_POST['role'])) {
	// Không nhận được đầy đủ dữ liệu cần thiết.
	exit('Please complete the registration form!');
}
// Kiểm tra các trường đăng ký không được để trống.
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	// Có ít nhất một trường đang bị bỏ trống.
	exit('Please complete the registration form');
}
// Kiểm tra địa chỉ email.
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit('Email is not valid!');
}
// Kiểm tra username chỉ gồm chữ cái và chữ số.
if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
    exit('Username is not valid!');
}
$allowed_roles = ['student', 'company', 'lecturer'];
if (!in_array($_POST['role'], $allowed_roles, true)) {
	exit('Role is not valid!');
}
// Kiểm tra mật khẩu có từ 5 đến 20 ký tự.
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	exit('Password must be between 5 and 20 characters long!');
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
		echo 'Username already exists! Please choose another!';
	} else {
		// Chuẩn bị dữ liệu tài khoản.
		$registered = date('Y-m-d H:i:s');
		// Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu.
		$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		// Tạo mã kích hoạt duy nhất.
		$uniqid = sha1(uniqid() . 'YOUR_SECRET_KEY');
		// Username chưa tồn tại, tiến hành tạo tài khoản.
		if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, registered, activation_code, role) VALUES (?, ?, ?, ?, ?, ?)')) {
			$stmt->bind_param('ssssss', $_POST['username'], $password, $_POST['email'], $registered, $uniqid, $_POST['role']);
			$stmt->execute();
			// Gửi email để kích hoạt tài khoản.
			$from    = 'noreply@example.com';
			$subject = 'Account Activation Required';
			$headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
			$activate_link = 'http://example.com/phplogin/activate.php?email=' . $_POST['email'] . '&code=' . $uniqid;
			$message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
			mail($_POST['email'], $subject, $message, $headers);
			// Hiển thị thông báo thành công.
			echo 'Please check your email to activate your account!';
		} else {
			// Lỗi câu lệnh SQL, hãy kiểm tra bảng tài khoản.
			echo 'Could not prepare statement!';
		}
	}
	// Đóng câu lệnh chuẩn bị.
	$stmt->close();
} else {
	// Lỗi câu lệnh SQL, hãy kiểm tra bảng tài khoản.
	echo 'Could not prepare statement!';
}
// Đóng kết nối cơ sở dữ liệu.
// Đóng kết nối cơ sở dữ liệu.
$con->close();
?>