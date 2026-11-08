<?php
// Function to load .env variables safely without throwing permission errors
function loadEnv($path)
{
    if (!@file_exists($path) || !@is_readable($path)) {
        return;
    }
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) return;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Safely load local .env if readable
loadEnv(__DIR__ . '/../.env');
loadEnv('/etc/secrets/.env');

// Read from Render environment variables, $_SERVER, $_ENV, or getenv
$host = getenv('DB_HOST') ?: ($_SERVER['DB_HOST'] ?? ($_ENV['DB_HOST'] ?? 'mysql-d262d7d-kimescobido30-1dde.j.aivencloud.com'));
$port = getenv('DB_PORT') ?: ($_SERVER['DB_PORT'] ?? ($_ENV['DB_PORT'] ?? '26777'));
$dbname = getenv('DB_NAME') ?: ($_SERVER['DB_NAME'] ?? ($_ENV['DB_NAME'] ?? 'defaultdb'));
$username = getenv('DB_USER') ?: ($_SERVER['DB_USER'] ?? ($_ENV['DB_USER'] ?? 'avnadmin'));
$password = getenv('DB_PASS') ?: ($_SERVER['DB_PASS'] ?? ($_ENV['DB_PASS'] ?? ''));

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}