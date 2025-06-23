<?php
require_once 'includes/functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to like posts'
    ]);
    exit;
}

// Check if post_id is provided
if (!isset($_POST['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID is required'
    ]);
    exit;
}

$post_id = (int) $_POST['post_id'];
$user_id = $_SESSION['user_id'];

// Check if post exists
$post = getPost($post_id);
if (!$post) {
    echo json_encode([
        'success' => false,
        'message' => 'Post not found'
    ]);
    exit;
}

// Toggle like status
$liked = toggleLike($post_id, $user_id);

// Get updated like count
$likes_count = getLikesCount($post_id);

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'likes_count' => $likes_count,
    'message' => $liked ? 'Post liked successfully' : 'Post unliked successfully'
]);