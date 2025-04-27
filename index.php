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

<?php include 'includes/header.php'; ?>

<<<<<<< HEAD
<<<<<<< HEAD
<!-- Hero Section -->
<div class="hero-section mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 hero-content">
                <h1 class="display-4 fw-bold">Welcome to ListItAll</h1>
                <p class="lead">Your modern community marketplace for everything. Buy, sell, or trade locally with ease.</p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-start hero-buttons">
                    <a href="/posts/create.php" class="btn btn-hero-primary me-md-2" type="button"><i class="fas fa-plus-circle me-2"></i>Post an Ad</a>
                    <a href="/how-it-works.php" class="btn btn-hero-secondary" type="button"><i class="fas fa-info-circle me-2"></i>How It Works</a>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0 hero-illustration">
                <!-- Consider replacing with a more modern/relevant illustration -->
                <img src="/assets/images/hero-illustration.svg" class="img-fluid" alt="Marketplace Illustration">
            </div>
        </div>
    </div>
</div>

<!-- Categories Section -->
<section class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Browse Categories</h2>
           
        </div>
=======
=======
>>>>>>> parent of f98d515 (sssaaavvve)
<!-- Hero Section - Enhanced with better visuals and stronger value proposition -->
<section class="hero mb-5 py-5" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; border-radius: 0 0 15px 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-7 text-center text-lg-start">
                <h1 class="display-3 fw-bold mb-3">Find Everything You Need Locally</h1>
                <p class="lead fs-4 mb-4 opacity-90">The modern marketplace to buy, sell, and connect with your community. Post listings for free and find everything you need nearby.</p>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="/posts/create.php" class="btn btn-light btn-lg fw-bold" style="padding: 12px 24px;">
                        <i class="fas fa-plus-circle me-2"></i> Post an Ad
                    </a>
                    <a href="/how-it-works.php" class="btn btn-outline-light btn-lg" style="padding: 12px 24px;">
                        <i class="fas fa-info-circle me-2"></i> How It Works
                    </a>
                </div>
                <div class="mt-4">
                    <div class="d-flex justify-content-center justify-content-lg-start gap-4">
                        <div>
                            <div class="text-warning fs-5"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                            <p class="mb-0 small">4.8/5 Rating</p>
                        </div>
                        <div>
                            <div class="text-white fs-5"><i class="fas fa-users"></i> 10,000+</div>
                            <p class="mb-0 small">Active Users</p>
                        </div>
                        <div>
                            <div class="text-white fs-5"><i class="fas fa-check-circle"></i> 100%</div>
                            <p class="mb-0 small">Secure</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <img src="/assets/images/hero-illustration.svg" alt="ListItAll Marketplace" class="img-fluid mt-4 mt-lg-0" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Browse Categories</h2>
           
        </div>
<<<<<<< HEAD
>>>>>>> parent of f98d515 (sssaaavvve)
=======
>>>>>>> parent of f98d515 (sssaaavvve)
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
            <?php foreach($categories as $category): ?>
                <div class="col">
                    <a href="/index.php?category=<?php echo $category['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none">
                        <div class="card h-100 text-center category-card">
                            <div class="card-body">
                                <?php
                                // Define icon for each category
                                $icon = 'tag';
                                $color = '#4e73df';
                                
                                switch($category['slug']) {
                                    case 'for-sale':
                                        $icon = 'tag';
                                        $color = '#4e73df';
                                        break;
                                    case 'housing':
                                        $icon = 'home';
                                        $color = '#1cc88a';
                                        break;
                                    case 'jobs':
                                        $icon = 'briefcase';
                                        $color = '#f6c23e';
                                        break;
                                    case 'services':
                                        $icon = 'tools';
                                        $color = '#e74a3b';
                                        break;
                                    case 'community':
                                        $icon = 'users';
                                        $color = '#36b9cc';
                                        break;
                                    case 'vehicles':
                                        $icon = 'car';
                                        $color = '#6f42c1';
                                        break;
                                }
                                ?>
                                <div class="category-icon-container" style="background-color: <?php echo $color; ?>">
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                </div>
                                <h5 class="card-title"><?php echo $category['name']; ?></h5>
                                <p class="small text-muted mb-0">Explore</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


</section>

