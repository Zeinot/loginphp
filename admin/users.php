<?php
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('/index.php', 'You do not have permission to access the admin panel.', 'danger');
}

// Initialize variables
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$adminFilter = isset($_GET['admin']) ? (int)$_GET['admin'] : -1; // -1 = all, 0 = regular users, 1 = admins
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Handle user deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Don't allow deleting yourself
    if ($user_id === (int)$_SESSION['user_id']) {
        redirect('/admin/users.php', 'You cannot delete your own account.', 'danger');
    }
    
    // Get user to check if they have profile image
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        // Delete profile image if exists
        if (!empty($user['profile_image'])) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $user['profile_image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete the user
        $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $del_stmt->bind_param('i', $user_id);
        $del_stmt->execute();
        
        redirect('/admin/users.php', 'User deleted successfully.');
    } else {
        redirect('/admin/users.php', 'User not found.', 'danger');
    }
}

// Handle admin status toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle_admin' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Get current admin status
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        $new_status = $user['is_admin'] ? 0 : 1;
        
        // Don't allow removing admin from yourself
        if ($user_id === (int)$_SESSION['user_id'] && $new_status === 0) {
            redirect('/admin/users.php', 'You cannot remove your own admin privileges.', 'danger');
        }
        
        // Update admin status
        $update_stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $update_stmt->bind_param('ii', $new_status, $user_id);
        $update_stmt->execute();
        
        $status_text = $new_status ? 'Admin' : 'Regular user';
        redirect('/admin/users.php', "User status updated to $status_text.");
    } else {
        redirect('/admin/users.php', 'User not found.', 'danger');
    }
}

// Build query for users
$where_clauses = [];
$params = [];
$types = "";

if (!empty($searchTerm)) {
    $where_clauses[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
}

if ($adminFilter !== -1) {
    $where_clauses[] = "is_admin = ?";
    $params[] = $adminFilter;
    $types .= "i";
}

// Build the SQL query
$sql_count = "SELECT COUNT(*) as total FROM users";
$sql = "SELECT * FROM users";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
    $sql_count .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Get total users count for pagination
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
$total_users = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Get users
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Set page title
$page_title = 'Manage Users';
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
                    <a href="/admin/posts.php" class="admin-menu-item">
                        <i class="fas fa-list-alt"></i> Posts
                    </a>
                    <a href="/admin/users.php" class="admin-menu-item active">
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
                    <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
                </div>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-7">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search username, email, name...">
                            </div>
                            <div class="col-md-3">
                                <label for="admin" class="form-label">User Type</label>
                                <select class="form-select" id="admin" name="admin">
                                    <option value="-1" <?php if ($adminFilter === -1) echo 'selected'; ?>>All Users</option>
                                    <option value="1" <?php if ($adminFilter === 1) echo 'selected'; ?>>Administrators</option>
                                    <option value="0" <?php if ($adminFilter === 0) echo 'selected'; ?>>Regular Users</option>
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
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Name</th>
                                        <th>Posts</th>
                                        <th>Joined</th>
                                        <th>Type</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No users found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2" style="width: 40px; height: 40px; overflow: hidden;">
                                                            <?php if (!empty($user['profile_image'])): ?>
                                                                <img src="/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" class="img-thumbnail rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 100%; height: 100%;">
                                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <span class="fw-medium"><?php echo htmlspecialchars($user['username']); ?></span>
                                                            <?php if ((int)$user['id'] === (int)$_SESSION['user_id']): ?>
                                                                <span class="badge bg-secondary ms-1">You</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php 
                                                    $name = trim($user['first_name'] . ' ' . $user['last_name']);
                                                    echo !empty($name) ? htmlspecialchars($name) : '<span class="text-muted">Not set</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Count user's posts
                                                    $post_stmt = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
                                                    $post_stmt->bind_param('i', $user['id']);
                                                    $post_stmt->execute();
                                                    $post_result = $post_stmt->get_result();
                                                    $post_count = $post_result->fetch_assoc()['count'];
                                                    echo $post_count;
                                                    ?>
                                                </td>
                                                <td><?php echo formatDate($user['created_at']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $user['is_admin'] ? 'bg-danger' : 'bg-info'; ?>">
                                                        <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group float-end">
                                                        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                                            <a href="/admin/users.php?action=toggle_admin&id=<?php echo $user['id']; ?>" 
                                                               class="btn btn-sm <?php echo $user['is_admin'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>"
                                                               onclick="return confirm('Are you sure you want to <?php echo $user['is_admin'] ? 'remove admin status from' : 'make admin'; ?> this user?');">
                                                                <i class="fas <?php echo $user['is_admin'] ? 'fa-user-minus' : 'fa-user-shield'; ?>"></i>
                                                                <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="/admin/user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                                            <a href="/admin/users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone and will delete all their posts.');">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        <?php endif; ?>
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
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&admin=<?php echo $adminFilter; ?>">
                                            Previous
                                        </a>
                                    </li>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    if ($start_page > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($searchTerm) . '&admin=' . $adminFilter . '">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                              <a class="page-link" href="?page=' . $i . '&search=' . urlencode($searchTerm) . '&admin=' . $adminFilter . '">' . $i . '</a>
                                              </li>';
                                    }
                                    
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($searchTerm) . '&admin=' . $adminFilter . '">' . $total_pages . '</a></li>';
                                    }
                                    ?>
                                    
                                    <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&admin=<?php echo $adminFilter; ?>">
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
                    Showing <?php echo count($users); ?> of <?php echo $total_users; ?> users
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
