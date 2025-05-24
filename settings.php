<?php
session_start();

// بيانات الاتصال بقاعدة البيانات
$host = 'localhost';
$db = 'gobus';
$user = 'root';      // غيرها لاسم مستخدم قاعدة البيانات عندك
$pass = '';          // غيرها لكلمة مرور قاعدة البيانات عندك

// الاتصال بقاعدة البيانات باستخدام PDO مع ضبط UTF-8
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// تأكد أن المستخدم مسجل الدخول (يوجد إيميل في الجلسة)
if (!isset($_SESSION['email'])) {
    die("Unauthorized access - please login first.");
}

$currentEmail = $_SESSION['email'];

// دالة لتحديد جدول المستخدم (students, drivers, admins)
function getUserTable($pdo, $email) {
    $tables = ['students', 'drivers', 'admins'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            return $table;
        }
    }
    return false;
}

$userTable = getUserTable($pdo, $currentEmail);
if (!$userTable) {
    die("User not found in any user table.");
}

// معالجة تغيير الاسم
if (isset($_POST['name_submit']) && !empty($_POST['name'])) {
    $newName = trim($_POST['name']);
    $stmt = $pdo->prepare("UPDATE $userTable SET name = ? WHERE email = ?");
    $stmt->execute([$newName, $currentEmail]);
    echo "Name updated successfully.";
    $_SESSION['name'] = $newName; // لو كنت تخزن الاسم في الجلسة
    exit;
}

// معالجة تغيير الإيميل
if (isset($_POST['email_submit']) && !empty($_POST['email'])) {
    $newEmail = trim($_POST['email']);
    // تحقق أن الإيميل الجديد غير مستخدم في أي جدول
    $tables = ['students', 'drivers', 'admins'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT email FROM $table WHERE email = ?");
        $stmt->execute([$newEmail]);
        if ($stmt->rowCount() > 0) {
            die("This email is already taken.");
        }
    }
    // حدث الإيميل في الجدول الحالي
    $stmt = $pdo->prepare("UPDATE $userTable SET email = ? WHERE email = ?");
    $stmt->execute([$newEmail, $currentEmail]);
    // حدّث الجلسة
    $_SESSION['email'] = $newEmail;
    echo "Email updated successfully.";
    exit;
}

// معالجة تغيير كلمة السر
if (isset($_POST['password_submit']) && !empty($_POST['password'])) {
    $newPassword = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // تشفير كلمة السر
    $stmt = $pdo->prepare("UPDATE $userTable SET password = ? WHERE email = ?");
    $stmt->execute([$newPassword, $currentEmail]);
    echo "Password updated successfully.";
    exit;
}

// لو لم يتم إرسال بيانات صحيحة
echo "No valid data submitted.";
