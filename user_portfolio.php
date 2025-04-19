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

// Display alerts
if (isset($_SESSION['alert'])) {
    display_alert($_SESSION['alert']['message'], $_SESSION['alert']['type']);
    unset($_SESSION['alert']);
}

echo "<h2>Your Portfolio Dashboard</h2>";

// Get action from URL (add/edit)
$action = isset($_GET['action']) ? $_GET['action'] : 'add';
?>

<?php if ($action === 'add'): ?>
    <h3>Add New Portfolio Item</h3>
    <form action="process.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
        <label>Title:</label>
        <input type="text" name="title" required><br>
        
        <label>Category:</label>
        <select name="category" required>
            <option value="web-design">Web Design</option>
            <option value="graphic-design">Graphic Design</option>
            <option value="photography">Photography</option>
            <option value="illustration">Illustration</option>
        </select><br>
        
        <label>Description:</label>
        <textarea name="description" required></textarea><br>
        
        <label>Image:</label>
        <input type="file" name="image" accept="image/*"><br>
        <p class="help-text">Only image files (JPG, PNG, GIF, etc.) are accepted. Maximum size: 5MB.</p>
        
        <button type="submit">Add Item</button>
    </form>
<?php endif; ?>

<h3>Your Items</h3>
<?php
$stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($items) {
    echo '<div class="portfolio-grid">';
    foreach ($items as $item) {
        ?>
        <div class="portfolio-item" data-category="<?= htmlspecialchars($item['category'] ?? 'web-design') ?>">
            <img src="<?= get_portfolio_image($item['image_path']) ?>" 
                 alt="<?= htmlspecialchars($item['title']) ?>" 
                 class="portfolio-item-image">
            
            <div class="portfolio-item-content">
                <h3><?= htmlspecialchars($item['title']) ?></h3>
                <p class="item-category">Category: <?= htmlspecialchars(get_category_name($item['category'] ?? 'web-design')) ?></p>
                <p><?= htmlspecialchars(truncate_text($item['description'], 150)) ?></p>
                
                <div class="portfolio-actions">
                    <a href="process.php?action=edit&id=<?= $item['id'] ?>" class="btn-edit">Edit</a>
                    <a href="process.php?action=delete&id=<?= $item['id'] ?>" 
                       onclick="return confirm('Are you sure you want to delete this item?')" 
                       class="btn-delete">Delete</a>
                </div>
            </div>
        </div>
        <?php
    }
    echo '</div>';
} else {
    echo "<div class='empty-state'>";
    echo "<p>No items found.</p>";
    echo "</div>";
}

include 'includes/footer.php';
?>
