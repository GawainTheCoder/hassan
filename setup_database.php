<?php
// setup_database.php - Run this script once to set up your database

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP has no password, change if needed
$port = 3306;
$dbname = 'portfolio_db';

try {
    // Connect to MySQL server without database selection
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' created or already exists.<br>";
    
    // Select the database
    $pdo->exec("USE `$dbname`");
    
    // Import schema file
    $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
    $pdo->exec($schema);
    echo "Database tables created successfully.<br>";

    // Create uploads directory if it doesn't exist
    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
        echo "Uploads directory created.<br>";
    }
    
    // Create sample admin user
    $username = 'admin';
    $password = 'admin123'; // Change this in production
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$username, $password_hash]);
        echo "Admin user created. Username: '$username', Password: '$password'<br>";
    } else {
        echo "Admin user already exists.<br>";
    }
    
    echo "<p>Setup completed successfully! <a href='index.php'>Go to home page</a></p>";
    
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?> 