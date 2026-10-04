<?php
require __DIR__ . '/_common.php';
$message = null;
$user_id = (int)$_SESSION['account_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$lecturer_code = trim($_POST['lecturer_code'] ?? '');
	$full_name = trim($_POST['full_name'] ?? '');
	$department = trim($_POST['department'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$academic_title = trim($_POST['academic_title'] ?? '');
	if ($lecturer_code === '' || $full_name === '') {
		$message = 'Mã giảng viên và họ tên là bắt buộc.';
	} else {
		$stmt = $con->prepare('INSERT INTO lecturers (user_id, lecturer_code, full_name, department, phone, academic_title) VALUES (?, ?, ?, NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, "")) ON DUPLICATE KEY UPDATE lecturer_code=VALUES(lecturer_code), full_name=VALUES(full_name), department=VALUES(department), phone=VALUES(phone), academic_title=VALUES(academic_title)');
		$stmt->bind_param('isssss', $user_id, $lecturer_code, $full_name, $department, $phone, $academic_title);
		if ($stmt->execute()) { header('Location: profile.php?saved=1'); exit; }
		$message = 'Không thể lưu hồ sơ. Mã giảng viên có thể đã tồn tại.';
		$stmt->close();
	}
}
if (isset($_GET['saved'])) $message = 'Đã cập nhật hồ sơ giảng viên.';
$stmt = $con->prepare('SELECT lecturer_code, full_name, department, phone, academic_title FROM lecturers WHERE user_id=?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc() ?: ['lecturer_code'=>'', 'full_name'=>'', 'department'=>'', 'phone'=>'', 'academic_title'=>''];
$stmt->close();
lecturer_header('Hồ sơ giảng viên');
?>
<div class="page-title"><h2>Hồ sơ giảng viên</h2><p>Cập nhật thông tin để sinh viên có thể đăng ký bạn hướng dẫn.</p></div>
<div class="block">
	<?php if ($message): ?><p class="upload-message"><?=lecturer_e($message)?></p><?php endif; ?>
	<form class="profile-form" method="post">
		<label>Mã giảng viên<input class="form-input" name="lecturer_code" value="<?=lecturer_e($profile['lecturer_code'])?>" required></label>
		<label>Họ tên<input class="form-input" name="full_name" value="<?=lecturer_e($profile['full_name'])?>" required></label>
		<label>Khoa / bộ môn<input class="form-input" name="department" value="<?=lecturer_e($profile['department'])?>"></label>
		<label>Học hàm / học vị<input class="form-input" name="academic_title" value="<?=lecturer_e($profile['academic_title'])?>"></label>
		<label>Số điện thoại<input class="form-input" name="phone" value="<?=lecturer_e($profile['phone'])?>"></label>
		<div class="profile-form-actions"><button class="btn" type="submit">Lưu hồ sơ</button></div>
	</form>
</div>
<?php lecturer_footer(); ?>
