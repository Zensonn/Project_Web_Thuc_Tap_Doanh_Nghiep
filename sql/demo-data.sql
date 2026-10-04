-- Import with phpMyAdmin (select database phplogin, then Import) or run with mysql.
-- Demo login password for every account below: password
-- Accounts: demo_admin, demo_company, demo_lecturer, demo_student, demo_student2

USE `phplogin`;
SET @demo_password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
-- Keep the demo log columns in sync with the fields used by the PHP pages.
ALTER TABLE `internship_logs`
    ADD COLUMN IF NOT EXISTS `log_date` date DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `content` text DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `result` text DEFAULT NULL;

START TRANSACTION;

INSERT IGNORE INTO `users` (`username`, `password`, `email`, `activation_code`, `role`, `status`) VALUES
    ('demo_admin', @demo_password_hash, 'demo-admin@example.test', 'activated', 'admin', 'active'),
    ('demo_company', @demo_password_hash, 'demo-company@example.test', 'activated', 'company', 'active'),
    ('demo_lecturer', @demo_password_hash, 'demo-lecturer@example.test', 'activated', 'lecturer', 'active'),
    ('demo_student', @demo_password_hash, 'demo-student@example.test', 'activated', 'student', 'active'),
    ('demo_student2', @demo_password_hash, 'demo-student2@example.test', 'activated', 'student', 'active');

UPDATE `users`
SET `password` = @demo_password_hash, `activation_code` = 'activated', `status` = 'active'
WHERE `username` IN ('demo_admin', 'demo_company', 'demo_lecturer', 'demo_student', 'demo_student2');

INSERT INTO `majors` (`name`, `faculty`, `status`) VALUES
    ('Cong nghe thong tin', 'Khoa Cong nghe', 'active'),
    ('Thiet ke do hoa', 'Khoa My thuat ung dung', 'active')
ON DUPLICATE KEY UPDATE `faculty` = VALUES(`faculty`), `status` = VALUES(`status`);

INSERT INTO `companies` (`user_id`, `company_code`, `company_name`, `tax_code`, `phone`, `address`, `website`, `industry`, `description`, `verified`)
SELECT u.`id`, 'DEMO-COMP-01', 'Cong ty Demo Tech', '0312345678', '0901234567', '123 Nguyen Hue, Quan 1, TP Ho Chi Minh', 'https://example.test', 'Cong nghe thong tin', 'Doanh nghiep mau phuc vu demo quan ly thuc tap.', 1
FROM `users` u
WHERE u.`username` = 'demo_company'
ON DUPLICATE KEY UPDATE
    `company_name` = VALUES(`company_name`), `tax_code` = VALUES(`tax_code`), `phone` = VALUES(`phone`),
    `address` = VALUES(`address`), `website` = VALUES(`website`), `industry` = VALUES(`industry`),
    `description` = VALUES(`description`), `verified` = VALUES(`verified`);

INSERT INTO `lecturers` (`user_id`, `lecturer_code`, `full_name`, `department`, `phone`, `academic_title`)
SELECT u.`id`, 'DEMO-GV-001', 'Nguyen Thi Minh Anh', 'Cong nghe phan mem', '0902345678', 'Thac si'
FROM `users` u
WHERE u.`username` = 'demo_lecturer'
ON DUPLICATE KEY UPDATE
    `full_name` = VALUES(`full_name`), `department` = VALUES(`department`),
    `phone` = VALUES(`phone`), `academic_title` = VALUES(`academic_title`);

INSERT INTO `students` (`user_id`, `student_code`, `full_name`, `date_of_birth`, `gender`, `phone`, `address`, `major`, `class_name`, `cohort`, `faculty`, `gpa`, `skills`, `cv_file`)
SELECT u.`id`, '20260001', 'Tran Minh Khoa', '2004-05-14', 'male', '0912345678', 'Thu Duc, TP Ho Chi Minh', 'Cong nghe thong tin', 'CNTT-K26', 'K26', 'Khoa Cong nghe', 3.45, 'PHP, MySQL, HTML, CSS, Git', NULL
FROM `users` u
WHERE u.`username` = 'demo_student'
ON DUPLICATE KEY UPDATE
    `full_name` = VALUES(`full_name`), `date_of_birth` = VALUES(`date_of_birth`), `gender` = VALUES(`gender`),
    `phone` = VALUES(`phone`), `address` = VALUES(`address`), `major` = VALUES(`major`),
    `class_name` = VALUES(`class_name`), `cohort` = VALUES(`cohort`), `faculty` = VALUES(`faculty`),
    `gpa` = VALUES(`gpa`), `skills` = VALUES(`skills`);

