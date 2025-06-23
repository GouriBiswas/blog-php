<?php
require_once '../includes/functions.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Check if user ID is provided
if (!isset($_GET['id'])) {
    redirect('manage-users.php');
}

$user_id = clean($_GET['id']);

// Get user data
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    redirect('manage-users.php');
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Validate inputs
    if (empty($username) || empty($email)) {
        $error = "Username and email are required.";
    } else {
        // Check if username already exists (excluding current user)
        $check_sql = "SELECT id FROM users WHERE username = '$username' AND id != '$user_id'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            // Check if email already exists (excluding current user)
            $check_sql = "SELECT id FROM users WHERE email = '$email' AND id != '$user_id'";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                $error = "Email already exists.";
            } else {
                // Handle avatar upload
                $avatar = $user['avatar']; // Keep existing avatar by default
                
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['avatar']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        $new_filename = uniqid('avatar_') . '.' . $filetype;
                        $upload_dir = '../assets/uploads/';
                        
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_filename)) {
                            // Delete old avatar if it exists and is not the default
                            if (!empty($avatar) && file_exists($upload_dir . $avatar) && $avatar != 'default-avatar.svg') {
                                unlink($upload_dir . $avatar);
                            }
                            
                            $avatar = $new_filename;
                        } else {
                            $error = "Failed to upload avatar.";
                        }
                    } else {
                        $error = "Invalid file type. Allowed types: jpg, jpeg, png, gif.";
                    }
                }
                
                // Update user data if no errors
                if (empty($error)) {
                    // Don't allow changing your own admin status or active status
                    if ($user_id == $_SESSION['user_id']) {
                        $is_admin = $user['is_admin']; // Keep current admin status
                        $status = 1; // Keep active
                    }
                    
                    $update_sql = "UPDATE users SET 
                        username = '$username', 
                        email = '$email', 
                        is_admin = $is_admin, 
                        status = $status, 
                        avatar = '$avatar' 
                        WHERE id = '$user_id'";
                    
                    if ($conn->query($update_sql)) {
                        $success = "User updated successfully.";
                        // Refresh user data
                        $result = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
                        $user = $result->fetch_assoc();
                    } else {
                        $error = "Error updating user: " . $conn->error;
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Blog Admin</title>
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
        
        .form-control {
            border-radius: 0.25rem;
            border: 1px solid #ced4da;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .user-avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f0f0f0;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include_once '../includes/navbar.php'; ?>
    
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
                    <h2><i class="fas fa-user-edit mr-2"></i> Edit User</h2>
                    <a href="manage-users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Users
                    </a>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <div class="card admin-card">
                    <div class="card-body">
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <div class="form-group">
                                        <label for="avatar"><strong>User Avatar</strong></label>
                                        <div class="d-flex justify-content-center mb-3">
                                            <?php if (!empty($user['avatar'])): ?>
                                                <img src="../assets/uploads/<?php echo $user['avatar']; ?>" alt="<?php echo $user['username']; ?>" class="user-avatar-preview">
                                            <?php else: ?>
                                                <img src="../assets/images/default-avatar.svg" alt="Default Avatar" class="user-avatar-preview">
                                            <?php endif; ?>
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="avatar" name="avatar">
                                            <label class="custom-file-label" for="avatar">Choose new avatar</label>
                                        </div>
                                        <small class="form-text text-muted">Max file size: 2MB. Allowed formats: JPG, PNG, GIF.</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="username"><i class="fas fa-user mr-1"></i> <strong>Username</strong></label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email"><i class="fas fa-envelope mr-1"></i> <strong>Email</strong></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-user-shield mr-1"></i> <strong>Role</strong></label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="is_admin" name="is_admin" <?php echo $user['is_admin'] ? 'checked' : ''; ?> <?php echo $user_id == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                    <label class="custom-control-label" for="is_admin">Admin privileges</label>
                                                </div>
                                                <?php if ($user_id == $_SESSION['user_id']): ?>
                                                    <small class="form-text text-muted">You cannot change your own admin status.</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-toggle-on mr-1"></i> <strong>Status</strong></label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="status" name="status" <?php echo isset($user['status']) && $user['status'] == 1 ? 'checked' : ''; ?> <?php echo $user_id == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                    <label class="custom-control-label" for="status">Active account</label>
                                                </div>
                                                <?php if ($user_id == $_SESSION['user_id']): ?>
                                                    <small class="form-text text-muted">You cannot deactivate your own account.</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-2"></i> Save Changes
                                        </button>
                                        <a href="manage-users.php" class="btn btn-secondary ml-2">
                                            <i class="fas fa-times mr-2"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Display file name when selected
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
            
            // Preview image
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.user-avatar-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>