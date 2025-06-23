<?php
require_once '../includes/functions.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get counts for dashboard
$sql = "SELECT COUNT(*) as post_count FROM posts";
$result = $conn->query($sql);
$post_count = $result->fetch_assoc()['post_count'];

$sql = "SELECT COUNT(*) as user_count FROM users";
$result = $conn->query($sql);
$user_count = $result->fetch_assoc()['user_count'];

$sql = "SELECT COUNT(*) as comment_count FROM comments";
$result = $conn->query($sql);
$comment_count = $result->fetch_assoc()['comment_count'];

// Get recent activity
$sql = "SELECT 'post' as type, p.id, p.title as content, p.created_at, u.username 
        FROM posts p JOIN users u ON p.user_id = u.id 
        UNION 
        SELECT 'comment' as type, c.id, c.content, c.created_at, u.username 
        FROM comments c JOIN users u ON c.user_id = u.id 
        ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);
$recent_activity = [];
while ($row = $result->fetch_assoc()) {
    $recent_activity[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blog Website</title>
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
        
        .admin-card .card-body {
            padding: 25px;
        }
        
        .admin-card .card-title {
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .admin-card .display-4 {
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .admin-card a {
            font-weight: 500;
            transition: opacity 0.2s ease;
        }
        
        .admin-card a:hover {
            opacity: 0.8;
            text-decoration: none;
        }
        
        .activity-item {
            padding: 15px;
            border-left: 3px solid #007bff;
            background-color: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 0 5px 5px 0;
            transition: transform 0.2s ease;
        }
        
        .activity-item:hover {
            transform: translateX(5px);
        }
        
        .activity-item.comment {
            border-left-color: #28a745;
        }
        
        .activity-meta {
            font-size: 0.85rem;
            color: #6c757d;
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-chart-line mr-2"></i> Advanced Dashboard
                    </a>
                    <a href="manage-posts.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt mr-2"></i> Manage Posts
                    </a>
                    <a href="manage-users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users mr-2"></i> Manage Users
                    </a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-chart-line mr-2"></i> Advanced Dashboard</h2>
                    <span class="text-muted">Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3 admin-card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-file-alt mr-2"></i> Total Posts</h5>
                                <p class="card-text display-4"><?php echo $post_count; ?></p>
                                <a href="manage-posts.php" class="text-white">
                                    <i class="fas fa-arrow-circle-right mr-1"></i> Manage Posts
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3 admin-card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-users mr-2"></i> Total Users</h5>
                                <p class="card-text display-4"><?php echo $user_count; ?></p>
                                <a href="manage-users.php" class="text-white">
                                    <i class="fas fa-arrow-circle-right mr-1"></i> Manage Users
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3 admin-card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-comments mr-2"></i> Total Comments</h5>
                                <p class="card-text display-4"><?php echo $comment_count; ?></p>
                                <a href="#" class="text-white">
                                    <i class="fas fa-arrow-circle-right mr-1"></i> View Comments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card admin-card">
                            <div class="card-header">
                                <h5><i class="fas fa-history mr-2"></i> Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_activity)): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i> No recent activity found.
                                    </div>
                                <?php else: ?>
                                    <div class="activity-list mt-3">
                                        <?php foreach ($recent_activity as $activity): ?>
                                            <div class="activity-item <?php echo ($activity['type'] == 'comment') ? 'comment' : ''; ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">
                                                        <?php if ($activity['type'] == 'post'): ?>
                                                            <i class="fas fa-file-alt mr-2"></i>
                                                            <span class="badge badge-primary">Post</span>
                                                        <?php else: ?>
                                                            <i class="fas fa-comment mr-2"></i>
                                                            <span class="badge badge-success">Comment</span>
                                                        <?php endif; ?>
                                                        <?php echo $activity['content']; ?>
                                                    </h6>
                                                    <span class="badge badge-light">
                                                        <i class="far fa-clock mr-1"></i> <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                                    </span>
                                                </div>
                                                <div class="activity-meta">
                                                    <i class="fas fa-user mr-1"></i> By <?php echo $activity['username']; ?>
                                                    <a href="../post.php?id=<?php echo $activity['id']; ?>" class="ml-2 text-primary">
                                                        <i class="fas fa-external-link-alt mr-1"></i> View
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
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