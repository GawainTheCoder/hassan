<?php
// index.php
// No need for session_start() here, it's handled in the header
include 'includes/config.php';
include 'includes/functions.php';
include 'includes/header.php';
?>

<?php if (isset($_SESSION['user_id'])): ?>
    <h2>Your Portfolio</h2>
    
    <?php
    // Retrieve portfolio items for the logged in user
    $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <?php if ($items): ?>
        <div class="portfolio-grid">
            <?php foreach ($items as $item): ?>
                <div class="portfolio-item">
                    <img src="<?= get_portfolio_image($item['image_path']) ?>" 
                         alt="<?= htmlspecialchars($item['title']) ?>" 
                         class="portfolio-item-image">
                    
                    <div class="portfolio-item-content">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p><?= htmlspecialchars(truncate_text($item['description'], 150)) ?></p>
                        
                        <div class="portfolio-actions">
                            <a href="user_portfolio.php?action=edit&id=<?= $item['id'] ?>" class="btn-edit">Edit</a>
                            <a href="process.php?action=delete&id=<?= $item['id'] ?>" 
                               onclick="return confirm('Are you sure you want to delete this item?')" 
                               class="btn-delete">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>You haven't added any portfolio items yet.</p>
            <a href="user_portfolio.php?action=add" class="btn-primary">Add Your First Item</a>
        </div>
    <?php endif; ?>
    
<?php else: ?>
    <!-- Hero section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Showcase Your Work</h2>
            <p class="hero-text">Create a stunning portfolio to highlight your skills and projects</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn-primary">Get Started</a>
                <a href="login.php" class="btn-secondary">Sign In</a>
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

    <!-- Example portfolios section -->
    <section class="example-portfolios">
        <h2>Example Portfolios</h2>
        <div class="portfolio-grid">
            <!-- Example 1 -->
            <div class="portfolio-item">
                <img src="https://images.unsplash.com/photo-1587440871875-191322ee64b0?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Web Design Project" class="portfolio-item-image">
                <div class="portfolio-item-content">
                    <h3>Modern E-commerce Website</h3>
                    <p>A responsive e-commerce platform built with modern web technologies and best practices for user experience.</p>
                </div>
            </div>
            
            <!-- Example 2 -->
            <div class="portfolio-item">
                <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Mobile App Design" class="portfolio-item-image">
                <div class="portfolio-item-content">
                    <h3>Fitness Tracking App</h3>
                    <p>A mobile application designed to help users track their fitness goals and maintain healthy habits.</p>
                </div>
            </div>
            
            <!-- Example 3 -->
            <div class="portfolio-item">
                <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Brand Identity" class="portfolio-item-image">
                <div class="portfolio-item-content">
                    <h3>Corporate Brand Identity</h3>
                    <p>Complete brand identity package including logo design, color palette, typography, and brand guidelines.</p>
                </div>
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
        <a href="register.php" class="btn-primary">Create Your Portfolio</a>
    </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
