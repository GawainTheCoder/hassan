<?php
// login.php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];  // e.g., "user" or "admin"
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: user_portfolio.php");
            }
            exit;
        } else {
            $error = "Invalid login credentials.";
        }
    }
}

include 'includes/header.php';
?>

<h2>Login</h2>
<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>
<form action="login.php" method="post">
    <label>Username:</label>
    <input type="text" name="username" required><br>
    <label>Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a>.</p>

<?php include 'includes/footer.php'; ?>
