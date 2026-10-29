<?php
declare(strict_types=1);

/**
 * R-07 รายการเกินกำหนด และตัวเลขสรุปบนแดชบอร์ด, R-08 รายงานประวัติการยืม-คืน
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

    /**
     * R-08 ประวัติการยืม-คืน กรองตามสมาชิกและช่วงวันที่ยืม (borrowed_at รวมวันปลายทั้งสองด้าน)
     * วันที่รูปแบบ YYYY-MM-DD หรือ null = ไม่กรอง
     * คืน ['ok' => bool, 'error' => ..., 'rows' => [...], 'count' => int, 'total_fine' => float]
     */
    public function history(?int $memberId = null, ?string $from = null, ?string $to = null): array
    {
        foreach ([$from, $to] as $date) {
            if ($date !== null && !valid_date($date)) {
                return ['ok' => false, 'error' => 'รูปแบบวันที่ไม่ถูกต้อง', 'rows' => [], 'count' => 0, 'total_fine' => 0.0];
            }
        }
        if ($from !== null && $to !== null && $from > $to) {
            return ['ok' => false, 'error' => 'วันที่เริ่มต้นต้องไม่หลังวันที่สิ้นสุด', 'rows' => [], 'count' => 0, 'total_fine' => 0.0];
        }

        $where = [];
        $params = [];
        if ($memberId !== null) {
            $where[] = 'l.member_id = ?';
            $params[] = $memberId;
        }
        if ($from !== null) {
            $where[] = 'l.borrowed_at >= ?';
            $params[] = $from;
        }
        if ($to !== null) {
            $where[] = 'l.borrowed_at <= ?';
            $params[] = $to;
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->pdo->prepare(
            "SELECT l.loan_id, l.borrowed_at, l.due_date, l.returned_at, l.fine_amount, l.status,
                    b.title, m.member_code, m.full_name
               FROM loans l
               JOIN books b   ON b.book_id = l.book_id
               JOIN members m ON m.member_id = l.member_id
               $whereSql
              ORDER BY l.borrowed_at DESC, l.loan_id DESC"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return [
            'ok'         => true,
            'rows'       => $rows,
            'count'      => count($rows),
            'total_fine' => array_sum(array_map('floatval', array_column($rows, 'fine_amount'))),
        ];
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
