<?php
require __DIR__ . '/_common.php';

$post_count = $application_count = $accepted_count = $active_count = 0;
$recent_applications = $position_stats = $student_statuses = null;
$pending_applications = $ending_soon = $pending_reports = 0;
$application_labels = ['submitted' => 'Chờ duyệt', 'reviewing' => 'Đang xem xét', 'accepted' => 'Đã nhận', 'rejected' => 'Từ chối', 'withdrawn' => 'Đã rút'];
$internship_labels = ['planned' => 'Sắp bắt đầu', 'ongoing' => 'Đang thực tập', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];

if ($company) {
	$company_id = (int)$company['id'];
	$stmt = $con->prepare('SELECT COUNT(*) FROM internship_posts WHERE company_id = ? AND status <> "cancelled"');
	$stmt->bind_param('i', $company_id); $stmt->execute(); $stmt->bind_result($post_count); $stmt->fetch(); $stmt->close();
	$stmt = $con->prepare('SELECT COUNT(*), SUM(a.status = "accepted"), SUM(a.status = "submitted") FROM applications a JOIN internship_posts p ON p.id = a.post_id WHERE p.company_id = ? AND p.status <> "cancelled"');
	$stmt->bind_param('i', $company_id); $stmt->execute(); $stmt->bind_result($application_count, $accepted_count, $pending_applications); $stmt->fetch(); $stmt->close();
	$accepted_count = (int)$accepted_count;
	$pending_applications = (int)$pending_applications;
	$stmt = $con->prepare('SELECT COUNT(*) FROM internships WHERE company_id = ? AND status IN ("planned", "ongoing")');
	$stmt->bind_param('i', $company_id); $stmt->execute(); $stmt->bind_result($active_count); $stmt->fetch(); $stmt->close();
	$stmt = $con->prepare('SELECT COUNT(*) FROM internships WHERE company_id = ? AND status IN ("planned", "ongoing") AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)');
	$stmt->bind_param('i', $company_id); $stmt->execute(); $stmt->bind_result($ending_soon); $stmt->fetch(); $stmt->close();
	$stmt = $con->prepare('SELECT COUNT(*) FROM reports r JOIN internships i ON i.id = r.internship_id WHERE i.company_id = ? AND i.status IN ("planned", "ongoing") AND r.status = "submitted"');
	$stmt->bind_param('i', $company_id); $stmt->execute(); $stmt->bind_result($pending_reports); $stmt->fetch(); $stmt->close();
	$stmt = $con->prepare('SELECT s.full_name, p.title, a.applied_at, a.status FROM applications a JOIN students s ON s.id = a.student_id JOIN internship_posts p ON p.id = a.post_id WHERE p.company_id = ? AND p.status <> "cancelled" ORDER BY a.applied_at DESC LIMIT 3');
	$stmt->bind_param('i', $company_id); $stmt->execute(); $recent_applications = $stmt->get_result(); $stmt->close();
	$stmt = $con->prepare('SELECT p.title, COUNT(a.id) AS total FROM internship_posts p LEFT JOIN applications a ON a.post_id = p.id WHERE p.company_id = ? AND p.status <> "cancelled" GROUP BY p.id, p.title ORDER BY total DESC, p.created_at DESC LIMIT 5');
	$stmt->bind_param('i', $company_id); $stmt->execute(); $position_stats = $stmt->get_result(); $stmt->close();
	$stmt = $con->prepare('SELECT i.status, COUNT(*) AS total FROM internships i WHERE i.company_id = ? GROUP BY i.status');
	$stmt->bind_param('i', $company_id); $stmt->execute(); $student_statuses = $stmt->get_result(); $stmt->close();
}
function company_date($date) { return $date ? date('d/m/Y', strtotime($date)) : '-'; }
company_header('Tổng quan doanh nghiệp');
?>
<div class="page-title company-dashboard-title"><div><h2>Dashboard doanh nghiệp</h2><p>Xin chào, <?=e($company['company_name'] ?? 'Vui lòng cập nhật hồ sơ doanh nghiệp')?></p></div><span class="company-dashboard-date"><?=e(date('d/m/Y'))?></span></div>
<?php if (!$company): ?><div class="block"><p>Hãy cập nhật hồ sơ doanh nghiệp trước khi tạo bài đăng.</p><a class="btn" href="profile.php">Cập nhật hồ sơ</a></div><?php else: ?>
<section class="company-metric-grid"><div class="company-metric"><span>Vị trí thực tập</span><strong><?=e($post_count)?></strong></div><div class="company-metric"><span>Đơn ứng tuyển</span><strong><?=e($application_count)?></strong></div><div class="company-metric"><span>Sinh viên được nhận</span><strong><?=e($accepted_count)?></strong></div><div class="company-metric"><span>Đang thực tập</span><strong><?=e($active_count)?></strong></div></section>
<section class="company-dashboard-panel company-applications-panel"><div class="company-panel-heading"><div><h3>Đơn ứng tuyển mới</h3><p>Các đơn gần đây nhất của doanh nghiệp</p></div><a href="applications.php">Xem tất cả đơn</a></div><div class="company-recent-table"><div class="company-recent-head"><span>Sinh viên</span><span>Vị trí</span><span>Ngày nộp</span><span>Trạng thái</span></div><?php if (!$recent_applications || $recent_applications->num_rows === 0): ?><p class="company-empty">Chưa có đơn ứng tuyển.</p><?php else: while ($application = $recent_applications->fetch_assoc()): ?><div class="company-recent-row"><strong><?=e($application['full_name'])?></strong><span><?=e($application['title'])?></span><span><?=e(company_date($application['applied_at']))?></span><span class="company-status <?=e($application['status'])?>"><?=e($application_labels[$application['status']] ?? $application['status'])?></span></div><?php endwhile; endif; ?></div></section>
<section class="company-dashboard-grid"><div class="company-dashboard-panel"><div class="company-panel-heading"><div><h3>Vị trí thực tập</h3><p>Số lượng đơn theo từng vị trí</p></div></div><div class="company-summary-list"><?php if (!$position_stats || $position_stats->num_rows === 0): ?><p class="company-empty">Chưa có vị trí nào.</p><?php else: while ($position = $position_stats->fetch_assoc()): ?><div><span><?=e($position['title'])?></span><strong><?=e($position['total'])?> đơn</strong></div><?php endwhile; endif; ?></div></div><div class="company-dashboard-panel"><div class="company-panel-heading"><div><h3>Trạng thái sinh viên</h3><p>Tình hình các kỳ thực tập</p></div></div><div class="company-summary-list"><?php if (!$student_statuses || $student_statuses->num_rows === 0): ?><p class="company-empty">Chưa có sinh viên được nhận.</p><?php else: while ($student_status = $student_statuses->fetch_assoc()): ?><div><span><?=e($internship_labels[$student_status['status']] ?? $student_status['status'])?></span><strong><?=e($student_status['total'])?></strong></div><?php endwhile; endif; ?></div></div></section>
<section class="company-dashboard-panel company-tasks-panel"><div class="company-panel-heading"><div><h3>Công việc cần xử lý</h3><p>Các mục cần doanh nghiệp kiểm tra</p></div></div><div class="company-task-list"><div><span class="company-task-icon warning">!</span><span><?=e($pending_applications)?></span> đơn ứng tuyển đang chờ duyệt<a href="applications.php">Xem</a></div><div><span class="company-task-icon warning">!</span><span><?=e($ending_soon)?></span> sinh viên sắp kết thúc kỳ thực tập<a href="internships.php">Theo dõi</a></div><div><span class="company-task-icon document">*</span><span><?=e($pending_reports)?></span> báo cáo cần xem<a href="evaluations.php">Đánh giá</a></div></div></section>
<?php endif; ?>
<?php company_footer(); ?>
