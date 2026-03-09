<?php
require_once __DIR__ . '/env_loader.php';

// php/config/db_mysql.php
define('MYSQL_HOST', $_ENV['MYSQL_HOST'] ?? getenv('MYSQL_HOST') ?: 'sql12.freesqldatabase.com');
define('MYSQL_PORT', (int)($_ENV['MYSQL_PORT'] ?? getenv('MYSQL_PORT') ?: 3306));
define('MYSQL_DB',   $_ENV['MYSQL_DB']   ?? getenv('MYSQL_DB')   ?: 'sql12819356');
define('MYSQL_USER', $_ENV['MYSQL_USER'] ?? getenv('MYSQL_USER') ?: 'sql12819356');
define('MYSQL_PASS', $_ENV['MYSQL_PASS'] ?? getenv('MYSQL_PASS') ?: 'm9RFHXdpaF');

$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, MYSQL_PORT);

if ($mysqli->connect_error) {
    die(json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

// Return the connection
return $mysqli;
