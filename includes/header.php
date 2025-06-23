<?php require_once 'functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Website</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/blog-main/assets/css/style.css">
    <link rel="stylesheet" href="/blog-main/assets/css/modern.css">
    <link rel="stylesheet" href="/blog-main/assets/css/custom-components.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/blog-main/index.php"><i class="fas fa-blog mr-2"></i>Blog Website</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/blog-main/index.php">Home</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/blog-main/profile.php">My Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/blog-main/create-post.php">Create Post</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/blog-main/admin/index.php">Admin</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/blog-main/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/blog-main/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/blog-main/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <div class="theme-toggle" onclick="toggleTheme()">
                            <i id="theme-icon" class="fas fa-moon theme-toggle-icon"></i>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">