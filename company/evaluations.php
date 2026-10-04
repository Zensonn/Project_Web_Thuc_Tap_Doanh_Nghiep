<?php
require __DIR__ . '/_common.php';
if (!$company) { header('Location: profile.php'); exit; }
$message = null;
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
	$score = trim($_POST['score'] ?? '');
	$comments = trim($_POST['comments'] ?? '');
	if (!$internship_id || $score === '' || !is_numeric($score) || (float)$score < 0 || (float)$score > 10) {
		$message = 'Vui lòng nhập điểm từ 0 đến 10.';
		$message_type = 'error';
	} else {
		$stmt = $con->prepare('SELECT i.id FROM internships i WHERE i.id = ? AND i.company_id = ?');
		$stmt->bind_param('ii', $internship_id, $company['id']);
		$stmt->execute();
		$allowed_internship = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		if (!$allowed_internship) {
			$message = 'Bạn không có quyền đánh giá kỳ thực tập này.';
			$message_type = 'error';
		} else {
			$stmt = $con->prepare('SELECT id FROM evaluations WHERE internship_id = ? AND evaluator_user_id = ? AND evaluator_type = "company"');
			$stmt->bind_param('ii', $internship_id, $user_id);
			$stmt->execute();
			$existing_evaluation = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			if ($existing_evaluation) {
				$message = 'Đánh giá đã được lưu và không thể chỉnh sửa.';
				$message_type = 'error';
			} else {
				$score_value = number_format((float)$score, 2, '.', '');
				$stmt = $con->prepare('INSERT INTO evaluations (internship_id, evaluator_user_id, evaluator_type, score, comments, status) VALUES (?, ?, "company", ?, ?, "submitted")');
				$stmt->bind_param('iiss', $internship_id, $user_id, $score_value, $comments);
				if ($stmt->execute()) {
					$message = 'Đã lưu đánh giá sinh viên.';
				} else {
					$message = 'Không thể lưu đánh giá. Vui lòng thử lại.';
					$message_type = 'error';
				}
				$stmt->close();
			}
		}
	}
}

$stmt = $con->prepare('SELECT i.id, i.status, s.full_name AS student_name, s.student_code, p.title AS position_title, e.score, e.comments FROM internships i JOIN students s ON s.id = i.student_id JOIN internship_posts p ON p.id = i.post_id LEFT JOIN evaluations e ON e.internship_id = i.id AND e.evaluator_user_id = ? AND e.evaluator_type = "company" WHERE i.company_id = ? AND i.status <> "cancelled" ORDER BY i.start_date DESC, i.created_at DESC');
$stmt->bind_param('ii', $user_id, $company['id']);
$stmt->execute();
$internships = $stmt->get_result();
$stmt->close();
$internship_statuses = ['planned' => 'Chưa bắt đầu', 'ongoing' => 'Đang thực tập', 'completed' => 'Hoàn thành'];
company_header('Đánh giá sinh viên');
?>
<div class="page-title"><h2>Đánh giá sinh viên</h2><p>Chấm điểm và ghi nhận xét cho sinh viên đã thực tập tại doanh nghiệp.</p></div>
<div class="block">
	<?php if ($message): ?><p class="upload-message <?=e($message_type)?>"><?=e($message)?></p><?php endif; ?>
	<?php if ($internships->num_rows === 0): ?><p>Chưa có sinh viên thực tập để đánh giá.</p><?php else: ?><div class="company-evaluation-list"><?php while ($internship = $internships->fetch_assoc()): ?><form class="company-evaluation-card" method="post"><input type="hidden" name="internship_id" value="<?=e($internship['id'])?>"><div class="company-evaluation-heading"><div><strong><?=e($internship['student_name'])?></strong><span><?=e($internship['student_code'])?> · <?=e($internship['position_title'])?></span></div><span class="status-badge"><?=e($internship_statuses[$internship['status']] ?? $internship['status'])?></span></div><?php if ($internship['score'] === null): ?><div class="company-evaluation-fields"><label>Điểm số <input class="form-input" type="number" name="score" min="0" max="10" step="0.01" required></label><label class="evaluation-comment">Nhận xét<textarea class="form-input" name="comments" rows="3" placeholder="Nhận xét về kết quả thực tập..."></textarea></label></div><button class="btn" type="submit">Lưu đánh giá</button><?php else: ?><div class="saved-evaluation"><strong>Đánh giá đã lưu</strong><span>Điểm: <?=e(number_format((float)$internship['score'], 2))?>/10</span><p><?=nl2br(e($internship['comments'] ?: 'Chưa có nhận xét.'))?></p></div><?php endif; ?></form><?php endwhile; ?></div><?php endif; ?>
</div>
<?php company_footer(); ?>
