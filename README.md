# مسار

## 1 ما هو المشروع

منصة ويب تربط الطلاب بفرص التدريب لدى جهات التدريب. الطالب يستعرض الفرص ويتقدم ويتابع الطلب. الجهة تنشر الفرص وتراجع المتقدمين بعد اعتماد حسابها. المدير يعتمد الجهات ويغيّر حالة الطالب بين `active` و`inactive`.

## 2 لماذا وُجد

الوصف في الملف السابق يطابق الصفحات: نشر فرصة، تقديم، قبول أو رفض، تقييم بعد انتهاء الفرصة المقبولة. لا وثيقة منتج منفصلة غير `README.md` السابق وملفات PHP.

## 3 المستخدمون

| الطرف | الجدول | الدخول |
| --- | --- | --- |
| طالب | `STUDENT` | البريد، والحالة `active` |
| جهة | `TRAINING_ORGANIZATION` | البريد، و`is_approved = 1` |
| مدير | `ADMIN` | اسم المستخدم أو البريد |
| مشرف جامعي | `UNIVERSITY_SUPERVISOR` | بيانات بذرية فقط. لا صفحة دخول في الملفات الحالية |

البريد التجريبي للمدير في `database/masar.sql`: `admin@masar.sa` واسم المستخدم `admin`. مثال طالب: `reif@uqu.edu.sa`. مثال جهة: البريد موجود في إدراج الجهات ضمن الملف نفسه (الملف السابق يذكر `hr@stc.com.sa`). كلمات المرور في تعليقات SQL والملف السابق ولا تُعاد هنا. التجزئة المخزنة من نوع bcrypt عبر `password_hash` في التسجيل و`PASSWORD_BCRYPT`.

تناقض شكلي: الملف السابق يقول إن قيم bcrypt طُوّرت لتطابق كلمات التعليقات. هذا الملف لا يعيد التحقق من التطابق ولا ينسخ الكلمات.

## 4 القدرات

- تسجيل طالب بحالة `active` أو جهة بحالة `is_approved = 0` من `register.php`.
- دخول بترتيب: طالب ثم مدير ثم جهة في `login.php`.
- استعادة كلمة مرور: رمز عشوائي، تخزين `sha256`، صلاحية ساعة، والرابط يظهر في رسالة وميض داخل الصفحة (`forgot_password.php`). لا دالة `mail`.
- قائمة فرص مع فلاتر و`training_mode` في `opportunities.php`.
- تفاصيل وتقييمات وتقديم من `opportunity_details.php` و`student/apply.php`.
- ملف طالب: بيانات، مرحلة علمية، معدل، مهارات، روابط، صورة، PDF سيرة.
- تقييم جهة من `student/write_review.php` بعد قبول وانتهاء `end_date`، مع منع التكرار بفهرس فريد `(student_id, organization_id)`.
- الجهة: ملف، إضافة وتعديل فرصة، إغلاق أو حذف، قبول أو رفض طلب مع سبب رفض.
- المدير: لوحة، اعتماد جهات، تفعيل أو تعطيل طلاب.

## 5 كيف يعمل

الصفحات تتضمن `includes/session_bootstrap.php` (اسم الجلسة `MASAR_SESSID`). `requireRole` يقارن `$_SESSION['user_type']`. النماذج ترسل حقل `csrf` وتُفحص بـ `csrfCheck`. الملفات المرفوعة تُحفظ تحت `assets/uploads/`.

## 6 أمثلة واقعية

`student/apply.php` بعد `csrfCheck` يدرج في `TRAINING_APPLICATION` حالة `pending` وتاريخ `CURDATE()` وينسخ `cv_path` من صف الطالب إن وُجد.

`student/write_review.php` يدرج في `REVIEW` مع `is_verified = 1` بعد التأكد من طلب `accepted` و`end_date < CURDATE()`.

`forgot_password.php` إن وجد البريد يبني رابط `reset_password.php?token=` ويعرضه في `setFlash` مع عبارة «بيئة تطوير». إن لم يوجد البريد فالرسالة العامة عن صندوق الوارد، بلا إرسال بريد.

## 7 رحلة المستخدم

طالب: `register.php` ثم `login.php` ثم `opportunities.php` ثم `opportunity_details.php` ثم `student/apply.php` ثم متابعة `student/applications.php`. بعد القبول وانتهاء الموعد: `student/write_review.php`.

جهة: تسجيل ثم انتظار `admin/approve_companies.php` ثم `company/add_opportunity.php` ثم `company/review_applications.php`.

