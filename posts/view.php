<?php
require_once '../includes/functions.php';

// Get post ID from URL
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get post details
$post = getPostById($postId);

// Redirect if post doesn't exist
if (!$post) {
    redirect('/index.php', 'Post not found.', 'danger');
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
            <?php if (!empty($post['categories'])): ?>
                <li class="breadcrumb-item">
                    <a href="/index.php?category=<?php echo $post['categories'][0]['id']; ?>">
                        <?php echo $post['categories'][0]['name']; ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $post['title']; ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Post Gallery -->
            <div class="post-gallery mb-4">
                <?php if (!empty($post['images'])): ?>
                    <img src="/<?php echo $post['images'][0]['image_path']; ?>" class="post-main-image" alt="<?php echo $post['title']; ?>">
                    
                    <?php if (count($post['images']) > 1): ?>
                        <div class="post-thumbnails">
                            <?php foreach ($post['images'] as $index => $image): ?>
                                <img src="/<?php echo $image['image_path']; ?>" class="post-thumbnail <?php echo ($index === 0) ? 'active' : ''; ?>" alt="Thumbnail">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="/assets/images/placeholder.png" class="post-main-image" alt="No image available">
                <?php endif; ?>
            </div>

            <!-- Post Details -->
            <div class="post-details">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <?php if (!empty($post['price'])): ?>
                        <div class="post-price"><?php echo '$' . number_format($post['price'], 2); ?></div>
                    <?php endif; ?>
                    
                    <div>
                        <?php if (!empty($post['categories'])): ?>
                            <?php foreach ($post['categories'] as $category): ?>
                                <span class="badge bg-primary me-1"><?php echo $category['name']; ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h1 class="post-title"><?php echo $post['title']; ?></h1>
                
                <div class="post-meta d-flex flex-wrap">
                    <div class="me-3">
                        <i class="far fa-user me-1"></i> Posted by <?php echo $post['author']; ?>
                    </div>
                    <div class="me-3">
                        <i class="far fa-calendar me-1"></i> <?php echo formatDate($post['created_at']); ?>
                    </div>
                    <?php if (!empty($post['location'])): ?>
                        <div class="me-3">
                            <i class="fas fa-map-marker-alt me-1"></i> <?php echo $post['location']; ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <i class="far fa-eye me-1"></i> <?php echo $post['views']; ?> views
                    </div>
                </div>
                
                <hr>
                
                <div class="post-description">
                    <?php echo nl2br($post['description']); ?>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    
                    <div>
                        <a href="#" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-flag me-1"></i> Report
                        </a>
                        <a href="#" class="btn btn-outline-info">
                            <i class="fas fa-share-alt me-1"></i> Share
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contact Information -->
            <div class="post-contact-info mb-4">
                <h4 class="mb-3">Contact Information</h4>
                
                <div class="author-info">
                    <?php if (!empty($post['author_image'])): ?>
                        <img src="/<?php echo $post['author_image']; ?>" alt="<?php echo $post['author']; ?>" class="author-avatar">
                    <?php else: ?>
                        <div class="author-avatar bg-primary d-flex align-items-center justify-content-center text-white">
                            <i class="fas fa-user fa-lg"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h5 class="mb-1"><?php echo $post['author']; ?></h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user-circle me-1"></i> Member since <?php echo date('M Y', strtotime($post['author_joined'] ?? $post['created_at'])); ?>
                        </p>
                    </div>
                </div>
                
                <div class="contact-methods">
                    <?php if (!empty($post['contact_email']) || !empty($post['author_email'])): ?>
                        <button class="btn btn-primary contact-btn" data-contact-type="email" data-contact-value="<?php echo !empty($post['contact_email']) ? $post['contact_email'] : $post['author_email']; ?>">
                            <i class="fas fa-envelope"></i> Email
                        </button>
                    <?php endif; ?>
                    
                    <?php if (!empty($post['contact_phone']) || !empty($post['author_phone'])): ?>
                        <button class="btn btn-success contact-btn" data-contact-type="phone" data-contact-value="<?php echo !empty($post['contact_phone']) ? $post['contact_phone'] : $post['author_phone']; ?>">
                            <i class="fas fa-phone"></i> Call
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Safety Tips -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-exclamation-triangle me-2"></i> Safety Tips
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check-circle me-2 text-success"></i> Meet in a public place</li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2 text-success"></i> Don't pay in advance</li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2 text-success"></i> Inspect items before buying</li>
                        <li><i class="fas fa-check-circle me-2 text-success"></i> Trust your instincts</li>
                    </ul>
                </div>
            </div>
            
            <!-- Similar Listings -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-tags me-2"></i> Similar Listings
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <!-- This would normally be populated with similar listings from the database -->
                        <li class="list-group-item">
                            <a href="#" class="text-decoration-none">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img src="/assets/images/placeholder.png" alt="Similar listing" style="width: 50px; height: 50px; object-fit: cover;">
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Another similar listing</h6>
                                        <small class="text-muted">$100.00</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="#" class="text-decoration-none">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img src="/assets/images/placeholder.png" alt="Similar listing" style="width: 50px; height: 50px; object-fit: cover;">
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Yet another listing</h6>
                                        <small class="text-muted">$85.00</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="list-group-item text-center">
                            <a href="/index.php" class="text-decoration-none">View all listings</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
