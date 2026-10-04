<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';

$b = app_base();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $orgId = (int) ($_POST['organization_id'] ?? 0);
    if ($orgId < 1) {
        setFlash('error', 'معرّف غير صالح.');
        redirect($b . 'admin/approve_companies.php');
    }
    $u = $pdo->prepare('UPDATE TRAINING_ORGANIZATION SET is_approved = 1 WHERE organization_id = ?');
    $u->execute([$orgId]);
    setFlash('success', 'تم اعتماد الجهة.');
    redirect($b . 'admin/approve_companies.php');
}

$rows = $pdo->query('SELECT organization_id, organization_name, email, city, phone, registration_date FROM TRAINING_ORGANIZATION WHERE is_approved = 0 ORDER BY registration_date DESC')->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$pageTitle = 'اعتماد الجهات - مسار';
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

        <h1 class="page-title">اعتماد جهات التدريب</h1>
        <p><a href="<?php echo $b; ?>admin/dashboard.php" class="btn btn-secondary btn-sm">العودة للوحة</a></p>

        <?php if (count($rows) === 0) : ?>
            <p class="alert alert-info">لا توجد جهات معلّقة.</p>
        <?php else : ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>الجهة</th>
                            <th>البريد</th>
                            <th>المدينة</th>
                            <th>الهاتف</th>
                            <th>التاريخ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r) : ?>
                            <tr>
                                <td><?php echo clean($r['organization_name']); ?></td>
                                <td><?php echo clean($r['email']); ?></td>
                                <td><?php echo clean($r['city']); ?></td>
                                <td><?php echo clean($r['phone']); ?></td>
                                <td><?php echo fDate($r['registration_date']); ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                                        <input type="hidden" name="organization_id" value="<?php echo (int) $r['organization_id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">موافقة</button>
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
