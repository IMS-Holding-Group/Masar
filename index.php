<?php
require_once __DIR__ . '/includes/session_bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$b = app_base();

$stat_students = (int) $pdo->query('SELECT COUNT(*) FROM STUDENT')->fetchColumn();
$stat_orgs = (int) $pdo->query('SELECT COUNT(*) FROM TRAINING_ORGANIZATION WHERE is_approved = 1')->fetchColumn();
$stat_opps = (int) $pdo->query('SELECT COUNT(*) FROM TRAINING_OPPORTUNITY WHERE is_active = 1')->fetchColumn();
$stat_apps = (int) $pdo->query('SELECT COUNT(*) FROM TRAINING_APPLICATION')->fetchColumn();
$stat_reviews = (int) $pdo->query('SELECT COUNT(*) FROM REVIEW')->fetchColumn();

// خلفية الهيرو: صورة تعليم/تدريب (مصدر خارجي)
$imgHero = 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1920&q=80&auto=format&fit=crop';
$imgEdu = 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1600&q=75&auto=format&fit=crop';
$imgTeam = 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1600&q=75&auto=format&fit=crop';
$iconStudents = 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=200&q=80&auto=format&fit=crop';
$iconOrgs = 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=200&q=80&auto=format&fit=crop';
$iconBriefcase = 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=200&q=80&auto=format&fit=crop';
$iconDocs = 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=200&q=80&auto=format&fit=crop';
$iconTeam = 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=200&q=80&auto=format&fit=crop';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مسار - منصة التدريب التعاوني</title>
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
</head>
<body class="page-home">
<?php require_once __DIR__ . '/includes/header.php'; ?>

    <main>
        <section class="home-hero" aria-labelledby="hero-heading">
            <div class="home-hero-photo" style="background-image:url('<?php echo htmlspecialchars($imgHero, ENT_QUOTES, 'UTF-8'); ?>');" aria-hidden="true"></div>
            <div class="home-hero-overlay" aria-hidden="true"></div>
            <div class="home-hero-inner section-inner">
                <div class="home-hero-visual reveal-on-scroll" aria-hidden="true">
                    <svg class="home-hero-svg" viewBox="0 0 200 120" xmlns="http://www.w3.org/2000/svg" role="img">
                        <defs>
                            <linearGradient id="g1" x1="0%" y1="100%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#54A346"/>
                                <stop offset="100%" style="stop-color:#2D5D8A"/>
                            </linearGradient>
                        </defs>
                        <path fill="none" stroke="url(#g1)" stroke-width="4" stroke-linecap="round" d="M20 95 Q55 40 95 55 T170 25" opacity="0.9"/>
                        <path fill="none" stroke="url(#g1)" stroke-width="3" stroke-linecap="round" d="M35 88 Q70 50 110 62 T185 38" opacity="0.55"/>
                        <circle cx="28" cy="92" r="7" fill="#54A346"/>
                    </svg>
                </div>
                <div class="home-hero-copy">
                    <p class="home-hero-kicker">منصة مسار — التدريب التعاوني بمعايير أكاديمية ورقمية رفيعة</p>
                    <h1 id="hero-heading" class="home-hero-title">بوابتكم الموحّدة لربط الطلاب بفرص التدريب المعتمدة</h1>
                    <p class="home-hero-desc">تجمع «مسار» بين وضوح المعلومات، وسهولة الإجراءات، وشفافية التقييم، لتدعم جاهزية الخريجين لسوق العمل وتعزز شراكات الجامعات مع القطاعين العام والخاص.</p>
                    <div class="home-hero-actions">
                        <a href="<?php echo $b; ?>opportunities.php" class="btn btn-primary home-btn-glow">استكشف الفرص</a>
                        <a href="<?php echo $b; ?>register.php" class="btn btn-secondary home-btn-outline">إنشاء حساب</a>
                        <a href="<?php echo $b; ?>login.php" class="btn btn-outline home-btn-ghost">تسجيل الدخول</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="home-stats section-inner" aria-label="إحصائيات المنصة">
            <div class="home-stats-grid">
                <div class="home-stat-card reveal-on-scroll">
                    <span class="home-stat-icon home-stat-icon--photo" aria-hidden="true">
                        <img src="<?php echo htmlspecialchars($iconStudents, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="home-stat-icon__img" width="56" height="56" loading="lazy" decoding="async">
                    </span>
                    <span class="home-stat-num">+<?php echo clean((string) $stat_students); ?></span>
                    <span class="home-stat-label">طالب مسجّل</span>
                </div>
                <div class="home-stat-card reveal-on-scroll">
                    <span class="home-stat-icon home-stat-icon--photo" aria-hidden="true">
                        <img src="<?php echo htmlspecialchars($iconOrgs, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="home-stat-icon__img" width="56" height="56" loading="lazy" decoding="async">
                    </span>
                    <span class="home-stat-num">+<?php echo clean((string) $stat_orgs); ?></span>
                    <span class="home-stat-label">جهة تدريب معتمدة</span>
                </div>
                <div class="home-stat-card reveal-on-scroll">
                    <span class="home-stat-icon home-stat-icon--photo" aria-hidden="true">
                        <img src="<?php echo htmlspecialchars($iconBriefcase, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="home-stat-icon__img" width="56" height="56" loading="lazy" decoding="async">
                    </span>
                    <span class="home-stat-num">+<?php echo clean((string) $stat_opps); ?></span>
                    <span class="home-stat-label">فرصة نشطة</span>
                </div>
                <div class="home-stat-card reveal-on-scroll">
                    <span class="home-stat-icon home-stat-icon--photo" aria-hidden="true">
                        <img src="<?php echo htmlspecialchars($iconDocs, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="home-stat-icon__img" width="56" height="56" loading="lazy" decoding="async">
                    </span>
                    <span class="home-stat-num">+<?php echo clean((string) $stat_apps); ?></span>
                    <span class="home-stat-label">طلب تقديم</span>
                </div>
                <div class="home-stat-card reveal-on-scroll">
                    <span class="home-stat-icon home-stat-icon--photo" aria-hidden="true">
                        <img src="<?php echo htmlspecialchars($iconTeam, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="home-stat-icon__img" width="56" height="56" loading="lazy" decoding="async">
                    </span>
                    <span class="home-stat-num">+<?php echo clean((string) $stat_reviews); ?></span>
                    <span class="home-stat-label">تقييم منشور</span>
                </div>
            </div>
        </section>

        <section class="home-section">
            <div class="section-inner">
                <div class="home-frost-panel reveal-on-scroll">
                    <div class="home-frost-panel__bg" style="background-image:url('<?php echo htmlspecialchars($imgEdu, ENT_QUOTES, 'UTF-8'); ?>');" aria-hidden="true"></div>
                    <div class="home-frost-panel__content">
                        <h2 class="home-frost-title"><span class="home-inline-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/></svg></span> منصة مسار — الأنظمة والخصائص الكاملة</h2>
                        <p class="home-frost-lead">منظومة ويب متكاملة للتدريب التعاوني: تسجيل آمن، فرص منظمة، تقديم موحّد، تقييمات معتمدة، ولوحات تحكم لكل طرف — بواجهة عربية كاملة الاتجاه وتصميم متجاوب.</p>
                    </div>
                </div>

                <h2 class="section-title home-section-title" style="margin-top:2.5rem;">المستخدمون (ثلاثة أطراف)</h2>
                <div class="table-wrap reveal-on-scroll">
                    <table class="home-spec-table">
                        <thead>
                            <tr>
                                <th>الطرف</th>
                                <th>الدور</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>الطالب</strong></td>
                                <td>البحث عن فرص التدريب، التقديم عليها، متابعة حالة الطلبات، وكتابة تقييمات تجريبية موثوقة بعد إتمام التدريب.</td>
                            </tr>
                            <tr>
                                <td><strong>جهة التدريب</strong></td>
                                <td>نشر الفرص بكامل التفاصيل، مراجعة الطلبات، واتخاذ قرار القبول أو الرفض لكل متقدم.</td>
                            </tr>
                            <tr>
                                <td><strong>مشرف المنصة</strong></td>
                                <td>الموافقة على الجهات الجديدة قبل تفعيل دخولها، إدارة حسابات المستخدمين، ومتابعة مؤشرات الأداء والإحصاءات العامة للمنصة.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="home-section home-section--muted">
            <div class="section-inner">
                <h2 class="section-title home-section-title">الأنظمة الرئيسية</h2>

                <div class="home-frost-panel reveal-on-scroll" style="margin-bottom:1.5rem;">
                    <div class="home-frost-panel__bg" style="background-image:url('<?php echo htmlspecialchars($imgTeam, ENT_QUOTES, 'UTF-8'); ?>');" aria-hidden="true"></div>
                    <div class="home-frost-panel__content">
                        <div class="home-system-block">
                            <h3><span class="home-inline-icon">1</span> نظام التسجيل وتسجيل الدخول</h3>
                            <ul>
                                <li>تسجيل حساب طالب أو جهة تدريب عبر نماذج مخصّصة وموحّدة للهوية البصرية.</li>
                                <li>جهات التدريب الجديدة تبقى قيد المراجعة حتى موافقة المشرف، ثم يُفعّل الدخول.</li>
                                <li>استعادة كلمة المرور عبر رابط زمني آمن (مع إرشادات للربط بالبريد في بيئة الإنتاج).</li>
                            </ul>
                        </div>
                        <div class="home-system-block">
                            <h3><span class="home-inline-icon">2</span> نظام الفرص</h3>
                            <ul>
                                <li>إدخال الفرصة بكل تفاصيلها الأكاديمية والتنظيمية: التخصص، المدة، المدينة، نمط التدريب (حضوري / عن بعد / هجين)، المقاعد، التواريخ، المتطلبات والمزايا.</li>
                                <li>تعديل الفرصة أو إغلاقها أو حذفها من لوحة الجهة.</li>
                                <li>بحث وتصفية للطلاب: الكلمة المفتاحية، التخصص، المدينة، نمط التدريب، نوع الجهة، والحد الأقصى للمدة.</li>
                            </ul>
                        </div>
                        <div class="home-system-block">
                            <h3><span class="home-inline-icon">3</span> نظام التقديم</h3>
                            <ul>
                                <li>تقديم بضغطة مع رسالة تعريفية اختيارية وربط تلقائي بملف السيرة عند توفره.</li>
                                <li>منع التقديم المكرر على ذات الفرصة.</li>
                                <li>عرض المتقدمين للجهة وقبول أو رفض كل طلب مع توثيق سبب الرفض عند الحاجة.</li>
                                <li>متابعة حالات الطلب للطالب: قيد المراجعة، مقبول، مرفوض.</li>
                            </ul>
                        </div>
                        <div class="home-system-block">
                            <h3><span class="home-inline-icon">4</span> نظام التقييمات</h3>
                            <ul>
                                <li>تقييم جهة التدريب بعد إتمام فترة تدريب مقبول (نجوم 1–5 وتعليق نصي).</li>
                                <li>عرض التقييمات على صفحة تفاصيل الفرصة لدعم القرار المهني للطلاب.</li>
                                <li>منع تكرار التقييم لنفس الجهة من قبل الطالب نفسه.</li>
                            </ul>
                        </div>
                        <div class="home-system-block">
                            <h3><span class="home-inline-icon">5</span> لوحات التحكم</h3>
                            <ul>
                                <li><strong>الطالب:</strong> إحصائيات الطلبات، آخر النشاطات، ملف شخصي غني (جامعة، تخصص، معدل، مهارات، روابط، سيرة وصورة).</li>
                                <li><strong>الجهة:</strong> إحصائيات الفرص والطلبات، إدارة الفرص، مراجعة المتقدمين، وملف تعريفي للقطاع والمدينة والمسؤول.</li>
                                <li><strong>المشرف:</strong> نظرة شاملة على المنصة، اعتماد الجهات، وإدارة حالة الطلاب.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <h3 class="home-subtitle"><span class="home-inline-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg></span> خصائص إضافية</h3>
                <ul class="home-feature-list reveal-on-scroll">
                    <li>ملف الطالب الأكاديمي: الجامعة، التخصص، المعدل، المهارات، السيرة الذاتية، الصورة الشخصية، روابط لينكدإن وجيثب.</li>
                    <li>الملف التعريفي للجهة: القطاع، المدينة، الوصف، الموقع الإلكتروني، واسم مسؤول التواصل.</li>
                    <li>صفحة تفاصيل كل فرصة تعرض بيانات الجهة كاملة وتقييماتها المجتمعة.</li>
                    <li>تصميم متجاوب يعمل على الحاسب والجهاز اللوحي والجوال، وواجهات عربية بالكامل (RTL).</li>
                </ul>
            </div>
        </section>

        <section class="home-section">
            <div class="section-inner">
                <h2 class="section-title home-section-title">انطلق مع مسار</h2>
                <div class="home-audience-grid home-audience-grid--two">
                    <div class="home-audience-card reveal-on-scroll">
                        <div class="home-audience-head"><span class="home-audience-badge">طالب</span></div>
                        <p class="home-audience-text">ابنِ ملفك الأكاديمي، واستكشف الفرص، وقدّم بطريقة واحدة موحّدة عبر المنصة.</p>
                        <div class="home-audience-links">
                            <a href="<?php echo $b; ?>opportunities.php" class="btn btn-primary btn-sm">الفرص</a>
                            <a href="<?php echo $b; ?>register.php" class="btn btn-outline btn-sm">تسجيل</a>
                        </div>
                    </div>
                    <div class="home-audience-card reveal-on-scroll">
                        <div class="home-audience-head"><span class="home-audience-badge home-audience-badge--org">جهة تدريب</span></div>
                        <p class="home-audience-text">اعرض برامجك التدريبية واستقبل الطلبات وادِر دورة القبول من لوحة واحدة بعد الاعتماد.</p>
                        <div class="home-audience-links">
                            <a href="<?php echo $b; ?>register.php" class="btn btn-primary btn-sm">تسجيل جهة</a>
                            <a href="<?php echo $b; ?>login.php" class="btn btn-outline btn-sm">دخول</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="home-section home-section--muted">
            <div class="section-inner">
                <h2 class="section-title home-section-title">مسار العمل في ثلاث خطوات</h2>
                <ol class="home-steps">
                    <li class="home-step reveal-on-scroll">
                        <span class="home-step-num">1</span>
                        <div class="home-step-body">
                            <h3 class="home-step-title">إنشاء الحساب وإثراء الملف</h3>
                            <p class="home-step-desc">يُكمل الطالب بياناته الأكاديمية، وتُسجَّل الجهة وتنتظر الاعتماد عند الاقتضاء.</p>
                        </div>
                    </li>
                    <li class="home-step reveal-on-scroll">
                        <span class="home-step-num">2</span>
                        <div class="home-step-body">
                            <h3 class="home-step-title">استكشاف الفرص والتقديم</h3>
                            <p class="home-step-desc">فلترة دقيقة ثم تقديم إلكتروني موحّد مع رسالة اختيارية وتتبع فوري للحالة.</p>
                        </div>
                    </li>
                    <li class="home-step reveal-on-scroll">
                        <span class="home-step-num">3</span>
                        <div class="home-step-body">
                            <h3 class="home-step-title">القرار والتغذية الراجعة</h3>
                            <p class="home-step-desc">تُحدَّث حالات الطلبات من جهة التدريب، ويُسهم الطالب بتقييم بنّاء بعد إتمام التجربة.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section class="home-section home-tech">
            <div class="section-inner home-tech-inner">
                <h2 class="section-title home-section-title home-tech-title">البنية التقنية</h2>
                <p class="home-tech-lead">تطبيق المنصة باستخدام PHP و MySQL عبر PDO، مع واجهات HTML5 و CSS3 و JavaScript، مع مراعاة الأمان وسهولة الاستخدام.</p>
                <ul class="home-tech-tags reveal-on-scroll">
                    <li>PHP</li>
                    <li>MySQL</li>
                    <li>HTML5</li>
                    <li>CSS3</li>
                    <li>JavaScript</li>
                    <li>PDO</li>
                </ul>
            </div>
        </section>

        <section class="home-cta" aria-labelledby="cta-heading">
            <div class="section-inner home-cta-inner">
                <h2 id="cta-heading" class="home-cta-title">انضم إلى منظومة التدريب التعاوني الرقمية</h2>
                <p class="home-cta-desc">مسار يدعم جودة المخرجات وشفافية الفرص — ابدأ اليوم.</p>
                <div class="home-cta-actions">
                    <a href="<?php echo $b; ?>opportunities.php" class="btn btn-primary home-btn-glow">تصفح الفرص</a>
                    <a href="<?php echo $b; ?>register.php" class="btn btn-secondary">إنشاء حساب</a>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
