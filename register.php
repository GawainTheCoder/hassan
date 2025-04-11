<?php
// register.php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    
    if (empty($username) || empty($password) || empty($confirm)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'user')");
            if ($stmt->execute([$username, $password_hash])) {
                $success = "Registration successful. You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<h2>Register</h2>
<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
<?php endif; ?>
<form action="register.php" method="post">
    <label>Username:</label>
    <input type="text" name="username" required><br>
    <label>Password:</label>
    <input type="password" name="password" required><br>
    <label>Confirm Password:</label>
    <input type="password" name="confirm" required><br>
    <button type="submit">Register</button>
</form>
<p>Already have an account? <a href="login.php">Login here</a>.</p>

<?php include 'includes/footer.php'; ?>
