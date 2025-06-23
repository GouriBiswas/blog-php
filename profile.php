<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user information
$user = getUser($_SESSION['user_id']);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $bio = clean($_POST['bio']);
    $current_password = clean($_POST['current_password']);
    $new_password = clean($_POST['new_password']);
    $confirm_password = clean($_POST['confirm_password']);
    
    // Handle avatar upload
    $avatar_path = isset($user['avatar']) ? $user['avatar'] : null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
        $upload_result = uploadImage($_FILES['avatar'], 'avatar');
        if ($upload_result['success']) {
            $avatar_path = $upload_result['file_path'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // Validate input
    $errors = [];
    
    // Check if username is changed and already exists
    if ($username !== $user['username']) {
        $sql = "SELECT * FROM users WHERE username = '$username' AND id != '{$user['id']}'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $errors[] = "Username already exists";
        }
    }
    
    // Check if email is changed and already exists
    if ($email !== $user['email']) {
        $sql = "SELECT * FROM users WHERE email = '$email' AND id != '{$user['id']}'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // If password is being changed
    if (!empty($current_password)) {
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        // Validate new password
        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long";
        }
        
        // Confirm new password
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }
    
    // If no errors, update the profile
    if (empty($errors)) {
        // Check if bio column exists
        $check_bio = $conn->query("SHOW COLUMNS FROM users LIKE 'bio'");
        $bio_exists = $check_bio->num_rows > 0;
        
        // Start with basic profile update
        if ($bio_exists) {
            $sql = "UPDATE users SET username = '$username', email = '$email', bio = '$bio', avatar = '$avatar_path' WHERE id = '{$user['id']}'";
        } else {
            $sql = "UPDATE users SET username = '$username', email = '$email', avatar = '$avatar_path' WHERE id = '{$user['id']}'";
        }
        
        // If password is being changed, update it too
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            if ($bio_exists) {
                $sql = "UPDATE users SET username = '$username', email = '$email', bio = '$bio', avatar = '$avatar_path', password = '$hashed_password' WHERE id = '{$user['id']}'";
            } else {
                $sql = "UPDATE users SET username = '$username', email = '$email', avatar = '$avatar_path', password = '$hashed_password' WHERE id = '{$user['id']}'";
            }
        }
        
        if ($conn->query($sql) === TRUE) {
            // Update session username if it was changed
            if ($username !== $user['username']) {
                $_SESSION['username'] = $username;
            }
            
            // Refresh user data
            $user = getUser($_SESSION['user_id']);
            $success = "Profile updated successfully!";
        } else {
            $errors[] = "Error updating profile: " . $conn->error;
        }
    }
}

// Get user's posts
$sql = "SELECT * FROM posts WHERE user_id = '{$user['id']}' ORDER BY created_at DESC";
$result = $conn->query($sql);
$user_posts = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $user_posts[] = $row;
    }
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Profile Information</h4>
            </div>
            <div class="card-body text-center">
                <div class="profile-avatar-container mb-3">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?php echo $user['avatar']; ?>" alt="<?php echo $user['username']; ?>" class="profile-avatar">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h5><?php echo $user['username']; ?></h5>
                <?php if (isset($user['bio']) && !empty($user['bio'])): ?>
                    <p class="text-muted"><?php echo $user['bio']; ?></p>
                <?php endif; ?>
                <hr>
                <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                <p><strong>Joined:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                <p><strong>Role:</strong> <?php echo $user['is_admin'] ? 'Administrator' : 'User'; ?></p>
                <p><strong>Posts:</strong> <?php echo count($user_posts); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Edit Profile</h4>
            </div>
            <div class="card-body">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="avatar">Profile Picture</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="avatar" name="avatar" accept="image/*" onchange="previewImage(this, 'avatar-preview')">
                            <label class="custom-file-label" for="avatar">Choose file</label>
                        </div>
                        <div class="mt-2">
                            <img id="avatar-preview" src="<?php echo isset($user['avatar']) && !empty($user['avatar']) ? $user['avatar'] : ''; ?>" class="img-thumbnail" style="max-width: 200px; <?php echo !isset($user['avatar']) || empty($user['avatar']) ? 'display: none;' : ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo isset($user['bio']) ? $user['bio'] : ''; ?></textarea>
                    </div>
                    
                    <h5 class="mt-4">Change Password</h5>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        <small class="form-text text-muted">Leave blank if you don't want to change your password.</small>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h4>My Posts</h4>
            </div>
            <div class="card-body">
                <?php if (empty($user_posts)): ?>
                    <div class="alert alert-info">You haven't created any posts yet.</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($user_posts as $post): ?>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo $post['title']; ?></h5>
                                    <small><?php echo date('M d, Y', strtotime($post['created_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?php echo substr(strip_tags($post['content']), 0, 100); ?>...</p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>