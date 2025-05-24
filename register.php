<?php
$host = "localhost";
$db = "gobus";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$role = $_POST['role'];
$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // أمان

if ($role === 'Student') {
  $sql = "INSERT INTO students (full_name, email, password) VALUES (?, ?, ?)";
  $redirect = "student.html";
} elseif ($role === 'Driver') {
  $sql = "INSERT INTO drivers (full_name, email, password) VALUES (?, ?, ?)";
  $redirect = "driver.html";
} elseif ($role === 'Admin') {
  $sql = "INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)";
  $redirect = "admin.html";
} else {
  die("Invalid role");
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
  header("Location: $redirect");
  exit;
} else {
  echo "Error: " . $stmt->error;
}

$conn->close();
?>
