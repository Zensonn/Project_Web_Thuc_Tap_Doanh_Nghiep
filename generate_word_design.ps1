$docxPath = 'C:\xampp\htdocs\internship-management\ThietkeCSDL_Design.docx'
If (Test-Path $docxPath) { Remove-Item $docxPath -Force }

$temp = Join-Path $env:TEMP ([System.Guid]::NewGuid().ToString())
New-Item -ItemType Directory -Path $temp | Out-Null
New-Item -ItemType Directory -Path (Join-Path $temp 'word') | Out-Null
New-Item -ItemType Directory -Path (Join-Path $temp '_rels') | Out-Null
New-Item -ItemType Directory -Path (Join-Path $temp 'docProps') | Out-Null

$docXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>THIẾT KẾ CSDL HỆ THỐNG THỰC TẬP SINH VIÊN</w:t></w:r></w:p>
    <w:p><w:r><w:t>Học phần: Cơ sở dữ liệu</w:t></w:r></w:p>
    <w:p><w:r><w:t>Người thực hiện: Nhóm quản lý hệ thống</w:t></w:r></w:p>
    <w:p><w:r><w:t>Ngày: 2026-09-15</w:t></w:r></w:p>

    <w:p><w:r><w:rPr><w:b/><w:sz w:val="26"/></w:rPr><w:t>1. Mục tiêu hệ thống</w:t></w:r></w:p>
    <w:p><w:r><w:t>- Quản lý tài khoản người dùng theo vai trò: student, company, lecturer, admin.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- Quản lý hồ sơ sinh viên, doanh nghiệp, giảng viên và các bài đăng thực tập.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- Quản lý ứng tuyển, thực tập, báo cáo, đánh giá và lịch sử hoạt động.</w:t></w:r></w:p>

    <w:p><w:r><w:rPr><w:b/><w:sz w:val="26"/></w:rPr><w:t>2. Danh sách thực thể chính</w:t></w:r></w:p>
    <w:tbl>
      <w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:insideH w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:insideV w:val="single" w:sz="4" w:space="0" w:color="auto"/></w:tblBorders></w:tblPr>
      <w:tr>
        <w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Bảng</w:t></w:r></w:p></w:tc>
        <w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Mô tả</w:t></w:r></w:p></w:tc>
        <w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Khóa chính</w:t></w:r></w:p></w:tc>
        <w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Khóa ngoại</w:t></w:r></w:p></w:tc>
        <w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Ghi chú</w:t></w:r></w:p></w:tc>
      </w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>users</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tài khoản hệ thống</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>-</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>username, email, password, role, status</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>students</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thông tin sinh viên</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>user_id -> users.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>student_code, full_name, major, cohort, cv_file</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>companies</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thông tin doanh nghiệp</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>user_id -> users.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>company_code, company_name, website, verified</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>lecturers</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thông tin giảng viên</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>user_id -> users.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>lecturer_code, full_name, department</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>internship_posts</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bài đăng thực tập</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>company_id -> companies.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>title, location, quantity, deadline, status</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>applications</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn ứng tuyển</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>post_id -> internship_posts.id, student_id -> students.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>cover_letter, resume_url, status</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>internships</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Quá trình thực tập</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>application_id -> applications.id, student_id -> students.id, company_id -> companies.id, lecturer_id -> lecturers.id, post_id -> internship_posts.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>start_date, end_date, company_supervisor</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>internship_logs</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Lịch sử thao tác</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>internship_id -> internships.id, actor_user_id -> users.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>action, description, created_at</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>reports</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Báo cáo thực tập</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>internship_id -> internships.id, student_id -> students.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>report_type, content, file_url, status</w:t></w:r></w:p></w:tc></w:tr>
      <w:tr><w:tc><w:p><w:r><w:t>evaluations</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đánh giá thực tập</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>internship_id -> internships.id, report_id -> reports.id, evaluator_user_id -> users.id</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>evaluator_type, score, comments</w:t></w:r></w:p></w:tc></w:tr>
    </w:tbl>

    <w:p><w:r><w:rPr><w:b/><w:sz w:val="26"/></w:rPr><w:t>3. Quan hệ giữa các bảng</w:t></w:r></w:p>
    <w:p><w:r><w:t>- users 1 - 1 students, companies, lecturers.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- companies 1 - n internship_posts.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- students 1 - n applications.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- internship_posts 1 - n applications.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- applications 1 - 1 internships.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- internships 1 - n reports, 1 - n evaluations, 1 - n internship_logs.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- users có vai trò tham gia vào evaluations và logs như người đánh giá hoặc người tạo hành động.</w:t></w:r></w:p>

    <w:p><w:r><w:rPr><w:b/><w:sz w:val="26"/></w:rPr><w:t>4. Mô tả trường dữ liệu quan trọng</w:t></w:r></w:p>
    <w:p><w:r><w:t>users: id, username, password, email, role, status, registered, activation_code.</w:t></w:r></w:p>
    <w:p><w:r><w:t>students: user_id, student_code, full_name, date_of_birth, gender, phone, address, major, cohort, gpa, skills, cv_file.</w:t></w:r></w:p>
    <w:p><w:r><w:t>companies: user_id, company_code, company_name, tax_code, phone, address, website, industry, description, verified.</w:t></w:r></w:p>
    <w:p><w:r><w:t>internship_posts: company_id, title, description, requirements, benefits, location, employment_type, quantity, deadline, status.</w:t></w:r></w:p>
    <w:p><w:r><w:t>applications: post_id, student_id, cover_letter, resume_url, status, applied_at, reviewed_at.</w:t></w:r></w:p>
    <w:p><w:r><w:t>internships: application_id, student_id, company_id, lecturer_id, post_id, start_date, end_date, status, company_supervisor.</w:t></w:r></w:p>
    <w:p><w:r><w:t>reports: internship_id, student_id, title, content, file_url, report_type, status, submitted_at, reviewed_at.</w:t></w:r></w:p>
    <w:p><w:r><w:t>evaluations: internship_id, report_id, evaluator_user_id, evaluator_type, score, comments, status.</w:t></w:r></w:p>

    <w:p><w:r><w:rPr><w:b/><w:sz w:val="26"/></w:rPr><w:t>5. Ràng buộc dữ liệu</w:t></w:r></w:p>
    <w:p><w:r><w:t>- Mỗi username và email trong users phải là duy nhất.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- Mỗi student_code, company_code, lecturer_code phải là duy nhất.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- Một ứng tuyển chỉ cho phép một sinh viên nộp tối đa một lần cho cùng một vị trí.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- Một internship liên kết duy nhất với một application.</w:t></w:r></w:p>
    <w:p><w:r><w:t>- Khi xóa user, dữ liệu ở bảng con sẽ được xóa hoặc đặt null theo quy tắc cascade/set null.</w:t></w:r></w:p>

    <w:p><w:r><w:rPr><w:b/><w:sz w:val="26"/></w:rPr><w:t>6. Kết luận</w:t></w:r></w:p>
    <w:p><w:r><w:t>Hệ thống cơ sở dữ liệu được thiết kế theo mô hình quan hệ, phân tách rõ ràng giữa tài khoản, hồ sơ và nghiệp vụ thực tập. Thiết kế này hỗ trợ quản lý tốt các quy trình đăng ký, ứng tuyển, đánh giá và báo cáo trong hệ thống thực tập sinh viên.</w:t></w:r></w:p>
    <w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/></w:sectPr>
  </w:body>
