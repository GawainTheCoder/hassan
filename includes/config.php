<?php
// includes/config.php
$db_path = __DIR__ . '/../data/portfolio.sqlite';
$db_dir = dirname($db_path);

// Create the data directory if it doesn't exist
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

try {
    // Connect to SQLite database
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Enable foreign keys in SQLite
    $pdo->exec('PRAGMA foreign_keys = ON;');
    
    // Create tables if they don't exist
    $schema = file_get_contents(__DIR__ . '/../sql/schema.sql');
    $pdo->exec($schema);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
