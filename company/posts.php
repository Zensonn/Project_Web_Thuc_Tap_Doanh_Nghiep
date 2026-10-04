<?php
require __DIR__ . '/_common.php';
$posts = null;
$message = null;
if ($company) {
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
		$post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
		if ($post_id) {
			$stmt = $con->prepare('UPDATE internship_posts SET status="cancelled" WHERE id=? AND company_id=? AND status<>"cancelled"');
			$stmt->bind_param('ii', $post_id, $company['id']);
			$stmt->execute();
			$message = $stmt->affected_rows ? 'Đã xóa bài đăng khỏi website.' : 'Bài đăng không tồn tại hoặc đã được xóa.';
			$stmt->close();
		}
	}
	$stmt = $con->prepare('SELECT id, title, location, quantity, deadline, status, created_at FROM internship_posts WHERE company_id = ? AND status<>"cancelled" ORDER BY created_at DESC');
	$stmt->bind_param('i', $company['id']); $stmt->execute(); $posts = $stmt->get_result();
}
company_header('Bài đăng thực tập');
?>
<div class="page-title"><h2>Bài đăng thực tập</h2><p>Quản lý các vị trí đang tuyển.</p></div><div class="block">
<p><a class="btn" href="post-create.php">+ Tạo bài đăng</a></p>
<?php if ($message): ?><p class="upload-message"><?=e($message)?></p><?php endif; ?>
<?php if (!$company): ?><p>Vui lòng cập nhật hồ sơ doanh nghiệp trước.</p><?php elseif ($posts->num_rows === 0): ?><p>Chưa có bài đăng nào.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Vị trí</th><th>Địa điểm</th><th>Số lượng</th><th>Hạn nộp</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody><?php while ($post = $posts->fetch_assoc()): ?><tr><td><?=e($post['title'])?></td><td><?=e($post['location'] ?: 'Không xác định')?></td><td><?=e($post['quantity'])?></td><td><?=e($post['deadline'] ?: 'Không giới hạn')?></td><td><?=e($post['status'])?></td><td><a href="post-edit.php?id=<?=e($post['id'])?>">Chỉnh sửa</a> <form method="post" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xóa bài đăng này?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="post_id" value="<?=e($post['id'])?>"><button type="submit">Xóa</button></form></td></tr><?php endwhile; ?></tbody></table></div><?php endif; ?></div>
<?php company_footer(); ?>
