<?php
$databaseUrl = getenv('DATABASE_URL');
$parsedUrl = parse_url($databaseUrl);

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $parsedUrl['host'], $parsedUrl['port'], ltrim($parsedUrl['path'], '/'));
$user = $parsedUrl['user'];
$pass = $parsedUrl['pass'];

try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "Conexión a base de datos OK\n";
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}