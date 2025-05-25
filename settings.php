<?php
session_start();
$host = 'localhost';
$db = 'gobus';
$user = 'root';      
$pass = '';          

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}


if (!isset($_SESSION['email'])) {
    die("Unauthorized access - please login first.");
}

$currentEmail = $_SESSION['email'];


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


if (isset($_POST['name_submit']) && !empty($_POST['name'])) {
    $newName = trim($_POST['name']);
    $stmt = $pdo->prepare("UPDATE $userTable SET name = ? WHERE email = ?");
    $stmt->execute([$newName, $currentEmail]);
    echo "Name updated successfully.";
    $_SESSION['name'] = $newName; 
    exit;
}


if (isset($_POST['email_submit']) && !empty($_POST['email'])) {
    $newEmail = trim($_POST['email']);
    
    $tables = ['students', 'drivers', 'admins'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT email FROM $table WHERE email = ?");
        $stmt->execute([$newEmail]);
        if ($stmt->rowCount() > 0) {
            die("This email is already taken.");
        }
    }
    
    $stmt = $pdo->prepare("UPDATE $userTable SET email = ? WHERE email = ?");
    $stmt->execute([$newEmail, $currentEmail]);
    
    $_SESSION['email'] = $newEmail;
    echo "Email updated successfully.";
    exit;
}


if (isset($_POST['password_submit']) && !empty($_POST['password'])) {
    $newPassword = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // تشفير كلمة السر
    $stmt = $pdo->prepare("UPDATE $userTable SET password = ? WHERE email = ?");
    $stmt->execute([$newPassword, $currentEmail]);
    echo "Password updated successfully.";
    exit;
}


echo "No valid data submitted.";
