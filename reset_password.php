<?php
require_once __DIR__ . '/includes/session_bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    redirect(app_base() . 'index.php');
}

$b = app_base();
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$token = is_string($token) ? trim($token) : '';
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $token = trim((string) ($_POST['token'] ?? ''));
    $p1 = $_POST['password'] ?? '';
    $p2 = $_POST['password_confirm'] ?? '';
    if (strlen($token) < 32) {
        setFlash('error', 'رابط غير صالح.');
        redirect($b . 'forgot_password.php');
    }
    if ($p1 === '' || $p1 !== $p2) {
        setFlash('error', 'كلمة المرور غير متطابقة أو فارغة.');
        redirect($b . 'reset_password.php?token=' . rawurlencode($token));
    }
    if (strlen($p1) < 8) {
        setFlash('error', 'كلمة المرور يجب ألا تقل عن 8 أحرف.');
        redirect($b . 'reset_password.php?token=' . rawurlencode($token));
    }

    $hash = hash('sha256', $token);
    $q = $pdo->prepare('SELECT reset_id, email, user_role FROM PASSWORD_RESET WHERE token_hash = ? AND expires_at > NOW() LIMIT 1');
    $q->execute([$hash]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        setFlash('error', 'انتهت صلاحية الرابط أو غير صالح. اطلب رابطاً جديداً.');
        redirect($b . 'forgot_password.php');
    }

    $newHash = password_hash($p1, PASSWORD_BCRYPT);
    $email = $row['email'];
    $role = $row['user_role'];

    if ($role === 'student') {
        $u = $pdo->prepare('UPDATE STUDENT SET password = ? WHERE email = ? LIMIT 1');
        $u->execute([$newHash, $email]);
    } elseif ($role === 'company') {
        $u = $pdo->prepare('UPDATE TRAINING_ORGANIZATION SET password = ? WHERE email = ? LIMIT 1');
        $u->execute([$newHash, $email]);
    } elseif ($role === 'admin') {
        $u = $pdo->prepare('UPDATE ADMIN SET password = ? WHERE email = ? LIMIT 1');
        $u->execute([$newHash, $email]);
    }

    $pdo->prepare('DELETE FROM PASSWORD_RESET WHERE reset_id = ?')->execute([(int) $row['reset_id']]);
    setFlash('success', 'تم تغيير كلمة المرور. يمكنك تسجيل الدخول الآن.');
    redirect($b . 'login.php');
}

if (strlen($token) < 32) {
    setFlash('error', 'رابط إعادة التعيين غير صالح.');
    redirect($b . 'forgot_password.php');
}

$verify = $pdo->prepare('SELECT reset_id FROM PASSWORD_RESET WHERE token_hash = ? AND expires_at > NOW() LIMIT 1');
$verify->execute([hash('sha256', $token)]);
if (!$verify->fetch()) {
    setFlash('error', 'انتهت صلاحية الرابط. اطلب رابطاً جديداً.');
    redirect($b . 'forgot_password.php');
}

$pageTitle = 'كلمة مرور جديدة - مسار';
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
<?php require_once __DIR__ . '/includes/header.php'; ?>

    <main class="page-wrap">
        <?php if ($flash) : ?>
            <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'error' : 'info'); ?>"><?php echo clean($flash['message']); ?></div>
        <?php endif; ?>

        <div class="form-box" style="max-width:440px;">
            <h2>تعيين كلمة مرور جديدة</h2>
            <form method="post" action="">
                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                <input type="hidden" name="token" value="<?php echo clean($token); ?>">
                <div class="form-group">
                    <label for="password">كلمة المرور الجديدة</label>
                    <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password_confirm">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirm" id="password_confirm" required minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">حفظ</button>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
