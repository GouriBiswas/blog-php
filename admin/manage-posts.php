<?php
require_once '../includes/functions.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get all posts
$sql = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
$result = $conn->query($sql);
$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - Blog Website</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="../assets/css/custom-components.css">
    <style>
        .admin-card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .admin-sidebar .list-group-item {
            border-radius: 0;
            border-left: 0;
            border-right: 0;
            padding: 15px 20px;
            transition: all 0.2s ease;
        }
        
        .admin-sidebar .list-group-item.active {
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .admin-sidebar .list-group-item:not(.active):hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .post-table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        
        .post-table tr {
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .post-table tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .post-table td, .post-table th {
            padding: 15px;
            vertical-align: middle;
        }
        
        .post-table td:first-child, .post-table th:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        .post-table td:last-child, .post-table th:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        
        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 5px;
            transition: all 0.2s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">View Site</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group admin-sidebar">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <a href="dashboard.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-line mr-2"></i> Advanced Dashboard
                    </a>
                    <a href="manage-posts.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-file-alt mr-2"></i> Manage Posts
                    </a>
                    <a href="manage-users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users mr-2"></i> Manage Users
                    </a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-file-alt mr-2"></i> Manage Posts</h2>
                    <a href="add-post.php" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i> Add New Post
                    </a>
                </div>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo ($_SESSION['message_type'] == 'success') ? 'check-circle' : 'exclamation-circle'; ?> mr-2"></i>
                        <?php 
                            echo $_SESSION['message']; 
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                        ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="card admin-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table post-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-info-circle mr-2"></i> No posts found
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($posts as $post): ?>
                                            <tr class="bg-white">
                                                <td><strong>#<?php echo $post['id']; ?></strong></td>
                                                <td>
                                                    <div class="font-weight-bold"><?php echo $post['title']; ?></div>
                                                    <div class="small text-muted mt-1">
                                                        <?php echo substr(strip_tags($post['content']), 0, 50) . '...'; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($post['avatar'])): ?>
                                                            <img src="../assets/uploads/<?php echo $post['avatar']; ?>" alt="<?php echo $post['username']; ?>" class="rounded-circle mr-2" width="30" height="30">
                                                        <?php else: ?>
                                                            <img src="../assets/images/default-avatar.svg" alt="Default Avatar" class="rounded-circle mr-2" width="30" height="30">
                                                        <?php endif; ?>
                                                        <?php echo $post['username']; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light">
                                                        <i class="far fa-calendar-alt mr-1"></i>
                                                        <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="../post.php?id=<?php echo $post['id']; ?>" class="btn btn-info btn-action" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning btn-action" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-action" title="Delete" onclick="return confirm('Are you sure you want to delete this post?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="m-0">© <?php echo date('Y'); ?> Blog Website. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>