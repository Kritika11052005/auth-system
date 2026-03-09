<?php
/**
 * Profile Backend - GET/POST for MongoDB profile data
 */

header('Content-Type: application/json');

// Auth middleware check
require_once __DIR__ . '/middleware/auth.php';
$userId = authenticate();

// Load connections
$mysqli = require __DIR__ . '/config/db_mysql.php';
$mongo = require __DIR__ . '/config/db_mongo.php';
$profileCollection = $mongo['collection'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 1. Fetch account info from MySQL
    $stmt = $mysqli->prepare("SELECT name, email, username FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 2. Fetch profile details from MongoDB
    $profile = $profileCollection->findOne(['user_id' => (int)$userId]);

    echo json_encode([
        "success" => true,
        "data" => [
            "account" => $user,
            "profile" => $profile
        ]
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Upsert into MongoDB
    $profileData = [
        'user_id' => (int)$userId,
        'age' => trim($_POST['age'] ?? ''),
        'dob' => trim($_POST['dob'] ?? ''),
        'contact' => trim($_POST['contact'] ?? ''),
        'gender' => trim($_POST['gender'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'country' => trim($_POST['country'] ?? ''),
        'bio' => trim($_POST['bio'] ?? ''),
        'signature' => $_POST['signature'] ?? '',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $result = $profileCollection->updateOne(
        ['user_id' => (int)$userId],
        ['$set' => $profileData],
        ['upsert' => true]
    );

    if ($result->getUpsertedCount() > 0 || $result->getModifiedCount() >= 0) {
        echo json_encode([
            "success" => true,
            "message" => "Profile updated effectively",
            "updated_at" => $profileData['updated_at']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes made or update failed"]);
    }
    exit;
}

$mysqli->close();
