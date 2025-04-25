<?php
require_once '../includes/functions.php';

// Only logged-in users can access this page
if (!isLoggedIn()) {
    redirect('/auth/login.php', 'You must be logged in to view your listings.', 'danger');
}

$user_id = $_SESSION['user_id'];

// Fetch user's posts
$sql = "SELECT p.*, (SELECT image_path FROM images WHERE post_id = p.id AND is_primary = 1 LIMIT 1) as main_image FROM posts p WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Listings';
include '../includes/header.php';
?>
<div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-list me-2"></i>My Listings</h2>
    <?php if (empty($posts)): ?>
        <div class="alert alert-info">You have not posted any listings yet.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card listing-card h-100">
                        <?php if ($post['main_image']): ?>
                            <img src="/<?php echo htmlspecialchars($post['main_image']); ?>" class="card-img-top listing-img" alt="Listing image">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light listing-img" style="height:180px;">
                                <img src="/assets/img/no-image.svg" alt="No image" style="height:80px;opacity:0.5;">
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2 small text-muted"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <span class="ms-auto small text-muted"><i class="far fa-calendar-alt"></i> <?php echo formatDate($post['created_at']); ?></span>
                            </div>
                            <h5 class="card-title mb-2 text-truncate"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <div class="mb-2 text-muted small text-truncate">
                                <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($post['location']); ?>
                            </div>
                            <div class="mb-2 fw-bold text-primary">$<?php echo number_format($post['price'], 2); ?></div>
                            <div class="mb-2">
                                <span class="badge bg-<?php echo ($post['status'] === 'active') ? 'success' : (($post['status'] === 'pending') ? 'warning' : 'secondary'); ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </div>
                            <div class="mt-auto d-grid gap-2">
                                <a href="/posts/view.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm w-100 mb-1"><i class="fas fa-eye me-1"></i>View Details</a>
                                <div class="d-flex gap-2">
                                    <a href="/posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-secondary btn-sm flex-fill"><i class="fas fa-edit me-1"></i>Edit</a>
                                    <a href="/posts/delete.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-danger btn-sm flex-fill" onclick="return confirm('Are you sure you want to delete this listing?');"><i class="fas fa-trash me-1"></i>Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
