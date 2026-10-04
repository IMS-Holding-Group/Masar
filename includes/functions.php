<?php

/**
 * بادئة روابط التطبيق تنتهي بشرطة مائلة.
 * في المتصفح: URL مطلق لجذر المشروع (نفس المضيف والمنفذ) — يعمل مع localhost:3000 والمجلدات الفرعية.
 * بدون HTTP_HOST (مثل CLI): مسار نسبي بالـ ../ إلى جذر المشروع.
 */
function app_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    if (!empty($_SERVER['HTTP_HOST'])) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];

        $scriptPath = '';
        foreach ([$_SERVER['SCRIPT_NAME'] ?? '', $_SERVER['PHP_SELF'] ?? ''] as $candidate) {
            $c = str_replace('\\', '/', (string) $candidate);
            if ($c !== '' && substr($c, -4) === '.php') {
                $scriptPath = $c;
                break;
            }
        }
        if ($scriptPath === '' && !empty($_SERVER['REQUEST_URI'])) {
            $p = parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $p = is_string($p) ? str_replace('\\', '/', $p) : '';
            if ($p !== '' && substr($p, -4) === '.php') {
                $scriptPath = $p;
            }
        }

        $dir = $scriptPath !== '' ? dirname($scriptPath) : '';
        $dir = str_replace('\\', '/', (string) $dir);
        if ($dir === '.' || $dir === '') {
            $p = isset($_SERVER['REQUEST_URI']) ? parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
            $p = is_string($p) ? str_replace('\\', '/', $p) : '';
            if ($p !== '' && substr($p, -4) === '.php') {
                $dir = dirname($p);
                $dir = str_replace('\\', '/', $dir);
            }
        }

        if ($dir !== '/' && $dir !== '.' && $dir !== '' && $dir !== '\\') {
            $parts = array_values(array_filter(explode('/', $dir), static function ($seg) {
                return $seg !== '';
            }));
            if ($parts !== [] && in_array(end($parts), ['student', 'company', 'admin'], true)) {
                array_pop($parts);
            }
            if ($parts !== []) {
                $base = $scheme . '://' . $host . '/' . implode('/', $parts) . '/';
                return $base;
            }
        }

        $base = $scheme . '://' . $host . '/';
        return $base;
    }

    $appRoot = realpath(__DIR__ . '/..');
    $script = $_SERVER['SCRIPT_FILENAME'] ?? '';
    $scriptDir = $script !== '' ? realpath(dirname($script)) : false;
    if ($appRoot === false || $scriptDir === false) {
        $base = '';
        return $base;
    }
    $appRoot = str_replace('\\', '/', $appRoot);
    $scriptDir = str_replace('\\', '/', $scriptDir);
    if ($scriptDir === $appRoot) {
        $base = '';
        return $base;
    }
    if (strpos($scriptDir, $appRoot . '/') === 0) {
        $rel = substr($scriptDir, strlen($appRoot) + 1);
        $depth = $rel === '' ? 0 : substr_count($rel, '/') + 1;
        $base = str_repeat('../', $depth);
        return $base;
    }
    $base = '';
    return $base;
}

/**
 * إصدار أصول الواجهة لكسر كاش المتصفح — يُقرأ من config/assets_version.php (عدّل الرقم هناك).
 */
function assets_version(): int
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $file = __DIR__ . '/../config/assets_version.php';
    if (is_file($file)) {
        $n = include $file;
        $cached = max(1, (int) $n);
    } else {
        $cached = 1;
    }
    return $cached;
}

/**
 * رابط نسبي من جذر المشروع (مثل assets/css/style.css) مع ?v= الإصدار.
 */
function asset_url(string $relativePath): string
{
    $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
    $url = app_base() . $relativePath;
    $sep = strpos($url, '?') !== false ? '&' : '?';

    return $url . $sep . 'v=' . assets_version();
}

// تنظيف المدخلات من XSS
function clean($val)
{
    return htmlspecialchars(strip_tags(trim((string) $val)), ENT_QUOTES, 'UTF-8');
}

// إعادة التوجيه
function redirect($url)
{
    header('Location: ' . $url);
    exit();
}

// هل المستخدم مسجّل دخول؟ (يتطلب نوعاً معروفاً حتى لا تكفي قيمة user_id من جلسة أجنبية)
function isLoggedIn()
{
    if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
        return false;
    }
    $t = $_SESSION['user_type'] ?? '';
    return $t === 'student' || $t === 'company' || $t === 'admin';
}

