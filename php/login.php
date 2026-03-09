<?php
/**
 * Login Backend - Validates credentials, creates Redis session, returns token
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$mysqli = require __DIR__ . '/config/db_mysql.php';
$redis = require __DIR__ . '/config/redis.php';

$identifier = trim($_POST['identifier'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) && $_POST['remember'] === 'true';

if (empty($identifier) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Please enter both credentials"]);
    exit;
}

// Timing-attack safe pattern: always complete the work
$dummy_hash = '$2y$10$nOuIs5kJ7naTuTFkPy1ve.S.U/s1/q8sM.S6K8.U.vS.S.S.S.S.S'; // Placeholder hash

$stmt = $mysqli->prepare("SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$valid = false;
$userId = null;

if ($user) {
    if (password_verify($password, $user['password_hash'])) {
        $valid = true;
        $userId = $user['id'];
    }
} else {
    // Perform verification against dummy hash even if user not found to prevent timing attacks
    password_verify($password, $dummy_hash);
}

if ($valid) {
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    
    // Session TTL (24h default or 30 days if remember)
    $ttl = $remember ? 2592000 : (int)(getenv('SESSION_TTL') ?: 86400);
    
    // Store in Redis
    $redis->set("session:" . $token, $userId, $ttl);

    echo json_encode([
        "success" => true,
        "message" => "Login successful. Welcome back!",
        "token" => $token,
        "user" => [
            "id" => $userId,
            "username" => $user['username']
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid username or password"]);
}

$stmt->close();
$mysqli->close();
