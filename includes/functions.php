<?php
session_start();

// Include database connection
require_once 'db.php';

// Function to sanitize user input
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Function to redirect with a message
function redirect($url, $message = '', $status = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'status' => $status
    ];
    header("Location: $url");
    exit();
}

// Function to display flash messages
function displayFlashMessages() {
    if (isset($_SESSION['flash'])) {
        $status = $_SESSION['flash']['status'];
        $message = $_SESSION['flash']['message'];
        $alertClass = ($status == 'success') ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        unset($_SESSION['flash']);
    }
}

// Function to get user by ID
function getUserById($userId) {
    global $conn;
    $userId = (int)$userId;
    $result = $conn->query("SELECT * FROM users WHERE id = $userId");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Function to get all posts with pagination
function getPosts($page = 1, $limit = 10, $categoryId = null, $search = null) {
    global $conn;
    
    $offset = ($page - 1) * $limit;
    $whereClause = " WHERE status = 'active'";
    
    if ($categoryId) {
        $categoryId = (int)$categoryId;
        $whereClause .= " AND p.id IN (SELECT post_id FROM post_categories WHERE category_id = $categoryId)";
    }
    
    if ($search) {
        $search = sanitize($search);
        $whereClause .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
    }
    
    $sql = "SELECT p.*, 
                   u.username as author,
                   (SELECT image_path FROM images WHERE post_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM posts p
            JOIN users u ON p.user_id = u.id
            $whereClause
            ORDER BY created_at DESC
            LIMIT $offset, $limit";
            
    $result = $conn->query($sql);
    
    $posts = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
    
    return $posts;
}

// Function to get post by ID
function getPostById($postId) {
    global $conn;
    $postId = (int)$postId;
    
    $sql = "SELECT p.*, 
                   u.username as author,
                   u.email as author_email,
                   u.phone as author_phone,
                   u.profile_image as author_image
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = $postId";
            
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
        
        // Get post images
        $imagesSql = "SELECT * FROM images WHERE post_id = $postId";
        $imagesResult = $conn->query($imagesSql);
        $post['images'] = [];
        
        if ($imagesResult && $imagesResult->num_rows > 0) {
            while ($image = $imagesResult->fetch_assoc()) {
                $post['images'][] = $image;
            }
        }
        
        // Get post categories
        $categoriesSql = "SELECT c.* 
                          FROM categories c
                          JOIN post_categories pc ON c.id = pc.category_id
                          WHERE pc.post_id = $postId";
        $categoriesResult = $conn->query($categoriesSql);
        $post['categories'] = [];
        
        if ($categoriesResult && $categoriesResult->num_rows > 0) {
            while ($category = $categoriesResult->fetch_assoc()) {
                $post['categories'][] = $category;
            }
        }
        
        // Increment view count
        $conn->query("UPDATE posts SET views = views + 1 WHERE id = $postId");
        
        return $post;
    }
    
    return null;
}

// Function to get all categories
function getCategories() {
    global $conn;
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Function to upload image
function uploadImage($file, $postId, $isPrimary = false) {
    global $conn;
    
    // Debug: log file details
    error_log("DEBUG: Starting image upload process for post ID: $postId");
    error_log("DEBUG: File details: " . json_encode($file));
    
    // Check if file was uploaded without errors
    if ($file['error'] == 0) {
        // Use document root for absolute path
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        $uploadDir = $docRoot . '/uploads/posts/';
        error_log("DEBUG: Upload directory: $uploadDir");
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            error_log("DEBUG: Creating directory: $uploadDir");
            $dirCreated = mkdir($uploadDir, 0777, true);
            error_log("DEBUG: Directory creation result: " . ($dirCreated ? 'success' : 'failed') );
            
            // Check if directory exists after creation attempt
            error_log("DEBUG: Directory exists after creation attempt: " . (file_exists($uploadDir) ? 'yes' : 'no'));
        }
        
        // Generate unique filename
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetFile = $uploadDir . $fileName;
        $relativePath = "uploads/posts/" . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        error_log("DEBUG: Target file path: $targetFile");
        error_log("DEBUG: Relative path for DB: $relativePath");
        
        // Check if image file is an actual image
        $check = getimagesize($file['tmp_name']);
        error_log("DEBUG: getimagesize result: " . ($check ? json_encode($check) : 'false'));
        if ($check === false) {
            error_log("ERROR: File is not an image: " . $file['name']);
            return false;
        }
        
        // Check file size (limit to 5MB)
        error_log("DEBUG: File size: " . $file['size'] . " bytes");
        if ($file['size'] > 5000000) {
            error_log("ERROR: File is too large: " . $file['size']);
            return false;
        }
        
        // Allow certain file formats
        error_log("DEBUG: File type: $imageFileType");
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            error_log("ERROR: Invalid file type: " . $imageFileType);
            return false;
        }
        
        // Check if target directory is writable
        error_log("DEBUG: Upload directory is writable: " . (is_writable($uploadDir) ? 'yes' : 'no'));
        
        // Check if temporary file exists
        error_log("DEBUG: Temporary file exists: " . (file_exists($file['tmp_name']) ? 'yes' : 'no'));
        
        // Try to upload file
        error_log("DEBUG: Attempting to move file from {$file['tmp_name']} to $targetFile");
        $moveResult = move_uploaded_file($file['tmp_name'], $targetFile);
        error_log("DEBUG: move_uploaded_file result: " . ($moveResult ? 'success' : 'failed'));
        
        if ($moveResult) {
            // Check if file exists at destination
            error_log("DEBUG: File exists at destination: " . (file_exists($targetFile) ? 'yes' : 'no'));
            
            // Insert image into database
            $isPrimaryInt = $isPrimary ? 1 : 0;
            $sql = "INSERT INTO images (post_id, image_path, is_primary) VALUES (?, ?, ?)";
            error_log("DEBUG: SQL query: $sql with params: [$postId, $relativePath, $isPrimaryInt]");
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("ERROR: Prepare statement failed: " . $conn->error);
                return false;
            }
            
            $bindResult = $stmt->bind_param("isi", $postId, $relativePath, $isPrimaryInt);
            if (!$bindResult) {
                error_log("ERROR: Bind params failed: " . $stmt->error);
                return false;
            }
            
            $executeResult = $stmt->execute();
            error_log("DEBUG: SQL execute result: " . ($executeResult ? 'success' : 'failed'));
            
            if ($executeResult) {
                $insertId = $conn->insert_id;
                error_log("DEBUG: Image record inserted with ID: $insertId");
                return $insertId;
            } else {
                error_log("ERROR: Database insert failed: " . $conn->error);
            }
        } else {
            error_log("ERROR: Failed to move uploaded file from " . $file['tmp_name'] . " to " . $targetFile);
            error_log("ERROR: Upload error code: " . $file['error']);
            error_log("ERROR: PHP last error: " . json_encode(error_get_last()));
        }
    } else {
        error_log("ERROR: File upload error code: " . $file['error']);
        // Provide more detailed error message based on error code
        $errorMessages = [
            1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
            2 => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form",
            3 => "The uploaded file was only partially uploaded",
            4 => "No file was uploaded",
            6 => "Missing a temporary folder",
            7 => "Failed to write file to disk",
            8 => "A PHP extension stopped the file upload"
        ];
        if (isset($errorMessages[$file['error']])) {
            error_log("ERROR: " . $errorMessages[$file['error']]);
        }
    }
    
    error_log("DEBUG: Image upload process failed, returning false");
    return false;
}

// Function to format date
function formatDate($date) {
    return date("F j, Y", strtotime($date));
}
?>
