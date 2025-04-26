<?php
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('/index.php', 'You do not have permission to access the admin panel.', 'danger');
}

// Initialize variables
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Delete post if requested
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $post_id = (int)$_GET['id'];
    
    // Delete post images first
    $img_sql = "SELECT image_path FROM images WHERE post_id = ?";
    $img_stmt = $conn->prepare($img_sql);
    $img_stmt->bind_param('i', $post_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    
    while ($img = $img_result->fetch_assoc()) {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $img['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete images records from DB
    $del_img_stmt = $conn->prepare("DELETE FROM images WHERE post_id = ?");
    $del_img_stmt->bind_param('i', $post_id);
    $del_img_stmt->execute();
    
    // Delete post categories association
    $del_cat_stmt = $conn->prepare("DELETE FROM post_categories WHERE post_id = ?");
    $del_cat_stmt->bind_param('i', $post_id);
    $del_cat_stmt->execute();
    
    // Delete post
    $del_post_stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $del_post_stmt->bind_param('i', $post_id);
    $del_post_stmt->execute();
    
    redirect('/admin/posts.php', 'Post deleted successfully.');
}

// Update post status if requested
if (isset($_GET['action']) && $_GET['action'] === 'status' && isset($_GET['id']) && isset($_GET['status'])) {
    $post_id = (int)$_GET['id'];
    $status = sanitize($_GET['status']);
    
    if (in_array($status, ['active', 'pending', 'expired', 'deleted'])) {
        $stmt = $conn->prepare("UPDATE posts SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $post_id);
        $stmt->execute();
        redirect('/admin/posts.php', "Post status updated to '$status'.");
    }
}

// Build query for posts
$where_clauses = [];
$params = [];
$types = "";

if (!empty($searchTerm)) {
    $where_clauses[] = "(p.title LIKE ? OR p.description LIKE ? OR u.username LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if (!empty($statusFilter)) {
    $where_clauses[] = "p.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($categoryFilter)) {
    $where_clauses[] = "pc.category_id = ?";
    $params[] = $categoryFilter;
    $types .= "i";
}

// Build the SQL query
$sql_count = "SELECT COUNT(DISTINCT p.id) as total FROM posts p 
              JOIN users u ON p.user_id = u.id";

$sql = "SELECT DISTINCT p.*, u.username 
        FROM posts p 
        JOIN users u ON p.user_id = u.id";

if ($categoryFilter) {
    $sql .= " LEFT JOIN post_categories pc ON p.id = pc.post_id";
    $sql_count .= " LEFT JOIN post_categories pc ON p.id = pc.post_id";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
    $sql_count .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY p.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Get total posts count for pagination
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $bind_params = array_slice($params, 0, -2); // Remove limit and offset
    if (!empty($bind_params)) {
        $bind_types = substr($types, 0, -2); // Remove "ii" for limit and offset
        $stmt_count->bind_param($bind_types, ...$bind_params);
    }
}
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_posts = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);

// Get posts
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

// Get categories for filter dropdown
$categories = getCategories();

// Set page title
$page_title = 'Manage Posts';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar p-3 rounded">
                <h5 class="text-white mb-3">Admin Dashboard</h5>
                <div class="d-flex flex-column">
                    <a href="/admin/index.php" class="admin-menu-item">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="/admin/posts.php" class="admin-menu-item active">
                        <i class="fas fa-list-alt"></i> Posts
                    </a>
                    <a href="/admin/users.php" class="admin-menu-item">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="/admin/categories.php" class="admin-menu-item">
                        <i class="fas fa-th-list"></i> Categories
                    </a>
                    <a href="/index.php" class="admin-menu-item">
                        <i class="fas fa-home"></i> Back to Site
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="admin-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-list-alt me-2"></i>Manage Posts</h2>
                    <a href="/posts/create.php" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Add New Post
                    </a>
                </div>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search title, description, username...">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?php if ($statusFilter === 'active') echo 'selected'; ?>>Active</option>
                                    <option value="pending" <?php if ($statusFilter === 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="expired" <?php if ($statusFilter === 'expired') echo 'selected'; ?>>Expired</option>
                                    <option value="deleted" <?php if ($statusFilter === 'deleted') echo 'selected'; ?>>Deleted</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="0">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php if ($categoryFilter == $category['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Posts Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No posts found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td><?php echo $post['id']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php 
                                                        // Get primary image
                                                        $img_sql = "SELECT image_path FROM images WHERE post_id = ? AND is_primary = 1 LIMIT 1";
                                                        $img_stmt = $conn->prepare($img_sql);
                                                        $img_stmt->bind_param('i', $post['id']);
                                                        $img_stmt->execute();
                                                        $img_result = $img_stmt->get_result();
                                                        $img = $img_result->fetch_assoc();
                                                        ?>
                                                        <div class="me-2" style="width: 40px; height: 40px; overflow: hidden;">
                                                            <?php if ($img): ?>
                                                                <img src="/<?php echo $img['image_path']; ?>" alt="Thumbnail" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 100%;">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <a href="/posts/view.php?id=<?php echo $post['id']; ?>" class="text-decoration-none fw-medium">
                                                                <?php echo htmlspecialchars($post['title']); ?>
                                                            </a>
                                                            <div class="small text-muted text-truncate" style="max-width: 300px;">
                                                                <?php echo htmlspecialchars(substr($post['description'], 0, 50)); ?><?php if (strlen($post['description']) > 50) echo '...'; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($post['username']); ?></td>
                                                <td><?php echo formatDate($post['created_at']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($post['status'] === 'active') ? 'success' : (($post['status'] === 'pending') ? 'warning' : (($post['status'] === 'expired') ? 'secondary' : 'danger')); ?>">
                                                        <?php echo ucfirst($post['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $post['views']; ?></td>
                                                <td>
                                                    <div class="btn-group float-end">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                            Status
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="/admin/posts.php?action=status&id=<?php echo $post['id']; ?>&status=active">Set Active</a></li>
                                                            <li><a class="dropdown-item" href="/admin/posts.php?action=status&id=<?php echo $post['id']; ?>&status=pending">Set Pending</a></li>
                                                            <li><a class="dropdown-item" href="/admin/posts.php?action=status&id=<?php echo $post['id']; ?>&status=expired">Set Expired</a></li>
                                                            <li><a class="dropdown-item" href="/admin/posts.php?action=status&id=<?php echo $post['id']; ?>&status=deleted">Set Deleted</a></li>
                                                        </ul>
                                                        <a href="/posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="/admin/posts.php?action=delete&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&status=<?php echo urlencode($statusFilter); ?>&category=<?php echo $categoryFilter; ?>">
                                            Previous
                                        </a>
                                    </li>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    if ($start_page > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($searchTerm) . '&status=' . urlencode($statusFilter) . '&category=' . $categoryFilter . '">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                              <a class="page-link" href="?page=' . $i . '&search=' . urlencode($searchTerm) . '&status=' . urlencode($statusFilter) . '&category=' . $categoryFilter . '">' . $i . '</a>
                                              </li>';
                                    }
                                    
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($searchTerm) . '&status=' . urlencode($statusFilter) . '&category=' . $categoryFilter . '">' . $total_pages . '</a></li>';
                                    }
                                    ?>
                                    
                                    <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&status=<?php echo urlencode($statusFilter); ?>&category=<?php echo $categoryFilter; ?>">
                                            Next
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Summary -->
                <div class="mt-3 text-muted small">
                    Showing <?php echo count($posts); ?> of <?php echo $total_posts; ?> posts
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