INSERT INTO `students` (`user_id`, `student_code`, `full_name`, `date_of_birth`, `gender`, `phone`, `address`, `major`, `class_name`, `cohort`, `faculty`, `gpa`, `skills`, `cv_file`)
SELECT u.`id`, '20260002', 'Le Ngoc Mai', '2004-11-02', 'female', '0987654321', 'Go Vap, TP Ho Chi Minh', 'Thiet ke do hoa', 'TKDH-K26', 'K26', 'Khoa My thuat ung dung', 3.72, 'Figma, Photoshop, UI design', NULL
FROM `users` u
WHERE u.`username` = 'demo_student2'
ON DUPLICATE KEY UPDATE
    `full_name` = VALUES(`full_name`), `date_of_birth` = VALUES(`date_of_birth`), `gender` = VALUES(`gender`),
    `phone` = VALUES(`phone`), `address` = VALUES(`address`), `major` = VALUES(`major`),
    `class_name` = VALUES(`class_name`), `cohort` = VALUES(`cohort`), `faculty` = VALUES(`faculty`),
    `gpa` = VALUES(`gpa`), `skills` = VALUES(`skills`);

INSERT INTO `internship_posts` (`company_id`, `title`, `description`, `requirements`, `benefits`, `location`, `employment_type`, `quantity`, `deadline`, `status`)
SELECT c.`id`, 'Thuc tap sinh PHP Developer', 'Ho tro phat trien va kiem thu cac chuc nang ung dung web noi bo.', 'Biet PHP co ban, SQL va Git.', 'Co nguoi huong dan, xac nhan thuc tap.', 'TP Ho Chi Minh', 'hybrid', 2, '2027-06-30', 'published'
FROM `companies` c JOIN `users` u ON u.`id` = c.`user_id`
WHERE u.`username` = 'demo_company'
  AND NOT EXISTS (SELECT 1 FROM `internship_posts` p WHERE p.`company_id` = c.`id` AND p.`title` = 'Thuc tap sinh PHP Developer');

INSERT INTO `internship_posts` (`company_id`, `title`, `description`, `requirements`, `benefits`, `location`, `employment_type`, `quantity`, `deadline`, `status`)
SELECT c.`id`, 'Thuc tap sinh QA Tester', 'Tham gia viet test case va ghi nhan loi cho san pham web.', 'Can than, biet quy trinh kiem thu co ban.', 'Dao tao quy trinh QA, co co hoi lam du an that.', 'TP Ho Chi Minh', 'full_time', 1, '2027-05-31', 'published'
FROM `companies` c JOIN `users` u ON u.`id` = c.`user_id`
WHERE u.`username` = 'demo_company'
  AND NOT EXISTS (SELECT 1 FROM `internship_posts` p WHERE p.`company_id` = c.`id` AND p.`title` = 'Thuc tap sinh QA Tester');

INSERT INTO `applications` (`post_id`, `student_id`, `lecturer_id`, `cover_letter`, `resume_url`, `status`, `applied_at`, `reviewed_at`, `accepted_at`)
SELECT p.`id`, s.`id`, l.`id`, 'Toi muon ap dung kien thuc PHP va MySQL vao du an thuc te.', NULL, 'accepted', '2026-08-20 09:00:00', '2026-08-22 14:00:00', '2026-08-22 14:00:00'
FROM `internship_posts` p
JOIN `companies` c ON c.`id` = p.`company_id`
JOIN `users` cu ON cu.`id` = c.`user_id` AND cu.`username` = 'demo_company'
JOIN `students` s ON s.`student_code` = '20260001'
JOIN `lecturers` l ON l.`lecturer_code` = 'DEMO-GV-001'
WHERE p.`title` = 'Thuc tap sinh PHP Developer'
ON DUPLICATE KEY UPDATE
    `lecturer_id` = VALUES(`lecturer_id`), `status` = VALUES(`status`),
    `reviewed_at` = VALUES(`reviewed_at`), `accepted_at` = VALUES(`accepted_at`);

