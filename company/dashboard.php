<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('company');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$oid = (int) $_SESSION['user_id'];

$stOpp = $pdo->prepare('SELECT COUNT(*) FROM TRAINING_OPPORTUNITY WHERE organization_id = ?');
$stOpp->execute([$oid]);
$cntOpp = (int) $stOpp->fetchColumn();

$stAct = $pdo->prepare('SELECT COUNT(*) FROM TRAINING_OPPORTUNITY WHERE organization_id = ? AND is_active = 1');
$stAct->execute([$oid]);
$cntAct = (int) $stAct->fetchColumn();

$stApp = $pdo->prepare('SELECT COUNT(*) FROM TRAINING_APPLICATION a INNER JOIN TRAINING_OPPORTUNITY o ON a.opportunity_id = o.opportunity_id WHERE o.organization_id = ?');
$stApp->execute([$oid]);
$cntApp = (int) $stApp->fetchColumn();

$list = $pdo->prepare('SELECT o.opportunity_id, o.title, o.is_active, o.available_positions,
    (SELECT COUNT(*) FROM TRAINING_APPLICATION a WHERE a.opportunity_id = o.opportunity_id) AS applicants
    FROM TRAINING_OPPORTUNITY o WHERE o.organization_id = ? ORDER BY o.posted_date DESC');
$list->execute([$oid]);
$opps = $list->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$pageTitle = 'لوحة الجهة - مسار';
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

        <h1 class="page-title"><?php echo clean($_SESSION['user_name'] ?? ''); ?></h1>

        <div class="dashboard-stats">
            <div class="stat-box"><span class="num"><?php echo $cntOpp; ?></span><span class="lbl">إجمالي الفرص</span></div>
            <div class="stat-box"><span class="num"><?php echo $cntAct; ?></span><span class="lbl">فرص نشطة</span></div>
            <div class="stat-box"><span class="num"><?php echo $cntApp; ?></span><span class="lbl">إجمالي المتقدمين</span></div>
        </div>

        <p>
            <a href="<?php echo $b; ?>company/add_opportunity.php" class="btn btn-primary btn-sm">إضافة فرصة</a>
            <a href="<?php echo $b; ?>company/manage_opportunities.php" class="btn btn-outline btn-sm">إدارة الفرص</a>
            <a href="<?php echo $b; ?>company/profile.php" class="btn btn-secondary btn-sm">الملف التعريفي</a>
        </p>

        <h2 class="page-title" style="font-size:1.15rem;">فرصك</h2>
        <?php if (count($opps) === 0) : ?>
            <p class="alert alert-info">لم تنشر فرصاً بعد.</p>
        <?php else : ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>العنوان</th>
                            <th>المتقدمون</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($opps as $o) : ?>
                            <tr>
                                <td><?php echo clean($o['title']); ?></td>
                                <td><?php echo (int) $o['applicants']; ?></td>
                                <td><?php echo (int) $o['is_active'] ? '<span class="badge badge-active">نشط</span>' : '<span class="badge badge-rejected">مغلق</span>'; ?></td>
                                <td>
                                    <a class="btn btn-outline btn-sm" href="<?php echo $b; ?>company/review_applications.php?opp=<?php echo (int) $o['opportunity_id']; ?>">الطلبات</a>
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
