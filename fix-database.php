<?php
require_once 'config/database.php';

$messages = [];

// Check if bio column exists
$check_bio = $conn->query("SHOW COLUMNS FROM users LIKE 'bio'");
$bio_exists = $check_bio->num_rows > 0;

if (!$bio_exists) {
    // Add bio column to users table
    $sql = "ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        $messages[] = ["type" => "success", "text" => "The 'bio' column has been added to the users table."];
    } else {
        $messages[] = ["type" => "error", "text" => "Failed to add 'bio' column: " . $conn->error];
    }
} else {
    $messages[] = ["type" => "info", "text" => "The 'bio' column already exists in the users table."];
}

// Check if avatar column exists
$check_avatar = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
$avatar_exists = $check_avatar->num_rows > 0;

if (!$avatar_exists) {
    // Add avatar column to users table
    $sql = "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        $messages[] = ["type" => "success", "text" => "The 'avatar' column has been added to the users table."];
    } else {
        $messages[] = ["type" => "error", "text" => "Failed to add 'avatar' column: " . $conn->error];
    }
} else {
    $messages[] = ["type" => "info", "text" => "The 'avatar' column already exists in the users table."];
}

// Display styled messages
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Database Structure</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #343a40;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        .message {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .btn:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Structure Fix</h1>
        
        <?php foreach ($messages as $message): ?>
            <div class="message <?php echo $message['type']; ?>">
                <p><?php echo $message['text']; ?></p>
            </div>
        <?php endforeach; ?>
        
        <p>This script has checked and fixed the database structure for your blog application.</p>
        
        <a href="profile.php" class="btn">Return to Profile</a>
    </div>
</body>
</html>