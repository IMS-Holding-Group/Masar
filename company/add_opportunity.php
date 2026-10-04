<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('company');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$oid = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $title = clean($_POST['title'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $required_major = clean($_POST['required_major'] ?? '');
    $training_mode = clean($_POST['training_mode'] ?? 'حضوري');
    $allowedModes = ['حضوري', 'عن بعد', 'هجين'];
    if (!in_array($training_mode, $allowedModes, true)) {
        $training_mode = 'حضوري';
    }
    $duration_weeks = (int) ($_POST['duration_weeks'] ?? 0);
    $start_date = clean($_POST['start_date'] ?? '');
    $end_date = clean($_POST['end_date'] ?? '');
    $available_positions = (int) ($_POST['available_positions'] ?? 0);
    $requirements = clean($_POST['requirements'] ?? '');
    $benefits = clean($_POST['benefits'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '' || $duration_weeks < 1 || $start_date === '' || $end_date === '' || $available_positions < 1) {
        setFlash('error', 'يرجى تعبئة الحقول الأساسية بشكل صحيح.');
        redirect($b . 'company/add_opportunity.php');
    }

    $ins = $pdo->prepare('INSERT INTO TRAINING_OPPORTUNITY (organization_id, title, description, required_major, training_mode, duration_weeks, start_date, end_date, available_positions, requirements, benefits, is_active, posted_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,CURDATE())');
    $ins->execute([$oid, $title, $description, $required_major, $training_mode, $duration_weeks, $start_date, $end_date, $available_positions, $requirements, $benefits, $is_active]);
    setFlash('success', 'تمت إضافة الفرصة.');
    redirect($b . 'company/manage_opportunities.php');
}

$flash = getFlash();
$pageTitle = 'إضافة فرصة - مسار';
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

        <h1 class="page-title">إضافة فرصة تدريب</h1>
        <p><a href="<?php echo $b; ?>company/dashboard.php" class="btn btn-secondary btn-sm">العودة</a></p>

        <div class="form-box" style="max-width:640px;">
            <form method="post" action="">
                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                <div class="form-group">
                    <label for="title">عنوان الفرصة</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <div class="form-group">
                    <label for="description">الوصف</label>
                    <textarea name="description" id="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="required_major">التخصص المطلوب</label>
                    <input type="text" name="required_major" id="required_major">
                </div>
                <div class="form-group">
                    <label for="training_mode">نمط التدريب</label>
                    <select name="training_mode" id="training_mode">
                        <option value="حضوري">حضوري</option>
                        <option value="عن بعد">عن بعد</option>
                        <option value="هجين">هجين</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="duration_weeks">المدة (أسابيع)</label>
                    <input type="number" name="duration_weeks" id="duration_weeks" min="1" required>
                </div>
                <div class="form-group">
                    <label for="start_date">تاريخ البدء</label>
                    <input type="date" name="start_date" id="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">تاريخ الانتهاء</label>
                    <input type="date" name="end_date" id="end_date" required>
                </div>
                <div class="form-group">
                    <label for="available_positions">عدد المقاعد</label>
                    <input type="number" name="available_positions" id="available_positions" min="1" required>
                </div>
                <div class="form-group">
                    <label for="requirements">المتطلبات</label>
                    <textarea name="requirements" id="requirements"></textarea>
                </div>
                <div class="form-group">
                    <label for="benefits">المزايا</label>
                    <textarea name="benefits" id="benefits"></textarea>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" value="1" checked> فرصة نشطة (ظاهرة للطلاب)</label>
                </div>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
