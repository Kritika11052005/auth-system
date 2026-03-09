<?php
/**
 * Profile Backend - GET/POST for MongoDB profile data
 */

header('Content-Type: application/json');

// Debug: Enable error reporting for this request
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep JSON clean, use logs if possible or return in response

// Custom logger
function debug_log($msg) {
    file_put_contents(__DIR__ . '/debug.log', date('[Y-m-d H:i:s] ') . print_r($msg, true) . "\n", FILE_APPEND);
}

// Auth middleware check
require_once __DIR__ . '/middleware/auth.php';
$userId = authenticate();

// Load connections
$mysqli = require __DIR__ . '/config/db_mysql.php';
$mongo = require __DIR__ . '/config/db_mongo.php';
$profileCollection = $mongo['collection'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 1. Fetch EVERYTHING from MySQL (now the primary profile store for Vercel stability)
    $stmt = $mysqli->prepare("SELECT name, email, username, phone, age, dob, gender, city, country, bio, signature, updated_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode([
        "success" => true,
        "data" => [
            "account" => [
                "name" => $userData['name'],
                "email" => $userData['email'],
                "username" => $userData['username']
            ],
            "profile" => $userData // Includes all fields: age, dob, bio, etc.
        ]
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = trim($_POST['age'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $phone = trim($_POST['phone'] ?? ''); // Changed from contact to phone
    $gender = trim($_POST['gender'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $signature = $_POST['signature'] ?? '';

    // Update MySQL
    $stmt = $mysqli->prepare("UPDATE users SET phone = ?, age = ?, dob = ?, gender = ?, city = ?, country = ?, bio = ?, signature = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssssssssi", $contact, $age, $dob, $gender, $city, $country, $bio, $signature, $userId);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Profile updated effectively",
            "updated_at" => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Database update failed: " . $mysqli->error]);
    }
    $stmt->close();
    exit;
}

$mysqli->close();
