<?php
require __DIR__ . '/_common.php';
$con->query('CREATE TABLE IF NOT EXISTS majors (id int unsigned NOT NULL AUTO_INCREMENT, name varchar(150) NOT NULL, faculty varchar(150) DEFAULT NULL, status enum("active","inactive") NOT NULL DEFAULT "active", PRIMARY KEY (id), UNIQUE KEY uq_major_name (name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	$major_id = filter_input(INPUT_POST, 'major_id', FILTER_VALIDATE_INT);
	$name = trim($_POST['name'] ?? '');
	$faculty = trim($_POST['faculty'] ?? '');
	$status = $_POST['status'] ?? 'active';
	if ($action === 'delete' && $major_id) {
		$stmt = $con->prepare('DELETE FROM majors WHERE id=?'); $stmt->bind_param('i', $major_id); $stmt->execute(); $stmt->close(); $message = 'Đã xóa ngành học.';
	} elseif ($name !== '' && in_array($status, ['active', 'inactive'], true)) {
		$stmt = $con->prepare('INSERT INTO majors (name, faculty, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE faculty=VALUES(faculty), status=VALUES(status)');
		$stmt->bind_param('sss', $name, $faculty, $status); $stmt->execute(); $stmt->close(); $message = 'Đã lưu ngành học.';
	}
}
$majors = $con->query('SELECT id, name, faculty, status FROM majors ORDER BY name ASC');
admin_header('Quản lý ngành học');
?>
<div class="page-title"><h2>Quản lý ngành học</h2><p>Thêm, cập nhật hoặc ngừng sử dụng ngành học.</p></div>
<div class="block admin-form-row"><?php if ($message): ?><p class="upload-message"><?=admin_e($message)?></p><?php endif; ?><form method="post"><input type="hidden" name="action" value="save"><label>Tên ngành<input class="form-input" name="name" required></label><label>Khoa<input class="form-input" name="faculty"></label><label>Trạng thái<select class="form-input" name="status"><option value="active">Đang sử dụng</option><option value="inactive">Ngừng sử dụng</option></select></label><button class="btn" type="submit">Lưu ngành</button></form></div>
<div class="block"><div class="table-wrap"><table><thead><tr><th>Ngành học</th><th>Khoa</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody><?php if (!$majors || $majors->num_rows === 0): ?><tr><td colspan="4">Chưa có ngành học.</td></tr><?php else: while ($major = $majors->fetch_assoc()): ?><tr><td><?=admin_e($major['name'])?></td><td><?=admin_e($major['faculty'] ?: 'Chưa cập nhật')?></td><td><?=admin_e($major['status'] === 'active' ? 'Đang sử dụng' : 'Ngừng sử dụng')?></td><td><form method="post" onsubmit="return confirm('Xóa ngành học này?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="major_id" value="<?=admin_e($major['id'])?>"><button type="submit">Xóa</button></form></td></tr><?php endwhile; endif; ?></tbody></table></div></div>
<?php admin_footer(); ?>
