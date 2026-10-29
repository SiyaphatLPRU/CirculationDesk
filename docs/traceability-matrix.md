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
| 0.2.0 | 2026-10-29 | ผู้พัฒนา | สร้างเอกสาร เพิ่ม R-01 |

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
| R-08 รายงานประวัติการยืม-คืน | `feature/R-08-history` | – | – | – | ยังไม่เริ่ม |

## กฎธุรกิจ → ตำแหน่งที่บังคับใช้

| กฎ | ค่าใน `config.php` | บังคับใช้ที่ | ทดสอบ |
|---|---|---|---|
| BR-01 ยืม 7 วัน | `loan.days` | `LoanService::borrow` | AT-19, TC-34, TC-38 |
| BR-02 ยืมค้างไม่เกิน 3 เล่ม | `loan.max_active` | `LoanService::borrow` | AT-21, TC-36 |
| BR-03 ค่าปรับ 5 บาท/วัน/เล่ม | `loan.fine_per_day` | `LoanService::calculateFine` (ใช้ใน `return`) | AT-25 – AT-27, TC-41 – TC-43 |
| BR-04 สมาชิก suspended ยืมไม่ได้ | – (สถานะ `members.status`) | `LoanService::borrow` | AT-20, TC-35 |