</w:document>
'@

$xmlEscape = { param($value) [System.Security.SecurityElement]::Escape([string]$value) }
$cell = { param($value, $header = $false) $bold = if ($header) { '<w:b/>' } else { '' }; "<w:tc><w:p><w:r><w:rPr>$bold</w:rPr><w:t>$(& $xmlEscape $value)</w:t></w:r></w:p></w:tc>" }
$table = {
  param($tableName, $rows)
  $xml = "<w:p><w:r><w:rPr><w:b/><w:sz w:val='26'/></w:rPr><w:t>$(& $xmlEscape "Bảng $tableName")</w:t></w:r></w:p>"
  $xml += '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders><w:top w:val="single"/><w:left w:val="single"/><w:bottom w:val="single"/><w:right w:val="single"/><w:insideH w:val="single"/><w:insideV w:val="single"/></w:tblBorders></w:tblPr>'
  $headers = @('Stt', 'Thuộc tính', 'Kiểu', 'Miền giá trị', 'Ý nghĩa', 'Ghi chú')
  $xml += '<w:tr>' + (($headers | ForEach-Object { & $cell $_ $true }) -join '') + '</w:tr>'
  $number = 1
  foreach ($row in $rows) { $xml += '<w:tr>' + ((@($number) + $row | ForEach-Object { & $cell $_ $false }) -join '') + '</w:tr>'; $number++ }
  $xml + '</w:tbl>'
}

