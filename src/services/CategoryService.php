<?php
declare(strict_types=1);

/**
 * R-02 จัดการหมวดหมู่หนังสือ
 * ผลลัพธ์ของคำสั่งเขียนเป็น array: ['ok' => bool, 'error' => ข้อความ, 'id' => รหัสที่สร้าง]
 */
final class CategoryService
{
    public function __construct(private PDO $pdo)
    {
    }

    /** หมวดหมู่ทั้งหมดพร้อมจำนวนหนังสือในหมวด */
    public function all(): array
    {
        return $this->pdo->query(
            'SELECT c.category_id, c.name, COUNT(b.book_id) AS book_count
               FROM categories c
               LEFT JOIN books b ON b.category_id = c.category_id
              GROUP BY c.category_id, c.name
              ORDER BY c.name'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT category_id, name FROM categories WHERE category_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name): array
    {
        $name = trim($name);
        $error = $this->validate($name, null);
        if ($error !== null) {
            return ['ok' => false, 'error' => $error];
        }
        try {
            $stmt = $this->pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                return ['ok' => false, 'error' => 'มีหมวดหมู่ชื่อนี้อยู่แล้ว'];
            }
            throw $e;
        }
        return ['ok' => true, 'id' => (int) $this->pdo->lastInsertId()];
    }

    public function rename(int $id, string $name): array
    {
        $name = trim($name);
        if ($this->find($id) === null) {
            return ['ok' => false, 'error' => 'ไม่พบหมวดหมู่'];
        }
        $error = $this->validate($name, $id);
        if ($error !== null) {
            return ['ok' => false, 'error' => $error];
        }
        try {
            $stmt = $this->pdo->prepare('UPDATE categories SET name = ? WHERE category_id = ?');
            $stmt->execute([$name, $id]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                return ['ok' => false, 'error' => 'มีหมวดหมู่ชื่อนี้อยู่แล้ว'];
            }
            throw $e;
        }
        return ['ok' => true];
    }

    /** ลบได้เฉพาะหมวดหมู่ที่ไม่มีหนังสือ */
    public function delete(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM books WHERE category_id = ?');
        $stmt->execute([$id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return ['ok' => false, 'error' => 'ลบไม่ได้: ยังมีหนังสืออยู่ในหมวดหมู่นี้'];
        }
        try {
            $stmt = $this->pdo->prepare('DELETE FROM categories WHERE category_id = ?');
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1451) {
                return ['ok' => false, 'error' => 'ลบไม่ได้: ยังมีหนังสืออยู่ในหมวดหมู่นี้'];
            }
            throw $e;
        }
        if ($stmt->rowCount() === 0) {
            return ['ok' => false, 'error' => 'ไม่พบหมวดหมู่'];
        }
        return ['ok' => true];
    }

    private function validate(string $name, ?int $exceptId): ?string
    {
        if ($name === '') {
            return 'กรุณากรอกชื่อหมวดหมู่';
        }
        if (mb_strlen($name) > 100) {
            return 'ชื่อหมวดหมู่ยาวเกิน 100 ตัวอักษร';
        }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ? AND category_id <> ?');
        $stmt->execute([$name, $exceptId ?? 0]);
        if ((int) $stmt->fetchColumn() > 0) {
            return 'มีหมวดหมู่ชื่อนี้อยู่แล้ว';
        }
        return null;
    }
}
