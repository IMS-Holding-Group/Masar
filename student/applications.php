<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$sid = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT a.application_id, a.application_date, a.status, a.cover_letter, a.rejection_reason, a.response_date,
    o.title, o.opportunity_id, org.organization_name
    FROM TRAINING_APPLICATION a
    INNER JOIN TRAINING_OPPORTUNITY o ON a.opportunity_id = o.opportunity_id
    INNER JOIN TRAINING_ORGANIZATION org ON o.organization_id = org.organization_id
    WHERE a.student_id = ? ORDER BY a.application_date DESC, a.application_id DESC');
$stmt->execute([$sid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'طلباتي - مسار';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo clean($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="page-wrap">
        <h1 class="page-title">طلبات التقديم</h1>
        <p><a href="<?php echo $b; ?>student/dashboard.php" class="btn btn-secondary btn-sm">العودة للوحة</a></p>

        <?php if (count($rows) === 0) : ?>
            <p class="alert alert-info">لا توجد طلبات بعد.</p>
        <?php else : ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>الفرصة</th>
                            <th>الجهة</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r) : ?>
                            <tr>
                                <td><?php echo clean($r['title']); ?></td>
                                <td><?php echo clean($r['organization_name']); ?></td>
                                <td><?php echo fDate($r['application_date']); ?></td>
                                <td>
                                    <?php
                                    $s = $r['status'];
                                    $cls = $s === 'accepted' ? 'badge-accepted' : ($s === 'rejected' ? 'badge-rejected' : 'badge-pending');
                                    ?>
                                    <span class="badge <?php echo $cls; ?>"><?php echo clean($s); ?></span>
                                </td>
                                <td><a class="btn btn-outline btn-sm" href="<?php echo $b; ?>opportunity_details.php?id=<?php echo (int) $r['opportunity_id']; ?>">تفاصيل الفرصة</a></td>
                            </tr>
                            <?php if (!empty($r['rejection_reason'])) : ?>
                                <tr>
                                    <td colspan="5" style="font-size:0.9rem;color:var(--gray-text);">سبب الرفض: <?php echo clean($r['rejection_reason']); ?> <?php echo $r['response_date'] ? '— ' . fDate($r['response_date']) : ''; ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
