<?php
// Include functions
require_once '../includes/functions.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Redirect to homepage
redirect('/index.php', 'You have been successfully logged out.');
?>