$meanings = @{
  id='Ma dinh danh'; user_id='Ma tai khoan nguoi dung'; username='Ten dang nhap'; password='Mat khau'; email='Dia chi email'; registered='Thoi gian dang ky'; activation_code='Ma kich hoat tai khoan'; role='Vai tro nguoi dung'; status='Trang thai ban ghi'; created_at='Thoi gian tao'; updated_at='Thoi gian cap nhat';
  full_name='Ho va ten'; student_code='Ma sinh vien'; company_code='Ma doanh nghiep'; company_name='Ten doanh nghiep'; lecturer_code='Ma giang vien'; phone='So dien thoai'; address='Dia chi'; major='Chuyen nganh'; class_name='Ten lop'; faculty='Khoa'; cohort='Khoa hoc'; date_of_birth='Ngay sinh'; gender='Gioi tinh'; gpa='Diem trung binh'; skills='Ky nang'; cv_file='Tep CV'; tax_code='Ma so thue'; website='Website'; industry='Linh vuc hoat dong'; description='Mo ta'; verified='Trang thai xac minh';
  company_id='Ma doanh nghiep'; lecturer_id='Ma giang vien'; student_id='Ma sinh vien'; post_id='Ma bai dang'; application_id='Ma don ung tuyen'; internship_id='Ma ky thuc tap'; report_id='Ma bao cao'; actor_user_id='Ma nguoi thuc hien'; evaluator_user_id='Ma nguoi danh gia';
  title='Tieu de'; content='Noi dung'; requirements='Yeu cau'; benefits='Quyen loi'; location='Dia diem'; employment_type='Hinh thuc lam viec'; quantity='So luong tuyen'; deadline='Han ung tuyen'; cover_letter='Thu xin ung tuyen'; resume_url='Duong dan CV'; applied_at='Thoi gian nop don'; reviewed_at='Thoi gian xet duyet'; start_date='Ngay bat dau'; end_date='Ngay ket thuc'; company_supervisor='Nguoi huong dan tai doanh nghiep'; action='Ten thao tac'; report_type='Loai bao cao'; submitted_at='Thoi gian nop bao cao'; evaluator_type='Loai nguoi danh gia'; score='Diem danh gia'; comments='Nhan xet'
}
$sqlText = Get-Content (Join-Path $PSScriptRoot 'sql/phplogin.sql') -Raw
$schemaRows = [ordered]@{}
$tableMatches = [regex]::Matches($sqlText, '(?ms)CREATE TABLE IF NOT EXISTS \x60(?<table>[^\x60]+)\x60\s*\((?<body>.*?)\) ENGINE=')
foreach ($match in $tableMatches) {
  $rows = @()
  $columnMatches = [regex]::Matches($match.Groups['body'].Value, '(?m)^\s*\x60(?<name>[^\x60]+)\x60\s+(?<type>[A-Z]+(?:\([^)]*\))?(?:\s+UNSIGNED)?(?:\s+AUTO_INCREMENT)?)(?<rest>[^\r\n]*)')
  foreach ($column in $columnMatches) {
    $name = $column.Groups['name'].Value; $type = $column.Groups['type'].Value.Trim(); $rest = $column.Groups['rest'].Value
    $domain = if ($type -like 'ENUM*') { $type.Substring(5).Trim('()').Replace("'", '') } elseif ($type -like 'VARCHAR*') { "String max $($type -replace '[^0-9]', '') chars" } elseif ($type -like '*DATE*' -or $type -eq 'TIMESTAMP' -or $type -eq 'DATETIME') { 'Valid date/time' } elseif ($type -like 'TEXT*') { 'Text' } elseif ($type -like 'DECIMAL*') { 'Decimal number' } elseif ($type -like '*INT*') { 'Integer' } else { $type }
    if ($rest -match 'DEFAULT NULL' -or $rest -notmatch 'NOT NULL') { $domain += ' or NULL' }
    $note = if ($name -eq 'id') { 'Primary key' } else { '' }
    if ($rest -match 'NOT NULL') { $note += if ($note) { ', required' } else { 'Required' } }
    if ($rest -match 'AUTO_INCREMENT') { $note += ', auto increment' }
    if ($rest -match 'DEFAULT') { $note += ', has default value' }
    $meaning = if ($meanings.ContainsKey($name)) { $meanings[$name] } else { "Attribute $name" }
    $rows += ,@($name, $type, $domain, $meaning, $note.Trim(', '))
  }
  $schemaRows[$match.Groups['table'].Value] = $rows
}
$body = '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>DATABASE DESIGN - INTERNSHIP MANAGEMENT</w:t></w:r></w:p><w:p><w:r><w:t>Detailed attribute tables based on the requested format</w:t></w:r></w:p>'
foreach ($entry in $schemaRows.GetEnumerator()) { $body += & $table $entry.Key $entry.Value }
$docXml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?><w:document xmlns:w='http://schemas.openxmlformats.org/wordprocessingml/2006/main'><w:body>$body<w:sectPr><w:pgSz w:w='12240' w:h='15840'/><w:pgMar w:top='720' w:right='720' w:bottom='720' w:left='720'/></w:sectPr></w:body></w:document>"

$rels = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
'@

$contentTypes = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
'@

$core = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Thiết kế CSDL hệ thống thực tập sinh viên</dc:title>
  <dc:creator>GitHub Copilot</dc:creator>
  <cp:lastModifiedBy>GitHub Copilot</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">2026-09-15T00:00:00Z</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">2026-09-15T00:00:00Z</dcterms:modified>
</cp:coreProperties>
'@

$app = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Office Word</Application>
</Properties>
'@

[System.IO.File]::WriteAllText((Join-Path $temp 'word/document.xml'), $docXml, [System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText((Join-Path $temp '_rels/.rels'), $rels, [System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText((Join-Path $temp '[Content_Types].xml'), $contentTypes, [System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText((Join-Path $temp 'docProps/core.xml'), $core, [System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText((Join-Path $temp 'docProps/app.xml'), $app, [System.Text.UTF8Encoding]::new($false))

[void][System.Reflection.Assembly]::LoadWithPartialName('System.IO.Compression.FileSystem')
[System.IO.Compression.ZipFile]::CreateFromDirectory($temp, $docxPath)
Remove-Item $temp -Recurse -Force

Write-Output "Created docx: $docxPath"
If (Test-Path $docxPath) { Get-Item $docxPath | Select-Object FullName, Length, LastWriteTime }
