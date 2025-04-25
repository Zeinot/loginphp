<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php', 'You must be logged in to edit listings.', 'danger');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('/user/my_listings.php', 'Invalid post ID.', 'danger');
}

$post_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Fetch post
$sql = "SELECT * FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    redirect('/user/my_listings.php', 'Listing not found.', 'danger');
}

// Permission check
if ($post['user_id'] !== $user_id && !$is_admin) {
    redirect('/user/my_listings.php', 'You do not have permission to edit this listing.', 'danger');
}

// Pre-fill form fields
$title = $post['title'];
$description = $post['description'];
$price = $post['price'];
$location = $post['location'];
$contact_email = $post['contact_email'];
$contact_phone = $post['contact_phone'];
$status = $post['status'];

// Fetch images for this post
$images = [];
$sql = "SELECT * FROM images WHERE post_id = ? ORDER BY is_primary DESC, id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
while ($img = $result->fetch_assoc()) {
    $images[] = $img;
}

// Handle image actions
if (isset($_GET['delete_image_id'])) {
    $img_id = (int)$_GET['delete_image_id'];
    // Get image path
    $img_sql = "SELECT image_path FROM images WHERE id = ? AND post_id = ?";
    $img_stmt = $conn->prepare($img_sql);
    $img_stmt->bind_param('ii', $img_id, $post_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    if ($img = $img_result->fetch_assoc()) {
        // Delete file from filesystem
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $img['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        // Delete from DB
        $del_stmt = $conn->prepare("DELETE FROM images WHERE id = ? AND post_id = ?");
        $del_stmt->bind_param('ii', $img_id, $post_id);
        $del_stmt->execute();
        // If deleted primary, set another as primary
        $conn->query("UPDATE images SET is_primary = 1 WHERE post_id = $post_id ORDER BY id ASC LIMIT 1");
        redirect("/posts/edit.php?id=$post_id", "Image deleted.");
    }
}
if (isset($_GET['set_primary_image_id'])) {
    $img_id = (int)$_GET['set_primary_image_id'];
    // Set all to not primary, then set selected as primary
    $conn->query("UPDATE images SET is_primary = 0 WHERE post_id = $post_id");
    $stmt = $conn->prepare("UPDATE images SET is_primary = 1 WHERE id = ? AND post_id = ?");
    $stmt->bind_param('ii', $img_id, $post_id);
    $stmt->execute();
    redirect("/posts/edit.php?id=$post_id", "Primary image updated.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_image_id']) && !isset($_POST['set_primary_image_id'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = isset($_POST['price']) ? (float)$_POST['price'] : null;
    $location = sanitize($_POST['location']);
    $contact_email = sanitize($_POST['contact_email']);
    $contact_phone = sanitize($_POST['contact_phone']);
    $status = in_array($_POST['status'], ['active', 'pending', 'expired', 'deleted']) ? $_POST['status'] : 'active';

    if (empty($title) || empty($description)) {
        $error = 'Title and description are required.';
    } else {
        $sql = "UPDATE posts SET title=?, description=?, price=?, location=?, contact_email=?, contact_phone=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdssssi', $title, $description, $price, $location, $contact_email, $contact_phone, $status, $post_id);
        $success = $stmt->execute();
        // Handle new image uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $has_primary = $conn->query("SELECT COUNT(*) FROM images WHERE post_id = $post_id AND is_primary = 1")->fetch_row()[0] > 0;
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                if ($_FILES['images']['error'][$i] == 0) {
                    $file = [
                        'name' => $_FILES['images']['name'][$i],
                        'type' => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error' => $_FILES['images']['error'][$i],
                        'size' => $_FILES['images']['size'][$i]
                    ];
                    $is_primary = !$has_primary && $i == 0 ? true : false;
                    $upload_result = uploadImage($file, $post_id, $is_primary);
                    if ($upload_result && !$has_primary && $i == 0) {
                        $has_primary = true;
                    }
                }
            }
        }
        if ($success) {
            redirect('/user/my_listings.php', 'Listing updated successfully.');
        } else {
            $error = 'Failed to update listing.';
        }
    }
}

$page_title = 'Edit Listing';
include '../includes/header.php';
?>
<div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Listing</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="mb-4">
            <label class="form-label">Current Images</label>
            <div class="d-flex flex-wrap gap-3">
                <?php foreach ($images as $img): ?>
                    <div class="position-relative border rounded p-2" style="width:120px;">
                        <img src="/<?php echo htmlspecialchars($img['image_path']); ?>" class="img-fluid rounded mb-1" style="height:80px;object-fit:cover;width:100%;">
                        <?php if ($img['is_primary']): ?>
                            <span class="badge bg-success position-absolute top-0 start-0 m-1">Primary</span>
                        <?php endif; ?>
                        <a href="/posts/edit.php?id=<?php echo $post_id; ?>&delete_image_id=<?php echo $img['id']; ?>" 
                           class="btn btn-outline-danger btn-sm w-100 my-1" 
                           onclick="return confirm('Delete this image?');">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php if (!$img['is_primary']): ?>
                        <a href="/posts/edit.php?id=<?php echo $post_id; ?>&set_primary_image_id=<?php echo $img['id']; ?>" 
                           class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-star"></i> Set Primary
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="mb-4">
            <label for="post-images" class="form-label">Add Images</label>
            <input type="file" class="form-control" id="post-images" name="images[]" accept="image/*" multiple>
            <div class="form-text">You can upload more images. The first image will be used as the main image if none is set.</div>
            <div id="image-preview-container" class="image-preview-container mt-2"></div>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>">
            </div>
            <div class="col-md-4">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?php if ($status === 'active') echo 'selected'; ?>>Active</option>
                    <option value="pending" <?php if ($status === 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="expired" <?php if ($status === 'expired') echo 'selected'; ?>>Expired</option>
                    <option value="deleted" <?php if ($status === 'deleted') echo 'selected'; ?>>Deleted</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="contact_email" class="form-label">Contact Email</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>">
            </div>
            <div class="col-md-6">
                <label for="contact_phone" class="form-label">Contact Phone</label>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($contact_phone); ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
        <a href="/user/my_listings.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
