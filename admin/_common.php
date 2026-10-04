<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: ../index.php');
	exit;
}
if (($_SESSION['account_role'] ?? '') !== 'admin') {
	http_response_code(403);
	exit('Bạn không có quyền truy cập khu vực quản trị.');
}

$con = mysqli_connect('localhost', 'root', '', 'phplogin');
if (!$con) {
	exit('Không thể kết nối cơ sở dữ liệu: ' . mysqli_connect_error());
}

function admin_e($value) {
	return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function admin_header($title) {
	echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1"><title>' . admin_e($title) . '</title><link href="../css/style.css?v=' . filemtime(__DIR__ . '/../css/style.css') . '" rel="stylesheet" type="text/css"></head><body><header class="header admin-header"><div class="wrapper admin-header-wrapper"><h1>Cổng quản trị</h1><nav class="menu"><a href="index.php">Tài khoản</a><a href="management.php?type=students">Sinh viên</a><a href="management.php?type=companies">Doanh nghiệp</a><a href="management.php?type=lecturers">Giảng viên</a><a href="management.php?type=posts">Vị trí</a><a href="majors.php">Ngành học</a><a href="statistics.php">Thống kê</a><a href="reports.php">Báo cáo</a><a href="../logout.php">Đăng xuất</a></nav></div></header><div class="content admin-content">';
}
function admin_footer() {
	echo '</div></body></html>';
}
