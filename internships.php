<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) { header('Location: index.php'); exit; }
if (($_SESSION['account_role'] ?? '') !== 'student') { http_response_code(403); exit('Trang này chỉ dành cho sinh viên.'); }
$con = mysqli_connect('localhost', 'root', '', 'phplogin');
if (!$con) exit('Không thể kết nối cơ sở dữ liệu: ' . mysqli_connect_error());
$query = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');
$employment_type = $_GET['employment_type'] ?? '';
$sql = 'SELECT p.id, p.title, p.description, p.location, p.employment_type, p.quantity, p.deadline, c.company_name FROM internship_posts p JOIN companies c ON c.id=p.company_id WHERE p.status="published" AND (p.deadline IS NULL OR p.deadline >= CURDATE())';
$params = [];
$types = '';
if ($query !== '') { $sql .= ' AND (p.title LIKE ? OR p.description LIKE ? OR p.requirements LIKE ? OR c.company_name LIKE ?)'; $search = '%' . $query . '%'; $params = [$search, $search, $search, $search]; $types .= 'ssss'; }
if ($location !== '') { $sql .= ' AND p.location LIKE ?'; $params[] = '%' . $location . '%'; $types .= 's'; }
if (in_array($employment_type, ['full_time','part_time','remote','hybrid'], true)) { $sql .= ' AND p.employment_type = ?'; $params[] = $employment_type; $types .= 's'; }
$sql .= ' ORDER BY p.created_at DESC';
$stmt = $con->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$posts = $stmt->get_result();
$type_labels = ['full_time'=>'Toàn thời gian','part_time'=>'Bán thời gian','remote'=>'Từ xa','hybrid'=>'Kết hợp'];
function e($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1"><title>Tìm kiếm thực tập</title><link href="css/style.css" rel="stylesheet" type="text/css"></head>
<body>
<header class="header"><div class="wrapper"><h1>Cơ hội thực tập</h1><nav class="menu"><a href="home.php">Trang chủ</a><a href="student-portal.php">Quản lý thực tập</a><a href="internships.php">Tìm thực tập</a><a href="internship-logs.php">Nhật ký</a><a href="profile.php">Hồ sơ</a><a href="logout.php">Đăng xuất</a></nav></div></header>
<div class="content">
	<div class="page-title"><h2>Tìm kiếm thực tập</h2></div>
	<div class="block"><form class="search-form" method="get"><input class="form-input" name="q" placeholder="Vị trí hoặc công ty" value="<?=e($query)?>"><input class="form-input" name="location" placeholder="Địa điểm" value="<?=e($location)?>"><select class="form-input" name="employment_type"><option value="">Tất cả hình thức</option><?php foreach ($type_labels as $value => $label): ?><option value="<?=e($value)?>" <?=$employment_type === $value ? 'selected' : ''?>><?=e($label)?></option><?php endforeach; ?></select><button class="btn" type="submit">Tìm kiếm</button></form></div>
	<div class="internship-list">
		<?php if ($posts->num_rows === 0): ?><div class="block"><p>Không tìm thấy vị trí thực tập phù hợp.</p></div>
		<?php else: while ($post = $posts->fetch_assoc()): ?>
			<article class="block internship-card"><div><h3><?=e($post['title'])?></h3><p class="company-name"><?=e($post['company_name'])?></p><p><?=e($post['description'])?></p><div class="internship-meta"><span><?=e($post['location'] ?: 'Linh hoạt')?></span><span><?=e($type_labels[$post['employment_type']] ?? $post['employment_type'])?></span><span><?=e($post['quantity'])?> vị trí</span><?php if ($post['deadline']): ?><span>Hạn <?=e(date('d/m/Y', strtotime($post['deadline'])))?></span><?php endif; ?></div></div><a class="btn btn-secondary" href="internship-detail.php?id=<?=e($post['id'])?>">Xem chi tiết</a></article>
		<?php endwhile; endif; ?>
	</div>
</div>
</body>
</html>
