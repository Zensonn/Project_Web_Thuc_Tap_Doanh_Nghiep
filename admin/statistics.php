<?php
require __DIR__ . '/_common.php';
$stats = [];
$queries = ['users' => 'SELECT COUNT(*) FROM users', 'students' => 'SELECT COUNT(*) FROM students', 'companies' => 'SELECT COUNT(*) FROM companies', 'lecturers' => 'SELECT COUNT(*) FROM lecturers', 'posts' => 'SELECT COUNT(*) FROM internship_posts WHERE status <> "cancelled"', 'applications' => 'SELECT COUNT(*) FROM applications', 'internships' => 'SELECT COUNT(*) FROM internships WHERE status <> "cancelled"', 'reports' => 'SELECT COUNT(*) FROM reports'];
foreach ($queries as $key => $query) {
	$result = $con->query($query);
	$stats[$key] = (int)$result->fetch_row()[0];
}
$application_statuses = ['submitted' => ['label' => 'Đã nộp', 'class' => 'is-blue'], 'reviewing' => ['label' => 'Đang xem xét', 'class' => 'is-amber'], 'accepted' => ['label' => 'Được chấp nhận', 'class' => 'is-green'], 'rejected' => ['label' => 'Bị từ chối', 'class' => 'is-red'], 'withdrawn' => ['label' => 'Đã rút', 'class' => 'is-muted']];
$internship_statuses = ['planned' => ['label' => 'Sắp bắt đầu', 'class' => 'is-blue'], 'ongoing' => ['label' => 'Đang thực tập', 'class' => 'is-amber'], 'completed' => ['label' => 'Hoàn thành', 'class' => 'is-green'], 'cancelled' => ['label' => 'Đã hủy', 'class' => 'is-red']];
$application_totals = array_fill_keys(array_keys($application_statuses), 0);
$result = $con->query('SELECT status, COUNT(*) AS total FROM applications GROUP BY status');
while ($row = $result->fetch_assoc()) { if (isset($application_totals[$row['status']])) $application_totals[$row['status']] = (int)$row['total']; }
$internship_totals = array_fill_keys(array_keys($internship_statuses), 0);
$result = $con->query('SELECT status, COUNT(*) AS total FROM internships GROUP BY status');
while ($row = $result->fetch_assoc()) { if (isset($internship_totals[$row['status']])) $internship_totals[$row['status']] = (int)$row['total']; }
$application_total = max(1, array_sum($application_totals));
$pending_applications = 0;
$stmt = $con->prepare('SELECT COUNT(*) FROM applications WHERE status = "submitted"');
$stmt->execute(); $stmt->bind_result($pending_applications); $stmt->fetch(); $stmt->close();
$pending_reports = 0;
$stmt = $con->prepare('SELECT COUNT(*) FROM reports WHERE status = "submitted"');
$stmt->execute(); $stmt->bind_result($pending_reports); $stmt->fetch(); $stmt->close();
$recent_activity = $con->query('SELECT activity_type, title, detail, occurred_at FROM (SELECT "application" AS activity_type, p.title, CONCAT(s.full_name, " đã nộp đơn") AS detail, a.applied_at AS occurred_at FROM applications a JOIN students s ON s.id = a.student_id JOIN internship_posts p ON p.id = a.post_id UNION ALL SELECT "post" AS activity_type, p.title, CONCAT(c.company_name, " đã tạo vị trí") AS detail, p.created_at AS occurred_at FROM internship_posts p JOIN companies c ON c.id = p.company_id UNION ALL SELECT "report" AS activity_type, r.title, CONCAT(s.full_name, " đã nộp báo cáo") AS detail, r.submitted_at AS occurred_at FROM reports r JOIN students s ON s.id = r.student_id WHERE r.submitted_at IS NOT NULL) activity ORDER BY occurred_at DESC LIMIT 5');
$upcoming_internships = $con->query('SELECT i.end_date, s.full_name, p.title FROM internships i JOIN students s ON s.id = i.student_id JOIN internship_posts p ON p.id = i.post_id WHERE i.end_date IS NOT NULL AND i.end_date >= CURDATE() AND i.status NOT IN ("completed", "cancelled") ORDER BY i.end_date ASC LIMIT 4');
$pending_report_list = $con->query('SELECT r.submitted_at, r.title, s.full_name FROM reports r JOIN students s ON s.id = r.student_id WHERE r.status = "submitted" ORDER BY r.submitted_at ASC LIMIT 3');
function admin_stat_date($date) { return $date ? date('d/m/Y', strtotime($date)) : '-'; }
admin_header('Thống kê hệ thống');
?>
<div class="page-title"><h2>Thống kê hệ thống</h2><p>Tổng quan dữ liệu và hoạt động thực tập.</p></div>
<section class="admin-metric-grid">
	<?php foreach ([['users', 'Tài khoản'], ['students', 'Sinh viên'], ['companies', 'Doanh nghiệp'], ['lecturers', 'Giảng viên'], ['posts', 'Vị trí thực tập'], ['applications', 'Đơn ứng tuyển'], ['internships', 'Kỳ thực tập'], ['reports', 'Báo cáo']] as [$key, $label]): ?>
		<div class="admin-metric"><span><?=admin_e($label)?></span><strong><?=admin_e($stats[$key])?></strong></div>
	<?php endforeach; ?>
