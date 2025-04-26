<?php
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('/index.php', 'You do not have permission to access the admin panel.', 'danger');
}

// Check if valid user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('/admin/users.php', 'Invalid user ID.', 'danger');
}

$user_id = (int)$_GET['id'];
$current_admin_id = (int)$_SESSION['user_id'];

// Get user data
$user = getUserById($user_id);

if (!$user) {
    redirect('/admin/users.php', 'User not found.', 'danger');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize($_POST['last_name']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Check if username or email exists (for someone else)
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = 'Username or email already exists.';
    } else {
        // Prevent removing your own admin privileges
        if ($user_id === $current_admin_id && $user['is_admin'] && !$is_admin) {
            $error = 'You cannot remove your own admin privileges.';
        } else {
            // Update user info
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, phone = ?, is_admin = ? WHERE id = ?");
            $stmt->bind_param("sssssii", $username, $email, $first_name, $last_name, $phone, $is_admin, $user_id);
            
            if ($stmt->execute()) {
                // Handle profile image upload
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                    $file = $_FILES['profile_image'];
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    
                    if (in_array($file['type'], $allowed_types)) {
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/profiles/';
                        
                        // Create directory if it doesn't exist
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $target_file = $upload_dir . $filename;
                        $relative_path = 'assets/images/profiles/' . $filename;
                        
                        // Delete old profile image if exists
                        if (!empty($user['profile_image'])) {
                            $old_file = $_SERVER['DOCUMENT_ROOT'] . '/' . $user['profile_image'];
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }
                        
                        // Upload new image
                        if (move_uploaded_file($file['tmp_name'], $target_file)) {
                            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                            $stmt->bind_param("si", $relative_path, $user_id);
                            $stmt->execute();
                        }
                    }
                }
                
                // Handle password update if provided
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $password, $user_id);
                    $stmt->execute();
                }
                
                // Refresh user data
                $user = getUserById($user_id);
                $success = 'User updated successfully.';
            } else {
                $error = 'Failed to update user.';
            }
        }
    }
}

$page_title = 'Edit User';
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
                    <h2><i class="fas fa-user-edit me-2"></i>Edit User</h2>
                    <a href="/admin/users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Users
                    </a>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-3 text-center mb-4">
                                    <div class="mb-3">
                                        <div class="profile-image-container mx-auto position-relative" style="width: 150px; height: 150px;">
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <img src="/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" class="img-thumbnail rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 100%; height: 100%; font-size: 64px;">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="profile_image" class="form-label">Change Profile Image</label>
                                        <input type="file" class="form-control form-control-sm" id="profile_image" name="profile_image" accept="image/*">
                                    </div>
                                </div>
                                
                                <div class="col-md-9">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="is_admin" class="form-label">User Role</label>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1" <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_admin">
                                                    Administrator
                                                </label>
                                                <?php if ($user_id === $current_admin_id && $user['is_admin']): ?>
                                                    <div class="form-text text-danger">You cannot remove your own admin privileges.</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave empty to keep current password">
                                            <div class="form-text">Leave empty to keep current password</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <label class="form-label">User Statistics</label>
                                            <div class="d-flex">
                                                <?php
                                                // Get user's post count
                                                $post_stmt = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
                                                $post_stmt->bind_param('i', $user_id);
                                                $post_stmt->execute();
                                                $post_result = $post_stmt->get_result();
                                                $post_count = $post_result->fetch_assoc()['count'];
                                                ?>
                                                <div class="me-4">
                                                    <strong>Posts:</strong> <?php echo $post_count; ?>
                                                </div>
                                                <div class="me-4">
                                                    <strong>Joined:</strong> <?php echo formatDate($user['created_at']); ?>
                                                </div>
                                                <div>
                                                    <strong>Last Updated:</strong> <?php echo formatDate($user['updated_at']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-end">
                                <a href="/admin/users.php" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
