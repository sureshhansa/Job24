-- =====================================================================
--  Job Portal - MySQL Schema + Seed Data
--  PHP 8.2 / MySQL 5.7+ / MariaDB 10.3+
--  Charset: utf8mb4
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
--  Database (create if you have privileges; on shared hosting use cPanel)
-- ---------------------------------------------------------------------
-- CREATE DATABASE IF NOT EXISTS `job_portal`
--   DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `job_portal`;

-- ---------------------------------------------------------------------
--  admins
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `email`         VARCHAR(180) NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  users (candidates)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `email`         VARCHAR(180) NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `phone`         VARCHAR(30)  DEFAULT NULL,
  `headline`      VARCHAR(180) DEFAULT NULL,
  `location`      VARCHAR(120) DEFAULT NULL,
  `bio`           TEXT         DEFAULT NULL,
  `resume_file`   VARCHAR(255) DEFAULT NULL,
  `status`        TINYINT NOT NULL DEFAULT 1,   -- 1 active, 0 blocked
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  categories
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `slug`          VARCHAR(140) NOT NULL,
  `icon`          VARCHAR(60)  DEFAULT 'bi-briefcase',
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  companies
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(160) NOT NULL,
  `slug`          VARCHAR(180) NOT NULL,
  `logo`          VARCHAR(255) DEFAULT NULL,
  `website`       VARCHAR(255) DEFAULT NULL,
  `location`      VARCHAR(120) DEFAULT NULL,
  `about`         TEXT         DEFAULT NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_companies_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  jobs
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(200) NOT NULL,
  `slug`          VARCHAR(230) NOT NULL,
  `category_id`   INT UNSIGNED NOT NULL,
  `company_id`    INT UNSIGNED NOT NULL,
  `location`      VARCHAR(120) DEFAULT NULL,
  `job_type`      ENUM('full-time','part-time','contract','internship','remote') NOT NULL DEFAULT 'full-time',
  `salary_min`    INT UNSIGNED DEFAULT NULL,
  `salary_max`    INT UNSIGNED DEFAULT NULL,
  `description`   MEDIUMTEXT NOT NULL,
  `requirements`  MEDIUMTEXT DEFAULT NULL,
  `apply_type`    ENUM('internal','external') NOT NULL DEFAULT 'internal',
  `external_url`  VARCHAR(255) DEFAULT NULL,
  `is_featured`   TINYINT NOT NULL DEFAULT 0,
  `status`        TINYINT NOT NULL DEFAULT 1,   -- 1 published, 0 draft/closed
  `views`         INT UNSIGNED NOT NULL DEFAULT 0,
  `deadline`      DATE DEFAULT NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jobs_slug` (`slug`),
  KEY `idx_jobs_category` (`category_id`),
  KEY `idx_jobs_company` (`company_id`),
  KEY `idx_jobs_status` (`status`),
  KEY `idx_jobs_type` (`job_type`),
  CONSTRAINT `fk_jobs_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jobs_company`  FOREIGN KEY (`company_id`)  REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  applications
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id`        INT UNSIGNED NOT NULL,
  `user_id`       INT UNSIGNED NOT NULL,
  `cover_letter`  TEXT DEFAULT NULL,
  `resume_file`   VARCHAR(255) DEFAULT NULL,
  `status`        ENUM('pending','reviewed','shortlisted','rejected','hired') NOT NULL DEFAULT 'pending',
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_application` (`job_id`, `user_id`),
  KEY `idx_app_user` (`user_id`),
  KEY `idx_app_status` (`status`),
  CONSTRAINT `fk_app_job`  FOREIGN KEY (`job_id`)  REFERENCES `jobs` (`id`)  ON DELETE CASCADE,
  CONSTRAINT `fk_app_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  SEED DATA
-- =====================================================================

-- Admin login:  admin@jobportal.test  /  Admin@123
INSERT INTO `admins` (`name`, `email`, `password`) VALUES
('Site Admin', 'admin@jobportal.test', '$2b$10$oa0t3OZgUizxcttk/kKrnuCO0I7ke6haRRmfuks/qJ9d0pdos3Hj6');

-- Candidate login:  john@example.com  /  User@123
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `headline`, `location`) VALUES
('John Candidate', 'john@example.com', '$2b$10$knezfmQdrNBzDDxku8HwE.ebNpvrJUV.NS9UBBmB6AE1jVz5pTVlK', '+1 555 0100', 'Full-Stack Developer', 'New York, NY');

INSERT INTO `categories` (`name`, `slug`, `icon`) VALUES
('Software Development', 'software-development', 'bi-code-slash'),
('Design & Creative',    'design-creative',     'bi-palette'),
('Marketing & Sales',    'marketing-sales',     'bi-megaphone'),
('Customer Support',     'customer-support',    'bi-headset'),
('Finance & Accounting', 'finance-accounting',  'bi-cash-coin'),
('Human Resources',      'human-resources',     'bi-people');

INSERT INTO `companies` (`name`, `slug`, `website`, `location`, `about`) VALUES
('Acme Technologies', 'acme-technologies', 'https://acme.example.com', 'San Francisco, CA', 'A leading product company building developer tools.'),
('BlueWave Media',    'bluewave-media',    'https://bluewave.example.com', 'Austin, TX', 'Creative agency specialising in brand and digital design.'),
('FinEdge Capital',   'finedge-capital',   'https://finedge.example.com', 'New York, NY', 'Fintech firm modernising personal finance.');

INSERT INTO `jobs`
(`title`,`slug`,`category_id`,`company_id`,`location`,`job_type`,`salary_min`,`salary_max`,`description`,`requirements`,`apply_type`,`external_url`,`is_featured`,`status`,`deadline`) VALUES
('Senior PHP Developer','senior-php-developer',1,1,'Remote','remote',90000,130000,
 'We are looking for a Senior PHP Developer to build and maintain high-traffic web applications.','PHP 8+, MySQL, REST APIs, 5+ years experience.','internal',NULL,1,1,'2026-12-31'),
('Frontend Engineer (React)','frontend-engineer-react',1,1,'San Francisco, CA','full-time',85000,120000,
 'Join our frontend team building delightful user interfaces with React and TypeScript.','React, TypeScript, CSS, 3+ years experience.','internal',NULL,1,1,'2026-12-31'),
('UI/UX Designer','ui-ux-designer',2,2,'Austin, TX','full-time',70000,95000,
 'Design intuitive product experiences from wireframe to high-fidelity mockups.','Figma, design systems, portfolio required.','external','https://bluewave.example.com/careers/ui-ux',0,1,'2026-11-30'),
('Digital Marketing Manager','digital-marketing-manager',3,2,'Remote','remote',60000,90000,
 'Own our digital marketing strategy across paid and organic channels.','SEO, SEM, analytics, 4+ years experience.','internal',NULL,0,1,'2026-12-15'),
('Financial Analyst','financial-analyst',5,3,'New York, NY','full-time',75000,105000,
 'Analyse financial data and build models to support strategic decisions.','Excel, SQL, finance degree, 2+ years experience.','internal',NULL,0,1,'2026-12-20'),
('Customer Support Specialist','customer-support-specialist',4,3,'Remote','part-time',35000,45000,
 'Help our customers succeed through email and chat support.','Excellent communication, empathy, problem solving.','external','https://finedge.example.com/jobs/support',0,1,'2026-11-15');

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
--  DEMO LOGINS (hashes below are REAL bcrypt — they work immediately):
--      Admin:      admin@jobportal.test  /  Admin@123    (at /admin/login)
--      Candidate:  john@example.com      /  User@123     (at /login)
--  No extra steps needed after import.
-- =====================================================================
