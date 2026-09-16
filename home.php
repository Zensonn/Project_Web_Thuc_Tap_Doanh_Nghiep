<?php
// We need to use sessions, so you should always initialize sessions using the below function
session_start();
// If the user is not logged in, redirect to the login page
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
		$stmt = $con->prepare('SELECT COUNT(*), SUM(status = "accepted") FROM applications WHERE student_id = ?');
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
					<a href="home.php">Home</a>
					<a href="profile.php">Profile</a>
					<?php if (($_SESSION['account_role'] ?? '') === 'student'): ?>
						<a href="internships.php">Tìm thực tập</a>
						<a href="internship-logs.php">Nhật ký</a>
					<?php endif; ?>
					<?php if (($_SESSION['account_role'] ?? '') === 'company'): ?>
						<a href="company/dashboard.php">Cổng doanh nghiệp</a>
					<?php endif; ?>
					<?php if (($_SESSION['account_role'] ?? '') === 'admin'): ?>
						<a href="admin.php">Quản trị</a>
					<?php endif; ?>
					<a href="logout.php">
						<svg width="12" height="12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/></svg>
						Logout
					</a>
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