INSERT INTO `applications` (`post_id`, `student_id`, `lecturer_id`, `cover_letter`, `resume_url`, `status`, `applied_at`)
SELECT p.`id`, s.`id`, l.`id`, 'Toi quan tam vi tri QA va muon hoc them kiem thu phan mem.', NULL, 'submitted', '2026-09-25 10:30:00'
FROM `internship_posts` p
JOIN `companies` c ON c.`id` = p.`company_id`
JOIN `users` cu ON cu.`id` = c.`user_id` AND cu.`username` = 'demo_company'
JOIN `students` s ON s.`student_code` = '20260002'
JOIN `lecturers` l ON l.`lecturer_code` = 'DEMO-GV-001'
WHERE p.`title` = 'Thuc tap sinh QA Tester'
ON DUPLICATE KEY UPDATE `lecturer_id` = VALUES(`lecturer_id`), `status` = VALUES(`status`);

INSERT INTO `internships` (`application_id`, `student_id`, `company_id`, `lecturer_id`, `post_id`, `start_date`, `end_date`, `status`, `company_supervisor`)
SELECT a.`id`, a.`student_id`, p.`company_id`, a.`lecturer_id`, a.`post_id`, '2026-09-01', '2026-12-31', 'ongoing', 'Pham Quoc Bao'
FROM `applications` a
JOIN `students` s ON s.`id` = a.`student_id` AND s.`student_code` = '20260001'
JOIN `internship_posts` p ON p.`id` = a.`post_id` AND p.`title` = 'Thuc tap sinh PHP Developer'
WHERE a.`status` = 'accepted'
  AND NOT EXISTS (SELECT 1 FROM `internships` i WHERE i.`application_id` = a.`id`);

UPDATE `internships` i
JOIN `applications` a ON a.`id` = i.`application_id`
JOIN `students` s ON s.`id` = a.`student_id`
JOIN `internship_posts` p ON p.`id` = a.`post_id`
SET i.`start_date` = '2026-09-01', i.`end_date` = '2026-12-31', i.`status` = 'ongoing', i.`company_supervisor` = 'Pham Quoc Bao'
WHERE s.`student_code` = '20260001' AND p.`title` = 'Thuc tap sinh PHP Developer';

INSERT INTO `internship_logs` (`internship_id`, `actor_user_id`, `action`, `description`, `log_date`, `content`, `result`, `created_at`)
SELECT i.`id`, u.`id`, 'daily_log', 'Demo log entry', '2026-09-05', 'Cai dat moi truong PHP, ket noi MySQL va doc cau truc du an.', 'Khoi dong duoc ung dung tren moi truong local.', '2026-09-05 16:00:00'
FROM `internships` i
JOIN `applications` a ON a.`id` = i.`application_id`
JOIN `students` s ON s.`id` = a.`student_id` AND s.`student_code` = '20260001'
JOIN `users` u ON u.`id` = s.`user_id`
WHERE NOT EXISTS (SELECT 1 FROM `internship_logs` il WHERE il.`internship_id` = i.`id` AND il.`action` = 'daily_log' AND il.`log_date` = '2026-09-05');

INSERT INTO `internship_logs` (`internship_id`, `actor_user_id`, `action`, `description`, `log_date`, `content`, `result`, `created_at`)
SELECT i.`id`, u.`id`, 'daily_log', 'Demo log entry', '2026-09-12', 'Xay dung truy van danh sach va bo loc bai dang thuc tap.', 'Hoan thanh bo loc theo dia diem va hinh thuc lam viec.', '2026-09-12 16:30:00'
FROM `internships` i
JOIN `applications` a ON a.`id` = i.`application_id`
JOIN `students` s ON s.`id` = a.`student_id` AND s.`student_code` = '20260001'
JOIN `users` u ON u.`id` = s.`user_id`
WHERE NOT EXISTS (SELECT 1 FROM `internship_logs` il WHERE il.`internship_id` = i.`id` AND il.`action` = 'daily_log' AND il.`log_date` = '2026-09-12');

