<?php
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('/index.php', 'You do not have permission to access the admin panel.', 'danger');
}

// Get stats for dashboard
$conn->select_db(DB_NAME);

// Total users
$totalUsers = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM users");
if ($result && $result->num_rows > 0) {
    $totalUsers = $result->fetch_assoc()['total'];
}

// Total posts
$totalPosts = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM posts");
if ($result && $result->num_rows > 0) {
    $totalPosts = $result->fetch_assoc()['total'];
}

// Total categories
$totalCategories = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM categories");
if ($result && $result->num_rows > 0) {
    $totalCategories = $result->fetch_assoc()['total'];
}

// Recent posts
$recentPosts = [];
$result = $conn->query("SELECT p.*, u.username 
                        FROM posts p 
                        JOIN users u ON p.user_id = u.id 
                        ORDER BY p.created_at DESC 
                        LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentPosts[] = $row;
    }
}

// Recent users
$recentUsers = [];
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentUsers[] = $row;
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar p-3 rounded">
                <h5 class="text-white mb-3">Admin Dashboard</h5>
                <div class="d-flex flex-column">
                    <a href="/admin/index.php" class="admin-menu-item active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="/admin/posts.php" class="admin-menu-item">
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
                <h2 class="mb-4">Dashboard</h2>
                
                <!-- Stats Cards -->
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    <div class="col">
                        <div class="stats-card h-100">
                            <div class="stats-card-icon bg-primary text-white">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div class="stats-card-value"><?php echo $totalPosts; ?></div>
                            <div class="stats-card-label">Total Posts</div>
                            <a href="/admin/posts.php" class="btn btn-sm btn-primary mt-3">Manage Posts</a>
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="stats-card h-100">
                            <div class="stats-card-icon bg-success text-white">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-card-value"><?php echo $totalUsers; ?></div>
                            <div class="stats-card-label">Registered Users</div>
                            <a href="/admin/users.php" class="btn btn-sm btn-success mt-3">Manage Users</a>
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="stats-card h-100">
                            <div class="stats-card-icon bg-info text-white">
                                <i class="fas fa-th-list"></i>
                            </div>
                            <div class="stats-card-value"><?php echo $totalCategories; ?></div>
                            <div class="stats-card-label">Categories</div>
                            <a href="/admin/categories.php" class="btn btn-sm btn-info mt-3">Manage Categories</a>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4">
                    <!-- Recent Posts Table -->
                    <div class="col-12 col-xl-7">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Recent Posts</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Views</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recentPosts)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">No posts found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recentPosts as $post): ?>
                                                    <tr>
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
                                                                    <div class="small text-muted text-truncate" style="max-width: 200px;">
                                                                        <?php echo htmlspecialchars(substr($post['description'], 0, 40)); ?><?php if (strlen($post['description']) > 40) echo '...'; ?>
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
                                                                <a href="/posts/view.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="/posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="/admin/posts.php?action=status&id=<?php echo $post['id']; ?>&status=<?php echo $post['status'] === 'active' ? 'pending' : 'active'; ?>" class="btn btn-sm btn-outline-<?php echo $post['status'] === 'active' ? 'secondary' : 'success'; ?>">
                                                                    <i class="fas <?php echo $post['status'] === 'active' ? 'fa-pause' : 'fa-play'; ?>"></i>
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
                            <div class="card-footer">
                                <a href="/admin/posts.php" class="text-decoration-none">View all posts</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Users Table -->
                    <div class="col-12 col-xl-5">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> Recent Users</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recentUsers)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No users found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recentUsers as $user): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <?php if (!empty($user['profile_image'])): ?>
                                                                        <img src="/<?php echo $user['profile_image']; ?>" alt="<?php echo $user['username']; ?>" class="rounded-circle" width="32" height="32">
                                                                    <?php else: ?>
                                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                                            <i class="fas fa-user"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php echo $user['username']; ?>
                                                                <?php if ($user['is_admin']): ?>
                                                                    <span class="ms-1 badge bg-danger">Admin</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <td><?php echo $user['email']; ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="/admin/users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="/admin/users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?')">
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
                            <div class="card-footer">
                                <a href="/admin/users.php" class="text-decoration-none">View all users</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
