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
- R-03 จัดการสมาชิก (ค้นหา เพิ่ม แก้ไข ระงับ/เปิดใช้งาน ไม่มีการลบ)
- R-04 ค้นหาหนังสือจากชื่อ/ผู้แต่ง/ISBN กรองหมวดหมู่ แบ่งหน้า 10 รายการ ป้าย "มีให้ยืม"/"หมดชั่วคราว" คงเหลือคำนวณจากรายการยืมจริง
- R-05 ยืมหนังสือ (`LoanService::borrow`) ใน transaction เดียว ล็อกแถวสมาชิกและหนังสือด้วย `SELECT ... FOR UPDATE` แจ้งเหตุผลเฉพาะเมื่อยืมไม่ได้
- R-06 คืนหนังสือ (`LoanService::return`) ล็อกแถว loan ปฏิเสธการคืนซ้ำ คำนวณค่าปรับ (วันที่เกิน × 5 บาท) และแสดงทันทีหลังคืน
- R-07 หน้ารายการเกินกำหนด พร้อมจำนวนวันที่เกินและค่าปรับสะสม ณ วันนี้ และแดชบอร์ดแสดงจำนวนรายการเกินกำหนด
- R-08 รายงานประวัติการยืม-คืน กรองตามสมาชิกและช่วงวันที่ยืม พร้อมสรุปจำนวนรายการและค่าปรับรวม
- ตัวรันทดสอบ CLI `tests/run.php` และสคริปต์ `tests/create-test-db.sql` สำหรับฐานข้อมูล `circulation_desk_test`
- เอกสาร `docs/BUILD.md`, `docs/test-cases.md`, `docs/traceability-matrix.md`, `docs/change-requests.md`

- `docs/ENVIRONMENT.md` (SCI-029) ตารางค่าโครงแบบสภาพแวดล้อม XAMPP และ Docker
- CMDB บน Google Sheet (SCI-030) และคู่มือการใช้งานสำหรับเจ้าหน้าที่ (SCI-031)
- CR-002 (ตัวอย่างเพิ่มคอลัมน์ `fine_paid_at`) ในทะเบียนคำขอเปลี่ยนแปลง สถานะรออนุมัติ CCB

### Changed
- ชื่อโฟลเดอร์และ URL ในเอกสารเปลี่ยนจาก `Circulation-Desk` เป็น `CirculationDesk` ให้ตรงกับชื่อ repository
- คู่มือการใช้งานเพิ่มขั้นตอนเข้าสู่ระบบ และพฤติกรรมของระบบเมื่อลบหมวดหมู่/หนังสือไม่ได้ ยืมไม่ได้ และหลังคืนหนังสือ
- `docs/02-sci-and-naming-convention.md` 0.2.0 ปรับสถานะ SCI และเพิ่ม SCI-024 ถึง SCI-028, `README.md` ปรับตัวเลขให้ตรงกับของจริง
- `config.php` อ่านตัวแปรสภาพแวดล้อมก่อน แล้วจึง fallback ไป `config.local.php` และเก็บกฎธุรกิจ BR-01..BR-04
- หน้าตรวจสภาพแวดล้อมเดิมใน `index.php` ย้ายไป `health.php` และ `index.php` เป็นแดชบอร์ด