INSERT INTO `reports` (`internship_id`, `student_id`, `title`, `content`, `file_url`, `report_type`, `status`, `submitted_at`, `reviewed_at`)
SELECT i.`id`, i.`student_id`, 'Bao cao tuan 1 - Lam quen du an', 'Da cai dat moi truong, doc tai lieu va chay thu cac chuc nang hien co.', NULL, 'weekly', 'reviewed', '2026-09-08 15:00:00', '2026-09-10 11:00:00'
FROM `internships` i
JOIN `applications` a ON a.`id` = i.`application_id`
JOIN `students` s ON s.`id` = a.`student_id` AND s.`student_code` = '20260001'
WHERE NOT EXISTS (SELECT 1 FROM `reports` r WHERE r.`internship_id` = i.`id` AND r.`title` = 'Bao cao tuan 1 - Lam quen du an');

INSERT INTO `reports` (`internship_id`, `student_id`, `title`, `content`, `file_url`, `report_type`, `status`, `submitted_at`)
SELECT i.`id`, i.`student_id`, 'Bao cao tuan 2 - Bo loc bai dang', 'Da hoan thien bo loc bai dang theo tu khoa, dia diem va hinh thuc.', NULL, 'weekly', 'submitted', '2026-09-15 16:00:00'
FROM `internships` i
JOIN `applications` a ON a.`id` = i.`application_id`
JOIN `students` s ON s.`id` = a.`student_id` AND s.`student_code` = '20260001'
WHERE NOT EXISTS (SELECT 1 FROM `reports` r WHERE r.`internship_id` = i.`id` AND r.`title` = 'Bao cao tuan 2 - Bo loc bai dang');

INSERT INTO `evaluations` (`internship_id`, `report_id`, `evaluator_user_id`, `evaluator_type`, `score`, `comments`, `status`)
SELECT i.`id`, r.`id`, lecturer_user.`id`, 'lecturer', 8.50, 'Trinh bay ro tien do. Tiep tuc bo sung test case cho cac nhanh loi.', 'submitted'
FROM `internships` i
JOIN `applications` a ON a.`id` = i.`application_id`
JOIN `students` s ON s.`id` = a.`student_id` AND s.`student_code` = '20260001'
JOIN `reports` r ON r.`internship_id` = i.`id` AND r.`title` = 'Bao cao tuan 1 - Lam quen du an'
JOIN `lecturers` l ON l.`id` = i.`lecturer_id`
JOIN `users` lecturer_user ON lecturer_user.`id` = l.`user_id`
WHERE NOT EXISTS (SELECT 1 FROM `evaluations` e WHERE e.`internship_id` = i.`id` AND e.`evaluator_user_id` = lecturer_user.`id` AND e.`evaluator_type` = 'lecturer')
ON DUPLICATE KEY UPDATE `report_id` = VALUES(`report_id`), `score` = VALUES(`score`), `comments` = VALUES(`comments`), `status` = VALUES(`status`);

INSERT INTO `evaluations` (`internship_id`, `report_id`, `evaluator_user_id`, `evaluator_type`, `score`, `comments`, `status`)
SELECT i.`id`, NULL, company_user.`id`, 'company', 9.00, 'Chu dong, hoan thanh cong viec dung tien do va phoi hop tot.', 'submitted'
FROM `internships` i
JOIN `applications` a ON a.`id` = i.`application_id`
JOIN `students` s ON s.`id` = a.`student_id` AND s.`student_code` = '20260001'
JOIN `companies` c ON c.`id` = i.`company_id`
JOIN `users` company_user ON company_user.`id` = c.`user_id`
WHERE NOT EXISTS (SELECT 1 FROM `evaluations` e WHERE e.`internship_id` = i.`id` AND e.`evaluator_user_id` = company_user.`id` AND e.`evaluator_type` = 'company')
ON DUPLICATE KEY UPDATE `score` = VALUES(`score`), `comments` = VALUES(`comments`), `status` = VALUES(`status`);

COMMIT;

-- Demo accounts (all use the password "password"):
-- demo_admin, demo_company, demo_lecturer, demo_student, demo_student2
