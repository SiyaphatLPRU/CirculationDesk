<?php
declare(strict_types=1);

/**
 * R-05 ยืมหนังสือ
 * กฎธุรกิจรับมาจาก config('loan'): days (BR-01), max_active (BR-02), fine_per_day (BR-03)
 * วันที่คำนวณใน PHP แล้วส่งเป็นพารามิเตอร์ (ส่ง $today เข้ามาได้เพื่อทดสอบ)
 *
 * ผลลัพธ์: ['ok' => true, ...] หรือ ['ok' => false, 'code' => รหัสเหตุผล, 'error' => ข้อความ]
 */
final class LoanService
{
    public const ERRORS = [
        'member_not_found' => 'ไม่พบสมาชิก',
        'member_suspended' => 'ยืมไม่ได้: สมาชิกถูกระงับ',
        'limit_reached'    => 'ยืมไม่ได้: สมาชิกยืมค้างครบ %d เล่มแล้ว',
        'book_not_found'   => 'ไม่พบหนังสือ',
        'out_of_stock'     => 'ยืมไม่ได้: หนังสือหมดชั่วคราว',
    ];

    public function __construct(private PDO $pdo, private array $rules)
    {
    }

    /**
     * ยืมหนังสือ 1 เล่ม ทำใน transaction เดียว
     * ล็อกแถวสมาชิก (กันยืมเกิน BR-02 พร้อมกัน) และแถวหนังสือ (กันยืมเกินจำนวนคงเหลือ) ด้วย SELECT ... FOR UPDATE
     */
    public function borrow(int $memberId, int $bookId, int $staffId, ?DateTimeImmutable $today = null): array
    {
        $today ??= new DateTimeImmutable('today');
        $dueDate = $today->modify('+' . (int) $this->rules['days'] . ' days');

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT member_id, status FROM members WHERE member_id = ? FOR UPDATE');
            $stmt->execute([$memberId]);
            $member = $stmt->fetch();
            if ($member === false) {
                return $this->fail('member_not_found');
            }
            if ($member['status'] !== 'active') {
                return $this->fail('member_suspended');
            }

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM loans WHERE member_id = ? AND status = 'borrowed'");
            $stmt->execute([$memberId]);
            if ((int) $stmt->fetchColumn() >= (int) $this->rules['max_active']) {
                return $this->fail('limit_reached');
            }

            $stmt = $this->pdo->prepare('SELECT book_id, title, copies_available FROM books WHERE book_id = ? FOR UPDATE');
            $stmt->execute([$bookId]);
            $book = $stmt->fetch();
            if ($book === false) {
                return $this->fail('book_not_found');
            }
            if ((int) $book['copies_available'] <= 0) {
                return $this->fail('out_of_stock');
            }

            $stmt = $this->pdo->prepare(
                "INSERT INTO loans (member_id, book_id, staff_id, borrowed_at, due_date, fine_amount, status)
                 VALUES (?, ?, ?, ?, ?, 0, 'borrowed')"
            );
            $stmt->execute([$memberId, $bookId, $staffId, $today->format('Y-m-d'), $dueDate->format('Y-m-d')]);
            $loanId = (int) $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare(
                'UPDATE books SET copies_available = copies_available - 1 WHERE book_id = ? AND copies_available > 0'
            );
            $stmt->execute([$bookId]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return [
            'ok'       => true,
            'loan_id'  => $loanId,
            'title'    => $book['title'],
            'due_date' => $dueDate->format('Y-m-d'),
        ];
    }

    /** ยกเลิก transaction แล้วคืนผลล้มเหลวพร้อมเหตุผล */
    private function fail(string $code): array
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        return ['ok' => false, 'code' => $code, 'error' => sprintf(self::ERRORS[$code], (int) $this->rules['max_active'])];
    }
}
