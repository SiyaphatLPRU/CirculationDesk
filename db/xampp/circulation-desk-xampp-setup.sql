-- ============================================================================
-- Circulation Desk - สคริปต์สร้างฐานข้อมูลสำหรับ XAMPP (MySQL/MariaDB ใน XAMPP)
-- Schema version: 1.0.0   |   ข้อมูลตัวอย่างสำหรับพัฒนาเท่านั้น
--
-- วิธีใช้ (phpMyAdmin): เปิด http://localhost/phpmyadmin -> แท็บ "นำเข้า" (Import)
--   -> เลือกไฟล์นี้ -> ชุดอักขระ utf-8 -> กด "ดำเนินการ"
--   หรือวางทั้งไฟล์ในแท็บ "SQL" แล้วกด "ดำเนินการ"
--
-- ก่อนรัน: เปลี่ยนรหัสผ่านของผู้ใช้ 'cdesk' ในส่วนที่ 2 (ค่าเริ่มต้นคือ change_me_app)
-- รันซ้ำ: ถ้ามีฐานข้อมูลอยู่แล้ว ตารางจะสร้างซ้ำไม่ได้ ให้ลบฐานข้อมูลเดิมก่อน
--   (phpMyAdmin -> เลือกฐานข้อมูล circulation_desk -> แท็บ "ดำเนินการ" -> ลบฐานข้อมูล)
-- ============================================================================

-- ส่วนที่ 1: สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS circulation_desk
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- ส่วนที่ 2: สร้างผู้ใช้สำหรับแอป (ไม่ใช้ root) และให้สิทธิ์เฉพาะฐานข้อมูลนี้
-- ให้ใส่ค่าเดียวกันนี้ใน src/config/config.local.php (user / password)
CREATE USER IF NOT EXISTS 'cdesk'@'localhost' IDENTIFIED BY 'change_me_app';
GRANT SELECT, INSERT, UPDATE, DELETE ON circulation_desk.* TO 'cdesk'@'localhost';
FLUSH PRIVILEGES;

USE circulation_desk;

-- ส่วนที่ 3: โครงสร้างตาราง (เหมือน db/init/01-schema.sql)
SET NAMES utf8mb4;

CREATE TABLE staff (
  staff_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(50)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(100) NOT NULL,
  role          ENUM('librarian','admin') NOT NULL DEFAULT 'librarian',
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (staff_id),
  UNIQUE KEY uq_staff_username (username)
) ENGINE=InnoDB;

CREATE TABLE members (
  member_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  member_code VARCHAR(20)  NOT NULL,
  full_name   VARCHAR(100) NOT NULL,
  phone       VARCHAR(20)  NULL,
  email       VARCHAR(100) NULL,
  status      ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (member_id),
  UNIQUE KEY uq_members_code (member_code)
) ENGINE=InnoDB;

CREATE TABLE categories (
  category_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  PRIMARY KEY (category_id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;

CREATE TABLE books (
  book_id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  isbn             VARCHAR(20)  NULL,
  title            VARCHAR(255) NOT NULL,
  author           VARCHAR(150) NOT NULL,
  category_id      INT UNSIGNED NOT NULL,
  copies_total     INT UNSIGNED NOT NULL DEFAULT 1,
  copies_available INT UNSIGNED NOT NULL DEFAULT 1,
  created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (book_id),
  UNIQUE KEY uq_books_isbn (isbn),
  KEY idx_books_title (title),
  CONSTRAINT fk_books_category FOREIGN KEY (category_id) REFERENCES categories (category_id),
  CONSTRAINT chk_books_copies CHECK (copies_available <= copies_total)
) ENGINE=InnoDB;

CREATE TABLE loans (
  loan_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  member_id   INT UNSIGNED NOT NULL,
  book_id     INT UNSIGNED NOT NULL,
  staff_id    INT UNSIGNED NOT NULL,
  borrowed_at DATE         NOT NULL,
  due_date    DATE         NOT NULL,
  returned_at DATE         NULL,
  fine_amount DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  status      ENUM('borrowed','returned') NOT NULL DEFAULT 'borrowed',
  PRIMARY KEY (loan_id),
  KEY idx_loans_member (member_id, status),
  KEY idx_loans_book (book_id, status),
  KEY idx_loans_due (due_date, status),
  CONSTRAINT fk_loans_member FOREIGN KEY (member_id) REFERENCES members (member_id),
  CONSTRAINT fk_loans_book   FOREIGN KEY (book_id)   REFERENCES books (book_id),
  CONSTRAINT fk_loans_staff  FOREIGN KEY (staff_id)  REFERENCES staff (staff_id)
) ENGINE=InnoDB;

-- ส่วนที่ 4: ข้อมูลตัวอย่าง (เหมือน db/init/02-seed.sql)
-- Dev login: username "admin" / password "admin1234"  (do not use in production)
SET NAMES utf8mb4;

INSERT INTO staff (username, password_hash, full_name, role) VALUES
  ('admin', '$2y$10$DkOo1ddSjA7kwHVLVG1Ypef7rj1Tb3XfmIfh6WEXbcXOMGVUviSAy', 'ผู้ดูแลระบบ (ตัวอย่าง)', 'admin');

INSERT INTO categories (name) VALUES
  ('วิศวกรรมซอฟต์แวร์'), ('ฐานข้อมูล'), ('คณิตศาสตร์และสถิติ');

INSERT INTO members (member_code, full_name, phone, email, status) VALUES
  ('M0001', 'สมชาย ใจดี',  '0810000001', 'somchai@example.com', 'active'),
  ('M0002', 'สมหญิง รักเรียน', '0810000002', 'somying@example.com', 'active'),
  ('M0003', 'ตัวอย่าง ถูกระงับ', NULL, NULL, 'suspended');

INSERT INTO books (isbn, title, author, category_id, copies_total, copies_available) VALUES
  ('9780000000011', 'Software Configuration Management Patterns', 'Berczuk', 1, 2, 2),
  ('9780000000028', 'Pro Git', 'Chacon and Straub', 1, 3, 3),
  ('9780000000035', 'Database System Concepts', 'Silberschatz', 2, 2, 2),
  ('9780000000042', 'Learning SQL', 'Beaulieu', 2, 1, 1),
  ('9780000000059', 'Statistics for Engineers', 'Sample', 3, 1, 1);
