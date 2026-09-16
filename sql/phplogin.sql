CREATE DATABASE IF NOT EXISTS `phplogin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `phplogin`;

CREATE TABLE IF NOT EXISTS `users` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`username` varchar(50) NOT NULL,
	`password` varchar(255) NOT NULL,
	`email` varchar(100) NOT NULL,
	`registered` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`activation_code` varchar(50) DEFAULT NULL,
	`role` enum('student','company','lecturer','admin') NOT NULL DEFAULT 'student',
	`status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_users_username` (`username`),
	UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER //
CREATE PROCEDURE `migrate_legacy_accounts`()
BEGIN
	IF EXISTS (
		SELECT 1 FROM information_schema.TABLES
		WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accounts' AND TABLE_TYPE = 'BASE TABLE'
	) THEN
		INSERT IGNORE INTO `users` (`id`, `username`, `password`, `email`, `registered`, `activation_code`, `role`)
		SELECT `id`, `username`, `password`, `email`, `registered`, `activation_code`, `role`
		FROM `accounts`;
		DROP TABLE `accounts`;
	END IF;
END//
DELIMITER ;
CALL `migrate_legacy_accounts`();
DROP PROCEDURE `migrate_legacy_accounts`;

CREATE TABLE IF NOT EXISTS `students` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int unsigned NOT NULL,
	`student_code` varchar(30) NOT NULL,
	`full_name` varchar(150) NOT NULL,
	`date_of_birth` date DEFAULT NULL,
	`gender` enum('male','female','other') DEFAULT NULL,
	`phone` varchar(30) DEFAULT NULL,
	`address` varchar(255) DEFAULT NULL,
	`major` varchar(150) DEFAULT NULL,
	`class_name` varchar(100) DEFAULT NULL,
	`cohort` varchar(50) DEFAULT NULL,
	`faculty` varchar(150) DEFAULT NULL,
	`gpa` decimal(3,2) DEFAULT NULL,
	`skills` text,
	`cv_file` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_students_user` (`user_id`),
	UNIQUE KEY `uq_students_code` (`student_code`),
	CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `students`
	ADD COLUMN IF NOT EXISTS `cohort` varchar(50) DEFAULT NULL,
	ADD COLUMN IF NOT EXISTS `skills` text,
	ADD COLUMN IF NOT EXISTS `cv_file` varchar(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `companies` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int unsigned NOT NULL,
	`company_code` varchar(30) NOT NULL,
	`company_name` varchar(200) NOT NULL,
	`tax_code` varchar(30) DEFAULT NULL,
	`phone` varchar(30) DEFAULT NULL,
	`address` varchar(255) DEFAULT NULL,
	`website` varchar(255) DEFAULT NULL,
	`industry` varchar(150) DEFAULT NULL,
	`description` text,
	`verified` tinyint(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_companies_user` (`user_id`),
	UNIQUE KEY `uq_companies_code` (`company_code`),
	CONSTRAINT `fk_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `companies`
	ADD COLUMN IF NOT EXISTS `industry` varchar(150) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `lecturers` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int unsigned NOT NULL,
	`lecturer_code` varchar(30) NOT NULL,
	`full_name` varchar(150) NOT NULL,
	`department` varchar(150) DEFAULT NULL,
	`phone` varchar(30) DEFAULT NULL,
	`academic_title` varchar(100) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_lecturers_user` (`user_id`),
	UNIQUE KEY `uq_lecturers_code` (`lecturer_code`),
	CONSTRAINT `fk_lecturers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `internship_posts` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`company_id` int unsigned NOT NULL,
	`title` varchar(200) NOT NULL,
	`description` text NOT NULL,
	`requirements` text,
	`benefits` text,
	`location` varchar(200) DEFAULT NULL,
	`employment_type` enum('full_time','part_time','remote','hybrid') DEFAULT 'full_time',
	`quantity` smallint unsigned NOT NULL DEFAULT 1,
	`deadline` date DEFAULT NULL,
	`status` enum('draft','published','closed','cancelled') NOT NULL DEFAULT 'draft',
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `idx_posts_company_status` (`company_id`,`status`),
	CONSTRAINT `fk_posts_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `applications` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`post_id` int unsigned NOT NULL,
	`student_id` int unsigned NOT NULL,
	`cover_letter` text,
	`resume_url` varchar(255) DEFAULT NULL,
	`status` enum('submitted','reviewing','accepted','rejected','withdrawn') NOT NULL DEFAULT 'submitted',
	`applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`reviewed_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_application_student_post` (`post_id`,`student_id`),
	KEY `idx_applications_student` (`student_id`),
	CONSTRAINT `fk_applications_post` FOREIGN KEY (`post_id`) REFERENCES `internship_posts` (`id`) ON DELETE CASCADE,
	CONSTRAINT `fk_applications_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `internships` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`application_id` int unsigned NOT NULL,
	`student_id` int unsigned NOT NULL,
	`company_id` int unsigned NOT NULL,
	`lecturer_id` int unsigned DEFAULT NULL,
	`post_id` int unsigned NOT NULL,
	`start_date` date NOT NULL,
	`end_date` date DEFAULT NULL,
	`status` enum('planned','ongoing','completed','cancelled') NOT NULL DEFAULT 'planned',
	`company_supervisor` varchar(150) DEFAULT NULL,
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_internship_application` (`application_id`),
	KEY `idx_internships_student_status` (`student_id`,`status`),
	CONSTRAINT `fk_internships_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
	CONSTRAINT `fk_internships_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
	CONSTRAINT `fk_internships_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
	CONSTRAINT `fk_internships_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE SET NULL,
	CONSTRAINT `fk_internships_post` FOREIGN KEY (`post_id`) REFERENCES `internship_posts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `internship_logs` (
	`id` bigint unsigned NOT NULL AUTO_INCREMENT,
	`internship_id` int unsigned NOT NULL,
	`actor_user_id` int unsigned DEFAULT NULL,
	`action` varchar(100) NOT NULL,
	`description` text,
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `idx_logs_internship_created` (`internship_id`,`created_at`),
	CONSTRAINT `fk_logs_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
	CONSTRAINT `fk_logs_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reports` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`internship_id` int unsigned NOT NULL,
	`student_id` int unsigned NOT NULL,
	`title` varchar(200) NOT NULL,
	`content` longtext,
	`file_url` varchar(255) DEFAULT NULL,
	`report_type` enum('weekly','midterm','final','other') NOT NULL DEFAULT 'weekly',
	`status` enum('draft','submitted','reviewed','needs_revision') NOT NULL DEFAULT 'draft',
	`submitted_at` timestamp NULL DEFAULT NULL,
	`reviewed_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_reports_internship` (`internship_id`),
	CONSTRAINT `fk_reports_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
	CONSTRAINT `fk_reports_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evaluations` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`internship_id` int unsigned NOT NULL,
	`report_id` int unsigned DEFAULT NULL,
	`evaluator_user_id` int unsigned NOT NULL,
	`evaluator_type` enum('company','lecturer') NOT NULL,
	`score` decimal(4,2) DEFAULT NULL,
	`comments` text,
	`status` enum('draft','submitted') NOT NULL DEFAULT 'draft',
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_evaluation_evaluator` (`internship_id`,`evaluator_user_id`,`evaluator_type`),
	KEY `idx_evaluations_report` (`report_id`),
	CONSTRAINT `fk_evaluations_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
	CONSTRAINT `fk_evaluations_report` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE SET NULL,
	CONSTRAINT `fk_evaluations_user` FOREIGN KEY (`evaluator_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `evaluations`
	ADD COLUMN IF NOT EXISTS `report_id` int unsigned DEFAULT NULL,
	ADD KEY IF NOT EXISTS `idx_evaluations_report` (`report_id`);

DELIMITER //
CREATE PROCEDURE `add_evaluation_report_fk`()
BEGIN
	IF NOT EXISTS (
		SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS
		WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'fk_evaluations_report'
	) THEN
		ALTER TABLE `evaluations`
			ADD CONSTRAINT `fk_evaluations_report` FOREIGN KEY (`report_id`)
			REFERENCES `reports` (`id`) ON DELETE SET NULL;
	END IF;
END//
DELIMITER ;
CALL `add_evaluation_report_fk`();
DROP PROCEDURE `add_evaluation_report_fk`;

DROP VIEW IF EXISTS `accounts`;

DELIMITER //
CREATE PROCEDURE `sync_legacy_accounts`()
BEGIN
	IF EXISTS (
		SELECT 1 FROM information_schema.TABLES
		WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accounts' AND TABLE_TYPE = 'BASE TABLE'
	) THEN
		INSERT IGNORE INTO `users` (`id`, `username`, `password`, `email`, `registered`, `activation_code`, `role`)
		SELECT `id`, `username`, `password`, `email`, `registered`, `activation_code`, `role`
		FROM `accounts`;
		DROP TABLE `accounts`;
	END IF;
END//
DELIMITER ;
CALL `sync_legacy_accounts`();
DROP PROCEDURE `sync_legacy_accounts`;

-- Compatibility view for the existing authentication PHP files.
CREATE OR REPLACE VIEW `accounts` AS
SELECT `id`, `username`, `password`, `email`, `registered`, `activation_code`, `role`
FROM `users`;

INSERT INTO `users` (`id`, `username`, `password`, `email`, `registered`, `activation_code`, `role`)
VALUES (1, 'test', '$2y$10$SfhYIDtn.iOuCW7zfoFLuuZHX6lja4lF4XA4JqNmpiH/.P3zB8JCa', 'test@example.com', '2025-01-01 00:00:00', 'activated', 'student')
ON DUPLICATE KEY UPDATE `id` = `id`;