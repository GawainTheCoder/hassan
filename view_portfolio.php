<?php
// view_portfolio.php
include 'includes/config.php';
include 'includes/functions.php';
include 'includes/header.php';

// Get user_id from URL
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Fetch user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If no user found, show error
if (!$user) {
    echo '<div class="notice alert-error">User not found.</div>';
    include 'includes/footer.php';
    exit;
}

// Fetch user's portfolio items
$stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="user-profile">
    <div class="user-profile-header">
        <h2><?= htmlspecialchars($user['username']) ?>'s Portfolio</h2>
        <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
    </div>
    
    <?php if ($items): ?>
        <div class="portfolio-grid">
            <?php foreach ($items as $item): ?>
                <div class="portfolio-item">
                    <img src="<?= get_portfolio_image($item['image_path']) ?>" 
                         alt="<?= htmlspecialchars($item['title']) ?>" 
                         class="portfolio-item-image">
                    
                    <div class="portfolio-item-content">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                        <p class="item-date">Posted on: <?= date('M d, Y', strtotime($item['created_at'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>This user hasn't added any portfolio items yet.</p>
        </div>
    <?php endif; ?>
    
    <div class="back-link">
        <a href="index.php">&larr; Back to all portfolios</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 