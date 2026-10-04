<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: index.php');
	exit;
}
if (($_SESSION['account_role'] ?? '') !== 'student') {
	http_response_code(403);
	exit('Trang này chỉ dành cho sinh viên.');
}

$con = mysqli_connect('localhost', 'root', '', 'phplogin');
if (!$con) {
	exit('Không thể kết nối cơ sở dữ liệu: ' . mysqli_connect_error());
}

$stmt = $con->prepare('ALTER TABLE internship_logs ADD COLUMN IF NOT EXISTS log_date date DEFAULT NULL');
if ($stmt) { $stmt->execute(); $stmt->close(); }

$stmt = $con->prepare('ALTER TABLE internship_logs ADD COLUMN IF NOT EXISTS content text DEFAULT NULL');
if ($stmt) { $stmt->execute(); $stmt->close(); }

$stmt = $con->prepare('ALTER TABLE internship_logs ADD COLUMN IF NOT EXISTS result text DEFAULT NULL');
if ($stmt) { $stmt->execute(); $stmt->close(); }

$user_id = (int)$_SESSION['account_id'];
$student_id = null;
$stmt = $con->prepare('SELECT id FROM students WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
if ($stmt->fetch()) {
	$student_id = (int)$student_id;
}
$stmt->close();

$student_internships = [];
if ($student_id) {
	$stmt = $con->prepare('SELECT i.id, i.start_date, i.end_date, i.status, p.title, c.company_name FROM internships i JOIN internship_posts p ON p.id = i.post_id JOIN companies c ON c.id = i.company_id WHERE i.student_id = ? AND i.status <> "completed" ORDER BY i.start_date DESC, i.created_at DESC');
	$stmt->bind_param('i', $student_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$student_internships = $result->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
}

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
	$log_date = $_POST['log_date'] ?? '';
	$content = trim($_POST['content'] ?? '');
	$result = trim($_POST['result'] ?? '');

	if (!$internship_id || !$log_date || $content === '' || $result === '') {
		$message = 'Vui lòng điền đầy đủ ngày, nội dung công việc và kết quả.';
	} else {
		$allowed_internship = false;
		foreach ($student_internships as $internship) {
			if ((int)$internship['id'] === (int)$internship_id) {
				$allowed_internship = true;
				break;
			}
		}
		if (!$allowed_internship) {
			$message = 'Bạn không có quyền ghi nhật ký cho thực tập này.';
		} else {
			$stmt = $con->prepare('INSERT INTO internship_logs (internship_id, log_date, content, result, created_at) VALUES (?, ?, ?, ?, NOW())');
			$stmt->bind_param('isss', $internship_id, $log_date, $content, $result);
			if ($stmt->execute()) {
				$message = 'Đã lưu nhật ký thực tập thành công.';
				$_POST = [];
				$log_date = $content = $result = '';
			} else {
				$message = 'Không thể lưu nhật ký. Vui lòng thử lại.';
			}
			$stmt->close();
		}
	}
}

$logs = [];
if ($student_id) {
	$log_sql = 'SELECT l.id, l.log_date, l.content, l.result, l.created_at, p.title, c.company_name
			FROM internship_logs l
			JOIN internships i ON i.id = l.internship_id
			JOIN internship_posts p ON p.id = i.post_id
			JOIN companies c ON c.id = i.company_id
			WHERE i.student_id = ? AND i.status <> "completed"
			ORDER BY l.log_date DESC, l.created_at DESC';
	$stmt = $con->prepare($log_sql);
	$stmt->bind_param('i', $student_id);
	$stmt->execute();
	$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
}

function e($value) {
	return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1">
	<title>Nhật ký thực tập</title>
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
	<header class="header">
		<div class="wrapper">
			<h1>Nhật ký thực tập</h1>
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
			<h2>Công việc và kết quả trong quá trình thực tập</h2>
		</div>

		<?php if (!$student_id): ?>
			<div class="block">
				<p>Bạn chưa có thực tập nào được chấp nhận để ghi nhật ký.</p>
			</div>
		<?php else: ?>
			<div class="block">
				<?php if ($message): ?>
					<p class="upload-message"><?=e($message)?></p>
				<?php endif; ?>
				<form method="post" class="form-stack">
					<div class="form-row">
						<label class="form-label">Thực tập</label>
						<select class="form-input" name="internship_id" required>
							<option value="">-- Chọn thực tập --</option>
							<?php foreach ($student_internships as $internship): ?>
								<option value="<?=e($internship['id'])?>"><?=e($internship['title'])?> · <?=e($internship['company_name'])?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="form-row">
						<label class="form-label">Ngày</label>
						<input class="form-input" type="date" name="log_date" value="<?=e($_POST['log_date'] ?? date('Y-m-d'))?>" required>
					</div>

					<div class="form-row form-row-full">
						<label class="form-label">Nội dung công việc</label>
						<textarea class="form-input" name="content" rows="8" placeholder="- Tìm hiểu hệ thống&#10;- Cài đặt môi trường PHP&#10;- Làm quen với Git" required><?=e($_POST['content'] ?? '')?></textarea>
					</div>

					<div class="form-row form-row-full">
						<label class="form-label">Kết quả</label>
						<textarea class="form-input" name="result" rows="3" placeholder="Hoàn thành" required><?=e($_POST['result'] ?? '')?></textarea>
					</div>

					<div class="form-row form-row-full">
						<button class="btn" type="submit">Lưu nhật ký</button>
					</div>
				</form>
			</div>

			<div class="block">
				<h3>Lịch sử nhật ký</h3>
				<?php if (empty($logs)): ?>
					<p>Chưa có nhật ký nào được lưu.</p>
				<?php else: ?>
					<?php foreach ($logs as $log): ?>
						<div class="block" style="margin-bottom: 12px;">
							<p><strong><?=e(date('d/m/Y', strtotime($log['log_date'])))?></strong> · <?=e($log['company_name'])?> · <?=e($log['title'])?></p>
							<p><strong>Nội dung công việc:</strong><br><?=nl2br(e($log['content']))?></p>
							<p><strong>Kết quả:</strong> <?=nl2br(e($log['result']))?></p>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</body>
</html>
