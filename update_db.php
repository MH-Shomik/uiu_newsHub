<?php
require_once 'includes/db_connect.php';

try {
    echo "Updating Database Schema...\n";

    // 1. Add student_id column if it doesn't exist
    // We use a safe check by trying to select it first, or just using ADD COLUMN IF NOT EXISTS (MariaDB 10.2+)
    // Or just raw ALTER and catch exception if exists.
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN student_id VARCHAR(20) UNIQUE DEFAULT NULL AFTER full_name");
        echo " - Added 'student_id' column.\n";
    } catch (PDOException $e) {
        echo " - 'student_id' column likely already exists.\n";
    }

    // 2. Make email nullable
    $pdo->exec("ALTER TABLE users MODIFY COLUMN email VARCHAR(150) NULL");
    echo " - Made 'email' column nullable.\n";

    // 3. Update the existing dummy student with a Student ID
    // User ID 3 is the student in our seed data
    $stmt = $pdo->prepare("UPDATE users SET student_id = '011201111', email = NULL WHERE user_id = 3 AND role = 'student'");
    $stmt->execute();
    echo " - Updated dummy student (ID: 3) with Student ID: 011201111\n";

    // 4. Ensure Admin/Moderator still have emails (optional, they should be untouched)
    
    echo "Database update completed successfully!\n";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?>
