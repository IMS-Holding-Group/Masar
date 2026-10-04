<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$sid = (int) $_SESSION['user_id'];

$stTot = $pdo->prepare('SELECT COUNT(*) FROM TRAINING_APPLICATION WHERE student_id = ?');
$stTot->execute([$sid]);
$tot = (int) $stTot->fetchColumn();

$stAcc = $pdo->prepare('SELECT COUNT(*) FROM TRAINING_APPLICATION WHERE student_id = ? AND status = \'accepted\'');
$stAcc->execute([$sid]);
$acc = (int) $stAcc->fetchColumn();

$stRej = $pdo->prepare('SELECT COUNT(*) FROM TRAINING_APPLICATION WHERE student_id = ? AND status = \'rejected\'');
$stRej->execute([$sid]);
$rej = (int) $stRej->fetchColumn();

$stPend = $pdo->prepare('SELECT COUNT(*) FROM TRAINING_APPLICATION WHERE student_id = ? AND status = \'pending\'');
$stPend->execute([$sid]);
$pend = (int) $stPend->fetchColumn();

$last = $pdo->prepare('SELECT a.application_id, a.status, a.application_date, o.title, org.organization_name
    FROM TRAINING_APPLICATION a
    INNER JOIN TRAINING_OPPORTUNITY o ON a.opportunity_id = o.opportunity_id
    INNER JOIN TRAINING_ORGANIZATION org ON o.organization_id = org.organization_id
    WHERE a.student_id = ? ORDER BY a.application_date DESC, a.application_id DESC LIMIT 3');
$last->execute([$sid]);
$recent = $last->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$pageTitle = 'لوحة الطالب - مسار';
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

        <h1 class="page-title">مرحباً، <?php echo clean($_SESSION['user_name'] ?? ''); ?></h1>

        <div class="dashboard-stats">
            <div class="stat-box"><span class="num"><?php echo $tot; ?></span><span class="lbl">إجمالي الطلبات</span></div>
            <div class="stat-box"><span class="num"><?php echo $acc; ?></span><span class="lbl">مقبولة</span></div>
            <div class="stat-box"><span class="num"><?php echo $rej; ?></span><span class="lbl">مرفوضة</span></div>
            <div class="stat-box"><span class="num"><?php echo $pend; ?></span><span class="lbl">قيد المراجعة</span></div>
        </div>

        <p>
            <a href="<?php echo $b; ?>opportunities.php" class="btn btn-primary btn-sm">استكشف الفرص</a>
            <a href="<?php echo $b; ?>student/applications.php" class="btn btn-outline btn-sm">طلباتي</a>
            <a href="<?php echo $b; ?>student/profile.php" class="btn btn-secondary btn-sm">الملف الشخصي</a>
            <a href="<?php echo $b; ?>student/write_review.php" class="btn btn-outline btn-sm">تقييم جهة</a>
        </p>

        <h2 class="page-title" style="font-size:1.15rem;">آخر الطلبات</h2>
        <?php if (count($recent) === 0) : ?>
            <p class="alert alert-info">لم تتقدم بعد. تصفح <a href="<?php echo $b; ?>opportunities.php">الفرص المتاحة</a>.</p>
        <?php else : ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>الفرصة</th>
                            <th>الجهة</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $r) : ?>
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
