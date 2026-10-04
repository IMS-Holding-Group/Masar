<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('company');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$oid = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $action = $_POST['action'] ?? '';
    $oppId = (int) ($_POST['opportunity_id'] ?? 0);

    $own = $pdo->prepare('SELECT opportunity_id FROM TRAINING_OPPORTUNITY WHERE opportunity_id = ? AND organization_id = ? LIMIT 1');
    $own->execute([$oppId, $oid]);
    if (!$own->fetch()) {
        setFlash('error', 'الفرصة غير موجودة أو لا تخص جهتك.');
        redirect($b . 'company/manage_opportunities.php');
    }

    if ($action === 'deactivate') {
        $u = $pdo->prepare('UPDATE TRAINING_OPPORTUNITY SET is_active = 0 WHERE opportunity_id = ? AND organization_id = ?');
        $u->execute([$oppId, $oid]);
        setFlash('success', 'تم إغلاق الفرصة.');
    } elseif ($action === 'delete') {
        $d = $pdo->prepare('DELETE FROM TRAINING_OPPORTUNITY WHERE opportunity_id = ? AND organization_id = ?');
        $d->execute([$oppId, $oid]);
        setFlash('success', 'تم حذف الفرصة.');
    }
    redirect($b . 'company/manage_opportunities.php');
}

$stmt = $pdo->prepare('SELECT opportunity_id, title, is_active, duration_weeks, posted_date FROM TRAINING_OPPORTUNITY WHERE organization_id = ? ORDER BY posted_date DESC');
$stmt->execute([$oid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$pageTitle = 'إدارة الفرص - مسار';
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
        <?php if ($flash) : ?>
            <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'error' : 'info'); ?>"><?php echo clean($flash['message']); ?></div>
        <?php endif; ?>

        <h1 class="page-title">إدارة الفرص</h1>
        <p>
            <a href="<?php echo $b; ?>company/dashboard.php" class="btn btn-secondary btn-sm">اللوحة</a>
            <a href="<?php echo $b; ?>company/add_opportunity.php" class="btn btn-primary btn-sm">إضافة فرصة</a>
        </p>

        <?php if (count($rows) === 0) : ?>
            <p class="alert alert-info">لا توجد فرص بعد. <a href="<?php echo $b; ?>company/add_opportunity.php">أضف فرصة</a>.</p>
        <?php else : ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>العنوان</th>
                        <th>المدة (أسابيع)</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r) : ?>
                        <tr>
                            <td><?php echo clean($r['title']); ?></td>
                            <td><?php echo (int) $r['duration_weeks']; ?></td>
                            <td><?php echo fDate($r['posted_date']); ?></td>
                            <td><?php echo (int) $r['is_active'] ? '<span class="badge badge-active">نشط</span>' : '<span class="badge badge-pending">مغلق</span>'; ?></td>
                            <td>
                                <a class="btn btn-outline btn-sm" href="<?php echo $b; ?>company/edit_opportunity.php?id=<?php echo (int) $r['opportunity_id']; ?>">تعديل</a>
                                <?php if ((int) $r['is_active'] === 1) : ?>
                                    <form method="post" style="display:inline;" action="">
                                        <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <input type="hidden" name="opportunity_id" value="<?php echo (int) $r['opportunity_id']; ?>">
                                        <button type="submit" class="btn btn-secondary btn-sm">إغلاق</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" style="display:inline;" action="">
                                    <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="opportunity_id" value="<?php echo (int) $r['opportunity_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm confirm-delete">حذف</button>
                                </form>
                            </td>
                        </tr>
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
