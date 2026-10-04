<?php
require_once __DIR__ . '/includes/session_bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    $b = app_base();
    $t = $_SESSION['user_type'] ?? '';
    if ($t === 'student') {
        redirect($b . 'student/dashboard.php');
    }
    if ($t === 'company') {
        redirect($b . 'company/dashboard.php');
    }
    if ($t === 'admin') {
        redirect($b . 'admin/dashboard.php');
    }
    redirect($b . 'index.php');
}

$flash = getFlash();
$b = app_base();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $identifier = clean($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        setFlash('error', 'يرجى إدخال البريد/اسم المستخدم وكلمة المرور.');
        redirect($b . 'login.php');
    }

    // طالب بالبريد
    $st = $pdo->prepare('SELECT student_id, full_name, email, password, status FROM STUDENT WHERE email = ? LIMIT 1');
    $st->execute([$identifier]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['password'])) {
        if (strtolower((string) $row['status']) !== 'active') {
            require_once __DIR__ . '/config/support.php';
            $s = masar_support();
            $tel = $s['phone_display'] !== '' ? $s['phone_display'] : ($s['phone'] ?? '');
            $hint = $tel !== '' ? ' للدعم: ' . $tel . ($s['email'] !== '' ? ' أو ' . $s['email'] : '') : ($s['email'] !== '' ? ' للدعم: ' . $s['email'] : '');
            setFlash('error', 'حسابك غير مفعّل.' . $hint);
            redirect($b . 'login.php');
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $row['student_id'];
        $_SESSION['user_type'] = 'student';
        $_SESSION['user_name'] = $row['full_name'];
        redirect($b . 'student/dashboard.php');
    }

    // حساب الإدارة: اسم المستخدم أو البريد
    $ad = $pdo->prepare('SELECT admin_id, username, password, full_name, email FROM ADMIN WHERE username = ? OR email = ? LIMIT 1');
    $ad->execute([$identifier, $identifier]);
    $row = $ad->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $row['admin_id'];
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_name'] = $row['full_name'];
        redirect($b . 'admin/dashboard.php');
    }

    // جهة تدريب بالبريد
    $org = $pdo->prepare('SELECT organization_id, organization_name, email, password, is_approved FROM TRAINING_ORGANIZATION WHERE email = ? LIMIT 1');
    $org->execute([$identifier]);
    $row = $org->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, (string) $row['password'])) {
        if (!(int) $row['is_approved']) {
            setFlash('error', 'حساب جهتك قيد مراجعة الإدارة ولم يُعتمد بعد.');
            redirect($b . 'login.php');
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $row['organization_id'];
        $_SESSION['user_type'] = 'company';
        $_SESSION['user_name'] = $row['organization_name'];
        redirect($b . 'company/dashboard.php');
    }

    setFlash('error', 'بيانات الدخول غير صحيحة.');
    redirect($b . 'login.php');
}

$pageTitle = 'تسجيل الدخول - مسار';
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

        <div class="form-box" style="max-width:400px;margin:2rem auto;">
            <h2>تسجيل الدخول</h2>
            <form method="post" action="">
                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                <div class="form-group">
                    <label for="identifier">البريد الإلكتروني أو اسم المستخدم (للإدارة)</label>
                    <input type="text" name="identifier" id="identifier" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" name="password" id="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">دخول</button>
            </form>
            <p style="margin-top:0.75rem;text-align:center;font-size:0.95rem;">
                <a href="<?php echo $b; ?>forgot_password.php">نسيت كلمة المرور؟</a>
            </p>
            <p style="margin-top:0.75rem;text-align:center;font-size:0.95rem;">
                ليس لديك حساب؟ <a href="<?php echo $b; ?>register.php">سجّل من هنا</a>
            </p>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
