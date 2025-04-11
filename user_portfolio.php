<?php
// user_portfolio.php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'includes/header.php';

echo "<h2>Your Portfolio Dashboard</h2>";
?>
<h3>Add New Portfolio Item</h3>
<form action="process.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add">
    <label>Title:</label>
    <input type="text" name="title" required><br>
    <label>Description:</label>
    <textarea name="description" required></textarea><br>
    <label>Image:</label>
    <input type="file" name="image"><br>
    <button type="submit">Add Item</button>
</form>

<h3>Your Items</h3>
<?php
$stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($items) {
    echo "<ul>";
    foreach ($items as $item) {
        echo "<li>";
        echo "<strong>" . htmlspecialchars($item['title']) . "</strong><br>";
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
    echo "<p>No items found.</p>";
}

include 'includes/footer.php';
?>
