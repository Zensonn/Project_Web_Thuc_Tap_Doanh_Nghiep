<?php
require __DIR__ . '/_common.php';
$reports = $con->query('SELECT r.id, r.title, r.report_type, r.status, r.submitted_at, s.full_name AS student_name, c.company_name, p.title AS position_title FROM reports r JOIN students s ON s.id = r.student_id JOIN internships i ON i.id = r.internship_id JOIN companies c ON c.id = i.company_id JOIN internship_posts p ON p.id = i.post_id ORDER BY r.submitted_at DESC, r.id DESC');
$report_types = ['weekly' => 'Báo cáo tuần', 'midterm' => 'Báo cáo giữa kỳ', 'final' => 'Báo cáo cuối kỳ', 'other' => 'Khác'];
$report_status = ['draft' => 'Bản nháp', 'submitted' => 'Đã nộp', 'reviewed' => 'Đã nhận xét', 'needs_revision' => 'Cần chỉnh sửa'];
admin_header('Báo cáo hệ thống');
?>
<div class="page-title"><h2>Báo cáo hệ thống</h2><p>Tra cứu tình hình nộp và nhận xét báo cáo thực tập.</p></div>
<div class="block"><div class="table-wrap"><table><thead><tr><th>Sinh viên</th><th>Doanh nghiệp</th><th>Báo cáo</th><th>Loại</th><th>Ngày nộp</th><th>Trạng thái</th></tr></thead><tbody><?php if (!$reports || $reports->num_rows === 0): ?><tr><td colspan="6">Chưa có báo cáo.</td></tr><?php else: while ($report = $reports->fetch_assoc()): ?><tr><td><?=admin_e($report['student_name'])?></td><td><?=admin_e($report['company_name'])?></td><td><?=admin_e($report['title'])?><br><small><?=admin_e($report['position_title'])?></small></td><td><?=admin_e($report_types[$report['report_type']] ?? $report['report_type'])?></td><td><?=admin_e($report['submitted_at'] ? date('d/m/Y H:i', strtotime($report['submitted_at'])) : 'Chưa nộp')?></td><td><?=admin_e($report_status[$report['status']] ?? $report['status'])?></td></tr><?php endwhile; endif; ?></tbody></table></div></div>
<?php admin_footer(); ?>
