<?php
require __DIR__ . '/_common.php';
$company_id = filter_input(INPUT_GET, 'company_id', FILTER_VALIDATE_INT);
$user_id = (int)$_SESSION['account_id'];
if (!$company_id) { header('Location: internships.php'); exit; }
$stmt = $con->prepare('SELECT c.* FROM companies c WHERE c.id = ? AND EXISTS (SELECT 1 FROM internships i JOIN lecturers l ON l.id = i.lecturer_id WHERE i.company_id = c.id AND l.user_id = ?)');
$stmt->bind_param('ii', $company_id, $user_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$company) { http_response_code(404); exit('Không tìm thấy doanh nghiệp trong kỳ thực tập được phân công.'); }
lecturer_header('Thông tin doanh nghiệp');
?>
<div class="page-title"><h2>Thông tin doanh nghiệp</h2><p><?=lecturer_e($company['company_name'])?></p></div>
<div class="block lecturer-profile"><div class="profile-grid"><div class="profile-detail"><strong>Tên doanh nghiệp</strong><?=lecturer_e($company['company_name'])?></div><div class="profile-detail"><strong>Mã doanh nghiệp</strong><?=lecturer_e($company['company_code'])?></div><div class="profile-detail"><strong>Mã số thuế</strong><?=lecturer_e($company['tax_code'] ?: 'Chưa cập nhật')?></div><div class="profile-detail"><strong>Lĩnh vực</strong><?=lecturer_e($company['industry'] ?: 'Chưa cập nhật')?></div><div class="profile-detail"><strong>Điện thoại</strong><?=lecturer_e($company['phone'] ?: 'Chưa cập nhật')?></div><div class="profile-detail"><strong>Website</strong><?php if ($company['website']): ?><a href="<?=lecturer_e($company['website'])?>" target="_blank" rel="noopener"><?=lecturer_e($company['website'])?></a><?php else: ?>Chưa cập nhật<?php endif; ?></div><div class="profile-detail profile-detail-wide"><strong>Địa chỉ</strong><?=lecturer_e($company['address'] ?: 'Chưa cập nhật')?></div><div class="profile-detail profile-detail-wide"><strong>Giới thiệu</strong><?=nl2br(lecturer_e($company['description'] ?: 'Chưa cập nhật'))?></div></div><p><a class="btn btn-secondary" href="internships.php">Quay lại danh sách</a></p></div>
<?php lecturer_footer(); ?>
