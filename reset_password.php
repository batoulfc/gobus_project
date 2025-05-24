<?php
header('Content-Type: application/json');
$host = "localhost";
$db = "gobus";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'فشل الاتصال بقاعدة البيانات']);
    exit;
}

$email = $_POST['email'] ?? '';

if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'البريد الإلكتروني مطلوب']);
    exit;
}

function findUserTable($conn, $email) {
    $tables = ['students', 'drivers', 'admins'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT id FROM $table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            return $table;
        }
    }
    return null;
}

$table = findUserTable($conn, $email);

if ($table) {
    
    echo json_encode(['status' => 'success', 'message' => "تم إرسال رابط إعادة تعيين كلمة المرور إلى بريد $table"]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'البريد الإلكتروني غير موجود في أي حساب']);
}
?>
