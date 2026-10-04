<?php

/**
 * اسم جلسة خاص بمسار حتى لا تختلط مع مشاريع PHP أخرى على نفس localhost
 * (نفس ملف تعريف الارتباط PHPSESSID كان يسبب اعتبار user_id من تطبيق آخر «مسجّل دخول»).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_name('MASAR_SESSID');
    session_start();
}
