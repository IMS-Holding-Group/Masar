<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('company');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$oid = (int) $_SESSION['user_id'];
$oppId = (int) ($_GET['opp'] ?? 0);
if ($oppId < 1) {
    redirect($b . 'company/dashboard.php');
}

$chk = $pdo->prepare('SELECT opportunity_id, title FROM TRAINING_OPPORTUNITY WHERE opportunity_id = ? AND organization_id = ? LIMIT 1');
$chk->execute([$oppId, $oid]);
$opp = $chk->fetch(PDO::FETCH_ASSOC);
if (!$opp) {
    setFlash('error', 'الفرصة غير موجودة أو لا تخص جهتك.');
    redirect($b . 'company/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $application_id = (int) ($_POST['application_id'] ?? 0);
    $action = $_POST['review_action'] ?? '';
    $reason = clean($_POST['rejection_reason'] ?? '');

    $appChk = $pdo->prepare('SELECT application_id FROM TRAINING_APPLICATION WHERE application_id = ? AND opportunity_id = ? LIMIT 1');
    $appChk->execute([$application_id, $oppId]);
    if (!$appChk->fetch()) {
        setFlash('error', 'طلب غير صالح.');
        redirect($b . 'company/review_applications.php?opp=' . $oppId);
    }

    if ($action === 'accept') {
        $u = $pdo->prepare('UPDATE TRAINING_APPLICATION SET status = \'accepted\', response_date = CURDATE(), rejection_reason = NULL WHERE application_id = ?');
        $u->execute([$application_id]);
        setFlash('success', 'تم قبول الطلب.');
    } elseif ($action === 'reject') {
        $u = $pdo->prepare('UPDATE TRAINING_APPLICATION SET status = \'rejected\', response_date = CURDATE(), rejection_reason = ? WHERE application_id = ?');
        $u->execute([$reason, $application_id]);
        setFlash('success', 'تم رفض الطلب.');
    }
    redirect($b . 'company/review_applications.php?opp=' . $oppId);
}

$stmt = $pdo->prepare('SELECT a.application_id, a.application_date, a.status, a.cover_letter, s.full_name, s.email, s.phone, s.major, s.university
    FROM TRAINING_APPLICATION a
    INNER JOIN STUDENT s ON a.student_id = s.student_id
    WHERE a.opportunity_id = ? ORDER BY a.application_date DESC');
$stmt->execute([$oppId]);
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$pageTitle = 'مراجعة الطلبات - مسار';
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

        <h1 class="page-title">طلبات: <?php echo clean($opp['title']); ?></h1>
        <p><a href="<?php echo $b; ?>company/dashboard.php" class="btn btn-secondary btn-sm">العودة</a></p>

        <?php if (count($apps) === 0) : ?>
            <p class="alert alert-info">لا توجد طلبات بعد.</p>
        <?php else : ?>
            <?php foreach ($apps as $a) : ?>
                <div class="opp-item" style="flex-direction:column;align-items:stretch;">
                    <div><strong><?php echo clean($a['full_name']); ?></strong> — <?php echo clean($a['email']); ?> — <?php echo clean($a['phone']); ?></div>
                    <p style="font-size:0.9rem;color:var(--gray-text);"><?php echo clean($a['university']); ?> / <?php echo clean($a['major']); ?></p>
                    <p style="margin:0.5rem 0;"><?php echo nl2br(clean($a['cover_letter'])); ?></p>
                    <p>الحالة:
                        <?php
                        $s = $a['status'];
                        $cls = $s === 'accepted' ? 'badge-accepted' : ($s === 'rejected' ? 'badge-rejected' : 'badge-pending');
                        ?>
                        <span class="badge <?php echo $cls; ?>"><?php echo clean($s); ?></span> — <?php echo fDate($a['application_date']); ?>
                    </p>
                    <?php if ($a['status'] === 'pending') : ?>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.5rem;">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                                <input type="hidden" name="application_id" value="<?php echo (int) $a['application_id']; ?>">
                                <input type="hidden" name="review_action" value="accept">
                                <button type="submit" class="btn btn-primary btn-sm">قبول</button>
                            </form>
                            <form method="post" style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:flex-end;">
                                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                                <input type="hidden" name="application_id" value="<?php echo (int) $a['application_id']; ?>">
                                <input type="hidden" name="review_action" value="reject">
                                <input type="text" name="rejection_reason" placeholder="سبب الرفض (اختياري)" style="min-width:200px;padding:0.4rem 0.6rem;border:2px solid var(--gray-light);border-radius:8px;">
                                <button type="submit" class="btn btn-danger btn-sm">رفض</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
