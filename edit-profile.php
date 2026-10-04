<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: index.php');
	exit;
}
if (($_SESSION['account_role'] ?? '') !== 'student') {
	header('Location: profile.php');
	exit;
}

$con = mysqli_connect('localhost', 'root', '', 'phplogin');
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$full_name = trim($_POST['full_name'] ?? '');
	$student_code = trim($_POST['student_code'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	if ($full_name === '' || $student_code === '') {
		$message = 'Họ tên và mã sinh viên không được để trống.';
	} elseif (!preg_match('/^\d{8}$/', $student_code)) {
		$message = 'Mã sinh viên phải gồm đúng 8 chữ số.';
	} elseif ($phone !== '' && !preg_match('/^0\d{9}$/', $phone)) {
		$message = 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.';
	} else {
		$stmt = $con->prepare('INSERT INTO students
			(user_id, student_code, full_name, date_of_birth, gender, phone, address, major, cohort, gpa, skills)
			VALUES (?, ?, ?, NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""))
			ON DUPLICATE KEY UPDATE student_code = VALUES(student_code), full_name = VALUES(full_name),
			date_of_birth = VALUES(date_of_birth), gender = VALUES(gender), phone = VALUES(phone),
			address = VALUES(address), major = VALUES(major), cohort = VALUES(cohort),
			gpa = VALUES(gpa), skills = VALUES(skills)');
		$stmt->bind_param('issssssssss', $_SESSION['account_id'], $student_code, $full_name,
			$_POST['date_of_birth'], $_POST['gender'], $_POST['phone'], $_POST['address'],
			$_POST['major'], $_POST['cohort'], $_POST['gpa'], $_POST['skills']);
		if ($stmt->execute()) {
			header('Location: profile.php');
			exit;
		}
		$message = 'Không thể cập nhật hồ sơ. Mã sinh viên có thể đã tồn tại.';
		$stmt->close();
	}
}

$stmt = $con->prepare('SELECT full_name, student_code, date_of_birth, gender, phone, address, major, cohort, gpa, skills FROM students WHERE user_id = ?');
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($full_name, $student_code, $date_of_birth, $gender, $phone, $address, $major, $cohort, $gpa, $skills);
$stmt->fetch();
$stmt->close();
$input = static function ($value) {
	return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1">
	<title>Chỉnh sửa hồ sơ</title>
	<link href="css/style.css?v=<?=filemtime(__DIR__ . '/css/style.css')?>" rel="stylesheet" type="text/css">
</head>
<body>
	<header class="header student-header">
		<div class="wrapper student-header-wrapper">
			<h1>Cổng sinh viên</h1>
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
	<div class="content student-edit-content">
		<div class="page-title">
			<h2>Chỉnh sửa hồ sơ</h2>
			<p>Cập nhật thông tin cá nhân để hoàn thiện hồ sơ thực tập.</p>
		</div>
		<div class="block student-edit-card">
			<?php if ($message): ?><p class="upload-message"><?=$input($message)?></p><?php endif; ?>
			<form class="profile-form" method="post">
				<label>Họ tên<input class="form-input" name="full_name" value="<?=$input($full_name)?>" required></label>
				<label>Mã sinh viên<input class="form-input" name="student_code" value="<?=$input($student_code)?>" inputmode="numeric" pattern="[0-9]{8}" minlength="8" title="Mã sinh viên phải gồm đúng 8 chữ số." required><small class="form-help">Bắt buộc nhập đúng 8 chữ số, không được thiếu hoặc dư.</small></label>
				<label>Ngày sinh<input class="form-input" type="date" name="date_of_birth" value="<?=$input($date_of_birth)?>"></label>
				<label>Giới tính<select class="form-input" name="gender"><option value="">Chưa cập nhật</option><option value="male" <?=$gender === 'male' ? 'selected' : ''?>>Nam</option><option value="female" <?=$gender === 'female' ? 'selected' : ''?>>Nữ</option><option value="other" <?=$gender === 'other' ? 'selected' : ''?>>Khác</option></select></label>
				<label>Số điện thoại<input class="form-input" name="phone" value="<?=$input($phone)?>" inputmode="numeric" pattern="0[0-9]{9}" minlength="10" title="Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0."><small class="form-help">Nhập đúng 10 chữ số và bắt đầu bằng số 0.</small></label>
				<label>Địa chỉ<input class="form-input" name="address" value="<?=$input($address)?>"></label>
				<label>Ngành học<input class="form-input" name="major" value="<?=$input($major)?>"></label>
				<label>Khóa<input class="form-input" name="cohort" value="<?=$input($cohort)?>"></label>
				<label>GPA<input class="form-input" type="number" step="0.01" min="0" max="4" name="gpa" value="<?=$input($gpa)?>"></label>
				<label class="profile-form-wide">Kỹ năng<textarea class="form-input" name="skills" rows="4"><?=$input($skills)?></textarea></label>
				<div class="profile-form-actions"><button class="btn" type="submit">Lưu hồ sơ</button><a class="btn btn-secondary" href="profile.php">Hủy</a></div>
			</form>
		</div>
	</div>
</body>
</html>
