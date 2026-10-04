<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: index.php');
	exit;
}
if (($_SESSION['account_role'] ?? '') !== 'student') {
	http_response_code(403);
	exit('Trang này chỉ dành cho sinh viên.');
}

$con = mysqli_connect('localhost', 'root', '', 'phplogin');
if (!$con) {
	exit('Không thể kết nối cơ sở dữ liệu: ' . mysqli_connect_error());
}
$con->set_charset('utf8mb4');
$stmt = $con->prepare('ALTER TABLE applications ADD COLUMN IF NOT EXISTS accepted_at timestamp NULL DEFAULT NULL AFTER reviewed_at');
$stmt->execute();
$stmt->close();
$user_id = (int)$_SESSION['account_id'];

$stmt = $con->prepare('SELECT id, full_name FROM students WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$student) {
	header('Location: edit-profile.php');
	exit;
}
$student_id = (int)$student['id'];
$message = null;
$message_type = 'success';
$report_types = ['weekly' => 'Báo cáo tuần', 'midterm' => 'Báo cáo giữa kỳ', 'final' => 'Báo cáo cuối kỳ', 'other' => 'Khác'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_report') {
	$internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
	$title = trim($_POST['title'] ?? '');
	$content = trim($_POST['content'] ?? '');
	$report_type = $_POST['report_type'] ?? 'weekly';
	$file_url = null;
	$allowed_types = array_keys($report_types);

	if (!$internship_id || $title === '' || !in_array($report_type, $allowed_types, true)) {
		$message = 'Vui lòng chọn kỳ thực tập, loại báo cáo và nhập tiêu đề.';
		$message_type = 'error';
	} elseif ($content === '' && (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] === UPLOAD_ERR_NO_FILE)) {
		$message = 'Báo cáo cần có nội dung hoặc file đính kèm.';
		$message_type = 'error';
	} else {
		$stmt = $con->prepare('SELECT id FROM internships WHERE id = ? AND student_id = ? AND status <> "cancelled"');
		$stmt->bind_param('ii', $internship_id, $student_id);
		$stmt->execute();
		$allowed_internship = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		if (!$allowed_internship) {
			$message = 'Bạn không có quyền nộp báo cáo cho kỳ thực tập này.';
			$message_type = 'error';
		} else {
			if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] !== UPLOAD_ERR_NO_FILE) {
				$file = $_FILES['report_file'];
				$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
				if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 10 * 1024 * 1024 || !in_array($extension, ['pdf', 'doc', 'docx'], true)) {
					$message = 'File báo cáo phải là PDF, DOC hoặc DOCX và không quá 10 MB.';
					$message_type = 'error';
				} else {
					$directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reports';
					if (!is_dir($directory)) mkdir($directory, 0755, true);
					$file_url = bin2hex(random_bytes(16)) . '.' . $extension;
					if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $file_url)) {
						$file_url = null;
						$message = 'Không thể lưu file báo cáo.';
						$message_type = 'error';
					}
				}
			}
			if ($message_type === 'success') {
				$stmt = $con->prepare('INSERT INTO reports (internship_id, student_id, title, content, file_url, report_type, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, "submitted", NOW())');
				$stmt->bind_param('iissss', $internship_id, $student_id, $title, $content, $file_url, $report_type);
				if ($stmt->execute()) {
					$message = 'Đã nộp báo cáo thực tập thành công.';
				} else {
					$message = 'Không thể nộp báo cáo. Vui lòng thử lại.';
					$message_type = 'error';
					if ($file_url) @unlink(__DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $file_url);
				}
				$stmt->close();
			}
		}
	}
}

