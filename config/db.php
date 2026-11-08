<?php
// Function to load .env variables without needing external libraries
function loadEnv($path)
{
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

// Load .env from project root
loadEnv(__DIR__ . '/../.env');

// Check if running locally (XAMPP) or live online (InfinityFree)
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1']);

if ($is_local) {
    // Localhost XAMPP Settings
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'travel_planner_db';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
} else {
    // InfinityFree Live Online Settings
    $host = getenv('DB_HOST') ?: 'sql203.infinityfree.com';
    $dbname = getenv('DB_NAME') ?: 'if0_43326827_travel_db';
    $username = getenv('DB_USER') ?: 'if0_43326827';
    // IMPORTANT: Replace the text inside quotes below with your real InfinityFree password!
    $password = getenv('DB_PASS') ?: 'Kimescobido29';
}

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}