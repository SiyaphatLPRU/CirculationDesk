-- ============================================================================
-- Circulation Desk - โครงสร้างฐานข้อมูล (SCI-004)
-- Schema version: 1.0.0   (baseline - ห้ามแก้ไฟล์นี้ ให้เพิ่ม migration 03-... แทน)
-- ใช้ได้ทั้ง MySQL 8.0 (Docker) และ MariaDB 10.x (XAMPP)
-- ============================================================================
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
