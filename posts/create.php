<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/auth/login.php', 'You must be logged in to create a post.', 'danger');
}

// Get categories
$categories = getCategories();

// Initialize variables
$title = '';
$description = '';
$price = '';
$location = '';
$contact_email = '';
$contact_phone = '';
$category_ids = [];
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = !empty($_POST['price']) ? (float)$_POST['price'] : null;
    $location = sanitize($_POST['location'] ?? '');
    $contact_email = sanitize($_POST['contact_email'] ?? '');
    $contact_phone = sanitize($_POST['contact_phone'] ?? '');
    $category_ids = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    // Validate form data
    if (empty($title) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } elseif (empty($category_ids)) {
        $error = 'Please select at least one category.';
    } else {
        // Insert post into database
        $user_id = $_SESSION['user_id'];
        $sql = "INSERT INTO posts (user_id, title, description, price, location, contact_email, contact_phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdsss", $user_id, $title, $description, $price, $location, $contact_email, $contact_phone);
        
        if ($stmt->execute()) {
            $post_id = $conn->insert_id;
            
            // Insert post categories
            foreach ($category_ids as $category_id) {
                $sql = "INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $post_id, $category_id);
                $stmt->execute();
            }
            
            // Handle image uploads
            $has_primary = false;
            
            error_log("DEBUG: Starting image upload handling for post ID: $post_id");
            error_log("DEBUG: PHP POST max size: " . ini_get('post_max_size'));
            error_log("DEBUG: PHP upload max filesize: " . ini_get('upload_max_filesize'));
            error_log("DEBUG: PHP max file uploads: " . ini_get('max_file_uploads'));
            
            // Dump complete $_FILES array
            error_log("DEBUG: Complete _FILES array: " . json_encode($_FILES));
            
            if (isset($_FILES['images'])) {
                error_log("DEBUG: Found 'images' in _FILES array");
                
                if (!empty($_FILES['images']['name'][0])) {
                    error_log("DEBUG: At least one file was uploaded");
                    error_log("DEBUG: Number of uploaded files: " . count($_FILES['images']['name']));
                    
                    for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                        error_log("DEBUG: Processing file $i: {$_FILES['images']['name'][$i]}");
                        error_log("DEBUG: File $i error code: {$_FILES['images']['error'][$i]}");
                        
                        if ($_FILES['images']['error'][$i] == 0) {
                            error_log("DEBUG: File $i has no upload errors");
                            
                            $file = [
                                'name' => $_FILES['images']['name'][$i],
                                'type' => $_FILES['images']['type'][$i],
                                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                                'error' => $_FILES['images']['error'][$i],
                                'size' => $_FILES['images']['size'][$i]
                            ];
                            
                            error_log("DEBUG: Prepared file array for uploadImage: " . json_encode($file));
                            
                            // Set first image as primary
                            $is_primary = (!$has_primary) ? true : false;
                            error_log("DEBUG: Is primary image: " . ($is_primary ? 'yes' : 'no'));
                            
                            // Try to upload the image
                            $upload_result = uploadImage($file, $post_id, $is_primary);
                            error_log("DEBUG: Upload result for file $i: " . ($upload_result ? "Success with ID: $upload_result" : "Failed"));
                            
                            if ($upload_result) {
                                $has_primary = true;
                                error_log("DEBUG: Image successfully uploaded and saved to database");
                            } else {
                                error_log("DEBUG: Image upload function returned false");
                            }
                        } else {
                            // Log detailed error based on PHP upload error code
                            $errorMessages = [
                                1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
                                2 => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form",
                                3 => "The uploaded file was only partially uploaded",
                                4 => "No file was uploaded",
                                6 => "Missing a temporary folder",
                                7 => "Failed to write file to disk",
                                8 => "A PHP extension stopped the file upload"
                            ];
                            
                            $errorMessage = isset($errorMessages[$_FILES['images']['error'][$i]]) 
                                ? $errorMessages[$_FILES['images']['error'][$i]] 
                                : "Unknown error";
                                
                            error_log("DEBUG: File $i upload error: $errorMessage");
                        }
                    }
                } else {
                    error_log("DEBUG: Images array exists but is empty (no files selected)");
                }
            } else {
                error_log("DEBUG: No 'images' key found in _FILES array");
                error_log("DEBUG: Available _FILES keys: " . implode(", ", array_keys($_FILES)));
            }
            
            // Redirect to the new post
            redirect('/posts/view.php?id=' . $post_id, 'Your post has been created successfully!');
        } else {
            $error = 'Error creating post: ' . $conn->error;
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Post</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="form-container">
                <h2 class="form-title">
                    <i class="fas fa-plus-circle me-2"></i>Create a New Listing
                </h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $title; ?>" required>
                        <div class="invalid-feedback">
                            Please enter a title for your listing.
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="categories" class="form-label">Categories <span class="text-danger">*</span></label>
                        <div class="row">
                            <?php foreach ($categories as $category): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>" id="category-<?php echo $category['id']; ?>" <?php echo in_array($category['id'], $category_ids) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="category-<?php echo $category['id']; ?>">
                                            <?php echo $category['name']; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="invalid-feedback">
                            Please select at least one category.
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?php echo $price; ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required><?php echo $description; ?></textarea>
                        <div class="invalid-feedback">
                            Please enter a description for your listing.
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="post-images" class="form-label">Images</label>
                        <input type="file" class="form-control" id="post-images" name="images[]" accept="image/*" multiple>
                        <div class="form-text">You can upload multiple images. The first image will be used as the main image.</div>
                        <div id="image-preview-container" class="image-preview-container mt-2"></div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo $contact_email; ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo $contact_phone; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="/terms.php" target="_blank">Terms of Service</a> and confirm that my post adheres to the <a href="/posting-rules.php" target="_blank">posting guidelines</a>.
                        </label>
                        <div class="invalid-feedback">
                            You must agree to the terms before submitting.
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Listing
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
