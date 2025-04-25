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

<!-- Hero Section -->
<section class="hero mb-5">
    <div class="container text-center hero-content">
        <h1 class="display-4 fw-bold">Find Everything You Need Locally</h1>
        <p class="lead mb-4">Buy and sell items, find jobs, housing, and services in your local community.</p>
        <div class="d-flex justify-content-center">
            <a href="/posts/create.php" class="btn btn-success btn-lg me-3">
                <i class="fas fa-plus-circle me-2"></i> Post an Ad
            </a>
            <a href="/how-it-works.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-info-circle me-2"></i> How It Works
            </a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Browse Categories</h2>
            <a href="/categories.php" class="text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
            <?php foreach($categories as $category): ?>
                <div class="col">
                    <a href="/index.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
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
<section class="mb-5">
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

<!-- How It Works Section -->
<section class="py-5 bg-light rounded-3">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How It Works</h2>
            <p class="lead">Follow these simple steps to buy or sell on ListItAll</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 h-100 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                        <h5 class="card-title">1. Create an Account</h5>
                        <p class="card-text">Sign up for a free account to start posting listings or contacting sellers.</p>
                        <a href="/auth/register.php" class="btn btn-outline-primary mt-2">Register Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 h-100 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-edit fa-2x"></i>
                        </div>
                        <h5 class="card-title">2. Post or Browse</h5>
                        <p class="card-text">Create a listing with details and photos or browse existing listings to find what you need.</p>
                        <a href="/posts/create.php" class="btn btn-outline-success mt-2">Post an Ad</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 h-100 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                        <h5 class="card-title">3. Connect & Complete</h5>
                        <p class="card-text">Contact sellers, negotiate deals, and complete transactions safely and easily.</p>
                        <a href="/safety-tips.php" class="btn btn-outline-info mt-2">Safety Tips</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
