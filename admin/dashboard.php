<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';

$b = app_base();

$cStudent = (int) $pdo->query('SELECT COUNT(*) FROM STUDENT')->fetchColumn();
$cOrg = (int) $pdo->query('SELECT COUNT(*) FROM TRAINING_ORGANIZATION')->fetchColumn();
$cOpp = (int) $pdo->query('SELECT COUNT(*) FROM TRAINING_OPPORTUNITY')->fetchColumn();
$cApp = (int) $pdo->query('SELECT COUNT(*) FROM TRAINING_APPLICATION')->fetchColumn();
$cRev = (int) $pdo->query('SELECT COUNT(*) FROM REVIEW')->fetchColumn();
$cSup = (int) $pdo->query('SELECT COUNT(*) FROM UNIVERSITY_SUPERVISOR')->fetchColumn();
$cAdm = (int) $pdo->query('SELECT COUNT(*) FROM ADMIN')->fetchColumn();

$pending = $pdo->query('SELECT organization_id, organization_name, email, registration_date FROM TRAINING_ORGANIZATION WHERE is_approved = 0 ORDER BY registration_date DESC')->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$pageTitle = 'لوحة الإدارة - مسار';
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

        <h1 class="page-title">لوحة الإدارة</h1>

        <div class="dashboard-stats">
            <div class="stat-box"><span class="num"><?php echo $cStudent; ?></span><span class="lbl">طلاب</span></div>
            <div class="stat-box"><span class="num"><?php echo $cOrg; ?></span><span class="lbl">جهات</span></div>
            <div class="stat-box"><span class="num"><?php echo $cOpp; ?></span><span class="lbl">فرص</span></div>
            <div class="stat-box"><span class="num"><?php echo $cApp; ?></span><span class="lbl">طلبات</span></div>
            <div class="stat-box"><span class="num"><?php echo $cRev; ?></span><span class="lbl">تقييمات</span></div>
            <div class="stat-box"><span class="num"><?php echo $cSup; ?></span><span class="lbl">مشرفون</span></div>
            <div class="stat-box"><span class="num"><?php echo $cAdm; ?></span><span class="lbl">مدراء</span></div>
        </div>

        <p>
            <a href="<?php echo $b; ?>admin/approve_companies.php" class="btn btn-primary btn-sm">اعتماد الجهات</a>
            <a href="<?php echo $b; ?>admin/manage_users.php" class="btn btn-outline btn-sm">إدارة الطلاب</a>
        </p>

        <h2 class="page-title" style="font-size:1.15rem;">جهات بانتظار الموافقة</h2>
        <?php if (count($pending) === 0) : ?>
            <p class="alert alert-info">لا توجد جهات معلّقة.</p>
        <?php else : ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>البريد</th>
                            <th>تاريخ التسجيل</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $p) : ?>
                            <tr>
                                <td><?php echo clean($p['organization_name']); ?></td>
                                <td><?php echo clean($p['email']); ?></td>
                                <td><?php echo fDate($p['registration_date']); ?></td>
                                <td>
                                    <form method="post" action="<?php echo $b; ?>admin/approve_companies.php" style="display:inline;">
                                        <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                                        <input type="hidden" name="organization_id" value="<?php echo (int) $p['organization_id']; ?>">
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
