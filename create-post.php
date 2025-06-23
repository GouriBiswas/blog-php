<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle form submission
if (isset($_POST['create_post'])) {
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $image_path = null;
    
    // Validate input
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($content)) {
        $errors[] = "Content is required";
    }
    
    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $upload_result = uploadImage($_FILES['image']);
        
        if ($upload_result['success']) {
            $image_path = $upload_result['file_path'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // If no errors, create the post
    if (empty($errors)) {
        $sql = "INSERT INTO posts (user_id, title, content, image) VALUES ('$user_id', '$title', '$content', '$image_path')";
        
        if ($conn->query($sql) === TRUE) {
            redirect('index.php?success=post_created');
        } else {
            $errors[] = "Error: " . $conn->error;
        }
    }
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Create New Post</h4>
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
                
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($title) ? $title : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo isset($content) ? $content : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Image (Optional)</label>
                        <input type="file" class="form-control-file image-input" id="image" name="image" data-preview-id="image-preview">
                        <img id="image-preview" class="mt-2" style="max-width: 100%; display: none;" alt="Image Preview">
                    </div>
                    <button type="submit" name="create_post" class="btn btn-primary">Create Post</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>