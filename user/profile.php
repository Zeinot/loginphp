<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/auth/login.php', 'You must be logged in to view your profile.', 'danger');
}

// Get current user data
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    redirect('/index.php', 'User not found.', 'danger');
}

// Initialize variables
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which form was submitted
    if (isset($_POST['update_profile'])) {
        // Sanitize and validate inputs
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);

        // Validate username
        if (empty($username)) {
            $error = 'Username is required.';
        } elseif ($username !== $user['username']) {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param('si', $username, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = 'Username already taken.';
            }
        }

        // Validate email
        if (empty($email)) {
            $error = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($email !== $user['email']) {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param('si', $email, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = 'Email already in use.';
            }
        }

        // If no errors, update profile
        if (empty($error)) {
            // Handle profile image upload
            $profile_image = $user['profile_image']; // Default to current image
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $file = $_FILES['profile_image'];
                $filename = $file['name'];
                $tmp_name = $file['tmp_name'];
                $file_error = $file['error'];
                $file_size = $file['size'];
                
                // Get file extension
                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                // Check if file is valid
                if ($file_error === 0) {
                    if (in_array($file_ext, $allowed_extensions)) {
                        if ($file_size <= 2097152) { // 2MB max size
                            // Create unique filename
                            $new_filename = uniqid('profile_') . '.' . $file_ext;
                            $upload_dir = '../uploads/profiles/';
                            
                            // Create directory if it doesn't exist
                            if (!file_exists($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            
                            $destination = $upload_dir . $new_filename;
                            
                            if (move_uploaded_file($tmp_name, $destination)) {
                                // Delete old profile image if it exists
                                if (!empty($user['profile_image']) && file_exists('../' . $user['profile_image'])) {
                                    unlink('../' . $user['profile_image']);
                                }
                                
                                $profile_image = 'uploads/profiles/' . $new_filename;
                            } else {
                                $error = 'Failed to upload image.';
                            }
                        } else {
                            $error = 'File size too large. Maximum size is 2MB.';
                        }
                    } else {
                        $error = 'Invalid file type. Allowed types: jpg, jpeg, png, gif.';
                    }
                } else {
                    $error = 'Error uploading file.';
                }
            }
            
            if (empty($error)) {
                // Update profile in database
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, phone = ?, profile_image = ? WHERE id = ?");
                $stmt->bind_param('ssssssi', $username, $email, $first_name, $last_name, $phone, $profile_image, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $success = 'Profile updated successfully.';
                    // Update session variables
                    $_SESSION['username'] = $username;
                    // Refresh user data
                    $user = getUserById($_SESSION['user_id']);
                } else {
                    $error = 'Failed to update profile: ' . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Handle password change
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate current password
        if (empty($current_password)) {
            $error = 'Current password is required.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        }
        
        // Validate new password
        if (empty($new_password)) {
            $error = 'New password is required.';
        } elseif (strlen($new_password) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        }
        
        // If no errors, update password
        if (empty($error)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Password changed successfully.';
            } else {
                $error = 'Failed to change password: ' . $conn->error;
            }
        }
    }
}

// Set page title
$page_title = 'My Profile';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($user['profile_image']) && file_exists('../' . $user['profile_image'])): ?>
                            <img src="/<?php echo $user['profile_image']; ?>" alt="<?php echo $user['username']; ?>" class="rounded-circle img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 48px;">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
                    <p class="text-muted">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/user/profile.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                    <a href="/user/my_listings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-list me-2"></i> My Listings
                    </a>
                    <a href="/auth/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Display error/success messages -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- Profile Information -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="profile_image" class="form-label">Profile Image</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                <small class="form-text text-muted">Maximum file size: 2MB. Allowed formats: JPG, PNG, GIF</small>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="form-text text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-secondary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
