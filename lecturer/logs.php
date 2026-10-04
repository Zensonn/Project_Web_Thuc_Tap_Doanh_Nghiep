<?php
require __DIR__ . '/_common.php';

$stmt = $con->prepare('ALTER TABLE internship_logs ADD COLUMN IF NOT EXISTS log_date date DEFAULT NULL');
$stmt->execute();
$stmt->close();
$stmt = $con->prepare('ALTER TABLE internship_logs ADD COLUMN IF NOT EXISTS content text DEFAULT NULL');
$stmt->execute();
$stmt->close();
$stmt = $con->prepare('ALTER TABLE internship_logs ADD COLUMN IF NOT EXISTS result text DEFAULT NULL');
$stmt->execute();
$stmt->close();

$user_id = (int)$_SESSION['account_id'];
$stmt = $con->prepare('SELECT l.id FROM lecturers l WHERE l.user_id=?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($lecturer_id);
$has_lecturer = $stmt->fetch();
$stmt->close();
$logs = [];
if ($has_lecturer) {
	$stmt = $con->prepare('SELECT il.log_date, il.content, il.result, il.created_at, s.full_name AS student_name, s.student_code, c.company_name, p.title FROM internship_logs il JOIN internships i ON i.id=il.internship_id JOIN students s ON s.id=i.student_id JOIN companies c ON c.id=i.company_id JOIN internship_posts p ON p.id=i.post_id WHERE i.lecturer_id=? AND i.status NOT IN ("completed", "cancelled") ORDER BY il.log_date DESC, il.created_at DESC');
	$stmt->bind_param('i', $lecturer_id);
	$stmt->execute();
	$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
}
lecturer_header('Nhật ký sinh viên');
?>
<div class="page-title"><h2>Nhật ký thực tập của sinh viên</h2><p>Các nhật ký thuộc sinh viên do bạn hướng dẫn.</p></div>
<div class="block">
	<?php if (!$has_lecturer || empty($logs)): ?>
		<p>Chưa có nhật ký thực tập nào.</p>
	<?php else: foreach ($logs as $log): ?>
		<div class="block" style="margin-bottom: 12px;">
			<p><strong><?=lecturer_e($log['student_name'])?></strong> (<?=lecturer_e($log['student_code'])?>) · <?=lecturer_e($log['company_name'])?> · <?=lecturer_e($log['title'])?></p>
			<p><strong>Ngày:</strong> <?=lecturer_e($log['log_date'] ? date('d/m/Y', strtotime($log['log_date'])) : date('d/m/Y', strtotime($log['created_at'])))?></p>
			<p><strong>Nội dung công việc:</strong><br><?=nl2br(lecturer_e($log['content']))?></p>
			<p><strong>Kết quả:</strong><br><?=nl2br(lecturer_e($log['result']))?></p>
		</div>
	<?php endforeach; endif; ?>
</div>
<?php lecturer_footer(); ?>