مدير: `admin/dashboard.php`.

زائر: `index.php` و`opportunities.php` بلا جلسة للاطلاع. التقديم يتطلب دور طالب.

## 8 الوحدات

| الوحدة | المسار |
| --- | --- |
| عام | `index.php` `login.php` `register.php` `logout.php` `forgot_password.php` `reset_password.php` `opportunities.php` `opportunity_details.php` |
| طالب | `student/` |
| جهة | `company/` |
| إدارة | `admin/` |
| مشترك | `includes/` و`config/` |
| بيانات | `database/masar.sql` و`database/patch_student_education_level.sql` |

## 9 الجهات والكيانات

جهات التدريب صفوف `TRAINING_ORGANIZATION` بأنواع وقطاع ومدينة. البذرة تضم جهات منها ما يذكره الملف السابق عن STC مع فرص وتقييمات. المشرف الجامعي صف مرجعي بلا واجهة. الدعم الفني في `config/support.php`: البريد `support@masar.sa` والهاتف `0550000000`.

## 10 الصلاحيات

`requireRole` بقيم الجلسة `student` و`company` و`admin`. الجهة تعدّل فرصها بشرط `organization_id` في الاستعلام (مثال `edit_opportunity.php`). الطالب يحدّث صفه فقط. المدير يحدّث `STUDENT.status` و`is_approved` للجهة. طالب `inactive` لا يكمل الدخول. جهة غير معتمدة لا تكمل الدخول.

## 11 الأتمتة

لا مجدول. تواريخ `CURDATE()` عند التسجيل والتقديم والرد. انتهاء رمز الاستعادة ساعة من `DateTime('+1 hour')`. رقم أصول الواجهة في `config/assets_version.php` ثابت راجع قيمته `1` ويُلحق بروابط CSS وJS عبر `asset_url` لكسر التحميل المؤقت.

## 12 أثر الوحدات على بعضها

اعتماد الجهة في `is_approved` يفتح الدخول ونشر الفرص. إغلاق الفرصة (`is_active`) يخرجها من قائمة `opportunities.php` التي تشترط `is_active = 1`. قبول الطلب يتيح لاحقاً التقييم بعد `end_date`. حذف فرصة في SQL عليه `ON DELETE CASCADE` للطلبات. التقييم المكرر يصطدم بالقيد الفريد. حفظ المرحلة العلمية يفشل على قاعدة قديمة بلا العمود، والرسالة في `register.php` و`student/profile.php` تطلب تنفيذ `patch_student_education_level.sql`.

## 13 المعجم

| المصطلح | المعنى |
| --- | --- |
| فرصة | صف `TRAINING_OPPORTUNITY` |
| طلب | صف `TRAINING_APPLICATION` بحالات منها `pending` و`accepted` و`rejected` |
| نمط التدريب | `حضوري` أو `عن بعد` أو `هجين` |
| مرحلة علمية | `education_level` وقيم الواجهة تشمل البكالوريوس والدبلوم |
| وميض | `setFlash` / `getFlash` لرسالة بعد التحويل |

## 14 الأسئلة الشائعة

| السؤال | الجواب |
| --- | --- |
| أين مخطط ERD؟ | الملف السابق يذكر `docs/ERD.md`. المجلد غير موجود في الملفات الحالية |
| هل تصل رسالة الاستعادة بالبريد؟ | غير موجود. الرابط يُطبع في رسالة الصفحة عند وجود الحساب |
| ما حد رفع الملفات؟ | الملف السابق: صورة 2MB وPDF 5MB مع فحص MIME في `student/profile.php`. هذا يطابق وجود `finfo` هناك |
| هل المشرف الجامعي يدخل؟ | لا صفحة دخول له |

## 15 المعمارية

```
المتصفح
   |
   +-- صفحات الجذر وصفحات student/ company/ admin/
            |
            +-- session_bootstrap.php (MASAR_SESSID)
            +-- functions.php (csrf, clean, requireRole)
            +-- config/database.php -- MySQL masar
            +-- assets/uploads/{cv,profiles,logos}
```

## 16 التقنيات المستخدمة

PHP مع PDO و`pdo_mysql`. MySQL أو MariaDB كما في الملف السابق (الملفات تستخدم SQL متوافق مع InnoDB و`utf8mb4`). HTML وCSS في `assets/css/style.css` وJavaScript في `assets/js/main.js`. خطوط IBM Plex Sans Arabic محلية. لا Composer.

