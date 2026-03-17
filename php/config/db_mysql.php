<?php
// php/config/db_mysql.php

$host = getenv('MYSQL_HOST') ?: 'sql12.freesqldatabase.com';
$port = (int)(getenv('MYSQL_PORT') ?: 3306);
$db   = getenv('MYSQL_DB')   ?: 'sql12820348';
$user = getenv('MYSQL_USER') ?: 'sql12820348';
$pass = getenv('MYSQL_PASS') ?: 'HD8tSTJnjk';

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
