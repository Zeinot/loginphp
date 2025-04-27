<?php
require_once 'includes/functions.php';

// Get categories
$categories = getCategories();

// Get search params
$search = isset($_GET['search']) ? $_GET['search'] : null;
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get posts with pagination
$postsPerPage = 12;
$posts = getPosts($page, $postsPerPage, $categoryId, $search);

// Get category name if filtering by category
$categoryName = '';
if ($categoryId) {
    foreach ($categories as $category) {
        if ($category['id'] == $categoryId) {
            $categoryName = $category['name'];
            break;
        }
    }
}

// Page title
$pageTitle = 'Home';
if ($categoryName) {
    $pageTitle = $categoryName . ' Listings';
} elseif ($search) {
    $pageTitle = 'Search Results for "' . $search . '"';
}
?>

<?php 
// Add custom meta tags for the homepage
$customMeta = <<<HTML
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta name="description" content="ListItAll - The modern marketplace to buy, sell, and connect with your local community">
<!-- Preload key assets -->
<link rel="preload" href="/assets/css/modern-styles.css" as="style">
<link rel="preload" href="/assets/js/modern-scripts.js" as="script">
<link rel="preload" href="/assets/images/pattern.svg" as="image" type="image/svg+xml">
<!-- Modern styles -->
<link href="/assets/css/modern-styles.css" rel="stylesheet">
<!-- Google Fonts with display swap -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<!-- Font Awesome with defer -->
<script src="https://kit.fontawesome.com/3b5edb5916.js" crossorigin="anonymous" defer></script>
HTML;

include 'includes/header.php'; 
?>

<!-- Modern Hero Section with Immersive Elements -->
<section class="hero position-relative overflow-hidden mb-5 p-0">
    <!-- Floating background elements -->
    <div class="floating-element floating-element-1"></div>
    <div class="floating-element floating-element-2"></div>
    <div class="floating-element floating-element-3"></div>
    
    <!-- Hero content container -->
    <div class="container position-relative z-index-1 py-5">
        <div class="row align-items-center min-vh-75 py-5">
            <div class="col-lg-6 text-center text-lg-start fade-in-up">
                <span class="badge d-inline-block mb-3">DISCOVER · CONNECT · TRADE</span>
                <h1 class="hero-title text-white mb-4">Find Everything <br>You Need <span class="text-warning">Locally</span></h1>
                <p class="hero-subtitle mb-4">The modern marketplace to buy, sell, and connect with your community. Post listings for free and discover everything you need nearby.</p>
                
                <!-- CTA Buttons with enhanced design -->
                <div class="d-flex flex-column flex-sm-row gap-3 mb-5">
                    <a href="/posts/create.php" class="btn btn-light btn-lg fw-bold position-relative overflow-hidden">
                        <span class="btn-flash"></span>
                        <i class="fas fa-plus-circle me-2"></i> Post an Ad
                    </a>
                    <a href="/how-it-works.php" class="btn btn-outline-light btn-lg position-relative overflow-hidden">
                        <span class="btn-glow"></span>
                        <i class="fas fa-info-circle me-2"></i> How It Works
                    </a>
                </div>
                
                <!-- Stats with enhanced visual design -->
                <div class="hero-stats d-flex flex-wrap justify-content-center justify-content-lg-start gap-4 mt-2">
                    <div class="hero-stat">
                        <div class="d-flex align-items-center">
                            <div class="text-warning me-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <div class="hero-stat-value">4.8</div>
                        </div>
                        <div class="hero-stat-label">User Rating</div>
                    </div>
                    <div class="hero-stat">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users text-info me-2"></i>
                            <div class="hero-stat-value">10K+</div>
                        </div>
                        <div class="hero-stat-label">Active Users</div>
                    </div>
                    <div class="hero-stat">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <div class="hero-stat-value">100%</div>
                        </div>
                        <div class="hero-stat-label">Secure Trades</div>
                    </div>
                </div>
            </div>
            
            <!-- Modern illustration with animation -->
            <div class="col-lg-6 d-none d-lg-block text-center fade-in-up" style="animation-delay: 0.3s;">
                <div class="position-relative">
                    <img src="/assets/images/modern-marketplace.svg" alt="ListItAll Marketplace" class="img-fluid hero-illustration">
                    <!-- Animated highlights -->
                    <div class="highlight-circle highlight-1"></div>
                    <div class="highlight-circle highlight-2"></div>
                    <div class="highlight-circle highlight-3"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Curved bottom shape divider -->
    <div class="position-absolute bottom-0 start-0 w-100 overflow-hidden" style="height: 50px; transform: translateY(1px);">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 150" preserveAspectRatio="none" style="height: 100%; width: 100%;">
            <path fill="#f9fafc" d="M0,96L80,85.3C160,75,320,53,480,64C640,75,800,117,960,117.3C1120,117,1280,75,1360,53.3L1440,32L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>
        </svg>
    </div>
