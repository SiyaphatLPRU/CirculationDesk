<?php
declare(strict_types=1);

/**
 * R-07 รายการเกินกำหนด และตัวเลขสรุปบนแดชบอร์ด
 * ค่าปรับสะสมคำนวณ ณ วันที่ส่งเข้ามา (ยังไม่บันทึกลงตาราง)
 */
final class ReportService
{
    private LoanService $loans;

    public function __construct(private PDO $pdo, array $rules)
    {
        $this->loans = new LoanService($pdo, $rules);
    }

    /** รายการที่ยังไม่คืนและเลยกำหนด เรียงจากเกินนานที่สุดก่อน */
    public function overdue(?DateTimeImmutable $today = null): array
    {
        $today ??= new DateTimeImmutable('today');
        $stmt = $this->pdo->prepare(
            "SELECT l.loan_id, l.borrowed_at, l.due_date, b.title, m.member_id, m.member_code, m.full_name, m.phone
               FROM loans l
               JOIN books b   ON b.book_id = l.book_id
               JOIN members m ON m.member_id = l.member_id
              WHERE l.status = 'borrowed' AND l.due_date < ?
              ORDER BY l.due_date, l.loan_id"
        );
        $stmt->execute([$today->format('Y-m-d')]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['days_late'] = $this->loans->daysLate($row['due_date'], $today);
            $row['accrued_fine'] = $this->loans->calculateFine($row['due_date'], $today);
        }
        return $rows;
    }

    /** ตัวเลขสรุปสำหรับแดชบอร์ด */
    public function dashboard(?DateTimeImmutable $today = null): array
    {
        $today ??= new DateTimeImmutable('today');
        $stmt = $this->pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM loans WHERE status = 'borrowed' AND due_date < ?) AS overdue,
                (SELECT COUNT(*) FROM loans WHERE status = 'borrowed') AS active_loans,
                (SELECT COUNT(*) FROM books) AS books,
                (SELECT COUNT(*) FROM members WHERE status = 'active') AS active_members"
        );
        $stmt->execute([$today->format('Y-m-d')]);
        return array_map('intval', $stmt->fetch());
    }
}