</section>

<section class="admin-dashboard-grid admin-dashboard-grid-main">
	<div class="admin-panel"><div class="admin-panel-heading"><div><h3>Đơn ứng tuyển</h3><p>Phân bổ theo trạng thái</p></div><span class="admin-panel-kpi"><?=admin_e($stats['applications'])?> tổng</span></div><div class="admin-bars">
		<?php foreach ($application_statuses as $status => $meta): $percent = (int)round($application_totals[$status] / $application_total * 100); ?><div class="admin-bar-row"><div class="admin-bar-label"><span><?=admin_e($meta['label'])?></span><strong><?=admin_e($application_totals[$status])?></strong></div><div class="admin-bar-track"><span class="<?=admin_e($meta['class'])?>" style="width: <?=$percent?>%"></span></div></div><?php endforeach; ?>
	</div></div>
	<div class="admin-panel"><div class="admin-panel-heading"><div><h3>Trạng thái thực tập</h3><p>Tiến độ các kỳ thực tập</p></div><span class="admin-panel-kpi"><?=admin_e($stats['internships'])?> tổng</span></div><div class="admin-status-list">
		<?php foreach ($internship_statuses as $status => $meta): ?><div class="admin-status-row"><span class="admin-status-dot <?=admin_e($meta['class'])?>"></span><span><?=admin_e($meta['label'])?></span><strong><?=admin_e($internship_totals[$status])?></strong></div><?php endforeach; ?>
	</div></div>
</section>

<section class="admin-attention"><div><span class="admin-attention-mark">!</span><div><strong>CẦN XỬ LÝ</strong><p>Các mục đang chờ thao tác của quản trị viên</p></div></div><div class="admin-attention-counts"><span><strong><?=admin_e($pending_applications)?></strong> đơn chờ duyệt</span><span><strong><?=admin_e($pending_reports)?></strong> báo cáo chờ đánh giá</span></div></section>

<section class="admin-dashboard-grid admin-dashboard-grid-lower">
	<div class="admin-panel"><div class="admin-panel-heading"><div><h3>Hoạt động gần đây</h3><p>Dòng sự kiện mới nhất trong hệ thống</p></div></div><div class="admin-activity-list">
		<?php if (!$recent_activity || $recent_activity->num_rows === 0): ?><p class="admin-empty">Chưa có hoạt động gần đây.</p><?php else: while ($activity = $recent_activity->fetch_assoc()): ?><div class="admin-activity-row"><span class="admin-activity-icon <?=admin_e($activity['activity_type'])?>"></span><div><strong><?=admin_e($activity['detail'])?></strong><span><?=admin_e($activity['title'])?></span></div><time><?=admin_e(admin_stat_date($activity['occurred_at']))?></time></div><?php endwhile; endif; ?>
	</div></div>
	<div class="admin-panel"><div class="admin-panel-heading"><div><h3>Sắp tới</h3><p>Các mốc cần theo dõi</p></div></div><div class="admin-upcoming-list">
		<?php if ($pending_report_list): while ($report = $pending_report_list->fetch_assoc()): ?><div class="admin-upcoming-row"><span class="admin-upcoming-tag report">Báo cáo chờ đánh giá</span><strong><?=admin_e($report['title'])?></strong><span><?=admin_e($report['full_name'])?> · <?=admin_e(admin_stat_date($report['submitted_at']))?></span></div><?php endwhile; endif; ?>
		<?php if ($upcoming_internships): while ($internship = $upcoming_internships->fetch_assoc()): ?><div class="admin-upcoming-row"><span class="admin-upcoming-tag internship">Kết thúc kỳ thực tập</span><strong><?=admin_e($internship['title'])?></strong><span><?=admin_e($internship['full_name'])?> · <?=admin_e(admin_stat_date($internship['end_date']))?></span></div><?php endwhile; endif; ?>
		<?php if ((!$pending_report_list || $pending_report_list->num_rows === 0) && (!$upcoming_internships || $upcoming_internships->num_rows === 0)): ?><p class="admin-empty">Chưa có mốc sắp tới.</p><?php endif; ?>
	</div></div>
</section>
<?php admin_footer(); ?>