الملف السابق: PHP 7.4 أو أحدث ويفضّل 8، مع Apache مثل XAMPP. رقم الإصدار غير مثبت في ملف اعتماديات.

## 17 شجرة الملفات

```
Masar/
├── index.php login.php register.php logout.php
├── forgot_password.php reset_password.php
├── opportunities.php opportunity_details.php
├── admin/ dashboard.php approve_companies.php manage_users.php
├── company/ dashboard.php profile.php add_opportunity.php
│            edit_opportunity.php manage_opportunities.php review_applications.php
├── student/ dashboard.php profile.php applications.php apply.php write_review.php
├── config/ database.php support.php assets_version.php
├── includes/ auth.php functions.php header.php footer.php session_bootstrap.php
├── database/ masar.sql patch_student_education_level.sql
├── assets/css/style.css
├── assets/js/main.js
├── assets/fonts/ assets/images/logo.png
└── assets/uploads/cv|profiles|logos (.gitkeep)
```

`docs/ERD.md` مذكور في الملف السابق وغير موجود في الشجرة الحالية.

## 18 الواجهة الأمامية

هيدر وتذييل مشتركين. `app_base()` يحسب المسار النسبي من مجلدات `student` و`company` و`admin`. `main.js` للقائمة والتبويبات والتنبيهات كما في الملف السابق. شعار `assets/images/logo.png`. لا وسم `rel="icon"` في نتائج البحث داخل PHP.

## 19 الواجهة الخلفية

صفحات PHP تولّد HTML. لا مجلد `api` منفصل. `config/database.php` ينشئ `$pdo`. فشل الاتصال يوقف التنفيذ بنص «فشل الاتصال بقاعدة البيانات» ملحقاً برسالة الاستثناء.

## 20 تدفق الطلب

مثال تقديم طالب على فرصة:

```
POST student/apply.php
  -> csrfCheck
  -> requireRole student
  -> التحقق من عدم تكرار الطلب
  -> INSERT TRAINING_APPLICATION
     status = pending
     application_date = CURDATE()
     cv_path من STUDENT عند وجوده
  -> redirect مع setFlash
```

## 21 جداول قاعدة البيانات

| الجدول | الوظيفة |
| --- | --- |
| STUDENT | حساب الطالب، الحالة، الصورة، السيرة، gpa، skills، linkedin_url، github_url، education_level |
| TRAINING_ORGANIZATION | الجهة، contact_person_name، password، is_approved |
| TRAINING_OPPORTUNITY | الفرصة، training_mode، is_active، FK CASCADE من الجهة |
| TRAINING_APPLICATION | الطلب، cover_letter، cv_path، response_date، rejection_reason |
| REVIEW | تقييم  مع UNIQUE (student_id, organization_id) |
| PASSWORD_RESET | email وuser_role وtoken_hash بطول 64 وexpires_at |
| ADMIN | username وpassword وrole وlast_login |
| UNIVERSITY_SUPERVISOR | اسم وبريد وهاتف وجامعة وقسم |

عمود `education_level` موجود في `masar.sql` الحالي. ملف `patch_student_education_level.sql` يضيفه للقواعد التي أُنشئت قبله.

## 22 نقاط النهاية

صفحات وليست JSON عام:

| المسار | الدور |
| --- | --- |
| `index.php` | الرئيسية وإحصاءات |
| `login.php` `logout.php` `register.php` | الجلسة والتسجيل |
| `forgot_password.php` `reset_password.php` | الرمز وكلمة جديدة بطول 8 على الأقل في نموذج إعادة التعيين |
| `opportunities.php` `opportunity_details.php` | العرض |
| `student/apply.php` | POST للتقديم |
| `student/dashboard.php` `applications.php` `profile.php` `write_review.php` | الطالب |
| `company/dashboard.php` `profile.php` `add_opportunity.php` `edit_opportunity.php` `manage_opportunities.php` `review_applications.php` | الجهة |
| `admin/dashboard.php` `approve_companies.php` `manage_users.php` | المدير |

## 23 المصادقة

جلسة `MASAR_SESSID`. بعد نجاح الدخول `session_regenerate_id(true)`. `logout.php` يمسح كعكة الجلسة ثم `session_destroy`. كلمات المرور بـ `password_hash` / `password_verify`. رمز الاستعادة يُخزَّن `hash('sha256', $token)` لا النص الخام.

## 24 ضوابط الأمان الموجودة فعلياً

