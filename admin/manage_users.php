<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';

$b = app_base();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $student_id = (int) ($_POST['student_id'] ?? 0);
    $status = clean($_POST['status'] ?? '');
    if ($student_id < 1 || !in_array($status, ['active', 'inactive'], true)) {
        setFlash('error', 'بيانات غير صالحة.');
        redirect($b . 'admin/manage_users.php');
    }
    $u = $pdo->prepare('UPDATE STUDENT SET status = ? WHERE student_id = ?');
    $u->execute([$status, $student_id]);
    setFlash('success', 'تم تحديث حالة الطالب.');
    redirect($b . 'admin/manage_users.php');
}

$rows = $pdo->query('SELECT student_id, full_name, email, university, major, registration_date, status FROM STUDENT ORDER BY registration_date DESC')->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlash();
$pageTitle = 'إدارة الطلاب - مسار';
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

        <h1 class="page-title">إدارة الطلاب</h1>
        <p><a href="<?php echo $b; ?>admin/dashboard.php" class="btn btn-secondary btn-sm">العودة للوحة</a></p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد</th>
                        <th>الجامعة</th>
                        <th>التخصص</th>
                        <th>الحالة</th>
                        <th>تغيير الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r) : ?>
                        <tr>
                            <td><?php echo clean($r['full_name']); ?></td>
                            <td><?php echo clean($r['email']); ?></td>
                            <td><?php echo clean($r['university']); ?></td>
                            <td><?php echo clean($r['major']); ?></td>
                            <td><span class="badge <?php echo strtolower((string) $r['status']) === 'active' ? 'badge-active' : 'badge-rejected'; ?>"><?php echo clean($r['status']); ?></span></td>
                            <td>
                                <form method="post" style="display:inline-flex;gap:0.35rem;align-items:center;flex-wrap:wrap;" action="">
                                    <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                                    <input type="hidden" name="student_id" value="<?php echo (int) $r['student_id']; ?>">
                                    <select name="status" style="padding:0.35rem;border-radius:8px;border:2px solid var(--gray-light);">
                                        <option value="active" <?php echo strtolower((string) $r['status']) === 'active' ? 'selected' : ''; ?>>active</option>
                                        <option value="inactive" <?php echo strtolower((string) $r['status']) === 'inactive' ? 'selected' : ''; ?>>inactive</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">حفظ</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
