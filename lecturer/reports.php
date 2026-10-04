<?php
require __DIR__ . '/_common.php';
$user_id = (int)$_SESSION['account_id'];
$message = null;
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$report_id = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
	$score = trim($_POST['score'] ?? '');
	$comments = trim($_POST['comments'] ?? '');
	if (!$report_id || $score === '' || !is_numeric($score) || (float)$score < 0 || (float)$score > 10 || $comments === '') {
		$message = 'Vui lòng nhập điểm từ 0 đến 10 và nhận xét cho báo cáo.';
		$message_type = 'error';
	} else {
		$stmt = $con->prepare('SELECT r.id, r.internship_id FROM reports r JOIN internships i ON i.id = r.internship_id JOIN lecturers l ON l.id = i.lecturer_id WHERE r.id = ? AND l.user_id = ?');
		$stmt->bind_param('ii', $report_id, $user_id);
		$stmt->execute();
		$report = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		if (!$report) {
			$message = 'Bạn không có quyền nhận xét báo cáo này.';
			$message_type = 'error';
		} else {
			$score_value = number_format((float)$score, 2, '.', '');
			$stmt = $con->prepare('INSERT INTO evaluations (internship_id, report_id, evaluator_user_id, evaluator_type, score, comments, status) VALUES (?, ?, ?, "lecturer", ?, ?, "submitted") ON DUPLICATE KEY UPDATE report_id = VALUES(report_id), score = VALUES(score), comments = VALUES(comments), status = "submitted"');
			$stmt->bind_param('iiiss', $report['internship_id'], $report_id, $user_id, $score_value, $comments);
			if ($stmt->execute()) {
				$stmt = $con->prepare('UPDATE reports SET status="reviewed", reviewed_at=NOW() WHERE id=?');
				$stmt->bind_param('i', $report_id);
				$stmt->execute();
				$stmt->close();
				$message = 'Đã lưu nhận xét và đánh giá báo cáo.';
			} else {
				$message = 'Không thể lưu đánh giá. Vui lòng thử lại.';
				$message_type = 'error';
			}
			$stmt->close();
		}
	}
}

$stmt = $con->prepare('SELECT r.id, r.title, r.content, r.file_url, r.report_type, r.status, r.submitted_at, i.id AS internship_id, s.full_name AS student_name, s.student_code, c.company_name, p.title AS position_title, e.score, e.comments FROM reports r JOIN internships i ON i.id = r.internship_id JOIN lecturers l ON l.id = i.lecturer_id JOIN students s ON s.id = i.student_id JOIN companies c ON c.id = i.company_id JOIN internship_posts p ON p.id = i.post_id LEFT JOIN evaluations e ON e.report_id = r.id AND e.evaluator_user_id = ? AND e.evaluator_type = "lecturer" WHERE l.user_id = ? ORDER BY r.submitted_at DESC, r.id DESC');
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$reports = $stmt->get_result();
$stmt->close();
$report_types = ['weekly' => 'Báo cáo tuần', 'midterm' => 'Báo cáo giữa kỳ', 'final' => 'Báo cáo cuối kỳ', 'other' => 'Khác'];
$report_status = ['draft' => 'Bản nháp', 'submitted' => 'Chờ nhận xét', 'reviewed' => 'Đã nhận xét', 'needs_revision' => 'Cần chỉnh sửa'];
function report_e($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
lecturer_header('Báo cáo và đánh giá');
?>
<div class="page-title"><h2>Báo cáo và đánh giá</h2><p>Xem báo cáo, nhận xét và đánh giá kết quả của sinh viên được phân công.</p></div>
<div class="block lecturer-reports">
	<?php if ($message): ?><p class="upload-message <?=report_e($message_type)?>"><?=report_e($message)?></p><?php endif; ?>
	<?php if ($reports->num_rows === 0): ?><p>Chưa có báo cáo nào từ sinh viên.</p><?php else: ?><div class="lecturer-report-list"><?php while ($report = $reports->fetch_assoc()): ?><article class="lecturer-report-card"><div class="lecturer-report-heading"><div><h3><?=report_e($report['title'])?></h3><p><?=report_e($report['student_name'])?> (<?=report_e($report['student_code'])?>) · <?=report_e($report['company_name'])?> · <?=report_e($report['position_title'])?></p></div><span class="status-badge"><?=report_e($report_status[$report['status']] ?? $report['status'])?></span></div><div class="lecturer-report-meta"><span><?=report_e($report_types[$report['report_type']] ?? $report['report_type'])?></span><span><?=report_e($report['submitted_at'] ? date('d/m/Y H:i', strtotime($report['submitted_at'])) : 'Chưa nộp')?></span><?php if ($report['file_url']): ?><a href="../uploads/reports/<?=rawurlencode($report['file_url'])?>" download>Tải báo cáo</a><?php endif; ?></div><?php if ($report['content']): ?><div class="lecturer-report-content"><?=nl2br(report_e($report['content']))?></div><?php endif; ?><form class="lecturer-report-review" method="post"><input type="hidden" name="report_id" value="<?=report_e($report['id'])?>"><label>Điểm đánh giá<input class="form-input" type="number" name="score" min="0" max="10" step="0.01" value="<?=report_e($report['score'])?>" required></label><label class="review-comment">Nhận xét<textarea class="form-input" name="comments" rows="3" required placeholder="Nhận xét và hướng dẫn sinh viên chỉnh sửa..."><?=report_e($report['comments'])?></textarea></label><button class="btn" type="submit">Lưu nhận xét</button></form></article><?php endwhile; ?></div><?php endif; ?>
</div>
<?php lecturer_footer(); ?>
