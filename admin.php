<?php
// admin.php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'includes/header.php';

echo "<h2>Admin Dashboard</h2>";
echo "<p>Welcome, " . htmlspecialchars($_SESSION['username']) . " (Admin).</p>";
?>
<h3>Add New Portfolio Item</h3>
<form action="process.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add">
    <label>Title:</label>
    <input type="text" name="title" required><br>
    <label>Description:</label>
    <textarea name="description" required></textarea><br>
    <!-- Optionally, assign this item to a specific user; if blank, your admin ID is used -->
    <label>User ID (optional):</label>
    <input type="text" name="user_id" placeholder="Leave blank to use your ID"><br>
    <label>Image:</label>
    <input type="file" name="image"><br>
    <button type="submit">Add Item</button>
</form>

<h3>All Portfolio Items</h3>
<?php
$stmt = $pdo->query("SELECT * FROM portfolio_items ORDER BY user_id, id");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($items) {
    echo "<ul>";
    foreach ($items as $item) {
        echo "<li>";
        echo "<strong>" . htmlspecialchars($item['title']) . "</strong> (User ID: " . $item['user_id'] . ")<br>";
        echo htmlspecialchars($item['description']) . "<br>";
        if (!empty($item['image_path'])) {
            echo "<img src='uploads/" . htmlspecialchars($item['image_path']) . "' alt='" . htmlspecialchars($item['title']) . "' style='max-width:200px;'><br>";
        }
        echo "<a href='process.php?action=edit&id=" . $item['id'] . "'>Edit</a> | ";
        echo "<a href='process.php?action=delete&id=" . $item['id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No portfolio items found.</p>";
}

include 'includes/footer.php';
?>
