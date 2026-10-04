<?php
// Khởi tạo session để kiểm tra đăng nhập.
session_start();
// Nếu chưa đăng nhập thì chuyển về trang đăng nhập.
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: index.php');
	exit;
}
$role_labels = [
	'student' => 'Sinh viên',
	'company' => 'Doanh nghiệp',
	'lecturer' => 'Giảng viên',
	'admin' => 'Admin'
];
$account_role = $_SESSION['account_role'] ?? 'student';
if ($account_role === 'admin') {
	header('Location: admin/index.php');
	exit;
}
if ($account_role === 'lecturer') {
	header('Location: lecturer/index.php');
	exit;
}
if ($account_role === 'company') {
	header('Location: company/dashboard.php');
	exit;
}
$student_dashboard = null;
if ($account_role === 'student') {
	$con = mysqli_connect('localhost', 'root', '', 'phplogin');
	if (!$con) {
		exit('Không thể kết nối cơ sở dữ liệu: ' . mysqli_connect_error());
	}
	$user_id = (int)$_SESSION['account_id'];
	$student_id = null;
	$stmt = $con->prepare('SELECT id FROM students WHERE user_id = ?');
	$stmt->bind_param('i', $user_id);
	$stmt->execute();
	$stmt->bind_result($student_id);
	$stmt->fetch();
	$stmt->close();
	$available_positions = 0;
	$stmt = $con->prepare('SELECT COUNT(*) FROM internship_posts WHERE status = "published" AND (deadline IS NULL OR deadline >= CURDATE())');
	$stmt->execute();
	$stmt->bind_result($available_positions);
	$stmt->fetch();
	$stmt->close();
	$applied_count = 0;
	$accepted_count = 0;
	if ($student_id) {
		$stmt = $con->prepare('SELECT COUNT(*), SUM(a.status = "accepted") FROM applications a JOIN internship_posts p ON p.id = a.post_id WHERE a.student_id = ? AND p.status <> "cancelled"');
		$stmt->bind_param('i', $student_id);
		$stmt->execute();
		$stmt->bind_result($applied_count, $accepted_count);
		$stmt->fetch();
		$stmt->close();
	}
	$accepted_count = (int)$accepted_count;
	$latest_posts = $con->query('SELECT p.id, p.title, c.company_name, p.location, p.employment_type FROM internship_posts p JOIN companies c ON c.id = p.company_id WHERE p.status = "published" AND (p.deadline IS NULL OR p.deadline >= CURDATE()) ORDER BY p.created_at DESC LIMIT 5');
	$student_name = $_SESSION['account_name'];
	$stmt = $con->prepare('SELECT full_name FROM students WHERE id = ?');
	if ($student_id) {
		$stmt->bind_param('i', $student_id);
		$stmt->execute();
		$stmt->bind_result($profile_name);
		if ($stmt->fetch() && $profile_name) $student_name = $profile_name;
	}
	$stmt->close();
	$student_dashboard = compact('available_positions', 'applied_count', 'accepted_count', 'latest_posts', 'student_name');
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Home</title>
		<link href="css/style.css" rel="stylesheet" type="text/css">
	</head>
    <body>

		<header class="header">

			<div class="wrapper">

				<h1>Quản Lý Sinh Viên</h1>
				
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
					<h2><?= $account_role === 'student' ? 'Dashboard sinh viên' : 'Trang chủ' ?></h2>
					<p>Xin chào, <?=htmlspecialchars($student_dashboard['student_name'] ?? $_SESSION['account_name'], ENT_QUOTES)?>!</p>
					<p>Vai trò: <?=htmlspecialchars($role_labels[$account_role] ?? 'Sinh viên', ENT_QUOTES)?></p>
				</div>
			</div>

			<?php if ($student_dashboard): ?>
				<div class="dashboard-stats">
					<div class="block"><strong><?=htmlspecialchars($student_dashboard['available_positions'], ENT_QUOTES)?></strong><span>Vị trí</span></div>
					<div class="block"><strong><?=htmlspecialchars($student_dashboard['applied_count'], ENT_QUOTES)?></strong><span>Đã ứng tuyển</span></div>
					<div class="block"><strong><?=htmlspecialchars($student_dashboard['accepted_count'], ENT_QUOTES)?></strong><span>Đã được nhận</span></div>
				</div>
				<div class="block dashboard-opportunities">
					<h3>Cơ hội thực tập mới</h3>
					<?php if ($student_dashboard['latest_posts']->num_rows === 0): ?>
						<p>Chưa có cơ hội thực tập mới.</p>
					<?php else: while ($post = $student_dashboard['latest_posts']->fetch_assoc()): ?>
						<div class="opportunity-row"><div><strong><?=htmlspecialchars($post['title'], ENT_QUOTES)?></strong><span><?=htmlspecialchars($post['company_name'], ENT_QUOTES)?> · <?=htmlspecialchars($post['location'] ?: 'Linh hoạt', ENT_QUOTES)?></span></div><a class="btn btn-secondary" href="internship-detail.php?id=<?=htmlspecialchars($post['id'], ENT_QUOTES)?>">Xem</a></div>
					<?php endwhile; endif; ?>
				</div>
			<?php else: ?>
				<div class="block"><p>This is the home page. You are logged in!</p></div>
			<?php endif; ?>
			
		</div>

    </body>
</html>