<?php
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('/index.php', 'You do not have permission to access the admin panel.', 'danger');
}

// Initialize variables
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$showing_text = ''; // Initialize showing_text to prevent undefined variable warnings

// Initialize search
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

// Add search query if search term is provided
if (!empty($searchTerm)) {
    $where_clause = "WHERE (name LIKE ? OR description LIKE ? OR slug LIKE ?)";
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







// First get all category IDs for later use
$categories_with_counts = [];
$category_ids = [];

// Special handling for searches with no results
if (!empty($searchTerm) && $total_categories == 0) {
    // Initialize empty arrays for categories and counts
    $categories = [];
    $category_counts = [];
    $category_ids = [];
    
    
    // Add a notice about the search results
    $success = "Search completed successfully.";
    $error = "No categories found matching '$searchTerm'.";
    
    goto output_stage; // Skip to the output stage to avoid unnecessary queries
}

// Get categories first
// Prepare the main SQL query for categories
$sql = "SELECT c.* 
        FROM categories c 
        $where_clause 
        ORDER BY c.name ASC 
        LIMIT ?, ?";

// Add pagination parameters (these must be the last parameters)
$pagination_params = [$offset, $limit];
$pagination_types = 'ii';

// Create a copy of the initial parameters for the main query
$query_params = $params;
$query_types = $types;

// Add pagination parameters to the query parameters
foreach ($pagination_params as $param) {
    $query_params[] = $param;
}
$query_types .= $pagination_types;




$stmt = $conn->prepare($sql);
if (!empty($query_params)) {
    // Bind all parameters including pagination
    $stmt->bind_param($query_types, ...$query_params);
}


$stmt->execute();
$result = $stmt->get_result();

// Store all categories and their IDs
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
    $category_ids[] = $row['id'];
}




// Get all post counts at once if we have categories
if (!empty($searchTerm)) {
    if ($total_categories == 0) {
        $showing_text = "Found 0 categories matching '$searchTerm'";
    } else {
        $showing_text = "Showing $total_categories result(s) for search query '$searchTerm'";
    }
} else {
    $showing_text = "Showing $total_categories out of $total_categories categories";
}

if (!empty($category_ids)) {
    // First, verify post_categories table exists and has data
    $check_table = "SHOW TABLES LIKE 'post_categories'";
    $table_result = $conn->query($check_table);
    
    if ($table_result->num_rows > 0) {
        // Check for any entries in post_categories
        $check_entries = "SELECT COUNT(*) as total FROM post_categories";
        $entries_result = $conn->query($check_entries);
        $entries_count = $entries_result->fetch_assoc()['total'];      
        // Get counts for all categories directly with a single query
        $counts_query = "SELECT category_id, COUNT(*) as post_count 
                        FROM post_categories 
                        WHERE category_id IN (" . implode(',', $category_ids) . ") 
                        GROUP BY category_id";
        
        $counts_result = $conn->query($counts_query);
        
        $category_counts = [];
        
        // Create a lookup array of counts by category ID
        if ($counts_result && $counts_result->num_rows > 0) {
            while ($count = $counts_result->fetch_assoc()) {
                $category_counts[$count['category_id']] = (int)$count['post_count'];
            }
        }
        
        // Merge the counts with the categories
        foreach ($categories as &$category) {
            $category['post_count'] = isset($category_counts[$category['id']]) ? $category_counts[$category['id']] : 0;
        }
    } else {
        // post_categories table does not exist
    }
}

output_stage:
// Apply additional checks to ensure categories is empty for empty search results
if (!empty($searchTerm) && $total_categories == 0) {
    // Force categories to be empty for no search results
    $categories = []; 
    
    // Ensure the JavaScript debug data correctly shows empty categories
    $category_ids = [];
    $category_counts = [];
}

// Make sure the showing_text is set
if (empty($showing_text)) {
    if (!empty($searchTerm)) {
        if ($total_categories == 0) {
            $showing_text = "Found 0 categories matching '$searchTerm'";
        } else {
            $showing_text = "Showing $total_categories result(s) for search query '$searchTerm'";
        }
    } else {
        $showing_text = "Showing " . count($categories) . " of $total_categories categories";
    }
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
                        <form action="/admin/categories.php" method="GET" class="row g-3">
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
                        <?php if (!empty($searchTerm)): ?>
                            <div class="alert alert-info mt-3 mb-0">
                                Searching for: <strong><?php echo htmlspecialchars($searchTerm); ?></strong>
                                <a href="/admin/categories.php" class="float-end">Clear Search</a>
                            </div>
                            

                        <?php endif; ?>
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
                                    <?php 
                                    // Check if this is a search with no results
                                    $is_empty_search = (!empty($searchTerm) && $total_categories == 0);
                                    
                                    // Only show categories if either:
                                    // 1. We're not searching, or
                                    // 2. We're searching and found results
                                    if ($is_empty_search || empty($categories)): 
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <?php if (!empty($searchTerm)): ?>
                                                    <div class="alert alert-warning mb-0">
                                                        <i class="fas fa-search me-2"></i>
                                                        No categories found matching <strong>"<?php echo htmlspecialchars($searchTerm); ?>"</strong>
                                                    </div>
                                                <?php else: ?>
                                                    No categories found
                                                <?php endif; ?>
                                            </td>
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
                                                <td class="post-count-cell" data-category-id="<?php echo $category['id']; ?>">
                                                    <?php 
                                                    // Output the raw post count directly
                                                    if (isset($category_counts[$category['id']])) {
                                                        echo $category_counts[$category['id']];
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo formatDate($category['created_at']); ?></td>
                                                <td>
                                                    <div class="btn-group float-end">
                                                        <a href="/admin/categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <?php if (!isset($category_counts[$category['id']]) || $category_counts[$category['id']] == 0): ?>
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
                
                <!-- Pagination footer -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <?php echo $showing_text; ?>
                        </div>
                    </div>
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