</section>

<!-- Categories Section with Modern Design -->
<section class="categories-section py-5">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="badge bg-primary px-3 py-2 mb-2">EXPLORE</span>
            <h2 class="display-text">Browse Categories</h2>
            <p class="lead text-muted col-md-8 mx-auto">Find exactly what you're looking for in our diverse marketplace categories</p>
        </div>
        
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-4 stagger-fade-in">
            <?php foreach($categories as $category): ?>
                <div class="col">
                    <a href="/index.php?category=<?php echo $category['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none">
                        <div class="card h-100 text-center category-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                                <?php
                                // Define icon for each category
                                $icon = 'tag';
                                $gradient = 'linear-gradient(135deg, #4e73df 0%, #6e8aef 100%)';
                                
                                switch($category['slug']) {
                                    case 'for-sale':
                                        $icon = 'tag';
                                        $gradient = 'linear-gradient(135deg, #4e73df 0%, #6e8aef 100%)';
                                        break;
                                    case 'housing':
                                        $icon = 'home';
                                        $gradient = 'linear-gradient(135deg, #1cc88a 0%, #25e8a7 100%)';
                                        break;
                                    case 'jobs':
                                        $icon = 'briefcase';
                                        $gradient = 'linear-gradient(135deg, #f6c23e 0%, #ffda85 100%)';
                                        break;
                                    case 'services':
                                        $icon = 'tools';
                                        $gradient = 'linear-gradient(135deg, #e74a3b 0%, #ff6a5e 100%)';
                                        break;
                                    case 'community':
                                        $icon = 'users';
                                        $gradient = 'linear-gradient(135deg, #36b9cc 0%, #5de6fc 100%)';
                                        break;
                                    case 'vehicles':
                                        $icon = 'car';
                                        $gradient = 'linear-gradient(135deg, #6f42c1 0%, #9a6fef 100%)';
                                        break;
                                }
                                ?>
                                <div class="category-icon-container" style="background: <?php echo $gradient; ?>">
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                </div>
                                <h5 class="card-title mt-4 mb-2"><?php echo $category['name']; ?></h5>
                                <p class="text-muted mb-0 small">Browse items <i class="fas fa-arrow-right ms-1 small"></i></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Mobile horizontal scroll for categories -->
        <div class="d-md-none mt-4 text-center">
            <div class="d-flex justify-content-center">
                <button class="btn btn-sm btn-outline-primary mx-1 scroll-btn scroll-left"><i class="fas fa-chevron-left"></i></button>
                <button class="btn btn-sm btn-outline-primary mx-1 scroll-btn scroll-right"><i class="fas fa-chevron-right"></i></button>
            </div>
            <p class="small text-muted mt-2">Swipe to see more categories</p>
        </div>
    </div>
</section>

