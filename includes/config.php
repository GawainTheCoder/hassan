<?php
// includes/config.php
$host = 'localhost';
$dbname = 'portfolio_db';
$user = 'root';
$pass = ''; // Default XAMPP has no password, change if needed
$port = 3306;

try {
    // Connect to MySQL database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Make sure database tables have all the required columns
    if (function_exists('ensure_tables_exist')) {
        ensure_tables_exist($pdo);
    }
    
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
