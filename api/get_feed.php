<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$category = $_GET['category'] ?? '';

try {
    $sql = "SELECT n.*, c.name as category_name, c.slug as category_slug
            FROM news n 
            JOIN categories c ON n.category_id = c.category_id 
            WHERE n.status = 'published'";
    
    if ($category === 'feed') {
        // Students "For You" feed (Academic + Notice)
        $sql .= " AND c.slug IN ('academic', 'notice')";
    }
    
    $sql .= " ORDER BY n.created_at DESC LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as Assoc for JSON
    
    echo json_encode(['status' => 'success', 'data' => $news]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
