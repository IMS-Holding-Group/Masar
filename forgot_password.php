<?php
require_once __DIR__ . '/includes/session_bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    redirect(app_base() . 'index.php');
}

$b = app_base();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $email = clean($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'يرجى إدخال بريد إلكتروني صالح.');
        redirect($b . 'forgot_password.php');
    }

    $role = null;
    $st = $pdo->prepare('SELECT student_id FROM STUDENT WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    if ($st->fetch()) {
        $role = 'student';
    }
    if ($role === null) {
        $o = $pdo->prepare('SELECT organization_id FROM TRAINING_ORGANIZATION WHERE email = ? LIMIT 1');
        $o->execute([$email]);
        if ($o->fetch()) {
            $role = 'company';
        }
    }
    if ($role === null) {
        $a = $pdo->prepare('SELECT admin_id FROM ADMIN WHERE email = ? LIMIT 1');
        $a->execute([$email]);
        if ($a->fetch()) {
            $role = 'admin';
        }
    }

    if ($role !== null) {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $exp = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
        $pdo->prepare('DELETE FROM PASSWORD_RESET WHERE email = ? AND user_role = ?')->execute([$email, $role]);
        $ins = $pdo->prepare('INSERT INTO PASSWORD_RESET (email, user_role, token_hash, expires_at) VALUES (?,?,?,?)');
        $ins->execute([$email, $role, $hash, $exp]);
        $link = $b . 'reset_password.php?token=' . rawurlencode($token);
        setFlash('success', 'إن وُجد الحساب، يمكنك إعادة تعيين كلمة المرور خلال ساعة. رابط مؤقت (بيئة تطوير — لا تشاركه): ' . $link);
    } else {
        setFlash('info', 'إن وُجد الحساب المرتبط بهذا البريد، ستصلك تعليمات إعادة التعيين. تحقق من صندوق الوارد أو الرسائل غير المرغوبة.');
    }
    redirect($b . 'forgot_password.php');
}

$pageTitle = 'استعادة كلمة المرور - مسار';
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
            <h2>استعادة كلمة المرور</h2>
            <?php require_once __DIR__ . '/config/support.php'; $fs = masar_support(); $fsp = $fs['phone_display'] !== '' ? $fs['phone_display'] : ($fs['phone'] ?? ''); ?>
            <p style="color:var(--gray-text);font-size:0.95rem;margin-bottom:1rem;">أدخل بريدك المسجّل في المنصة. في بيئة الإنتاج يُرسل الرابط عبر البريد فقط.</p>
            <?php if ($fsp !== '' || ($fs['email'] ?? '') !== '') : ?>
                <p style="color:var(--gray-text);font-size:0.95rem;margin-bottom:1rem;">للمساعدة: <?php if ($fsp !== '') : ?>هاتف الدعم <a href="tel:<?php echo preg_replace('/\s+/u', '', (string) ($fs['phone'] ?? '')); ?>"><?php echo clean($fsp); ?></a><?php endif; ?><?php if ($fsp !== '' && ($fs['email'] ?? '') !== '') : ?> — <?php endif; ?><?php if (($fs['email'] ?? '') !== '') : ?>بريد الدعم <a href="mailto:<?php echo htmlspecialchars($fs['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo clean($fs['email']); ?></a><?php endif; ?>.</p>
            <?php endif; ?>
            <form method="post" action="">
                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" required autocomplete="email">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">متابعة</button>
            </form>
            <p style="margin-top:1rem;text-align:center;"><a href="<?php echo $b; ?>login.php">العودة لتسجيل الدخول</a></p>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
