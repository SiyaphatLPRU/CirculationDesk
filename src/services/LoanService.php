<?php
declare(strict_types=1);

/**
 * R-05 ยืมหนังสือ, R-06 คืนหนังสือ
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
        'loan_not_found'   => 'ไม่พบรายการยืม',
        'already_returned' => 'คืนไม่ได้: รายการนี้คืนไปแล้ว',
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

    /**
     * R-06 คืนหนังสือ ทำใน transaction เดียว ล็อกแถว loan ก่อนตรวจสถานะ (กันคืนซ้ำพร้อมกัน)
     * ค่าปรับ = จำนวนวันที่เกินกำหนด × fine_per_day (BR-03) ไม่เกินกำหนดเป็น 0
     */
    public function return(int $loanId, ?DateTimeImmutable $today = null): array
    {
        $today ??= new DateTimeImmutable('today');

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT loan_id, book_id, due_date, status FROM loans WHERE loan_id = ? FOR UPDATE');
            $stmt->execute([$loanId]);
            $loan = $stmt->fetch();
            if ($loan === false) {
                return $this->fail('loan_not_found');
            }
            if ($loan['status'] !== 'borrowed') {
                return $this->fail('already_returned');
            }

            $daysLate = $this->daysLate($loan['due_date'], $today);
            $fine = $this->calculateFine($loan['due_date'], $today);

            $stmt = $this->pdo->prepare(
                "UPDATE loans SET returned_at = ?, fine_amount = ?, status = 'returned' WHERE loan_id = ?"
            );
            $stmt->execute([$today->format('Y-m-d'), $fine, $loanId]);

            $stmt = $this->pdo->prepare(
                'UPDATE books SET copies_available = copies_available + 1 WHERE book_id = ? AND copies_available < copies_total'
            );
            $stmt->execute([$loan['book_id']]);

            $stmt = $this->pdo->prepare('SELECT title FROM books WHERE book_id = ?');
            $stmt->execute([$loan['book_id']]);
            $title = (string) $stmt->fetchColumn();

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return [
            'ok'          => true,
            'loan_id'     => $loanId,
            'title'       => $title,
            'due_date'    => $loan['due_date'],
            'returned_at' => $today->format('Y-m-d'),
            'days_late'   => $daysLate,
            'fine'        => $fine,
        ];
    }

    /** รายการที่สมาชิกยืมค้างอยู่ เรียงตามกำหนดคืน */
    public function activeLoansForMember(int $memberId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT l.loan_id, l.borrowed_at, l.due_date, b.title, b.isbn
               FROM loans l
               JOIN books b ON b.book_id = l.book_id
              WHERE l.member_id = ? AND l.status = 'borrowed'
              ORDER BY l.due_date, l.loan_id"
        );
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    /** จำนวนวันที่เกินกำหนด (0 ถ้ายังไม่เกิน) */
    public function daysLate(string $dueDate, DateTimeImmutable $on): int
    {
        $due = new DateTimeImmutable($dueDate);
        $on = $on->setTime(0, 0);
        return $on > $due ? (int) $due->diff($on)->days : 0;
    }

    public function calculateFine(string $dueDate, DateTimeImmutable $on): int
    {
        return $this->daysLate($dueDate, $on) * (int) $this->rules['fine_per_day'];
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
