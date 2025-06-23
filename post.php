<?php
require_once 'includes/header.php';

// Check if post ID is provided
if (!isset($_GET['id'])) {
    redirect('index.php');
}

// Get post details
$post = getPost($_GET['id']);

// If post doesn't exist, redirect to home
if (!$post) {
    redirect('index.php');
}

// Handle comment submission
if (isset($_POST['submit_comment']) && isLoggedIn()) {
    $comment = clean($_POST['comment']);
    $post_id = clean($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    if (!empty($comment)) {
        $sql = "INSERT INTO comments (post_id, user_id, comment) VALUES ('$post_id', '$user_id', '$comment')";
        if ($conn->query($sql) === TRUE) {
            // Refresh the page to show the new comment
            redirect("post.php?id=$post_id&success=1");
        } else {
            $error = "Error: " . $conn->error;
        }
    } else {
        $error = "Comment cannot be empty";
    }
}

// Get comments for this post
$comments = getComments($post['id']);
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Comment added successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card mb-4 post-card solid-card">
    <?php if (!empty($post['image'])): ?>
        <img src="<?php echo $post['image']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
    <?php else: ?>
        <img src="/blog-main/assets/uploads/default-post.jpg.svg" class="card-img-top" alt="Default Image">
    <?php endif; ?>
    <div class="card-body">
        <h1 class="card-title"><?php echo $post['title']; ?></h1>
        <div class="post-meta mb-3">
            <span class="post-author">
                <?php if (!empty($post['avatar'])): ?>
                    <img src="<?php echo $post['avatar']; ?>" alt="<?php echo $post['username']; ?>" class="post-avatar">
                <?php else: ?>
                    <img src="/blog-main/assets/images/default-avatar.svg" alt="<?php echo $post['username']; ?>" class="post-avatar">
                <?php endif; ?>
                <?php echo $post['username']; ?>
            </span>
            <span class="post-date"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
        </div>
                
                <?php 
                    // Get actual like count from the database
                    $likeCount = getLikesCount($post['id']);
                ?>
                
                <div class="post-actions mb-4">
                    <button class="btn btn-sm btn-outline-danger like-button <?php echo isLoggedIn() && hasUserLikedPost($post['id'], $_SESSION['user_id']) ? 'liked' : ''; ?>" onclick="return likePost(<?php echo $post['id']; ?>, this)">
                        <i class="<?php echo isLoggedIn() && hasUserLikedPost($post['id'], $_SESSION['user_id']) ? 'fas' : 'far'; ?> fa-heart"></i> <span class="like-count" id="likes-count-<?php echo $post['id']; ?>"><?php echo $likeCount; ?></span> Likes
                    </button>
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="far fa-comment"></i> <?php echo count($comments); ?> Comments
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="sharePost(<?php echo $post['id']; ?>)">
                        <i class="fas fa-share"></i> Share
                    </button>
                </div>
                
                <div class="post-content">
                    <?php echo nl2br($post['content']); ?>
                </div>
                
                <div class="post-actions mt-4 d-flex justify-content-between align-items-center">
                    <div class="post-stats">
                        <span class="post-likes mr-3">
                            <button class="like-btn <?php echo isLoggedIn() && hasUserLikedPost($post['id'], $_SESSION['user_id']) ? 'liked' : ''; ?>" onclick="likePost(<?php echo $post['id']; ?>, this)">
                                <i class="<?php echo isLoggedIn() && hasUserLikedPost($post['id'], $_SESSION['user_id']) ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                            <span class="likes-count" id="likes-count-<?php echo $post['id']; ?>"><?php echo getLikesCount($post['id']); ?></span> Likes
                        </span>
                        <span class="post-comments">
                            <i class="fas fa-comments"></i> <?php echo count($comments); ?> Comments
                        </span>
                        <span class="post-share">
                            <button class="btn btn-sm btn-outline-secondary" onclick="sharePost(<?php echo $post['id']; ?>)">
                                <i class="fas fa-share-alt"></i> Share
                            </button>
                        </span>
                    </div>
                    
                    <?php if (isLoggedIn() && ($_SESSION['user_id'] == $post['user_id'] || isAdmin())): ?>
                    <div>
                        <a href="/blog-main/edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Edit</a>
                        <a href="/blog-main/delete-post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Comments Section -->
        <div class="card mb-4 solid-card">
            <div class="card-header gradient-bg">
                <h4>Comments (<?php echo count($comments); ?>)</h4>
            </div>
            <div class="card-body">
                <?php if (isLoggedIn()): ?>
                <form method="post" action="" class="comment-form">
                    <div class="form-group">
                        <textarea class="form-control" name="comment" rows="3" placeholder="Write a comment..." required></textarea>
                    </div>
                    <button type="submit" name="submit_comment" class="btn btn-primary">Submit Comment</button>
                </form>
                <hr>
                <?php else: ?>
                    <div class="alert alert-info">Please <a href="/blog-main/login.php">login</a> to leave a comment.</div>
                <?php endif; ?>
                
                <?php if (empty($comments)): ?>
                    <div class="alert alert-info">No comments yet. Be the first to comment!</div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
        <div class="comment mb-3">
            <div class="comment-header d-flex align-items-center">
                <?php 
                $comment_user = getUser($comment['user_id']);
                $avatar = isset($comment_user['avatar']) ? $comment_user['avatar'] : '';
                ?>
                <div class="comment-avatar mr-2">
                    <?php if (!empty($avatar)): ?>
                        <img src="<?php echo $avatar; ?>" alt="<?php echo $comment['username']; ?>" class="comment-user-avatar">
                    <?php else: ?>
                        <span class="comment-avatar-placeholder"><i class="fas fa-user"></i></span>
                    <?php endif; ?>
                </div>
                <div>
                    <strong><?php echo $comment['username']; ?></strong>
                    <small class="text-muted d-block"><?php echo date('F j, Y g:i a', strtotime($comment['created_at'])); ?></small>
                </div>
            </div>
            <div class="comment-body mt-2">
                <?php echo nl2br($comment['comment']); ?>
            </div>
        </div>
        <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>