<!-- Listings Section -->
<section id="listings" class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">
                <?php if ($categoryName): ?>
                    <?php echo $categoryName; ?> Listings
                <?php elseif ($search): ?>
                    Search Results for "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    Recent Listings
                <?php endif; ?>
            </h2>
            
            <?php if ($categoryId || $search): ?>
                <a href="/index.php" class="btn btn-outline-primary">
                    <i class="fas fa-undo me-1"></i> Clear Filters
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($posts)): ?>
            <div class="alert alert-info p-4 text-center">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <h4>No listings found</h4>
                <p>Try adjusting your search criteria or check back later for new listings.</p>
                <a href="/index.php" class="btn btn-primary mt-2">View All Listings</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col">
                        <div class="card h-100 listing-card">
                            <div class="listing-img-container">
                                <?php if (!empty($post['primary_image'])): ?>
                                    <img src="/<?php echo $post['primary_image']; ?>" class="listing-img" alt="<?php echo $post['title']; ?>">
                                <?php else: ?>
                                    <img src="/assets/images/placeholder.png" class="listing-img" alt="No image available">
                                <?php endif; ?>
                                
                                <?php if (!empty($post['price'])): ?>
                                    <div class="listing-price">$<?php echo number_format($post['price'], 2); ?></div>
                                <?php endif; ?>
                                
                                <?php
                                // Get first category from post categories
                                $categoryName = '';
                                foreach ($categories as $category) {
                                    if (in_array($category['id'], explode(',', $post['category_id'] ?? ''))) {
                                        $categoryName = $category['name'];
                                        break;
                                    }
                                }
                                
                                if (!empty($categoryName)): ?>
                                    <div class="listing-category"><?php echo $categoryName; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="listing-meta d-flex justify-content-between mb-2">
                                    <span><i class="far fa-user me-1"></i> <?php echo $post['author']; ?></span>
                                    <span><i class="far fa-clock me-1"></i> <?php echo formatDate($post['created_at']); ?></span>
                                </div>
                                <h5 class="listing-title">
                                    <a href="/posts/view.php?id=<?php echo $post['id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo $post['title']; ?>
                                    </a>
                                </h5>
                                <?php if (!empty($post['location'])): ?>
                                    <div class="listing-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo $post['location']; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0">
                                <a href="/posts/view.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm w-100">
                                    View Details
                                </a>
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

<!-- How It Works Section - Improved design and flow -->
<section class="how-it-works py-5 my-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary px-3 py-2 mb-2">SIMPLE PROCESS</span>
            <h2 class="display-5 fw-bold">How It Works</h2>
            <p class="lead col-md-8 mx-auto">Getting started on ListItAll is quick and easy. Follow these three simple steps to buy or sell items in your local community.</p>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 h-100 shadow-sm hover-lift transition-300">
                    <div class="card-body text-center p-4">
                        <div class="process-icon rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 90px; height: 90px; border: 5px solid rgba(78,115,223,0.2);">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold fs-4">1. Create an Account</h5>
                        <p class="card-text text-muted">Sign up for a free account in less than 60 seconds to start posting listings or contacting sellers.</p>
                        <a href="/auth/register.php" class="btn btn-outline-primary mt-3 fw-semibold px-4">
                            <i class="fas fa-arrow-right me-2"></i> Register Now
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 h-100 shadow-sm hover-lift transition-300">
                    <div class="card-body text-center p-4">
                        <div class="process-icon rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 90px; height: 90px; border: 5px solid rgba(28,200,138,0.2);">
                            <i class="fas fa-edit fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold fs-4">2. Post or Browse</h5>
                        <p class="card-text text-muted">Create a listing with details and photos or browse existing listings in your area to find exactly what you need.</p>
                        <a href="/posts/create.php" class="btn btn-outline-success mt-3 fw-semibold px-4">
                            <i class="fas fa-plus me-2"></i> Post an Ad
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 h-100 shadow-sm hover-lift transition-300">
                    <div class="card-body text-center p-4">
                        <div class="process-icon rounded-circle bg-info text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 90px; height: 90px; border: 5px solid rgba(54,185,204,0.2);">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold fs-4">3. Connect & Complete</h5>
                        <p class="card-text text-muted">Contact sellers, negotiate deals, and complete transactions safely and easily through our secure platform.</p>
                        <a href="/safety-tips.php" class="btn btn-outline-info mt-3 fw-semibold px-4">
                            <i class="fas fa-shield-alt me-2"></i> Safety Tips
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Testimonials Section -->
        <div class="testimonials bg-light p-4 p-md-5 rounded-4 mt-5">
            <div class="text-center mb-4">
                <h3 class="fw-bold">What Our Users Say</h3>
                <p class="text-muted">Join thousands of satisfied ListItAll users</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 h-100 bg-white">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <p class="fst-italic mb-3">"I sold my bicycle within 24 hours of posting it on ListItAll. The process was so easy and the buyer lived just a few blocks away!"</p>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">MB</div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Michael B.</h6>
                                    <small class="text-muted">San Francisco, CA</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 h-100 bg-white">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <p class="fst-italic mb-3">"Found the perfect apartment in my neighborhood through ListItAll. The interface made it so easy to filter for exactly what I needed."</p>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">SJ</div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Sarah J.</h6>
                                    <small class="text-muted">Chicago, IL</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 h-100 bg-white">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                            <p class="fst-italic mb-3">"I've been using ListItAll for over a year now to find clients for my handyman services. It's been a game-changer for my business!"</p>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">RT</div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Robert T.</h6>
                                    <small class="text-muted">Austin, TX</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
