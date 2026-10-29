-- ============================================================================
-- Circulation Desk - ข้อมูลตัวอย่าง (SCI-005)
-- Schema version: 1.0.0   |   สำหรับพัฒนาเท่านั้น
-- Dev login: username "admin" / password "admin1234"  (do not use in production)
-- ============================================================================
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
