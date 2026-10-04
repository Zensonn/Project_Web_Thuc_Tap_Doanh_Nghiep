<?php
require __DIR__ . '/_common.php';
$allowed_roles = ['student', 'company', 'lecturer', 'admin'];
$allowed_statuses = ['active', 'inactive', 'blocked'];
$message = null;
$query = trim($_GET['q'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$account_id = filter_input(INPUT_POST, 'account_id', FILTER_VALIDATE_INT);
	$role = $_POST['role'] ?? '';
	$status = $_POST['status'] ?? '';
	if ($account_id && in_array($role, $allowed_roles, true) && in_array($status, $allowed_statuses, true)) {
		$stmt = $con->prepare('UPDATE users SET role = ?, status = ? WHERE id = ? AND id <> ?');
		$stmt->bind_param('ssii', $role, $status, $account_id, $_SESSION['account_id']);
		$stmt->execute();
		$message = $stmt->affected_rows ? 'Đã cập nhật tài khoản.' : 'Không thể khóa hoặc thay đổi tài khoản quản trị hiện tại.';
		$stmt->close();
	}
}
$sql = 'SELECT id, username, email, role, status, registered FROM users';
if ($query !== '') $sql .= ' WHERE username LIKE ? OR email LIKE ? OR role LIKE ? OR status LIKE ?';
$sql .= ' ORDER BY id ASC';
$stmt = $con->prepare($sql);
if ($query !== '') { $search = '%' . $query . '%'; $stmt->bind_param('ssss', $search, $search, $search, $search); }
$stmt->execute();
$accounts = $stmt->get_result();
$stmt->close();
$role_labels = ['student' => 'Sinh viên', 'company' => 'Doanh nghiệp', 'lecturer' => 'Giảng viên', 'admin' => 'Quản trị viên'];
$status_labels = ['active' => 'Đang hoạt động', 'inactive' => 'Chưa kích hoạt', 'blocked' => 'Đã khóa'];
admin_header('Quản trị tài khoản');
?>
<div class="page-title"><h2>Quản lý tài khoản</h2><p>Tra cứu, phân quyền và khóa/mở khóa tài khoản hệ thống.</p></div>
<div class="block admin-filter"><form method="get"><input class="form-input" name="q" placeholder="Tìm tên đăng nhập, email, vai trò..." value="<?=admin_e($query)?>"><button class="btn" type="submit">Tra cứu</button></form></div>
<div class="block"><div class="table-wrap"><table><thead><tr><th>ID</th><th>Tài khoản</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Đăng ký</th><th>Thao tác</th></tr></thead><tbody><?php if ($accounts->num_rows === 0): ?><tr><td colspan="7">Không tìm thấy tài khoản.</td></tr><?php else: while ($account = $accounts->fetch_assoc()): ?><tr><td><?=admin_e($account['id'])?></td><td><?=admin_e($account['username'])?></td><td><?=admin_e($account['email'])?></td><td><select form="account-<?=admin_e($account['id'])?>" name="role"><option value="student" <?=$account['role'] === 'student' ? 'selected' : ''?>>Sinh viên</option><option value="company" <?=$account['role'] === 'company' ? 'selected' : ''?>>Doanh nghiệp</option><option value="lecturer" <?=$account['role'] === 'lecturer' ? 'selected' : ''?>>Giảng viên</option><option value="admin" <?=$account['role'] === 'admin' ? 'selected' : ''?>>Quản trị viên</option></select></td><td><select form="account-<?=admin_e($account['id'])?>" name="status"><option value="active" <?=$account['status'] === 'active' ? 'selected' : ''?>>Đang hoạt động</option><option value="inactive" <?=$account['status'] === 'inactive' ? 'selected' : ''?>>Chưa kích hoạt</option><option value="blocked" <?=$account['status'] === 'blocked' ? 'selected' : ''?>>Đã khóa</option></select></td><td><?=admin_e($account['registered'])?></td><td><form id="account-<?=admin_e($account['id'])?>" method="post"><input type="hidden" name="account_id" value="<?=admin_e($account['id'])?>"><button class="btn btn-secondary" type="submit">Lưu</button></form></td></tr><?php endwhile; endif; ?></tbody></table></div></div>
<?php admin_footer(); ?>
