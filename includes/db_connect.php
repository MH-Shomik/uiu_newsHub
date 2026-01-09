<?php
$host = 'localhost';
$db_name = 'uiu_news_system';
$username = 'root';
$password = ''; // Default XAMPP password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // In production, log this error instead of showing it
    die("Database Connection Failed: " . $e->getMessage());
}

// Global specific helper function
if (!function_exists('logActivity')) {
    function logActivity($pdo, $user_id, $action, $details) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $action, $details, $ip]);
        } catch (PDOException $e) {
            // silent fail
        }
    }
}
?>
