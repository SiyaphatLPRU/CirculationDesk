# Circulation Desk – ระบบยืม-คืนหนังสือ

Mini Project ของผู้สอนรายวิชา 5672603 การจัดการโครงแบบซอฟต์แวร์ (SCM)

## สถานะ

- Baseline: เอกสารความต้องการ, การออกแบบ, schema 5 ตาราง, หน้าตรวจสภาพแวดล้อม
- ยังไม่ได้พัฒนา: R-01 ถึง R-08

## เอกสาร

| เอกสาร | ไฟล์ |
|---|---|
| กฎบัตรและความต้องการ | [docs/01-project-charter-and-requirements.md](docs/01-project-charter-and-requirements.md) |
| SCI และกติกาตั้งชื่อ | [docs/02-sci-and-naming-convention.md](docs/02-sci-and-naming-convention.md) |
| ER และสถาปัตยกรรม | [docs/03-design-er-and-architecture.md](docs/03-design-er-and-architecture.md) |

## รันด้วย Docker

```bash
cp .env.example .env   # แล้วเปลี่ยนรหัสผ่าน
docker compose up -d --build
# เปิด http://localhost:8080
```
