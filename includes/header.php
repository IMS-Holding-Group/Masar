<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/functions.php';
$b = app_base();
$ut = $_SESSION['user_type'] ?? '';
?>
    <header class="header" id="site-header">
        <div class="header-inner">
            <a href="<?php echo $b; ?>index.php" class="logo-link">
                <img src="<?php echo $b; ?>assets/images/logo.png" alt="مسار" class="logo-img">
            </a>
            <button type="button" class="menu-toggle" id="menu-toggle" aria-expanded="false" aria-controls="main-nav" aria-label="فتح القائمة">
                <span class="menu-toggle__lines" aria-hidden="true">
                    <span class="menu-toggle__bar"></span>
                    <span class="menu-toggle__bar"></span>
                </span>
            </button>
            <nav class="nav" id="main-nav">
                <a href="<?php echo $b; ?>index.php" class="nav-link">الرئيسية</a>
                <a href="<?php echo $b; ?>opportunities.php" class="nav-link">الفرص</a>
                <?php if (!isLoggedIn()) : ?>
                    <a href="<?php echo $b; ?>register.php" class="nav-link nav-link--register">تسجيل جديد</a>
                    <a href="<?php echo $b; ?>login.php" class="nav-link">تسجيل الدخول</a>
                <?php else : ?>
                    <?php if ($ut === 'student') : ?>
                        <a href="<?php echo $b; ?>student/dashboard.php" class="nav-link">لوحة التحكم</a>
                        <a href="<?php echo $b; ?>student/profile.php" class="nav-link nav-link--profile">الملف الشخصي</a>
                    <?php elseif ($ut === 'company') : ?>
                        <a href="<?php echo $b; ?>company/dashboard.php" class="nav-link">لوحة التحكم</a>
                        <a href="<?php echo $b; ?>company/profile.php" class="nav-link nav-link--profile">الملف التعريفي</a>
                    <?php elseif ($ut === 'admin') : ?>
                        <a href="<?php echo $b; ?>admin/dashboard.php" class="nav-link">لوحة التحكم</a>
                    <?php endif; ?>
                    <a href="<?php echo $b; ?>logout.php" class="nav-link">خروج</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