// التحقق من الدور وإعادة التوجيه إن لم يكن مناسباً
function requireRole($role)
{
    if (!isLoggedIn() || ($_SESSION['user_type'] ?? '') !== $role) {
        redirect(app_base() . 'index.php');
    }
}

// تعيين رسالة Flash
function setFlash($type, $msg)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $msg];
}

// جلب رسالة Flash وحذفها
function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// تنسيق التاريخ
function fDate($d)
{
    return $d ? date('Y/m/d', strtotime((string) $d)) : '---';
}

// عرض نجوم التقييم
function starsHtml($r)
{
    $h = '';
    $r = (int) $r;
    for ($i = 1; $i <= 5; $i++) {
        $h .= ($i <= $r ? '★' : '☆');
    }
    return $h;
}

// CSRF token
function csrfToken()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfCheck()
{
    if (!isset($_POST['csrf'], $_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string) $_POST['csrf'])) {
        die('طلب غير صالح.');
    }
}

// رابط اختياري (لينكدإن، جيثب، إلخ) — فارغ أو URL صالح
function optionalUrl($val)
{
    $v = trim((string) $val);
    if ($v === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $v)) {
        $v = 'https://' . ltrim($v, '/');
    }
    return filter_var($v, FILTER_VALIDATE_URL) !== false ? $v : null;
}

/**
 * شعار الجهة: روابط رسمية محددة لبعض الجهات، وإلا أيقونة نطاق الموقع.
 *
 * @param int|null $organizationId معرّف TRAINING_ORGANIZATION (للتطابق مع بذور masar.sql: 3 علم، 4 الوزارة، 5 موبايلي، 7 معادن، 8 الأهلي، 9 الحبيب، 10 سدايا، 14 ثقة)
 */
function org_logo_src(?string $website, ?int $organizationId = null): string
{
    static $customByOrgId = [
        3 => 'https://www.alwatan.com.sa/uploads/images/2022/01/02/760039.jpeg',
        4 => 'https://www.almowaten.net/wp-content/uploads/2019/11/951123.jpg',
        5 => 'https://etisalangy.com/wp-content/uploads/2022/06/%D8%AA%D9%81%D8%A7%D8%B5%D9%8A%D9%84-%D8%A8%D8%A7%D9%82%D8%A9-65-%D9%85%D9%88%D8%A8%D8%A7%D9%8A%D9%84%D9%8A.jpg.jpg',
        7 => 'https://archi-tent.ru/wp-content/themes/arhi/images/service-logo.png',
        8 => 'https://www.almowaten.net/wp-content/uploads/2021/06/%D8%A7%D9%84%D8%A8%D9%86%D9%83-%D8%A7%D9%84%D8%A3%D9%87%D9%84%D9%8A-%D8%A7%D9%84%D8%B3%D8%B9%D9%88%D8%AF%D9%8A.jpg',
        9 => 'https://forbesme-prestaging-media.s3.us-east-2.amazonaws.com/lists/uploads/2023/03/07073946/DR.Sulaiman-1.jpg',
        10 => 'https://makkahnewspaper.com/uploads/images/2024/12/02/1751594.jpeg',
        14 => 'https://fastly.4sqi.net/img/general/600x600/18125358_g9HZUfyGCBmjQYvbXRn4V-Nwl2n7HNEi867kumIBrpM.jpg',
    ];

    if ($organizationId !== null && $organizationId > 0 && isset($customByOrgId[$organizationId])) {
        return $customByOrgId[$organizationId];
    }

    $u = trim((string) $website);
    if ($u !== '') {
        if (!preg_match('#^https?://#i', $u)) {
            $u = 'https://' . ltrim($u, '/');
        }
        $p = parse_url($u);
        $host = isset($p['host']) ? preg_replace('/^www\./i', '', $p['host']) : '';
        if ($host !== '') {
            return 'https://www.google.com/s2/favicons?domain=' . rawurlencode($host) . '&sz=128';
        }
    }
    return 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=128&h=128&fit=crop&q=75&auto=format';
}

/** لون شارة نمط التدريب */
function training_mode_badge_class(string $mode): string
{
    if ($mode === 'عن بعد') {
        return 'opp-mode-badge opp-mode-badge--remote';
    }
    if ($mode === 'هجين') {
        return 'opp-mode-badge opp-mode-badge--hybrid';
    }
    return 'opp-mode-badge opp-mode-badge--onsite';
}
