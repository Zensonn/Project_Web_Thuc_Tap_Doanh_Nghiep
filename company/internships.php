<?php
require __DIR__ . '/_common.php';
if (!$company) { header('Location: profile.php'); exit; }

$internship_statuses = [
	'planned' => 'Chưa bắt đầu',
	'ongoing' => 'Đang thực tập',
	'completed' => 'Hoàn thành',
	'cancelled' => 'Đã hủy',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
	$status = $_POST['status'] ?? '';
	$start_date = $_POST['start_date'] ?? '';
	$end_date = $_POST['end_date'] ?? '';
	$company_supervisor = trim($_POST['company_supervisor'] ?? '');
	if ($internship_id && isset($internship_statuses[$status])) {
		$stmt = $con->prepare('UPDATE internships SET status=?, start_date=CASE WHEN ? = "" THEN start_date ELSE ? END, end_date=CASE WHEN ? = "" THEN NULL ELSE ? END, company_supervisor=CASE WHEN ? = "" THEN NULL ELSE ? END WHERE id=? AND company_id=?');
		$stmt->bind_param('sssssssii', $status, $start_date, $start_date, $end_date, $end_date, $company_supervisor, $company_supervisor, $internship_id, $company['id']);
		$stmt->execute();
		$stmt->close();
	}
}

$stmt = $con->prepare('SELECT i.id, i.start_date, i.end_date, i.status, i.company_supervisor, s.full_name AS student_name, s.student_code, p.title AS position_title, c.company_name FROM internships i JOIN students s ON s.id = i.student_id JOIN internship_posts p ON p.id = i.post_id JOIN companies c ON c.id = i.company_id WHERE i.company_id = ? ORDER BY i.start_date DESC, i.created_at DESC');
$stmt->bind_param('i', $company['id']);
$stmt->execute();
$internships = $stmt->get_result();
$stmt->close();

company_header('Quản lý thực tập');
?>
<div class="page-title">
	<h2>Danh sách thực tập</h2>
	<p>Quản lý sinh viên đang làm thực tập tại doanh nghiệp.</p>
</div>
<div class="block">
	<div class="table-wrap">
		<table>
			<thead>
				<tr>
					<th>Sinh viên</th>
					<th>Doanh nghiệp</th>
					<th>Vị trí</th>
					<th>Ngày bắt đầu</th>
					<th>Ngày kết thúc</th>
					<th>Người hướng dẫn</th>
					<th>Trạng thái</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($internships->num_rows === 0): ?>
					<tr><td colspan="7">Chưa có sinh viên nào được chấp nhận thực tập.</td></tr>
				<?php else: while ($internship = $internships->fetch_assoc()): ?>
					<tr>
						<td><?=e($internship['student_name'])?><br><small><?=e($internship['student_code'])?></small></td>
						<td><?=e($internship['company_name'])?></td>
						<td><?=e($internship['position_title'])?></td>
						<td>
							<form method="post">
								<input type="hidden" name="internship_id" value="<?=e($internship['id'])?>">
								<input class="form-input" type="date" name="start_date" value="<?=e($internship['start_date'])?>">
							</form>
						</td>
						<td>
							<form method="post">
								<input type="hidden" name="internship_id" value="<?=e($internship['id'])?>">
								<input class="form-input" type="date" name="end_date" value="<?=e($internship['end_date'])?>">
							</form>
						</td>
						<td>
							<form method="post">
								<input type="hidden" name="internship_id" value="<?=e($internship['id'])?>">
								<input class="form-input" type="text" name="company_supervisor" value="<?=e($internship['company_supervisor'])?>">
							</form>
						</td>
						<td>
							<form method="post">
								<input type="hidden" name="internship_id" value="<?=e($internship['id'])?>">
								<select class="form-input" name="status">
									<?php foreach ($internship_statuses as $value => $label): ?>
										<option value="<?=e($value)?>" <?=$internship['status'] === $value ? 'selected' : ''?>><?=e($label)?></option>
									<?php endforeach; ?>
								</select>
								<button type="submit">Lưu</button>
							</form>
						</td>
					</tr>
				<?php endwhile; endif; ?>
			</tbody>
		</table>
	</div>
</div>
<?php company_footer(); ?>
