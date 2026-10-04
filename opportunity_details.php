<?php
require_once __DIR__ . '/includes/session_bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$b = app_base();
$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    redirect($b . 'opportunities.php');
}

$stmt = $pdo->prepare('SELECT o.*, org.organization_name, org.city, org.organization_type, org.industry_sector, org.website, org.description AS org_desc, org.phone, org.email, org.address, org.contact_person_name
    FROM TRAINING_OPPORTUNITY o
    INNER JOIN TRAINING_ORGANIZATION org ON o.organization_id = org.organization_id
    WHERE o.opportunity_id = ? AND o.is_active = 1 LIMIT 1');
$stmt->execute([$id]);
$opp = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$opp) {
    setFlash('error', 'الفرصة غير موجودة أو غير متاحة.');
    redirect($b . 'opportunities.php');
}

$flash = getFlash();

$revStmt = $pdo->prepare('SELECT r.rating, r.review_text, r.review_date, s.full_name AS student_name
    FROM REVIEW r
    INNER JOIN STUDENT s ON r.student_id = s.student_id
    WHERE r.organization_id = ? ORDER BY r.review_date DESC');
$revStmt->execute([(int) $opp['organization_id']]);
$reviews = $revStmt->fetchAll(PDO::FETCH_ASSOC);

$already = false;
$isStudent = isLoggedIn() && ($_SESSION['user_type'] ?? '') === 'student';
if ($isStudent) {
    $chk = $pdo->prepare('SELECT application_id FROM TRAINING_APPLICATION WHERE student_id = ? AND opportunity_id = ? LIMIT 1');
    $chk->execute([(int) $_SESSION['user_id'], $id]);
    $already = (bool) $chk->fetch();
}

$tm = $opp['training_mode'] ?? 'حضوري';
$orgLogo = org_logo_src($opp['website'] ?? '', (int) $opp['organization_id']);
$heroBg = 'https://images.unsplash.com/photo-1596524430617-4e4a6a65d3d8?auto=format&fit=crop&w=2000&q=80';
$orgPanelBg = 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1600&q=80';

$pageTitle = clean($opp['title']) . ' - مسار';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
</head>
<body class="page-opp-detail">
<?php require_once __DIR__ . '/includes/header.php'; ?>

    <div class="opp-detail-hero">
        <div class="opp-detail-hero__bg" style="background-image:url('<?php echo htmlspecialchars($heroBg, ENT_QUOTES, 'UTF-8'); ?>');" aria-hidden="true"></div>
        <div class="opp-detail-hero__overlay" aria-hidden="true"></div>
        <div class="opp-detail-hero__inner page-wrap">
            <p class="opp-detail-hero__crumb"><a href="<?php echo $b; ?>opportunities.php" class="opp-detail-back">← العودة لقائمة الفرص</a></p>
            <div class="opp-detail-hero__head">
                <div class="opp-detail-hero__logo-wrap">
                    <img src="<?php echo htmlspecialchars($orgLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="opp-detail-hero__logo" width="72" height="72" loading="eager" decoding="async" referrerpolicy="no-referrer">
                </div>
                <div class="opp-detail-hero__titles">
                    <h1 class="opp-detail-hero__title"><?php echo clean($opp['title']); ?></h1>
                    <p class="opp-detail-hero__sub"><?php echo clean($opp['organization_name']); ?> — <?php echo clean($opp['city']); ?> — <?php echo clean($opp['organization_type']); ?></p>
                    <span class="<?php echo training_mode_badge_class($tm); ?>"><?php echo clean($tm); ?></span>
                </div>
            </div>
        </div>
    </div>

    <main class="page-wrap opp-detail-main">
        <?php if (!empty($flash)) : ?>
            <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'error' : 'info'); ?>"><?php echo clean($flash['message']); ?></div>
        <?php endif; ?>

        <div class="detail-org-panel detail-org-panel--frost">
            <div class="detail-org-panel__bg" style="background-image:url('<?php echo htmlspecialchars($orgPanelBg, ENT_QUOTES, 'UTF-8'); ?>');" aria-hidden="true"></div>
            <div class="detail-org-panel__inner">
                <div class="detail-org-panel__head">
                    <img src="<?php echo htmlspecialchars($orgLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="detail-org-panel__logo" width="56" height="56" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                    <h2 class="detail-org-panel__title">بيانات جهة التدريب</h2>
                </div>
                <ul class="detail-org-facts">
                    <li><span class="detail-org-facts__k">القطاع</span><span class="detail-org-facts__v"><?php echo clean($opp['industry_sector'] ?? '—'); ?></span></li>
                    <?php if (!empty($opp['contact_person_name'])) : ?>
                        <li><span class="detail-org-facts__k">مسؤول التواصل</span><span class="detail-org-facts__v"><?php echo clean($opp['contact_person_name']); ?></span></li>
                    <?php endif; ?>
                    <?php if (!empty($opp['address'])) : ?>
                        <li><span class="detail-org-facts__k">العنوان</span><span class="detail-org-facts__v"><?php echo clean($opp['address']); ?></span></li>
                    <?php endif; ?>
                    <li><span class="detail-org-facts__k">الهاتف</span><span class="detail-org-facts__v"><?php echo clean($opp['phone'] ?? '—'); ?></span></li>
                    <li><span class="detail-org-facts__k">البريد</span><span class="detail-org-facts__v"><?php echo clean($opp['email'] ?? '—'); ?></span></li>
                    <?php if (!empty($opp['website'])) : ?>
                        <li><span class="detail-org-facts__k">الموقع</span><span class="detail-org-facts__v"><a href="<?php echo clean($opp['website']); ?>" target="_blank" rel="noopener noreferrer"><?php echo clean($opp['website']); ?></a></span></li>
                    <?php endif; ?>
                </ul>
                <?php if (!empty($opp['org_desc'])) : ?>
                    <p class="detail-org-panel__desc"><?php echo nl2br(clean($opp['org_desc'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <section class="opp-detail-section" aria-labelledby="opp-about">
            <h2 id="opp-about" class="opp-detail-section__title">عن الفرصة</h2>
            <div class="opp-detail-grid">
                <article class="opp-detail-card opp-detail-card--wide">
                    <h3 class="opp-detail-card__label">الوصف</h3>
                    <p class="opp-detail-card__text"><?php echo nl2br(clean($opp['description'])); ?></p>
                </article>
                <article class="opp-detail-card">
                    <h3 class="opp-detail-card__label">التخصص المطلوب</h3>
                    <p class="opp-detail-card__text"><?php echo clean($opp['required_major']); ?></p>
                </article>
                <article class="opp-detail-card">
                    <h3 class="opp-detail-card__label">نمط التدريب</h3>
                    <p class="opp-detail-card__text"><span class="<?php echo training_mode_badge_class($tm); ?>"><?php echo clean($tm); ?></span></p>
                </article>
                <article class="opp-detail-card">
                    <h3 class="opp-detail-card__label">المدة</h3>
                    <p class="opp-detail-card__text opp-detail-card__emph"><?php echo (int) $opp['duration_weeks']; ?> أسبوعاً</p>
                </article>
                <article class="opp-detail-card">
                    <h3 class="opp-detail-card__label">الفترة</h3>
                    <p class="opp-detail-card__text">من <strong><?php echo fDate($opp['start_date']); ?></strong><br>إلى <strong><?php echo fDate($opp['end_date']); ?></strong></p>
                </article>
                <article class="opp-detail-card">
                    <h3 class="opp-detail-card__label">المقاعد المتاحة</h3>
                    <p class="opp-detail-card__text opp-detail-card__emph"><?php echo (int) $opp['available_positions']; ?> مقعداً</p>
                </article>
                <article class="opp-detail-card opp-detail-card--wide">
                    <h3 class="opp-detail-card__label">المتطلبات</h3>
                    <p class="opp-detail-card__text"><?php echo nl2br(clean($opp['requirements'])); ?></p>
                </article>
                <article class="opp-detail-card opp-detail-card--wide opp-detail-card--benefits">
                    <h3 class="opp-detail-card__label">المزايا</h3>
                    <p class="opp-detail-card__text"><?php echo nl2br(clean($opp['benefits'])); ?></p>
                </article>
            </div>
        </section>

        <section class="opp-detail-section opp-detail-section--apply" aria-labelledby="opp-apply-h">
            <div class="opp-apply-panel">
                <div class="opp-apply-panel__text">
                    <h2 id="opp-apply-h" class="opp-detail-section__title" style="margin:0 0 0.5rem;">التقديم على الفرصة</h2>
                    <p class="opp-apply-panel__lead">قدّم بطلبك إلكترونياً مع رسالة تعريفية اختيارية؛ يُرفق سيرتك تلقائياً إن وُجدت في ملفك.</p>
                </div>
                <div class="opp-apply-panel__action">
                    <?php if (!$isStudent) : ?>
                        <a href="<?php echo $b; ?>login.php" class="btn btn-primary btn-lg">تسجيل الدخول للتقديم</a>
                        <a href="<?php echo $b; ?>register.php" class="btn btn-outline btn-lg">إنشاء حساب طالب</a>
                    <?php elseif ($already) : ?>
                        <p class="opp-apply-panel__status opp-apply-panel__status--ok">تم التقديم مسبقاً على هذه الفرصة.</p>
                        <a href="<?php echo $b; ?>student/applications.php" class="btn btn-primary btn-lg">متابعة طلباتي</a>
                    <?php else : ?>
                        <form method="post" action="<?php echo $b; ?>student/apply.php" class="opp-apply-form">
                            <input type="hidden" name="csrf" value="<?php echo clean(csrfToken()); ?>">
                            <input type="hidden" name="opportunity_id" value="<?php echo (int) $id; ?>">
                            <div class="form-group">
                                <label for="cover_letter">رسالة تعريفية (اختياري)</label>
                                <textarea name="cover_letter" id="cover_letter" rows="4" placeholder="يمكنك تركها فارغة والاعتماد على ملف السيرة في ملفك الشخصي"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">تقدّم الآن</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="opp-detail-section" aria-labelledby="opp-reviews">
            <h2 id="opp-reviews" class="opp-detail-section__title">تقييمات عن الجهة</h2>
            <?php if (count($reviews) === 0) : ?>
                <p class="alert alert-info">لا توجد تقييمات منشورة بعد لهذه الجهة.</p>
            <?php else : ?>
                <div class="opp-reviews-grid">
                    <?php foreach ($reviews as $rv) : ?>
                        <article class="opp-review-card">
                            <div class="opp-review-card__top">
                                <strong class="opp-review-card__name"><?php echo clean($rv['student_name']); ?></strong>
                                <span class="opp-review-card__stars" aria-label="التقييم <?php echo (int) $rv['rating']; ?> من 5"><?php echo starsHtml((int) $rv['rating']); ?></span>
                                <time class="opp-review-card__date" datetime="<?php echo htmlspecialchars((string) $rv['review_date'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo fDate($rv['review_date']); ?></time>
                            </div>
                            <p class="opp-review-card__body"><?php echo nl2br(clean($rv['review_text'])); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
