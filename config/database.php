<?php
// Securely read credentials from Render environment variables
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$port = getenv('DB_PORT') ?: '5432';

try {
    // Establish connection to Supabase PostgreSQL database via Pooler
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db;", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $GLOBALS['conn'] = $conn;
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// FIX: Define the e() sanitization function globally
if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
