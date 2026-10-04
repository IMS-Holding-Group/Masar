<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$sid = (int) $_SESSION['user_id'];

$eligibleSql = 'SELECT DISTINCT org.organization_id, org.organization_name
    FROM TRAINING_APPLICATION a
    INNER JOIN TRAINING_OPPORTUNITY o ON a.opportunity_id = o.opportunity_id
    INNER JOIN TRAINING_ORGANIZATION org ON o.organization_id = org.organization_id
    WHERE a.student_id = ? AND a.status = \'accepted\' AND o.end_date < CURDATE()
    AND NOT EXISTS (
        SELECT 1 FROM REVIEW r WHERE r.student_id = a.student_id AND r.organization_id = org.organization_id
    )
    ORDER BY org.organization_name';
$elStmt = $pdo->prepare($eligibleSql);
$elStmt->execute([$sid]);
$eligible = $elStmt->fetchAll(PDO::FETCH_ASSOC);

$orgId = (int) ($_GET['org'] ?? ($_POST['organization_id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $orgId = (int) ($_POST['organization_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $text = clean($_POST['review_text'] ?? '');

    if ($orgId < 1 || $rating < 1 || $rating > 5) {
        setFlash('error', 'يرجى اختيار الجهة وتقييم صالح (1–5).');
        redirect($b . 'student/write_review.php');
    }
    if ($text === '') {
        setFlash('error', 'يرجى كتابة تعليق نصي يصف تجربتك.');
        redirect($b . 'student/write_review.php?org=' . $orgId);
    }

    $ok = $pdo->prepare('SELECT 1 FROM TRAINING_APPLICATION a
        INNER JOIN TRAINING_OPPORTUNITY o ON a.opportunity_id = o.opportunity_id
        INNER JOIN TRAINING_ORGANIZATION org ON o.organization_id = org.organization_id
        WHERE a.student_id = ? AND org.organization_id = ? AND a.status = \'accepted\' AND o.end_date < CURDATE()
        AND NOT EXISTS (SELECT 1 FROM REVIEW r WHERE r.student_id = a.student_id AND r.organization_id = org.organization_id)
        LIMIT 1');
    $ok->execute([$sid, $orgId]);
    if (!$ok->fetch()) {
        setFlash('error', 'لا يمكنك تقييم هذه الجهة (تأكد من إتمام تدريب مقبول وانتهاء الفترة، وعدم وجود تقييم سابق).');
        redirect($b . 'student/write_review.php');
    }

    $dup = $pdo->prepare('SELECT review_id FROM REVIEW WHERE student_id = ? AND organization_id = ? LIMIT 1');
    $dup->execute([$sid, $orgId]);
    if ($dup->fetch()) {
        setFlash('error', 'سبق أن قيّمت هذه الجهة.');
        redirect($b . 'student/write_review.php');
    }

    $ins = $pdo->prepare('INSERT INTO REVIEW (student_id, organization_id, rating, review_text, review_date, is_verified) VALUES (?,?,?,?,CURDATE(),1)');
    $ins->execute([$sid, $orgId, $rating, $text]);
    setFlash('success', 'شكراً لك، نُشر تقييمك وسيظهر لزملائك على صفحات فرص الجهة.');
    redirect($b . 'student/write_review.php');
}

$flash = getFlash();
$selectedOrg = null;
if ($orgId > 0) {
    foreach ($eligible as $e) {
        if ((int) $e['organization_id'] === $orgId) {
            $selectedOrg = $e;
            break;
        }
    }
}

$pageTitle = 'تقييم جهة تدريب - مسار';
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

        <h1 class="page-title">تقييم تجربة تدريب</h1>
        <p><a href="<?php echo $b; ?>student/dashboard.php" class="btn btn-secondary btn-sm">العودة للوحة</a></p>

        <p style="color:var(--gray-text);max-width:640px;line-height:1.7;">بعد إتمام تدريب مقبول وانتهاء تاريخ الفرصة، يمكنك تقييم جهة التدريب مرة واحدة فقط لكل جهة، لمساعدة الطلاب على اختيار مستنير.</p>

        <?php if (count($eligible) === 0) : ?>
            <p class="alert alert-info">لا توجد جهات متاحة للتقييم حالياً. يجب أن يكون لديك طلب <strong>مقبول</strong> على فرصة انتهت فعلياً، وألا يكون لديك تقييم سابق لنفس الجهة.</p>
        <?php elseif ($selectedOrg) : ?>
            <div class="form-box" style="max-width:520px;">
                <h2 style="font-size:1.15rem;margin-bottom:1rem;"><?php echo clean($selectedOrg['organization_name']); ?></h2>
                <form method="post" action="">
                    <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                    <input type="hidden" name="organization_id" value="<?php echo (int) $selectedOrg['organization_id']; ?>">
                    <div class="form-group">
                        <label for="rating">التقييم (1–5)</label>
                        <select name="rating" id="rating" required>
                            <?php for ($i = 5; $i >= 1; $i--) : ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> — <?php echo starsHtml($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="review_text">تعليقك عن التجربة</label>
                        <textarea name="review_text" id="review_text" required placeholder="صف بيئة العمل، الإشراف، والفائدة المهنية..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">نشر التقييم</button>
                </form>
            </div>
        <?php else : ?>
            <h2 class="page-title" style="font-size:1.1rem;">اختر الجهة</h2>
            <ul style="list-style:none;padding:0;">
                <?php foreach ($eligible as $e) : ?>
                    <li class="opp-item" style="margin-bottom:0.5rem;">
                        <span><?php echo clean($e['organization_name']); ?></span>
                        <a class="btn btn-primary btn-sm" href="<?php echo $b; ?>student/write_review.php?org=<?php echo (int) $e['organization_id']; ?>">تقييم</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
