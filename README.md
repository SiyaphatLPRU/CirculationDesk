# Circulation Desk – ระบบยืม-คืนหนังสือ

Mini Project ของผู้สอนรายวิชา 5672603 การจัดการโครงแบบซอฟต์แวร์ (SCM)
Repository: https://github.com/SiyaphatLPRU/CirculationDesk
PHP 8.2 ล้วน (ไม่ใช้ framework/Composer) + PDO + MySQL 8.0 / MariaDB (XAMPP)

## สถานะ

| รายการ | จำนวน / สถานะ |
|---|---|
| Requirements ที่พัฒนาเสร็จ | 8 / 8 (R-01 ถึง R-08) |
| กฎธุรกิจ | BR-01 ถึง BR-04 (ค่าอยู่ใน `src/config/config.php`) |
| ตารางฐานข้อมูล | 5 ตาราง (`staff`, `members`, `categories`, `books`, `loans`) Schema 1.0.0 |
| บริการ (services) | 5 (`BookService`, `CategoryService`, `MemberService`, `LoanService`, `ReportService`) |
| กรณีทดสอบอัตโนมัติ | 36 (AT-01 ถึง AT-36) ใน 8 ไฟล์ |
| กรณีทดสอบด้วยมือ | 54 (TC-01 ถึง TC-54) |
| รุ่น | 1.0.0 (Tag `v1.0.0`) · Baseline แรก `v0.1.0-baseline` |

## ฟังก์ชันหลัก

| รหัส | ฟังก์ชัน | หน้า |
|---|---|---|
| R-01 | เข้าสู่ระบบ/ออกจากระบบเจ้าหน้าที่ | `login.php`, `logout.php` |
| R-02 | จัดการหนังสือและหมวดหมู่ | `books/`, `categories/` |
| R-03 | จัดการสมาชิก | `members/` |
| R-04 | ค้นหาและดูสถานะหนังสือ | `books/index.php` |
| R-05 | ยืมหนังสือ | `loans/borrow.php` |
| R-06 | คืนหนังสือและค่าปรับ | `loans/return.php` |
| R-07 | รายการเกินกำหนด | `loans/overdue.php`, แดชบอร์ด `index.php` |
| R-08 | รายงานประวัติการยืม-คืน | `reports/history.php` |

หน้า `health.php` (ไม่ต้องเข้าสู่ระบบ) แสดงรุ่น PHP และรุ่นฐานข้อมูลที่เชื่อมต่ออยู่จริง

## เริ่มต้นใช้งาน

- **XAMPP**: ทำตาม [docs/BUILD.md](docs/BUILD.md) (นำเข้า `db/xampp/circulation-desk-xampp-setup.sql`, สร้าง `src/config/config.local.php`, เปิด `php -S localhost:8000 -t src/public`)
- บัญชีสำหรับพัฒนา: `admin` / `admin1234` (ห้ามใช้ในระบบจริง)
- ทดสอบ: `php tests/run.php` (ต้องเตรียมฐานข้อมูล `circulation_desk_test` ด้วย `tests/create-test-db.sql` ก่อน)

### Docker (ยังไม่ได้ทดสอบในรอบนี้)

```bash
cp .env.example .env   # แล้วเปลี่ยนรหัสผ่าน
docker compose up -d --build
# เปิด http://localhost:8080
```

## โครงสร้าง

```text
src/config/    ค่าตั้งระบบ (config.php, config.local.example.php)
src/lib/       bootstrap, db, auth, csrf, helpers
src/services/  ตรรกะธุรกิจ
src/views/     layout และ partial
src/public/    DocumentRoot
tests/         ตัวรันทดสอบ CLI และกรณีทดสอบ
db/init/       schema และข้อมูลตัวอย่าง (Docker)
db/xampp/      สคริปต์รวมสำหรับ phpMyAdmin
docs/          เอกสาร SCM
```

## เอกสาร

| เอกสาร | ไฟล์ |
|---|---|
| กฎบัตรและความต้องการ (SCI-001) | [docs/01-project-charter-and-requirements.md](docs/01-project-charter-and-requirements.md) |
| SCI และกติกาตั้งชื่อ (SCI-002) | [docs/02-sci-and-naming-convention.md](docs/02-sci-and-naming-convention.md) |
| ER และสถาปัตยกรรม (SCI-003) | [docs/03-design-er-and-architecture.md](docs/03-design-er-and-architecture.md) |
| คู่มือติดตั้งและรัน (SCI-006) | [docs/BUILD.md](docs/BUILD.md) |
| ทะเบียนคำขอเปลี่ยนแปลง (SCI-008) | [docs/change-requests.md](docs/change-requests.md) |
| Traceability Matrix (SCI-010) | [docs/traceability-matrix.md](docs/traceability-matrix.md) |
| กรณีทดสอบ (SCI-023) | [docs/test-cases.md](docs/test-cases.md) |
| บันทึกการเปลี่ยนแปลง (SCI-025) | [CHANGELOG.md](CHANGELOG.md) |
| โครงแบบสภาพแวดล้อม (SCI-029) | [docs/ENVIRONMENT.md](docs/ENVIRONMENT.md) |
| ฐานข้อมูลโครงแบบ CMDB (SCI-030) | [Google Sheet: CMDB - Circulation Desk](https://docs.google.com/spreadsheets/d/1CFOxE0rC2GueMrVz3em4DXkLlYoonNWbkejcUn7f4sk/edit) |
| คู่มือการใช้งานสำหรับเจ้าหน้าที่ (SCI-031) | [docs/คู่มือระบบยืม-คืนหนังสือ.docx](docs/คู่มือระบบยืม-คืนหนังสือ.docx) |
