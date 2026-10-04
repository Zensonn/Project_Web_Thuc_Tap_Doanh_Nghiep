<?php
// Khởi tạo session để kiểm tra đăng nhập.
session_start();
// Nếu chưa đăng nhập thì chuyển về trang đăng nhập.
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: index.php');
	exit;
}
if (($_SESSION['account_role'] ?? '') !== 'student') {
	$role_dashboards = ['admin' => 'admin/index.php', 'lecturer' => 'lecturer/index.php', 'company' => 'company/dashboard.php'];
	header('Location: ' . ($role_dashboards[$_SESSION['account_role'] ?? ''] ?? 'home.php'));
	exit;
}
// Thông tin kết nối cơ sở dữ liệu.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';
$role_labels = [
	'student' => 'Sinh viên',
	'company' => 'Doanh nghiệp',
	'lecturer' => 'Giảng viên',
	'admin' => 'Admin'
];
$gender_labels = [
	'male' => 'Nam',
	'female' => 'Nữ',
	'other' => 'Khác'
];
// Kết nối đến cơ sở dữ liệu.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
// Đảm bảo kết nối không có lỗi.
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
$upload_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv_file'])) {
	$file = $_FILES['cv_file'];
	$allowed_extensions = ['pdf', 'doc', 'docx'];
	$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	if ($file['error'] !== UPLOAD_ERR_OK) {
		$upload_message = 'Không thể tải CV lên. Vui lòng thử lại.';
	} elseif ($file['size'] > 5 * 1024 * 1024 || !in_array($extension, $allowed_extensions, true)) {
		$upload_message = 'CV phải là file PDF, DOC hoặc DOCX và không quá 5 MB.';
	} else {
		$upload_directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv';
		if (!is_dir($upload_directory)) {
			mkdir($upload_directory, 0755, true);
		}
		$stored_filename = bin2hex(random_bytes(16)) . '.' . $extension;
		$stored_path = $upload_directory . DIRECTORY_SEPARATOR . $stored_filename;
		if (move_uploaded_file($file['tmp_name'], $stored_path)) {
			$update_stmt = $con->prepare('UPDATE students SET cv_file = ? WHERE user_id = ?');
			$update_stmt->bind_param('si', $stored_filename, $_SESSION['account_id']);
			$update_stmt->execute();
			$updated = $update_stmt->affected_rows > 0;
			$update_stmt->close();
			if ($updated) {
				$upload_message = 'Đã cập nhật CV thành công.';
			} else {
				unlink($stored_path);
				$upload_message = 'Vui lòng cập nhật thông tin sinh viên trước khi tải CV.';
			}
		} else {
			$upload_message = 'Không thể lưu CV. Vui lòng kiểm tra quyền ghi thư mục uploads.';
		}
	}
}
$stmt = $con->prepare('SELECT u.username, u.email, u.registered, u.role,
		 s.full_name, s.date_of_birth, s.gender, s.phone, s.address,
		 s.student_code, s.major, s.cohort, s.gpa, s.skills, s.cv_file
	FROM users u
	LEFT JOIN students s ON s.user_id = u.id
	WHERE u.id = ?');
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($username, $email, $registered, $role, $full_name, $date_of_birth,
	$gender, $phone, $address, $student_code, $major, $cohort, $gpa, $skills, $cv_file);
$stmt->fetch();
$stmt->close();
$display_value = static function ($value) {
	return ($value !== null && $value !== '') ? htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') : 'Chưa cập nhật';
};
$formatted_birth_date = $date_of_birth ? date('d/m/Y', strtotime($date_of_birth)) : null;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Thông tin cá nhân</title>
		<link href="css/style.css" rel="stylesheet" type="text/css">
	</head>
    <body>

		<header class="header">

			<div class="wrapper">

				<h1>Hồ sơ</h1>
				
				<nav class="menu">
					<a href="home.php">Trang chủ</a>
					<a href="student-portal.php">Quản lý thực tập</a>
					<a href="internships.php">Tìm thực tập</a>
					<a href="internship-logs.php">Nhật ký</a>
					<a href="profile.php">Hồ sơ</a>
					<a href="logout.php">Đăng xuất</a>
				</nav>

			</div>

		</header>

		<div class="content">

			<div class="page-title">
				<div class="wrap">
					<h2>Thông tin cá nhân</h2>
				</div>
			</div>

			<div class="block">

				<div class="profile-grid">
					<div class="profile-detail"><strong>Họ tên</strong><?= $display_value($full_name ?: $username) ?></div>
					<div class="profile-detail"><strong>Ngày sinh</strong><?= $display_value($formatted_birth_date) ?></div>
					<div class="profile-detail"><strong>Giới tính</strong><?= $display_value($gender_labels[$gender] ?? $gender) ?></div>
					<div class="profile-detail"><strong>Số điện thoại</strong><?= $display_value($phone) ?></div>
					<div class="profile-detail"><strong>Email</strong><?= $display_value($email) ?></div>
					<div class="profile-detail"><strong>Địa chỉ</strong><?= $display_value($address) ?></div>
					<div class="profile-detail"><strong>Mã sinh viên</strong><?= $display_value($student_code) ?></div>
					<div class="profile-detail"><strong>Ngành học</strong><?= $display_value($major) ?></div>
					<div class="profile-detail"><strong>Khóa</strong><?= $display_value($cohort) ?></div>
					<div class="profile-detail"><strong>GPA</strong><?= $display_value($gpa) ?></div>
					<div class="profile-detail profile-detail-wide"><strong>Kỹ năng</strong><?= $display_value($skills) ?></div>
				</div>

			</div>

			<div class="block cv-panel">
				<div class="cv-panel-heading">
					<div>
						<h3>Hồ sơ sinh viên</h3>
						<p>Thêm CV để sử dụng khi ứng tuyển thực tập.</p>
					</div>
					<a class="btn btn-secondary" href="edit-profile.php">Chỉnh sửa hồ sơ</a>
				</div>
				<?php if ($upload_message): ?>
					<p class="upload-message"><?=htmlspecialchars($upload_message, ENT_QUOTES, 'UTF-8')?></p>
				<?php endif; ?>
				<div class="cv-actions">
					<form class="cv-upload" method="post" enctype="multipart/form-data">
						<label for="cv_file">CV của bạn</label>
						<input id="cv_file" type="file" name="cv_file" accept=".pdf,.doc,.docx" required>
						<button class="btn" type="submit">Tải CV lên</button>
					</form>
					<?php if ($cv_file): ?>
						<a class="btn btn-secondary" href="uploads/cv/<?=rawurlencode($cv_file)?>" download>Tải CV</a>
					<?php endif; ?>
				</div>
			</div>

		</div>

    </body>
</html>