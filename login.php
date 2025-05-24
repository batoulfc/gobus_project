<?php
session_start();

$host = "localhost";
$db = "gobus";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    die("All fields are required.");
}

function findUser($conn, $email) {
    $tables = ['students', 'drivers', 'admins'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $user['table'] = $table;
            return $user;
        }
    }
    return null;
}

$user = findUser($conn, $email);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['table'];

    $redirect = match ($user['table']) {
        'students' => 'student.html',
        'drivers' => 'driver.html',
        'admins'  => 'admin.html',
        default   => 'index.html',
    };

    header("Location: $redirect");
    exit;
} else {
    // فشل تسجيل الدخول، أعد المستخدم للصفحة مع رسالة
    header("Location: login.html?error=1");
    exit;
}
?>