- PDO مع `prepare` و`execute`.
- `clean()` للعرض: تقليم وإزالة وسوم و`htmlspecialchars`.
- `csrfToken` عبر `random_bytes(32)` و`csrfCheck` عبر `hash_equals`.
- `password_hash(..., PASSWORD_BCRYPT)` و`password_verify`.
- تجديد معرف الجلسة بعد الدخول.
- `finfo` للصورة (JPG/PNG) وPDF في `student/profile.php` مع حد الحجم المذكور في الملف السابق.
- `requireRole` وملكية `organization_id`.
- `optionalUrl` للروابط الاختيارية في `functions.php`.

رسالة فشل PDO تكشف `$e->getMessage()`. لا ترويسة أمان مخصصة في الملفات. خصائص الكعكة `HttpOnly` و`Secure` و`SameSite` غير مضبوطة في `session_bootstrap.php` (يُستخدم `session_start` الافتراضي).

## 25 الإعدادات

`config/database.php`: مضيف `localhost`، مستخدم `root`، كلمة مرور الاتصال فارغة، قاعدة `masar`، الترميز `utf8mb4`. `config/support.php` للبريد والهاتف الظاهرين للدعم. `config/assets_version.php` يرجع `1`. لا `.env`.

## 26 التكاملات

غير موجود في الملفات الحالية: لا SMTP رغم نص الرسالة عند بريد غير موجود، ولا بوابة دفع، ولا تخزين خارجي. `org_logo_src` في `functions.php` يبني مسار شعار الجهة من الملفات المحلية أو الموقع حسب تنفيذ الدالة.

## 27 المهام المجدولة

غير موجود في الملفات الحالية. صلاحية الرمز تُفحص عند الاستخدام بـ `expires_at > NOW()` في `reset_password.php`.

## 28 تخزين الملفات

`assets/uploads/cv/` و`profiles/` و`logos/` مع `.gitkeep`. المسارات تُحفظ في `cv_path` و`profile_image`.

## 29 السجلات

غير موجود في الملفات الحالية. `ADMIN.last_login` عمود في الجدول. لا تحديث له ظهر في مسار `login.php` ضمن ما فُحص من تعبئة الجلسة.

## 30 التثبيت

1. PHP مع `pdo_mysql` وخادم ويب وMySQL. الملف السابق يطلب PHP 7.4 أو أحدث.
2. وضع المجلد تحت جذر الويب.
3. استيراد `database/masar.sql` مرة على قاعدة جديدة (يعيد إنشاء `masar` والجداول والبذرة). تكرار `ALTER` أو الإدراج قد يفشل كما نبّه الملف السابق.
4. قاعدة أقدم بلا `education_level`: تنفيذ `database/patch_student_education_level.sql` مرة واحدة.
5. مطابقة `config/database.php`.
6. صلاحية الكتابة على مجلدات `assets/uploads/`.
7. فتح `index.php`.

## 31 دليل التطوير

الصفحات حسب الدور داخل المجلدات الثلاثة. الدوال المشتركة في `includes/functions.php`. أي نموذج POST جديد يحتاج `csrf` و`csrfCheck` ليتبع النمط الحالي. استعلامات القوائم تُبنى بمعاملات `prepare` كما في `opportunities.php`. بعد تعديل CSS أو JS تُرفع قيمة الإرجاع في `assets_version.php` لأن `asset_url` يقرأها.

## 32 النشر

غير موثق بملف منصة. التطبيق PHP تقليدي مع ملفات مرفوعة على القرص، فيحتاج قرصاً يبقى بين إعادات التشغيل إن استُخدم رفع السير والصور.

## 33 النسخ الاحتياطي

غير موثق. انسخ قاعدة `masar` ومجلد `assets/uploads/`.

## 34 استكشاف الأخطاء

| الظاهرة | الاستنتاج من الكود |
| --- | --- |
| فشل الاتصال | MySQL أو اسم القاعدة `masar` أو بيانات `config/database.php`، والرسالة تلحق نص PDO |
| جهة لا تدخل | `is_approved` ليس 1 حتى الاعتماد |
| طالب لا يدخل | `status` ليس `active` |
| فشل التسجيل بعد إضافة المرحلة | العمود `education_level` غائب ويُطلب ملف التصحيح |
| رفع يفشل | صلاحيات المجلد أو حدود `upload_max_filesize` أو MIME المرفوض |
| تنسيق مكسور من مجلد فرعي | مسار `app_base()` نحو `assets/css/style.css` |
| جلسة غريبة بين مشاريع على نفس المضيف | التعليق في `session_bootstrap.php` يشرح أن الاسم `MASAR_SESSID` فُصل عن `PHPSESSID` |

