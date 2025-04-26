<?php
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('/index.php', 'You do not have permission to access the admin panel.', 'danger');
}

// Initialize variables
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Handle category action (add, edit, delete)
$action = isset($_GET['action']) ? $_GET['action'] : '';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Delete category
if ($action === 'delete' && $category_id > 0) {
    // Check if category has posts
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM post_categories WHERE category_id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post_count = $result->fetch_assoc()['count'];
    
    if ($post_count > 0) {
        $error = "Cannot delete category. It has $post_count posts associated with it.";
    } else {
        // Delete category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param('i', $category_id);
        if ($stmt->execute()) {
            $success = "Category deleted successfully.";
        } else {
            $error = "Failed to delete category.";
        }
    }
}

// Add new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $slug = sanitize($_POST['slug']);
    
    // Validate input
    if (empty($name) || empty($slug)) {
        $error = "Name and slug are required.";
    } else {
        // Check if slug exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Slug already exists. Please choose a different one.";
        } else {
            // Insert new category
            $stmt = $conn->prepare("INSERT INTO categories (name, description, slug) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $name, $description, $slug);
            
            if ($stmt->execute()) {
                $success = "Category added successfully.";
            } else {
                $error = "Failed to add category.";
            }
        }
    }
}

// Edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $edit_id = (int)$_POST['edit_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $slug = sanitize($_POST['slug']);
    
    // Validate input
    if (empty($name) || empty($slug)) {
        $error = "Name and slug are required.";
    } else {
        // Check if slug exists (except for this category)
        $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt->bind_param('si', $slug, $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Slug already exists. Please choose a different one.";
        } else {
            // Update category
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, slug = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $description, $slug, $edit_id);
            
            if ($stmt->execute()) {
                $success = "Category updated successfully.";
            } else {
                $error = "Failed to update category.";
            }
        }
    }
}

// Get category to edit
$category_to_edit = null;
if ($action === 'edit' && $category_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $category_to_edit = $result->fetch_assoc();
    }
}

// Build query for categories
$where_clause = '';
$params = [];
$types = '';

if (!empty($searchTerm)) {
    $where_clause = "WHERE name LIKE ? OR description LIKE ? OR slug LIKE ?";
    $searchParam = "%$searchTerm%";
    $params = [$searchParam, $searchParam, $searchParam];
    $types = 'sss';
}

// Count total categories for pagination
$sql_count = "SELECT COUNT(*) as total FROM categories $where_clause";
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_categories = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_categories / $limit);

// Get categories
$sql = "SELECT c.*, 
              (SELECT COUNT(*) FROM post_categories WHERE category_id = c.id) as post_count 
        FROM categories c 
        $where_clause 
        ORDER BY c.name ASC 
        LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Set page title
$page_title = 'Manage Categories';
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
                    <a href="/admin/users.php" class="admin-menu-item">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="/admin/categories.php" class="admin-menu-item active">
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
                    <h2><i class="fas fa-th-list me-2"></i>Manage Categories</h2>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus-circle"></i> Add New Category
                    </button>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-10">
                                <label for="search" class="form-label">Search Categories</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search by name, description, or slug...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Categories Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Posts</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No categories found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo $category['id']; ?></td>
                                                <td>
                                                    <div>
                                                        <span class="fw-medium"><?php echo htmlspecialchars($category['name']); ?></span>
                                                        <?php if (!empty($category['description'])): ?>
                                                            <div class="small text-muted text-truncate" style="max-width: 300px;">
                                                                <?php echo htmlspecialchars($category['description']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                                                <td><?php echo isset($category['post_count']) ? $category['post_count'] : 0; ?></td>
                                                <td><?php echo formatDate($category['created_at']); ?></td>
                                                <td>
                                                    <div class="btn-group float-end">
                                                        <a href="/admin/categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <?php if (!isset($category['post_count']) || $category['post_count'] == 0): ?>
                                                            <a href="/admin/categories.php?action=delete&id=<?php echo $category['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this category?');">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" disabled title="Cannot delete category with posts">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
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
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchTerm); ?>">
                                            Previous
                                        </a>
                                    </li>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    if ($start_page > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($searchTerm) . '">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                              <a class="page-link" href="?page=' . $i . '&search=' . urlencode($searchTerm) . '">' . $i . '</a>
                                              </li>';
                                    }
                                    
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($searchTerm) . '">' . $total_pages . '</a></li>';
                                    }
                                    ?>
                                    
                                    <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchTerm); ?>">
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
                    Showing <?php echo count($categories); ?> of <?php echo $total_categories; ?> categories
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-slug" name="slug" required>
                        <div class="form-text">Unique identifier for the category (e.g., "for-sale"). Only letters, numbers, and hyphens.</div>
                    </div>
                    <div class="mb-3">
                        <label for="add-description" class="form-label">Description</label>
                        <textarea class="form-control" id="add-description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<?php if ($category_to_edit): ?>
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <a href="/admin/categories.php" class="btn-close" aria-label="Close"></a>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" value="<?php echo $category_to_edit['id']; ?>">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" value="<?php echo htmlspecialchars($category_to_edit['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-slug" name="slug" value="<?php echo htmlspecialchars($category_to_edit['slug']); ?>" required>
                        <div class="form-text">Unique identifier for the category (e.g., "for-sale"). Only letters, numbers, and hyphens.</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"><?php echo htmlspecialchars($category_to_edit['description']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/admin/categories.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="edit_category" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    editModal.show();
});
</script>
<?php endif; ?>

<script>
// Auto-generate slug from name
document.addEventListener('DOMContentLoaded', function() {
    // Function to convert name to slug
    function slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }
    
    // For add modal
    var addNameInput = document.getElementById('add-name');
    var addSlugInput = document.getElementById('add-slug');
    
    if (addNameInput && addSlugInput) {
        addNameInput.addEventListener('input', function() {
            // Only update slug if it's empty or has not been manually edited
            if (addSlugInput.value === '' || addSlugInput.value === slugify(addNameInput.value.trim())) {
                addSlugInput.value = slugify(addNameInput.value.trim());
            }
        });
    }
    
    // For edit modal
    var editNameInput = document.getElementById('edit-name');
    var editSlugInput = document.getElementById('edit-slug');
    
    if (editNameInput && editSlugInput) {
        var originalSlug = editSlugInput.value;
        var userEditedSlug = false;
        
        editNameInput.addEventListener('input', function() {
            // Only update slug if it has not been manually edited
            if (!userEditedSlug) {
                editSlugInput.value = slugify(editNameInput.value.trim());
            }
        });
        
        editSlugInput.addEventListener('input', function() {
            // Flag that user has manually edited the slug
            userEditedSlug = (editSlugInput.value !== slugify(editNameInput.value.trim()));
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
