<?php
require __DIR__ . '/_common.php';
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$values = [trim($_POST['company_code'] ?? ''), trim($_POST['company_name'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['tax_code'] ?? ''), trim($_POST['phone'] ?? ''), trim($_POST['address'] ?? ''), trim($_POST['website'] ?? ''), trim($_POST['industry'] ?? ''), trim($_POST['description'] ?? '')];
	if ($values[0] === '' || $values[1] === '' || !filter_var($values[2], FILTER_VALIDATE_EMAIL)) {
		$message = 'Mã doanh nghiệp, tên công ty và email hợp lệ là bắt buộc.';
	} else {
		[$company_code, $company_name, $email, $tax_code, $phone, $address, $website, $industry, $description] = $values;
		$con->begin_transaction();
		$stmt = $con->prepare('INSERT INTO companies (user_id, company_code, company_name, tax_code, phone, address, website, industry, description) VALUES (?, ?, ?, NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, "")) ON DUPLICATE KEY UPDATE company_code=VALUES(company_code), company_name=VALUES(company_name), tax_code=VALUES(tax_code), phone=VALUES(phone), address=VALUES(address), website=VALUES(website), industry=VALUES(industry), description=VALUES(description)');
		$stmt->bind_param('issssssss', $user_id, $company_code, $company_name, $tax_code, $phone, $address, $website, $industry, $description);
		$company_saved = $stmt->execute();
		$stmt->close();
		$email_stmt = $con->prepare('UPDATE users SET email = ? WHERE id = ?');
		$email_stmt->bind_param('si', $email, $user_id);
		$email_saved = $email_stmt->execute();
		$email_stmt->close();
		if ($company_saved && $email_saved) { $con->commit(); header('Location: profile.php?saved=1'); exit; }
		$con->rollback();
		$message = 'Không thể lưu hồ sơ. Mã doanh nghiệp có thể đã tồn tại.';
	}
}
if (isset($_GET['saved'])) $message = 'Đã cập nhật hồ sơ doanh nghiệp.';
$company = $company ?: ['company_code'=>'', 'company_name'=>'', 'email'=>'', 'tax_code'=>'', 'phone'=>'', 'address'=>'', 'website'=>'', 'industry'=>'', 'description'=>''];
company_header('Hồ sơ doanh nghiệp');
?>
<div class="page-title"><h2>Hồ sơ doanh nghiệp</h2><p>Cập nhật thông tin để sinh viên và giảng viên nhận diện doanh nghiệp.</p></div><div class="block company-profile-card">
<?php if ($message): ?><p class="upload-message"><?=e($message)?></p><?php endif; ?>
<form class="profile-form" method="post"><label>Mã doanh nghiệp<input class="form-input" name="company_code" value="<?=e($company['company_code'])?>" required></label><label>Tên công ty<input class="form-input" name="company_name" value="<?=e($company['company_name'])?>" required></label><label>Email<input class="form-input" type="email" name="email" value="<?=e($company['email'])?>" required></label><label>Mã số thuế<input class="form-input" name="tax_code" value="<?=e($company['tax_code'])?>"></label><label>Địa chỉ<input class="form-input" name="address" value="<?=e($company['address'])?>"></label><label>Số điện thoại<input class="form-input" name="phone" value="<?=e($company['phone'])?>"></label><label>Website<input class="form-input" type="url" name="website" value="<?=e($company['website'])?>"></label><label>Lĩnh vực<input class="form-input" name="industry" value="<?=e($company['industry'])?>"></label><label class="profile-form-wide">Mô tả<textarea class="form-input" name="description" rows="5"><?=e($company['description'])?></textarea></label><div class="profile-form-actions"><button class="btn" type="submit">Lưu hồ sơ</button></div></form></div>
<?php company_footer(); ?>
