# เอกสารโครงแบบสภาพแวดล้อม (ENVIRONMENT)

| รายการ | ค่า |
|---|---|
| รหัส SCI | SCI-029 |
| ชื่อเอกสาร | เอกสารโครงแบบสภาพแวดล้อม |
| โครงการ | Circulation Desk – ระบบยืม-คืนหนังสือ |
| เวอร์ชันเอกสาร | 0.1.0 |
| สถานะ | ใช้งาน |

## ประวัติการแก้ไข

| เวอร์ชัน | วันที่ | ผู้แก้ไข | รายละเอียด |
|---|---|---|---|
| 0.1.0 | 2026-10-30 | ผู้พัฒนา | บันทึกค่าโครงแบบของสภาพแวดล้อม XAMPP และ Docker |

ขั้นตอนติดตั้งอยู่ใน [BUILD.md](BUILD.md) เอกสารนี้บันทึกเฉพาะ **ค่าโครงแบบ** ที่ระบบพึ่งพา เพื่อให้ตรวจย้อนได้ว่าระบบรุ่นใดทดสอบบนสภาพแวดล้อมแบบใด

## 1. ตารางค่าโครงแบบ

| รายการค่าโครงแบบ | XAMPP (เครื่องพัฒนา) | Docker | หมายเหตุ / เหตุผล |
|---|---|---|---|
| ระบบปฏิบัติการเครื่องแม่ | Windows 11 Pro | Windows / Linux ที่มี Docker | |
| PHP | 8.3.36 (มากับ XAMPP) | 8.2 (`php:8.2-apache`) | เขียนโค้ดเฉพาะไวยากรณ์ที่มีใน PHP 8.2 |
| ส่วนขยาย PHP | `pdo_mysql` (เปิดไว้แล้วใน XAMPP) | `pdo_mysql` (ติดตั้งใน `docker/php/Dockerfile`) | ไม่มี extension นี้ PDO จะแจ้ง "could not find driver" |
| ฐานข้อมูล | MariaDB 10.11.16 | MySQL 8.0 (`mysql:8.0`) | ใช้ SQL มาตรฐานเท่านั้น, `health.php` แสดง `SELECT VERSION()` |
| เว็บเซิร์ฟเวอร์ | Apache 2.4 (XAMPP) หรือ `php -S localhost:8000` | Apache ใน `php:8.2-apache` | DocumentRoot = `src/public` |
| พอร์ตเว็บ | 80 (Apache) / 8000 (`php -S`) | 8080 → 80 | ไม่ชนกับ Apache ของ XAMPP |
| พอร์ตฐานข้อมูล | 3306 | ไม่เปิดออกนอก Docker | แอปใน Docker เชื่อมต่อผ่านชื่อบริการ `db` |
| ชื่อฐานข้อมูล | `circulation_desk` (ทดสอบ: `circulation_desk_test`) | `circulation_desk` | |
| ผู้ใช้ฐานข้อมูลของแอป | `cdesk` สิทธิ์ `SELECT, INSERT, UPDATE, DELETE` | `cdesk` | ห้ามใช้ `root` |
| แหล่งค่าตั้ง | `src/config/config.local.php` | ตัวแปรสภาพแวดล้อมจาก `.env` | ทั้งสองไฟล์อยู่ใน `.gitignore` |
| ตัวแปรสภาพแวดล้อม | – | `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `MYSQL_ROOT_PASSWORD` | แม่แบบอยู่ใน `.env.example` |
| ชุดอักขระ | `utf8mb4` / `utf8mb4_unicode_ci` | `utf8mb4` / `utf8mb4_unicode_ci` | รองรับภาษาไทย |
| เขตเวลา | `Asia/Bangkok` (ตั้งใน `config.php`) | `Asia/Bangkok` | วันที่คำนวณใน PHP ไม่ใช้ `CURDATE()` |
| Volume ข้อมูล | `C:\xampp\mysql\data` | `dbdata:/var/lib/mysql` | ข้อมูลไม่หายเมื่อสร้าง container ใหม่ |
| สคริปต์สร้างฐานข้อมูล | `db/xampp/circulation-desk-xampp-setup.sql` (นำเข้าผ่าน phpMyAdmin) | `db/init/*.sql` (รันอัตโนมัติครั้งแรก) | ต้องตรงกันเสมอ (Schema version 1.0.0) |

## 2. เครื่องมือฝั่งผู้พัฒนา

| เครื่องมือ | รุ่นที่ใช้ |
|---|---|
| Git for Windows | 2.56 |
| VS Code | รุ่นปัจจุบัน |
| เบราว์เซอร์ | Google Chrome รุ่นปัจจุบัน |

## 3. การตรวจสภาพแวดล้อม

- เปิด `health.php` ต้องเห็น "เชื่อมต่อสำเร็จ", รุ่นฐานข้อมูล และจำนวนตาราง 5
- รัน `php tests/run.php` ต้องผ่านทุกกรณี (ใช้ฐานข้อมูล `circulation_desk_test`)
- เมื่อรุ่นของ PHP หรือฐานข้อมูลเปลี่ยน ให้แก้ตารางในข้อ 1 ขึ้นเลขรุ่นเอกสาร และรันทดสอบซ้ำ
