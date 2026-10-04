<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('company');
require_once __DIR__ . '/../config/database.php';

$b = app_base();
$oid = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    redirect($b . 'company/manage_opportunities.php');
}

$stmt = $pdo->prepare('SELECT * FROM TRAINING_OPPORTUNITY WHERE opportunity_id = ? AND organization_id = ? LIMIT 1');
$stmt->execute([$id, $oid]);
$opp = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$opp) {
    setFlash('error', 'الفرصة غير موجودة.');
    redirect($b . 'company/manage_opportunities.php');
}

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
        redirect($b . 'company/edit_opportunity.php?id=' . $id);
    }

    $upd = $pdo->prepare('UPDATE TRAINING_OPPORTUNITY SET title = ?, description = ?, required_major = ?, training_mode = ?, duration_weeks = ?, start_date = ?, end_date = ?, available_positions = ?, requirements = ?, benefits = ?, is_active = ? WHERE opportunity_id = ? AND organization_id = ?');
    $upd->execute([$title, $description, $required_major, $training_mode, $duration_weeks, $start_date, $end_date, $available_positions, $requirements, $benefits, $is_active, $id, $oid]);
    setFlash('success', 'تم تحديث الفرصة.');
    redirect($b . 'company/manage_opportunities.php');
}

$flash = getFlash();
$pageTitle = 'تعديل فرصة - مسار';
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

        <h1 class="page-title">تعديل فرصة</h1>
        <p><a href="<?php echo $b; ?>company/manage_opportunities.php" class="btn btn-secondary btn-sm">العودة</a></p>

        <div class="form-box" style="max-width:640px;">
            <form method="post" action="">
                <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                <div class="form-group">
                    <label for="title">عنوان الفرصة</label>
                    <input type="text" name="title" id="title" required value="<?php echo clean($opp['title']); ?>">
                </div>
                <div class="form-group">
                    <label for="description">الوصف</label>
                    <textarea name="description" id="description"><?php echo clean($opp['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="required_major">التخصص المطلوب</label>
                    <input type="text" name="required_major" id="required_major" value="<?php echo clean($opp['required_major']); ?>">
                </div>
                <?php $tm = $opp['training_mode'] ?? 'حضوري'; ?>
                <div class="form-group">
                    <label for="training_mode">نمط التدريب</label>
                    <select name="training_mode" id="training_mode">
                        <option value="حضوري" <?php echo $tm === 'حضوري' ? 'selected' : ''; ?>>حضوري</option>
                        <option value="عن بعد" <?php echo $tm === 'عن بعد' ? 'selected' : ''; ?>>عن بعد</option>
                        <option value="هجين" <?php echo $tm === 'هجين' ? 'selected' : ''; ?>>هجين</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="duration_weeks">المدة (أسابيع)</label>
                    <input type="number" name="duration_weeks" id="duration_weeks" min="1" required value="<?php echo (int) $opp['duration_weeks']; ?>">
                </div>
                <div class="form-group">
                    <label for="start_date">تاريخ البدء</label>
                    <input type="date" name="start_date" id="start_date" required value="<?php echo clean(substr((string) $opp['start_date'], 0, 10)); ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">تاريخ الانتهاء</label>
                    <input type="date" name="end_date" id="end_date" required value="<?php echo clean(substr((string) $opp['end_date'], 0, 10)); ?>">
                </div>
                <div class="form-group">
                    <label for="available_positions">عدد المقاعد</label>
                    <input type="number" name="available_positions" id="available_positions" min="1" required value="<?php echo (int) $opp['available_positions']; ?>">
                </div>
                <div class="form-group">
                    <label for="requirements">المتطلبات</label>
                    <textarea name="requirements" id="requirements"><?php echo clean($opp['requirements']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="benefits">المزايا</label>
                    <textarea name="benefits" id="benefits"><?php echo clean($opp['benefits']); ?></textarea>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" value="1" <?php echo (int) $opp['is_active'] ? 'checked' : ''; ?>> فرصة نشطة</label>
                </div>
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
