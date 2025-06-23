<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if post ID is provided
if (!isset($_GET['id'])) {
    redirect('index.php');
}

// Get post details
$post_id = clean($_GET['id']);
$post = getPost($post_id);

// If post doesn't exist, redirect to home
if (!$post) {
    redirect('index.php');
}

// Check if user is authorized to edit this post
if ($_SESSION['user_id'] != $post['user_id'] && !isAdmin()) {
    redirect('index.php?error=unauthorized');
}

// Handle form submission
if (isset($_POST['update_post'])) {
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);
    $image_path = $post['image'];
    
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
    
    // If no errors, update the post
    if (empty($errors)) {
        $sql = "UPDATE posts SET title = '$title', content = '$content', image = '$image_path' WHERE id = '$post_id'";
        
        if ($conn->query($sql) === TRUE) {
            redirect("post.php?id=$post_id&success=updated");
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
                <h4>Edit Post</h4>
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
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $post['title']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo $post['content']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Image (Optional)</label>
                        <?php if (!empty($post['image'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo $post['image']; ?>" alt="Current Image" style="max-width: 200px;">
                                <p class="text-muted">Current image. Upload a new one to replace it.</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control-file image-input" id="image" name="image" data-preview-id="image-preview">
                        <img id="image-preview" class="mt-2" style="max-width: 100%; display: none;" alt="Image Preview">
                    </div>
                    <button type="submit" name="update_post" class="btn btn-primary">Update Post</button>
                    <a href="post.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>