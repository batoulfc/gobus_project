<?php

$config = [
    'host' => 'localhost',
    'db'   => 'gobus',
    'user' => 'root',
    'pass' => ''
];


try {
    $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage()]));
}


function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


function isEmailUnique($conn, $email) {
    $tables = ['students', 'drivers', 'admins'];
    
    foreach ($tables as $table) {
        $sql = "SELECT email FROM $table WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false;
        }
    }
    return true;
}


try {
    
    $role = validateInput($_POST['role'] ?? '');
    $name = validateInput($_POST['name'] ?? '');
    $email = validateInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    
    if (empty($role) || empty($name) || empty($email) || empty($password)) {
        throw new Exception("All fields are required");
    }

   
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    
    if (!isEmailUnique($conn, $email)) {
        throw new Exception("Email already exists");
    }

    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    
    $validRoles = [
        'Student' => 'student.html',
        'Driver' => 'driver.html',
        'Admin' => 'admin.html'
    ];

    
    if (!array_key_exists($role, $validRoles)) {
        throw new Exception("Invalid role");
    }

   
    $table = strtolower($role) . 's';
    $sql = "INSERT INTO $table (full_name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if (!$stmt->execute()) {
        throw new Exception("Registration failed: " . $stmt->error);
    }

    
    header("Location: " . $validRoles[$role]);
    exit;

} catch (Exception $e) {
    
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    
    $conn->close();
}
?> 