$stmt = $con->prepare('SELECT a.id, a.status, a.applied_at, a.accepted_at, p.title, c.company_name FROM applications a JOIN internship_posts p ON p.id = a.post_id JOIN companies c ON c.id = p.company_id LEFT JOIN internships i ON i.application_id = a.id WHERE a.student_id = ? AND (i.id IS NULL OR i.status NOT IN ("completed", "cancelled")) ORDER BY a.applied_at DESC');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $con->prepare('SELECT i.id, i.start_date, i.end_date, i.status, i.company_supervisor, p.title, c.company_name FROM internships i JOIN internship_posts p ON p.id = i.post_id JOIN companies c ON c.id = i.company_id WHERE i.student_id = ? AND i.status NOT IN ("completed", "cancelled") ORDER BY i.start_date DESC, i.created_at DESC');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$internships = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$reports = [];
$evaluations = [];
if ($internships) {
	$stmt = $con->prepare('SELECT r.id, r.title, r.report_type, r.status, r.submitted_at, r.file_url, p.title AS position_title FROM reports r JOIN internships i ON i.id = r.internship_id JOIN internship_posts p ON p.id = i.post_id WHERE r.student_id = ? ORDER BY r.submitted_at DESC, r.id DESC');
	$stmt->bind_param('i', $student_id);
	$stmt->execute();
	$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
	$stmt = $con->prepare('SELECT e.score, e.comments, e.evaluator_type, e.status, p.title AS position_title FROM evaluations e JOIN internships i ON i.id = e.internship_id JOIN internship_posts p ON p.id = i.post_id WHERE i.student_id = ? AND e.status = "submitted" ORDER BY e.updated_at DESC');
	$stmt->bind_param('i', $student_id);
	$stmt->execute();
	$evaluations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
}
$status_labels = ['submitted' => 'Đã nộp', 'reviewing' => 'Đang xem xét', 'accepted' => 'Được chấp nhận', 'rejected' => 'Bị từ chối', 'withdrawn' => 'Đã rút'];
$internship_status = ['planned' => 'Sắp bắt đầu', 'ongoing' => 'Đang thực tập', 'completed' => 'Đã hoàn thành', 'cancelled' => 'Đã hủy'];
$report_status = ['submitted' => 'Đã nộp', 'reviewed' => 'Đã phản hồi', 'needs_revision' => 'Cần chỉnh sửa'];
$evaluation_types = ['company' => 'Doanh nghiệp', 'lecturer' => 'Giảng viên'];
function e($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
function progress_percent($start, $end, $status) {
	if ($status === 'completed') return 100;
	if (!$start || !$end) return $status === 'ongoing' ? 50 : 0;
	$start_time = strtotime($start);
	$end_time = strtotime($end);
	if ($end_time <= $start_time) return 0;
	return max(0, min(100, (int)round((time() - $start_time) / ($end_time - $start_time) * 100)));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1">
	<title>Cổng sinh viên</title>
	<link href="css/style.css?v=<?=filemtime(__DIR__ . '/css/style.css')?>" rel="stylesheet" type="text/css">
</head>
<body>
<header class="header"><div class="wrapper"><h1>Cổng sinh viên</h1><nav class="menu"><a href="home.php">Trang chủ</a><a href="student-portal.php">Quản lý thực tập</a><a href="internships.php">Tìm thực tập</a><a href="internship-logs.php">Nhật ký</a><a href="profile.php">Hồ sơ</a><a href="logout.php">Đăng xuất</a></nav></div></header>
<div class="content student-portal">
	<div class="page-title"><div><h2>Theo dõi toàn bộ hành trình thực tập tại đây</h2></div></div>
	<?php if ($message): ?><div class="notice <?=e($message_type)?>"><?=e($message)?></div><?php endif; ?>
	<section class="portal-section"><div class="section-heading"><div><h3>Đơn ứng tuyển</h3><p>Theo dõi trạng thái các vị trí bạn đã ứng tuyển.</p></div><a class="btn btn-secondary" href="internships.php">Tìm vị trí mới</a></div>
		<?php if (!$applications): ?><p>Chưa có đơn ứng tuyển nào.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Vị trí</th><th>Doanh nghiệp</th><th>Ngày nộp</th><th>Ngày được chấp nhận</th><th>Trạng thái</th></tr></thead><tbody><?php foreach ($applications as $application): ?><tr><td><?=e($application['title'])?></td><td><?=e($application['company_name'])?></td><td><?=e(date('d/m/Y', strtotime($application['applied_at'])))?></td><td><?=e($application['accepted_at'] ? date('d/m/Y', strtotime($application['accepted_at'])) : '-')?></td><td><span class="status-badge status-<?=e($application['status'])?>"><?=e($status_labels[$application['status']] ?? $application['status'])?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
	</section>
	<section class="portal-section"><div class="section-heading"><div><h3>Kỳ thực tập & tiến độ</h3><p>Xem thông tin doanh nghiệp, thời gian và tiến độ thực hiện.</p></div><a class="btn btn-secondary" href="internship-logs.php">Ghi nhật ký</a></div>
		<?php if (!$internships): ?><p>Chưa có kỳ thực tập được chấp nhận.</p><?php else: ?><div class="internship-progress-list"><?php foreach ($internships as $internship): $percent = progress_percent($internship['start_date'], $internship['end_date'], $internship['status']); ?><div class="progress-item"><div class="progress-item-header"><div><strong><?=e($internship['title'])?></strong><span><?=e($internship['company_name'])?><?php if ($internship['company_supervisor']): ?> · Người phụ trách: <?=e($internship['company_supervisor'])?><?php endif; ?></span></div><span class="status-badge status-<?=e($internship['status'])?>"><?=e($internship_status[$internship['status']] ?? $internship['status'])?></span></div><div class="progress-track"><span style="width: <?=$percent?>%"></span></div><div class="progress-caption"><span><?=e(date('d/m/Y', strtotime($internship['start_date'])))?></span><strong><?=$percent?>%</strong><span><?=e($internship['end_date'] ? date('d/m/Y', strtotime($internship['end_date'])) : 'Chưa xác định ngày kết thúc')?></span></div></div><?php endforeach; ?></div><?php endif; ?>
	</section>
	<section class="portal-section"><div class="section-heading"><div><h3>Nộp báo cáo thực tập</h3><p>Nộp nội dung hoặc file báo cáo cho kỳ thực tập đang tham gia.</p></div></div>
		<?php if (!$internships): ?><p>Bạn cần có một kỳ thực tập được chấp nhận trước khi nộp báo cáo.</p><?php else: ?><form class="report-form" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="submit_report"><label>Kỳ thực tập<select class="form-input" name="internship_id" required><option value="">-- Chọn kỳ thực tập --</option><?php foreach ($internships as $internship): if ($internship['status'] !== 'cancelled'): ?><option value="<?=e($internship['id'])?>"><?=e($internship['title'])?> · <?=e($internship['company_name'])?></option><?php endif; endforeach; ?></select></label><label>Loại báo cáo<select class="form-input" name="report_type"><?php foreach ($report_types as $value => $label): ?><option value="<?=e($value)?>"><?=e($label)?></option><?php endforeach; ?></select></label><label>Tiêu đề<input class="form-input" name="title" required maxlength="200"></label><label>Nội dung<textarea class="form-input" name="content" rows="6" placeholder="Tóm tắt nội dung báo cáo..."></textarea></label><label>File báo cáo <span>(PDF, DOC, DOCX, tối đa 10 MB)</span><input class="form-input" type="file" name="report_file" accept=".pdf,.doc,.docx"></label><button class="btn" type="submit">Nộp báo cáo</button></form><?php endif; ?>
		<?php if ($reports): ?><h4 class="subsection-title">Báo cáo đã nộp</h4><div class="table-wrap"><table><thead><tr><th>Tiêu đề</th><th>Loại</th><th>Ngày nộp</th><th>Trạng thái</th><th>File</th></tr></thead><tbody><?php foreach ($reports as $report): ?><tr><td><?=e($report['title'])?></td><td><?=e($report_types[$report['report_type']] ?? $report['report_type'])?></td><td><?=e($report['submitted_at'] ? date('d/m/Y H:i', strtotime($report['submitted_at'])) : '-')?></td><td><?=e($report_status[$report['status']] ?? $report['status'])?></td><td><?php if ($report['file_url']): ?><a href="uploads/reports/<?=rawurlencode($report['file_url'])?>" download>Tải xuống</a><?php else: ?>- <?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
	</section>
	<section class="portal-section"><div class="section-heading"><div><h3>Kết quả đánh giá</h3><p>Điểm số và nhận xét từ doanh nghiệp, giảng viên hướng dẫn.</p></div></div>
		<?php if (!$evaluations): ?><p>Chưa có kết quả đánh giá được công bố.</p><?php else: ?><div class="evaluation-list"><?php foreach ($evaluations as $evaluation): ?><article class="evaluation-item"><div><strong><?=e($evaluation_types[$evaluation['evaluator_type']] ?? $evaluation['evaluator_type'])?></strong><span><?=e($evaluation['position_title'])?></span></div><b><?=e($evaluation['score'] !== null ? $evaluation['score'] . '/10' : 'Chưa chấm điểm')?></b><p><?=nl2br(e($evaluation['comments'] ?: 'Chưa có nhận xét.'))?></p></article><?php endforeach; ?></div><?php endif; ?>
	</section>
</div>
</body>
</html>
