<?php
// Use the server document root to get the absolute path to functions.php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once $docRoot . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CraigsList Clone - Modern Classified Ads</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/index.php">
                <i class="fas fa-list-alt me-2"></i>ListItAll
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-th-list me-1"></i> Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            $nav_categories = getCategories();
                            foreach ($nav_categories as $category) {
                                echo '<li><a class="dropdown-item" href="/index.php?category=' . $category['id'] . '">' . $category['name'] . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/index.php"><i class="fas fa-cog me-1"></i> Admin Panel</a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <form class="d-flex mx-auto search-form" action="/index.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search listings..." aria-label="Search">
                        <button class="btn btn-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-success text-white post-btn" href="/posts/create.php">
                            <i class="fas fa-plus-circle me-1"></i> Post Ad
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Account'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/user/profile.php"><i class="fas fa-user me-1"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="/user/my_listings.php"><i class="fas fa-list me-1"></i> My Listings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/auth/login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/auth/register.php"><i class="fas fa-user-plus me-1"></i> Register</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container py-4">
        <?php displayFlashMessages(); ?>
