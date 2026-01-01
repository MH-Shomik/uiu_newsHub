<?php
require_once 'includes/db_connect.php';

try {
    // Clear existing active alerts to avoid clutter
    $pdo->exec("DELETE FROM alerts");

    $alerts = [
        [
            'title' => 'Campus Road Blocked',
            'message' => 'Due to ongoing construction, the main entrance road is temporarily blocked. Please use the south gate.',
            'type' => 'traffic',
            'severity' => 'warning',
            'is_active' => 1
        ],
        [
            'title' => 'Heavy Rain Forecast',
            'message' => 'Heavy rainfall is expected this afternoon. Students are advised to carry umbrellas and leave early if possible.',
            'type' => 'weather',
            'severity' => 'info',
            'is_active' => 1
        ],
        [
            'title' => 'Server Maintenance',
            'message' => 'The student portal will be down for maintenance from 2 AM to 4 AM tonight.',
            'type' => 'system',
            'severity' => 'info',
            'is_active' => 1
        ],
        [
            'title' => 'Emergency: Fire Drill',
            'message' => 'A mandatory fire drill will be conducted at 11:00 AM in Building B.',
            'type' => 'security',
            'severity' => 'danger', 
            'is_active' => 1
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO alerts (title, message, type, severity, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

    foreach ($alerts as $alert) {
        $stmt->execute([
            $alert['title'],
            $alert['message'],
            $alert['type'],
            $alert['severity'],
            $alert['is_active']
        ]);
    }

    echo "✅ Dummy alerts seeded successfully!<br>";
    echo "Inserted: " . count($alerts) . " alerts.";

} catch (PDOException $e) {
    echo "❌ Error seeding alerts: " . $e->getMessage();
}
?>
