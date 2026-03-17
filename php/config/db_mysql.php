<?php
// php/config/db_mysql.php

$host = trim(getenv('MYSQL_HOST') ?: 'sql12.freesqldatabase.com');
$port = (int)(trim(getenv('MYSQL_PORT') ?: 3306));
$db   = trim(getenv('MYSQL_DB')   ?: 'sql12820348');
$user = trim(getenv('MYSQL_USER') ?: 'sql12820348');
$pass = trim(getenv('MYSQL_PASS') ?: 'HD8tSTJnjk');

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "DB Error: " . $mysqli->connect_error,
        "host" => $host,
        "user" => $user,
        "db" => $db
    ]));
}
return $mysqli;
