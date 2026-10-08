-- =============================================================================
-- مسار Masar — ملف قاعدة البيانات الموحّد (هيكل + بيانات تجريبية)
-- استورد الملف مرة واحدة على قاعدة جديدة. كلمات المرور التجريبية:
--   مدير:   |  طالب:   |  جهة: 
-- =============================================================================

CREATE DATABASE IF NOT EXISTS masar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE masar;

SET NAMES utf8mb4;

CREATE TABLE UNIVERSITY_SUPERVISOR (
    supervisor_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    university VARCHAR(255),
    department VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE STUDENT (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255),
    email VARCHAR(255),
    password VARCHAR(255),
    phone VARCHAR(20),
    university VARCHAR(255),
    major VARCHAR(255),
    city VARCHAR(100),
    academic_year INT,
    education_level VARCHAR(50) NOT NULL DEFAULT 'بكالوريوس',
    registration_date DATE,
    status VARCHAR(50),
    profile_image VARCHAR(255) NULL,
    cv_path VARCHAR(255) NULL,
    gpa VARCHAR(20) NULL,
    skills TEXT NULL,
    linkedin_url VARCHAR(512) NULL,
    github_url VARCHAR(512) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ADMIN (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    password VARCHAR(255),
    full_name VARCHAR(255),
    email VARCHAR(255),
    role VARCHAR(100),
    last_login DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE TRAINING_ORGANIZATION (
    organization_id INT AUTO_INCREMENT PRIMARY KEY,
    organization_name VARCHAR(255),
    contact_person_name VARCHAR(255) NULL,
    organization_type VARCHAR(100),
    industry_sector VARCHAR(255),
    city VARCHAR(100),
    address VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    password VARCHAR(255),
    website VARCHAR(255),
    description TEXT,
    is_approved BOOLEAN,
    registration_date DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE TRAINING_OPPORTUNITY (
    opportunity_id INT AUTO_INCREMENT PRIMARY KEY,
    organization_id INT,
    title VARCHAR(255),
    description TEXT,
    required_major VARCHAR(255),
    training_mode VARCHAR(50) NOT NULL DEFAULT 'حضوري',
    duration_weeks INT,
    start_date DATE,
    end_date DATE,
    available_positions INT,
    requirements TEXT,
    benefits TEXT,
    is_active BOOLEAN,
    posted_date DATE,
    FOREIGN KEY (organization_id) REFERENCES TRAINING_ORGANIZATION(organization_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE TRAINING_APPLICATION (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    opportunity_id INT,
    application_date DATE,
    status VARCHAR(50),
    cover_letter TEXT,
    cv_path VARCHAR(255),
    response_date DATE,
    rejection_reason TEXT,
    FOREIGN KEY (student_id) REFERENCES STUDENT(student_id) ON DELETE CASCADE,
    FOREIGN KEY (opportunity_id) REFERENCES TRAINING_OPPORTUNITY(opportunity_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE REVIEW (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    organization_id INT,
    rating INT,
    review_text TEXT,
    review_date DATE,
    is_verified BOOLEAN,
    FOREIGN KEY (student_id) REFERENCES STUDENT(student_id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES TRAINING_ORGANIZATION(organization_id) ON DELETE CASCADE,
    UNIQUE KEY uq_review_student_org (student_id, organization_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE PASSWORD_RESET (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    user_role VARCHAR(20) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_expires (expires_at),
    INDEX idx_email_role (email, user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- كلمة مرور المدير:  (bcrypt)
INSERT INTO ADMIN (username, password, full_name, email, role) VALUES
('admin', '',
 'مدير النظام', 'admin@masar.sa', 'admin');

INSERT INTO UNIVERSITY_SUPERVISOR (full_name, email, phone, university, department) VALUES
('د. سوزان جستنية', 'supervisor@uqu.edu.sa', '0500000001', 'جامعة أم القرى', 'برمجة وعلوم حاسب');

-- كلمة مرور الطلاب:  (bcrypt)
INSERT INTO STUDENT (full_name, email, password, phone, university, major, city, academic_year, registration_date, status) VALUES
('ريف سعد العتيبي',        'reif@uqu.edu.sa',    '', '0501111101', 'جامعة أم القرى', 'برمجة وعلوم حاسب', 'مكة المكرمة',  4, CURDATE(), 'active'),
('ريماس محمد العسيري',     'rimas@uqu.edu.sa',   '', '0501111102', 'جامعة أم القرى', 'برمجة وعلوم حاسب', 'مكة المكرمة',  4, CURDATE(), 'active'),
('وسن سعود الهذلي',        'wasan@uqu.edu.sa',   '', '0501111103', 'جامعة أم القرى', 'برمجة وعلوم حاسب', 'مكة المكرمة',  4, CURDATE(), 'active'),
('ميار الغامدي',           'miyar@uqu.edu.sa',   '', '0501111104', 'جامعة أم القرى', 'برمجة وعلوم حاسب', 'مكة المكرمة',  4, CURDATE(), 'active'),
('غلا الكبكبي',            'ghalaa@uqu.edu.sa',  '', '0501111105', 'جامعة أم القرى', 'برمجة وعلوم حاسب', 'مكة المكرمة',  3, CURDATE(), 'active'),
('مدى السلمي',             'mada@uqu.edu.sa',    '', '0501111106', 'جامعة أم القرى', 'برمجة وعلوم حاسب', 'مكة المكرمة',  4, CURDATE(), 'active'),
('ليان روزن حمزه مليباري', 'layan@uqu.edu.sa',   '', '0501111107', 'جامعة أم القرى', 'برمجة وعلوم حاسب', 'مكة المكرمة',  4, CURDATE(), 'active'),
('رغد فهد الحربي',         'raghad@uqu.edu.sa',  '', '0501111108', 'جامعة أم القرى', 'برمجة وعلوم حاسب', 'مكة المكرمة',  3, CURDATE(), 'active'),
('عبدالله فيصل الدوسري',   'abdullah.ksu@edu.sa','', '0502222001', 'جامعة الملك سعود', 'هندسة حاسوب وشبكات', 'الرياض', 4, CURDATE(), 'active'),
('نورة خالد الشهري',       'noura.kau@edu.sa',   '', '0502222002', 'جامعة الملك عبدالعزيز', 'نظم معلومات إدارية', 'جدة', 3, CURDATE(), 'active'),
('سارة تركي المطيري',      'sara.pnu@edu.sa',    '', '0502222003', 'جامعة الأميرة نورة', 'تسويق رقمي', 'الرياض', 4, CURDATE(), 'active'),
('فهد ماجد القحطاني',      'fahad.qassim@edu.sa','', '0502222004', 'جامعة القصيم', 'محاسبة', 'بريدة', 3, CURDATE(), 'active'),
('هند سعيد العتيبي',       'hind.taibah@edu.sa', '', '0502222005', 'جامعة طيبة', 'تصميم جرافيكي وتجربة مستخدم', 'المدينة المنورة', 4, CURDATE(), 'active'),
('خالد يوسف الزهراني',     'khalid.najran@edu.sa','', '0502222006', 'جامعة نجران', 'هندسة كهربائية', 'نجران', 4, CURDATE(), 'active'),
('لينا عبدالرحمن باحسين',  'lina.alfaisal@edu.sa','', '0502222007', 'جامعة الملك فيصل', 'إدارة أعمال', 'الأحساء', 3, CURDATE(), 'active'),
('عمر حاتم الغامدي',       'omar.ubt@edu.sa',    '', '0502222008', 'جامعة تبوك', 'أمن سيبراني', 'تبوك', 4, CURDATE(), 'active'),
('دانة مشعل السبيعي',      'dana.imamu@edu.sa',  '', '0502222009', 'جامعة الإمام محمد بن سعود', 'قانون وتقنية', 'الرياض', 3, CURDATE(), 'active'),
('بدر ناصر الحربي',        'bader.kfu@edu.sa',   '', '0502222010', 'جامعة الملك فهد للبترول والمعادن', 'هندسة برمجيات', 'الظهران', 4, CURDATE(), 'active'),
('جمانة علي العجمي',       'jumana.iau@edu.sa',  '', '0502222011', 'جامعة الإمام عبدالرحمن بن فيصل', 'تحليل أعمال', 'الدمام', 3, CURDATE(), 'active'),
('طلال راشد المالكي',      'talal.btu@edu.sa',   '', '0502222012', 'جامعة الباحة', 'علوم حاسب', 'الباحة', 4, CURDATE(), 'active'),
('أمل فهد الدلبحي',        'amal.jazan@edu.sa',  '', '0502222013', 'جامعة جازان', 'هندسة صناعية', 'جازان', 3, CURDATE(), 'active'),
('يوسف حمد العنزي',        'yousef.hail@edu.sa', '', '0502222014', 'جامعة حائل', 'ذكاء اصطناعي وتعلم آلي', 'حائل', 4, CURDATE(), 'active'),
('شهد بندر القرني',        'shahad.shaqra@edu.sa','', '0502222015', 'جامعة شقراء', 'تقنية معلومات', 'شقراء', 3, CURDATE(), 'active'),
('مازن سعد العتيبي',       'mazen.uqu@edu.sa',   '', '0502222016', 'جامعة أم القرى', 'هندسة برمجيات', 'مكة المكرمة', 4, CURDATE(), 'active');

-- كلمة مرور الجهات:  (bcrypt)
INSERT INTO TRAINING_ORGANIZATION (organization_name, organization_type, industry_sector, city, phone, email, password, website, description, is_approved, registration_date) VALUES
('شركة الاتصالات السعودية STC', 'شركة مساهمة',  'تقنية المعلومات والاتصالات', 'الرياض',  '920011111', 'hr@stc.com.sa',        '', 'https://stc.com.sa',    'الشركة الرائدة في قطاع الاتصالات في المملكة.',             1, CURDATE()),
('أرامكو السعودية',              'شركة مساهمة',  'طاقة وبترول',                'الظهران', '920033333', 'hr@aramco.com',        '', 'https://aramco.com',    'أكبر شركة طاقة في العالم وتقدم فرص تدريب استثنائية.',      1, CURDATE()),
('شركة علم للخدمات الرقمية',    'شركة مساهمة',  'تقنية المعلومات',            'الرياض',  '920044444', 'intern@elm.sa',        '', 'https://elm.sa',        'متخصصة في الخدمات الحكومية الرقمية.',                       1, CURDATE()),
('وزارة الاتصالات وتقنية المعلومات','جهة حكومية','حكومي',                     'الرياض',  '920055555', 'training@mcit.gov.sa', '', 'https://mcit.gov.sa',   'الوزارة المشرفة على قطاع الاتصالات ضمن رؤية 2030.',         1, CURDATE()),
('شركة موبايلي',                 'شركة مساهمة',  'تقنية المعلومات والاتصالات', 'الرياض',  '920066666', 'careers@mobily.com',   '', 'https://mobily.com',    'إحدى أكبر شركات الاتصالات بفرص تدريب متطورة.',              1, CURDATE()),
('سابك SABIC',                   'شركة مساهمة',  'بتروكيماويات وصناعة',        'الجبيل',  '920077777', 'talent@sabic.com',     '', 'https://sabic.com',     'شركة عالمية في مجال الكيماويات مع برامج تدريب هندسية.',     1, CURDATE()),
('معادن Maaden',                 'شركة مساهمة',  'تعدين ومعادن',               'الرياض',  '920088888', 'internship@maaden.com.sa','','https://maaden.com.sa','الشركة العربية للتعدين وفرص ميدانية متنوعة.',              1, CURDATE()),
('البنك الأهلي السعودي',         'شركة مساهمة',  'خدمات مصرفية',               'جدة',     '920099000', 'careers@alahli.com',   '', 'https://alahli.com',    'تدريب في التحول الرقمي والامتثال والتحليل المالي.',        1, CURDATE()),
('مجموعة مستشفيات الدكتور سليمان الحبيب','شركة مساهمة','رعاية صحية',            'الرياض',  '920010011', 'hr@habib.health',      '', 'https://habib.health', 'تدريب إداري وتقني في بيئة طبية رائدة.',                     1, CURDATE()),
('الهيئة السعودية للبيانات والذكاء الاصطناعي (سدايا)','جهة حكومية','بيانات وذكاء اصطناعي','الرياض','920020022','careers@sdaia.gov.sa','','https://sdaia.gov.sa',  'مشاريع وطنية في البيانات والذكاء الاصطناعي.',              1, CURDATE()),
('شركة زين السعودية',            'شركة مساهمة',  'اتصالات',                    'الرياض',  '920030033', 'jobs@sa.zain.com',     '', 'https://sa.zain.com',   'تدريب تقني وتجربة عميل في قطاع الاتصالات.',                 1, CURDATE()),
('مؤسسة محمد بن سلمان «مسك»',    'مؤسسة أهلية','ريادة أعمال وتعليم',          'الرياض',  '920040044', 'programs@misk.org.sa', '', 'https://misk.org.sa',   'برامج وطنية للمهارات الرقمية وريادة الأعمال.',              1, CURDATE()),
('شركة نيوم',                    'شركة مساهمة',  'تطوير عمراني وتقنية',        'نيوم',    '920050055', 'careers@neom.com',     '', 'https://neom.com',      'مشاريع مستقبلية في البنية التحتية والطاقة والتقنية.',       0, CURDATE()),
('شركة ثقة لخدمات الأعمال',      'شركة مساهمة',  'خدمات تقنية وتوثيق',         'الرياض',  '920060066', 'hr@trust.sa',          '', 'https://trust.sa',      'حلول رقمية للقطاعين العام والخاص.',                         1, CURDATE());

INSERT INTO TRAINING_OPPORTUNITY (organization_id, title, description, required_major, duration_weeks, start_date, end_date, available_positions, requirements, benefits, is_active, posted_date) VALUES
(1,'متدرب تطوير تطبيقات ويب',   'العمل مع فريق تطوير الويب في STC على مشاريع حقيقية باستخدام أحدث التقنيات.','برمجة وعلوم حاسب',12,'2026-07-01','2026-09-24',5,'HTML, CSS, JavaScript, PHP أو Python','مكافأة شهرية 3000 ريال، شهادة إتمام، إمكانية التوظيف',1,CURDATE()),
(1,'متدرب أمن المعلومات',        'التدريب في قسم أمن المعلومات على SIEM واختبار الاختراق وتحليل التهديدات.','برمجة وعلوم حاسب',16,'2026-07-01','2026-10-22',3,'معرفة بالشبكات وLinux وأساسيات الأمن','مكافأة 3500 ريال، تدريب معتمد Cisco وFortinet',1,CURDATE()),
(2,'متدرب علوم بيانات',          'العمل مع فريق تحليل البيانات في أرامكو لتحويل البيانات إلى قرارات استراتيجية.','برمجة وعلوم حاسب',20,'2026-08-01','2026-12-25',4,'Python, R, SQL, Machine Learning','مكافأة 4000 ريال، سكن وانتقالات، شهادة أرامكو المعتمدة',1,CURDATE()),
(3,'متدرب تطوير تطبيقات الجوال','تطوير تطبيقات جوال لخدمات حكومية ذكية تؤثر في حياة ملايين المواطنين.','برمجة وعلوم حاسب',12,'2026-07-15','2026-10-08',3,'Android أو iOS Development','مكافأة 2500 ريال، شهادة وتوصية مهنية',1,CURDATE()),
(4,'متدرب تحويل رقمي حكومي',    'المشاركة في مشاريع التحول الرقمي الوطنية ضمن مبادرات رؤية 2030.','برمجة وعلوم حاسب',8,'2026-09-01','2026-10-27',6,'مهارات التحليل والتوثيق وإدارة المشاريع','شهادة حكومية معتمدة، نقاط تدريب أكاديمية',1,CURDATE()),
(5,'متدرب برمجة PHP وقواعد بيانات','تطوير الأنظمة الداخلية وواجهات برمجة التطبيقات في بيئة عمل ديناميكية.','برمجة وعلوم حاسب',12,'2026-07-01','2026-09-24',4,'PHP, MySQL, JavaScript, REST API','مكافأة 2800 ريال، منحة إنترنت مجانية',1,CURDATE()),
(6,'متدرب هندسة عمليات بتروكيماوية','متابعة خطوط الإنتاج وتحسين الجودة والسلامة في مجمع صناعي متكامل.','هندسة كيميائية أو ميكانيكية',16,'2026-08-15','2026-12-07',6,'Excel، أساسيات البرمجة، اللغة الإنجليزية','مكافأة تنافسية، نقل، إرشاد مهندسين معتمدين',1,CURDATE()),
(6,'متدرب تحليل مالي واستثمار','دعم فريق الاستثمار في نمذجة الأعمال وإعداد العروض التقديمية للإدارة العليا.','محاسبة أو إدارة أعمال',10,'2026-09-01','2026-11-10',4,'Excel متقدم، PowerPoint، قراءة قوائم مالية','تجربة في بيئة كبرى الشركات الصناعية',1,CURDATE()),
(7,'متدرب جيولوجيا واستكشاف','المساعدة في عينات الخامات وخرائط المناطق التعدينية تحت إشراف فريق ميداني.','جيولوجيا أو هندسة تعدين',14,'2026-07-20','2026-11-02',5,'GIS أساسي، اللياقة للعمل الميداني','بدل ميداني، معدات سلامة، إقامة حسب السياسة',1,CURDATE()),
(7,'متدرب صيانة أنظمة تحكم صناعي','دعم صيانة أنظمة SCADA في منشآت التعدين.','هندسة كهربائية أو ميكاترونكس',12,'2026-08-01','2026-10-24',3,'أساسيات التحكم، قراءة مخططات','تدريب معتمد على معدات حقيقية',1,CURDATE()),
(8,'متدرب تحول رقمي وخدمات مصرفية','المشاركة في مشاريع التطبيقات المصرفية وتحسين رحلة العميل الرقمية.','نظم معلومات أو تسويق رقمي',12,'2026-07-10','2026-09-30',8,'تحليل متطلبات، أساسيات API، أمن المعلومات','بيئة بنكية منظمة، شهادة معتمدة',1,CURDATE()),
(8,'متدرب امتثال ومخاطر','دعم إدارة الامتثال في مراجعة السياسات والإجراءات.','قانون أو محاسبة',8,'2026-09-15','2026-11-10',3,'دقة في التوثيق، Excel','تعرف على الأنظمة المصرفية السعودية',1,CURDATE()),
(9,'متدرب إدارة منشآت ومشتريات','متابعة عقود الصيانة ومخزون المعدات الطبية.','إدارة مستشفيات أو هندسة صناعية',10,'2026-08-01','2026-10-10',4,'مهارات تنظيمية، أساسيات ERP','تجربة في قطاع الرعاية الصحية الخاص',1,CURDATE()),
(9,'متدرب تجربة مستخدم في التطبيقات الصحية','تحسين واجهات تطبيقات المرضى والمواعيد.','تصميم جرافيكي أو HCI',10,'2026-07-01','2026-09-09',2,'Figma، بحث مستخدم','تعاون مع فرق طبية وتقنية',1,CURDATE()),
(10,'متدرب علوم بيانات حكومية','معالجة مجموعات بيانات وطنية ضمن سياسات الخصوصية والحوكمة.','علوم بيانات أو إحصاء',14,'2026-08-01','2026-11-15',5,'Python، SQL، أخلاقيات البيانات','شهادة من جهة وطنية، تعامل مع بيانات حقيقية مجهزة',1,CURDATE()),
(10,'متدرب أمن سيبراني واستجابة حوادث','مراقبة التنبيهات وتوثيق الحوادث ضمن فريق SOC.','أمن سيبراني أو شبكات',12,'2026-07-15','2026-10-07',4,'Linux، أساسيات الشبكات، الرغبة في التوثيق','تدريب مع مختبرات محاكاة',1,CURDATE()),
(11,'متدرب شبكات الجيل الخامس','دعم فرق الاختبار الميداني لجودة التغطية.','هندسة اتصالات أو حاسب',14,'2026-08-20','2026-12-05',5,'رياضيات، أساسيات RF','تجربة ميدانية مع فرق تقنية',1,CURDATE()),
(12,'متدرب برامج مهارات المستقبل','تنظيم ورش عمل ودعم منصات التعلم الإلكتروني للمستفيدين.','إدارة أعمال أو إعلام',8,'2026-09-01','2026-10-27',10,'مهارات عرض، عمل جماعي، لغة إنجليزية','شهادة تطوعية معتمدة، شبكة علاقات واسعة',1,CURDATE()),
(14,'متدرب توقيع إلكتروني وهوية رقمية','دعم تكامل خدمات الثقة الإلكترونية مع الجهات الحكومية.','برمجة أو نظم معلومات',10,'2026-07-01','2026-09-09',4,'Java أو .NET، REST','تعامل مع معايير وطنية للتوثيق',1,CURDATE()),
(14,'متدرب ضمان جودة برمجيات','اختبار تلقائي ويدوي لمنصات التوثيق.','هندسة برمجيات أو حاسب',12,'2026-08-10','2026-10-31',3,'JUnit، Selenium، توثيق الأعطال','بيئة Agile مع فرق متعددة التخصصات',1,CURDATE()),
(1,'متدرب تجربة عميل رقمية','تحليل سلوك المستخدم في قنوات STC الرقمية واقتراح تحسينات.','تسويق رقمي أو نظم معلومات',8,'2026-09-01','2026-10-27',5,'Google Analytics أساسي، عرض نتائج','مكافأة شهرية، إرشاد من مدير منتج',1,CURDATE()),
(2,'متدرب هندسة عمليات لوجستية','تحسين سلاسل التوريد في مواقع أرامكو.','هندسة صناعية أو لوجستيات',16,'2026-07-01','2026-10-22',4,'تحليل بيانات، مهارات عرض','سكن للمؤهلين، بدلات ميدانية',1,CURDATE()),
(3,'متدرب واجهات خدمات حكومية','تصميم وتطوير واجهات الويب لبوابات الخدمات.','تصميم واجهات أو برمجة ويب',10,'2026-08-01','2026-10-10',4,'HTML/CSS، WCAG أساسي','مكافأة، فرصة نشر أعمال في بيئة حكومية',1,CURDATE()),
(4,'متدرب سياسات تنظيمية تقنية','البحث في أفضل الممارسات العالمية لسياسات البيانات والحوسبة السحابية.','قانون تقني أو نظم معلومات',8,'2026-09-01','2026-10-27',3,'كتابة تقارير، مصادر موثوقة','شهادة من الوزارة، التعامل مع خبراء',1,CURDATE()),
(5,'متدرب تحليلات شبكة الجوال','تحليل مؤشرات الأداء ودعم قرارات تخطيط الشبكة.','هندسة اتصالات أو علوم بيانات',12,'2026-08-15','2026-11-07',4,'Python، SQL، مهارات عرض','مكافأة، تدريب على أدوات الناقل',1,CURDATE()),
(6,'متدرب ابتكار مستدام','مشاريع تقليل البصمة الكربونية في المجمع الصناعي.','هندسة بيئة أو كيمياء',12,'2026-09-01','2026-11-24',3,'قراءة أبحاث، Excel','مشاركة في مبادرات الاستدامة العالمية للشركة',1,CURDATE()),
(8,'متدرب ذكاء اصطناعي للخدمات المصرفية','نماذج تعلم آلي لكشف الاحتيال المالي التجريبي.','ذكاء اصطناعي أو علوم بيانات',14,'2026-07-01','2026-10-10',2,'Python، مكتبات sklearn','وصول لبيانات مجهّزة للتجربة',1,CURDATE()),
(11,'متدرب دعم فني متقدم للأعمال','حل تذاكر المؤسسات الكبرى وتحليل الأعطال المتكررة.','علوم حاسب أو شبكات',8,'2026-09-01','2026-10-27',6,'مهارات تواصل، أساسيات VoIP','تدريب على منصات إدارة الخدمة',1,CURDATE()),
(12,'متدرب إنتاج محتوى تعليمي رقمي','إعداد مواد بصرية ونصية لمساقات قصيرة عبر الإنترنت.','إعلام رقمي أو تعليم',8,'2026-07-15','2026-09-09',5,'أدوات تحرير فيديو أو تصميم','محفظة أعمال معتمدة من المؤسسة',1,CURDATE()),
(7,'متدرب سلامة وصحة مهنية','جولات تفتيشية وتوثيق المخاطر في مواقع العمل.','هندسة سلامة أو صحة مهنية',10,'2026-09-01','2026-11-10',4,'OSHA أساسي، التوثيق بالصور','زيارات ميدانية بإشراف مرخص',1,CURDATE()),
(10,'متدرب حوكمة بيانات','إعداد قواميس بيانات وتوثيق مصادرها وفق السياسات الوطنية.','نظم معلومات أو مكتبات',8,'2026-08-01','2026-09-24',3,'تنظيم معرفة، Excel','تعاون مع جهات حكومية متعددة',1,CURDATE());

INSERT INTO TRAINING_APPLICATION (student_id, opportunity_id, application_date, status, cover_letter) VALUES
(1,1,CURDATE(),'accepted', 'لدي خبرة في PHP وJavaScript من مشاريع أكاديمية ومشروع التخرج. أرغب بالتدريب في STC.'),
(2,2,CURDATE(),'pending',  'أهتم بأمن المعلومات وخبرتي في الشبكات والـ Linux.'),
(3,1,CURDATE(),'pending',  'لدي شغف بتطوير الويب وتصميم الواجهات. أرغب في المساهمة في مشاريع STC.'),
(4,4,CURDATE(),'rejected', 'اهتمامي بالتحول الرقمي ومهارات التوثيق تناسب عمل الوزارة.'),
(5,2,CURDATE(),'accepted', 'دراستي في أمن المعلومات وخبرتي في الشبكات تؤهلني لهذه الفرصة.'),
(6,5,CURDATE(),'pending',  'PHP وMySQL هما تخصصي الأساسي. أريد تطبيق مهاراتي في موبايلي.'),
(7,1,CURDATE(),'pending',  'خبرتي في JavaScript تتناسب مع متطلبات الفرصة في STC.'),
(8,3,CURDATE(),'pending',  'أطور تطبيقات Android وأرغب في خدمات حكومية ذكية.'),
(9,19,CURDATE(),'pending',  'خلفيتي في هندسة الحاسب تساعدني على تحليل سلوك المستخدم في القنوات الرقمية.'),
(10,20,CURDATE(),'accepted', 'أدرس نظم معلومات وأرغب في تحسين سلاسل التوريد.'),
(11,21,CURDATE(),'pending',  'تخصصي تسويق رقمي مع اهتمام بالوصولية في الواجهات الحكومية.'),
(12,22,CURDATE(),'pending',  'أبحث عن فرصة في السياسات التنظيمية للتقنية.'),
(13,23,CURDATE(),'pending',  'أجمع بين Python وSQL لتحليل مؤشرات الشبكة.'),
(14,7,CURDATE(),'pending',  'درست جيولوجيا وأرغب في العمل الميداني في التعدين.'),
(15,12,CURDATE(),'pending',  'تصميم تجربة المستخدم في القطاع الصحي يهمّني.'),
(16,13,CURDATE(),'pending',  'أعمل على مشاريع Python لمعالجة البيانات.'),
(17,14,CURDATE(),'rejected', 'أرغب في الانضمام لفريق الاستجابة للحوادث الأمنية.'),
(18,16,CURDATE(),'pending',  'إدارة الأعمال مع شغف بتنظيم الفعاليات التعليمية.'),
(19,17,CURDATE(),'pending',  'أطور بـ Java وأرغب في خدمات الثقة الإلكترونية.'),
(20,25,CURDATE(),'pending',  'أستخدم sklearn في مشاريع الجامعة وأرغب في كشف الاحتيال.'),
(21,26,CURDATE(),'pending',  'خبرة في حل تذاكر الدعم الفني للمختبرات.'),
(22,27,CURDATE(),'pending',  'أنتج محتوى تعليمياً قصيراً على منصات التواصل.'),
(23,28,CURDATE(),'pending',  'هندستي الصناعية تدعمني في السلامة والصحة المهنية.'),
(24,29,CURDATE(),'pending',  'أرغب في تنظيم قواميس البيانات والتوثيق.'),
(11,8,CURDATE(),'pending',  'متابعة أنظمة SCADA بإشراف المهندسين.'),
(12,9,CURDATE(),'pending',  'اهتمام بالتحول الرقمي في القطاع المصرفي.'),
(13,10,CURDATE(),'accepted', 'خلفية قانونية مع دقة في التوثيق.'),
(14,11,CURDATE(),'pending',  'إدارة المشتريات والمخزون في بيئة طبية.'),
(15,6,CURDATE(),'pending',  'هندسة كيميائية مع اهتمام بالمجمعات الصناعية.'),
(16,24,CURDATE(),'pending',  'الاستدامة والبيئة من أهدافي المهنية.'),
(17,30,CURDATE(),'pending',  'سلامة المهندسين والتفتيش الميداني.'),
(9,15,CURDATE(),'pending',  'Figma وبحث المستخدم لتحسين مواعيد المرضى.'),
(20,11,CURDATE(),'pending',  'ERP والمخزون في المستشفيات.'),
(21,31,CURDATE(),'pending',  'تنظيم المعرفة وقواميس البيانات.'),
(22,18,CURDATE(),'pending',  'اختبار تلقائي وJUnit في مشاريع الجامعة.'),
(23,5,CURDATE(),'pending',  'PHP وREST في مشروع تخرجي.'),
(24,4,CURDATE(),'pending',  'التحول الرقمي الحكومي وتوثيق المتطلبات.');

INSERT INTO REVIEW (student_id, organization_id, rating, review_text, review_date, is_verified) VALUES
(1,1,5,'تجربة استثنائية في STC. فريق محترف ومهام حقيقية. تعلمت الكثير وأنصح به بشدة.',CURDATE(),1),
(5,1,4,'بيئة عمل ممتازة وفريق داعم. مهام أمن المعلومات كانت تحدياً رائعاً.',CURDATE(),1),
(2,2,5,'تدريب أرامكو منظم للغاية. التوجيه المهني واضح والمهام ذات صلة بالتخصص.',CURDATE(),1),
(3,3,4,'شركة علم تمنحك إحساساً بأثر العمل على خدمات المواطنين.',CURDATE(),1),
(4,4,5,'تجربة حكومية منظمة مع خبراء محترفين في التحول الرقمي.',CURDATE(),1),
(6,5,4,'موبايلي بيئة ديناميكية، تعلمت العمل ضمن فريق تقني متكامل.',CURDATE(),1),
(9,8,5,'البنك الأهلي يقدم برنامجاً واضحاً في التحول الرقمي والامتثال.',CURDATE(),1),
(10,10,5,'سدايا منصة مثيرة للعمل على بيانات وطنية بمسؤولية عالية.',CURDATE(),1),
(11,11,4,'زين توفر تجربة ميدانية جيدة في شبكات الاتصالات.',CURDATE(),1),
(12,12,5,'مؤسسة مسك تفتح آفاقاً في التعليم والمهارات الرقمية.',CURDATE(),1),
(14,7,4,'معادن: عمل ميداني مكثف مع تعلم عملي في الموقع.',CURDATE(),1),
(15,9,5,'مستشفيات الحبيب بيئة احترافية في التطبيقات الصحية.',CURDATE(),1),
(16,14,4,'ثقة للخدمات الرقمية تمنحك تعاملاً مع معايير وطنية حقيقية.',CURDATE(),1),
(17,6,5,'سابك تبرز في التنظيم والسلامة والابتكار الصناعي.',CURDATE(),1),
(18,5,4,'برنامج موبايلي متوازن بين التعلم والتطبيق.',CURDATE(),1);

UPDATE TRAINING_OPPORTUNITY SET training_mode = 'حضوري' WHERE opportunity_id IN (1,2,6,7,8,10,11,12,14,15,16,17,18,20,23,24,26,28,30);
UPDATE TRAINING_OPPORTUNITY SET training_mode = 'عن بعد' WHERE opportunity_id IN (3,5,13,19,22,25,27,29,31);
UPDATE TRAINING_OPPORTUNITY SET training_mode = 'هجين' WHERE opportunity_id IN (4,9,21);

UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'قسم الموارد البشرية' WHERE organization_id = 1;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'إدارة التدريب' WHERE organization_id = 2;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'مسؤول التوظيف' WHERE organization_id = 3;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'مكتب التدريب' WHERE organization_id = 4;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'الموارد البشرية' WHERE organization_id = 5;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'إدارة المواهب' WHERE organization_id = 6;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'قسم التدريب الميداني' WHERE organization_id = 7;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'إدارة التوظيف والتطوير' WHERE organization_id = 8;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'مكتب الخريجين' WHERE organization_id = 9;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'إدارة الموارد البشرية' WHERE organization_id = 10;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'قسم التدريب التقني' WHERE organization_id = 11;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'برامج التطوير' WHERE organization_id = 12;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'مكتب الاستقطاب' WHERE organization_id = 13;
UPDATE TRAINING_ORGANIZATION SET contact_person_name = 'إدارة التعلم والأداء' WHERE organization_id = 14;
