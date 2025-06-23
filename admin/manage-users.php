<?php
require_once '../includes/functions.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Handle user status change (activate/deactivate)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = clean($_GET['id']);
    $action = clean($_GET['action']);
    
    if ($action == 'activate') {
        $sql = "UPDATE users SET status = 1 WHERE id = '$id'";
        $conn->query($sql);
        redirect('manage-users.php?success=activated');
    } elseif ($action == 'deactivate') {
        // Don't deactivate your own account
        if ($id != $_SESSION['user_id']) {
            $sql = "UPDATE users SET status = 0 WHERE id = '$id'";
            $conn->query($sql);
            redirect('manage-users.php?success=deactivated');
        } else {
            redirect('manage-users.php?error=self_deactivate');
        }
    } elseif ($action == 'make_admin') {
        $sql = "UPDATE users SET is_admin = 1 WHERE id = '$id'";
        $conn->query($sql);
        redirect('manage-users.php?success=admin_granted');
    } elseif ($action == 'remove_admin') {
        // Don't remove your own admin privileges
        if ($id != $_SESSION['user_id']) {
            $sql = "UPDATE users SET is_admin = 0 WHERE id = '$id'";
            $conn->query($sql);
            redirect('manage-users.php?success=admin_removed');
        } else {
            redirect('manage-users.php?error=self_admin_remove');
        }
    }
}

// Get all users
$users = getUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Blog Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="../assets/css/custom-components.css">
    <style>
        .admin-sidebar .list-group-item {
            border-radius: 0;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .admin-sidebar .list-group-item:hover {
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
        }
        
        .admin-sidebar .list-group-item.active {
            background-color: #007bff;
            border-color: #007bff;
            border-left: 3px solid #0056b3;
        }
        
        .admin-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }
        
        .admin-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .user-table th {
            background-color: #f8f9fa;
            border-top: none;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        .user-table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
            border-top: 1px solid #f0f0f0;
        }
        
        .user-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            line-height: 36px;
            text-align: center;
            border-radius: 50%;
            margin: 0 3px;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f0f0f0;
        }
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
        
        .user-table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        
        .user-table tr {
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .user-table tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .user-table td, .user-table th {
            padding: 15px;
            vertical-align: middle;
        }
        
        .user-table td:first-child, .user-table th:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        .user-table td:last-child, .user-table th:last-child {
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
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
                    <a href="manage-posts.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt mr-2"></i> Manage Posts
                    </a>
                    <a href="manage-users.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-users mr-2"></i> Manage Users
                    </a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users mr-2"></i> Manage Users</h2>
                    <a href="add-user.php" class="btn btn-primary">
                        <i class="fas fa-user-plus mr-2"></i> Add New User
                    </a>
                </div>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php if ($_GET['success'] == 'activated'): ?>
                            User activated successfully.
                        <?php elseif ($_GET['success'] == 'deactivated'): ?>
                            User deactivated successfully.
                        <?php elseif ($_GET['success'] == 'admin_granted'): ?>
                            Admin privileges granted successfully.
                        <?php elseif ($_GET['success'] == 'admin_removed'): ?>
                            Admin privileges removed successfully.
                        <?php endif; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php if ($_GET['error'] == 'self_deactivate'): ?>
                            You cannot deactivate your own account.
                        <?php elseif ($_GET['error'] == 'self_admin_remove'): ?>
                            You cannot remove your own admin privileges.
                        <?php endif; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="card admin-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table user-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-info-circle mr-2"></i> No users found
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr class="bg-white">
                                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($user['avatar'])): ?>
                                                            <img src="../assets/uploads/<?php echo $user['avatar']; ?>" alt="<?php echo $user['username']; ?>" class="user-avatar mr-3">
                                                        <?php else: ?>
                                                            <img src="../assets/images/default-avatar.svg" alt="Default Avatar" class="user-avatar mr-3">
                                                        <?php endif; ?>
                                                        <div>
                                                            <div class="font-weight-bold"><?php echo $user['username']; ?></div>
                                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                                <span class="badge badge-light">You</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo $user['email']; ?></td>
                                                <td>
                                                    <?php if ($user['is_admin']): ?>
                                                        <span class="badge badge-primary">
                                                            <i class="fas fa-user-shield mr-1"></i> Admin
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-user mr-1"></i> User
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($user['status']) && $user['status'] == 0): ?>
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times-circle mr-1"></i> Inactive
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle mr-1"></i> Active
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light">
                                                        <i class="far fa-calendar-alt mr-1"></i>
                                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <?php if (isset($user['status']) && $user['status'] == 0): ?>
                                                            <a href="manage-users.php?action=activate&id=<?php echo $user['id']; ?>" class="btn btn-success btn-action" title="Activate">
                                                                <i class="fas fa-toggle-on"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="manage-users.php?action=deactivate&id=<?php echo $user['id']; ?>" class="btn btn-warning btn-action" title="Deactivate">
                                                                <i class="fas fa-toggle-off"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($user['is_admin']): ?>
                                                            <a href="manage-users.php?action=remove_admin&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-action" title="Remove Admin">
                                                                <i class="fas fa-user-minus"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="manage-users.php?action=make_admin&id=<?php echo $user['id']; ?>" class="btn btn-primary btn-action" title="Make Admin">
                                                                <i class="fas fa-user-shield"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge badge-light">Current User</span>
                                                    <?php endif; ?>
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