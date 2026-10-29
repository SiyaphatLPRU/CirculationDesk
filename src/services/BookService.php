<?php
declare(strict_types=1);

/**
 * R-02 จัดการข้อมูลหนังสือ
 * create/update คืน ['ok' => bool, 'errors' => [ช่อง => ข้อความ], 'id' => ...]
 * delete คืน ['ok' => bool, 'error' => ข้อความ]
 */
final class BookService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT b.book_id, b.isbn, b.title, b.author, b.copies_total, b.copies_available, c.name AS category_name
               FROM books b
               JOIN categories c ON c.category_id = b.category_id
              ORDER BY b.title'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT book_id, isbn, title, author, category_id, copies_total, copies_available FROM books WHERE book_id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** เพิ่มหนังสือใหม่: copies_available = copies_total */
    public function create(array $input): array
    {
        [$data, $errors] = $this->validate($input, null);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO books (isbn, title, author, category_id, copies_total, copies_available)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $data['isbn'], $data['title'], $data['author'], $data['category_id'],
                $data['copies_total'], $data['copies_total'],
            ]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                return ['ok' => false, 'errors' => ['isbn' => 'ISBN นี้มีอยู่แล้วในระบบ']];
            }
            throw $e;
        }
        return ['ok' => true, 'id' => (int) $this->pdo->lastInsertId()];
    }

    /**
     * แก้ไขหนังสือ: copies_total ต้องไม่น้อยกว่าจำนวนที่ถูกยืมอยู่
     * และปรับ copies_available = copies_total ใหม่ - จำนวนที่ถูกยืมอยู่
     */
    public function update(int $id, array $input): array
    {
        [$data, $errors] = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT copies_total, copies_available FROM books WHERE book_id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $book = $stmt->fetch();
            if ($book === false) {
                $this->pdo->rollBack();
                return ['ok' => false, 'errors' => ['_' => 'ไม่พบหนังสือ']];
            }

            $onLoan = (int) $book['copies_total'] - (int) $book['copies_available'];
            if ($data['copies_total'] < $onLoan) {
                $this->pdo->rollBack();
                return ['ok' => false, 'errors' => [
                    'copies_total' => "จำนวนทั้งหมดต้องไม่น้อยกว่าจำนวนที่ถูกยืมอยู่ ($onLoan เล่ม)",
                ]];
            }

            $stmt = $this->pdo->prepare(
                'UPDATE books SET isbn = ?, title = ?, author = ?, category_id = ?, copies_total = ?, copies_available = ?
                  WHERE book_id = ?'
            );
            $stmt->execute([
                $data['isbn'], $data['title'], $data['author'], $data['category_id'],
                $data['copies_total'], $data['copies_total'] - $onLoan, $id,
            ]);
            $this->pdo->commit();
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            if (($e->errorInfo[1] ?? 0) === 1062) {
                return ['ok' => false, 'errors' => ['isbn' => 'ISBN นี้มีอยู่แล้วในระบบ']];
            }
            throw $e;
        }
        return ['ok' => true, 'id' => $id];
    }

    /** ลบได้เฉพาะหนังสือที่ไม่เคยถูกยืม (ตรวจก่อน และจับ FK error ซ้ำอีกชั้น) */
    public function delete(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM loans WHERE book_id = ?');
        $stmt->execute([$id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return ['ok' => false, 'error' => 'ลบไม่ได้: หนังสือเล่มนี้มีประวัติการยืม'];
        }
        try {
            $stmt = $this->pdo->prepare('DELETE FROM books WHERE book_id = ?');
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1451) {
                return ['ok' => false, 'error' => 'ลบไม่ได้: หนังสือเล่มนี้มีประวัติการยืม'];
            }
            throw $e;
        }
        if ($stmt->rowCount() === 0) {
            return ['ok' => false, 'error' => 'ไม่พบหนังสือ'];
        }
        return ['ok' => true];
    }

    /**
     * ตรวจข้อมูลฝั่งเซิร์ฟเวอร์ คืน [ข้อมูลที่ทำความสะอาดแล้ว, ข้อผิดพลาดรายช่อง]
     * ISBN ว่าง -> NULL (ไม่เก็บสตริงว่าง เพราะ UNIQUE จะชนกัน)
     */
    public function validate(array $input, ?int $exceptId): array
    {
        $isbn = trim((string) ($input['isbn'] ?? ''));
        $data = [
            'isbn'         => $isbn === '' ? null : $isbn,
            'title'        => trim((string) ($input['title'] ?? '')),
            'author'       => trim((string) ($input['author'] ?? '')),
            'category_id'  => filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT),
            'copies_total' => filter_var($input['copies_total'] ?? null, FILTER_VALIDATE_INT),
        ];
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'] = 'กรุณากรอกชื่อหนังสือ';
        } elseif (mb_strlen($data['title']) > 255) {
            $errors['title'] = 'ชื่อหนังสือยาวเกิน 255 ตัวอักษร';
        }
        if ($data['author'] === '') {
            $errors['author'] = 'กรุณากรอกชื่อผู้แต่ง';
        } elseif (mb_strlen($data['author']) > 150) {
            $errors['author'] = 'ชื่อผู้แต่งยาวเกิน 150 ตัวอักษร';
        }
        if ($data['isbn'] !== null) {
            if (!preg_match('/^[0-9Xx-]{1,20}$/', $data['isbn'])) {
                $errors['isbn'] = 'ISBN ใช้ได้เฉพาะตัวเลข ขีด และ X ไม่เกิน 20 ตัว';
            } else {
                $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM books WHERE isbn = ? AND book_id <> ?');
                $stmt->execute([$data['isbn'], $exceptId ?? 0]);
                if ((int) $stmt->fetchColumn() > 0) {
                    $errors['isbn'] = 'ISBN นี้มีอยู่แล้วในระบบ';
                }
            }
        }
        if ($data['category_id'] === false) {
            $errors['category_id'] = 'กรุณาเลือกหมวดหมู่';
        } else {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE category_id = ?');
            $stmt->execute([$data['category_id']]);
            if ((int) $stmt->fetchColumn() === 0) {
                $errors['category_id'] = 'ไม่พบหมวดหมู่ที่เลือก';
            }
        }
        if ($data['copies_total'] === false || $data['copies_total'] < 1) {
            $errors['copies_total'] = 'จำนวนทั้งหมดต้องเป็นจำนวนเต็มตั้งแต่ 1 ขึ้นไป';
        } elseif ($data['copies_total'] > 9999) {
            $errors['copies_total'] = 'จำนวนทั้งหมดต้องไม่เกิน 9999';
        }

        return [$data, $errors];
    }
}