<!-- Modern Listings Section -->
<section id="listings" class="listings-section py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 fade-in-up">
            <div>
                <span class="badge bg-primary px-3 py-2 mb-2">
                    <?php if ($categoryName): ?>CATEGORY: <?php echo strtoupper($categoryName); ?>
                    <?php elseif ($search): ?>SEARCH RESULTS
                    <?php else: ?>DISCOVER
                    <?php endif; ?>
                </span>
                <h2 class="display-text">
                    <?php if ($categoryName): ?>
                        <?php echo $categoryName; ?> Listings
                    <?php elseif ($search): ?>
                        Results for "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        Recent Listings
                    <?php endif; ?>
                </h2>
            </div>
            
            <?php if ($categoryId || $search): ?>
                <a href="/index.php" class="btn btn-outline-primary mt-3 mt-md-0 align-self-start">
                    <i class="fas fa-undo me-1"></i> Clear Filters
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($posts)): ?>
            <div class="alert p-5 text-center rounded-4 shadow-sm fade-in-up">
                <div class="py-4">
                    <div class="d-inline-block p-3 bg-light rounded-circle mb-3">
                        <i class="fas fa-search fa-2x text-primary"></i>
                    </div>
                    <h3 class="mb-3">No listings found</h3>
                    <p class="lead text-muted mb-4">Try adjusting your search criteria or check back later for new listings.</p>
                    <a href="/index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i> View All Listings
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 stagger-fade-in">
                <?php foreach ($posts as $post): ?>
                    <div class="col">
                        <div class="listing-card h-100">
                            <div class="listing-img-container loading">
                                <?php if (!empty($post['primary_image'])): ?>
                                    <img src="/<?php echo $post['primary_image']; ?>" class="listing-img" alt="<?php echo $post['title']; ?>" loading="lazy">
                                <?php else: ?>
                                    <img src="/assets/images/placeholder.png" class="listing-img" alt="No image available" loading="lazy">
                                <?php endif; ?>
                                
                                <!-- Shimmer loading effect -->
                                <div class="shimmer-effect"></div>
                                
                                <?php if (!empty($post['price'])): ?>
                                    <div class="listing-price">$<?php echo number_format($post['price'], 2); ?></div>
                                <?php endif; ?>
                                
                                <?php
                                // Get first category from post categories
                                $categoryName = '';
                                $categorySlug = '';
                                foreach ($categories as $category) {
                                    if (in_array($category['id'], explode(',', $post['category_id'] ?? ''))) {
                                        $categoryName = $category['name'];
                                        $categorySlug = $category['slug'];
                                        break;
                                    }
                                }
                                
                                // Define category badge color based on slug
                                $categoryColor = 'rgba(0, 0, 0, 0.6)';
                                switch($categorySlug) {
                                    case 'for-sale': $categoryColor = 'rgba(78, 115, 223, 0.8)'; break;
                                    case 'housing': $categoryColor = 'rgba(28, 200, 138, 0.8)'; break;
                                    case 'jobs': $categoryColor = 'rgba(246, 194, 62, 0.8)'; break;
                                    case 'services': $categoryColor = 'rgba(231, 74, 59, 0.8)'; break;
                                    case 'community': $categoryColor = 'rgba(54, 185, 204, 0.8)'; break;
                                    case 'vehicles': $categoryColor = 'rgba(111, 66, 193, 0.8)'; break;
                                }
                                
                                if (!empty($categoryName)): ?>
                                    <div class="listing-category" style="background-color: <?php echo $categoryColor; ?>">
                                        <?php echo $categoryName; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="listing-actions">
                                    <button class="action-btn favorite-btn" title="Add to favorites">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <button class="action-btn share-btn" title="Share listing">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="listing-meta d-flex justify-content-between mb-2">
                                    <span class="meta-item"><i class="far fa-user text-primary me-1"></i> <?php echo $post['author']; ?></span>
                                    <span class="meta-item"><i class="far fa-clock text-primary me-1"></i> <?php echo formatDate($post['created_at']); ?></span>
                                </div>
                                <h5 class="listing-title">
                                    <a href="/posts/view.php?id=<?php echo $post['id']; ?>" class="text-decoration-none"><?php echo $post['title']; ?></a>
                                </h5>
                                <p class="listing-description"><?php echo substr(strip_tags($post['description']), 0, 100); ?>...</p>
                                <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                                    <a href="/posts/view.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">
                                        <span>View Details</span> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <?php if (!empty($post['location'])): ?>
                                        <div class="location-badge">
                                            <i class="fas fa-map-marker-alt me-1"></i><?php echo $post['location']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php
                    // Simple pagination, can be improved with total count from DB
                    $prevPage = max(1, $page - 1);
                    $nextPage = $page + 1;
                    $hasNextPage = count($posts) == $postsPerPage;
                    
                    // Build the query string for pagination links
                    $queryParams = [];
                    if ($categoryId) {
                        $queryParams['category'] = $categoryId;
                    }
                    if ($search) {
                        $queryParams['search'] = $search;
                    }
                    
                    $queryString = '';
                    if (!empty($queryParams)) {
                        $queryString = '&' . http_build_query($queryParams);
                    }
                    ?>
                    
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $prevPage . $queryString; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
                    <?php if ($hasNextPage): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $nextPage . $queryString; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <a class="page-link" href="#" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works Section - Ultra Modern 2025 Design -->
