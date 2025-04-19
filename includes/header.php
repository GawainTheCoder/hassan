<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portify</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
<header>
    <div class="container">
        <h1><a href="index.php" class="site-title">Portify</a></h1>
        <nav>
            <a href="index.php">Home</a>
            <?php
            if (isset($_SESSION['user_id'])) {
                echo ' | <a href="user_portfolio.php">My Portfolio</a>';
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    echo ' | <a href="admin.php">Admin Dashboard</a>';
                }
                echo ' | <a href="logout.php">Logout</a>';
            } else {
                echo ' | <a href="login.php">Login</a>';
                echo ' | <a href="register.php">Register</a>';
            }
            ?>
        </nav>
    </div>
</header>
<main class="container">
