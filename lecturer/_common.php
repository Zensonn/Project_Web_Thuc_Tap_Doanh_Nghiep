<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: ../index.php');
	exit;
}
if (($_SESSION['account_role'] ?? '') !== 'lecturer') {
	http_response_code(403);
	exit('Bạn không có quyền truy cập khu vực giảng viên.');
}

$con = mysqli_connect('localhost', 'root', '', 'phplogin');
if (!$con) {
	exit('Không thể kết nối cơ sở dữ liệu: ' . mysqli_connect_error());
}

function lecturer_e($value) {
	return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function lecturer_header($title) {
	echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1"><title>' . lecturer_e($title) . '</title><link href="../css/style.css?v=' . filemtime(__DIR__ . '/../css/style.css') . '" rel="stylesheet" type="text/css"></head><body><header class="header lecturer-header"><div class="wrapper lecturer-header-wrapper"><h1>Cổng giảng viên</h1><nav class="menu"><a href="index.php">Tổng quan</a><a href="profile.php">Hồ sơ</a><a href="internships.php">Sinh viên thực tập</a><a href="logs.php">Nhật ký sinh viên</a><a href="reports.php">Báo cáo & đánh giá</a><a href="../logout.php">Đăng xuất</a></nav></div></header><div class="content lecturer-content">';
}
function lecturer_footer() {
	echo '</div></body></html>';
}
