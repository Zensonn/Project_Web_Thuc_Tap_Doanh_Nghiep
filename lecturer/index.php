<?php
require __DIR__ . '/_common.php';

$user_id = (int)$_SESSION['account_id'];
$assigned_count = $active_count = $completed_count = 0;
$stmt = $con->prepare('SELECT COUNT(i.id), SUM(i.status IN ("planned", "ongoing")), SUM(i.status = "completed") FROM internships i JOIN lecturers l ON l.id = i.lecturer_id WHERE l.user_id = ? AND i.status <> "cancelled"');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($assigned_count, $active_count, $completed_count);
$stmt->fetch();
$stmt->close();
$active_count = (int)$active_count;
$completed_count = (int)$completed_count;
$report_submitted = $report_reviewed = $report_needs_revision = 0;
$stmt = $con->prepare('SELECT SUM(r.status = "submitted"), SUM(r.status = "reviewed"), SUM(r.status = "needs_revision") FROM reports r JOIN internships i ON i.id = r.internship_id JOIN lecturers l ON l.id = i.lecturer_id WHERE l.user_id = ? AND i.status IN ("planned", "ongoing")');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($report_submitted, $report_reviewed, $report_needs_revision);
$stmt->fetch();
$stmt->close();
$report_submitted = (int)$report_submitted;
$report_reviewed = (int)$report_reviewed;
$report_needs_revision = (int)$report_needs_revision;
$missing_reports = $expiring_soon = 0;
$stmt = $con->prepare('SELECT COUNT(*) FROM internships i JOIN lecturers l ON l.id = i.lecturer_id WHERE l.user_id = ? AND i.status IN ("planned", "ongoing") AND NOT EXISTS (SELECT 1 FROM reports r WHERE r.internship_id = i.id)');
$stmt->bind_param('i', $user_id); $stmt->execute(); $stmt->bind_result($missing_reports); $stmt->fetch(); $stmt->close();
$stmt = $con->prepare('SELECT COUNT(*) FROM internships i JOIN lecturers l ON l.id = i.lecturer_id WHERE l.user_id = ? AND i.status IN ("planned", "ongoing") AND i.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)');
$stmt->bind_param('i', $user_id); $stmt->execute(); $stmt->bind_result($expiring_soon); $stmt->fetch(); $stmt->close();
$students = [];
$stmt = $con->prepare('SELECT i.start_date, i.end_date, i.status, s.full_name, s.student_code, p.title FROM internships i JOIN lecturers l ON l.id = i.lecturer_id JOIN students s ON s.id = i.student_id JOIN internship_posts p ON p.id = i.post_id WHERE l.user_id = ? AND i.status IN ("planned", "ongoing") ORDER BY i.start_date DESC, s.full_name LIMIT 8');
$stmt->bind_param('i', $user_id); $stmt->execute(); $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
function lecturer_progress($start, $end, $status) {
	if (!$start || !$end) return $status === 'ongoing' ? 50 : 0;
	$start_time = strtotime($start); $end_time = strtotime($end);
	if ($end_time <= $start_time) return 0;
	return max(0, min(100, (int)round((time() - $start_time) / ($end_time - $start_time) * 100)));
}

lecturer_header('Tổng quan giảng viên');
?>
<div class="page-title lecturer-dashboard-title"><div><h2>Dashboard giảng viên</h2><p>Xin chào, <?=lecturer_e($_SESSION['account_name'])?></p></div><span class="lecturer-dashboard-date"><?=lecturer_e(date('d/m/Y'))?></span></div>
<section class="lecturer-metric-grid"><div class="lecturer-metric"><span>Sinh viên được phân công</span><strong><?=lecturer_e($assigned_count)?></strong></div><div class="lecturer-metric"><span>Đang thực tập</span><strong><?=lecturer_e($active_count)?></strong></div><div class="lecturer-metric"><span>Hoàn thành</span><strong><?=lecturer_e($completed_count)?></strong></div><div class="lecturer-metric lecturer-metric-alert"><span>Cần xử lý</span><strong><?=lecturer_e($missing_reports + $report_submitted)?></strong></div></section>
<section class="lecturer-dashboard-panel lecturer-progress-panel"><div class="lecturer-panel-heading"><div><h3>Tiến độ sinh viên</h3><p>Các kỳ thực tập đang được theo dõi</p></div><a href="internships.php">Xem danh sách</a></div><?php if (!$students): ?><p class="lecturer-empty">Chưa có sinh viên đang thực tập.</p><?php else: ?><div class="lecturer-progress-list"><?php foreach ($students as $student): $progress = lecturer_progress($student['start_date'], $student['end_date'], $student['status']); ?><div class="lecturer-progress-row"><div class="lecturer-progress-info"><strong><?=lecturer_e($student['full_name'])?></strong><span><?=lecturer_e($student['student_code'])?> · <?=lecturer_e($student['title'])?></span></div><div class="lecturer-progress-track"><span style="width: <?=$progress?>%"></span></div><strong class="lecturer-progress-value"><?=lecturer_e($progress)?>%</strong></div><?php endforeach; ?></div><?php endif; ?></section>
<section class="lecturer-dashboard-grid"><div class="lecturer-dashboard-panel"><div class="lecturer-panel-heading"><div><h3>Báo cáo thực tập</h3><p>Tình hình báo cáo của sinh viên đang theo dõi</p></div><a href="reports.php">Xem báo cáo</a></div><div class="lecturer-report-stats"><div><strong><?=lecturer_e($report_submitted)?></strong><span>Đã nộp</span></div><div><strong><?=lecturer_e($report_needs_revision)?></strong><span>Đã nhận xét</span></div><div><strong><?=lecturer_e($report_reviewed)?></strong><span>Đã duyệt</span></div></div></div><div class="lecturer-dashboard-panel"><div class="lecturer-panel-heading"><div><h3>Cần xử lý</h3><p>Các mục cần giảng viên theo dõi</p></div></div><div class="lecturer-action-list"><div><span>Chưa nộp báo cáo</span><strong><?=lecturer_e($missing_reports)?></strong></div><div><span>Chờ nhận xét</span><strong><?=lecturer_e($report_submitted)?></strong></div><div><span>Sắp hết hạn</span><strong><?=lecturer_e($expiring_soon)?></strong></div></div></div></section>
<section class="lecturer-dashboard-panel lecturer-management-panel"><div class="lecturer-panel-heading"><div><h3>Quản lý hướng dẫn</h3><p>Điều phối sinh viên, nhật ký và báo cáo trong các kỳ được phân công.</p></div></div><div class="lecturer-dashboard-actions"><a class="btn" href="internships.php">Xem sinh viên</a><a class="btn btn-secondary" href="reports.php">Xem báo cáo</a><a class="btn btn-secondary" href="logs.php">Theo dõi tiến độ</a></div></section>
<?php lecturer_footer(); ?>
