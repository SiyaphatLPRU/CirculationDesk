<?php
declare(strict_types=1);

/**
 * R-03 จัดการข้อมูลสมาชิก (เพิ่ม แก้ไข เปลี่ยนสถานะ ไม่มีการลบ)
 * create/update คืน ['ok' => bool, 'errors' => [ช่อง => ข้อความ], 'id' => ...]
 */
final class MemberService
{
    public const STATUSES = ['active' => 'ใช้งาน', 'suspended' => 'ถูกระงับ'];

    public function __construct(private PDO $pdo)
    {
    }

    /** ค้นจากรหัสหรือชื่อสมาชิก พร้อมจำนวนเล่มที่ยืมค้าง */
    public function search(string $q = ''): array
    {
        $like = '%' . trim($q) . '%';
        $stmt = $this->pdo->prepare(
            "SELECT m.member_id, m.member_code, m.full_name, m.phone, m.email, m.status,
                    (SELECT COUNT(*) FROM loans l WHERE l.member_id = m.member_id AND l.status = 'borrowed') AS active_loans
               FROM members m
              WHERE m.member_code LIKE ? OR m.full_name LIKE ?
              ORDER BY m.member_code"
        );
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT member_id, member_code, full_name, phone, email, status FROM members WHERE member_id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT member_id, member_code, full_name, phone, email, status FROM members WHERE member_code = ?'
        );
        $stmt->execute([trim($code)]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $input): array
    {
        [$data, $errors] = $this->validate($input, null);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO members (member_code, full_name, phone, email, status) VALUES (?, ?, ?, ?, 'active')"
            );
            $stmt->execute([$data['member_code'], $data['full_name'], $data['phone'], $data['email']]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                return ['ok' => false, 'errors' => ['member_code' => 'รหัสสมาชิกนี้มีอยู่แล้ว']];
            }
            throw $e;
        }
        return ['ok' => true, 'id' => (int) $this->pdo->lastInsertId()];
    }

    public function update(int $id, array $input): array
    {
        if ($this->find($id) === null) {
            return ['ok' => false, 'errors' => ['_' => 'ไม่พบสมาชิก']];
        }
        [$data, $errors] = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE members SET member_code = ?, full_name = ?, phone = ?, email = ? WHERE member_id = ?'
            );
            $stmt->execute([$data['member_code'], $data['full_name'], $data['phone'], $data['email'], $id]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                return ['ok' => false, 'errors' => ['member_code' => 'รหัสสมาชิกนี้มีอยู่แล้ว']];
            }
            throw $e;
        }
        return ['ok' => true, 'id' => $id];
    }

    public function setStatus(int $id, string $status): array
    {
        if (!array_key_exists($status, self::STATUSES)) {
            return ['ok' => false, 'error' => 'สถานะไม่ถูกต้อง'];
        }
        if ($this->find($id) === null) {
            return ['ok' => false, 'error' => 'ไม่พบสมาชิก'];
        }
        $stmt = $this->pdo->prepare('UPDATE members SET status = ? WHERE member_id = ?');
        $stmt->execute([$status, $id]);
        return ['ok' => true];
    }

    /** ตรวจข้อมูลฝั่งเซิร์ฟเวอร์ ช่องที่ไม่บังคับถ้าว่างเก็บเป็น NULL */
    public function validate(array $input, ?int $exceptId): array
    {
        $optional = static function (mixed $v): ?string {
            $v = trim((string) $v);
            return $v === '' ? null : $v;
        };
        $data = [
            'member_code' => trim((string) ($input['member_code'] ?? '')),
            'full_name'   => trim((string) ($input['full_name'] ?? '')),
            'phone'       => $optional($input['phone'] ?? ''),
            'email'       => $optional($input['email'] ?? ''),
        ];
        $errors = [];

        if ($data['member_code'] === '') {
            $errors['member_code'] = 'กรุณากรอกรหัสสมาชิก';
        } elseif (!preg_match('/^[A-Za-z0-9-]{1,20}$/', $data['member_code'])) {
            $errors['member_code'] = 'รหัสสมาชิกใช้ได้เฉพาะ A-Z, 0-9 และขีด ไม่เกิน 20 ตัว';
        } else {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM members WHERE member_code = ? AND member_id <> ?');
            $stmt->execute([$data['member_code'], $exceptId ?? 0]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['member_code'] = 'รหัสสมาชิกนี้มีอยู่แล้ว';
            }
        }
        if ($data['full_name'] === '') {
            $errors['full_name'] = 'กรุณากรอกชื่อ-นามสกุล';
        } elseif (mb_strlen($data['full_name']) > 100) {
            $errors['full_name'] = 'ชื่อยาวเกิน 100 ตัวอักษร';
        }
        if ($data['phone'] !== null && !preg_match('/^[0-9+\- ]{1,20}$/', $data['phone'])) {
            $errors['phone'] = 'เบอร์โทรใช้ได้เฉพาะตัวเลข + - และเว้นวรรค ไม่เกิน 20 ตัว';
        }
        if ($data['email'] !== null && (mb_strlen($data['email']) > 100 || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false)) {
            $errors['email'] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }

        return [$data, $errors];
    }
}
