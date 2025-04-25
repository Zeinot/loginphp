<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php', 'You must be logged in to delete listings.', 'danger');
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
    redirect('/user/my_listings.php', 'You do not have permission to delete this listing.', 'danger');
}

// Delete post (cascades to images and post_categories)
$sql = "DELETE FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $post_id);
if ($stmt->execute()) {
    redirect('/user/my_listings.php', 'Listing deleted successfully.');
} else {
    redirect('/user/my_listings.php', 'Failed to delete listing.', 'danger');
}
