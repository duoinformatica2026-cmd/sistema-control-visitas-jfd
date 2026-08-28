<?php
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'control_visitas';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';

if (strpos($host, ':') !== false) {
    [$host, $port] = explode(':', $host, 2);
}

$conn = @mysqli_connect($host, $user, $pass, $db, (int)$port);
if (!$conn) {
    http_response_code(500);
    die('No se pudo conectar a MySQL. Verifique las variables de Railway. Detalle: ' . htmlspecialchars(mysqli_connect_error()));
}
mysqli_set_charset($conn, 'utf8mb4');
date_default_timezone_set('America/Tegucigalpa');
@mysqli_query($conn, "SET time_zone = '-06:00'");

if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }
function hasRole(string $rol): bool { return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === $rol; }
function requireLogin(string $base = '..'): void {
    if (!isLoggedIn()) { header('Location: ' . $base . '/login.php'); exit; }
}
function requireRole(string $rol, string $base = '..'): void {
    requireLogin($base);
    if (!hasRole($rol)) { header('Location: ' . $base . '/index.php'); exit; }
}
