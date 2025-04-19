<?php
// index.php
// No need for session_start() here, it's handled in the header
include 'includes/config.php';
include 'includes/functions.php';
include 'includes/header.php';

// Hero section - show for everyone
?>
<section class="hero">
    <div class="hero-content">
        <h2>Showcase Your Work</h2>
        <p class="hero-text">Create a stunning portfolio to highlight your skills and projects</p>
        <div class="hero-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user_portfolio.php" class="btn-primary">Manage Your Portfolio</a>
            <?php else: ?>
                <a href="register.php" class="btn-primary">Get Started</a>
                <a href="login.php" class="btn-secondary">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Portfolio Showcase -->
<section class="portfolio-showcase">
    <div class="container">
        <h2>Discover Amazing Portfolios</h2>
        
        <!-- Add debugging button in development mode -->
        <?php if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE): ?>
        <div class="debug-tools">
            <a href="debug.php" class="btn-secondary">Debug Database</a>
        </div>
        <?php endif; ?>
        
        <!-- Filtering and Sorting Controls -->
        <div class="portfolio-controls">
            <div class="portfolio-filters">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Add a view filter for logged-in users -->
                    <select id="view-filter" class="filter-select">
                        <option value="all">All Portfolios</option>
                        <option value="mine">My Portfolio</option>
                    </select>
                <?php endif; ?>

                <select id="category-filter" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="web-design">Web Design</option>
                    <option value="graphic-design">Graphic Design</option>
                    <option value="photography">Photography</option>
                    <option value="illustration">Illustration</option>
                </select>
                
                <input type="text" id="search-filter" class="search-input" placeholder="Search portfolios...">
            </div>
            
            <div class="portfolio-sorting">
                <label for="sort-order">Sort by:</label>
                <select id="sort-order" class="sort-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name-asc">Name (A-Z)</option>
                    <option value="name-desc">Name (Z-A)</option>
                </select>
            </div>
        </div>
        
        <!-- Portfolio Grid -->
        <div class="portfolio-grid" id="portfolio-grid">
            <?php
            // Check if we have real portfolio items to display
            $hasRealItems = false;
            
            // Get all portfolio items regardless of login status
            $stmt = $pdo->prepare("SELECT pi.*, u.username FROM portfolio_items pi 
                                 JOIN users u ON pi.user_id = u.id 
                                 ORDER BY pi.created_at DESC");
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($items) {
                $hasRealItems = true;
                foreach ($items as $item) {
                    // Get category - default to 'web-design' if not set
                    $category = isset($item['category']) && !empty($item['category']) ? 
                               $item['category'] : 'web-design';
                    
                    $isOwner = isset($_SESSION['user_id']) && ($item['user_id'] == $_SESSION['user_id']);
                    ?>
                    <div class="portfolio-item" 
                         data-category="<?= htmlspecialchars($category) ?>" 
                         data-owner="<?= $isOwner ? 'mine' : 'other' ?>"
                         data-timestamp="<?= strtotime($item['created_at']) ?: 0 ?>">
                        <img src="<?= get_portfolio_image($item['image_path']) ?>" 
                             alt="<?= htmlspecialchars($item['title']) ?>" 
                             class="portfolio-item-image">
                        
                        <div class="portfolio-item-content">
                            <div class="portfolio-creator">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($item['username']) ?>&background=random" alt="<?= htmlspecialchars($item['username']) ?>" class="avatar">
                                <span><?= htmlspecialchars($item['username']) ?></span>
                            </div>
                            <h3><?= htmlspecialchars($item['title']) ?></h3>
                            <p class="item-category">Category: <?= htmlspecialchars(get_category_name($category)) ?></p>
                            <p><?= htmlspecialchars(truncate_text($item['description'], 150)) ?></p>
                            
                            <?php if ($isOwner): ?>
                                <div class="portfolio-actions">
                                    <a href="user_portfolio.php?action=edit&id=<?= $item['id'] ?>" class="btn-edit">Edit</a>
                                    <a href="process.php?action=delete&id=<?= $item['id'] ?>" 
                                       onclick="return confirm('Are you sure you want to delete this item?')" 
                                       class="btn-delete">Delete</a>
                                </div>
                            <?php else: ?>
                                <a href="view_portfolio.php?user_id=<?= $item['user_id'] ?>" class="btn-view">View Portfolio</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            }
            
            // If no real items exist in the database, show message
            if (!$hasRealItems):
            ?>
                <!-- Demo Portfolio items code here -->
                <!-- You can remove all demo portfolio items if you want -->
                <div class="portfolio-empty-message">
                    No portfolio items have been added yet. Be the first to showcase your work!
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Load More Button -->
        <div class="load-more-container">
            <button id="load-more" class="btn-primary">Load More</button>
        </div>
    </div>
</section>

<!-- Features section -->
<section class="features">
    <h2>Why Choose Portfolio App?</h2>
    <div class="features-grid">
        <div class="feature-item">
            <div class="feature-icon">📱</div>
            <h3>Responsive Design</h3>
            <p>Your portfolio looks great on any device - desktop, tablet, or mobile</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">🔍</div>
            <h3>SEO Friendly</h3>
            <p>Get discovered by potential clients and employers</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">⚡</div>
            <h3>Easy to Use</h3>
            <p>No technical skills required - build your portfolio in minutes</p>
        </div>
    </div>
</section>

<!-- Testimonials section -->
<section class="testimonials">
    <h2>What Our Users Say</h2>
    <div class="testimonials-container">
        <div class="testimonial">
            <div class="testimonial-content">
                <p>"This platform helped me showcase my work and land my dream job. The interface is intuitive and the designs look professional."</p>
            </div>
            <div class="testimonial-author">
                <p><strong>Sarah Johnson</strong> - Graphic Designer</p>
            </div>
        </div>
        <div class="testimonial">
            <div class="testimonial-content">
                <p>"I've been using Portfolio App for my photography business and it's been a game-changer. My clients love how easy it is to view my work."</p>
            </div>
            <div class="testimonial-author">
                <p><strong>Michael Rodriguez</strong> - Photographer</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA section -->
<section class="cta">
    <h2>Ready to Build Your Portfolio?</h2>
    <p>Join thousands of professionals showcasing their work online</p>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="user_portfolio.php" class="btn-primary">Manage Your Portfolio</a>
    <?php else: ?>
        <a href="register.php" class="btn-primary">Create Your Portfolio</a>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
