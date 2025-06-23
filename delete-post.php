<?php
require_once 'includes/functions.php';

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

// Check if user is authorized to delete this post
if ($_SESSION['user_id'] != $post['user_id'] && !isAdmin()) {
    redirect('index.php?error=unauthorized');
}

// Delete the post
$sql = "DELETE FROM posts WHERE id = '$post_id'";

if ($conn->query($sql) === TRUE) {
    redirect('index.php?success=post_deleted');
} else {
    redirect("post.php?id=$post_id&error=delete_failed");
}
?>