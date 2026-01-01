<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

try {
    // Fetch active alerts not expired
    $stmt = $pdo->query("SELECT * FROM alerts WHERE is_active = 1 AND (expires_at > NOW() OR expires_at IS NULL) ORDER BY created_at DESC");
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $alerts]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
