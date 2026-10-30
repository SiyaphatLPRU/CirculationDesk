# รายการ SCI และกติกาการตั้งชื่อ (SCI & Naming Convention)

| รายการ | ค่า |
|---|---|
| รหัส SCI | SCI-002 |
| ชื่อเอกสาร | รายการ SCI และกติกาการตั้งชื่อ |
| โครงการ | Circulation Desk – ระบบยืม-คืนหนังสือ |
| เวอร์ชันเอกสาร | 0.3.0 |
| สถานะ | ใช้งาน |

## ประวัติการแก้ไข

| เวอร์ชัน | วันที่ | ผู้แก้ไข | รายละเอียด |
|---|---|---|---|
| 0.1.0 | 2026-10-29 | ผู้พัฒนา | จัดทำฉบับ baseline (สร้างขึ้นใหม่ในเครื่อง ดู CR-001) |
| 0.2.0 | 2026-10-29 | ผู้พัฒนา | ปรับสถานะ SCI-006, 008, 010, 020–023 เป็น "สร้างแล้ว" และเพิ่ม SCI-024 ถึง SCI-028 จากการพัฒนา R-01..R-08 |
| 0.3.0 | 2026-10-30 | ผู้พัฒนา | เพิ่ม SCI-029 (ENVIRONMENT.md), SCI-030 (CMDB บน Google Sheet) และ SCI-031 (คู่มือการใช้งาน) |

## 1. รายการ Software Configuration Items (SCI)