<section class="how-it-works py-5 my-5 position-relative">
    <!-- Decorative background elements -->
    <div class="how-it-works-bg-element how-it-works-bg-1"></div>
    <div class="how-it-works-bg-element how-it-works-bg-2"></div>
    
    <div class="container position-relative">
        <div class="text-center mb-5 fade-in-up">
            <span class="super-badge">THE PROCESS</span>
            <h2 class="mega-title">How It Works</h2>
            <p class="lead-text mx-auto">Join thousands already using ListItAll to connect, buy, and sell in your community</p>
        </div>
        
        <div class="process-timeline">
            <!-- Process steps with interactive 3D cards -->
            <div class="process-step" data-step="1">
                <div class="process-card">
                    <div class="process-card-inner">
                        <div class="process-card-front">
                            <div class="process-icon-wrapper">
                                <div class="process-icon-3d">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                            </div>
                            <h3 class="process-title">Create Your Account</h3>
                            <p class="process-text">Quick 60-second signup with social login options</p>
                            <div class="process-hover-hint pulse-animation">
                                <i class="fas fa-hand-pointer"></i>
                                <span>Hover to learn more</span>
                            </div>
                        </div>
                        <div class="process-card-back">
                            <div class="process-back-content">
                                <h4>Benefits of Joining</h4>
                                <ul class="process-features">
                                    <li><i class="fas fa-check-circle"></i> Free personal account</li>
                                    <li><i class="fas fa-check-circle"></i> Secure messaging system</li>
                                    <li><i class="fas fa-check-circle"></i> Save favorite listings</li>
                                    <li><i class="fas fa-check-circle"></i> Get personalized alerts</li>
                                </ul>
                                <a href="/auth/register.php" class="btn-3d btn-glow">
                                    <span>Register Now</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="process-connector"></div>
            </div>

            <div class="process-step" data-step="2">
                <div class="process-card">
                    <div class="process-card-inner">
                        <div class="process-card-front">
                            <div class="process-icon-wrapper">
                                <div class="process-icon-3d">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                            <h3 class="process-title">Post or Browse</h3>
                            <p class="process-text">Create listings or find what you need in seconds</p>
                            <div class="process-hover-hint pulse-animation">
                                <i class="fas fa-hand-pointer"></i>
                                <span>Hover to learn more</span>
                            </div>
                        </div>
                        <div class="process-card-back">
                            <div class="process-back-content">
                                <h4>Powerful Features</h4>
                                <ul class="process-features">
                                    <li><i class="fas fa-check-circle"></i> AI-enhanced photo uploads</li>
                                    <li><i class="fas fa-check-circle"></i> Location-based search</li>
                                    <li><i class="fas fa-check-circle"></i> Advanced filtering</li>
                                    <li><i class="fas fa-check-circle"></i> Real-time notifications</li>
                                </ul>
                                <a href="/posts/create.php" class="btn-3d btn-glow">
                                    <span>Post Listing</span>
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="process-connector"></div>
            </div>

            <div class="process-step" data-step="3">
                <div class="process-card">
                    <div class="process-card-inner">
                        <div class="process-card-front">
                            <div class="process-icon-wrapper">
                                <div class="process-icon-3d">
                                    <i class="fas fa-handshake"></i>
                                </div>
                            </div>
                            <h3 class="process-title">Connect & Complete</h3>
                            <p class="process-text">Secure messaging and transaction support</p>
                            <div class="process-hover-hint pulse-animation">
                                <i class="fas fa-hand-pointer"></i>
                                <span>Hover to learn more</span>
                            </div>
                        </div>
                        <div class="process-card-back">
                            <div class="process-back-content">
                                <h4>Safe Transactions</h4>
                                <ul class="process-features">
                                    <li><i class="fas fa-check-circle"></i> Verified user profiles</li>
                                    <li><i class="fas fa-check-circle"></i> Secure messaging</li>
                                    <li><i class="fas fa-check-circle"></i> Transaction protection</li>
                                    <li><i class="fas fa-check-circle"></i> Community ratings</li>
                                </ul>
                                <a href="/safety-tips.php" class="btn-3d btn-glow">
                                    <span>Safety Guide</span>
                                    <i class="fas fa-shield-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile version (simplified) -->
        <div class="d-md-none mt-5">
            <div class="mobile-process-steps">
                <div class="mobile-process-step">
                    <div class="mobile-process-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4>1. Create Account</h4>
                    <p>Sign up in seconds to start your journey</p>
                    <a href="/auth/register.php" class="btn btn-gradient-primary">Register</a>
                </div>
                
                <div class="mobile-process-step">
                    <div class="mobile-process-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <h4>2. Post or Browse</h4>
                    <p>Create listings or find what you need</p>
                    <a href="/posts/create.php" class="btn btn-gradient-success">Post Ad</a>
                </div>
                
                <div class="mobile-process-step">
                    <div class="mobile-process-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>3. Connect & Complete</h4>
                    <p>Safe and secure transactions</p>
                    <a href="/safety-tips.php" class="btn btn-gradient-info">Safety Tips</a>
                </div>
            </div>
        </div>
    </div>
