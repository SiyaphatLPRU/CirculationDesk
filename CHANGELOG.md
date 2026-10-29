# CHANGELOG

บันทึกการเปลี่ยนแปลงของ Circulation Desk ตามรูปแบบ [Keep a Changelog](https://keepachangelog.com/)
เลขรุ่นใช้ Semantic Versioning (ยังไม่ติดเลขรุ่น/tag ผู้สอนเป็นผู้กำหนด)

## [Unreleased]

### Added
- R-01 เข้าสู่ระบบ/ออกจากระบบเจ้าหน้าที่ (`password_verify`, `session_regenerate_id`, session cookie `httponly` + `SameSite=Lax`)
- ไลบรารีพื้นฐาน `src/lib/` (bootstrap, PDO singleton, CSRF, helper `e()`, `url()`, `flash()`, `redirect()`, `render()`)
- `src/config/config.local.example.php` แม่แบบค่าเฉพาะเครื่องสำหรับ XAMPP
- หน้า `health.php` แสดงรุ่นฐานข้อมูลที่เชื่อมต่อจริง
- R-02 จัดการหนังสือ (รายการ เพิ่ม แก้ไข ลบ) และหมวดหมู่ (เพิ่ม แก้ชื่อ ลบ) พร้อมตรวจข้อมูลฝั่งเซิร์ฟเวอร์
- ตัวรันทดสอบ CLI `tests/run.php` และสคริปต์ `tests/create-test-db.sql` สำหรับฐานข้อมูล `circulation_desk_test`
- เอกสาร `docs/BUILD.md`, `docs/test-cases.md`, `docs/traceability-matrix.md`, `docs/change-requests.md`

### Changed
- `config.php` อ่านตัวแปรสภาพแวดล้อมก่อน แล้วจึง fallback ไป `config.local.php` และเก็บกฎธุรกิจ BR-01..BR-04
- หน้าตรวจสภาพแวดล้อมเดิมใน `index.php` ย้ายไป `health.php` และ `index.php` เป็นแดชบอร์ด
