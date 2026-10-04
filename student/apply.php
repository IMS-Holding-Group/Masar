<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_base() . 'opportunities.php');
}

csrfCheck();

$oppId = (int) ($_POST['opportunity_id'] ?? 0);
$cover = clean($_POST['cover_letter'] ?? '');
$sid = (int) $_SESSION['user_id'];
$b = app_base();

if ($oppId < 1) {
    setFlash('error', 'بيانات التقديم غير كاملة.');
    redirect($b . 'opportunities.php');
}

$chkOpp = $pdo->prepare('SELECT opportunity_id FROM TRAINING_OPPORTUNITY WHERE opportunity_id = ? AND is_active = 1 LIMIT 1');
$chkOpp->execute([$oppId]);
if (!$chkOpp->fetch()) {
    setFlash('error', 'الفرصة غير متاحة.');
    redirect($b . 'opportunities.php');
}

$chk = $pdo->prepare('SELECT application_id FROM TRAINING_APPLICATION WHERE student_id = ? AND opportunity_id = ? LIMIT 1');
$chk->execute([$sid, $oppId]);
if ($chk->fetch()) {
    setFlash('error', 'سبق التقديم على هذه الفرصة.');
    redirect($b . 'opportunity_details.php?id=' . $oppId);
}

$cvPath = null;
$cvStmt = $pdo->prepare('SELECT cv_path FROM STUDENT WHERE student_id = ? LIMIT 1');
$cvStmt->execute([$sid]);
$stRow = $cvStmt->fetch(PDO::FETCH_ASSOC);
if ($stRow && !empty($stRow['cv_path'])) {
    $cvPath = $stRow['cv_path'];
}

$coverDb = $cover === '' ? null : $cover;
$ins = $pdo->prepare('INSERT INTO TRAINING_APPLICATION (student_id, opportunity_id, application_date, status, cover_letter, cv_path) VALUES (?,?,CURDATE(),\'pending\',?,?)');
$ins->execute([$sid, $oppId, $coverDb, $cvPath]);

setFlash('success', 'تم إرسال طلب التقديم بنجاح. يمكنك متابعة الحالة من «طلباتي».');
redirect($b . 'opportunity_details.php?id=' . $oppId);
