<?php
require __DIR__ . '/_common.php';
$type = $_GET['type'] ?? 'students';
$allowed = ['students', 'companies', 'lecturers', 'posts'];
if (!in_array($type, $allowed, true)) $type = 'students';
$query = trim($_GET['q'] ?? '');
$config = [
	'students' => ['title' => 'Quản lý sinh viên', 'description' => 'Tra cứu hồ sơ sinh viên trong hệ thống.', 'sql' => 'SELECT id, student_code AS code, full_name AS name, major AS detail, cohort AS extra, phone FROM students', 'headers' => ['Mã sinh viên', 'Họ tên', 'Ngành học', 'Khóa', 'Điện thoại']],
	'companies' => ['title' => 'Quản lý doanh nghiệp', 'description' => 'Tra cứu thông tin doanh nghiệp và trạng thái xác minh.', 'sql' => 'SELECT id, company_code AS code, company_name AS name, industry AS detail, address AS extra, verified FROM companies', 'headers' => ['Mã doanh nghiệp', 'Tên doanh nghiệp', 'Lĩnh vực', 'Địa chỉ', 'Xác minh']],
	'lecturers' => ['title' => 'Quản lý giảng viên', 'description' => 'Tra cứu thông tin giảng viên hướng dẫn.', 'sql' => 'SELECT id, lecturer_code AS code, full_name AS name, department AS detail, academic_title AS extra, phone FROM lecturers', 'headers' => ['Mã giảng viên', 'Họ tên', 'Khoa / bộ môn', 'Học hàm / học vị', 'Điện thoại']],
	'posts' => ['title' => 'Quản lý vị trí thực tập', 'description' => 'Theo dõi các vị trí thực tập đã được đăng.', 'sql' => 'SELECT p.id, p.title AS name, c.company_name AS detail, p.location AS extra, p.status, p.deadline FROM internship_posts p JOIN companies c ON c.id = p.company_id', 'headers' => ['Vị trí', 'Doanh nghiệp', 'Địa điểm', 'Trạng thái', 'Hạn nộp']]
];
$selected = $config[$type];
$sql = $selected['sql'];
if ($query !== '') {
	$sql .= $type === 'students' ? ' WHERE student_code LIKE ? OR full_name LIKE ? OR major LIKE ?' : ($type === 'companies' ? ' WHERE company_code LIKE ? OR company_name LIKE ? OR industry LIKE ?' : ($type === 'lecturers' ? ' WHERE lecturer_code LIKE ? OR full_name LIKE ? OR department LIKE ?' : ' WHERE p.title LIKE ? OR c.company_name LIKE ? OR p.location LIKE ?'));
	$sql .= ' ORDER BY name ASC';
	$search = '%' . $query . '%';
	$stmt = $con->prepare($sql);
	$stmt->bind_param('sss', $search, $search, $search);
} else {
	$sql .= ' ORDER BY ' . ($type === 'posts' ? 'p.created_at' : 'name') . ' DESC';
	$stmt = $con->prepare($sql);
}
$stmt->execute();
$records = $stmt->get_result();
$stmt->close();
admin_header($selected['title']);
?>
<div class="page-title"><h2><?=admin_e($selected['title'])?></h2><p><?=admin_e($selected['description'])?></p></div>
<div class="block admin-filter"><form method="get"><input type="hidden" name="type" value="<?=admin_e($type)?>"><input class="form-input" name="q" placeholder="Nhập từ khóa tra cứu..." value="<?=admin_e($query)?>"><button class="btn" type="submit">Tra cứu</button></form></div>
<div class="block"><div class="table-wrap"><table><thead><tr><?php foreach ($selected['headers'] as $header): ?><th><?=admin_e($header)?></th><?php endforeach; ?></tr></thead><tbody><?php if ($records->num_rows === 0): ?><tr><td colspan="<?=count($selected['headers'])?>">Không có dữ liệu.</td></tr><?php else: while ($record = $records->fetch_assoc()): ?><tr><?php if ($type !== 'posts'): ?><td><?=admin_e($record['code'])?></td><?php endif; ?><td><?=admin_e($record['name'])?></td><td><?=admin_e($record['detail'] ?: 'Chưa cập nhật')?></td><td><?=admin_e($record['extra'] ?: 'Chưa cập nhật')?></td><td><?=admin_e($type === 'companies' ? ($record['verified'] ? 'Đã xác minh' : 'Chưa xác minh') : ($type === 'posts' ? $record['status'] : ($type === 'students' || $type === 'lecturers' ? $record['phone'] : '')))?></td><?php if ($type === 'posts'): ?><td><?=admin_e($record['deadline'] ?: 'Không giới hạn')?></td><?php endif; ?></tr><?php endwhile; endif; ?></tbody></table></div></div>
<?php admin_footer(); ?>
