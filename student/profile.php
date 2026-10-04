<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$sid = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM STUDENT WHERE student_id = ? LIMIT 1');
$stmt->execute([$sid]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    redirect($b . 'index.php');
}

$uploadProfiles = __DIR__ . '/../assets/uploads/profiles';
$uploadCv = __DIR__ . '/../assets/uploads/cv';
foreach ([$uploadProfiles, $uploadCv] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    $full_name = clean($_POST['full_name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $university = clean($_POST['university'] ?? '');
    $major = clean($_POST['major'] ?? '');
    $gpa = clean($_POST['gpa'] ?? '');
    $skills = clean($_POST['skills'] ?? '');
    $linkedin_raw = $_POST['linkedin_url'] ?? '';
    $github_raw = $_POST['github_url'] ?? '';
    $city = clean($_POST['city'] ?? '');
    $academic_year = (int) ($_POST['academic_year'] ?? 0);
    $education_level = clean($_POST['education_level'] ?? '');
    $allowed_levels = ['بكالوريوس', 'دبلوم'];
    if (!in_array($education_level, $allowed_levels, true)) {
        $education_level = '';
    }

    if ($full_name === '' || $academic_year < 1 || $education_level === '') {
        setFlash('error', 'الاسم والسنة الأكاديمية والمرحلة العلمية مطلوبة.');
        redirect($b . 'student/profile.php');
    }

    $linkedin = optionalUrl($linkedin_raw);
    $github = optionalUrl($github_raw);
    if ($linkedin === null) {
        setFlash('error', 'رابط لينكدإن غير صالح. اترك الحقل فارغاً أو أدخل رابطاً كاملاً يبدأ بـ https://');
        redirect($b . 'student/profile.php');
    }
    if ($github === null) {
        setFlash('error', 'رابط جيثب غير صالح. اترك الحقل فارغاً أو أدخل رابطاً كاملاً.');
        redirect($b . 'student/profile.php');
    }

    $profile_image = $student['profile_image'] ?? null;
    $cv_path = $student['cv_path'] ?? null;

    if (!empty($_FILES['profile_picture']['name']) && (int) $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
            setFlash('error', 'صورة الملف الشخصي يجب ألا تتجاوز 2 ميجابايت.');
            redirect($b . 'student/profile.php');
        }
        $tmp = $_FILES['profile_picture']['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $tmp) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true) || !in_array($mime, ['image/jpeg', 'image/png'], true)) {
            setFlash('error', 'صورة الملف الشخصي يجب أن تكون JPG أو PNG.');
            redirect($b . 'student/profile.php');
        }
        $newName = 's' . $sid . '_' . time() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $dest = rtrim($uploadProfiles, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newName;
        if (!move_uploaded_file($tmp, $dest)) {
            setFlash('error', 'تعذر حفظ الصورة.');
            redirect($b . 'student/profile.php');
        }
        $profile_image = $newName;
    }

    if (!empty($_FILES['cv_file']['name']) && (int) $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['cv_file']['size'] > 5 * 1024 * 1024) {
            setFlash('error', 'ملف السيرة يجب ألا يتجاوز 5 ميجابايت.');
            redirect($b . 'student/profile.php');
        }
        $tmp = $_FILES['cv_file']['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $tmp) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        $ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf' || $mime !== 'application/pdf') {
            setFlash('error', 'ملف السيرة يجب أن يكون PDF.');
            redirect($b . 'student/profile.php');
        }
        $newName = 'cv' . $sid . '_' . time() . '.pdf';
        $dest = rtrim($uploadCv, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newName;
        if (!move_uploaded_file($tmp, $dest)) {
            setFlash('error', 'تعذر حفظ ملف السيرة.');
            redirect($b . 'student/profile.php');
        }
        $cv_path = $newName;
    }

    try {
        $upd = $pdo->prepare('UPDATE STUDENT SET full_name = ?, phone = ?, university = ?, major = ?, gpa = ?, skills = ?, linkedin_url = ?, github_url = ?, city = ?, academic_year = ?, education_level = ?, profile_image = ?, cv_path = ? WHERE student_id = ?');
        $upd->execute([$full_name, $phone, $university, $major, $gpa, $skills, $linkedin, $github, $city, $academic_year, $education_level, $profile_image, $cv_path, $sid]);
    } catch (PDOException $e) {
        setFlash('error', 'تعذر حفظ التعديلات. إن ظهرت المشكلة بعد تحديث النظام، نفّذ ملف database/patch_student_education_level.sql على قاعدة البيانات.');
        redirect($b . 'student/profile.php');
    }

    $_SESSION['user_name'] = $full_name;
    setFlash('success', 'تم تحديث الملف الشخصي.');
    redirect($b . 'student/profile.php');
}

$flash = getFlash();
$picUrl = '';
if (!empty($student['profile_image'])) {
    $picUrl = $b . 'assets/uploads/profiles/' . rawurlencode(basename((string) $student['profile_image']));
}
// صورة افتراضية للمعاينة فقط — لا تستخدم شعار الموقع حتى لا يُخلط بينه وبين الهوية البصرية
$avatarPlaceholder = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><circle cx="40" cy="40" r="40" fill="#F5F5F5"/><circle cx="40" cy="32" r="12" fill="#2D5D8A" opacity=".35"/><ellipse cx="40" cy="62" rx="22" ry="14" fill="#2D5D8A" opacity=".25"/></svg>'
);
$pageTitle = 'الملف الشخصي - مسار';
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

        <h1 class="page-title">الملف الشخصي</h1>
        <p class="profile-page-lead">من هنا تعدّل بياناتك الأكاديمية والتواصل، وترفع <strong>صورة شخصية</strong> و<strong>سيرة ذاتية PDF</strong>، وتربط <strong>حساب لينكدإن</strong> و<strong>جيثب</strong>. البريد الإلكتروني ثابت لأسباب أمنية.</p>
        <p><a href="<?php echo $b; ?>student/dashboard.php" class="btn btn-secondary btn-sm">العودة للوحة</a></p>

        <div class="form-box profile-form-box">
            <form method="post" enctype="multipart/form-data" action="">
                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">

                <div class="form-group" style="text-align:center;">
                    <img src="<?php echo $picUrl !== '' ? $picUrl : $avatarPlaceholder; ?>" alt="" class="profile-pic" id="pic-preview">
                </div>
                <div class="form-group">
                    <label for="profile_picture">صورة شخصية (JPG/PNG، حتى 2 ميجابايت)</label>
                    <input type="file" name="profile_picture" id="profile_picture" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                </div>

                <div class="form-group">
                    <label for="full_name">الاسم الكامل</label>
                    <input type="text" name="full_name" id="full_name" required value="<?php echo clean($student['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">البريد (لا يمكن تغييره)</label>
                    <input type="email" id="email" value="<?php echo clean($student['email'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="phone">الجوال</label>
                    <input type="tel" name="phone" id="phone" value="<?php echo clean($student['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="university">الجامعة</label>
                    <input type="text" name="university" id="university" value="<?php echo clean($student['university'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="major">التخصص</label>
                    <input type="text" name="major" id="major" value="<?php echo clean($student['major'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="gpa">المعدل التراكمي (مثال: 4.50 أو 3.75/5)</label>
                    <input type="text" name="gpa" id="gpa" value="<?php echo clean($student['gpa'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="skills">المهارات</label>
                    <textarea name="skills" id="skills" placeholder="مفصولة بفواصل أو أسطر"><?php echo clean($student['skills'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="linkedin_url">حساب لينكدإن (اختياري)</label>
                    <input type="url" name="linkedin_url" id="linkedin_url" value="<?php echo clean($student['linkedin_url'] ?? ''); ?>" placeholder="https://www.linkedin.com/in/اسمك" inputmode="url" autocomplete="url">
                    <small class="form-hint">اترك الحقل فارغاً إن لم يكن لديك حساب؛ إن أدخلت رابطاً يجب أن يكون كاملاً وصالحاً.</small>
                </div>
                <div class="form-group">
                    <label for="github_url">حساب جيثب (اختياري)</label>
                    <input type="url" name="github_url" id="github_url" value="<?php echo clean($student['github_url'] ?? ''); ?>" placeholder="https://github.com/اسم_المستخدم" inputmode="url" autocomplete="url">
                    <small class="form-hint">اختياري — لعرض مشاريعك البرمجية للجهات عند المراجعة.</small>
                </div>
                <div class="form-group">
                    <label for="city">المدينة</label>
                    <input type="text" name="city" id="city" value="<?php echo clean($student['city'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="education_level">المرحلة العلمية</label>
                    <select name="education_level" id="education_level" required>
                        <?php $el = (string) ($student['education_level'] ?? 'بكالوريوس'); ?>
                        <option value="بكالوريوس"<?php echo $el === 'بكالوريوس' ? ' selected' : ''; ?>>بكالوريوس</option>
                        <option value="دبلوم"<?php echo $el === 'دبلوم' ? ' selected' : ''; ?>>دبلوم</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="academic_year">السنة الأكاديمية</label>
                    <input type="number" name="academic_year" id="academic_year" min="1" max="10" required value="<?php echo (int) ($student['academic_year'] ?? 1); ?>">
                </div>

                <div class="form-group">
                    <label for="cv_file">السيرة الذاتية (PDF، حتى 5 ميجابايت)</label>
                    <input type="file" name="cv_file" id="cv_file" accept=".pdf,application/pdf">
                    <?php if (!empty($student['cv_path'])) : ?>
                        <p style="margin-top:0.5rem;font-size:0.9rem;">الملف الحالي: <a href="<?php echo $b; ?>assets/uploads/cv/<?php echo rawurlencode(basename((string) $student['cv_path'])); ?>" target="_blank" rel="noopener">تحميل</a></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">حفظ التغييرات</button>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
