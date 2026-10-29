# คู่มือติดตั้งและรัน (BUILD)

| รายการ | ค่า |
|---|---|
| รหัส SCI | SCI-006 |
| ชื่อเอกสาร | คู่มือติดตั้งและรัน |
| โครงการ | Circulation Desk – ระบบยืม-คืนหนังสือ |
| เวอร์ชันเอกสาร | 0.2.0 |
| สถานะ | ใช้งาน |

## ประวัติการแก้ไข

| เวอร์ชัน | วันที่ | ผู้แก้ไข | รายละเอียด |
|---|---|---|---|
| 0.2.0 | 2026-10-29 | ผู้พัฒนา | สร้างคู่มือติดตั้งบน XAMPP และบันทึกเหตุการณ์ระหว่างพัฒนา |

## 1. สิ่งที่ต้องมี

| ซอฟต์แวร์ | รุ่น | หมายเหตุ |
|---|---|---|
| XAMPP | PHP 8.2 ขึ้นไป + MariaDB 10.x | ทดสอบแล้วกับ PHP 8.3.36, MariaDB 10.11.16 |
| Git for Windows | 2.x | |
| เบราว์เซอร์ | รุ่นปัจจุบัน | |

PHP ต้องเปิด extension `pdo_mysql` (XAMPP เปิดไว้แล้ว)

## 2. ติดตั้งบน XAMPP ทีละขั้น

### 2.1 นำโค้ดลงเครื่อง

```powershell
cd C:\xampp\htdocs
git clone <url-ของ-repo> Circulation-Desk
```

### 2.2 สร้างฐานข้อมูล (ครั้งเดียว)

1. เปิดไฟล์ `db/xampp/circulation-desk-xampp-setup.sql` ด้วย editor
2. **เปลี่ยนรหัสผ่านผู้ใช้ `cdesk`** ในส่วนที่ 2 (บรรทัด `IDENTIFIED BY 'change_me_app'`) เป็นรหัสผ่านของคุณ
3. เปิด XAMPP Control Panel → Start **Apache** และ **MySQL**
4. เปิด `http://localhost/phpmyadmin` → แท็บ **นำเข้า (Import)** → เลือกไฟล์ → ชุดอักขระ `utf-8` → **ดำเนินการ**
5. ตรวจว่ามีฐานข้อมูล `circulation_desk` และตาราง 5 ตาราง (`staff`, `members`, `categories`, `books`, `loans`)

> แอปเชื่อมต่อด้วยผู้ใช้ `cdesk` ที่มีสิทธิ์เพียง `SELECT, INSERT, UPDATE, DELETE` ห้ามใช้ `root` ใน config ของแอป

### 2.3 สร้างไฟล์ค่าเฉพาะเครื่อง

```powershell
copy src\config\config.local.example.php src\config\config.local.php
```

แก้ `src/config/config.local.php` ใส่รหัสผ่าน `cdesk` ที่ตั้งในข้อ 2.2 ไฟล์นี้อยู่ใน `.gitignore` **ห้าม commit**

ลำดับการอ่านค่า: ตัวแปรสภาพแวดล้อม (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) → `config.local.php` → ค่าเริ่มต้น
จึงใช้โค้ดชุดเดียวกันได้ทั้ง XAMPP และ Docker

### 2.4 เปิดเว็บ (เลือกวิธีใดวิธีหนึ่ง)

**วิธี ก – PHP built-in server (ง่ายที่สุด)**

```powershell
C:\xampp\php\php.exe -S localhost:8000 -t src/public
```

เปิด `http://localhost:8000/`

**วิธี ข – Apache VirtualHost ชี้ `src/public` (แนะนำ)**

เพิ่มใน `C:\xampp\apache\conf\extra\httpd-vhosts.conf`

```apache
<VirtualHost *:80>
    ServerName cdesk.localhost
    DocumentRoot "C:/xampp/htdocs/Circulation-Desk/src/public"
    <Directory "C:/xampp/htdocs/Circulation-Desk/src/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Restart Apache แล้วเปิด `http://cdesk.localhost/`

**วิธี ค – ผ่าน htdocs โดยตรง**

เปิด `http://localhost/Circulation-Desk/src/public/` ได้ทันที ไฟล์ `.htaccess` ที่รากโปรเจกต์ปิดการเข้าถึงทุกอย่างนอก `src/public` (config, lib, docs, db, tests) ด้วย 403

ลิงก์ทุกจุดสร้างด้วย `url()` จึงทำงานได้ทั้งสามวิธีโดยไม่ต้องแก้โค้ด

### 2.5 ตรวจการติดตั้ง

1. เปิด `health.php` ต้องเห็น "เชื่อมต่อสำเร็จ", รุ่นฐานข้อมูล และจำนวนตาราง 5
2. เปิดหน้าแรก เข้าสู่ระบบด้วย `admin` / `admin1234` (บัญชีสำหรับพัฒนาเท่านั้น)

## 3. Docker (ภายหลัง)

ดู `README.md` และ `docker-compose.yml` (รอบนี้ไม่ได้แก้ไฟล์ Docker) แอปอ่านค่าจากตัวแปรสภาพแวดล้อมอัตโนมัติ

## 4. บันทึกเหตุการณ์ระหว่างพัฒนา

| วันที่ | เหตุการณ์ | สาเหตุ | การแก้ไข |
|---|---|---|---|
| 2026-10-29 | โฟลเดอร์โปรเจกต์ว่าง ไม่มี repo ต้นฉบับ และเครื่องไม่มี Git | ยังไม่ได้ clone repo `miniproject-Circulation-Desk` | ติดตั้ง Git ด้วย `winget install Git.Git`, `git init` แล้วสร้าง baseline ใหม่จากสคริปต์ XAMPP และ prompt งาน (commit `671050a`) บันทึกเป็น CR-001 |
| 2026-10-29 | PHP ใน XAMPP เป็นรุ่น 8.3 ไม่ใช่ 8.2 | รุ่นที่ติดมากับ XAMPP | เขียนโค้ดเฉพาะไวยากรณ์ที่มีใน PHP 8.2 (Docker ใช้ `php:8.2-apache`) |
| 2026-10-29 | XAMPP ใช้ MariaDB 10.11 ส่วน Docker ใช้ MySQL 8.0 | ต่างผลิตภัณฑ์ | ใช้ SQL มาตรฐานเท่านั้น และ `health.php` แสดง `SELECT VERSION()` |
