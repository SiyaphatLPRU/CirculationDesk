# Traceability Matrix

| รายการ | ค่า |
|---|---|
| รหัส SCI | SCI-010 |
| ชื่อเอกสาร | Traceability Matrix |
| โครงการ | Circulation Desk – ระบบยืม-คืนหนังสือ |
| เวอร์ชันเอกสาร | 0.2.0 |
| สถานะ | ใช้งาน (อัปเดตทุกครั้งที่ merge branch ของ Requirement) |

## ประวัติการแก้ไข

| เวอร์ชัน | วันที่ | ผู้แก้ไข | รายละเอียด |
|---|---|---|---|
| 0.2.0 | 2026-10-29 | ผู้พัฒนา | สร้างเอกสาร เพิ่ม R-01 ถึง R-08 (อัปเดตทุกครั้งที่ merge) เพิ่ม merge commit และข้อกำหนดความปลอดภัย |

## ตาราง Requirement → Branch → Commit → ไฟล์หลัก → Test Case

Commit คือ hash สั้นของ commit งานใน branch (merge commit ดูได้จาก `git log --merges`)
บรรทัด `Refs: R-xx` ในข้อความ commit ใช้แมปกับเลข Issue ภายหลัง

| Requirement | Branch | Commit | ไฟล์หลัก | Test Case | สถานะ |
|---|---|---|---|---|---|
| R-01 เข้าสู่ระบบเจ้าหน้าที่ | `feature/R-01-login` | `9dbc768`, `43f32ba`, `1b2d2ff`, `eb7a52f` | `src/config/config.php`, `src/lib/auth.php`, `src/lib/csrf.php`, `src/lib/bootstrap.php`, `src/public/login.php`, `src/public/logout.php`, `src/public/health.php` | TC-01 – TC-11 | Merge แล้ว |
| R-02 จัดการหนังสือและหมวดหมู่ | `feature/R-02-books` | `8a25303`, `b1ae514`, `c1423cb` | `src/services/BookService.php`, `src/services/CategoryService.php`, `src/public/books/*`, `src/public/categories/index.php`, `tests/run.php` | TC-12 – TC-22, AT-01 – AT-10 | Merge แล้ว |
| R-03 จัดการสมาชิก | `feature/R-03-members` | `ef540c9`, `58f7925` | `src/services/MemberService.php`, `src/public/members/*` | TC-23 – TC-28, AT-11 – AT-14 | Merge แล้ว |
| R-04 ค้นหาและดูสถานะหนังสือ | `feature/R-04-search` | `7681c59`, `45c4391` | `src/services/BookService.php` (`search`), `src/public/books/index.php`, `src/views/partials/pagination.php` | TC-29 – TC-33, AT-15 – AT-18 | Merge แล้ว |
| R-05 ยืมหนังสือ | `feature/R-05-borrow` | `83f5d5b`, `ec1c549` | `src/services/LoanService.php` (`borrow`), `src/public/loans/borrow.php` | TC-34 – TC-39, AT-19 – AT-24 | Merge แล้ว |
| R-06 คืนหนังสือ | `feature/R-06-return` | `ce46461`, `1c527f2` | `src/services/LoanService.php` (`return`, `calculateFine`), `src/public/loans/return.php` | TC-40 – TC-45, AT-25 – AT-29 | Merge แล้ว |
| R-07 ตรวจรายการเกินกำหนด | `feature/R-07-overdue` | `f44cba4`, `41888cb` | `src/services/ReportService.php` (`overdue`, `dashboard`), `src/public/loans/overdue.php`, `src/public/index.php` | TC-46 – TC-49, AT-30 – AT-32 | Merge แล้ว |
| R-08 รายงานประวัติการยืม-คืน | `feature/R-08-history` | `063b1ec`, `f0a782c` | `src/services/ReportService.php` (`history`), `src/public/reports/history.php` | TC-50 – TC-54, AT-33 – AT-36 | Merge แล้ว |

## Merge commit บน `main`

| Requirement | Merge commit |
|---|---|
| R-01 | `1a466f0` |
| R-02 | `f31c677` |
| R-03 | `2b9adb2` |
| R-04 | `e6bf258` |
| R-05 | `8b723f1` |
| R-06 | `fe86130` |
| R-07 | `ffffa5e` |
| R-08 | `d7f0da9` |

## กฎธุรกิจ → ตำแหน่งที่บังคับใช้

| กฎ | ค่าใน `config.php` | บังคับใช้ที่ | ทดสอบ |
|---|---|---|---|
| BR-01 ยืม 7 วัน | `loan.days` | `LoanService::borrow` | AT-19, TC-34, TC-38 |
| BR-02 ยืมค้างไม่เกิน 3 เล่ม | `loan.max_active` | `LoanService::borrow` | AT-21, TC-36 |
| BR-03 ค่าปรับ 5 บาท/วัน/เล่ม | `loan.fine_per_day` | `LoanService::calculateFine` (ใช้ใน `return`) | AT-25 – AT-27, TC-41 – TC-43 |
| BR-04 สมาชิก suspended ยืมไม่ได้ | – (สถานะ `members.status`) | `LoanService::borrow` | AT-20, TC-35 |

## ข้อกำหนดความปลอดภัย → ตำแหน่งที่บังคับใช้

| ข้อกำหนด | บังคับใช้ที่ | ทดสอบ |
|---|---|---|
| escape ทุกค่าที่แสดง (`ENT_QUOTES`, UTF-8) | `e()` ใน `src/lib/helpers.php` ใช้ในทุก view/หน้า | TC-15, TC-33 |
| CSRF ทุกฟอร์ม POST | `csrf_field()`, `csrf_check()` ใน `src/lib/csrf.php`, `require_post()` | TC-07 |
| เขียนข้อมูลผ่าน POST เท่านั้น | `require_post()` (ตอบ 405 เมื่อไม่ใช่ POST) | TC-06, TC-22 |
| session cookie `httponly`, `SameSite=Lax`, regenerate หลัง login | `src/lib/bootstrap.php`, `login()` ใน `src/lib/auth.php` | TC-08 |
| ไม่แสดงข้อผิดพลาด/SQL ให้ผู้ใช้ บันทึกลง error log | `src/lib/bootstrap.php` (`display_errors=0`, exception handler) | TC-11 |
| PDO + prepared statements เท่านั้น | `src/services/*`, `src/lib/auth.php` (`ATTR_EMULATE_PREPARES=false`) | AT-15, AT-36, TC-54 |
| config/lib อยู่นอก DocumentRoot | `src/public` เป็น DocumentRoot, `.htaccess` ที่ราก (กรณี htdocs) | TC-10 |
