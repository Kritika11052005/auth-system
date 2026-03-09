<?php
/**
 * Auth Middleware - Validates Bearer Token against Redis
 */

function authenticate() {
    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null);

    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized: No token provided"]);
        exit;
    }

    $token = substr($authHeader, 7);
    $redis = require __DIR__ . '/../config/redis.php';

    $userId = $redis->get("session:" . $token);

    if (!$userId) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized: Invalid or expired token"]);
        exit;
    }

    return $userId;
}

// Ensure apache_request_headers exists for Nginx/other servers
if (!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
}
