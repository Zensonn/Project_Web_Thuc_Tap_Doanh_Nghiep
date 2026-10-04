<?php
require __DIR__ . '/_common.php';
$student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
$user_id = (int)$_SESSION['account_id'];
if (!$student_id) { header('Location: internships.php'); exit; }
$stmt = $con->prepare('SELECT s.* FROM students s WHERE s.id = ? AND EXISTS (SELECT 1 FROM internships i JOIN lecturers l ON l.id = i.lecturer_id WHERE i.student_id = s.id AND l.user_id = ?)');
$stmt->bind_param('ii', $student_id, $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$student) { http_response_code(404); exit('Không tìm thấy sinh viên được phân công.'); }
$gender_labels = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'];
$birth_date = $student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : null;
$class_and_cohort = trim(($student['class_name'] ?? '') . ' ' . ($student['cohort'] ?? ''));
function profile_e($value) { return ($value !== null && $value !== '') ? lecturer_e($value) : 'Chưa cập nhật'; }
lecturer_header('Thông tin sinh viên');
?>
<div class="page-title"><h2>Thông tin sinh viên</h2><p><?=lecturer_e($student['full_name'])?> · <?=lecturer_e($student['student_code'])?></p></div>
<div class="block lecturer-profile">
	<div class="profile-grid">
		<div class="profile-detail"><strong>Họ tên</strong><?=profile_e($student['full_name'])?></div>
		<div class="profile-detail"><strong>Mã sinh viên</strong><?=profile_e($student['student_code'])?></div>
		<div class="profile-detail"><strong>Ngày sinh</strong><?=profile_e($birth_date)?></div>
		<div class="profile-detail"><strong>Giới tính</strong><?=profile_e($gender_labels[$student['gender']] ?? $student['gender'])?></div>
		<div class="profile-detail"><strong>Số điện thoại</strong><?=profile_e($student['phone'])?></div>
		<div class="profile-detail"><strong>Ngành học</strong><?=profile_e($student['major'])?></div>
		<div class="profile-detail"><strong>Lớp / khóa</strong><?=profile_e($class_and_cohort)?></div>
		<div class="profile-detail"><strong>GPA</strong><?=profile_e($student['gpa'])?></div>
		<div class="profile-detail profile-detail-wide"><strong>Kỹ năng</strong><?=nl2br(profile_e($student['skills']))?></div>
	</div>
	<p><a class="btn btn-secondary" href="internships.php">Quay lại danh sách</a><?php if ($student['cv_file']): ?> <a class="btn" href="../uploads/cv/<?=rawurlencode($student['cv_file'])?>" download>Tải CV</a><?php endif; ?></p>
</div>
<?php lecturer_footer(); ?>
