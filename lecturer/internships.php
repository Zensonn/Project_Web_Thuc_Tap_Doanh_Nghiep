<?php
require __DIR__ . '/_common.php';

$user_id = (int)$_SESSION['account_id'];
$stmt = $con->prepare('SELECT i.start_date, i.end_date, i.status, i.student_id, i.company_id, s.full_name AS student_name, s.student_code, c.company_name, p.title AS position_title FROM internships i JOIN lecturers l ON l.id = i.lecturer_id JOIN students s ON s.id = i.student_id JOIN companies c ON c.id = i.company_id JOIN internship_posts p ON p.id = i.post_id WHERE l.user_id = ? AND i.status <> "cancelled" ORDER BY i.start_date DESC, i.created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$internships = $stmt->get_result();
$stmt->close();
$status_labels = ['planned' => 'Chưa bắt đầu', 'ongoing' => 'Đang thực tập', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
lecturer_header('Sinh viên thực tập');
?>
<div class="page-title"><h2>Sinh viên thực tập</h2><p>Danh sách sinh viên được phân công hướng dẫn.</p></div>
<div class="block"><div class="table-wrap"><table><thead><tr><th>Sinh viên</th><th>Doanh nghiệp</th><th>Vị trí</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Trạng thái</th></tr></thead><tbody>
<?php if ($internships->num_rows === 0): ?><tr><td colspan="6">Chưa có sinh viên được phân công.</td></tr><?php else: while ($internship = $internships->fetch_assoc()): ?><tr><td><a href="student-profile.php?student_id=<?=lecturer_e($internship['student_id'])?>"><?=lecturer_e($internship['student_name'])?></a><br><small><?=lecturer_e($internship['student_code'])?></small></td><td><a href="company-profile.php?company_id=<?=lecturer_e($internship['company_id'])?>"><?=lecturer_e($internship['company_name'])?></a></td><td><?=lecturer_e($internship['position_title'])?></td><td><?=lecturer_e($internship['start_date'])?></td><td><?=lecturer_e($internship['end_date'] ?: 'Chưa cập nhật')?></td><td><?=lecturer_e($status_labels[$internship['status']] ?? $internship['status'])?></td></tr><?php endwhile; endif; ?>
</tbody></table></div></div>
<?php lecturer_footer(); ?>
