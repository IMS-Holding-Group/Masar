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

$b = app_base();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $form = $_POST['form_type'] ?? '';

    if ($form === 'student') {
        $full_name = clean($_POST['full_name'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $pass2 = $_POST['confirm_password'] ?? '';
        $phone = clean($_POST['phone'] ?? '');
        $university = clean($_POST['university'] ?? '');
        $major = clean($_POST['major'] ?? '');
        $city = clean($_POST['city'] ?? '');
        $year = (int) ($_POST['academic_year'] ?? 0);
        $education_level = clean($_POST['education_level'] ?? '');
        $allowed_levels = ['بكالوريوس', 'دبلوم'];
        if (!in_array($education_level, $allowed_levels, true)) {
            $education_level = '';
        }

        if ($full_name === '' || $email === '' || $pass === '' || $year < 1 || $education_level === '') {
            setFlash('error', 'يرجى تعبئة جميع الحقول المطلوبة بما فيها المرحلة العلمية والسنة الأكاديمية.');
            redirect($b . 'register.php');
        }
        if ($pass !== $pass2) {
            setFlash('error', 'كلمة المرور وتأكيدها غير متطابقتين.');
            redirect($b . 'register.php');
        }
        if ($phone === '' || $university === '' || $major === '' || $city === '') {
            setFlash('error', 'يرجى إكمال الجوال والجامعة والتخصص والمدينة حتى يُحفظ ملفك كاملاً.');
            redirect($b . 'register.php');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'البريد الإلكتروني غير صالح.');
            redirect($b . 'register.php');
        }

        $chk = $pdo->prepare('SELECT student_id FROM STUDENT WHERE email = ? LIMIT 1');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            setFlash('error', 'البريد مسجّل مسبقاً.');
            redirect($b . 'register.php');
        }

        $hash = password_hash($pass, PASSWORD_BCRYPT);
        try {
            $ins = $pdo->prepare('INSERT INTO STUDENT (full_name, email, password, phone, university, major, city, academic_year, education_level, registration_date, status) VALUES (?,?,?,?,?,?,?,?,?,CURDATE(),\'active\')');
            $ins->execute([$full_name, $email, $hash, $phone, $university, $major, $city, $year, $education_level]);
        } catch (PDOException $e) {
            require_once __DIR__ . '/config/support.php';
            $s = masar_support();
            $tel = $s['phone_display'] !== '' ? $s['phone_display'] : ($s['phone'] ?? '');
            $hint = $tel !== '' ? ' تواصل مع الدعم: ' . $tel : '';
            setFlash('error', 'تعذر حفظ بيانات التسجيل في قاعدة البيانات. إن كانت القاعدة قديمة، نفّذ ملف التحديث database/patch_student_education_level.sql ثم أعد المحاولة.' . $hint);
            redirect($b . 'register.php');
        }
        setFlash('success', 'تم إنشاء حساب الطالب بنجاح. يمكنك تسجيل الدخول.');
        redirect($b . 'login.php');
    }

    if ($form === 'company') {
        $name = clean($_POST['organization_name'] ?? '');
        $contact = clean($_POST['contact_person_name'] ?? '');
        $type = clean($_POST['organization_type'] ?? '');
        $sector = clean($_POST['industry_sector'] ?? '');
        $city = clean($_POST['city'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $pass = $_POST['company_password'] ?? '';
        $website = clean($_POST['website'] ?? '');
        $desc = clean($_POST['description'] ?? '');

        if ($name === '' || $email === '' || $pass === '') {
            setFlash('error', 'يرجى تعبئة اسم الجهة والبريد وكلمة المرور.');
            redirect($b . 'register.php');
        }
        $pass2 = $_POST['company_confirm_password'] ?? '';
        if ($pass !== $pass2) {
            setFlash('error', 'كلمة المرور وتأكيدها غير متطابقتين.');
            redirect($b . 'register.php');
        }

        $chk = $pdo->prepare('SELECT organization_id FROM TRAINING_ORGANIZATION WHERE email = ? LIMIT 1');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            setFlash('error', 'البريد مسجّل مسبقاً لجهة أخرى.');
            redirect($b . 'register.php');
        }

        $hash = password_hash($pass, PASSWORD_BCRYPT);
        try {
            $ins = $pdo->prepare('INSERT INTO TRAINING_ORGANIZATION (organization_name, contact_person_name, organization_type, industry_sector, city, phone, email, password, website, description, is_approved, registration_date) VALUES (?,?,?,?,?,?,?,?,?,?,0,CURDATE())');
            $ins->execute([$name, $contact, $type, $sector, $city, $phone, $email, $hash, $website, $desc]);
        } catch (PDOException $e) {
            require_once __DIR__ . '/config/support.php';
            $s = masar_support();
            $tel = $s['phone_display'] !== '' ? $s['phone_display'] : ($s['phone'] ?? '');
            $hint = $tel !== '' ? ' تواصل مع الدعم: ' . $tel : '';
            setFlash('error', 'تعذر حفظ طلب الجهة في قاعدة البيانات.' . $hint);
            redirect($b . 'register.php');
        }
        setFlash('success', 'تم الإرسال للموافقة. ستُراجع الإدارة بيانات الجهة قبل تفعيل الدخول.');
        redirect($b . 'login.php');
    }

    setFlash('error', 'طلب غير صالح.');
    redirect($b . 'register.php');
}

