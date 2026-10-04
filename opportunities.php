<?php
require_once __DIR__ . '/includes/session_bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$b = app_base();
$keyword = clean($_GET['keyword'] ?? '');
$major = clean($_GET['major'] ?? '');
$city = clean($_GET['city'] ?? '');
$org_type = clean($_GET['org_type'] ?? '');
$delivery_mode = clean($_GET['delivery_mode'] ?? '');
$max_weeks = (int) ($_GET['max_weeks'] ?? 0);

$sql = 'SELECT o.opportunity_id, o.organization_id, o.title, o.required_major, o.duration_weeks, o.start_date, o.available_positions,
        o.training_mode,
        org.organization_name, org.city, org.organization_type, org.website
        FROM TRAINING_OPPORTUNITY o
        INNER JOIN TRAINING_ORGANIZATION org ON o.organization_id = org.organization_id
        WHERE o.is_active = 1';
$params = [];

if ($keyword !== '') {
    $sql .= ' AND (o.title LIKE ? OR o.description LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
}
if ($major !== '') {
    $sql .= ' AND o.required_major LIKE ?';
    $params[] = '%' . $major . '%';
}
if ($city !== '') {
    $sql .= ' AND org.city LIKE ?';
    $params[] = '%' . $city . '%';
}
if ($org_type !== '') {
    $sql .= ' AND org.organization_type LIKE ?';
    $params[] = '%' . $org_type . '%';
}
if ($delivery_mode !== '') {
    $sql .= ' AND o.training_mode = ?';
    $params[] = $delivery_mode;
}
if ($max_weeks > 0) {
    $sql .= ' AND o.duration_weeks <= ?';
    $params[] = $max_weeks;
}
$sql .= ' ORDER BY o.posted_date DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'فرص التدريب - مسار';
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
        <h1 class="page-title">فرص التدريب</h1>

        <form class="filters-bar" method="get" action="">
            <div class="form-group">
                <label for="keyword">كلمة مفتاحية</label>
                <input type="text" name="keyword" id="keyword" value="<?php echo clean($keyword); ?>" placeholder="عنوان أو وصف">
            </div>
            <div class="form-group">
                <label for="major">التخصص</label>
                <input type="text" name="major" id="major" value="<?php echo clean($major); ?>">
            </div>
            <div class="form-group">
                <label for="city">المدينة</label>
                <input type="text" name="city" id="city" value="<?php echo clean($city); ?>">
            </div>
            <div class="form-group">
                <label for="delivery_mode">نمط التدريب</label>
                <select name="delivery_mode" id="delivery_mode">
                    <option value="">الكل</option>
                    <option value="حضوري" <?php echo $delivery_mode === 'حضوري' ? 'selected' : ''; ?>>حضوري</option>
                    <option value="عن بعد" <?php echo $delivery_mode === 'عن بعد' ? 'selected' : ''; ?>>عن بعد</option>
                    <option value="هجين" <?php echo $delivery_mode === 'هجين' ? 'selected' : ''; ?>>هجين</option>
                </select>
            </div>
            <div class="form-group">
                <label for="org_type">نوع الجهة</label>
                <input type="text" name="org_type" id="org_type" value="<?php echo clean($org_type); ?>" placeholder="مثال: شركة مساهمة">
            </div>
            <div class="form-group">
                <label for="max_weeks">أقصى مدة (أسابيع)</label>
                <input type="number" name="max_weeks" id="max_weeks" min="0" value="<?php echo $max_weeks > 0 ? (int) $max_weeks : ''; ?>" placeholder="الكل">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">بحث</button>
            </div>
        </form>

        <?php if (count($rows) === 0) : ?>
            <p class="alert alert-info">لا توجد فرص مطابقة حالياً.</p>
        <?php else : ?>
            <?php foreach ($rows as $r) : ?>
                <?php
                $rLogo = org_logo_src($r['website'] ?? '', (int) $r['organization_id']);
                $rMode = $r['training_mode'] ?? 'حضوري';
                ?>
                <div class="opp-item opp-item--card">
                    <div class="opp-item__brand">
                        <img src="<?php echo htmlspecialchars($rLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="opp-item__logo" width="52" height="52" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                    </div>
                    <div class="opp-item__body">
                        <div class="opp-title"><?php echo clean($r['title']); ?></div>
                        <div class="opp-company"><?php echo clean($r['organization_name']); ?> — <?php echo clean($r['city']); ?></div>
                        <div class="opp-meta">
                            <span>التخصص: <?php echo clean($r['required_major']); ?></span>
                            <span class="<?php echo training_mode_badge_class($rMode); ?>"><?php echo clean($rMode); ?></span>
                            <span>المدة: <?php echo (int) $r['duration_weeks']; ?> أسبوعاً</span>
                            <span>المقاعد: <?php echo (int) $r['available_positions']; ?></span>
                            <span>البداية: <?php echo fDate($r['start_date']); ?></span>
                        </div>
                    </div>
                    <div class="opp-item__cta">
                        <a class="btn btn-primary btn-sm" href="<?php echo $b; ?>opportunity_details.php?id=<?php echo (int) $r['opportunity_id']; ?>">التفاصيل والتقديم</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
