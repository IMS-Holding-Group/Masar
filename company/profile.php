<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('company');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$oid = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM TRAINING_ORGANIZATION WHERE organization_id = ? LIMIT 1');
$stmt->execute([$oid]);
$org = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$org) {
    redirect($b . 'index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $organization_name = clean($_POST['organization_name'] ?? '');
    $contact_person_name = clean($_POST['contact_person_name'] ?? '');
    $organization_type = clean($_POST['organization_type'] ?? '');
    $industry_sector = clean($_POST['industry_sector'] ?? '');
    $city = clean($_POST['city'] ?? '');
    $address = clean($_POST['address'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $website = clean($_POST['website'] ?? '');
    $description = clean($_POST['description'] ?? '');

    if ($organization_name === '') {
        setFlash('error', 'اسم الجهة مطلوب.');
        redirect($b . 'company/profile.php');
    }
    $web = $website;
    if ($web !== '' && !preg_match('#^https?://#i', $web)) {
        $web = 'https://' . ltrim($web, '/');
    }
    if ($web !== '' && filter_var($web, FILTER_VALIDATE_URL) === false) {
        setFlash('error', 'رابط الموقع غير صالح.');
        redirect($b . 'company/profile.php');
    }

    $upd = $pdo->prepare('UPDATE TRAINING_ORGANIZATION SET organization_name = ?, contact_person_name = ?, organization_type = ?, industry_sector = ?, city = ?, address = ?, phone = ?, website = ?, description = ? WHERE organization_id = ?');
    $upd->execute([$organization_name, $contact_person_name, $organization_type, $industry_sector, $city, $address, $phone, $web, $description, $oid]);

    $_SESSION['user_name'] = $organization_name;
    setFlash('success', 'تم تحديث الملف التعريفي للجهة.');
    redirect($b . 'company/profile.php');
}

$flash = getFlash();
$pageTitle = 'الملف التعريفي - مسار';
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

        <h1 class="page-title">الملف التعريفي لجهة التدريب</h1>
        <p class="profile-page-lead">صفحة واحدة لتحديث <strong>اسم الجهة</strong>، <strong>مسؤول التواصل</strong>، <strong>القطاع والمدينة</strong>، <strong>الهاتف والموقع</strong>، و<strong>وصف الجهة</strong> الظاهر للطلاب. البريد المستخدم للدخول يبقى كما هو.</p>
        <p><a href="<?php echo $b; ?>company/dashboard.php" class="btn btn-secondary btn-sm">العودة للوحة</a></p>

        <div class="form-box profile-form-box">
            <p style="color:var(--gray-text);font-size:0.95rem;margin-bottom:1rem;">البريد المستخدم للدخول: <strong><?php echo clean($org['email'] ?? ''); ?></strong> (لتغييره تواصل مع الإدارة)</p>
            <form method="post" action="">
                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                <div class="form-group">
                    <label for="organization_name">اسم الجهة</label>
                    <input type="text" name="organization_name" id="organization_name" required value="<?php echo clean($org['organization_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="contact_person_name">اسم مسؤول التواصل</label>
                    <input type="text" name="contact_person_name" id="contact_person_name" value="<?php echo clean($org['contact_person_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="organization_type">نوع الجهة</label>
                    <input type="text" name="organization_type" id="organization_type" value="<?php echo clean($org['organization_type'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="industry_sector">القطاع / المجال</label>
                    <input type="text" name="industry_sector" id="industry_sector" value="<?php echo clean($org['industry_sector'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="city">المدينة</label>
                    <input type="text" name="city" id="city" value="<?php echo clean($org['city'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address">العنوان</label>
                    <input type="text" name="address" id="address" value="<?php echo clean($org['address'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">الهاتف</label>
                    <input type="tel" name="phone" id="phone" value="<?php echo clean($org['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="website">الموقع الإلكتروني</label>
                    <input type="url" name="website" id="website" value="<?php echo clean($org['website'] ?? ''); ?>" placeholder="https://">
                </div>
                <div class="form-group">
                    <label for="description">وصف الجهة</label>
                    <textarea name="description" id="description"><?php echo clean($org['description'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">حفظ</button>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
