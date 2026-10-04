<?php
require __DIR__ . '/_common.php';
$student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
if (!$company || !$student_id) { header('Location: applications.php'); exit; }

$stmt = $con->prepare('SELECT s.id, s.full_name, s.student_code, s.date_of_birth, s.gender, s.phone, s.address, s.major, s.class_name, s.cohort, s.faculty, s.gpa, s.skills, s.cv_file FROM students s WHERE s.id = ? AND EXISTS (SELECT 1 FROM applications a JOIN internship_posts p ON p.id = a.post_id WHERE a.student_id = s.id AND p.company_id = ?)');
$stmt->bind_param('ii', $student_id, $company['id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$student) { http_response_code(404); exit('Không tìm thấy hồ sơ sinh viên.'); }

$gender_labels = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'];
function student_value($value) { return ($value !== null && $value !== '') ? e($value) : 'Chưa cập nhật'; }
company_header('Hồ sơ sinh viên');
?>
<div class="page-title"><h2>Hồ sơ sinh viên</h2><p><?=e($student['full_name'])?> · <?=e($student['student_code'])?></p></div>
<div class="block company-student-profile">
	<div class="profile-grid">
		<div class="profile-detail"><strong>Họ tên</strong><?=student_value($student['full_name'])?></div>
		<div class="profile-detail"><strong>Mã sinh viên</strong><?=student_value($student['student_code'])?></div>
		<div class="profile-detail"><strong>Ngày sinh</strong><?=student_value($student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : null)?></div>
		<div class="profile-detail"><strong>Giới tính</strong><?=student_value($gender_labels[$student['gender']] ?? $student['gender'])?></div>
		<div class="profile-detail"><strong>Số điện thoại</strong><?=student_value($student['phone'])?></div>
		<div class="profile-detail"><strong>Ngành học</strong><?=student_value($student['major'])?></div>
		<div class="profile-detail"><strong>Lớp</strong><?=student_value($student['class_name'])?></div>
		<div class="profile-detail"><strong>Khóa</strong><?=student_value($student['cohort'])?></div>
		<div class="profile-detail"><strong>Khoa</strong><?=student_value($student['faculty'])?></div>
		<div class="profile-detail"><strong>GPA</strong><?=student_value($student['gpa'])?></div>
		<div class="profile-detail"><strong>Địa chỉ</strong><?=student_value($student['address'])?></div>
		<div class="profile-detail profile-detail-wide"><strong>Kỹ năng</strong><?=nl2br(student_value($student['skills']))?></div>
	</div>
	<div class="profile-form-actions"><a class="btn btn-secondary" href="applications.php">Quay lại danh sách ứng tuyển</a><?php if ($student['cv_file']): ?><a class="btn" href="../uploads/cv/<?=rawurlencode($student['cv_file'])?>" download>Tải CV</a><?php endif; ?></div>
</div>
<?php company_footer(); ?>