</section>
        
        <!-- Testimonials Section - 2025 Design With Interactive Elements -->
        <div class="testimonials-container position-relative my-5 py-5">
            <!-- Decorative elements -->
            <div class="testimonial-bg-blob testimonial-blob-1"></div>
            <div class="testimonial-bg-blob testimonial-blob-2"></div>
            
            <div class="container position-relative">
                <div class="testimonial-header text-center mb-5 fade-in-up">
                    <span class="super-badge">SUCCESS STORIES</span>
                    <h2 class="mega-title">Our Community Speaks</h2>
                    <div class="rating-summary">
                        <div class="giant-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="rating-text">4.9 out of 5 from over 10,000 reviews</p>
                    </div>
                </div>
                
                <!-- Testimonial Carousel with 3D Cards -->
                <div class="testimonial-carousel">
                    <div class="testimonial-track">
                        <!-- Testimonial Card 1 -->
                        <div class="testimonial-card" data-rating="5">
                            <div class="testimonial-card-inner">
                                <div class="testimonial-content">
                                    <div class="testimonial-stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"I sold my bicycle within 24 hours of posting it on ListItAll. The process was incredibly smooth, and the smart location matching helped me find a buyer just blocks away!"</p>
                                    <div class="testimonial-category-tag">Seller • For Sale</div>
                                </div>
                                <div class="testimonial-author">
                                    <div class="testimonial-author-avatar bg-gradient-primary">MB</div>
                                    <div class="testimonial-author-info">
                                        <h5>Michael Blanchard</h5>
                                        <p>San Francisco, CA</p>
                                    </div>
                                    <div class="testimonial-verified-badge" title="Verified User">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonial Card 2 -->
                        <div class="testimonial-card featured-testimonial" data-rating="5">
                            <div class="testimonial-card-inner">
                                <div class="testimonial-content">
                                    <div class="testimonial-stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"Found the perfect apartment through ListItAll with the advanced filtering options. The 3D virtual tour feature saved me so much time as I could see the space before visiting in person."</p>
                                    <div class="testimonial-category-tag">Buyer • Housing</div>
                                </div>
                                <div class="testimonial-author">
                                    <div class="testimonial-author-avatar bg-gradient-success">SJ</div>
                                    <div class="testimonial-author-info">
                                        <h5>Sarah Johnson</h5>
                                        <p>Chicago, IL</p>
                                    </div>
                                    <div class="testimonial-verified-badge" title="Verified User">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonial Card 3 -->
                        <div class="testimonial-card" data-rating="4.5">
                            <div class="testimonial-card-inner">
                                <div class="testimonial-content">
                                    <div class="testimonial-stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <p class="testimonial-quote">"I've been using ListItAll for my handyman business for over a year now. The platform has helped me connect with local clients and grow my service area by 300%!"</p>
                                    <div class="testimonial-category-tag">Seller • Services</div>
                                </div>
                                <div class="testimonial-author">
                                    <div class="testimonial-author-avatar bg-gradient-info">RT</div>
                                    <div class="testimonial-author-info">
                                        <h5>Robert Thompson</h5>
                                        <p>Austin, TX</p>
                                    </div>
                                    <div class="testimonial-verified-badge" title="Verified User">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonial Card 4 -->
                        <div class="testimonial-card" data-rating="5">
                            <div class="testimonial-card-inner">
                                <div class="testimonial-content">
                                    <div class="testimonial-stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"As a photographer, I needed a place to showcase my portfolio and find clients. ListItAll's visual-focused design has helped me attract more photography jobs than any other platform."</p>
                                    <div class="testimonial-category-tag">Seller • Jobs</div>
                                </div>
                                <div class="testimonial-author">
                                    <div class="testimonial-author-avatar bg-gradient-warning">AK</div>
                                    <div class="testimonial-author-info">
                                        <h5>Alisha Kim</h5>
                                        <p>Portland, OR</p>
                                    </div>
                                    <div class="testimonial-verified-badge" title="Verified User">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carousel Controls -->
                    <div class="testimonial-controls">
                        <button class="testimonial-control prev-testimonial">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="testimonial-indicators">
                            <span class="testimonial-indicator active"></span>
                            <span class="testimonial-indicator"></span>
                            <span class="testimonial-indicator"></span>
                            <span class="testimonial-indicator"></span>
                        </div>
                        <button class="testimonial-control next-testimonial">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Success Stats -->
                <div class="stats-container mt-5 fade-in-up">
                    <div class="row g-4 text-center">
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-value">1M+</div>
                                <div class="stat-label">Active Users</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-value">500K+</div>
                                <div class="stat-label">Monthly Listings</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-value">24hr</div>
                                <div class="stat-label">Avg. Sale Time</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-value">98%</div>
                                <div class="stat-label">Satisfaction</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <div class="text-center mt-5">
                    <a href="/auth/register.php" class="btn-3d btn-xl btn-glow">
                        <span>Join Our Community</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