## 35 الاعتماديات

لا `composer.json`. الامتدادات المستخدمة: PDO MySQL، `fileinfo`، `session`، `json` غير مطلوب لمسار API عام. إصدار أصول الواجهة: `1`.

## 36 القيود المعروفة

- رابط الاستعادة يُعرض في الصفحة ولا يُرسل بالبريد، بينما رسالة «البريد غير الموجود» تتحدث عن صندوق الوارد.
- `UNIVERSITY_SUPERVISOR` بلا واجهة.
- `docs/ERD.md` مذكور سابقاً وغير موجود.
- خطأ الاتصال يكشف رسالة القاعدة.
- لا CSRF على طلبات غير النماذج التي تستدعي `csrfCheck` (المسار الحالي للنماذج الحساسة يستدعيه).
- لا أيقونة تبويب في ملفات PHP المفحوصة.

## 37 الحالة الحالية

منصة تدريب ثلاثية الأدوار مع استعادة كلمة مرور محلية للتجربة، ومرحلة علمية، وأنماط تدريب، وتقييم بعد انتهاء الفرصة. رقم إصدار التطبيق غير موجود. إصدار كاش الأصول `1`.

## 38 قرارات معمارية

- استنتاج من الكود: ثلاث جداول حسابات منفصلة بدل جدول مستخدم واحد، لذلك `login.php` يفحصها بالتتابع و`PASSWORD_RESET.user_role` يميز الهدف.
- استنتاج من الكود: اسم جلسة خاص لتقليل اختلاط `PHPSESSID` مع مشاريع أخرى على `localhost`.
- استنتاج من الكود: التقييم يُقفل بعد انتهاء تاريخ الفرصة والقبول، لا بمجرد التقديم.

## 39 سجل التغييرات

غير موجود كسجل إصدارات. الملف السابق يذكر تعديلات دُمجت في `masar.sql` (كلمة مرور الجهة، حقول الطالب، `training_mode`، `PASSWORD_RESET`، القيد الفريد للتقييم). القيمة الرقمية الوحيدة للإصدار في الكود هي كاش الأصول `1` في `config/assets_version.php`.

## System Overview

مسار يربط طالباً معتمداً بفرصة جهة معتمدة، ويمرر الطلب من `pending` إلى قبول أو رفض، ثم يسمح بتقييم واحد بعد انتهاء الفترة.

## Quick Reference

| الجزء | التقنية | الموقع | الدور |
| --- | --- | --- | --- |
| الاتصال | PDO | `config/database.php` | قاعدة `masar` |
| الجلسة | PHP Session | `includes/session_bootstrap.php` | `MASAR_SESSID` |
| الدوال | PHP | `includes/functions.php` | CSRF ومسارات وأدوار |
| الدعم | PHP | `config/support.php` | بريد وهاتف ظاهران |
| الطالب | PHP | `student/` | تقديم وملف وتقييم |
| الجهة | PHP | `company/` | فرص وطلبات |
| المدير | PHP | `admin/` | اعتماد وحالة طالب |
| المخطط | SQL | `database/masar.sql` | الجداول والبذرة |
| التصحيح | SQL | `database/patch_student_education_level.sql` | عمود المرحلة |
| الواجهة | CSS/JS | `assets/css` و`assets/js` | التنسيق والسلوك |
| الرفع | ملفات | `assets/uploads/` | سيرة وصورة وشعار |

## Quick Start

1. استورد `database/masar.sql` على خادم جديد.
2. راجع `config/database.php`.
3. افتح `index.php`.
4. حسابات البذرة في تعليقات `database/masar.sql` والملف السابق. لا تُنسخ كلمات المرور هنا.

## For Non-Technical Users

الطالب يسجّل ويقدّم على فرصة ظاهرة. الجهة تنتظر موافقة المدير قبل الدخول. بعد القبول وانتهاء مدة الفرصة يمكن تقييم الجهة مرة واحدة. إذا نسيت كلمة المرور فالرابط يظهر على الصفحة في هذه النسخة ولا يصل برسالة بريد.

## For Developers

أبقِ `prepare` و`csrfCheck` و`requireRole`. عند إضافة عمود لطالب قديم وفّر ملف تصحيح مثل `patch_student_education_level.sql` لأن `masar.sql` يُستورد مرة على قاعدة جديدة. ارفع `assets_version.php` بعد تغيير ملفات `assets`.
