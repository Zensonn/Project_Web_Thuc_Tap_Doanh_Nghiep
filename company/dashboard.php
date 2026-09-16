<?php
require __DIR__ . '/_common.php';
$post_count = $application_count = $accepted_count = 0;
if ($company) {
	$stmt = $con->prepare('SELECT COUNT(*) FROM internship_posts WHERE company_id = ?');
	$stmt->bind_param('i', $company['id']); $stmt->execute(); $stmt->bind_result($post_count); $stmt->fetch(); $stmt->close();
	$stmt = $con->prepare('SELECT COUNT(*), SUM(a.status = "accepted") FROM applications a JOIN internship_posts p ON p.id = a.post_id WHERE p.company_id = ?');
	$stmt->bind_param('i', $company['id']); $stmt->execute(); $stmt->bind_result($application_count, $accepted_count); $stmt->fetch(); $stmt->close();
	$accepted_count = (int)$accepted_count;
}
company_header('Tổng quan doanh nghiệp');
?>
<div class="page-title"><h2>Tổng quan doanh nghiệp</h2><p><?=e($company['company_name'] ?? 'Vui lòng cập nhật hồ sơ doanh nghiệp')?></p></div>
<?php if (!$company): ?><div class="block"><p>Hãy cập nhật hồ sơ doanh nghiệp trước khi tạo bài đăng.</p><a class="btn" href="profile.php">Cập nhật hồ sơ</a></div><?php else: ?>
<div class="company-stat-grid"><div class="block"><strong><?=e($post_count)?></strong><span>Bài đăng</span></div><div class="block"><strong><?=e($application_count)?></strong><span>Lượt ứng tuyển</span></div><div class="block"><strong><?=e($accepted_count)?></strong><span>Đã tuyển</span></div></div>
<div class="block"><h3>Thao tác nhanh</h3><p><a class="btn" href="post-create.php">Đăng vị trí thực tập</a> <a class="btn btn-secondary" href="applications.php">Xem ứng viên</a></p></div>
<?php endif; ?>
<?php company_footer(); ?>