| รหัส | รายการ | ตำแหน่ง | สถานะ |
|---|---|---|---|
| SCI-001 | กฎบัตรโครงการและความต้องการ | `docs/01-project-charter-and-requirements.md` | Baseline |
| SCI-002 | รายการ SCI และกติกาการตั้งชื่อ | `docs/02-sci-and-naming-convention.md` | Baseline |
| SCI-003 | การออกแบบ ER และสถาปัตยกรรม | `docs/03-design-er-and-architecture.md` | Baseline |
| SCI-004 | โครงสร้างฐานข้อมูล | `db/init/01-schema.sql` | Baseline (Schema 1.0.0) |
| SCI-005 | ข้อมูลตัวอย่าง | `db/init/02-seed.sql` | Baseline |
| SCI-006 | คู่มือติดตั้งและรัน | `docs/BUILD.md` | สร้างแล้ว (0.2.0) |
| SCI-007 | สภาพแวดล้อม Docker | `docker-compose.yml`, `docker/` | Baseline (ไม่ได้แก้ในรอบนี้) |
| SCI-008 | ทะเบียนคำขอเปลี่ยนแปลง | `docs/change-requests.md` | สร้างแล้ว (0.2.0) |
| SCI-009 | ค่าตั้งระบบ | `src/config/config.php` | แก้ไขแล้ว (อ่าน env ก่อน แล้ว fallback `config.local.php`, กฎธุรกิจ BR-01..BR-03) |
| SCI-010 | Traceability Matrix | `docs/traceability-matrix.md` | สร้างแล้ว (0.2.0) |
| SCI-011 | หน้าเว็บ (DocumentRoot) | `src/public/` | แก้ไขแล้ว (R-01..R-08) |
| SCI-012 | แม่แบบตัวแปรสภาพแวดล้อม | `.env.example` | Baseline (ไม่ได้แก้ในรอบนี้) |
| SCI-013 | README | `README.md` | แก้ไขแล้ว |
| SCI-020 | ไลบรารีพื้นฐาน | `src/lib/` | สร้างแล้ว |
| SCI-021 | บริการตรรกะธุรกิจ | `src/services/` | สร้างแล้ว |
| SCI-022 | ส่วนแสดงผล | `src/views/`, `src/public/assets/style.css` | สร้างแล้ว |
| SCI-023 | กรณีทดสอบ | `docs/test-cases.md` | สร้างแล้ว (0.2.0) |
| SCI-024 | ชุดทดสอบอัตโนมัติ | `tests/` (`run.php`, `lib.php`, `*Test.php`, `create-test-db.sql`) | สร้างแล้ว |
| SCI-025 | บันทึกการเปลี่ยนแปลงตามรุ่น | `CHANGELOG.md` | สร้างแล้ว (Unreleased) |
| SCI-026 | แม่แบบค่าเฉพาะเครื่อง (XAMPP) | `src/config/config.local.example.php` | สร้างแล้ว |
| SCI-027 | สคริปต์สร้างฐานข้อมูลบน XAMPP | `db/xampp/circulation-desk-xampp-setup.sql` | สร้างแล้ว (Schema 1.0.0 ตรงกับ `db/init`) |
| SCI-028 | กฎการเข้าถึงบน XAMPP htdocs | `.htaccess` | สร้างแล้ว |
| SCI-029 | เอกสารโครงแบบสภาพแวดล้อม | `docs/ENVIRONMENT.md` | สร้างแล้ว (0.1.0) |
| SCI-030 | ฐานข้อมูลโครงแบบ (CMDB) | Google Sheet: [CMDB - Circulation Desk](https://docs.google.com/spreadsheets/d/1CFOxE0rC2GueMrVz3em4DXkLlYoonNWbkejcUn7f4sk/edit) | สร้างแล้ว (0.2.0) |
| SCI-031 | คู่มือการใช้งานสำหรับเจ้าหน้าที่ | `docs/คู่มือระบบยืม-คืนหนังสือ.docx` | สร้างแล้ว (1.2) |

ไฟล์ที่ **ไม่ใช่** SCI และต้องไม่อยู่ใน Git: `src/config/config.local.php`, `.env` (ระบุใน `.gitignore`)

## 2. กติกาการตั้งชื่อไฟล์

- เอกสาร: `docs/NN-kebab-case.md` สำหรับเอกสารหลักที่มีลำดับ, เอกสารอื่นใช้ `kebab-case.md` (ยกเว้น `BUILD.md`, `README.md`, `CHANGELOG.md`)
- SQL: `db/init/NN-kebab-case.sql` เรียงลำดับการรัน ไฟล์ที่ baseline แล้วห้ามแก้ ให้เพิ่มไฟล์ลำดับถัดไป
- PHP: ไฟล์ฟังก์ชันใช้ตัวพิมพ์เล็ก (`helpers.php`), คลาสใช้ PascalCase ตรงกับชื่อคลาส (`LoanService.php`)

## 3. เลขรุ่น

- ซอฟต์แวร์และเอกสารใช้ Semantic Versioning `MAJOR.MINOR.PATCH`
- Schema version เขียนไว้ที่หัวไฟล์ SQL (`-- Schema version: x.y.z`) ขึ้นเลขเมื่อมี migration ใหม่
- Tag รุ่น `vX.Y.Z` สร้างโดยผู้สอนเท่านั้น

## 4. Branch

| ชนิด | รูปแบบ | ตัวอย่าง |
|---|---|---|
| ฟีเจอร์ตาม Requirement | `feature/R-xx-<สั้น>` | `feature/R-05-borrow` |
| แก้ข้อบกพร่อง | `fix/<สั้น>` | `fix/fine-rounding` |
| เอกสาร | `docs/<สั้น>` | `docs/build-and-traceability` |

- `main` ต้องอยู่ในสภาพทำงานได้เสมอ รวมงานด้วย `git merge --no-ff` เพื่อเก็บประวัติ branch

## 5. รูปแบบ Commit

```text
<ชนิด>(<ขอบเขต>): <สรุปภาษาอังกฤษ ขึ้นต้นด้วยกริยา>

Refs: R-xx
```

- ชนิด: `feat`, `fix`, `docs`, `test`, `refactor`, `style`, `chore`
- ขอบเขต: เช่น `auth`, `book`, `member`, `loan`, `report`, `db`, `docs`
- บรรทัด `Refs:` อ้างถึง Requirement (หรือเลข Issue เมื่อมี)
