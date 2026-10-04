<?php
session_start();

if (($_SESSION['account_role'] ?? '') === 'admin') {
	header('Location: admin/index.php');
	exit;
}

if (!isset($_SESSION['account_loggedin'])) {
	header('Location: index.php');
	exit;
}

if (($_SESSION['account_role'] ?? '') !== 'admin') {
	http_response_code(403);
	exit('Bạn không có quyền truy cập trang này.');
}

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (!$con) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$allowed_roles = ['student', 'company', 'lecturer', 'admin'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['account_id'], $_POST['role'])) {
	$account_id = filter_input(INPUT_POST, 'account_id', FILTER_VALIDATE_INT);
	$role = $_POST['role'];
	if ($account_id && in_array($role, $allowed_roles, true)) {
		$stmt = $con->prepare('UPDATE accounts SET role = ? WHERE id = ?');
		$stmt->bind_param('si', $role, $account_id);
		$stmt->execute();
		$stmt->close();
	}
}

$accounts = $con->query('SELECT id, username, email, role, registered FROM accounts ORDER BY id ASC');
?>
<!DOCTYPE html>
<html lang="vi">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Quản trị tài khoản</title>
		<link href="css/style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<header class="header">
			<div class="wrapper">
				<h1>Quản trị tài khoản</h1>
				<nav class="menu">
					<a href="home.php">Home</a>
					<a href="profile.php">Profile</a>
					<a href="logout.php">Đăng xuất</a>
				</nav>
			</div>
		</header>
		<div class="content">
			<div class="page-title">
				<div class="wrap">
					<h2>Danh sách tài khoản</h2>
				</div>
			</div>
			<div class="block">
				<table>
					<thead>
						<tr><th>ID</th><th>Tên đăng nhập</th><th>Email</th><th>Vai trò</th><th>Ngày đăng ký</th><th></th></tr>
					</thead>
					<tbody>
					<?php while ($account = $accounts->fetch_assoc()): ?>
						<tr>
							<td><?=htmlspecialchars($account['id'], ENT_QUOTES)?></td>
							<td><?=htmlspecialchars($account['username'], ENT_QUOTES)?></td>
							<td><?=htmlspecialchars($account['email'], ENT_QUOTES)?></td>
							<td>
								<form method="post">
									<input type="hidden" name="account_id" value="<?=htmlspecialchars($account['id'], ENT_QUOTES)?>">
									<select name="role">
										<option value="student" <?=$account['role'] === 'student' ? 'selected' : ''?>>Sinh viên</option>
										<option value="company" <?=$account['role'] === 'company' ? 'selected' : ''?>>Doanh nghiệp</option>
										<option value="lecturer" <?=$account['role'] === 'lecturer' ? 'selected' : ''?>>Giảng viên</option>
										<option value="admin" <?=$account['role'] === 'admin' ? 'selected' : ''?>>Admin</option>
									</select>
									<button type="submit">Lưu</button>
								</form>
							</td>
							<td><?=htmlspecialchars($account['registered'], ENT_QUOTES)?></td>
							<td></td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>
<?php $con->close(); ?>