$pageTitle = 'تسجيل حساب - مسار';
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

        <div class="form-box" style="max-width:520px;">
            <h2>إنشاء حساب</h2>
            <div class="tabs">
                <button type="button" class="tab-btn active" data-tab="tab-student">طالب</button>
                <button type="button" class="tab-btn" data-tab="tab-company">جهة تدريب</button>
            </div>

            <div id="tab-student" class="tab-content" style="display:block;">
                <form method="post" action="" id="register-form">
                    <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                    <input type="hidden" name="form_type" value="student">
                    <div class="form-group">
                        <label for="full_name">الاسم الكامل</label>
                        <input type="text" name="full_name" id="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">تأكيد كلمة المرور</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">الجوال</label>
                        <input type="tel" name="phone" id="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="university">الجامعة</label>
                        <input type="text" name="university" id="university" required>
                    </div>
                    <div class="form-group">
                        <label for="major">التخصص</label>
                        <input type="text" name="major" id="major" required>
                    </div>
                    <div class="form-group">
                        <label for="city">المدينة</label>
                        <input type="text" name="city" id="city" required>
                    </div>
                    <div class="form-group">
                        <label for="education_level">المرحلة العلمية</label>
                        <select name="education_level" id="education_level" required>
                            <option value="" disabled selected>اختر المرحلة</option>
                            <option value="بكالوريوس">بكالوريوس</option>
                            <option value="دبلوم">دبلوم</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="academic_year">السنة الأكاديمية (رقم)</label>
                        <input type="number" name="academic_year" id="academic_year" min="1" max="10" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">تسجيل طالب</button>
                </form>
            </div>

            <div id="tab-company" class="tab-content" style="display:none;">
                <form method="post" action="" id="register-form-company">
                    <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                    <input type="hidden" name="form_type" value="company">
                    <div class="form-group">
                        <label for="organization_name">اسم الجهة</label>
                        <input type="text" name="organization_name" id="organization_name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_person_name">اسم مسؤول التواصل</label>
                        <input type="text" name="contact_person_name" id="contact_person_name">
                    </div>
                    <div class="form-group">
                        <label for="organization_type">نوع الجهة</label>
                        <input type="text" name="organization_type" id="organization_type" placeholder="مثال: شركة مساهمة">
                    </div>
                    <div class="form-group">
                        <label for="industry_sector">القطاع</label>
                        <input type="text" name="industry_sector" id="industry_sector">
                    </div>
                    <div class="form-group">
                        <label for="ccity">المدينة</label>
                        <input type="text" name="city" id="ccity">
                    </div>
                    <div class="form-group">
                        <label for="cphone">الهاتف</label>
                        <input type="tel" name="phone" id="cphone">
                    </div>
                    <div class="form-group">
                        <label for="cemail">البريد الإلكتروني</label>
                        <input type="email" name="email" id="cemail" required>
                    </div>
                    <div class="form-group">
                        <label for="company_password">كلمة المرور</label>
                        <input type="password" name="company_password" id="company_password" required>
                    </div>
                    <div class="form-group">
                        <label for="company_confirm_password">تأكيد كلمة المرور</label>
                        <input type="password" name="company_confirm_password" id="company_confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label for="website">الموقع الإلكتروني</label>
                        <input type="url" name="website" id="website" placeholder="https://">
                    </div>
                    <div class="form-group">
                        <label for="description">وصف الجهة</label>
                        <textarea name="description" id="description"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">تسجيل جهة</button>
                </form>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
