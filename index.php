<?php
require_once 'includes/header.php';

// Get all posts
$posts = getPosts();
?>

<div class="page-header mb-4">
    <h1>Latest Blog Posts</h1>
    <p class="text-muted">Discover interesting stories and ideas</p>
</div>

<div class="row">
    <?php if (empty($posts)): ?>
        <div class="col-12">
            <div class="alert alert-info">No posts found. Be the first to create a post!</div>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php 
                // Get comment count for this post
                $comments = getComments($post['id']);
                $commentCount = count($comments);
                
                // Get actual like count from the database
                $likeCount = getLikesCount($post['id']);
            ?>
            <div class="col-md-4">
                <div class="card">
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?php echo $post['image']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
                    <?php else: ?>
                        <img src="assets/uploads/default-post.jpg.svg" class="card-img-top" alt="Default Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $post['title']; ?></h5>
                        <p class="card-text"><?php echo substr(strip_tags($post['content']), 0, 100); ?>...</p>
                        <div class="post-meta">
                            <div class="post-meta-item">
                                <img src="<?php echo !empty($post['avatar']) ? $post['avatar'] : 'assets/images/default-avatar.svg'; ?>" alt="<?php echo $post['username']; ?>" class="post-avatar">
                                <span><?php echo $post['username']; ?></span>
                            </div>
                            <div class="post-meta-item">
                                <i class="far fa-calendar"></i>
                                <span><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                            <div class="post-meta-item">
                                <i class="far fa-comment"></i>
                                <span><?php echo $commentCount; ?> comments</span>
                            </div>
                            <div class="post-meta-item">
                                <button class="like-btn <?php echo isLoggedIn() && hasUserLikedPost($post['id'], $_SESSION['user_id']) ? 'liked' : ''; ?>" onclick="likePost(<?php echo $post['id']; ?>, this)">
                                    <i class="<?php echo isLoggedIn() && hasUserLikedPost($post['id'], $_SESSION['user_id']) ? 'fas' : 'far'; ?> fa-heart"></i>
                                    <span class="like-count" id="likes-count-<?php echo $post['id']; ?>"><?php echo $likeCount; ?></span>
                                </button>
                            </div>
                        </div>
                        <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary w-100">Read More</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>