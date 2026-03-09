<?php
/**
 * Register Backend - Inserts user into MySQL
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$mysqli = require __DIR__ . '/config/db_mysql.php';

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

// Basic Validation
if (empty($name) || empty($username) || empty($email) || empty($phone) || empty($password)) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format", "field" => "email"]);
    exit;
}

// Check if username or email already exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Username or Email already taken"]);
    $stmt->close();
    exit;
}
$stmt->close();

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert User
$stmt = $mysqli->prepare("INSERT INTO users (name, username, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $username, $email, $phone, $password_hash);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Registration successful! Redirecting to login..."]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
